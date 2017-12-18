<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmedia
	//title
	//plot
	//sorttitle
	//season
	//episode
	//year
	//genre
	//actor
	
	if( array_key_exists( 'idmedia', $G_DATA ) 
	&& ( $MEDIADATA = sqlite_media_getdata( $G_DATA[ 'idmedia' ] ) ) != FALSE
	&& count( $MEDIADATA ) > 0
	&& file_exists( $MEDIADATA[ 0 ][ 'file' ] )
	){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
        $MEDIADATA = $MEDIADATA[ 0 ];
	}else{
        echo "Invalid ID: idmedia";
        die();
	}
	
	if( array_key_exists( 'title', $G_DATA ) 
	&& strlen( $G_DATA[ 'title' ] ) > 2
	){
        $TITLE = $G_DATA[ 'title' ];
	}else{
        echo "Invalid Title: ";
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
	
	//SEARCH EXISTENT AND FORCE
	if( ( $mediainfo = sqlite_mediainfo_check_exist( $TITLE, $SEASON, $EPISODE ) ) != FALSE  ){
        if( is_numeric( $SEASON ) ){
            echo "<br />Serie/Season/Episode Exist: " . $TITLE . ' ' . $SEASON . 'x' . $EPISODE;
        }else{
            echo "<br />Movie Exist: " . $TITLE;
        }
	}else{
        $midata = array();
        foreach( $G_MEDIAINFO AS $k => $v ){
            if( array_key_exists( $k, $G_DATA ) 
            && strlen( $G_DATA[ $k ] ) > 0
            ){
                $midata[ $k ] = $G_DATA[ $k ];
            }else{
                $midata[ $k ] = '';
            }
        }
        $midata[ 'idmediainfo' ] = 'NULL';
        $midata[ 'dateadded' ] = date( 'Y-m-d H:i:s' );
        $midata[ 'runtime' ] = ffmpeg_file_info_lenght_minutes( $MEDIADATA[ 'file' ] );
        if( ( $mi_new = sqlite_mediainfo_insert( $midata ) ) != FALSE 
        && $mi_new > 0
        ){
            if( is_numeric( $SEASON ) ){
                echo "<br />Serie/Season/Episode added: " . $TITLE . ' ' . $SEASON . 'x' . $EPISODE;
            }else{
                echo "<br />Movie added: " . $TITLE;
            }
            if( sqlite_media_update_mediainfo( $IDMEDIA, $mi_new ) ){
                echo "<br />File updated: " . basename( $MEDIADATA[ 'file' ] );
            }
        }else{
            echo "<br />Error insert data: " . $mi_new;
        }
	}
	
?>
