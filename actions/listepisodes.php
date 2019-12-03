<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//idmedia
	//idmediainfo
	
	$HTMLRESULT = '';
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
	
	if( $IDMEDIAINFO > 0
	&& ( $MEDIAINFO = sqlite_mediainfo_getdata( $IDMEDIAINFO ) ) != FALSE 
	&& is_array( $MEDIAINFO )
	&& count( $MEDIAINFO ) > 0
	){
        $MEDIAINFO = $MEDIAINFO[ 0 ];
        $HTMLRESULT = '';
	}elseif( $IDMEDIA > 0
	&& ( $m = sqlite_media_getdata( $IDMEDIA ) ) != FALSE 
	&& is_array( $m )
	&& count( $m ) > 0
	&& array_key_exists( 0, $m )
	&& array_key_exists( 'idmediainfo', $m[ 0 ] )
	&& $m[ 0 ][ 'idmediainfo' ] > 0
	&& ( $MEDIAINFO = sqlite_mediainfo_getdata( $m[ 0 ][ 'idmediainfo' ] ) ) != FALSE 
	&& is_array( $MEDIAINFO )
	&& count( $MEDIAINFO ) > 0
	){
        $MEDIAINFO = $MEDIAINFO[ 0 ];
        $IDMEDIAINFO = $MEDIAINFO[ 'idmediainfo' ];
        $HTMLRESULT = '';
	}else{
        $MEDIAINFO = FALSE;
        $HTMLRESULT = get_msg( 'DEF_NOTEXIST' );
	}
	
	if( is_array( $MEDIAINFO )
	&& ( $edata = sqlite_media_getdata_chapters( $MEDIAINFO[ 'title' ] ) ) != FALSE 
	&& count( $edata ) > 0
	){
        echo get_html_list_chapters( $edata, $MEDIAINFO );
	}else{
        echo get_msg( 'DEF_EMPTYLIST', FALSE );
	}
	
?>
