<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//search
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];       
	}else{
        $G_SEARCH = '';
	}
	
    //LiveTV
    if( //check_user_admin() && 
    ( $edata = sqlite_medialive_getdata_filter( $G_SEARCH, 10000 ) ) != FALSE 
    && count( $edata ) > 0
    ){
        $TITLE = get_msg( 'LIVETV_TITLE', FALSE );
        echo get_html_list_live( $edata, $TITLE );
    }
	
?>
