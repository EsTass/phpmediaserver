<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmedia
	
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        echo "Invalid ID: idmedia";
        die();
	}
	
	$SHOW_IDENTIFY_DATA = TRUE;
	
	//ADDING FOR DATA
	
	if( media_scrap_idmedia( $IDMEDIA, $SHOW_IDENTIFY_DATA )
	&& ( $info_data = sqlite_media_getdata_order_mediainfo( $IDMEDIA, 1 ) ) != FALSE
	&& is_array( $info_data )
	&& count( $info_data )
	&& array_key_exists( 0, $info_data )
	&& is_array( $info_data[ 0 ] )
	&& array_key_exists( 'title', $info_data[ 0 ] )
	&& strlen( $info_data[ 0 ][ 'title' ] ) > 0
	){
        if( $SHOW_IDENTIFY_DATA == FALSE ){
            echo get_msg( 'IDENT_DETECTEDOK' ) . ' ' . $info_data[ 0 ][ 'title' ] . ' => ' . $info_data[ 0 ][ 'file' ];
        }
    }else{
        echo get_msg( 'IDENT_NOTDETECTED' );
    }
?>
