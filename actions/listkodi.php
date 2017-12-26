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
	
	$ELIST = array();
	if( strlen( $G_SEARCH ) == 0 
	&& $G_PAGE === FALSE
	){
	
        //Continue
        if( ( $edata = sqlite_played_getdata_ext( FALSE, '', TRUE, O_LIST_BIG_QUANTITY, TRUE ) ) != FALSE 
        && count( $edata ) > 0
        ){
            $TITLE = get_msg( 'LIST_TITLE_CONTINUE', FALSE );
            $ELIST = array_merge( $ELIST,  get_html_list_kodi( $edata, $TITLE ) );
        }
        
        //Premiere
        if( ( $edata = sqlite_media_getdata_premiere_ex( O_LIST_BIG_QUANTITY ) ) != FALSE 
        && count( $edata ) > 0
        ){
            $TITLE = get_msg( 'LIST_TITLE_PREMIERE', FALSE );
            $ELIST = array_merge( $ELIST,  get_html_list_kodi( $edata, $TITLE ) );
        }
        
        //Recommended
        if( ( $edata = media_get_recomended( O_LIST_BIG_QUANTITY ) ) != FALSE 
        && count( $edata ) > 0
        ){
            $TITLE = get_msg( 'LIST_TITLE_RECOMENDED', FALSE );
            $ELIST = array_merge( $ELIST,  get_html_list_kodi( $edata, $TITLE ) );
        }
        
        //Last Added
        if( ( $edata = sqlite_media_getdata_filtered( $G_SEARCH, O_LIST_BIG_QUANTITY, 0 ) ) != FALSE 
        && count( $edata ) > 0
        ){
            $TITLE = get_msg( 'LIST_TITLE_LAST', FALSE );
            $ELIST = array_merge( $ELIST,  get_html_list_kodi( $edata, $TITLE ) );
        }
        
        //generes menu
        if( defined( 'O_MENU_GENRES' )
        && is_array( O_MENU_GENRES )
        ){
            foreach( O_MENU_GENRES AS $g => $extrasearch ){
                //genre
                if( ( $edata = sqlite_media_getdata_filtered( $g, O_LIST_BIG_QUANTITY ) ) != FALSE 
                && count( $edata ) > 0
                ){
                    $TITLE = $g;
                    $ELIST = array_merge( $ELIST,  get_html_list_kodi( $edata, $TITLE ) );
                }
            }
        }
        
    }else{
        if( $G_PAGE === FALSE ){
            $G_PAGE = 0;
        }
        $TITLE = get_msg( 'LIST_SEARCH_RESULT', FALSE );
        if( ( $edata_pages = sqlite_media_getdata_filtered_grouped_pages_total( $G_SEARCH, O_LIST_BIG_QUANTITY ) ) != FALSE 
        && ( $edata = sqlite_media_getdata_filtered( $G_SEARCH, O_LIST_BIG_QUANTITY, $G_PAGE, $edata_pages ) ) != FALSE 
        ){
            $TITLE = get_msg( 'LIST_TITLE_LAST', FALSE );
            $edata_pages = (int)( $edata_pages / O_LIST_BIG_QUANTITY );
            $ELIST = get_html_list_kodi( $edata, $TITLE, $G_PAGE, $edata_pages );
        }else{
            die( get_msg( 'DEF_EMPTYLIST', FALSE ) );
        }
    }
	
	header( 'Content-Type: application/json; charset=UTF-8' );
	echo json_encode( $ELIST, JSON_HEX_APOS );
?>
