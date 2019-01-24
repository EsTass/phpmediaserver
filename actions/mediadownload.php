<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//idmedia
	//idmediainfo
	
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
	
	if( array_key_exists( 'type', $G_DATA ) 
	&& array_key_exists( $G_DATA[ 'type' ], $G_MEDIADATA )
	){
        $TYPE = $G_DATA[ 'type' ];
	}else{
        $TYPE = 'poster';
	}
	
	if( $IDMEDIA > 0
	&& ( $mi = sqlite_media_getdata( $IDMEDIA ) ) != FALSE 
	&& is_array( $mi )
	&& count( $mi ) > 0
	&& file_exists( $mi[ 0 ][ 'file' ] )
	&& getFileMimeTypeVideo( $mi[ 0 ][ 'file' ] )
	){
        $FMEDIA = $mi[ 0 ][ 'file' ];
        $IDMEDIAINFO = $mi[ 0 ][ 'idmediainfo' ];
	}elseif( $IDMEDIAINFO > 0
	&& ( $mi = sqlite_media_getdata_mediainfo( $IDMEDIAINFO ) ) != FALSE 
	&& is_array( $mi )
	&& count( $mi ) > 0
	&& file_exists( $mi[ 0 ][ 'file' ] )
	&& getFileMimeTypeVideo( $mi[ 0 ][ 'file' ] )
	){
        $FMEDIA = $mi[ 0 ][ 'file' ];
        $IDMEDIA = $mi[ 0 ][ 'idmedia' ];
	}else{
        $FMEDIA = FALSE;
	}
	
	if( $FMEDIA ){
        
        //SET PLAYING NOW
        if( ( $idplaying = sqlite_playing_insert( USERNAME, $IDMEDIA, FALSE, 'WEB-download', getmypid() ) ) != FALSE 
        ){
            $CHECKPLAYING = TRUE;
            register_shutdown_function( function( $idplaying ){
                sqlite_playing_delete( $idplaying );
            }, $idplaying );
        }else{
            $CHECKPLAYING = FALSE;
            $idplaying = FALSE;
        }
        
        //header( "X-Sendfile: $FMEDIA" );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . basename( $FMEDIA ) . '"' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . filesize( $FMEDIA ) );
		ob_clean();
		flush();
		//readfile( $FMEDIA );
		readfile_chunked( $FMEDIA );
		
        //REMOVE PLAYING NOW
        if( $CHECKPLAYING
        && $idplaying
        ){
            sqlite_playing_delete( $idplaying );
        }
		
		exit( 0 );
    }else{
        echo get_msg( 'DEF_NOTEXIST' );
    }

?>
