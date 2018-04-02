<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	$FILEBOT_MOVIES = 'TheMovieDB';
	$FILEBOT_SERIES = 'TheTVDB';
	$FILEBOT_MOVIES_ART = 'tmdb';
	$FILEBOT_SERIES_ART = 'tvdb';
	//ADD SCRAPPER
	$G_SCRAPPERS[ 'filebot' ] = array( '', 'ident_detect_file_filebot' );
	$G_SCRAPPERS_KEY[ 'filebot' ] = '';
	
	//BASE
	function ident_detect_file_filebot( $file, $title, $movies = TRUE, $imdb = FALSE, $season = FALSE, $episode = FALSE ){
        //Create temp folder
        //create hardlink to temp folder with file and title
        //autodetect filebot
        //wait for changes
        //recolect NEEDED DATA
        // array( poster, fanart, logo, poster, banner, landscape, data => $G_MEDIAINFO )
        global $FILEBOT_MOVIES;
        global $FILEBOT_SERIES;
        global $G_MEDIADATA;
        $result = $G_MEDIADATA;
        
        $TMP_FOLDER = PPATH_TEMP . DS . getRandomString( 8 );
        $fext = get_file_extension( $file );
        if( $season !== FALSE 
        && get_media_chapter( $title ) == FALSE
        ){
            $title .= ' ' . $season . 'x' . $episode;
        }
        $filet = $TMP_FOLDER . DS . $title . '.' . $fext;
        //echo "<br />" . $file . ' => ' . $filet;die();
        if( file_exists( $file )
        && mkdir( $TMP_FOLDER )
        && ( @link( $file, $filet ) || @symlink( $file, $filet ) )
        ){
            if( ( $fb_r = ident_detect_filebot( $filet, $movies ) ) != FALSE ){
                $folder = $TMP_FOLDER;
                //Check folder have data
                if( ( $files = getFiles( $folder ) ) != FALSE 
                && count( $files ) > 1
                ){
                    $data_nfo = FALSE;
                    //Get NFO Data To Add
                    foreach( $files AS $f ){
                        if( endsWith( $f, '.nfo' ) ){
                            if( ( $data_nfo = ident_filebot_read_nfo( $f, $file ) ) != FALSE ){
                                $result[ 'data' ] = $data_nfo;
                            }
                        }
                    }
                    //EXTRA SEASON && EPISODE FROM FILENAME SSxCC
                    foreach( $files AS $f ){
                        if( $filet != $f
                        && getFileMimeTypeVideo( $f ) 
                        && ( $d = get_media_chapter( basename( $f ) ) ) != FALSE
                        ){
                            $result[ 'data' ][ 'season' ] = (int)$d[ 0 ];
                            $result[ 'data' ][ 'episode' ] = (int)$d[ 1 ];
                        }
                    }
                    
                    //if have nfo data
                    if( is_array( $data_nfo ) 
                    && array_key_exists( 'title', $data_nfo )
                    && strlen( $data_nfo[ 'title' ] ) > 0
                    ){
                        //Get Images
                        foreach( $files AS $f ){
                            foreach( $result AS $k => $v ){
                                if( $k != 'data'
                                && is_file( $f )
                                && startsWith( basename( $f ), $k )
                                && getFileMimeTypeImg( $f )
                                ){
                                    $result[ $k ] = $f;
                                }
                            }
                        }
                    }else{
                        $result = FALSE;
                    }
                }
            }
        }
        
        //delete temp after copy imgs
        //delTree( $TMP_FOLDER );
        return $result;
	}
	
	//$type = TheMovieDB, TheTVDB
	function ident_detect_filebot( $folder, $type = TRUE ){
        global $FILEBOT_MOVIES_ART;
        global $FILEBOT_SERIES_ART;
        global $FILEBOT_MOVIES;
        global $FILEBOT_SERIES;
        $result = FALSE;
        $cmd2 = FALSE;
        if( is_file( $folder ) ){
            $folder = dirname( $folder );
        }
        if( $type ){
            $mediatype = $FILEBOT_MOVIES_ART;
            $force_type = ' --def "ut_label=movie" ';
            $db = $FILEBOT_MOVIES;
            $movie_filter = '{n}/{n}';
            $tv_filter = '';
        }else{
            $mediatype = $FILEBOT_SERIES_ART;
            $force_type = ' --def "ut_label=tv" ';
            $db = $FILEBOT_SERIES;
            $movie_filter = '';
            $tv_filter = '{n}/{n} - {sxe} - {t}';
        }
        
        //CLEAN XATTR
        $cmd = O_FILEBOT . ' -script fn:xattr --action clear "' . $folder . '"';
        runExtCommand( $cmd );
        
        //AMC SCRIPT
        $cmd = O_FILEBOT . ' -script fn:amc --encoding UTF-8 -non-strict "' . $folder . '" --db ' . $db . ' --action move --lang ' . O_LANG . ' --conflict auto ' . $force_type . ' --def clean=y --def artwork=y --def movieFormat="' . $movie_filter . '" --def seriesFormat="' . $tv_filter . '" --def animeFormat="' . $tv_filter . '" musicFormat=""';
        
        $result = '';
		
		$result = runExtCommand( $cmd );
		
		if( $cmd2 != FALSE ){
            $result .= '<br />' . runExtCommand( $cmd2 );
		}
		
		return $result;
	}
	
	//NFO INFO
	
	function ident_filebot_read_nfo( $file, $basefile = FALSE ) { 
		global $G_MEDIAINFO;
		$rtesult = $G_MEDIAINFO;
		
		if( ( $xml = simplexml_load_string( file_get_contents( $file ) ) ) != FALSE
		&& ( $xml = object2array( $xml ) ) != FALSE
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
            $f = 'sorttitle';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $added = FALSE;
                if( ( $d = explode( '::', $xml[ $f ] ) ) != FALSE
                ){
                    foreach( $d AS $dat ){
                        $dat = trim( $dat );
                        if( strtotime( $dat ) != FALSE ){
                            $rtesult[ $fi ] = $dat;
                            $added = TRUE;
                        }
                    }
                }
                if( !$added ){
                    if( array_key_exists( 'year', $xml ) 
                    && is_string( $xml[ 'year' ] )
                    ){
                        $rtesult[ $fi ] = $xml[ 'year' ] . '00-00';
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
            $f = 'episode';
            $fi = $f;
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
            $f = 'mpaa';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //tagline
            $f = 'tagline';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //runtime
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
            //file info stream details HEIGHT WIDTH CODEC
            if( array_key_exists( 'fileinfo', $xml )
            && array_key_exists( 'streamdetails', $xml[ 'fileinfo' ] ) 
            && array_key_exists( 'video', $xml[ 'fileinfo' ][ 'streamdetails' ] ) 
            ){
                $f = 'height';
                if( array_key_exists( $f, $xml[ 'fileinfo' ][ 'streamdetails' ][ 'video' ] )  ){
                    $rtesult[ $f ] = $xml[ 'fileinfo' ][ 'streamdetails' ][ 'video' ][ $f ];
                }
                $f = 'width';
                if( array_key_exists( $f, $xml[ 'fileinfo' ][ 'streamdetails' ][ 'video' ] )  ){
                    $rtesult[ $f ] = $xml[ 'fileinfo' ][ 'streamdetails' ][ 'video' ][ $f ];
                }
                $f = 'codec';
                if( array_key_exists( $f, $xml[ 'fileinfo' ][ 'streamdetails' ][ 'video' ] )  ){
                    $rtesult[ $f ] = $xml[ 'fileinfo' ][ 'streamdetails' ][ 'video' ][ $f ];
                }
            }
            //imdbid
            $f = 'imdbid';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //imdb
            $f = 'imdb';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //tmdbid
            $f = 'tmdbid';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //tmdb
            $f = 'tmdb';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //tvdbid
            $f = 'tvdbid';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //tvdb
            $f = 'tvdb';
            $fi = $f;
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            //runtime
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
            $f = 'titleepisode'; 
            $fi = strtolower( $f );
            if( array_key_exists( $f, $xml ) 
            && is_string( $xml[ $f ] ) 
            ){
                $rtesult[ $fi ] = $xml[ $f ];
            }
            
            //GENERES
            $f = 'genre'; 
            $fi = $f;
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
            $f = 'actor'; 
            $fi = $f;
            if( array_key_exists( $f, $xml ) ){
                if( is_array( $xml[ $f ] ) ){
                    foreach( $xml[ $f ] AS $actor ){
                        $f2 = 'name'; 
                        if( is_array( $actor ) 
                        && array_key_exists( $f2, $actor ) 
                        ){
                            if( strlen( $rtesult[ $fi ] ) > 0 ){
                                $rtesult[ $fi ] .= ',';
                            }
                            $rtesult[ $fi ] .= $actor[ $f2 ];
                        }
                    }
                }else{
                    $rtesult[ $fi ] = $xml[ $f ];
                }
            }
            
            //AUDIO
            $f = 'audio'; 
			if( array_key_exists( 'fileinfo', $xml ) 
			&& array_key_exists( 'streamdetails', $xml[ 'fileinfo' ] ) 
			&& array_key_exists( 'audio', $xml[ 'fileinfo' ][ 'streamdetails' ] ) 
			&& count( $xml[ 'fileinfo' ][ 'streamdetails' ][ 'audio' ] ) > 1
			){
				$data = $xml[ 'fileinfo' ][ 'streamdetails' ][ 'audio' ];
				foreach( $data AS $d ){
					if( is_array( $d )
					&& array_key_exists( 'language', $d ) 
					&& is_string( $d[ 'language' ] )
					&& strlen( $d[ 'language' ] ) > 0
					){
                        $lang = $d[ 'language' ];
					}elseif( is_array( $d )
					&& array_key_exists( 'language', $d )
					&& is_array( $d[ 'language' ] )
					&& count( $d[ 'language' ] ) > 0
					){
                        foreach( $d[ 'language' ] AS $k => $v ){
                            $lang .= $v . ', ';
                        }
					}else{
                        $lang = get_msg( 'DEF_NOTSPECIFIED', FALSE );
					}
					if( strlen( $rtesult[ $f ] ) > 0 ){
                        $rtesult[ $f ] .= ',';
                    }
                    $rtesult[ $f ] .= $lang;
				}
			}
			
            //SUBS
            $f = 'subtitle'; 
			if( array_key_exists( 'fileinfo', $xml ) 
			&& array_key_exists( 'streamdetails', $xml[ 'fileinfo' ] ) 
			&& array_key_exists( 'subtitle', $xml[ 'fileinfo' ][ 'streamdetails' ] ) 
			&& count( $xml[ 'fileinfo' ][ 'streamdetails' ][ 'subtitle' ] ) > 1
			){
				$data = $xml[ 'fileinfo' ][ 'streamdetails' ][ 'subtitle' ];
				foreach( $data AS $d ){
					if( is_array( $d )
					&& array_key_exists( 'language', $d )
					&& is_string( $d[ 'language' ] )
					&& strlen( $d[ 'language' ] ) > 0
					){
                        $lang = $d[ 'language' ];
					}elseif( is_array( $d )
					&& array_key_exists( 'language', $d )
					&& is_array( $d[ 'language' ] )
					&& count( $d[ 'language' ] ) > 0
					){
                        foreach( $d[ 'language' ] AS $k => $v ){
                            $lang .= $v . ', ';
                        }
					}else{
                        $lang = get_msg( 'DEF_NOTSPECIFIED', FALSE );
					}
					if( strlen( $rtesult[ $f ] ) > 0 ){
                        $rtesult[ $f ] .= ',';
                    }
                    $rtesult[ $f ] .= $lang;
				}
			}
		}
		
		return $rtesult;
	} 
	
	function filebot_get_list_episodes( $title ){
        $result = FALSE;
        $cmd = O_FILEBOT . ' -list --db thetvdb --q "' . $title . '"';
        
        if( ( $r = runExtCommandNoRedirect( $cmd ) ) != FALSE 
        ){
            if( is_array( $r ) ){
                $result = $r;
            }else{
                $result = preg_split ('/$\R?^/m', $r );
            }
        }
        
        return $result;
	}
?>
