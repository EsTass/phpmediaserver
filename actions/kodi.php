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
        if( mb_detect_encoding( $G_DATA[ 'search' ], 'UTF-8', TRUE ) ){
            $G_SEARCH = $G_DATA[ 'search' ];
        }else{
            $G_SEARCH = utf8_encode( $G_DATA[ 'search' ] );
        }
	}else{
        $G_SEARCH = '';
	}
	
	if( array_key_exists( 'saction', $G_DATA ) ){
        $G_SUBACTION = $G_DATA[ 'saction' ];       
	}else{
        $G_SUBACTION = '';
	}
	
	if( array_key_exists( 'cat', $G_DATA ) ){
        if( mb_detect_encoding( $G_DATA[ 'cat' ], 'UTF-8', TRUE ) ){
            $G_CAT = $G_DATA[ 'cat' ];
        }else{
            $G_CAT = utf8_encode( $G_DATA[ 'cat' ] );
        }
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
	
	//Log
	sqlite_log_insert( $G_DATA[ 'action' ], 'as: ' . $G_SUBACTION . ' - cat: ' . $G_CAT );
	
	$RESULT = array();
	
	//SUBACTIONS
	//check
	switch( $G_SUBACTION ){
        case 'check':
            $RESULT = array( 'login' => TRUE );
        break;
        case 'categories':
            $RESULT = array( 
                ' ' . get_msg( 'LIST_TITLE_CONTINUE', FALSE ),
                ' ' . get_msg( 'LIST_TITLE_PREMIERE', FALSE ),
                ' ' . get_msg( 'LIST_TITLE_RECOMENDED', FALSE ),
                ' ' . get_msg( 'LIST_TITLE_LAST', FALSE ),
                '+' . get_msg( 'MEDIA_TYPE_SERIE', FALSE ),
                '-' . get_msg( 'MENU_SEARCH', FALSE ),
                '*' . get_msg( 'MENU_UPDATE', FALSE ),
                ' ' . get_msg( 'LIVETV_TITLE', FALSE ),
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
            if( $G_CAT == ' ' . get_msg( 'LIVETV_TITLE', FALSE ) ){
                //Live TV
                if( ( $edata = sqlite_medialive_getdata_filter( $G_SEARCH, O_LIST_BIG_QUANTITY ) ) != FALSE 
                && count( $edata ) > 0
                ){
                    $TITLE = get_msg( 'LIVETV_TITLE', FALSE );
                    $RESULT = get_html_list_kodi_live( $edata, $TITLE );
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
            
            }elseif( $G_CAT == ' ' . get_msg( 'LIST_TITLE_LAST', FALSE ) ){
                //Last Added
                if( ( $edata = sqlite_media_getdata_filtered( $G_SEARCH, O_LIST_BIG_QUANTITY ) ) != FALSE 
                && count( $edata ) > 0
                ){
                    $TITLE = get_msg( 'LIST_TITLE_LAST', FALSE );
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
            
            }elseif( $G_CAT == ' ' . get_msg( 'LIST_TITLE_CONTINUE', FALSE ) ){
                //Continue
                if( ( $edata = sqlite_played_getdata_ext( FALSE, '', TRUE, O_LIST_BIG_QUANTITY, TRUE ) ) != FALSE 
                && count( $edata ) > 0
                ){
                    $TITLE = get_msg( 'LIST_TITLE_CONTINUE', FALSE );
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
            
            }elseif( $G_CAT == ' ' . get_msg( 'LIST_TITLE_PREMIERE', FALSE ) ){
                //Premiere
                if( ( $edata = sqlite_media_getdata_premiere_ex( O_LIST_BIG_QUANTITY ) ) != FALSE 
                && count( $edata ) > 0
                ){
                    $TITLE = get_msg( 'LIST_TITLE_PREMIERE', FALSE );
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
            
            }elseif( $G_CAT == ' ' . get_msg( 'LIST_TITLE_RECOMENDED', FALSE ) ){
                //Recommended
                if( ( $edata = media_get_recomended( O_LIST_BIG_QUANTITY ) ) != FALSE 
                && count( $edata ) > 0
                ){
                    $TITLE = get_msg( 'LIST_TITLE_RECOMENDED', FALSE );
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
            
            }elseif( ( $edata = sqlite_media_getdata_filtered( trim( $G_CAT ), 1000 ) ) != FALSE 
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
            if( ( $edata = sqlite_media_getdata_filtered( $G_SEARCH, 1000 ) ) != FALSE 
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
            if( ( $edata = sqlite_media_getdata_filtered_grouped( $G_SEARCH, 10000, FALSE, TRUE ) ) != FALSE 
            && is_array( $edata )
            && count( $edata ) > 0
            ){
                //add showed series first in list
                if( ( $edata2 = sqlite_played_getdata_ext( FALSE, '', TRUE, O_LIST_BIG_QUANTITY, TRUE ) ) != FALSE 
                && count( $edata2 ) > 0
                ){
                    $x = 1;
                    foreach( $edata2 AS $e ){
                        if( $e[ 'season' ] > 0 || $e[ 'episode' ] > 0 ){
                            $RESULT[] = " " . $e[ 'title' ];
                            $x++;
                        }
                    }
                }
                
                $TITLE = 'LIST';
                foreach( $edata AS $e ){
                    if( !in_array( $e[ 'title' ], $RESULT ) ){
                        $RESULT[] = $e[ 'title' ];
                    }
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
