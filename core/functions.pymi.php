<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//ADD SCRAPPER
	$G_SCRAPPERS[ 'pymi' ] = array( '', 'ident_detect_file_pymi' );
	$G_SCRAPPERS_KEY[ 'pymi' ] = '';
	$G_SCRAPPERS_SEARCH[ 'pymi' ] = array( 'imdb.com/title/tt', 'imdb.com', 'getIMDB_ID' );
	
	//BASE
	function ident_detect_file_pymi( $file, $title, $movies = TRUE, $imdb = FALSE, $season = FALSE, $episode = FALSE ){
        //autodetect pymediaident
        //wait for changes
        //recolect NEEDED DATA
        // array( poster, fanart, logo, poster, banner, landscape, data => $G_MEDIAINFO )
        global $G_MEDIADATA;
        $result = $G_MEDIADATA;
        
        if( $season !== FALSE 
        && get_media_chapter( $title ) == FALSE
        ){
            $title .= ' ' . $season . 'x' . $episode;
        }
        
        if( ( $pydata = ident_detect_pymi( $file, $title, $movies, $imdb ) ) != FALSE 
        && ( $data_nfo = ident_pymi_read_data( $pydata, $file ) ) != FALSE
        ){
            $result = $data_nfo;
            //EXTRA SEASON && EPISODE FROM FILENAME SSxCC
            if( is_numeric( $season ) ){
                $result[ 'data' ][ 'season' ] = (int)$season;
                $result[ 'data' ][ 'episode' ] = (int)$episode;
            }
            //Images URLs
            $k='poster';
            $TMP_FOLDER = PPATH_TEMP . DS . getRandomString( 8 );
            $FILENAME = $TMP_FOLDER . DS . getRandomString( 8 );
            if( array_key_exists( $k, $result ) 
            && filter_var( $result[ $k ], FILTER_VALIDATE_URL )
            && @mkdir( $TMP_FOLDER )
            && ident_download_pymi( $result[ $k ], $FILENAME )
            && file_exists( $FILENAME )
            ){
                $result[ $k ] = $FILENAME;
            }else{
                $result[ $k ] = '';
            }
        }else{
            $result = FALSE;
        }
        
        return $result;
	}
	
	function ident_detect_pymi( $file, $title, $movies, $imdbid = FALSE ){
        $result = FALSE;
        
        if( $movies ){
            $force_type = ' ';
        }else{
            $force_type = ' ';
        }
        if( $imdbid != FALSE ){
            $force_ident = ' -sid ' . $imdbid . ' ';
            $cmd = O_PYMEDIAIDENT . ' -s imdb -l ' . O_LANG . ' -fs "' . $title . '" ' . $force_ident . ' --json ';
        }else{
            $force_ident = '';
            $cmd = O_PYMEDIAIDENT . ' -s imdb -l ' . O_LANG . ' -fs "' . $title . '" ' . $force_ident . ' --json ';
        }
        
        //Get Data
        //echo "CMD: " . $cmd;
        $result = runExtCommand( $cmd );
		
		return $result;
	}
	
	//GET INFO
	
	function ident_pymi_read_data( $data, $basefile ){
		global $G_MEDIAINFO;
		global $G_MEDIADATA;
		$result = $G_MEDIADATA;
		$rtesult = $G_MEDIAINFO;
		
		if( ( $xml = (array)@json_decode( $data ) ) != FALSE
		){
            //TITLE
            $f = 'title';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //sorttitle
            $f = 'releasedate';
            $fi = 'sorttitle';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $added = FALSE;
                $dat = trim( $xml[ $f ] );
                if( strtotime( $dat ) != FALSE ){
                    $rtesult[ $fi ] = $dat;
                    $added = TRUE;
                }
                if( !$added ){
                    if( array_key_exists( 'year', $xml ) 
                    && is_string( $xml[ 'year' ] )
                    ){
                        $rtesult[ $fi ] = $xml[ 'year' ] . '-01-01';
                    }else{
                        $rtesult[ $fi ] = '0000-00-00';
                    }
                }
            }
            //SEASON
            $f = 'season';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //ALTERNATIVE SEASON from filename SSxCC on main
            //EPISODE
            $f = 'chapter';
            $fi = 'episode';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //ALTERNATIVE EPISODE from filename SSxCC on main
            //year
            $f = 'year';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //rating
            $f = 'rating';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //votes
            $f = 'votes';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //mpaa
            $f = 'rated';
            $fi = 'mpaa';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //tagline
            $f = 'plotshort';
            $fi = 'tagline';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //runtime -- NO EXIST
            $f = 'runtime';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            
            //plot
            $f = 'plot';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //imdbid -- NO EXIST
            $f = 'imdbid';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //imdb -- NO EXIST
            $f = 'imdb';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //tmdbid -- NO EXIST
            $f = 'tmdbid';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //tmdb -- NO EXIST
            $f = 'tmdb';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //tvdbid -- NO EXIST
            $f = 'tvdbid';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //tvdb -- NO EXIST
            $f = 'tvdb';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //runtime -- NO EXIST
            $f = 'runtime';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //re runtime to ffmpeg
            if( ( !is_numeric( $rtesult[ $fi ] ) 
            || $rtesult[ $fi ] == 0
            )
            && ( $mins = ffmpeg_file_info_lenght_minutes( $basefile ) ) != FALSE
            ){
                $rtesult[ $fi ] = $mins;
            }
            
            //titleepisode
            $f = 'chaptertitle';
            $fi = 'titleepisode';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            
            //GENRES
            $f = 'genres';
            $fi = 'genre';
            if( array_key_exists( $f, $xml ) ){
                if( is_array( $xml[ $f ] ) ){
                    foreach( $xml[ $f ] AS $g ){
                        if( strlen( $rtesult[ $fi ] ) > 0 ){
                            $rtesult[ $fi ] .= ',';
                        }
                        $rtesult[ $fi ] .= $g;
                    }
                }else{
                    $rtesult[ $fi ] = $xml[ $f ];
                }
            }
            
            //ACTORS
            $f = 'actors'; 
            $fi = 'actor';
            if( array_key_exists( $f, $xml ) ){
                if( is_array( $xml[ $f ] ) ){
                    foreach( $xml[ $f ] AS $actor ){
                        if( strlen( $actor ) > 0 ){
                            $rtesult[ $fi ] .= ',' . $actor;
                        }
                    }
                }else{
                    $rtesult[ $fi ] = $xml[ $f ];
                }
            }
            
            //AUDIO --NOEXIST
            $f = 'audio';
			
            //SUBS --NOEXIST
            $f = 'subtitle'; 
            
            //IMGs
            //POSTER
            $f = 'urlposter';
            $fi = 'poster';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] )
            ){
                $result[ $fi ] = $xml[ $f ];
            }
            $result[ 'data' ] = $rtesult;
            
            return $result;
        } 
    }

	function ident_download_pymi( $url, $file ){
        $result = FALSE;
        
        //Get Data
        $cmd = O_WGET . ' -O "' . $file . '" "' . $url . '"';
        runExtCommand( $cmd );
        $result = TRUE;
		
		return $result;
	}
	
?>
