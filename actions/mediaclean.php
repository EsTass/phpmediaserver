<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//idmedia
	
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        $IDMEDIA = '';
	}
	
	if( $IDMEDIA > 0
	&& sqlite_media_update_mediainfo( $IDMEDIA, 0 )
	){
        echo get_msg( 'DEF_DELETED' );
	}else{
        echo get_msg( 'DEF_DELETED_ERROR' );
	}
?>
