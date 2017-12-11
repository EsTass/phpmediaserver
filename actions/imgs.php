<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//idmedia
	//idmediainfo
	//type = $G_MEDIADATA
	
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        $IDMEDIA = '';
	}
	
	if( array_key_exists( 'idmediainfo', $G_DATA ) ){
        $IDMEDIAINFO = $G_DATA[ 'idmediainfo' ];
	}else{
        $IDMEDIAINFO = '';
	}
	
	if( ( array_key_exists( 'type', $G_DATA ) 
        && array_key_exists( $G_DATA[ 'type' ], $G_MEDIADATA ) 
        )
    || $G_DATA[ 'type' ] == 'back'
    || $G_DATA[ 'type' ] == 'next'
	){
        $TYPE = $G_DATA[ 'type' ];
	}else{
        $TYPE = 'poster';
	}
	
	$FMEDIA = PPATH_MEDIAINFO . DS . $IDMEDIAINFO . '.' . $TYPE;
	$FMEDIA_B = PPATH_IMGS . DS . $TYPE . '.jpg';
	if( ( $IDMEDIAINFO == 1 || $IDMEDIA == 1 )
	&& ( $TYPE == 'next' || $TYPE == 'back' )
	&& file_exists( $FMEDIA_B )
	&& getFileMimeTypeImg( $FMEDIA_B )
	){
        $FMEDIA = $FMEDIA_B;
	}elseif( $IDMEDIAINFO > 0
	&& ( $mi = sqlite_mediainfo_getdata( $IDMEDIAINFO ) ) != FALSE 
	&& is_array( $mi )
	&& count( $mi ) > 0
	&& file_exists( $FMEDIA )
	&& getFileMimeTypeImg( $FMEDIA )
	){
        
	}elseif( $IDMEDIA > 0
	&& ( $m = sqlite_media_getdata( $IDMEDIA ) ) != FALSE 
	&& is_array( $m )
	&& count( $m ) > 0
	&& array_key_exists( 0, $m )
	&& array_key_exists( 'idmediainfo', $m[ 0 ] )
	&& $m[ 0 ][ 'idmediainfo' ] > 0
	&& ( $FMEDIA = PPATH_MEDIAINFO . DS . $m[ 0 ][ 'idmediainfo' ] . '.' . $TYPE ) !== FALSE
	&& file_exists( $FMEDIA )
	&& getFileMimeTypeImg( $FMEDIA )
	){
        
	}else{
        $FMEDIA = FALSE;
	}
	//var_dump( $mi );
	//var_dump( $m );
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
