<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//ALL SCRAPPERS
	
    function scrap_all_nearest_title( $title, $type = TRUE, $filterurl = 'imdb.com/title/tt', $site = 'imdb.com' ){
        $result = FALSE;
        
        $result = scrap_ddg_nearest_title( $title, $type, $filterurl, $site );
        if( getIMDB_ID( $result ) != $result
        ){
            $result = scrap_bing_nearest_title( $title, $type, $filterurl, $site );
        }
        
        return $result;
    }
    
    function scrap_all( $search, $filterurl = '', $site = '', $timed = FALSE ){
        $result = FALSE;
        $rd = array();
        
        $rd[] = scrap_duckduckgo( $search, $filterurl, $site, $timed );
        $rd[] = scrap_bing( $search, $filterurl, $site, $timed );
        
        foreach( $rd AS $d ){
            if( is_array( $d ) 
            && count( $d ) > 0
            ){
                if( !is_array( $result ) ){
                    $result = array();
                }
                $result = array_merge( $result, $d );
            }
        }
        
        return $result;
    }
    
    
    //DUCKDUCKGO SEARCH SCRAPPING
    
    function scrap_duckduckgo( $search, $filterurl = '', $site = '', $timed = FALSE ){
        //SECURE TIME TO SPAM
        if( $timed ) sleep( 30 );
        $result = array();
        $URL = 'https://duckduckgo.com/html/?';
        if( strlen( $site ) > 0 ){
            $URL .= 'q=' . urlencode( $search. ' site:' . $site );
        }else{
            $URL .= 'q=' . urlencode( $search );
        }
        $URL .= '&s=0';
        
        $data = @file_get_contents_timed( $URL );
        
        // Create a new DOMDocument
        $dom = new DOMDocument();
        @$dom->loadHTML( mb_convert_encoding( $data, 'HTML-ENTITIES', 'utf-8' ) );
        $xp = new DOMXPath($dom);
        $nodes = $xp->query('//a[@class="result__a"]');
        foreach ( $nodes as $re ){
            $href = $re->getAttribute( "href" );
            $href = substr( $href, stripos( $href, 'http' ) );
            $href = urldecode( $href );
            $title = $re->nodeValue;
            if( strlen( $filterurl ) == 0
            || stripos( $href, $filterurl ) !== FALSE
            ){
                $result[ $title ] = $href;
            }
        }
        
        return $result;
    }
    
    function scrap_ddg_nearest_title( $title, $type = TRUE, $filterurl = 'imdb.com/title/tt', $site = 'imdb.com' ){
        $result = '';
        if( $type ){
            $STYPE = 'Movie';
        }else{
            $STYPE = 'TV';
        }
        $search = O_LANG . ' ' . $STYPE . ' ' . $title;
        if( ( $links = scrap_duckduckgo( $search, $filterurl, $site, TRUE ) ) != FALSE 
        && count( $links ) > 0
        ){
            //Clean Title - imdb | imdb -
            $l2 = array();
            $CLEAN = array( '- imdb', 'imdb -' );
            foreach( $links AS $t => $href ){
                if( getIMDB_ID( $href ) ){
                    foreach( $CLEAN AS $c ){
                        $t = str_ireplace( $c, '', $t );
                    }
                    $l2[ trim( $t ) ] = $href;
                }
            }
            $links = $l2;
        }
        $now_k = 0;
        $now_k_sim = 0;
        foreach( $links AS $t => $href ){
            if( similar_text( $title, $t, $pc ) > $now_k_sim ){
                $now_k_sim = $pc;
                $now_k = $t;
            }
        }
        if( array_key_exists( $now_k, $links ) 
        && ( $result = getIMDB_ID( $links[ $now_k ] ) ) != FALSE
        ){
            
        }else{
            $result = '';
        }
        return $result;
    }
    
    //BING SEARCH SCRAPPING
    
    function scrap_bing( $search, $filterurl = '', $site = '', $timed = FALSE ){
        //SECURE TIME TO SPAM
        if( $timed ) sleep( 30 );
        $result = array();
        $URL = 'https://bing.com/search?';
        if( strlen( $site ) > 0 ){
            $URL .= 'q=' . urlencode( ' site:' . $site . ' ' . $search );
        }else{
            $URL .= 'q=' . urlencode( $search );
        }
        $URL .= '&format=rss';
        
        $data = @file_get_contents_timed( $URL );
        //XML DOC
        if( ( $xml = @simplexml_load_string( $data ) ) != FALSE
		&& ( $xml = object2array( $xml ) ) != FALSE
		&& array_key_exists( 'channel', $xml )
		&& array_key_exists( 'item', $xml[ 'channel' ] )
		&& is_array( $xml[ 'channel' ][ 'item' ] )
		){
            foreach( $xml[ 'channel' ][ 'item' ] AS $item ){
                $title = $item[ 'title' ];
                $href = $item[ 'link' ];
                if( strlen( $filterurl ) == 0
                || stripos( $href, $filterurl ) !== FALSE
                ){
                    $result[ $title ] = $href;
                }
            }
		}
        
        return $result;
    }
    
    function scrap_bing_nearest_title( $title, $type = TRUE, $filterurl = 'imdb.com/title/tt', $site = 'imdb.com' ){
        $result = '';
        if( $type ){
            $STYPE = 'Movie';
        }else{
            $STYPE = 'TV';
        }
        $search = O_LANG . ' ' . $STYPE . ' ' . $title;
        if( ( $links = scrap_bing( $search, $filterurl, $site, TRUE ) ) != FALSE 
        && count( $links ) > 0
        ){
            //Clean Title - imdb | imdb -
            $l2 = array();
            $CLEAN = array( '- imdb', 'imdb -' );
            foreach( $links AS $t => $href ){
                if( getIMDB_ID( $href ) ){
                    foreach( $CLEAN AS $c ){
                        $t = str_ireplace( $c, '', $t );
                    }
                    $l2[ trim( $t ) ] = $href;
                }
            }
            $links = $l2;
        }
        $now_k = 0;
        $now_k_sim = 0;
        foreach( $links AS $t => $href ){
            if( similar_text( $title, $t, $pc ) > $now_k_sim ){
                $now_k_sim = $pc;
                $now_k = $t;
            }
        }
        if( array_key_exists( $now_k, $links ) 
        && ( $result = getIMDB_ID( $links[ $now_k ] ) ) != FALSE
        ){
            
        }else{
            $result = '';
        }
        return $result;
    }
    
    //IMGS DOWNLOADS
    
	function getActorFile( $name ) {
		$result = FALSE;
		
		$fileactor = PPATH_MEDIAINFO . DS . $name;
		if( !file_exists( $fileactor )
		&& ( $imgs = searchImages( 'actor photo ' . $name, 20 ) ) != FALSE 
		&& count( $imgs ) > 0
		){
			$x = 0;
			while( $x < count( $imgs )
			&& !downloadPosterToFile( $imgs[ $x ], $fileactor )
			){
				$x++;
			}
		}elseif( isset( $imgs )
		&& is_array( $imgs )
		&& count( $imgs ) == 0
		){
            
		}
		
		if( file_exists( $fileactor ) ){
			$result = $fileactor;
		}else{
			$result = PPATH_IMGS . DS . 'u.png';
		}
		
		return $result;
	}
	
	function searchImages( $search, $max = 5, $getthumb = TRUE ) {
		$result = array();
		
		if( ( $links = searchImagesBing( $search, $max, $getthumb ) ) != FALSE ){
            $result = array_merge( $result, $links );
		}
		if( count( $result ) < $max
		&& ( $links = searchImagesIXQick( $search, ( $max - count( $result  ) ), $getthumb ) ) != FALSE ){
            $result = array_merge( $result, $links );
		}
		if( count( $result ) < $max
		&& ( $links = searchImagesDuckDuckGo( $search, ( $max - count( $result  ) ), $getthumb ) ) != FALSE ){
            $result = array_merge( $result, $links );
		}
		//Captchas
		if( count( $result ) < $max
		&& ( $links = searchImagesYandex( $search, ( $max - count( $result  ) ), $getthumb ) ) != FALSE ){
            $result = array_merge( $result, $links );
		}
		
		return $result;
	}
	
	function searchImagesIXQick( $search, $max = 5, $getthumb = TRUE ){
		$result = array();
		
		$url = "https://www.ixquick.com/do/search?cat=pics&query=" . urlencode( $search ) . "";
		if( ( $html = @file_get_contents_timed( $url ) ) != FALSE ){
			$result = array();

			$doc = new DOMDocument();
			@$doc->loadHTML($html);
            
            if( $getthumb ){
                $divs = $doc->getElementsByTagName('div');
                $n = 0;
                foreach( $divs AS $div ){
                    if( $div->getAttribute('class') == 'img_box' ){
                        //THUMB IMG
                        $tags = $div->getElementsByTagName('img');
                        $n = 0;
                        
                        foreach ($tags as $tag) {
                            //url thumb
                            $turl = $tag->getAttribute('src');
                            //var_dump( $turl );
                            //duckduckgoim
                            if( startsWith( $turl, '//' ) ){
                                $turl = 'https:' . $turl;
                            }
                            if( filter_var( $turl, FILTER_VALIDATE_URL )
                            ){
                                $result[] = $turl;
                            }
                            if( $n > $max ) break;
                            $n++;
                        }
                    }
                    if( $n > $max ) break;
                }
            }else{
                //TARGET IMG
            }
		}
		
		return $result;
	}
	
	//captcha sometimes
	function searchImagesYandex( $search, $max = 5, $getthumb = TRUE ){
		$result = array();
		
		$url = "https://yandex.com/images/search?text=" . urlencode( $search ) . "";
		if( ( $html = @file_get_contents_timed( $url ) ) != FALSE ){
			$result = array();

			$doc = new DOMDocument();
			@$doc->loadHTML($html);
            
            if( $getthumb ){
                //THUMB IMG
                $tags = $doc->getElementsByTagName('img');
                $n = 0;
                
                foreach ($tags as $tag) {
                    //url thumb
                    $turl = $tag->getAttribute('src');
                    //var_dump( $turl );
                    //duckduckgoim
                    if( startsWith( $turl, '//' ) ){
                        $turl = 'https:' . $turl;
                    }
                    if( filter_var( $turl, FILTER_VALIDATE_URL )
                    ){
                        if( stripos( $turl, 'captcha' ) === FALSE ){
                            $result[] = $turl;
                        }
                    }
                    if( $n > $max ) break;
                    $n++;
                }
            }else{
                //TARGET IMG
            }
		}
		
		return $result;
	}
	
	function searchImagesBing( $search, $max = 5, $getthumb = TRUE ){
		$result = array();
		
		//image resulution+wide: &qft=+filterui:imagesize-large+filterui:aspect-tall
		$url = "https://www.bing.com/images/search?q=" . urlencode( $search ) . "&FORM=HDRSC2";
		//$url = "https://duckduckgo.com/?q=" . urlencode( $search ) . "&iax=1&ia=images";
		if( ( $html = @file_get_contents_timed( $url ) ) != FALSE ){
			$result = array();

			$doc = new DOMDocument();
			@$doc->loadHTML($html);
            
            if( $getthumb ){
                //THUMB IMG
                $tags = $doc->getElementsByTagName('img');
                $n = 0;
                
                foreach ($tags as $tag) {
                    //url thumb
                    $turl = $tag->getAttribute('src');
                    //var_dump( $turl );
                    //duckduckgoim
                    if( startsWith( $turl, '//' ) ){
                        $turl = 'https:' . $turl;
                    }
                    if( filter_var( $turl, FILTER_VALIDATE_URL )
                    ){
                        $result[] = $turl;
                    }
                    if( $n > $max ) break;
                    $n++;
                }
            }else{
                //TARGET IMG
            }
		}
		
		return $result;
	}
	
	function searchImagesDuckDuckGo( $search, $max = 5, $getthumb = TRUE ){
		$result = array();
		
		$url = "https://duckduckgo.com/?q=" . urlencode( $search ) . "&t=hj&iax=images&ia=images";
		if( ( $html = @file_get_contents_timed( $url ) ) != FALSE ){
			$result = array();

			$doc = new DOMDocument();
			@$doc->loadHTML($html);
            
            if( $getthumb ){
                //THUMB IMG
                $tags = $doc->getElementsByTagName('img');
                $n = 0;
                
                foreach ($tags as $tag) {
                    $class = $tag->getAttribute( 'class' );
                    if( stripos( $class, 'tile--img' ) !== FALSE ){
                        //url thumb
                        $turl = $tag->getAttribute( 'src' );
                        //var_dump( $turl );
                        //duckduckgoim
                        if( startsWith( $turl, '//' ) ){
                            $turl = 'https:' . $turl;
                        }
                        if( filter_var( $turl, FILTER_VALIDATE_URL )
                        ){
                            $result[] = $turl;
                        }
                        if( $n > $max ) break;
                        $n++;
                    }
                }
            }else{
                //TARGET IMG
            }
		}
		
		return $result;
	}
	
	function downloadPosterToFile( $url, $file ){
		$result = @file_put_contents( $file, file_get_contents_timed( $url ) );
		
		if( filesize( $file ) < 500 ){
			@unlink( $file );
			$result = FALSE;
		}elseif( !getFileMimeTypeImg( $file )
		){
            @unlink( $file );
            $result = FALSE;
		}
		
		return $result;
		
	}
	
	//DETECT IMDB ID ttXXXXXXX
	
	function getIMDB_ID( $url ){
        $result = FALSE;
        
        if( preg_match( "/tt[0-9]{7,10}/", $url, $match ) 
        && is_array( $match )
        && count( $match ) > 0
        && strlen( $match[ 0 ] ) > 8
        ){
            $result = $match[ 0 ];
        }
        
        return $result;
	}
	
	//GET URL WITH MAX TIME
	
	function file_get_contents_timed_basic( $url, $time = 5, $sessionid = '' ){
        
        if( strlen( $sessionid ) > 0 ){
            $ext_header = "\r\nCookie: PHPSESSID=" . $sessionid . "\r\n";
        }else{
            $ext_header = '';
        }
        
        $strdata = array(
            'http'=> array(
                'method' => "GET",
                'header' => "Accept-language: " . O_LANG . $ext_header,
                'timeout' => $time
            ),
            "ssl"=>array(
                "verify_peer" => FALSE,
                "verify_peer_name" => FALSE,
            ),
        );
        
        $ctx = stream_context_create( $strdata );
        
        return file_get_contents( $url, FALSE, $ctx );
	}
	
	function file_get_contents_timed( $url, $time = 5, $sessionid = '', $debug = FALSE ){
        
        $curl = curl_init();

        $header = array();
        $header[] = "Accept: text/xml,application/xml,application/json,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        //q=0.7,en;q=0.3
        $header[] = "Accept-Language: " . O_LANG . ";";
        if( strlen( $sessionid ) > 0 ){
            $header[] = "Cookie: PHPSESSID=" . $sessionid . "";
        }
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: " . $time;
        $header[] = "Pragma: ";
        $header[] = "Referer: ";
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $curl, CURLOPT_HEADER, FALSE );
        
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_REFERER, '' );
        //curl_setopt( $curl, CURLOPT_COOKIEJAR, "cookie.txt" );
        //curl_setopt( $curl, CURLOPT_COOKIEFILE, "cookie.txt" );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $time );
        curl_setopt( $curl, CURLOPT_COOKIESESSION, TRUE );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9.0.7) Gecko/2009021910 Firefox/3.0.7 (.NET CLR 3.5.30729)');
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, TRUE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_VERBOSE, TRUE );
        
        //DEBUG
        if( $debug ){
            ob_start();  
            $out = fopen('php://output', 'w');
            curl_setopt($curl, CURLOPT_STDERR, $out );
        }
        
        $response = curl_exec( $curl );
        
        //DEBUG
        if( $debug ){
            fclose( $out );
            $debug = ob_get_clean();
            var_dump( $debug );
        }
        
        curl_close( $curl );

        return $response;
	}
	
	function file_get_contents_timed_post( $url, $post_data, $time = 5, $sessionid = '', $debug = FALSE ){
        
        $curl = curl_init();

        $header = array();
        $header[] = "Accept: text/xml,application/xml,application/json,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
        $header[] = "Accept-Language: " . O_LANG . ";";
        if( strlen( $sessionid ) > 0 ){
            $header[] = "Cookie: PHPSESSID=" . $sessionid . "";
        }
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: " . $time;
        $header[] = "Pragma: ";
        $header[] = "Referer: ";
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $curl, CURLOPT_HEADER, FALSE );
        
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_REFERER, '' );
        //curl_setopt( $curl, CURLOPT_COOKIEJAR, "cookie.txt" );
        //curl_setopt( $curl, CURLOPT_COOKIEFILE, "cookie.txt" );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $time );
        curl_setopt( $curl, CURLOPT_COOKIESESSION, TRUE );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9.0.7) Gecko/2009021910 Firefox/3.0.7 (.NET CLR 3.5.30729)');
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, TRUE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_VERBOSE, TRUE );
        
        //POST
        
        curl_setopt( $curl, CURLOPT_POST, TRUE );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query( $post_data ) );
        
        //DEBUG
        if( $debug ){
            ob_start();  
            $out = fopen('php://output', 'w');
            curl_setopt($curl, CURLOPT_STDERR, $out );
        }
        
        $response = curl_exec( $curl );
        
        //DEBUG
        if( $debug ){
            fclose( $out );
            $debug = ob_get_clean();
            var_dump( $debug );
        }
        
        curl_close( $curl );

        return $response;
	}
	
	function file_get_contents_timed_post_json( $url, $post_data, $time = 5, $sessionid = '', $auth = '', $debug = FALSE ){
        
        $curl = curl_init();

        $header = array();
        $header[] = 'Content-Type: application/json';
        if( strlen( $auth ) > 0 ){
            $header[] = $auth;
        }
        $header[] = "Accept: application/json";
        $header[] = "Accept-Language: " . O_LANG . ";";
        if( strlen( $sessionid ) > 0 ){
            $header[] = "Cookie: PHPSESSID=" . $sessionid . "";
        }
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: " . $time;
        $header[] = "Pragma: ";
        $header[] = "Referer: ";
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $curl, CURLOPT_HEADER, FALSE );
        
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_REFERER, '' );
        //curl_setopt( $curl, CURLOPT_COOKIEJAR, "cookie.txt" );
        //curl_setopt( $curl, CURLOPT_COOKIEFILE, "cookie.txt" );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $time );
        curl_setopt( $curl, CURLOPT_COOKIESESSION, TRUE );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9.0.7) Gecko/2009021910 Firefox/3.0.7 (.NET CLR 3.5.30729)');
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, TRUE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_VERBOSE, TRUE );
        
        //POST
        
        curl_setopt( $curl, CURLOPT_POST, TRUE );
        curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $post_data ) );
        
        //DEBUG
        if( $debug ){
            ob_start();  
            $out = fopen('php://output', 'w');
            curl_setopt($curl, CURLOPT_STDERR, $out );
        }
        
        $response = curl_exec( $curl );
        
        //DEBUG
        if( $debug ){
            fclose( $out );
            $debug = ob_get_clean();
            var_dump( $debug );
        }
        
        curl_close( $curl );

        return $response;
	}
	
	function file_get_contents_timed_get_json( $url, $time = 5, $sessionid = '', $auth = '', $debug = FALSE ){
        
        $curl = curl_init();

        $header = array();
        $header[] = 'Content-Type: application/json';
        if( strlen( $auth ) > 0 ){
            $header[] = $auth;
        }
        $header[] = "Accept: application/json";
        $header[] = "Accept-Language: " . O_LANG . ";";
        if( strlen( $sessionid ) > 0 ){
            $header[] = "Cookie: PHPSESSID=" . $sessionid . "";
        }
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: " . $time;
        $header[] = "Pragma: ";
        $header[] = "Referer: ";
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $curl, CURLOPT_HEADER, FALSE );
        
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_REFERER, '' );
        //curl_setopt( $curl, CURLOPT_COOKIEJAR, "cookie.txt" );
        //curl_setopt( $curl, CURLOPT_COOKIEFILE, "cookie.txt" );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $time );
        curl_setopt( $curl, CURLOPT_COOKIESESSION, TRUE );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9.0.7) Gecko/2009021910 Firefox/3.0.7 (.NET CLR 3.5.30729)');
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, TRUE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $curl, CURLOPT_VERBOSE, TRUE );
        
        //DEBUG
        if( $debug ){
            ob_start();  
            $out = fopen('php://output', 'w');
            curl_setopt($curl, CURLOPT_STDERR, $out );
        }
        
        $response = curl_exec( $curl );
        
        //DEBUG
        if( $debug ){
            fclose( $out );
            $debug = ob_get_clean();
            var_dump( $debug );
        }
        
        curl_close( $curl );

        return $response;
	}
?>
