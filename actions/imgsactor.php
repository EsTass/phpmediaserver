<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//actor
	
	if( array_key_exists( 'actor', $G_DATA ) ){
        $ACTORNAME = urldecode( $G_DATA[ 'actor' ] );
	}else{
        $ACTORNAME = '';
	}
	
	$FMEDIA = PPATH_MEDIAINFO . DS . $ACTORNAME;
	if( ( $FMEDIA = getActorFile( $ACTORNAME ) ) != FALSE ){
        //$type = 'image/jpeg';
        $type = getFileMimeType( $FMEDIA );
        header('Content-Type:' . $type);
        header( 'Content-Length: ' . filesize( $FMEDIA ) );
        readfile( $FMEDIA );
        exit( 0 );
    }else{
        //$type = 'image/jpeg';
        $FMEDIA = PPATH_IMGS . DS . 'u.png';
        $type = getFileMimeType( $FMEDIA );
        header('Content-Type:' . $type);
        header( 'Content-Length: ' . filesize( $FMEDIA ) );
        readfile( $FMEDIA );
        exit( 0 );
        //echo get_msg( 'DEF_NOTEXIST' );
    }

?>
