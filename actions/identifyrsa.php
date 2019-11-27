<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmedia
	//filename
	//atitle
	//preview
	//season
	//episode
	
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        echo "Invalid ID: idmedia";
        die();
	}
	
	if( array_key_exists( 'atitle', $G_DATA ) 
	&& strlen( $G_DATA[ 'atitle' ] ) > 2
	){
        $TITLE = $G_DATA[ 'atitle' ];
	}else{
        echo "Invalid Search: ";
        die();
	}
	
	if( array_key_exists( 'filename', $G_DATA ) 
	&& strlen( $G_DATA[ 'filename' ] ) > 2
	){
        $FILENAME = $G_DATA[ 'filename' ];
	}else{
        echo "Invalid filename: ";
        die();
	}
	
	if( array_key_exists( 'preview', $G_DATA ) 
	&& $G_DATA[ 'preview' ] == '0'
	){
        $PREVIEW = FALSE;
	}else{
        $PREVIEW = TRUE;
	}
	
	if( array_key_exists( 'season', $G_DATA ) 
	&& strlen( $G_DATA[ 'season' ] ) > 2
	){
        $BSEASON = $G_DATA[ 'season' ];
	}else{
        $BSEASON = FALSE;
	}
	
	if( array_key_exists( 'episode', $G_DATA ) 
	&& strlen( $G_DATA[ 'episode' ] ) > 2
	){
        $BEPISODE = $G_DATA[ 'episode' ];
	}else{
        $BEPISODE = FALSE;
	}
	
	if( array_key_exists( 'imdb', $G_DATA )
	&& strlen( $G_DATA[ 'imdb' ] ) > 5
	){
        //$IMDB = getIMDB_ID( $G_DATA[ 'imdb' ] );
        $IMDB = $G_DATA[ 'imdb' ];
	}else{
        $IMDB = FALSE;
	}
	
	//HELPERS
	function get_numerics( $str, $size = 1 ){
        $result = array();
        if( $size > 0 ){
            $re = '/\d{' . $size . ',' . $size . '}/';
            //$re = '/\d{1,' . $size . '}/';
        }else{
            //on 0 is XX0
            $re = '/\d{1,3}/';
        }
        
        //var_dump( $re );
        //preg_match_all( $re, $str, $matches, PREG_SET_ORDER, 0 );
        preg_match_all( $re, $str, $matches, PREG_PATTERN_ORDER, 0 );
        if( is_array( $matches ) 
        && array_key_exists( 0, $matches )
        ){
            $result = $matches[ 0 ];
        }
        return $result;
    }
	
	//Check filenames
	//get bad idents, not idents and last elements added
	if( ( $edata = sqlite_media_getdata_identify_orderer( $FILENAME ) ) ){
        echo '<br /><br />Files: ' . count( $edata );
        foreach( $edata AS $mdata ){
            if( $mdata[ 'idmediainfo' ] > 0 ){
                //echo '<br />Skip: ' . basename( $mdata[ 'file' ] );
                //echo get_msg( 'IDENT_DETECTED' ) . $mdata[ 'title' ] . ' ' . $mdata[ 'year' ];
            }else{
                echo '<br />File: ' . basename( $mdata[ 'file' ] );
                //Extract season if needed
                if( $BSEASON != FALSE ){
                    $pos = FALSE;
                    //SFixedX
                    if( startsWith( $BSEASON, 'SFixed' ) ){
                        //Fixed number
                        $SEASON = (int)filter_var( $BSEASON, FILTER_SANITIZE_NUMBER_INT );
                    }
                    //firstnumberX
                    elseif( startsWith( $BSEASON, 'firstnumber' ) ){
                        //Number in string with quantity X (0, 00, 000)
                        $pos = 0;
                    }
                    //secondnumberX
                    elseif( startsWith( $BSEASON, 'secondnumber' ) ){
                        //Number in string with quantity X (0, 00, 000)
                        $pos = 1;
                    }
                    //thirdlynumberX
                    elseif( startsWith( $BSEASON, 'thirdlynumber' ) ){
                        //Number in string with quantity X (0, 00, 000)
                        $pos = 2;
                    }
                    //by pos action
                    if( $pos !== FALSE ){
                        //Number in string with quantity X (0, 00, 000)
                        //$pos = 0;
                        $q = (int)filter_var( $BSEASON, FILTER_SANITIZE_NUMBER_INT );
                        if( $q > -1
                        && ( $nl = get_numerics( basename( $mdata[ 'file' ] ), $q ) ) != FALSE
                        && is_array( $nl )
                        && array_key_exists( $pos, $nl )
                        && $nl[ $pos ] > 0
                        ){
                            //Number finded
                            $SEASON = $nl[ $pos ];
                            echo "<br />Numbers Season: " . print_r( $nl, TRUE );
                            echo "<br />Season: " . $SEASON;
                        }else{
                            echo get_msg( 'IDENT_DETECTEDKO' ) . ': SEASON NOT FOUND!';
                            var_dump( $nl );
                            break;
                        }
                    }
                }else{
                    $SEASON = FALSE;
                }
                //Extract episode if needed
                if( $BEPISODE != FALSE ){
                    $pos = FALSE;
                    //SFixedX
                    if( startsWith( $BEPISODE, 'SFixed' ) ){
                        //Fixed number
                        $EPISODE = (int)filter_var( $BEPISODE, FILTER_SANITIZE_NUMBER_INT );
                    }
                    //firstnumberX
                    elseif( startsWith( $BEPISODE, 'firstnumber' ) ){
                        //Number in string with quantity X (0, 00, 000)
                        $pos = 0;
                    }
                    //secondnumberX
                    elseif( startsWith( $BEPISODE, 'secondnumber' ) ){
                        //Number in string with quantity X (0, 00, 000)
                        $pos = 1;
                    }
                    //thirdlynumberX
                    elseif( startsWith( $BEPISODE, 'thirdlynumber' ) ){
                        //Number in string with quantity X (0, 00, 000)
                        $pos = 2;
                    }
                    //by pos action
                    if( $pos !== FALSE ){
                        //Number in string with quantity X (0, 00, 000)
                        //$pos = 0;
                        $q = (int)filter_var( $BEPISODE, FILTER_SANITIZE_NUMBER_INT );
                        if( $q > -1
                        && ( $nl = get_numerics( basename( $mdata[ 'file' ] ), $q ) ) != FALSE
                        && is_array( $nl )
                        && array_key_exists( $pos, $nl )
                        && $nl[ $pos ] > 0
                        ){
                            //Number finded
                            $EPISODE = $nl[ $pos ];
                            echo "<br />Numbers Episodes: " . print_r( $nl, TRUE );
                            echo "<br />Episode: " . $EPISODE;
                        }else{
                            echo get_msg( 'IDENT_DETECTEDKO' ) . ': EPISODE NOT FOUND!';
                            var_dump( $nl );
                            break;
                        }
                    }
                }else{
                    $EPISODE = FALSE;
                }
                //Set type based on params
                if( $BSEASON != FALSE ){
                    $stype = FALSE;
                    $STYPE = 'SERIE';
                }else{
                    $stype = TRUE;
                    $STYPE = 'MOVIE';
                }
                //Try to ident with mydb
                //GET ALL DATA NEEDED TO COPY
                // Media Files:
                // - fanart.*
                // - folder.*
                // - logo.*
                // - poster.*
                // - banner.*
                // - landscape.*
                // Data
                // - data => $G_MEDIAINFO 
                // array( poster, fanart, logo, poster, banner, landscape, data => $G_MEDIAINFO )
                if( ( $info_data = ident_detect_file_db( $mdata[ 'file' ], $TITLE, $stype, $IMDB, $SEASON, $EPISODE ) ) != FALSE 
                && array_key_exists( 'data', $info_data )
                && array_key_exists( 'title', $info_data[ 'data' ] )
                && array_key_exists( 'year', $info_data[ 'data' ] )
                && strlen( $info_data[ 'data' ][ 'title' ] ) > 0
                ){
                    echo get_msg( 'IDENT_DETECTED' ) . $info_data[ 'data' ][ 'title' ] . ' ' . $info_data[ 'data' ][ 'year' ];
                    echo ' ' . $STYPE;
                    //check duplicates in idmediainfo
                    if( ( $idmediainfo = sqlite_mediainfo_check_exist( $info_data[ 'data' ][ 'title' ], $info_data[ 'data' ][ 'season' ], $info_data[ 'data' ][ 'episode' ] ) ) != FALSE 
                    ){
                        //finded, update and assign
                        echo get_msg( 'DEF_EXIST' );
                        $info_data[ 'data' ][ 'idmediainfo' ] = $idmediainfo;
                        if( $PREVIEW ){
                            //PREVIEW MODE
                            echo get_msg( 'IDENT_DETECTEDOK' ) . ' - PREVIEW UPDATE - ' . basename( $mdata[ 'file' ] );
                            echo get_msg( 'IDENT_DETECTEDOK' ) . ' - PREVIEW UPDATE - ' . $info_data[ 'data' ][ 'title' ] . ' ' . $info_data[ 'data' ][ 'year' ] . ' ' . $info_data[ 'data' ][ 'season' 
] . 'x' . $info_data[ 'data' ][ 'episode' ];
                        }elseif( sqlite_mediainfo_update( $info_data[ 'data' ] ) 
                        && sqlite_media_update_mediainfo( $mdata[ 'idmedia' ], $idmediainfo )
                        ){
                            //Copy needed files
                            foreach( $info_data AS $kif => $vif ){
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
                                    //check same
                                    if( $imgfile != $vif ){
                                        if( file_exists( $imgfile ) ){
                                            @unlink( $imgfile );
                                        }
                                        if( @link( $vif, $imgfile ) 
                                        || @copy( $vif, $imgfile )
                                        ){
                                            //echo get_msg( 'DEF_COPYOK' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $kif;
                                        }else{
                                            //echo get_msg( 'DEF_COPYKO' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $kif;
                                        }
                                    }
                                }
                            }
                            echo get_msg( 'IDENT_DETECTEDOK' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $mdata[ 'file' ];
                        }else{
                            echo get_msg( 'IDENT_DETECTEDKO' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $mdata[ 'file' ];
                        }
                    }else{
                        //NOT finded, update and assign
                        echo get_msg( 'DEF_NOTEXIST' );
                        if( $PREVIEW ){
                            //PREVIEW MODE
                            echo get_msg( 'IDENT_DETECTEDOK' ) . ' - PREVIEW CREATE - ' . basename( $mdata[ 'file' ] );
                            echo get_msg( 'IDENT_DETECTEDOK' ) . ' - PREVIEW CREATE - ' . $info_data[ 'data' ][ 'title' ] . ' ' . $info_data[ 'data' ][ 'year' ] . ' ' . $info_data[ 'data' ][ 'season' 
] . 'x' . $info_data[ 'data' ][ 'episode' ];
                        }elseif( ( $idmediainfo = sqlite_mediainfo_insert( $info_data[ 'data' ] ) ) != FALSE
                        && sqlite_media_update_mediainfo( $mdata[ 'idmedia' ], $idmediainfo )
                        ){
                            //Copy needed files
                            foreach( $info_data AS $kif => $vif ){
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
                                    if( @link( $vif, $imgfile ) 
                                    || @copy( $vif, $imgfile )
                                    ){
                                        echo get_msg( 'DEF_COPYOK' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $kif;
                                    }else{
                                        echo get_msg( 'DEF_COPYKO' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $kif;
                                    }
                                }
                            }
                            echo get_msg( 'IDENT_DETECTEDOK' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $mdata[ 'file' ];
                        }else{
                            echo get_msg( 'IDENT_DETECTEDKO' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $mdata[ 'file' ];
                        }
                    }
                }else{
                    echo get_msg( 'IDENT_NOTDETECTED' );
                }
            }
        }
	}else{
        echo get_msg( 'DEF_EMPTYLIST' );
	}
	
?>
