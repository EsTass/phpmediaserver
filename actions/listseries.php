<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//search
	//page
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];       
	}else{
        $G_SEARCH = '';
	}
	
	if( array_key_exists( 'page', $G_DATA ) 
	&& is_numeric( $G_DATA[ 'page' ] )
	&& (int)$G_DATA[ 'page' ] >= 0
	){
        $G_PAGE = (int)$G_DATA[ 'page' ];
	}else{
        $G_PAGE = FALSE;
	}
	
    if( $G_PAGE === FALSE ){
        $G_PAGE = 0;
    }
    $TITLE = get_msg( 'LIST_SEARCH_RESULT', FALSE );
    if( ( $edata_pages = sqlite_media_getdata_filtered_grouped_pages_total( $G_SEARCH, O_LIST_BIG_QUANTITY, TRUE ) ) != FALSE 
    && ( $edata = sqlite_media_getdata_filtered_grouped( $G_SEARCH, O_LIST_BIG_QUANTITY, $G_PAGE, TRUE ) ) != FALSE 
    && count( $edata ) > 0
    ){
        $TITLE = get_msg( 'LIST_TITLE_LAST', FALSE );
        $edata_pages = (int)( $edata_pages / O_LIST_BIG_QUANTITY );
        echo get_html_list( $edata, $TITLE, $G_PAGE, $edata_pages );
    }else{
        echo get_msg( 'DEF_EMPTYLIST', FALSE );
    }
	
?>
