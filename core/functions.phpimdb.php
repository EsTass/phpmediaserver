<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//ADD SCRAPPER
	$G_SCRAPPERS[ 'phpimdb' ] = array( '', 'ident_detect_file_phpimdb' );
	$G_SCRAPPERS_KEY[ 'phpimdb' ] = '';
	
	//BASE
	function ident_detect_file_phpimdb( $file, $title, $movies = TRUE, $imdb = FALSE, $season = FALSE, $episode = FALSE ){
        //autodetect phpimdb
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
        
        if( ( $pydata = ident_detect_phpimdb( $file, $title, $movies, $imdb ) ) != FALSE 
        && ( $data_nfo = ident_phpimdb_read_data( $pydata, $file ) ) != FALSE
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
            && ident_download_phpimdb( $result[ $k ], $FILENAME )
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
	
	function ident_detect_phpimdb( $file, $title, $movies, $imdbid = FALSE ){
        $result = FALSE;
        
        $cfile = PPATH_CORE . DS . 'imdb.class.php';
        if( !file_exists( $cfile ) ){
            echo "<br />NO FILE imdb.class.php IN CORE FOLDER!!!!";
        }else{
            //set title or imdb url
            if( $imdbid ){
                //set url 'http://www.imdb.com/title/tt0144117'
                $titlef = 'http://www.imdb.com/title/' . $imdbid;
            }else{
                $titlef = $title;
            }
            require_once $cfile;
            //TODO search inet before?
            $IMDB = new IMDB( $titlef );
            
            //TODO set languaje
            //$IMDB->IMDB_LANG = 'es-ES,es;q=0.9';
            
            if( $IMDB->isReady ){
                if( !is_string( $IMDB->getTitle() ) 
                || strlen( $IMDB->getTitle() ) == 0
                || !is_string( $IMDB->getPlot() ) 
                || strlen( $IMDB->getPlot() ) < 5
                ){
                    //check in def languaje
                    $IMDB = new IMDB( $titlef );
                    if( $IMDB->isReady ){
                        $result = $IMDB;
                    }else{
                        $result = FALSE;
                    }
                }else{
                    $result = $IMDB;
                }
                //$result = $IMDB->getAll();
            }else{
                $result = FALSE;
            }
        }
		
		return $result;
	}
	
	//GET INFO
	
	function ident_phpimdb_read_data( $data, $basefile ){
		global $G_MEDIAINFO;
		global $G_MEDIADATA;
		$result = $G_MEDIADATA;
		$rtesult = $G_MEDIAINFO;
		
		if( $data != FALSE
		){
            //TITLE
            $f = 'title';
            $fi = $f;
            $rtesult[ $fi ] = $data->getTitle();
            
            //sorttitle
            $f = 'releasedate';
            $fi = 'sorttitle';
            if( is_string( $data->getReleaseDate() ) 
            ){
                $added = FALSE;
                $dat = trim( $data->getReleaseDate() );
                if( strtotime( $dat ) != FALSE ){
                    $rtesult[ $fi ] = $dat;
                    $added = TRUE;
                }
                if( !$added ){
                    if( is_string( $data->getYear() )
                    ){
                        $rtesult[ $fi ] = $data->getYear() . '-01-01';
                    }else{
                        $rtesult[ $fi ] = '0000-00-00';
                    }
                }
            }
            //SEASON
            $f = 'season';
            $fi = $f;
            if( is_string( $data->getSeasons() ) 
            ){
                $rtesult[ $fi ] = $data->getSeasons();
            }
            //ALTERNATIVE SEASON from filename SSxCC on main
            //EPISODE
            $f = 'chapter';
            $fi = 'episode';
            $rtesult[ $fi ] = 0;
            
            //year
            $f = 'year';
            $fi = $f;
            if( is_string( $data->getYear() ) 
            ){
                $rtesult[ $fi ] = $data->getYear();
            }
            
            //rating
            $f = 'rating';
            $fi = $f;
            if( is_string( $data->getRating() ) 
            ){
                $rtesult[ $fi ] = $data->getRating();
            }
            
            //votes
            $f = 'votes';
            $fi = $f;
            if( is_string( $data->getVotes() ) 
            ){
                $rtesult[ $fi ] = $data->getVotes();
            }
            
            //mpaa
            $f = 'rated';
            $fi = 'mpaa';
            if( is_string( $data->getMpaa() ) 
            ){
                $rtesult[ $fi ] = $data->getMpaa();
            }
            
            //tagline
            $f = 'plotshort';
            $fi = 'tagline';
            if( is_string( $data->getTagline() ) 
            ){
                $rtesult[ $fi ] = $data->getTagline();
            }
            
            //runtime getRuntime
            $f = 'runtime';
            $fi = $f;
            if( is_string( $data->getRuntime() ) 
            ){
                $rtesult[ $fi ] = $data->getRuntime();
            }
            
            //plot
            $f = 'plot';
            $fi = $f;
            if( is_string( $data->getPlot() ) 
            ){
                $rtesult[ $fi ] = $data->getPlot();
            }
            
            //imdbid from getUrl
            $f = 'imdbid';
            $fi = $f;
            if( is_string( $data->getUrl() ) 
            ){
                $rtesult[ $fi ] = getIMDB_ID( $data->getUrl() );
            }
            
            //imdb
            $f = 'imdb';
            $fi = $f;
            if( is_string( $data->getUrl() ) 
            ){
                $rtesult[ $fi ] = $data->getUrl();
            }
            
            //tmdbid -- NO EXIST
            $f = 'tmdbid';
            $fi = $f;
            
            //tmdb -- NO EXIST
            $f = 'tmdb';
            $fi = $f;
            
            //tvdbid -- NO EXIST
            $f = 'tvdbid';
            $fi = $f;
            
            //tvdb -- NO EXIST
            $f = 'tvdb';
            $fi = $f;
            
            //runtime getRuntime
            $f = 'runtime';
            $fi = $f;
            if( is_string( $data->getRuntime() ) 
            ){
                $rtesult[ $fi ] = $data->getRuntime();
            }
            //re runtime to ffmpeg
            if( ( !is_numeric( $rtesult[ $fi ] ) 
            || $rtesult[ $fi ] == 0
            )
            && ( $mins = ffmpeg_file_info_lenght_minutes( $basefile ) ) != FALSE
            ){
                $rtesult[ $fi ] = $mins;
            }
            
            //titleepisode -- NO EXIST
            $f = 'chaptertitle';
            $fi = 'titleepisode';
            
            //GENRES getGenre
            $f = 'genres';
            $fi = 'genre';
            if( is_string( $data->getGenre() ) 
            ){
                $rtesult[ $fi ] = $data->getGenre();
            }
            
            //ACTORS getCast
            $f = 'actors'; 
            $fi = 'actor';
            if( is_string( $data->getCast() ) ){
                $da = explode( '/', $data->getCast() );
                if( is_array( $da ) ){
                    foreach( $da AS $actor ){
                        $actor = trim( $actor );
                        if( strlen( $actor ) > 0 ){
                            $rtesult[ $fi ] .= ',' . $actor;
                        }
                    }
                }else{
                    $rtesult[ $fi ] = '';
                }
            }
            
            //AUDIO --NOEXIST
            $f = 'audio';
			
            //SUBS --NOEXIST
            $f = 'subtitle'; 
            
            //IMGs
            //POSTER getPoster
            $f = 'urlposter';
            $fi = 'poster';
            if( is_string( $data->getPoster( 'small', FALSE ) ) ){
                $result[ $fi ] = $data->getPoster();
            }
            $result[ 'data' ] = $rtesult;
            
            return $result;
        } 
    }
        
	function ident_download_phpimdb( $url, $file ){
        $result = FALSE;
        
        //Get Data
        $cmd = O_WGET . ' -O "' . $file . '" "' . $url . '"';
        runExtCommand( $cmd );
        $result = TRUE;
		
		return $result;
	}
	
?>
