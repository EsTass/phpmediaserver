<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//search
	//url
	//deep
	
	//VARS
	$G_WEBSPIDER = array(
        //'all' => 'ALL (INFINITE)?',
        'self' => 'Own Web (5)',
        'self1' => 'Own Web (5) +1 External',
        '0' => '0',
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
	);
	
	if( array_key_exists( 'search', $G_DATA ) 
	){
        $SEARCH = $G_DATA[ 'search' ];
	}else{
        $SEARCH = '';
	}
	
	if( array_key_exists( 'url', $G_DATA ) 
	&& strlen( $G_DATA[ 'url' ] ) > 3
	&& filter_var( $G_DATA[ 'url' ], FILTER_VALIDATE_URL )
	){
        $URL = $G_DATA[ 'url' ];
	}else{
        echo "Invalid url: ";
        die();
	}
	
	if( array_key_exists( 'deep', $G_DATA ) 
	&& array_key_exists( $G_DATA[ 'deep' ], $G_WEBSPIDER )
	){
        $SPIDER = $G_DATA[ 'deep' ];
	}else{
        echo "Invalid spider: ";
        die();
	}
	
	echo "<br />";
	echo get_msg( 'MENU_SEARCH', FALSE ) . ': ' . $SPIDER . ' => ' . $URL;
	
	//return array( 'ishtml', 'html', 'links', 'p2p' )
	function webspider_get_full_data( $url, $onlysubpages = FALSE, $debug = FALSE ){
        //Get page
        //extract links
        //extrack p2p links in links
        //extract p2p links in html
        $htmldata = file_get_contents_timed( $url );
        $result = array(
            'ishtml' => TRUE,
            'html' => $htmldata,
            'links' => array(),
            'p2p' => array(),
        );
        
        //Check File type
        if( inString( $htmldata, '<html' ) 
        || inString( $htmldata, '<body' ) 
        ){
            //HTML TYPE
            $dom = new DOMDocument();
            @$dom->loadHTML( mb_convert_encoding( $htmldata, 'HTML-ENTITIES', mb_detect_encoding( $htmldata ) ) );
            //@$dom->loadHTML( $htmldata );
            
            $stitle = '';
            $ltitle = $dom->getElementsByTagName('title');
            if( $ltitle->length > 0 ){
                $stitle = $ltitle->item(0)->textContent;
                if( strlen( $stitle ) == 0 ){
                    $stitle = $ltitle->item(0)->nodeValue;
                }
            }
            $stitle = preg_replace('/[^a-zA-Z0-9\-\._]/','', $stitle);
            
            //Get links
            $elements = $dom->getElementsByTagName( 'a' );
            foreach( $elements AS $link ){
                $href = $link->getAttribute( "href" );
                $title = substr( strip_tags( $link->nodeValue ), 1, 120 );
                if( strlen( $title ) == 0 ){
                    $title = substr( strip_tags( $link->textContent ), 1, 120 );
                }
                if( $debug ) echo "<br />LINKS++: " . $href;
                if( strlen( $href ) > 0 
                ){
                    if( !startsWith( $href, 'http' ) 
                    &&  !startsWith( $href, 'magnet' ) 
                    &&  !startsWith( $href, 'ed2k' ) 
                    ){
                        $urld = parse_url( $url );
                        if( !startsWith( $href, '/' ) ){
                            $href = '/' . $href;
                        }
                        $href = $urld[ 'scheme' ] . "://" . $urld['host'] . $href;
                        if( $debug ) echo "<br />LINKS+++: " . $href;
                    }
                    if( filter_var( $href, FILTER_VALIDATE_URL ) 
                    && strlen( $href ) < 512
                    ){
                        if( $debug ) echo "<br />LINKS ADDED: " . $href;
                        $result[ 'links' ][ $href ] = $title . '_' . getRandomString( 6 );
                    }
                }
            }
            if( $debug ) echo "<br />LINKS TOTAL: " . count( $result[ 'links' ] );
            $x = 1;
            //Get links raw
            if( ( $links = extract_links( $htmldata ) ) != FALSE ){
                $sbtitle = $stitle;
                if( strlen( $sbtitle ) == 0  ){
                    $sbtitle = 'Extractlinks_' . getRandomString( 6 ) . '_';
                }
                foreach( $links AS $l ){
                    if( filter_var( $l, FILTER_VALIDATE_URL ) 
                    && endsWith( $l, '.torrent' )
                    ){
                        $result[ 'p2p' ][ $l ] = $sbtitle . $x;
                        unset( $result[ 'links' ][ $l ] );
                        $x++;
                    }
                }
            }
            //Get Links P2P
            foreach( $result[ 'links' ] AS $link => $title ){
                //know links
                if( startsWith( $link, 'magnet' )
                || startsWith( $link, 'ed2k' )
                || endsWith( $link, '.torrent' )
                ){
                    $result[ 'p2p' ][ $link ] = $title;
                    unset( $result[ 'links' ][ $link ] );
                }
            }
            //extract from html
            $sbtitle = $stitle;
            if( strlen( $sbtitle ) == 0  ){
                $sbtitle = 'Extract_' . getRandomString( 6 ) . '_';
            }
            if( ( $links = extract_elinks( $htmldata ) ) != FALSE ){
                //$x = 1;
                foreach( $links AS $l ){
                    $result[ 'p2p' ][ $l ] = $sbtitle . $x;
                    $x++;
                }
            }
            if( ( $links = extract_magnets( $htmldata ) ) != FALSE ){
                //$x = 1;
                foreach( $links AS $l ){
                    $result[ 'p2p' ][ $l ] = $sbtitle . $x;
                    $x++;
                }
            }
            
            //Clean links if needed
            if( $onlysubpages ){
                $links = $result[ 'links' ];
                foreach( $links AS $href => $title ){
                    if( !startsWith( $url, $href ) ){
                        unset( $result[ 'links' ][ $href ] );
                    }
                }
            }
        }else{
            //NON HTML TYPE
            $result[ 'ishtml' ] = FALSE;
        }
        
        return $result;
	}
	
	function webspider_search( $url, $deep, $maxdeep = 10, $SPIDER = 'all', &$scrapped = FALSE ){
        $nowurl = $url;
        $deepstr = str_repeat( '-', ( $maxdeep - $deep ) );
        echo "<br /><br /> " . $deepstr . " DEEP: " . $deep;
        echo "<br /> " . $deepstr . " URL: " . $nowurl;
        
        if( in_array( $url, $scrapped ) ){
            echo "<br />" . $deepstr . "SCRAPPED BEFORE: " . $url;
        }elseif( ( $data = webspider_get_full_data( $nowurl ) ) != FALSE 
        && $data[ 'ishtml' ]
        ){
            $scrapped[] = $url;
            echo "<br />" . $deepstr . "DATA: " . count( $data );
            echo "<br />" . $deepstr . "LINKS P2P: " . count( $data[ 'p2p' ] );
            if( count( $data[ 'p2p' ] ) > 0 ){
                foreach( $data[ 'p2p' ] AS $link => $title ){
                    
                    if( in_array( $link, $scrapped ) ){
                        echo "<br />" . $deepstr . "SCRAPPED BEFORE: " . $url;
                    }elseif( startsWith( $link, 'magnet' ) ){
                        $scrapped[] = $link;
                        echo "<br />" . $deepstr . "LINKS P2P ADDED: " . $title . ' - ' . $link;
                        magnetAdd( $link );
                    }elseif( startsWith( $link, 'ed2k' ) ){
                        $scrapped[] = $link;
                        echo "<br />" . $deepstr . "LINKS P2P ADDED: " . $title . ' - ' . $link;
                        amuleAdd( $link );
                    }else{
                        $scrapped[] = $link;
                        echo "<br />" . $deepstr . "LINKS P2P DOWNLOAD: " . $title . ' - ' . $link;
                        //PPATH_WEBSCRAP_DOWNLOAD  PPATH_TEMP
                        if( torrentAdd( $link, PPATH_TEMP . DS . $title ) ){
                            echo "<br />" . $deepstr . "LINKS P2P DOWNLOADED: " . PPATH_TEMP . DS . $title;
                        }
                    }
                }
            }
            //Check deep
            echo "<br />" . $deepstr . "LINKS LIST: " . count( $data[ 'links' ] );
            if( $deep > 0 ){
                if( count( $data[ 'links' ] ) > 0 ){
                    foreach( $data[ 'links' ] AS $link => $title ){
                        $urld = parse_url( $url );
                        if( in_array( $link, $scrapped ) ){
                            echo "<br />" . $deepstr . "SCRAPPED BEFORE: " . $url;
                        }elseif( 
                            ( 
                            is_numeric( $SPIDER )
                            && inString( $link, $urld[ 'host' ] ) !== FALSE
                            )
                        || $SPIDER == 'all'
                        || ( 
                            $SPIDER == 'self' 
                            && inString( $link, $urld[ 'host' ] ) !== FALSE
                            )
                        ){
                            echo "<br />" . $deepstr . "INTERNAL URL: " . $link;
                            webspider_search( $link, $deep - 1, $maxdeep, $SPIDER, $scrapped );
                        }elseif( $SPIDER == 'self1' 
                        && !inString( $link, $urld[ 'host' ] ) !== FALSE
                        ){
                            echo "<br />" . $deepstr . "EXTERNAL URL: " . $link;
                            webspider_search( $link, 0, $maxdeep, $SPIDER, $scrapped );
                        }else{
                            echo "<br />" . $deepstr . "URL OUT: " . $link;
                        }
                        $scrapped[] = $link;
                    }
                }
            }
        }elseif( is_array( $data ) 
        && $data[ 'ishtml' ] == FALSE
        ){
            //Is file check type
            $scrapped[] = $url;
            echo "<br />ERROR. NOT HTML TYPE: " . $nowurl;
        }else{
            $scrapped[] = $url;
            echo "<br />ERROR: " . $nowurl;
        }
    }
	
	//Get needed times
	$TIMES = 1;
	if( $SPIDER == 'all'
    || $SPIDER == 'self' 
    || $SPIDER == 'self1'
	){
        $TIMES = 10;
	}elseif( is_numeric( $SPIDER ) ){
        $TIMES = (int)$SPIDER;
	}
	$scrapped = array();
	webspider_search( $URL, $TIMES, $TIMES, $SPIDER, $scrapped );
	
?>
