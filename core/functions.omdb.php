<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//ADD SCRAPPER
	$G_SCRAPPERS[ 'omdb' ] = array( '', 'ident_detect_file_omdb' );
	//$G_SCRAPPERS_KEY[ 'omdb' ] = '--YOURKEY--';
	$OMDB_URL = 'http://www.omdbapi.com/?apikey=' . $G_SCRAPPERS_KEY[ 'omdb' ];
	
	//BASE
	function ident_detect_file_omdb( $file, $title, $movies = TRUE, $imdb = FALSE, $season = FALSE, $episode = FALSE ){
        //Create temp folder
        //detect if $imdb == IMDB tt2866360
        // get data from query
        //not IMDB search for title and compare strings with first page
        // get data from nearest in title
        //recolect NEEDED DATA
        // array( poster, fanart, logo, poster, banner, landscape, data => $G_MEDIAINFO )
        global $G_MEDIADATA;
        $result = $G_MEDIADATA;
        
        if( $season !== FALSE 
        && get_media_chapter( $title ) == FALSE
        ){
            $title .= ' ' . $season . 'x' . $episode;
        }
        
        $TMP_FOLDER = PPATH_TEMP . DS . getRandomString( 8 );
        //echo "<br />" . $file . ' => ' . $title;die();
        if( file_exists( $file )
        && mkdir( $TMP_FOLDER )
        ){
            if( $imdb != FALSE 
            && ( $result = ident_detect_omdb_id( $imdb, $movies ) ) != FALSE
            ){
                //var_dump( $result );
            }elseif( ( $result = ident_search_omdb( $title, $movies ) ) != FALSE ){
                //var_dump( $result );
            }else{
                $result = FALSE;
            }
            if( $result !== FALSE ){
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
            }
        }else{
            $result = FALSE;
        }
        
        return $result;
	}
	
	//IMDBID
	
	function ident_search_omdb( $title, $movies = TRUE ){
        $result = FALSE;
        global $OMDB_URL;
        
        //BY ID OR TITLE
        //i=imdbtitle
        //t=search title
        //type=movie,series,episode
        //y= year of release
        //plot=short|full
        //r=json|xml
        
        //BY SEARCH
        //s=search title
        //type=movie,series,episode
        //r=json|xml
        //y= year of release
        
        if( $movies ){
            $stype = 'movie';
        }else{
            $stype = 'series';
            //$stype = 'episode';
        }
        
        $url = $OMDB_URL . '&s=' . $title; 
        //die( $url );
        if( ( $data = json_decode( @file_get_contents_timed( $url ), TRUE ) ) != FALSE
        && is_array( $data )
        && array_key_exists( 'Search', $data )
        && is_array( $data[ 'Search' ] )
        && count( $data[ 'Search' ] ) > 0
        ){
            //Title, Year imdbID, Type, Poster
            $Titles = array();
            foreach( $data[ 'Search' ] AS $k => $d ){
                if( is_array( $d )
                && array_key_exists( 'Title', $d ) 
                ){
                    $Titles[ $k ] = $d[ 'Title' ];
                }
            }
            $now_k = 0;
            $now_k_sim = 0;
            foreach( $Titles AS $k => $v ){
                if( similar_text( $title, $v, $pc ) > $now_k_sim ){
                    $now_k_sim = $pc;
                    $now_k = $k;
                }
            }
            //Get Similiarity
            if( array_key_exists( $now_k, $data[ 'Search' ] ) 
            && array_key_exists( 'imdbID', $data[ 'Search' ][ $now_k ] )
            ){
                $result = ident_detect_omdb_id( $data[ 'Search' ][ $now_k ][ 'imdbID' ], $movies );
            }elseif( array_key_exists( 0, $data[ 'Search' ] ) 
            && array_key_exists( 'imdbID', $data[ 'Search' ][ 0 ] )
            ){
                $result = ident_detect_omdb_id( $data[ 'Search' ][ 0 ][ 'imdbID' ], $movies );
            }
        }else{
            $result = FALSE;
        }
        
        return $result;
	}
	
	function ident_detect_omdb_id( $imdbID, $movies = TRUE ){
        $result = FALSE;
        global $OMDB_URL;
        
        //BY ID OR TITLE
        //i=imdbtitle
        //t=search title
        //type=movie,series,episode
        //y= year of release
        //plot=short|full
        //r=json|xml
        
        //BY SEARCH
        //s=search title
        //type=movie,series,episode
        //r=json|xml
        //y= year of release
        
        if( $movies ){
            $stype = 'movie';
        }else{
            $stype = 'series';
            //$stype = 'episode';
        }
        
        //$imdbID = 'tt2866360';
        $url = $OMDB_URL . '&plot=full&i=' . $imdbID;
        //die( $url );
        if( ( $data = json_decode( @file_get_contents_timed( $url ), TRUE ) ) != FALSE
        && is_array( $data )
        && array_key_exists( 'Title', $data )
        && strlen( $data[ 'Title' ] ) > 0
        && ( $result = ident_omdb_read_data( $data ) ) != FALSE
        ){
            
        }else{
            $result = FALSE;
        }
        //var_dump( $data );
        return $result;
	}
	
	//TITLE
	
	//GET DATA FROM ARRAY
	
	function ident_omdb_read_data( $xml ) { 
		global $G_MEDIAINFO;
		global $G_MEDIADATA;
		global $G_DATA;
		$result = $G_MEDIADATA;
		$rtesult = $G_MEDIAINFO;
		
		if( is_array( $xml )
		){
            //TITLE
            $f = 'Title';
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //sorttitle = Released (Omdb format:  05 May 2017)
            $f = 'Released';
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
            $f = 'season';
            $fi = strtolower( $f );
            if( array_key_exists( $f, $G_DATA ) 
            && is_numeric( $G_DATA[ $f ] ) 
            ){
                $rtesult[ $fi ] = $G_DATA[ $f ];
            }
            //ALTERNATIVE SEASON from filename SSxCC on main
            //EPISODE
            $f = 'episode';
            $fi = strtolower( $f );
            if( array_key_exists( $f, $G_DATA ) 
            && is_numeric( $G_DATA[ $f ] ) 
            ){
                $rtesult[ $fi ] = $G_DATA[ $f ];
            }
            //ALTERNATIVE EPISODE from filename SSxCC on main
            //year
            $f = 'Year';
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = substr( preg_replace('/\D+/', '', $xml[ $f ] ), 0, 4 );
            }
            //rating
            $f = 'imdbRating';
            $fi = 'rating';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //votes
            $f = 'imdbVotes';
            $fi = 'votes';
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] )
            ){
                $rtesult[ $fi ] = (int)$xml[ $f ];
            }
            //mpaa
            $f = 'Rated';
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
            $f = 'Runtime';
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = (int)$xml[ $f ];
            }
            //plot
            $f = 'Plot';
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //file info stream details HEIGHT WIDTH CODEC
            //
            //imdbid
            $f = 'imdbID';
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //imdb -- NOT EXIST
            
            //tmdbid -- NOT EXIST
            
            //tmdb -- NOT EXIST
            
            //tvdbid -- NOT EXIST
            
            //tvdb -- NOT EXIST
            
            //GENERES
            $f = 'Genre'; 
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            
            //ACTORS
            $f = 'Actors'; 
            $fi = 'actor'; 
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            
            //AUDIO
            
            //SUBS
            
            //titleepisode
            $f = 'titleepisode'; 
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            
            //IMGs
            //POSTER
            $f = 'Poster';
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] )
            ){
                $result[ $fi ] = $xml[ $f ];
            }
            $result[ 'data' ] = $rtesult;
            
		}
		
		return $result;
	} 
	
?>
