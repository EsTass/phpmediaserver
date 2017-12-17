<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	function get_html_list( $data, $title, $page = FALSE, $totalpages = FALSE, $toplayer = FALSE, $urltitle = FALSE ){
        $result = '';
        global $G_DATA;
        
        $result .= "<div class='boxList'>";
        $result .= "<h2>";
        if( $urltitle != FALSE ){
            $result .= "<a href='" . $urltitle . "'>" . $title . '</a>';
        }else{
            $result .= $title;
        }
        if( $totalpages !== FALSE ){
            $result .= " (" . get_msg( 'LIST_TITLE_PAGE', FALSE ) . " " . $page . "";
            if( $page !== FALSE ){
                $result .= "/" . $totalpages . "";
            }
            $result .= ")";
        }
        $result .= "</h2>";
        if( array_key_exists( 'search', $G_DATA ) ){
            $search = $G_DATA[ 'search' ];
        }else{
            $search = '';
        }
        
        //Back
        if( $page > 0
        ){
            if( array_key_exists( 'action', $G_DATA ) ){
                $action = $G_DATA[ 'action' ];
            }else{
                $action = '';
            }
            $played = '';
            $css_played = '';
            $css_extra = '';
            $urlposter = getURLImg( FALSE, 1, 'back' );
            $result .= "<div class='listElement " . $css_extra . "' onclick='list_show_next( \"" . $action . "\", " . ( $page - 1 ) . ", \"" . $search .  "\" );'><a href='#' title='" . get_msg( 'LIST_TITLE_PREVPAGE', FALSE ) . "'><img class='listElementImg' src='" . $urlposter . "' title='" . get_msg( 'LIST_TITLE_PREVPAGE', FALSE ) . "' /><span class='" . $css_played . "'>" . get_msg( 'LIST_TITLE_PREVPAGE', FALSE ) . "" . $played . "</span></a></div>";
        }
        
        foreach( $data AS $element ){
            $played = '';
            $css_played = '';
            $css_extra = '';
            $urlinfo = '';
            $ftitle = $element[ 'title' ];
            $plot = $element[ 'plot' ];
            $urlposter = getURLImg( FALSE, $element[ 'idmediainfo' ], 'poster' );
            $urlplayer = getURLPlayer( $element[ 'idmedia' ], FALSE, 'poster' );
            if( $element[ 'season' ] > 0 ){
                $ftitle .= ' ' . sprintf( '%02d', $element[ 'season' ] ) . 'x' . sprintf( '%02d', $element[ 'episode' ] );
                if( strlen( $element[ 'titleepisode' ] ) > 0 ){
                    $ftitle .= ' ' . $element[ 'titleepisode' ];
                }
            }
            if( ( $dplayed = sqlite_played_getdata( $element[ 'idmedia' ] ) ) !== FALSE
            && is_array( $dplayed )
            && array_key_exists( 0, $dplayed )
            && is_array( $dplayed[ 0 ] )
            ){
                $dplayed = $dplayed[ 0 ];
                $css_played = 'dTitlePlayed';
                if( $dplayed[ 'max' ] > 0
                && $dplayed[ 'now' ] > 10 
                ){
                    $pplayed = (int)( ( $dplayed[ 'now' ] * 100 ) / $dplayed[ 'max' ] );
                    $played = "
                <div class='dPlayedTimeBox'>
                    <div class='dPlayedTimeBar' style='max-width:" . $pplayed . "%;'></div>
                </div>
                    ";
                }
            }
            
            if( $toplayer ){
                $onclick = 'goToURL( "' . $urlplayer . '" );';
            }else{
                $onclick = "list_show_info( this, " . $element[ 'idmediainfo' ] . " );";
            }
            $result .= "<div class='listElement " . $css_extra . "' onclick='" . $onclick .  "'><a href='#' title='" . $plot . "'><img class='listElementImg lazy' data-src='" . $urlposter . "' src='' title='" . $ftitle . "' /><span class='" . $css_played . "'>" . $ftitle . "" . $played . "</span></a></div>";
        }
        
        //Next
        if( $page !== FALSE 
        && $page >= 0
        && ( 
            count( $data ) == O_LIST_MINI_QUANTITY
            || count( $data ) == O_LIST_QUANTITY
            || count( $data ) == O_LIST_BIG_QUANTITY
        )
        ){
            if( array_key_exists( 'action', $G_DATA ) ){
                $action = $G_DATA[ 'action' ];
            }else{
                $action = '';
            }
            $played = '';
            $css_played = '';
            $css_extra = '';
            $urlposter = getURLImg( FALSE, 1, 'next' );
            $result .= "<div class='listElement " . $css_extra . "' onclick='list_show_next( \"" . $action . "\", " . ( $page + 1 ) . ",\"" . $search .  "\" );'><a href='#' title='" . get_msg( 'LIST_TITLE_NEXTPAGE', FALSE ) . "'><img class='listElementImg' src='" . $urlposter . "' title='" . get_msg( 'LIST_TITLE_NEXTPAGE', FALSE ) . "' /><span class='" . $css_played . "'>" . get_msg( 'LIST_TITLE_NEXTPAGE', FALSE ) . "" . $played . "</span></a></div>";
        }
        $result .= "</div>";
        
        return $result;
	}
	
	function get_html_list_chapters( $data, $mediainforow ){
        $result = '';
        $result .= "<div class='boxList'>";
        $result .= "<h2>" . $mediainforow[ 'title' ] . ' (' . $mediainforow[ 'year' ] . ")</h2>";
        if( strlen( $mediainforow[ 'plot' ] ) > 0 ){
            $result .= "<div class='dBoxInfoE'>" . $mediainforow[ 'plot' ] . "</div>";
        }
        $in_list = array();
        for( $s = 30; $s > -1; $s-- ){
            $temp_d = array();
            for( $e = 0; $e < 100; $e++ ){
                foreach( $data AS $el ){
                    if( !in_array( $el[ 'idmediainfo' ], $in_list )
                    && $el[ 'season' ] == $s
                    && $el[ 'episode' ] == $e
                    ){
                        $temp_d[] = $el;
                        $in_list[] = $el[ 'idmediainfo' ];
                    }
                }
            }
            if( count( $temp_d ) > 0 ){
                $result .= get_html_list( $temp_d, get_msg( 'MENU_SEASON' ) . ' ' . $s );
            }
        }
        return $result;
	}
	
?>
