<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//saction
	//cat
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
	
	if( array_key_exists( 'saction', $G_DATA ) ){
        $G_SUBACTION = $G_DATA[ 'saction' ];       
	}else{
        $G_SUBACTION = '';
	}
	
	if( array_key_exists( 'cat', $G_DATA ) ){
        $G_CAT = utf8_decode( $G_DATA[ 'cat' ] );
	}else{
        $G_CAT = '';
	}
	
	if( array_key_exists( 'page', $G_DATA ) 
	&& is_numeric( $G_DATA[ 'page' ] )
	&& (int)$G_DATA[ 'page' ] >= 0
	){
        $G_PAGE = (int)$G_DATA[ 'page' ];
	}else{
        $G_PAGE = FALSE;
	}
	
	$RESULT = array();
	
	//SUBACTIONS
	//check
	switch( $G_SUBACTION ){
        case 'check':
            $RESULT = array( 'login' => TRUE );
        break;
        case 'categories':
            $RESULT = array( 
                get_msg( 'LIST_TITLE_CONTINUE', FALSE ),
                get_msg( 'LIST_TITLE_PREMIERE', FALSE ),
                get_msg( 'LIST_TITLE_RECOMENDED', FALSE ),
                get_msg( 'LIST_TITLE_LAST', FALSE ),
                '+' . get_msg( 'MEDIA_TYPE_SERIE', FALSE ),
                '-' . get_msg( 'MENU_SEARCH', FALSE ),
                '*' . get_msg( 'MENU_UPDATE', FALSE ),
            );
            if( defined( 'O_MENU_GENRES' )
            && is_array( O_MENU_GENRES )
            ){
                foreach( O_MENU_GENRES AS $g => $extrasearch ){
                    $RESULT[] = $g;
                }
            }
        break;
        case 'category':
            if( ( $edata = sqlite_media_getdata_filtered( utf8_encode( $G_CAT ), 1000 ) ) != FALSE 
            && is_array( $edata )
            && count( $edata ) > 0
            ){
                $TITLE = 'LIST';
                $RESULT = get_html_list_kodi( $edata, $TITLE );
                $RESULT = $RESULT[ $TITLE ];
            }else{
                $e = array(
                    'name' => get_msg( 'DEF_EMPTYLIST', FALSE ),
                    'plot' => get_msg( 'DEF_EMPTYLIST', FALSE ),
                    'year' => '',
                    'season' => '',
                    'episode' => '',
                    'thumb' => '',
                    'landscape' => '',
                    'banner' =>  '',
                    'video' => '',
                    'genre' => '',
                );
                $RESULT = array( $e );
            }
        break;
        case 'search':
            if( ( $edata = sqlite_media_getdata_filtered( utf8_encode( $G_SEARCH ), 1000 ) ) != FALSE 
            && is_array( $edata )
            && count( $edata ) > 0
            ){
                $TITLE = 'LIST';
                $RESULT = get_html_list_kodi( $edata, $TITLE );
                $RESULT = $RESULT[ $TITLE ];
            }else{
                $e = array(
                    'name' => get_msg( 'DEF_EMPTYLIST', FALSE ),
                    'plot' => get_msg( 'DEF_EMPTYLIST', FALSE ),
                    'year' => '',
                    'season' => '',
                    'episode' => '',
                    'thumb' => '',
                    'landscape' => '',
                    'banner' =>  '',
                    'video' => '',
                    'genre' => '',
                );
                $RESULT = array( $e );
            }
        break;
        case 'series':
            if( ( $edata = sqlite_media_getdata_filtered_grouped( utf8_encode( $G_SEARCH ), 10000, FALSE, TRUE ) ) != FALSE 
            && is_array( $edata )
            && count( $edata ) > 0
            ){
                $TITLE = 'LIST';
                foreach( $edata AS $e ){
                    $RESULT[] = $e[ 'title' ];
                }
            }else{
                $RESULT = array( get_msg( 'DEF_EMPTYLIST', FALSE ) );
            }
        break;
        default:
            
	}
	
	header( 'Content-Type: application/json; charset=UTF-8' );
	echo json_encode( $RESULT, JSON_HEX_APOS );
?>
