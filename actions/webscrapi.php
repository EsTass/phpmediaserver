<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//action
	//url
	
	if( array_key_exists( 'url', $G_DATA ) 
	&& strlen( $G_DATA[ 'url' ] ) > 0
	&& filter_var( $G_DATA[ 'url' ], FILTER_VALIDATE_URL )
	){
        $URL = $G_DATA[ 'url' ];
	}else{
        echo "Invalid img";
        die();
	}
	
	//Get url
	if( ( $imgdata = file_get_contents_timed( $URL ) ) != FALSE
	&& getDataMimeTypeImg( $imgdata )
	){
        $type = getDataMimeType( $imgdata );
        header('Content-Type:' . $type);
        header( 'Content-Length: ' . strlen( $imgdata ) );
        echo $imgdata;
        exit( 0 );
    }
    
    //send default
    //$type = 'image/jpeg';
    $FMEDIA = PPATH_IMGS . DS . 'def.jpg';
    $type = getFileMimeType( $FMEDIA );
    header('Content-Type:' . $type);
    header( 'Content-Length: ' . filesize( $FMEDIA ) );
    readfile( $FMEDIA );
    exit( 0 );
    //echo get_msg( 'DEF_NOTEXIST' );

?>
