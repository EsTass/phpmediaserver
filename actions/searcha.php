<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//search
	//year
	//year2
	//rating
	//genre1
	//genre2
	//genre3
	//orderby
	
	$ORDERBY = array(
        'title' => get_msg( 'MENU_TITLE', FALSE ),
        'year' => get_msg( 'MENU_YEAR', FALSE ),
        'rating' => get_msg( 'MENU_RATING', FALSE ),
        'dateadded' => get_msg( 'DEF_ELEMENTUPDATED', FALSE ),
        'sorttitle' => get_msg( 'LIST_TITLE_PREMIERE', FALSE ),
	);
	
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
	
	if( array_key_exists( 'year', $G_DATA ) 
	&& is_numeric( $G_DATA[ 'year' ] )
	&& (int)$G_DATA[ 'year' ] >= 0
	){
        $G_YEAR = (int)$G_DATA[ 'year' ];
	}else{
        $G_YEAR = FALSE;
	}
	
	if( array_key_exists( 'year2', $G_DATA ) 
	&& is_numeric( $G_DATA[ 'year2' ] )
	&& (int)$G_DATA[ 'year2' ] >= 0
	){
        $G_YEAR2 = (int)$G_DATA[ 'year2' ];
	}else{
        $G_YEAR2 = FALSE;
	}
	
	if( array_key_exists( 'rating', $G_DATA ) 
	&& is_numeric( $G_DATA[ 'rating' ] )
	&& (int)$G_DATA[ 'rating' ] >= 0
	){
        $G_RATING = (int)$G_DATA[ 'rating' ];
	}else{
        $G_RATING = FALSE;
	}
	
	$G_GENRES = array();
	for( $x = 1; $x < 10; $x++ ){
        if( array_key_exists( 'genre' . $x, $G_DATA ) ){
            $G_GENRES[] = $G_DATA[ 'genre' . $x ];       
        }
	}
    
	if( array_key_exists( 'orderby', $G_DATA ) 
	&& array_key_exists( $G_DATA[ 'orderby' ], $ORDERBY )
	){
        $G_ORDERBY = $G_DATA[ 'orderby' ];
	}else{
        $G_ORDERBY = FALSE;
	}
	
    $TITLE = get_msg( 'LIST_SEARCH_RESULT', FALSE );
    if( ( $edata = sqlite_media_getdata_filtered_ext( $G_SEARCH, $G_YEAR, $G_YEAR2, $G_RATING, $G_GENRES, $G_ORDERBY, 1000, $G_PAGE ) ) != FALSE 
    ){
        $TITLE = get_msg( 'LIST_SEARCH_RESULT', FALSE );
        echo get_html_list( $edata, $TITLE, $G_PAGE, FALSE );
    }else{
        echo get_msg( 'DEF_EMPTYLIST', FALSE );
    }
	
?>
