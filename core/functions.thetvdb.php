<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//ADD SCRAPPER
	$G_SCRAPPERS[ 'thetvdb' ] = array( '', 'ident_detect_file_thetvdb' );
	//$G_SCRAPPERS_KEY[ 'thetvdb' ] = '--YOURKEY--';
	$THETVDB_URL = 'https://api.thetvdb.com/';
	$THETVBD_FILE = PPATH_TEMP . DS . 'thetvbd';
	
	if( file_exists( $THETVBD_FILE ) 
	&& filesize( $THETVBD_FILE ) > 0
	&& ( time() - filemtime( $THETVBD_FILE ) ) < ( 24 * 3600 )
	){
        $THETVDB_TOKEN = @file_get_contents( $THETVBD_FILE );
	}else{
        @unlink( $THETVBD_FILE );
        $THETVDB_TOKEN = FALSE;
	}
	
	//BASE
	function ident_detect_file_thetvdb( $file, $title, $movies = TRUE, $imdb = FALSE, $season = FALSE, $episode = FALSE ){
        //Create temp folder
        //detect if $imdb == IMDB tt2866360
        // get data from query
        //not IMDB search for title and compare strings with first page
        // get data from nearest in title
        //recolect NEEDED DATA
        // array( poster, fanart, logo, poster, banner, landscape, data => $G_MEDIAINFO )
        global $G_MEDIADATA;
        global $THETVDB_TOKEN;
        $result = $G_MEDIADATA;
        
        $debug = FALSE;
        if( $debug ) echo "<br />THETVDB DETECT FILE: " . $imdb . ' - ' . $title . ' - ' . $file;
        if( $season !== FALSE 
        && get_media_chapter( $title ) == FALSE
        ){
            //$title .= ' ' . $season . 'x' . $episode;
        }
        //extra clean name, not chapter/episode or ()[]
        $title = clean_filename( $title, TRUE );
        $TMP_FOLDER = PPATH_TEMP . DS . getRandomString( 8 );
        
        if( file_exists( $file )
        && mkdir( $TMP_FOLDER )
        && ( 
            is_string( $THETVDB_TOKEN )
            || ident_thetvdb_login( TRUE ) != FALSE
            )
        ){
            if( $imdb != FALSE 
            && ( $result = ident_detect_thetvdb( $imdb, $title, TRUE ) ) != FALSE
            ){
                //Convert URLs IMGs to Files On TMP Folder
                foreach( $result AS $k => $v ){
                    if( is_string( $v )
                    ){
                        $FILENAME = $TMP_FOLDER . DS . getRandomString( 8 );
                        if( filter_var( $v, FILTER_VALIDATE_URL )
                        && downloadPosterToFile( $v, $FILENAME ) 
                        ){
                            $result[ $k ] = $FILENAME;
                        }else{
                            $result[ $k ] = '';
                        }
                    }
                }
                //Add season episode
                if( $season !== FALSE 
                && array_key_exists( 'data', $result )
                ){
                    $result[ 'data' ][ 'season' ] = $season;
                }
                if( $episode !== FALSE
                && array_key_exists( 'data', $result )
                ){
                    $result[ 'data' ][ 'episode' ] = $episode;
                }
            }
        }else{
            $result = FALSE;
        }
        if( $debug ) echo "<br />THETVDB DETECT FILE RESULT: " . print_r( $result, TRUE );
        return $result;
	}
	
	//IMDBID && TITLE
	
	function ident_detect_thetvdb( $imdbID, $title = FALSE, $debug = FALSE ){
        $result = FALSE;
        global $THETVDB_URL;
        
        if( $debug ) echo "<br />THETVDB DETECT: " . $imdbID . ' - ' . $title;
        if( ( $finded = ident_detect_thetvdb_search_id( $imdbID, $title, FALSE, $debug ) ) 
        && is_array( $finded )
        && count( $finded ) > 0
        && ( $id = array_keys( $finded ) ) != FALSE
        && count( $id ) > 0
        ){
            $id = $id[ 0 ];
            if( ( $data = ident_detect_thetvdb_getdata_id( $id, $debug ) ) != FALSE
            && is_array( $data )
            && ( $result = ident_thetvdb_read_data( $data ) ) != FALSE
            ){
                
            }else{
                $result = FALSE;
            }
        }
        if( $debug ) echo "<br />THETVDB DETECT RESULT: " . print_r( $result, TRUE );
        //var_dump( $data );
        return $result;
	}
	
	function ident_detect_thetvdb_getdata_id( $thetvbd_id, $debug = FALSE ){
        $result = FALSE;
        global $THETVDB_URL;
        global $THETVDB_TOKEN;
        
        //BY ID
        //id
        
        $url = $THETVDB_URL . 'series/' . $thetvbd_id;
        if( $debug ) echo "<br />THETVDB GET DATA ID: " . $thetvbd_id;
        
        $postdata = array();
        //$postdata[ 'id' ] = $thetvbd_id;
        $postdata[ 'token' ] = $THETVDB_TOKEN;
        
        if( ( $data = ident_thetvdb_get_data( $url, $THETVDB_TOKEN, TRUE ) ) != FALSE
        && is_array( $data )
        && array_key_exists( 'data', $data )
        && is_array( $data[ 'data' ] )
        && count( $data[ 'data' ] ) > 0
        ){
            $result = array();
            $result = $data[ 'data' ];
            
            //ACTORS
            $result[ 'actors' ] = '';
            if( ( $data = ident_thetvdb_get_data( $url . '/actors', $THETVDB_TOKEN, TRUE ) ) != FALSE
            && is_array( $data )
            && array_key_exists( 'data', $data )
            && is_array( $data[ 'data' ] )
            && count( $data[ 'data' ] ) > 0
            ){
                foreach( $data[ 'data' ] AS $row ){
                    if( array_key_exists( 'name', $row ) 
                    && array_key_exists( 'image', $row ) 
                    ){
                        if( strlen( $result[ 'actors' ] ) > 0 ) $result[ 'actors' ] .= ',';
                        $result[ 'actors' ] .= '' . $row[ 'name' ];
                        //CHECK IMAGES
                        if( filter_var( $THETVDB_URL . $row[ 'image' ], FILTER_VALIDATE_URL )
                        ){
                            $tofile = PPATH_MEDIAINFO . DS . $row[ 'name' ];
                            if( !file_exists( $tofile ) ){
                                @downloadPosterToFile( $THETVDB_URL . $row[ 'image' ], $tofile );
                            }
                        }
                    }
                }
            }
            
            //IMAGES
            $result[ 'images' ] = array();
            if( ( $data = ident_thetvdb_get_data( $url . '/images', $THETVDB_TOKEN, $debug ) ) != FALSE
            && is_array( $data )
            && array_key_exists( 'data', $data )
            && is_array( $data[ 'data' ] )
            && count( $data[ 'data' ] ) > 0
            ){
                foreach( $data[ 'data' ] AS $row ){
                    //POSTER
                    if( array_key_exists( 'poster', $row ) 
                    && filter_var( $row[ 'poster' ], FILTER_VALIDATE_URL )
                    ){
                        $result[ 'images' ][ 'poster' ] = $row[ 'poster' ];
                    }
                    //LANDSCAPE
                    if( array_key_exists( 'fanart', $row ) 
                    && filter_var( $row[ 'fanart' ], FILTER_VALIDATE_URL )
                    ){
                        $result[ 'images' ][ 'landscape' ] = $row[ 'fanart' ];
                    }
                }
            }
        }else{
            $result = FALSE;
        }
        if( $debug ) echo "<br />THETVDB GET DATA ID: " . print_r( $result, TRUE );
        //var_dump( $data );
        return $result;
	}
	
	function ident_detect_thetvdb_search_id( $imdbID = FALSE, $title = FALSE, $movies = FALSE, $debug = FALSE ){
        $result = FALSE;
        global $THETVDB_URL;
        global $THETVDB_TOKEN;
        
        //BY ID OR TITLE
        //imdbId=imdbtitle
        //name=search title
        //zap2itId=series,episode
        //y= year of release
        
        if( $debug ) echo "<br />THETVDB SEARCH IDENT: " . $imdbID . ' - ' . $title;
        if( $movies ){
            $stype = 'movie';
        }else{
            $stype = 'series';
            //$stype = 'episode';
        }
        
        //$imdbID = 'tt2866360';
        $url = $THETVDB_URL . 'search/series?imdbId=' . $imdbID;
        $url2 = $THETVDB_URL . 'search/series?name=' . $title;
        
        if( (
            ( $data = ident_thetvdb_get_data( $url, $THETVDB_TOKEN, $debug ) ) != FALSE
            ||
            ( $data = ident_thetvdb_get_data( $url, $THETVDB_TOKEN, $debug ) ) != FALSE
            )
        && is_array( $data )
        && array_key_exists( 'data', $data )
        && is_array( $data[ 'data' ] )
        && count( $data[ 'data' ] )
        ){
            $result = array();
            foreach( $data[ 'data' ] AS $row ){
                if( array_key_exists( 'seriesName', $row )
                && array_key_exists( 'id', $row ) 
                ){
                    $result[ $row[ 'id' ] ] = $row[ 'seriesName' ];
                }
            }
        }else{
            $result = FALSE;
        }
        if( $debug ) echo "<br />THETVDB SEARCH IDENT RESULT: " . print_r( $result, TRUE );
        //var_dump( $data );
        return $result;
	}
	
	//GET DATA FROM ARRAY
	
	function ident_thetvdb_read_data( $xml ) { 
		global $G_MEDIAINFO;
		global $G_MEDIADATA;
		global $G_DATA;
		$result = $G_MEDIADATA;
		$rtesult = $G_MEDIAINFO;
		
		if( is_array( $xml )
		){
            //TITLE
            $f = 'seriesName';
            $fi = 'title';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //sorttitle = Released (Omdb format:  05 May 2017)
            $f = 'firstAired';
            $fi = 'sorttitle';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            && ( $temp_date = date( 'Y-m-d', strtotime( $xml[ $f ] ) ) ) !== FALSE
            ){
                $rtesult[ $fi ] = $temp_date;
            }elseif( array_key_exists( 'Year', $xml ) ){
                $rtesult[ $fi ] = $xml[ 'Year' ] . '00-00';
            }else{
                $rtesult[ $fi ] = '0000-00-00';
            }
            //SEASON
            //ALTERNATIVE SEASON from filename SSxCC on main
            //EPISODE
            //ALTERNATIVE EPISODE from filename SSxCC on main
            //year
            $f = 'firstAired';
            $fi = 'year';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            && ( $temp_date = date( 'Y', strtotime( $xml[ $f ] ) ) ) !== FALSE
            ){
                $rtesult[ $fi ] = $temp_date;
            }
            //rating
            $f = 'siteRating';
            $fi = 'rating';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //votes
            $f = 'siteRatingCount';
            $fi = 'votes';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] )
            ){
                $rtesult[ $fi ] = (int)$xml[ $f ];
            }
            //mpaa
            $f = 'rating';
            $fi = 'mpaa';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //tagline -- NOT EXIST
            $f = 'tagline';
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //runtime //136 min
            $f = 'runtime';
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = (int)$xml[ $f ];
            }
            //plot
            $f = 'overview';
            $fi = 'plot';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //file info stream details HEIGHT WIDTH CODEC
            //
            //imdbid
            $f = 'imdbId';
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //imdb -- NOT EXIST
            
            //tmdbid -- NOT EXIST
            
            //tmdb -- NOT EXIST
            
            //tvdbid
            $f = 'id';
            $fi = 'tvdbid';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            
            //tvdb -- NOT EXIST
            
            //GENERES genre
            $f = 'genre'; 
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }elseif( array_key_exists( $f, $xml ) 
            && is_array( $xml[ $f ] ) 
            ){
                foreach( $xml[ $f ] AS $g ){
                    if( strlen( $rtesult[ $fi ] ) > 0 ) $rtesult[ $fi ] .= ',';
                    $rtesult[ $fi ] .= $g;
                }
            }
            
            //ACTORS
            $f = 'actors'; 
            $fi = 'actor'; 
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            
            //AUDIO
            
            //SUBS
            
            //titleepisode -- NOT EXIST
            $f = 'titleepisode'; 
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            
            //IMGs
            //POSTER
            $f = 'images';
            if( array_key_exists( $f, $xml ) 
            && is_array( $xml[ $f ] )
            ){
                $eurl = 'https://www.thetvdb.com/banners/';
                $fi = 'poster';
                if( array_key_exists( $fi, $xml[ $f ] ) 
                && filter_var( $eurl . $xml[ $f ][ $fi ], FILTER_VALIDATE_URL )
                ){
                    $result[ $fi ] = $eurl . $xml[ $f ][ $fi ];
                }
                $fi = 'landscape';
                if( array_key_exists( $fi, $xml[ $f ] ) 
                && filter_var( $eurl . $xml[ $f ][ $fi ], FILTER_VALIDATE_URL )
                ){
                    $result[ $fi ] = $eurl . $xml[ $f ][ $fi ];
                }
                $fi = 'banner';
                if( array_key_exists( $fi, $xml[ $f ] ) 
                && filter_var( $eurl . $xml[ $f ][ $fi ], FILTER_VALIDATE_URL )
                ){
                    $result[ $fi ] = $eurl . $xml[ $f ][ $fi ];
                }
            }
            
            $result[ 'data' ] = $rtesult;
		}
		
		return $result;
	} 
	
	//LOGIN REFRESH KEY
	
	function ident_thetvdb_login( $debug = FALSE ){
        $result = FALSE;
        global $THETVDB_TOKEN;
        global $G_SCRAPPERS_KEY;
        global $THETVDB_URL;
        global $THETVBD_FILE;
        
        if( $debug ) echo "<br />THETVDB LOGIN: " . $THETVDB_TOKEN;
        
        if( $THETVDB_TOKEN
        || (
            is_array( $G_SCRAPPERS_KEY )
            && array_key_exists( 'thetvdb', $G_SCRAPPERS_KEY )
            && strlen( $G_SCRAPPERS_KEY[ 'thetvdb' ] ) > 0
            )
        ){
            $url = $THETVDB_URL . 'login';
            $postdata = array(
                'apikey' => $G_SCRAPPERS_KEY[ 'thetvdb' ],
            );
            
            if( $THETVDB_TOKEN 
            && ( $THETVDB_TOKEN = ident_thetvdb_login_refresh( $THETVDB_TOKEN, $debug ) ) != FALSE
            ){
                $result = $THETVDB_TOKEN;
                file_put_contents( $THETVBD_FILE, $THETVDB_TOKEN );
            }elseif( ( $data = ident_thetvdb_get_data_post( $url, $postdata, $debug ) ) != FALSE
            && is_array( $data )
            && array_key_exists( 'token', $data )
            && strlen( $data[ 'token' ] ) > 0
            ){
                $result = $data[ 'token' ];
                $THETVDB_TOKEN = $data[ 'token' ];
                file_put_contents( $THETVBD_FILE, $THETVDB_TOKEN );
            }else{
                file_put_contents( $THETVBD_FILE, '' );
            }
        }
        if( $debug ) echo "<br />THETVDB LOGIN: " . print_r( $result, TRUE );
        return $result;
	}
	
	function ident_thetvdb_login_refresh( $token, $debug = FALSE ){
        $result = FALSE;
        global $THETVDB_URL;
        $url = $THETVDB_URL . 'refresh_token';
        
        $postdata = array(
            'token' => $token,
        );
        
        if( ( $data = ident_thetvdb_get_data_post( $url, $postdata, $debug ) ) != FALSE
        && is_array( $data )
        && array_key_exists( 'token', $data )
        && strlen( $data[ 'token' ] ) > 0
        ){
            $result = $data[ 'token' ];
        }
        if( $debug ) echo "<br />THETVDB LOGIN REFRESH: " . print_r( $result, TRUE );
        return $result;
	}
	
	function ident_thetvdb_get_data( $url, $auth, $debug = FALSE ){
        $result = FALSE;
        
        if( $debug ){
            echo "<br />INIT THETVDB GET DATA: " . $url;
        }
        $auth = 'Authorization: Bearer ' . $auth;
        if( $debug ){
            echo "<br />POST THETVDB GET DATA: " . $url;
            echo "<br />" . print_r( $auth, TRUE );
        }
        if( ( 
            ( $data = json_decode( @file_get_contents_timed_get_json( $url, 10, '', $auth, $debug ), TRUE ) ) != FALSE
            || (
                //try relogin
                ident_thetvdb_login( $debug )
                && 
                ( $data = json_decode( @file_get_contents_timed_get_json( $url, 10, '', $auth, $debug ), TRUE ) ) != FALSE
            )
        )
        && is_array( $data )
        && count( $data ) > 0
        && !array_key_exists( 'Error', $data )
        ){
            $result = $data;
        }
        if( $debug ){
            echo "<br />";
            echo "<br />THETVDB END GET DATA: " . $url;
            echo "<br />THETVDB END GET DATA RESULT: ";
            echo "<br />" . print_r( $data, TRUE );
        }
        if( $debug ) echo "<br />THETVDB GET DATA RESULT: " . print_r( $result, TRUE );
        return $result;
	}
	
	function ident_thetvdb_get_data_post( $url, $postdata, $debug = FALSE ){
        $result = FALSE;
        
        if( $debug ){
            echo "<br />INIT THETVDB GET DATA: " . $url;
            echo "<br />" . print_r( $postdata, TRUE );
        }
        if( array_key_exists( 'token', $postdata ) ){
            $auth = 'Authorization: Bearer ' . $postdata[ 'token' ];
            unset( $postdata[ 'token' ] );
        }else{
            $auth = '';
        }
        if( $debug ){
            echo "<br />POST THETVDB GET DATA: " . $url;
            echo "<br />" . print_r( $postdata, TRUE );
            echo "<br />" . print_r( $auth, TRUE );
        }
        if( ( 
            ( $data = json_decode( @file_get_contents_timed_post_json( $url, $postdata, 10, '', $auth, $debug ), TRUE ) ) != FALSE
            || (
                //try relogin
                ident_thetvdb_login( $debug )
                && 
                ( $data = json_decode( @file_get_contents_timed_post_json( $url, $postdata, 10, '', $auth, $debug ), TRUE ) ) != FALSE
            )
        )
        && is_array( $data )
        && count( $data ) > 0
        && !array_key_exists( 'Error', $data )
        ){
            $result = $data;
        }
        if( $debug ){
            echo "<br />";
            echo "<br />THETVDB END GET DATA: " . $url;
            echo "<br />THETVDB END GET DATA RESULT: ";
            echo "<br />" . print_r( $data, TRUE );
        }
        if( $debug ) echo "<br />THETVDB GET DATA RESULT: " . print_r( $result, TRUE );
        return $result;
	}
	
?>
