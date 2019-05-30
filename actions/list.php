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
	
	if( strlen( $G_SEARCH ) == 0 
	&& $G_PAGE === FALSE
	){
        
        //premierer mode
        $premierfuncts = array(
            //get_msg( 'LIST_TITLE_PREMIERE', FALSE ) => 'sqlite_media_getdata_premiere',
            get_msg( 'LIST_TITLE_PREMIERE', FALSE ) => 'sqlite_media_getdata_premiere_ex', //last in sorttitle
            get_msg( 'LIST_TITLE_PREMIERE_BESTRAT', FALSE ) => 'sqlite_media_getdata_premiere_ex2', //last 3 months better rating
            //get_msg( 'LIST_TITLE_PREMIERE_MOVIES', FALSE ) => 'sqlite_media_getdata_premiere_ex3', //last 3 months better rating only movies
            //get_msg( 'LIST_TITLE_PREMIERE', FALSE ) => 'sqlite_media_getdata_premiere_ex4', //last 3 months rnd
            get_msg( 'LIST_TITLE_PREMIERE_SERIES', FALSE ) => 'sqlite_media_getdata_premiere_ex5', //last 3 months series year + season + weeks*episode
        );
        /*
        //Randomize 1 list
        $premierefunct = $premierfuncts[ mt_rand( 0, ( count( $premierfuncts ) - 1 ) ) ];
        //Premiere
        if( ( $edata = $premierefunct( O_LIST_MINI_QUANTITY ) ) != FALSE
        && count( $edata ) > 0
        ){
            $TITLE = get_msg( 'LIST_TITLE_PREMIERE', FALSE );
            $urltitle = '?action=searcha&orderby=sorttitle';
            echo get_html_list( $edata, $TITLE, FALSE, FALSE, FALSE, $urltitle );
        }
        */
        //All lists Premiere
        foreach( $premierfuncts AS $t => $funct ){
            if( ( $edata = $funct( O_LIST_MINI_QUANTITY ) ) != FALSE
            && count( $edata ) > 0
            ){
                //$TITLE = get_msg( 'LIST_TITLE_PREMIERE', FALSE );
                $TITLE = $t;
                $urltitle = '?action=searcha&orderby=sorttitle';
                echo get_html_list( $edata, $TITLE, FALSE, FALSE, FALSE, $urltitle );
            }
        }
        
        //Continue
        if( ( $edata = sqlite_played_getdata_ext( FALSE, '', TRUE, O_LIST_MINI_QUANTITY, TRUE ) ) != FALSE 
        && count( $edata ) > 0
        ){
            $TITLE = get_msg( 'LIST_TITLE_CONTINUE', FALSE );
            $urltitle = FALSE;
            echo get_html_list( $edata, $TITLE, FALSE, FALSE, FALSE, $urltitle );
        }
        
        //LiveTV
        if( //check_user_admin() && 
        ( $edata = sqlite_medialive_getdata( FALSE, O_LIST_MINI_QUANTITY ) ) != FALSE 
        && count( $edata ) > 0
        ){
            $TITLE = get_msg( 'LIVETV_TITLE', FALSE );
            $urltitle = '?action=listlive';
            echo get_html_list_live( $edata, $TITLE, FALSE, FALSE, $urltitle );
        }
        
        //Recommended
        if( ( $edata = media_get_recomended( O_LIST_MINI_QUANTITY ) ) != FALSE 
        && count( $edata ) > 0
        ){
            $TITLE = get_msg( 'LIST_TITLE_RECOMENDED', FALSE );
            echo get_html_list( $edata, $TITLE );
        }
        
        //not ident
        if( ( $edata = sqlite_media_getdata_identify( '', O_LIST_MINI_QUANTITY ) ) != FALSE 
        && count( $edata ) > 0
        ){
            $r = array();
            foreach( $edata AS $row ){
                if( (int)$row[ 'idmediainfo' ] <= 0 ){
                    $row[ 'title' ] = basename( $row[ 'file' ] );
                    $row[ 'plot' ] = basename( $row[ 'file' ] );
                    $row[ 'season' ] = '';
                    $r[] = $row;
                }
            }
            $edata = $r;
            if( count( $edata ) > 0 ){
                $TITLE = get_msg( 'INFO_NEXT', FALSE ) . ' ' . get_msg( 'MENU_IDENTIFY', FALSE );
                $urltitle = '?action=searcha&orderby=dateadded';
                echo get_html_list( $edata, $TITLE, FALSE, FALSE, TRUE, $urltitle );
            }
        }
        
        //Last Added
        if( ( $edata = sqlite_media_getdata_filtered( $G_SEARCH, O_LIST_QUANTITY, 0 ) ) != FALSE 
        && count( $edata ) > 0
        ){
            $TITLE = get_msg( 'LIST_TITLE_LAST', FALSE );
            if( ( $edata_pages = sqlite_media_getdata_filtered_grouped_pages_total( $G_SEARCH, O_LIST_BIG_QUANTITY, FALSE, FALSE ) ) != FALSE ){
                $edata_pages = (int)( $edata_pages / O_LIST_BIG_QUANTITY );
            }else{
                $edata_pages = 1;
            }
            echo get_html_list( $edata, $TITLE, 0, $edata_pages );
        }
        
    }else{
        if( $G_PAGE === FALSE ){
            $G_PAGE = 0;
        }
        //add downloads user
        if( PPATH_WEBSCRAP_SEARCH != FALSE
        && $G_PAGE == 0
        && defined( 'O_MENU_GENRES' )
        && is_array( O_MENU_GENRES )
        && !array_key_exists( $G_SEARCH, O_MENU_GENRES )
        ){
            echo "" . get_html_list_newdownloads_base( $G_SEARCH );
        }
        //check search genre
        if( defined( 'O_MENU_GENRES' )
        && is_array( O_MENU_GENRES )
        && array_key_exists( $G_SEARCH, O_MENU_GENRES )
        ){
            $onlygenre = TRUE;
        }else{
            $onlygenre = FALSE;
        }
        
        $TITLE = get_msg( 'LIST_SEARCH_RESULT', FALSE );
        if( ( $edata_pages = sqlite_media_getdata_filtered_grouped_pages_total( $G_SEARCH, O_LIST_BIG_QUANTITY, FALSE, $onlygenre ) ) != FALSE 
        && ( $edata = sqlite_media_getdata_filtered( $G_SEARCH, O_LIST_BIG_QUANTITY, $G_PAGE, $onlygenre ) ) != FALSE 
        ){
            $TITLE = get_msg( 'LIST_TITLE_LAST', FALSE );
            $edata_pages = (int)( $edata_pages / O_LIST_BIG_QUANTITY );
            echo get_html_list( $edata, $TITLE, $G_PAGE, $edata_pages );
        }else{
            echo '<h2>' . get_msg( 'DEF_EMPTYLIST', FALSE ) . '</h2>';
        }
    }
	
?>
