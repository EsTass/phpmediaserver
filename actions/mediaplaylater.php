<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmedia
	//idmediainfo
	//user
	
	$HTMLRESULT = '';
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        $IDMEDIA = FALSE;
	}
	
	if( array_key_exists( 'idmediainfo', $G_DATA ) ){
        $IDMEDIAINFO = $G_DATA[ 'idmediainfo' ];
	}else{
        $IDMEDIAINFO = FALSE;
	}
	
	if( $IDMEDIA == FALSE
	&& $IDMEDIAINFO == FALSE
	){
		$HTMLDATA = get_msg( 'DEF_NOTEXIST' ) . ' idmedia';
	}elseif( $IDMEDIA != FALSE
	&& ( $mediadata = sqlite_media_getdata_order_mediainfo( $IDMEDIA, 1 ) ) != FALSE
	&& is_array( $mediadata )
	&& array_key_exists( 0, $mediadata )
	&& is_array( $mediadata[ 0 ] )
	&& array_key_exists( 'file', $mediadata[ 0 ] )
	&& file_exists( $mediadata[ 0 ][ 'file' ] )
	&& sqlite_played_replace( $IDMEDIA, 0, ffmpeg_file_info_lenght_seconds( $mediadata[ 0 ][ 'file' ] ) ) 
	){
		$HTMLDATA = get_msg( 'INFO_PLAY_LATER', FALSE ) . ': ' . $mediadata[ 0 ][ 'title' ];
	}elseif( $IDMEDIAINFO != FALSE
	&& ( $mediadata = sqlite_media_getdata_mediainfo( $IDMEDIAINFO, 1 ) ) != FALSE
	&& is_array( $mediadata )
	&& array_key_exists( 0, $mediadata )
	&& is_array( $mediadata[ 0 ] )
	&& array_key_exists( 'file', $mediadata[ 0 ] )
	&& array_key_exists( 'idmedia', $mediadata[ 0 ] )
	&& file_exists( $mediadata[ 0 ][ 'file' ] )
	&& ( $mediad = sqlite_mediainfo_getdata( $IDMEDIAINFO, 1 ) ) != FALSE
	&& is_array( $mediad )
	&& array_key_exists( 0, $mediad )
	&& is_array( $mediad[ 0 ] )
	&& array_key_exists( 'title', $mediad[ 0 ] )
	&& sqlite_played_replace( $mediadata[ 0 ][ 'idmedia' ], 0, ffmpeg_file_info_lenght_seconds( $mediadata[ 0 ][ 'file' ] ) ) 
	){
		$HTMLDATA = get_msg( 'INFO_PLAY_LATER', FALSE ) . ': ' . $mediad[ 0 ][ 'title' ];
	}else{
		$HTMLDATA = get_msg( 'DEF_FILENOTEXIST', FALSE );
	}
	
	//header("Refresh: 2");
	echo $HTMLDATA;
?>
