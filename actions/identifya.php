<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmedia
	//title
	//scrapper
	//stype
	//season
	//episode
	//imdb
	
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        echo "Invalid ID: idmedia";
        die();
	}
	
	if( array_key_exists( 'title', $G_DATA ) 
	&& strlen( $G_DATA[ 'title' ] ) > 2
	){
        $TITLE = $G_DATA[ 'title' ];
	}else{
        echo "Invalid Search: ";
        die();
	}
	
	if( array_key_exists( 'scrapper', $G_DATA ) 
	&& array_key_exists( $G_DATA[ 'scrapper' ], $G_SCRAPPERS )
	){
        $SCRAPPER = $G_DATA[ 'scrapper' ];
	}else{
        echo "Invalid scrapper: ";
        die();
	}
	
	if( array_key_exists( 'stype', $G_DATA ) 
	&& in_array( $G_DATA[ 'stype' ], $G_STYPE )
	){
        $STYPE = $G_DATA[ 'stype' ];
	}else{
        echo "Invalid Type: ";
        die();
	}
	
	if( array_key_exists( 'season', $G_DATA ) 
	&& (int)$G_DATA[ 'season' ] > 0
	){
        $SEASON = $G_DATA[ 'season' ];
	}else{
        $SEASON = FALSE;
	}
	
	if( array_key_exists( 'episode', $G_DATA ) 
	&& (int)$G_DATA[ 'episode' ] > 0
	){
        $EPISODE = $G_DATA[ 'episode' ];
	}else{
        $EPISODE = FALSE;
	}
	
	if( array_key_exists( 'imdb', $G_DATA )
	&& strlen( $G_DATA[ 'imdb' ] ) > 5
	){
        $IMDB = getIMDB_ID( $G_DATA[ 'imdb' ] );
	}else{
        $IMDB = FALSE;
	}
	
	//ADDING FOR DATA
	
	if( array_key_exists( 1, $G_SCRAPPERS[ $SCRAPPER ] )
	&& function_exists( $G_SCRAPPERS[ $SCRAPPER ][ 1 ] )
	&& ( $media = sqlite_media_getdata( $IDMEDIA ) ) != FALSE 
	&& count( $media ) > 0
	){
        if( $STYPE == 'series' ){
            $stype = FALSE;
        }else{
            $stype = TRUE;
        }
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
        if( ( $info_data = $G_SCRAPPERS[ $SCRAPPER ][ 1 ]( $media[ 0 ][ 'file' ], $TITLE, $stype, $IMDB, $SEASON, $EPISODE ) ) != FALSE 
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
                if( sqlite_mediainfo_update( $info_data[ 'data' ] ) 
                && sqlite_media_update_mediainfo( $IDMEDIA, $idmediainfo )
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
                                    echo get_msg( 'DEF_COPYOK' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $kif;
                                }else{
                                    echo get_msg( 'DEF_COPYKO' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $kif;
                                }
                            }
                        }
                    }
                    echo get_msg( 'IDENT_DETECTEDOK' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $media[ 0 ][ 'file' ];
                }else{
                    echo get_msg( 'IDENT_DETECTEDKO' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $media[ 0 ][ 'file' ];
                }
            }else{
                //NOT finded, update and assign
                echo get_msg( 'DEF_NOTEXIST' );
                if( ( $idmediainfo = sqlite_mediainfo_insert( $info_data[ 'data' ] ) ) != FALSE
                && sqlite_media_update_mediainfo( $IDMEDIA, $idmediainfo )
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
                    echo get_msg( 'IDENT_DETECTEDOK' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $media[ 0 ][ 'file' ];
                }else{
                    echo get_msg( 'IDENT_DETECTEDKO' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $media[ 0 ][ 'file' ];
                }
            }
        }else{
            echo get_msg( 'IDENT_NOTDETECTED' );
        }
    }
?>
