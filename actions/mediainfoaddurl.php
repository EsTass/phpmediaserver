<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	//check_mod_admin();
	
	//action
	//idmediainfo
	//type
	//tfolder
	//url
	
	if( array_key_exists( 'idmediainfo', $G_DATA ) 
	&& ( $MIDATA = sqlite_mediainfo_getdata( $G_DATA[ 'idmediainfo' ] ) ) != FALSE 
	&& is_array( $MIDATA )
	&& array_key_exists( 0, $MIDATA )
	){
        $MIDATA = $MIDATA[ 0 ];
        $IDMEDIAINFO = $G_DATA[ 'idmediainfo' ];
	}else{
        echo "Invalid idmediainfo";
        die();
	}
	
	if( ( array_key_exists( 'type', $G_DATA ) 
        && array_key_exists( $G_DATA[ 'type' ], $G_MEDIADATA ) 
        )
	){
        $TYPE = $G_DATA[ 'type' ];
	}else{
        echo "Invalid type";
        die();
	}
	
	if( array_key_exists( 'tfolder', $G_DATA ) 
	&& strlen( $G_DATA[ 'tfolder' ] ) > 0
	&& file_exists( PPATH_TEMP . DS . $G_DATA[ 'tfolder' ] )
	){
        $TFOLDER = $G_DATA[ 'tfolder' ];
	}else{
        echo "Invalid tfolder";
        die();
	}
	
	if( array_key_exists( 'url', $G_DATA ) 
	&& strlen( $G_DATA[ 'url' ] ) > 3
	&& filter_var( $G_DATA[ 'url' ], FILTER_VALIDATE_URL )
	){
        $URL = $G_DATA[ 'url' ];
	}else{
        echo "Invalid URL: ";
        die();
	}
	
	$G_DATA[ 'tfile' ] = mt_rand( 1000, 9999 );
	$FMEDIA = PPATH_TEMP . DS . $G_DATA[ 'tfolder' ] . DS . $G_DATA[ 'tfile' ];
	if( downloadPosterToFile( $URL, $FMEDIA )
	&& getFileMimeTypeImg( $FMEDIA )
	){
        require( 'mediainfoaddimg.php' );
	}else{
        echo "<br />" . get_msg( 'DEF_COPYKO' , FALSE ) . ' ' . $URL;
	}
	
?>
