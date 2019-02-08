<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	function get_html_list( $data, $title, $page = FALSE, $totalpages = FALSE, $toplayer = FALSE, $urltitle = FALSE ){
        $result = '';
        global $G_DATA;
        global $G_FILENAME_INFO;
        
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
            
            $extrainfo = "";
            if( //check_user_admin() &&
            isset( $G_FILENAME_INFO )
            && is_array( $G_FILENAME_INFO )
            && count( $G_FILENAME_INFO ) > 0
            && ( $edata = sqlite_media_getdata( $element[ 'idmedia' ] ) ) != FALSE
            && is_array( $edata )
            && array_key_exists( 0, $edata ) > 0
            && is_array( $edata[ 0 ] )
            && array_key_exists( 'file', $edata[ 0 ] )
            ){
                $extrai = '';
                foreach( $G_FILENAME_INFO AS $ititle => $grep ){
                    if( preg_match("/" . $grep . "/i", $edata[ 0 ][ 'file' ] ) ){
                        $extrai .= '' . $ititle . ' ';
                    }
                }
                $extrai .= '';
                if( strlen( $extrai ) > 0 ){
                    $extrainfo = "<div style='position:absolute;top:1;right:0;background-color: blue !important;'>" . $extrai . "</div>";
                }
            }
            $result .= "<div class='listElement " . $css_extra . "' onclick='" . $onclick .  "'><a href='#' title='" . htmlspecialchars( $ftitle, ENT_QUOTES ) . "'>" . $extrainfo . "<img title='" . htmlspecialchars( $ftitle, ENT_QUOTES ) . ": " . htmlspecialchars( $plot, ENT_QUOTES ) . "' class='listElementImg lazy' data-src='" . $urlposter . "' src='' /><span class='" . $css_played . "'>" . $ftitle . "" . $played . "</span></a></div>";
        }
        
        //Next
        if( $page !== FALSE 
        && $page >= 0
        && $page < $totalpages
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
	
	function get_html_list_series( $data, $title, $page = FALSE, $totalpages = FALSE, $toplayer = FALSE, $urltitle = FALSE ){
        $result = '';
        global $G_DATA;
        global $G_FILENAME_INFO;
        
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
            $urlchapters = getURLChapterList( $element[ 'idmedia' ], $element[ 'idmediainfo' ] );
            /*
            if( $element[ 'season' ] > 0 ){
                $ftitle .= ' ' . sprintf( '%02d', $element[ 'season' ] ) . 'x' . sprintf( '%02d', $element[ 'episode' ] );
                if( strlen( $element[ 'titleepisode' ] ) > 0 ){
                    $ftitle .= ' ' . $element[ 'titleepisode' ];
                }
            }
            */
            //TODO recalculate bar based on chapters played
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
                //$onclick = "list_show_info( this, " . $element[ 'idmediainfo' ] . " );";
                $onclick = 'goToURL( "' . $urlchapters . '" );';
            }
            
            $extrainfo = "";
            if( //check_user_admin() &&
            isset( $G_FILENAME_INFO )
            && is_array( $G_FILENAME_INFO )
            && count( $G_FILENAME_INFO ) > 0
            && ( $edata = sqlite_media_getdata( $element[ 'idmedia' ] ) ) != FALSE
            && is_array( $edata )
            && array_key_exists( 0, $edata ) > 0
            && is_array( $edata[ 0 ] )
            && array_key_exists( 'file', $edata[ 0 ] )
            ){
                $extrai = '';
                foreach( $G_FILENAME_INFO AS $ititle => $grep ){
                    if( preg_match("/" . $grep . "/i", $edata[ 0 ][ 'file' ] ) ){
                        $extrai .= '' . $ititle . ' ';
                    }
                }
                $extrai .= '';
                if( strlen( $extrai ) > 0 ){
                    $extrainfo = "<div style='position:absolute;top:1;right:0;background-color: blue !important;'>" . $extrai . "</div>";
                }
            }
            $result .= "<div class='listElement " . $css_extra . "' onclick='" . $onclick .  "'><a href='#' title='" . htmlspecialchars( $ftitle, ENT_QUOTES ) . "'>" . $extrainfo . "<img title='" . htmlspecialchars( $ftitle, ENT_QUOTES ) . ": " . htmlspecialchars( $plot, ENT_QUOTES ) . "' class='listElementImg lazy' data-src='" . $urlposter . "' src='' /><span class='" . $css_played . "'>" . $ftitle . "" . $played . "</span></a></div>";
        }
        
        //Next
        if( $page !== FALSE 
        && $page >= 0
        && $page < $totalpages
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
	
	//DOWNLOAD ELEMENTS
	
	function get_html_list_newdownloads_base( $search ){
        $result = '';
        $title = get_msg( 'DOWNLOADS_USER_TITLE', FALSE );
        $url = 'javascript:load_in_id( "' . getURLBase() . '?r=r&action=listdownloads&search=' . urlencode( $search ) . '", "newdownloadsresult" );return false;';
        
        $result .= "<div class='boxList'>";
        $result .= "<h2>";
        if( $url != FALSE ){
            $result .= "<a class='cursorPointer' onclick='" . $url . "'>" . $title . '</a>';
        }else{
            $result .= $title;
        }
        $result .= "</h2>";
        $result .= "<div id='newdownloadsresult' class='newdownloadsresult'>";
        $result .= "</div>";
        $result .= "</div>";
        
        return $result;
	}
	
	// KODI
	
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
	
	function get_html_list_kodi( $data, $title ){
        $result[ $title ] = array();
        $session = '&PHPSESSION=' . session_id() . '&|verifypeer=false';
        global $G_FILENAME_INFO;
        
        foreach( $data AS $element ){
            $genres = $element[ 'genre' ];
            $ftitle = $element[ 'title' ];
            $plot = $element[ 'plot' ];
            $year = $element[ 'year' ];
            $season = $element[ 'season' ];
            $episode = $element[ 'episode' ];
            $urlposter = getURLImg( FALSE, $element[ 'idmediainfo' ], 'poster' ) . $session;
            $urllandscape = getURLImg( FALSE, $element[ 'idmediainfo' ], 'landscape' ) . $session;
            $urlbanner =  getURLImg( FALSE, $element[ 'idmediainfo' ], 'banner' ) . $session;
            //direct, fast, mp4, 
            $urlplay = getURLBase() . '?r=r&action=playtime&mode=' . KODI_PLAYMODE . '&timeplayed=-1&idmedia=' . $element[ 'idmedia' ] . $session;
            if( $element[ 'season' ] > 0 ){
                $ftitle .= ' ' . sprintf( '%02d', $element[ 'season' ] ) . 'x' . sprintf( '%02d', $element[ 'episode' ] );
                if( strlen( $element[ 'titleepisode' ] ) > 0 ){
                    $ftitle .= ' ' . $element[ 'titleepisode' ];
                }
            }
            
            //add to plot extra filename info
            $extrainfo = "";
            if( //check_user_admin() &&
            isset( $G_FILENAME_INFO )
            && is_array( $G_FILENAME_INFO )
            && count( $G_FILENAME_INFO ) > 0
            && array_key_exists( 'file', $element )
            ){
                $extrai = '';
                foreach( $G_FILENAME_INFO AS $ititle => $grep ){
                    if( preg_match("/" . $grep . "/i", $element[ 'file' ] ) ){
                        $extrai .= '' . $ititle . ' ';
                    }
                }
                $extrai .= '';
                if( strlen( $extrai ) > 0 ){
                    $extrainfo = '( ' . $extrai . ') ';
                }
            }
            
            $e = array(
                'name' => $ftitle,
                'plot' => $extrainfo . $plot,
                'year' => $year,
                'season' => $season,
                'episode' => $episode,
                'thumb' => $urlposter,
                'landscape' => $urllandscape,
                'banner' =>  $urlbanner,
                'video' => $urlplay,
                'genre' => $genres,
            );
            $result[ $title ][] = $e;
        }
        
        return $result;
	}
	
	//LIVETV
	
	function get_html_list_live( $data, $title, $page = FALSE, $totalpages = FALSE, $urltitle = FALSE ){
        $result = '';
        global $G_DATA;
        global $G_FILENAME_INFO;
        
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
        
        $plot = '';
        foreach( $data AS $element ){
            $played = '';
            $css_played = '';
            $css_extra = '';
            $urlinfo = '';
            $ftitle = $element[ 'title' ];
            $urlposter = getURLImg( $element[ 'idmedialive' ], $element[ 'idmedialive' ], 'livetv' );
            $urlplayer = getURLPlayerLive( $element[ 'idmedialive' ], FALSE, 'poster' );
            
            $onclick = 'goToURL( "' . $urlplayer . '" );';
            
            $extrainfo = "";
            if( isset( $G_FILENAME_INFO )
            && is_array( $G_FILENAME_INFO )
            && count( $G_FILENAME_INFO ) > 0
            ){
                $extrai = '';
                foreach( $G_FILENAME_INFO AS $ititle => $grep ){
                    if( preg_match("/" . $grep . "/i", $element[ 'url' ] ) ){
                        $extrai .= '' . $ititle . ' ';
                    }
                }
                $extrai .= '';
                if( strlen( $extrai ) > 0 ){
                    $extrainfo = "<div style='position:absolute;top:1;right:0;background-color: blue !important;'>" . $extrai . "</div>";
                }
            }
            $result .= "<div class='listElement " . $css_extra . "' onclick='" . $onclick .  "'><a href='#' title='" . htmlspecialchars( $ftitle, ENT_QUOTES ) . "'>" . $extrainfo . "<img title='" . htmlspecialchars( $ftitle, ENT_QUOTES ) . ": " . htmlspecialchars( $plot, ENT_QUOTES ) . "' class='listElementImg lazy' data-src='" . $urlposter . "' src='' /><span class='" . $css_played . "'>" . $ftitle . "" . $played . "</span></a></div>";
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
	
	
	function get_html_list_kodi_live( $data, $title ){
        $result[ $title ] = array();
        $session = '&PHPSESSION=' . session_id();
        
        foreach( $data AS $element ){
            $genres = $element[ 'title' ];
            $ftitle = $element[ 'title' ];
            $plot = '';
            $year = date( 'Y' );
            $season = '';
            $episode = '';
            //$urlposter = $element[ 'poster' ];
            $urlposter = getURLImg( $element[ 'idmedialive' ], $element[ 'idmedialive' ], 'livetv' ) . $session;
            $urllandscape = '';
            $urlbanner =  '';
            //direct, fast, mp4, 
            $urlplay = getURLBase() . '?r=r&action=playlive&mode=mp4&idmedialive=' . $element[ 'idmedialive' ] . $session;
            if( $element[ 'season' ] > 0 ){
                $ftitle .= ' ' . sprintf( '%02d', $element[ 'season' ] ) . 'x' . sprintf( '%02d', $element[ 'episode' ] );
                if( strlen( $element[ 'titleepisode' ] ) > 0 ){
                    $ftitle .= ' ' . $element[ 'titleepisode' ];
                }
            }
            $e = array(
                'name' => $ftitle,
                'plot' => $plot,
                'year' => $year,
                'season' => $season,
                'episode' => $episode,
                'thumb' => $urlposter,
                'landscape' => $urllandscape,
                'banner' =>  $urlbanner,
                'video' => $urlplay,
                'genre' => $genres,
            );
            $result[ $title ][] = $e;
        }
        
        return $result;
	}
	
?>
