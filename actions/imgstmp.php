<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//action
	//tfolder
	//tfile
	
	if( array_key_exists( 'tfolder', $G_DATA ) 
	&& strlen( $G_DATA[ 'tfolder' ] ) > 0
	&& file_exists( PPATH_TEMP . DS . $G_DATA[ 'tfolder' ] )
	){
        $TFOLDER = $G_DATA[ 'tfolder' ];
	}else{
        echo "Invalid tfolder";
        die();
	}
	
	if( array_key_exists( 'tfile', $G_DATA ) 
	&& strlen( $G_DATA[ 'tfile' ] ) > 0
	&& file_exists( PPATH_TEMP . DS . $G_DATA[ 'tfolder' ] . DS . $G_DATA[ 'tfile' ] )
	){
        $TFILE = $G_DATA[ 'tfile' ];
	}else{
        echo "Invalid tfile";
        die();
	}
	
	$FMEDIA = PPATH_TEMP . DS . $G_DATA[ 'tfolder' ] . DS . $G_DATA[ 'tfile' ];
	if( getFileMimeTypeImg( $FMEDIA )
	){
        $FMEDIA = $FMEDIA;
	}
	
	if( $FMEDIA ){
        //$type = 'image/jpeg';
        $type = getFileMimeType( $FMEDIA );
        header('Content-Type:' . $type);
        header( 'Content-Length: ' . filesize( $FMEDIA ) );
        readfile( $FMEDIA );
        exit( 0 );
    }else{
        //$type = 'image/jpeg';
        $FMEDIA = PPATH_IMGS . DS . 'def.jpg';
        $type = getFileMimeType( $FMEDIA );
        header('Content-Type:' . $type);
        header( 'Content-Length: ' . filesize( $FMEDIA ) );
        readfile( $FMEDIA );
        exit( 0 );
        //echo get_msg( 'DEF_NOTEXIST' );
    }

?>
