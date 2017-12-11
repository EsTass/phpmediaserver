<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//folder
	//quantity
	
	if( array_key_exists( 'quantity', $G_DATA ) 
	&& (int)$G_DATA[ 'quantity' ] > 0
	){
        $QUANTITY = (int)$G_DATA[ 'quantity' ];
	}else{
        $QUANTITY = 10;
	}
	
	//FileBot Format Import Folder
	if( function_exists( 'ident_filebot_read_nfo' )
	&& array_key_exists( 'folder', $G_DATA ) 
	&& ( $DFOLDER = $G_DATA[ 'folder' ] ) != FALSE
	&& file_exists( $DFOLDER )
	&& ( $folders = getFolders( $DFOLDER ) ) != FALSE
	&& count( $folders ) > 0
	){
        foreach( $folders AS $f ){
            echo get_msg( 'MENU_IMPORT', FALSE ) . " " . $f;
            $folder = $f;
            //Check folder have data
            if( ( $files = getFiles( $folder ) ) != FALSE 
            && count( $files ) > 1
            ){
                $GMI = $G_MEDIADATA;
                $data_nfo = FALSE;
                $file = '';
                //GET FILE
                $file_lists = array();
                foreach( $files AS $f ){
                    if( getFileMimeTypeVideo( $f ) 
                    ){
                        $file = $f;
                        $file_lists[] = $f;
                    }
                }
                //Get NFO Data To Add
                foreach( $files AS $f ){
                    if( endsWith( $f, 'tvshow.nfo' ) 
                    || endsWith( $f, 'movie.nfo' ) 
                    ){
                        if( ( $data_nfo = ident_filebot_read_nfo( $f, $file ) ) != FALSE ){
                            $GMI[ 'data' ] = $data_nfo;
                        }
                    }
                }
                //if have nfo data
                if( is_array( $data_nfo ) 
                && array_key_exists( 'title', $data_nfo )
                && strlen( $data_nfo[ 'title' ] ) > 0
                ){
                    //Get Images
                    foreach( $files AS $f ){
                        foreach( $GMI AS $k => $v ){
                            if( $k != 'data'
                            && is_file( $f )
                            && startsWith( basename( $f ), $k )
                            && getFileMimeTypeImg( $f )
                            ){
                                $GMI[ $k ] = $f;
                            }
                        }
                    }
                }else{
                    $GMI = FALSE;
                }
                
                //WITH DATA TRY TO ADD
                if( $GMI != FALSE
                && array_key_exists( 'data', $GMI )
                && array_key_exists( 'title', $GMI[ 'data' ] )
                && array_key_exists( 'year', $GMI[ 'data' ] )
                && strlen( $GMI[ 'data' ][ 'title' ] ) > 0
                ){
                    echo get_msg( 'IDENT_DETECTED' ) . $GMI[ 'data' ][ 'title' ] . ' ' . $GMI[ 'data' ][ 'year' ];
                    foreach( $file_lists AS $file ){
                        //QUANTITY
                        if( sqlite_media_check_exist( $file ) == FALSE ){
                            echo "<br />Q:" . $QUANTITY;
                            if( $QUANTITY <= 0 ){
                                break;
                            }
                            $QUANTITY--;
                        }
                        echo '<br />' . get_msg( 'IDENT_FILETODETECTED' ) . $file;
                        //EXTRA SEASON && EPISODE FROM FILENAME SSxCC
                        if( ( $d = get_media_chapter( basename( $file ) ) ) != FALSE
                        ){
                            $GMI[ 'data' ][ 'season' ] = (int)$d[ 0 ];
                            $GMI[ 'data' ][ 'episode' ] = (int)$d[ 1 ];
                        }
                        //Recheck Data For FILENAME.nfo and get plot and titleepisode
                        $GMI_2 = $GMI[ 'data' ];
                        $file_nfo_extra = dirname( $file ) . DS . pathinfo( $file, PATHINFO_FILENAME ) . '.nfo';
                        if( file_exists( $file_nfo_extra ) 
                        && ( $GMI_E = ident_filebot_read_nfo( $file_nfo_extra, $file ) ) != FALSE
                        && array_key_exists( 'title', $GMI_E )
                        ){
                            $GMI[ 'data' ][ 'titleepisode' ] = $GMI_E[ 'title' ];
                            if( array_key_exists( 'plot', $GMI_E ) ){
                                $GMI[ 'data' ][ 'plot' ] = $GMI_E[ 'plot' ];
                            }
                        }
                        $IDMEDIA = FALSE;
                        //Insert idmedia and get idmedia
                        if( ( $IDMEDIA =  sqlite_media_check_exist( $file ) ) != FALSE
                        || ( $IDMEDIA = sqlite_media_insert( $file ) ) != FALSE
                        ){
                            //check duplicates in idmediainfo
                            if( ( $idmediainfo = sqlite_mediainfo_check_exist( $GMI[ 'data' ][ 'title' ], $GMI[ 'data' ][ 'season' ], $GMI[ 'data' ][ 'episode' ] ) ) != FALSE 
                            ){
                                //finded, update and assign
                                echo get_msg( 'DEF_EXIST' );
                                $GMI[ 'data' ][ 'idmediainfo' ] = $idmediainfo;
                                if( sqlite_mediainfo_update( $GMI[ 'data' ] ) 
                                && sqlite_media_update_mediainfo( $IDMEDIA, $idmediainfo )
                                ){
                                    //Copy needed files
                                    foreach( $GMI AS $kif => $vif ){
                                        if( $kif != 'data' 
                                        && file_exists( $vif )
                                        && getFileMimeTypeImg( $vif )
                                        ){
                                            $numtimes = 3;
                                            if( O_MAX_IMG_FILES > 0 )
                                            while( filesize( $vif ) > O_MAX_IMG_FILES 
                                            && filesize( $vif ) > 0
                                            && $numtimes > 0
                                            ){
                                                if( !resize_img_div2( $vif ) 
                                                ){
                                                    break;
                                                }
                                                $numtimes--;
                                            }
                                            //copy file to format idmediainfo.poster|landscape|...
                                            $imgfile = PPATH_MEDIAINFO . DS . $idmediainfo . '.' . $kif;
                                            if( file_exists( $imgfile ) ){
                                                @unlink( $imgfile );
                                            }
                                            if( link( $vif, $imgfile ) ){
                                                echo get_msg( 'DEF_COPYOK' ) . ' ' . $GMI[ 'data' ][ 'title' ] . ' => ' . $kif;
                                            }else{
                                                echo get_msg( 'DEF_COPYKO' ) . ' ' . $GMI[ 'data' ][ 'title' ] . ' => ' . $kif;
                                            }
                                        }
                                    }
                                    echo get_msg( 'IDENT_DETECTEDOK' ) . ' ' . $GMI[ 'data' ][ 'title' ] . ' => ' . $file;
                                }else{
                                    echo get_msg( 'IDENT_DETECTEDKO' ) . ' ' . $GMI[ 'data' ][ 'title' ] . ' => ' . $file;
                                }
                            }else{
                                //NOT finded, update and assign
                                echo get_msg( 'DEF_NOTEXIST' );
                                if( ( $idmediainfo = sqlite_mediainfo_insert( $GMI[ 'data' ] ) ) != FALSE
                                //&& ( $idmediainfo = sqlite_lastid() ) != FALSE
                                && sqlite_media_update_mediainfo( $IDMEDIA, $idmediainfo )
                                ){
                                    //Copy needed files
                                    foreach( $GMI AS $kif => $vif ){
                                        if( $kif != 'data' 
                                        && file_exists( $vif )
                                        && getFileMimeTypeImg( $vif )
                                        ){
                                            $numtimes = 3;
                                            if( O_MAX_IMG_FILES > 0 )
                                            while( filesize( $vif ) > O_MAX_IMG_FILES 
                                            && filesize( $vif ) > 0
                                            && $numtimes > 0
                                            ){
                                                if( !resize_img_div2( $vif ) 
                                                ){
                                                    break;
                                                }
                                                $numtimes--;
                                            }
                                            //copy file to format idmediainfo.poster|landscape|...
                                            $imgfile = PPATH_MEDIAINFO . DS . $idmediainfo . '.' . $kif;
                                            if( file_exists( $imgfile ) ){
                                                @unlink( $imgfile );
                                            }
                                            if( link( $vif, $imgfile ) ){
                                                echo get_msg( 'DEF_COPYOK' ) . ' ' . $GMI[ 'data' ][ 'title' ] . ' => ' . $kif;
                                            }else{
                                                echo get_msg( 'DEF_COPYKO' ) . ' ' . $GMI[ 'data' ][ 'title' ] . ' => ' . $kif;
                                            }
                                        }
                                    }
                                    echo get_msg( 'IDENT_DETECTEDOK' ) . ' ' . $GMI[ 'data' ][ 'title' ] . ' => ' . $file;
                                }else{
                                    echo get_msg( 'IDENT_DETECTEDKO' ) . ' ' . $GMI[ 'data' ][ 'title' ] . ' => ' . $file;
                                }
                            }
                            $GMI[ 'data' ] = $GMI_2;
                        }else{
                            echo get_msg( 'IDENT_DETECTEDKO' ) . $IDMEDIA;
                        }
                    }
                }else{
                    echo get_msg( 'IDENT_DETECTEDKO', FALSE );
                }
            }
            echo '<br />';
            if( $QUANTITY <= 0 ){
                break;
            }
        }
	}else{
        echo get_msg( 'DEF_NOTEXIST' );
	}
	
?>
