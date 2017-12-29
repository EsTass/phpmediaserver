<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//search
	//page
	
	/*
	'''
    {
        'Category': [
            {
                'name' => 'title',
                'plot' => 'plot',
                'year' => 'year',
                'season' => 'season',
                'episode' => 'episode',
                'thumb' => 'url',
                'landscape' => 'url',
                'banner' =>  'banner',
                'video' => 'video',
                'genre' => 'genres',
            },
            ...
        ],
        ...
    }
    '''
	*/
	
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
        
        //ADD SEARCH & UPDATE
        $e = array(
            'name' => get_msg( 'MENU_SEARCH', FALSE ),
            'plot' => get_msg( 'MENU_SEARCH', FALSE ),
            'year' => '',
            'season' => '',
            'episode' => '',
            'thumb' => '',
            'landscape' => '',
            'banner' =>  '',
            'video' => '',
            'genre' => '',
        );
        $e[ 'search' ] = TRUE;
        $ELIST[ get_msg( 'MENU_SEARCH', FALSE ) ] = array( $e );
        unset( $e[ 'search' ] );
        $e[ 'update' ] = TRUE; 
        $ELIST[ get_msg( 'MENU_UPDATE', FALSE ) ] = array( $e );
        
    }else{
        $TITLE = get_msg( 'LIST_SEARCH_RESULT', FALSE );
        if( ( $edata = sqlite_media_getdata_filtered( $G_SEARCH, 1000 ) ) != FALSE 
        ){
            $TITLE = get_msg( 'LIST_TITLE_LAST', FALSE );
            $ELIST = get_html_list_kodi( $edata, $TITLE );
            $ELIST = $ELIST[ $TITLE ];
        }else{
            die( get_msg( 'DEF_EMPTYLIST', FALSE ) );
        }
    }
	
	header( 'Content-Type: application/json; charset=UTF-8' );
	echo json_encode( $ELIST, JSON_HEX_APOS );
?>
