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
	
	//SEARCH EXISTENT AND FORCE
	if( ( $mediainfo = sqlite_mediainfo_check_exist( $TITLE, $SEASON, $EPISODE ) ) != FALSE  ){
        if( is_numeric( $SEASON ) ){
            echo "<br />Serie/Season/Episode Exist: " . $TITLE . ' ' . $SEASON . 'x' . $EPISODE;
        }else{
            echo "<br />Movie Exist: " . $TITLE;
        }
        if( sqlite_media_update_mediainfo( $IDMEDIA, $mediainfo ) ){
            if( is_numeric( $SEASON ) ){
                echo "<br />Serie/Season/Episode Added: " . $TITLE . ' ' . $SEASON . 'x' . $EPISODE;
            }else{
                echo "<br />Movie Added: " . $TITLE;
            }
        }else{
            echo "<br />Error adding Movie/Serie/Season/Episode Added: " . $TITLE . ' ' . $SEASON . 'x' . $EPISODE;
        }
	}elseif( (
        ( isset( $mediainfo ) && is_numeric( $mediainfo ) )
        || ( $mediainfo = sqlite_mediainfo_check_exist( $TITLE ) ) != FALSE 
        )
	&& ( $mediainfo = sqlite_mediainfo_getdata( $mediainfo, 1 ) ) != FALSE
	&& is_array( $mediainfo )
	&& count( $mediainfo ) > 0
	&& array_key_exists( 0, $mediainfo )
	&& is_array( $mediainfo[ 0 ] )
	&& count( $mediainfo[ 0 ] ) > 0
	&& array_key_exists( 'title', $mediainfo[ 0 ] )
	){
        $mediainfo[ 0 ][ 'idmediainfo' ] = 'NULL';
        $mediainfo[ 0 ][ 'dateadded' ] = date( 'Y-m-d H:i:s' );
        $mediainfo[ 0 ][ 'season' ] = $SEASON;
        $mediainfo[ 0 ][ 'episode' ] = $EPISODE;
        $mediainfo[ 0 ][ 'sorttitle' ] = '';
        if( strlen( $IMDB ) > 0 
        && !getIMDB_ID( $IMDB )
        ){
            $mediainfo[ 0 ][ 'titleepisode' ] = $IMDB;
        }
        if( ( $idmi = sqlite_mediainfo_insert( $mediainfo[ 0 ] ) ) != FALSE 
        && sqlite_media_update_mediainfo( $IDMEDIA, $idmi ) != FALSE
        ){
            if( is_numeric( $SEASON ) ){
                echo "<br />Serie/Season/Episode Added: " . $TITLE . ' ' . $SEASON . 'x' . $EPISODE;
            }else{
                echo "<br />Movie Added: " . $TITLE;
            }
        }else{
            echo "<br />Error adding Serie/Season/Episode Added: " . $TITLE . ' ' . $SEASON . 'x' . $EPISODE;
        }
	}else{
        echo "<br /> Title not exist: " . $TITLE;
	}
	
?>
