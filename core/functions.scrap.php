<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//ALL SCRAPPERS
	
    function scrap_all_nearest_title( $title, $type = TRUE, $filterurl = 'imdb.com/title/tt', $site = 'imdb.com' ){
        $result = FALSE;
        $debug = FALSE;
        
        //random select search prevent captchas
        $searchers = array(
            'scrap_ddg_nearest_title',
            'scrap_bing_nearest_title',
            'scrap_startpage_nearest_title',
            'scrap_searchencrypt_nearest_title',
            'scrap_searx_nearest_title',
        );
        
        //serch 2 times, enought to know viable title
        $rnd = mt_rand( 0, count( $searchers ) - 1 );
        if( $debug ) echo "<br />SCRAPALLNEAREST-SEARCHER: " . $searchers[ $rnd ];
        if( ( $rd = $searchers[ $rnd ]( $title, $type, $filterurl, $site ) ) != FALSE 
        && getIMDB_ID( $rd ) != FALSE
        ){
            $result = $rd;
        }else{
            while( ( $rnd2 = mt_rand( 0, count( $searchers ) - 1 ) ) == $rnd ){
            }
            if( $debug ) echo "<br />SCRAPALLNEAREST-SEARCHER2: " . $searchers[ $rnd2 ];
            if( ( $rd = $searchers[ $rnd2 ]( $title, $type, $filterurl, $site ) ) != FALSE 
            && getIMDB_ID( $rd ) != FALSE
            ){
                $result = $rd;
            }
        }
        if( $debug ) echo "<br />SCRAPALLNEAREST-RESULT: " . $result;
        
        return $result;
    }
    
    function scrap_all( $search, $filterurl = '', $site = '', $timed = FALSE ){
        $result = FALSE;
        $rd = array();
        $debug = FALSE;
        
        //random select search prevent captchas
        $searchers = array(
            'scrap_duckduckgo',
            'scrap_bing',
            'scrap_startpage',
            'scrap_searchencrypt',
            'scrap_searx',
        );
        
        //serch 2 times, enought to know viable title
        $rnd = mt_rand( 0, count( $searchers ) - 1 );
        if( $debug ) echo "<br />SCRAPALL-SEARCHER: " . $searchers[ $rnd ];
        if( ( $rd = $searchers[ $rnd ]( $search, $filterurl, $site, $timed ) ) != FALSE 
        && is_array( $rd )
        && count( $rd ) > 0
        ){
            $result = $rd;
        }else{
            while( ( $rnd2 = mt_rand( 0, count( $searchers ) - 1 ) ) == $rnd ){
            }
            if( $debug ) echo "<br />SCRAPALL-SEARCHER2: " . $searchers[ $rnd2 ];
            if( ( $rd = $searchers[ $rnd2 ]( $search, $filterurl, $site, $timed ) ) != FALSE 
            && is_array( $rd )
            && count( $rd ) > 0
            ){
                $result = $rd;
            }
        }
        if( $debug ) echo "<br />SCRAPALL-RESULT: " . nl2br( print_r( $result, TRUE ) );
        
        return $result;
    }
    
    
    //DUCKDUCKGO SEARCH SCRAPPING
    
    function scrap_duckduckgo( $search, $filterurl = '', $site = '', $timed = FALSE ){
        //SECURE TIME TO SPAM
        if( $timed ) sleep( 5 );
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
            if( strSimilarity( $title, $t, $pc ) > $now_k_sim ){
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
        if( $timed ) sleep( 5 );
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
                if( is_array( $item )
                && array_key_exists( 'title', $item ) 
                && array_key_exists( 'link', $item ) 
                ){
                    $title = $item[ 'title' ];
                    $href = $item[ 'link' ];
                    if( strlen( $filterurl ) == 0
                    || stripos( $href, $filterurl ) !== FALSE
                    ){
                        $result[ $title ] = $href;
                    }
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
            if( strSimilarity( $title, $t, $pc ) > $now_k_sim ){
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
    
    //STARTPAGE SEARCH SCRAPPING
    
    function scrap_startpage( $search, $filterurl = '', $site = '', $timed = FALSE ){
        //SECURE TIME TO SPAM
        if( $timed ) sleep( 5 );
        $result = array();
        $URL = 'https://www.startpage.com/do/dsearch?';
        if( strlen( $site ) > 0 ){
            $URL .= 'query=' . urlencode( $search. ' site:' . $site );
        }else{
            $URL .= 'query=' . urlencode( $search );
        }
        $URL .= '&s=0';
        
        $data = @file_get_contents_timed( $URL );
        
        // Create a new DOMDocument
        $dom = new DOMDocument();
        @$dom->loadHTML( mb_convert_encoding( $data, 'HTML-ENTITIES', 'utf-8' ) );
        $xp = new DOMXPath($dom);
        $nodes = $xp->query('//a[starts-with(@id, "title_")]');
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
    
    function scrap_startpage_nearest_title( $title, $type = TRUE, $filterurl = 'imdb.com/title/tt', $site = 'imdb.com' ){
        $result = '';
        if( $type ){
            $STYPE = 'Movie';
        }else{
            $STYPE = 'TV';
        }
        $search = O_LANG . ' ' . $STYPE . ' ' . $title;
        if( ( $links = scrap_startpage( $search, $filterurl, $site, TRUE ) ) != FALSE 
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
            if( strSimilarity( $title, $t, $pc ) > $now_k_sim ){
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
    
    //searchencrypt SEARCH SCRAPPING
    
    function scrap_searchencrypt( $search, $filterurl = '', $site = '', $timed = FALSE ){
        //SECURE TIME TO SPAM
        if( $timed ) sleep( 5 );
        $result = array();
        $URL = 'https://www.searchencrypt.com/search?';
        if( strlen( $site ) > 0 ){
            $URL .= 'eq=' . urlencode( $search. ' site:' . $site );
        }else{
            $URL .= 'eq=' . urlencode( $search );
        }
        $URL .= '&s=0';
        
        $data = @file_get_contents_timed( $URL );
        
        // Create a new DOMDocument
        $dom = new DOMDocument();
        @$dom->loadHTML( mb_convert_encoding( $data, 'HTML-ENTITIES', 'utf-8' ) );
        $xp = new DOMXPath($dom);
        $nodes = $xp->query('//a[@class="result-link"]');
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
    
    function scrap_searchencrypt_nearest_title( $title, $type = TRUE, $filterurl = 'imdb.com/title/tt', $site = 'imdb.com' ){
        $result = '';
        if( $type ){
            $STYPE = 'Movie';
        }else{
            $STYPE = 'TV';
        }
        $search = O_LANG . ' ' . $STYPE . ' ' . $title;
        if( ( $links = scrap_searchencrypt( $search, $filterurl, $site, TRUE ) ) != FALSE 
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
            if( strSimilarity( $title, $t, $pc ) > $now_k_sim ){
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
    
    //searx.me SEARCH SCRAPPING
    
    function scrap_searx( $search, $filterurl = '', $site = '', $timed = FALSE ){
        //SECURE TIME TO SPAM
        if( $timed ) sleep( 5 );
        $result = array();
        $URL = 'https://searx.me/?';
        if( strlen( $site ) > 0 ){
            $URL .= 'q=' . rawurlencode( $search. ' site:' . $site );
        }else{
            $URL .= 'q=' . rawurlencode( $search );
        }
        $URL .= '';
        
        $data = @file_get_contents_timed( $URL );
        
        // Create a new DOMDocument
        $dom = new DOMDocument();
        @$dom->loadHTML( mb_convert_encoding( $data, 'HTML-ENTITIES', 'utf-8' ) );
        $xp = new DOMXPath($dom);
        $nodes = $xp->query('//h4[@class="result_header"]/a');
        //$nodes = $xp->query('//a[@rel="noreferrer"]');
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
    
    function scrap_searx_nearest_title( $title, $type = TRUE, $filterurl = 'imdb.com/title/tt', $site = 'imdb.com' ){
        $result = '';
        if( $type ){
            $STYPE = 'Movie';
        }else{
            $STYPE = 'TV';
        }
        $search = O_LANG . ' ' . $STYPE . ' ' . $title;
        if( ( $links = scrap_searx( $search, $filterurl, $site, TRUE ) ) != FALSE 
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
            if( strSimilarity( $title, $t, $pc ) > $now_k_sim ){
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
    
    //ACTOR FILE DIRECT DOWNLOAD
    
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
	
	//GENERIC IN ALL SEARCH IMAGES
	
	function searchImages( $search, $max = 5, $getthumb = TRUE ) {
		$result = array();
		$debug = FALSE;
		$in_list = array();
		
		$list = array( 'searchImagesPyMI', 'searchImagesBing', 'searchImagesIXQick', 'searchImagesWebcrawler' );
		
		if( $debug ) echo "<br />SEARCHIMAGES: " . $search;
		
		while( count( $result ) < $max 
		&& count( $in_list ) < ( count( $list ) - 1 )
		){
            $rnd = mt_rand( 0, ( count( $list ) - 1 ) );
            if( array_key_exists( $rnd, $list ) 
            && function_exists( $list[ $rnd ] )
            && !in_array( $rnd, $in_list )
            ){
                if( $debug ) echo "<br />SEARCHIMAGES-SEARCHER: " . $list[ $rnd ];
                $in_list[] = $rnd;
                if( ( $links = $list[ $rnd ]( $search, $max, $getthumb ) ) != FALSE 
                && is_array( $links )
                && count( $links ) > 0
                ){
                    $result = array_merge( $result, $links );
                    if( $debug ) echo "<br />SEARCHIMAGES-SEARCHER-FINDED: " . count( $links );
                }
            }
        }
		
		$result = array_slice( $result, 0, $max );
		
		return $result;
	}
	
	//WebCrawler SEARCH IMAGES
	
	function searchImagesWebcrawler( $search, $max = 5, $getthumb = TRUE ){
		$result = array();
		
		$url = "https://www.webcrawler.com/info.wbcrwl.udog/search/images?q=" . urlencode( $search ) . "";
		if( ( $html = @file_get_contents_timed( $url ) ) != FALSE ){
			$result = array();

			$doc = new DOMDocument();
			@$doc->loadHTML($html);
            
            //file_put_contents( PPATH_CACHE . DS . 'Webcrawler-html-' . date( 'd-m-y-H-i-s' ), $html );
            
            if( $getthumb ){
                $divs = $doc->getElementsByTagName( 'img' );
                $n = 0;
                foreach( $divs AS $div ){
                    if( $div->getAttribute('class') == '' ){
                        $turl = $div->getAttribute('src');
                        //var_dump( $turl );
                        if( startsWith( $turl, '//' ) ){
                            $turl = 'https:' . $turl;
                        }
                        if( filter_var( $turl, FILTER_VALIDATE_URL )
                        ){
                            $result[] = $turl;
                            $n++;
                            if( $n > $max ) break;
                        }
                    }
                    if( $n > $max ) break;
                }
            }else{
                //TARGET IMG
                $divs = $doc->getElementsByTagName('a');
                $n = 0;
                foreach( $divs AS $div ){
                    if( $div->getAttribute('class') == 'link' ){
                        $turl = $div->getAttribute('href');
                        //var_dump( $turl );
                        if( startsWith( $turl, '//' ) ){
                            $turl = 'https:' . $turl;
                        }
                        if( filter_var( $turl, FILTER_VALIDATE_URL )
                        ){
                            $result[] = $turl;
                            $n++;
                            if( $n > $max ) break;
                        }
                    }
                    if( $n > $max ) break;
                }
                //echo htmlspecialchars( $html );
                //var_dump( $result );die();
            }
		}
		
		return $result;
	}
	
	//IXQick SEARCH IMAGES
	
	function searchImagesIXQick( $search, $max = 5, $getthumb = TRUE ){
		$result = array();
		
		$url = "https://www.ixquick.com/do/search?cat=pics&query=" . urlencode( $search ) . "";
		if( ( $html = @file_get_contents_timed( $url ) ) != FALSE ){
			$result = array();

			$doc = new DOMDocument();
			@$doc->loadHTML($html);
            
            //file_put_contents( PPATH_CACHE . DS . 'IXQick-html-' . date( 'd-m-y-H-i-s' ), $html );
            
            if( $getthumb ){
                $divs = $doc->getElementsByTagName('ul');
                $n = 0;
                foreach( $divs AS $div ){
                    if( $div->getAttribute('class') == 'image-results' ){
                        //THUMB IMG
                        $tags = $div->getElementsByTagName('img');
                        $n = 0;
                        
                        foreach ($tags as $tag) {
                            //url thumb
                            $turl = $tag->getAttribute('data-src');
                            //var_dump( $turl );
                            //duckduckgoim
                            if( startsWith( $turl, '//' ) ){
                                $turl = 'https:' . $turl;
                            }
                            if( filter_var( $turl, FILTER_VALIDATE_URL )
                            ){
                                $result[] = $turl;
                                $n++;
                                if( $n > $max ) break;
                            }
                        }
                    }
                    if( $n > $max ) break;
                }
            }else{
                //TARGET IMG
                $divs = $doc->getElementsByTagName('ul');
                $n = 0;
                foreach( $divs AS $div ){
                    if( $div->getAttribute('class') == 'image-results' ){
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
                                $n++;
                                if( $n > $max ) break;
                            }
                        }
                    }
                    if( $n > $max ) break;
                }
                //echo htmlspecialchars( $html );
                //var_dump( $result );die();
            }
		}
		
		return $result;
	}
	
	//Yandex SEARCH IMAGES
	
	//captcha sometimes
	function searchImagesYandex( $search, $max = 5, $getthumb = TRUE ){
		$result = array();
		
		$url = "https://yandex.com/images/search?text=" . urlencode( $search ) . "";
		if( ( $html = @file_get_contents_timed( $url ) ) != FALSE ){
			$result = array();

			$doc = new DOMDocument();
			@$doc->loadHTML($html);
            
            //file_put_contents( PPATH_CACHE . DS . 'Yandex-html-' . date( 'd-m-y-H-i-s' ), $html );
            
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
                //not direct link, get thumb
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
            }
		}
		
		return $result;
	}
	
	//Bing SEARCH IMAGES
	
	function searchImagesBing( $search, $max = 5, $getthumb = TRUE ){
		$result = array();
		
		//force thumbs because dd img not found
		//$getthumb = TRUE;
		
		//image resulution+wide: &qft=+filterui:imagesize-large+filterui:aspect-tall
		$url = "https://www.bing.com/images/search?q=" . urlencode( $search ) . "&FORM=HDRSC2";
		//$url = "https://duckduckgo.com/?q=" . urlencode( $search ) . "&iax=1&ia=images";
		if( ( $html = @file_get_contents_timed( $url ) ) != FALSE ){
			$result = array();
            
			$doc = new DOMDocument();
			@$doc->loadHTML($html);
            
            //file_put_contents( PPATH_CACHE . DS . 'bing-html-' . date( 'd-m-y-H-i-s' ), $html );
            
            if( $getthumb ){
                //THUMB IMG
                $tags = $doc->getElementsByTagName('img');
                $n = 0;
                
                foreach ($tags as $tag) {
                    if( $tag->getAttribute('class') == 'mimg' ){
                        //url thumb
                        $turl = $tag->getAttribute('src');
                        //var_dump( $turl );
                        if( startsWith( $turl, '//' ) ){
                            $turl = 'https:' . $turl;
                        }
                        if( startsWith( $turl, '/th' ) ){
                            $turl = 'https://www.bing.com' . $turl;
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
                $tags = $doc->getElementsByTagName('a');
                $n = 0;
                
                foreach ($tags as $tag) {
                    if( strlen( ( $html2 = $tag->getAttribute('m') ) ) > 0 ){
                        //var_dump( $html2 );
                        $pattern = '/\"murl\"\:\"(.*?)\"\,/';
                        //$pattern = '/\<a\ class\=\"thumb\"\ target\=\"\_blank\"\ href\=\"(.*?)\"/';
                        if( preg_match_all( $pattern, $html2, $match)
                        && array_key_exists( 1, $match )
                        && count( $match[ 1 ] ) > 0
                        ){
                            $n = 0;
                            foreach( $match[ 1 ] AS $turl ){
                                if( $turl ){
                                    if( filter_var( $turl, FILTER_VALIDATE_URL )
                                    ){
                                        $result[] = $turl;
                                    }
                                }
                                if( $n > $max ) break;
                                $n++;
                            }
                        }
                    }
                }
                //echo htmlspecialchars( $html );
                //var_dump( $result );die();
            }
		}
		
		return $result;
	}
	
	//DuckDuckGo SEARCH IMAGES
	
	function searchImagesDuckDuckGo( $search, $max = 5, $getthumb = TRUE ){
		$result = array();
		
		$url = "https://duckduckgo.com/?q=" . urlencode( $search ) . "&t=hj&iax=images&ia=images";
		if( ( $html = @file_get_contents_timed( $url ) ) != FALSE ){
			$result = array();

			$doc = new DOMDocument();
			@$doc->loadHTML($html);
            
            //file_put_contents( PPATH_CACHE . DS . 'DuckDuckGo-html-' . date( 'd-m-y-H-i-s' ), $html );
            
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
                $pattern = '/\<a\ class\=\"thumb\"\ target\=\"\_blank\"\ href\=\"(.*?)\"/';
                if( preg_match_all( $pattern, $html, $match)
                && array_key_exists( 1, $match )
                && count( $match[ 1 ] ) > 0
                ){
                    $n = 0;
                    foreach( $match[ 1 ] AS $turl ){
                        if( $turl ){
                            if( filter_var( $turl, FILTER_VALIDATE_URL )
                            ){
                                $result[] = $turl;
                            }
                        }
                        if( $n > $max ) break;
                        $n++;
                    }
                }else{
                    //THUMB IMG
                    
                }
                //echo htmlspecialchars( $html );
                //var_dump( $result );die();
            }
		}
		
		return $result;
	}
	
	//DOWNLOAD IMG FILE
	
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
	
	//DETECT thetvdb ID &id=XXXXXX
	
	function getTHETVDB_ID( $url ){
        $result = FALSE;
        
        if( preg_match( "/id=[0-9]{1,10}/", $url, $match ) 
        && is_array( $match )
        && count( $match ) > 0
        && strlen( $match[ 0 ] ) > 8
        ){
            $result = str_ireplace( 'id=' , '', $match[ 0 ] );
        }
        
        return $result;
	}
	
	//DETECT themoviedb.com ID /movie/XXXXXX
	
	function getTHEMOVIEDB_ID( $url ){
        $result = FALSE;
        
        if( preg_match( "/\/movie\/[0-9]{1,10}/", $url, $match ) 
        && is_array( $match )
        && count( $match ) > 0
        && strlen( $match[ 0 ] ) > 8
        ){
            $result = str_ireplace( '/movie/' , '', $match[ 0 ] );
        }
        
        return $result;
	}
	
	//DETECT filmaffinity.com ID /filmXXXXXX.html
	
	function getFILMAFFINITY_ID( $url ){
        $result = FALSE;
        
        if( preg_match( "/\/film([0-9]{5,10})\.html/", $url, $match ) 
        && is_array( $match )
        && array_key_exists( 1, $match )
        && strlen( $match[ 1 ] ) > 0
        ){
            $result = $match[ 1 ];
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
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36 OPR/38.0.2220.41');
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
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36 OPR/38.0.2220.41');
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
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36 OPR/38.0.2220.41');
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
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36 OPR/38.0.2220.41');
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
	
	//SIMILARITY
	
	function strSimilarity( $str1, $str2, $debug = FALSE ){
        $result = 0;
        
        $str1 = strtoupper( $str1 );
        $str2 = strtoupper( $str2 );
        
        if( $debug ) echo "<br />MEDIAINFO FINDED TITLE: " . $str1;
        if( $debug ) echo "<br />MEDIAINFO FINDED TITLE: " . $str2;
        similar_text( $str1, $str2, $pc_st );
        if( $debug ) echo "<br />MEDIAINFO SIMILARITY: " . $result;
        $pc_l = levenshtein( $str1, $str2 );
        if( $debug ) echo "<br />MEDIAINFO SIMILARITY(L): " . $pc_l;
        similar_text( soundex( $str1 ), soundex( $str2 ), $pc_sd );
        if( $debug ) echo "<br />MEDIAINFO SIMILARITY(SD): " . $pc_sd;
        if( $debug ) echo "<br />MEDIAINFO SIMILARITY(SD1): " . soundex( $str1 );
        if( $debug ) echo "<br />MEDIAINFO SIMILARITY(SD2): " . soundex( $str2 );
        similar_text( metaphone( $str1 ), metaphone( $str2 ), $pc_mp );
        if( $debug ) echo "<br />MEDIAINFO SIMILARITY(MP): " . $pc_mp;
        if( $debug ) echo "<br />MEDIAINFO SIMILARITY(MP1): " . metaphone( $str1 );
        if( $debug ) echo "<br />MEDIAINFO SIMILARITY(MP2): " . metaphone( $str2 );
        
        //Sum Similarity / options to compare with 80/90
        $result = ( $pc_sd / 4 ) + ( $pc_mp / 4 ) + ( $pc_st / 4 );
        $result += ( 100 / 4 ) - $pc_l;
        //diff size -similarity
        if( strlen( $str1 ) > strlen( $str2 ) ){
            $result -= strlen( $str1 ) - strlen( $str2 );
        }elseif( strlen( $str1 ) < strlen( $str2 ) ){
            $result -= strlen( $str2 ) - strlen( $str2 );
        }
        
        //Better similarity to compare with 80/90
        /*
        //SOUNDEX too many errors on filename size
        if( $result < $pc_sd 
        && $pc_sd >= 100 //soundex test min % needed (4 letters at least 4)
        ){
            $result = $pc_sd;
        }
        if( $result < $pc_mp 
        && $pc_mp >= 70 //metaphone test min % needed
        ){
            $result = $pc_mp;
        }
        //diff size -similarity
        if( strlen( $str1 ) > strlen( $str2 ) ){
            $result -= strlen( $str1 ) - strlen( $str2 );
        }elseif( strlen( $str1 ) < strlen( $str2 ) ){
            $result -= strlen( $str2 ) - strlen( $str2 );
        }
        */
        
        return $result;
	}
	
	//OWN DB SCRAPPER
	
	$G_SCRAPPERS[ 'my_db' ] = array( '', 'ident_detect_file_db' );
	
	function ident_detect_file_db( $file, $title, $movies = TRUE, $imdb = FALSE, $season = FALSE, $episode = FALSE ){
        //Check same title in db
        //recolect NEEDED DATA
        // array( poster, fanart, logo, poster, banner, landscape, data => $G_MEDIAINFO )
        global $G_MEDIADATA;
        $result = $G_MEDIADATA;
        $bfile = basename( $file );
        $debug = FALSE;
        $MIN_SIMILARITY_TITLE = 80;
        $MIN_SIMILARITY_FILE = 80;
        $MIN_SIMILARITY_BWORD = 80;
        
        if( $debug ){
            if( $debug ) echo "<br />File: ";
            var_dump( $file );
            if( $debug ) echo "<br />Title: ";
            var_dump( $title );
            if( $debug ) echo "<br />Movies: ";
            var_dump( $movies );
            if( $debug ) echo "<br />IMDB: ";
            var_dump( $imdb );
            if( $debug ) echo "<br />SEASON: ";
            var_dump( $season );
            if( $debug ) echo "<br />EPISODE: ";
            var_dump( $episode );
        }
        
        if( $debug ) echo "<br />TITLE CLEAN 1: " . $title;
        
        $titlebaseall = $title;
        $title = clean_media_chapter( $title );
        $titlebase = $title;
        $title = clean_filename( $title, TRUE );
        $title = trim( $title );
        
        if( $debug ) echo "<br />TITLE CLEAN 2: " . $title;
        
        //Clean non standar chars
        $title = preg_replace( '/[^\x20-\x7E]/','_', $title );
        $title = str_ireplace( '__', '_', $title );
        if( $debug ) echo "<br />TITLE CLEAN 2b: " . $title;
        //remove 1 letter words
        $title = preg_replace('/(^| )[a-z0-9]{1}( |$)/i', ' ', $title);
        if( $debug ) echo "<br />TITLE CLEAN 2c: " . $title;
        //remove 1 number between letters (2 time double numbers TODO)
        $title = preg_replace('/(^| |[a-z]|_)[0-9]{1}(\d|[a-z]| |$)/i', '$1_$2', $title);
        $title = preg_replace('/(^| |[a-z]|_)[0-9]{1}(\d|[a-z]| |$)/i', '$1_$2', $title);
        if( $debug ) echo "<br />TITLE CLEAN 2d: " . $title;
        $title = str_ireplace( ' ', '%', $title );
        $title = trim( $title, '_' );
        $title = trim( $title, '%' );
        $title = trim( $title, '_' );
        $title = trim( $title, '%' );
        //add full search variants title
        $title = '%' . $title . '%';
        if( $debug ) echo "<br />TITLE CLEAN 3: " . $title;
        
        //testing removing words from title
        $titles = array();
        if( ( $e = explode( '%', $title ) ) != FALSE ){
            foreach( $e AS $ea ){
                //remove only tiny words
                if( strlen( $ea ) <= 3 ){
                    //$lt = ' ' . $ea;
                    $lt = str_replace( $ea . '', '', $title );
                    $titles[] = $lt;
                    if( $debug ) echo "<br />TITLE CLEAN TITLES-RWORDS: " . $lt;
                }
            }
            
        }
        //Add cut on seasonXepisode
        $sepsc = '-!-';
        $titlesc = clean_media_chapter( $titlebaseall, $sepsc );
        if( $debug ) echo "<br />TITLE CLEAN PRETITLES-SXECUT: " . $titlesc;
        if( ( $e = explode( $sepsc, $titlesc ) ) != FALSE 
        && is_array( $e )
        && count( $e ) > 1
        && array_key_exists( 0, $e )
        && strlen( $e[ 0 ] ) > 3
        ){
            $lt = $e[ 0 ];
            $lt = clean_filename( $lt, TRUE );
            //Clean non standar chars
            $lt = preg_replace( '/[^\x20-\x7E]/','_', $lt );
            $lt = str_ireplace( '__', '_', $lt );
            if( $debug ) echo "<br />TITLE CLEAN-SXECUT 2b: " . $lt;
            //remove 1 letter words
            $lt = preg_replace('/(^| )[a-z0-9]{1}( |$)/i', ' ', $lt);
            if( $debug ) echo "<br />TITLE CLEAN-SXECUT 2c: " . $lt;
            //remove 1 number between letters (2 time double numbers TODO)
            $lt = preg_replace('/(^| |[a-z]|_)[0-9]{1}(\d|[a-z]| |$)/i', '$1_$2', $lt);
            $lt = preg_replace('/(^| |[a-z]|_)[0-9]{1}(\d|[a-z]| |$)/i', '$1_$2', $lt);
            if( $debug ) echo "<br />TITLE CLEAN-SXECUT 2d: " . $lt;
            $lt = str_ireplace( ' ', '%', $lt );
            $lt = trim( $lt, '_' );
            $lt = trim( $lt, '%' );
            $lt = trim( $lt, '_' );
            $lt = trim( $lt, '%' );
            //add full search variants title
            $lt = '%' . $lt . '%';
            if( $debug ) echo "<br />TITLE CLEAN-SXECUT 3: " . $lt;
            
            $titles[] = $lt;
            if( $debug ) echo "<br />TITLE CLEAN TITLES-SXECUT: " . $lt;
        }
        
        //add base title
        $titles[] = $title;
        
        //add base title from imdb
        if( $imdb != FALSE
        && $imdb != ""
        && ( $imdbdata = sqlite_mediainfo_search_imdb( $imdb ) ) != FALSE
        && is_array( $imdbdata )
        && count( $imdbdata ) > 0
        && array_key_exists( 0, $imdbdata )
        && is_array( $imdbdata[ 0 ] )
        && array_key_exists( 'title', $imdbdata[ 0 ] )
        ){
            $titles[] = $imdbdata[ 0 ][ 'title' ];
            $titles[] = $imdb;
        }
        
        //sort
        $titles = array_reverse( $titles );
        
        if( $debug ) echo "<br />TITLES LIST: " . nl2br( print_r( $titles, TRUE ) );
        
        foreach( $titles AS $title ){
            //BASE MODE: string similarity by search
            if( ( 
                ( $mediainfo = sqlite_mediainfo_search_title( $title, 5 ) ) != FALSE
                || ( $mediainfo = sqlite_mediainfo_search_imdb( $title, 5 ) ) != FALSE
            )
            && is_array( $mediainfo )
            && count( $mediainfo ) > 0
            ){
                if( $debug ) echo "<br />MEDIAINFO FINDED: " . count( $mediainfo );
                $similars = array();
                $midata = array();
                foreach( $mediainfo AS $mi ){
                    if( array_key_exists( 'idmediainfo', $mi ) 
                    && (int)$mi[ 'idmediainfo' ] > 0
                    ){
                        if( $debug ) echo "<br />MEDIAINFO FINDED TITLE: " . $mi[ 'title' ];
                        
                        //Similarity check
                        $pc = strSimilarity( $title, $mi[ 'title' ], $debug );
                        if( ( $pc2 = strSimilarity( $titlebase, $mi[ 'title' ], $debug ) ) > $pc ){
                            $pc = $pc2;
                        }
                        
                        if( $debug ) echo "<br />MEDIAINFO FINAL SIMILARITY: " . $pc;
                        $similars[ $pc ] = $mi;
                    }
                }
                krsort( $similars );
                if( $debug ) echo "<br />TITLES SIMILARITY: " . nl2br( print_r( $similars, TRUE ) );
                $midatafirst = FALSE;
                foreach( $similars AS $pc => $mi ){
                    if( $pc > $MIN_SIMILARITY_TITLE ){
                        if( $debug ) echo "<br />MEDIAINFO FINDED: " . $mi[ 'title' ];
                        
                        //check year if exist $titlebase
                        $year = get_year( $titlebase );
                        if( is_numeric( $year )
                        && $mi[ 'year' ] > 0
                        ){
                            if( $mi[ 'year' ] == $year 
                            //sometimes file published after
                            || (int)$mi[ 'year' ] - 1 == $year 
                            ){
                                if( $debug ) echo "<br />MEDIAINFO FINDED YEAR CONFIRM: " . $mi[ 'year' ];
                                $midata = $mi;
                                break;
                            }else{
                                if( $debug ) echo "<br />MEDIAINFO FINDED YEAR NO-CONFIRM: " . $mi[ 'year' ] . ' ' . $year;
                            }
                        }else{
                            //set first on worst case
                            if( $midatafirst == FALSE ){
                                $midatafirst = $mi;
                            }
                        }
                    }
                }
                //Set first on no year confirm
                if( $midatafirst != FALSE
                && !array_key_exists( 'idmediainfo', $midata ) 
                ){
                    $midata = $midatafirst;
                }
                
                if( array_key_exists( 'idmediainfo', $midata ) ){
                    foreach( $result AS $rkey => $rvalue ){
                        if( is_string( $rvalue ) ){
                            $result[ $rkey ] = PPATH_MEDIAINFO . DS . $midata[ 'idmediainfo' ] . '.' . $rkey;
                        }
                    }
                    $midata[ 'idmediainfo' ] = 'NULL';
                    $midata[ 'dateadded' ] = date( 'Y-m-d H:i:s' );
                    $midata[ 'season' ] = $season;
                    $midata[ 'episode' ] = $episode;
                    $midata[ 'titleepisode' ] = '';
                    $result[ 'data' ] = $midata;
                }
            }elseif( $season !== FALSE ){
                //SERIES MODE SIMILARITY
                $FINDED = FALSE;
                //Check by title similarity ONLY SERIES
                if( ( $mediadata = sqlite_media_getdata_file_search( $title ) ) != FALSE
                && is_array( $mediadata )
                && count( $mediadata ) > 0
                ){
                    if( $debug ) echo "<br />MEDIA FINDED (TITLE): " . count( $mediadata );
                    $similars = array();
                    $midata = array();
                    foreach( $mediadata AS $mi ){
                        if( array_key_exists( 'idmediainfo', $mi ) 
                        && (int)$mi[ 'idmediainfo' ] > 0
                        ){
                            if( $debug ) echo "<br />MEDIA FINDED FILE: " . $mi[ 'title' ];
                            
                            //Similarity check
                            $pc = strSimilarity( $title, $mi[ 'title' ], $debug );
                            if( ( $pc2 = strSimilarity( $titlebase, $mi[ 'title' ], $debug ) ) > $pc ){
                                $pc = $pc2;
                            }
                            
                            if( $debug ) echo "<br />MEDIAINFO FINAL SIMILARITY: " . $pc;
                            $similars[ $pc ] = $mi;
                        }
                    }
                    krsort( $similars );
                    if( $debug ) echo "<br />TITLES SIMILARITY: " . nl2br( print_r( $similars, TRUE ) );
                    //$pcmin = ( 10 * ( ( strlen( $title ) ) + 1 ) );
                    $pcmin = $MIN_SIMILARITY_TITLE;
                    if( $debug ) echo "<br />MEDIA PC NEEDED: " . $pcmin;
                    foreach( $similars AS $pc => $mi ){
                        if( $pc > $pcmin 
                        //&& $pc < 100
                        ){
                            if( $debug ) echo "<br />MEDIA FINDED: " . $mi[ 'title' ];
                            $midata = $mi;
                            break;
                        }
                    }
                    if( array_key_exists( 'idmediainfo', $midata ) 
                    ){
                        foreach( $result AS $rkey => $rvalue ){
                            if( is_string( $rvalue ) ){
                                $result[ $rkey ] = PPATH_MEDIAINFO . DS . $midata[ 'idmediainfo' ] . '.' . $rkey;
                            }
                        }
                        $midata[ 'idmediainfo' ] = 'NULL';
                        $midata[ 'dateadded' ] = date( 'Y-m-d H:i:s' );
                        $midata[ 'season' ] = $season;
                        $midata[ 'episode' ] = $episode;
                        $midata[ 'titleepisode' ] = '';
                        $result[ 'data' ] = $midata;
                        $FINDED = TRUE;
                    }
                }
                
                //Check by file similarity SERIES MODE
                if( $FINDED == FALSE
                && ( $betterword = get_word_better( basename( $file ) ) ) != FALSE
                && is_string( $betterword )
                && strlen( $betterword ) > 4 //at least 4 chars
                && ( $mediadata = sqlite_media_getdata_file_search( $betterword ) ) != FALSE
                && is_array( $mediadata )
                && count( $mediadata ) > 0
                ){
                    if( $debug ) echo "<br />MEDIA BETTERWORD (FILE2): " . $betterword;
                    if( $debug ) echo "<br />MEDIA FINDED (FILE2): " . count( $mediadata );
                    $similars = array();
                    $midata = array();
                    $f1 = clean_filename( basename( $file ) );
                    foreach( $mediadata AS $mi ){
                        if( array_key_exists( 'idmediainfo', $mi ) 
                        && (int)$mi[ 'idmediainfo' ] > 0
                        ){
                            $f2 = clean_filename( basename( $mi[ 'file' ] ) );
                            if( $debug ) echo "<br />MEDIA FINDED FILE1: " . $f1;
                            if( $debug ) echo "<br />MEDIA FINDED FILE2: " . $f2;
                            
                            //Similarity check
                            $pc = strSimilarity( $f1, $f2, $debug );
                            if( ( $pc2 = strSimilarity( $titlebase, $f2, $debug ) ) > $pc ){
                                $pc = $pc2;
                            }
                            
                            if( $debug ) echo "<br />MEDIAINFO FINAL SIMILARITY: " . $pc;
                            
                            if( $pc > $MIN_SIMILARITY_BWORD ){
                                if( $debug ) echo "<br />MEDIA FINDED FILE: " . basename( $mi[ 'file' ] );
                                if( $debug ) echo "<br />MEDIA SIMILARITY STRINGA: " . $f1;
                                if( $debug ) echo "<br />MEDIA SIMILARITY STRINGB: " . $f2;
                                if( $debug ) echo "<br />MEDIA SIMILARITY: " . $pc;
                            }
                            $similars[ $pc ] = $mi;
                        }
                    }
                    krsort( $similars );
                    if( $debug ) echo "<br />TITLES SIMILARITY: " . nl2br( print_r( $similars, TRUE ) );
                    $pcmin = $MIN_SIMILARITY_BWORD;
                    if( $debug ) echo "<br />MEDIA PC NEEDED: " . $pcmin;
                    foreach( $similars AS $pc => $mi ){
                        if( $pc > $pcmin 
                        ){
                            if( $debug ) echo "<br />MEDIA FINDED: " . $mi[ 'title' ];
                            $midata = $mi;
                            break;
                        }
                    }
                    if( array_key_exists( 'idmediainfo', $midata ) 
                    && ( $midata = sqlite_mediainfo_getdata( $midata[ 'idmediainfo' ] )) != FALSE
                    && is_array( $midata )
                    && count( $midata ) > 0
                    ){
                        $midata = $midata[ 0 ];
                        foreach( $result AS $rkey => $rvalue ){
                            if( is_string( $rvalue ) ){
                                $result[ $rkey ] = PPATH_MEDIAINFO . DS . $midata[ 'idmediainfo' ] . '.' . $rkey;
                            }
                        }
                        $midata[ 'idmediainfo' ] = 'NULL';
                        $midata[ 'dateadded' ] = date( 'Y-m-d H:i:s' );
                        $midata[ 'season' ] = $season;
                        $midata[ 'episode' ] = $episode;
                        $midata[ 'titleepisode' ] = '';
                        $result[ 'data' ] = $midata;
                    }
                }
            }
        }
        
        if( $debug ){
            echo "<br />RESULT: ";
            var_dump( $result );
            //die();
        }
        return $result;
	}
	
	//SEARCH POSTERS IMAGES
	
	function get_medinfo_images( $limit = 10, $type = 'poster', $print = TRUE, $debug = FALSE ){
        $result = FALSE;
        
        if( $debug ) echo "<br />GETTING IMGS AUTO: " . $limit;
        if( ( $MEDIAINFO = sqlite_mediainfo_search( '', 1000 ) ) != FALSE
        && is_array( $MEDIAINFO )
        && count( $MEDIAINFO ) > 0
        ){
            if( $debug ) echo "<br />MEDIAINFOS: " . count( $MEDIAINFO );
            foreach( $MEDIAINFO AS $row ){
                $FTARGET = PPATH_MEDIAINFO . DS . $row[ 'idmediainfo' ] . '.' . $type;
                if( $debug ) echo "<br />CHECKING: " . $row[ 'idmediainfo' ] . ' - ' . $row[ 'title' ];
                if( !file_exists( $FTARGET )
                && 
                    ( 
                        ( $images = get_image_pymediaident( $row[ 'idmediainfo' ], $type ) ) != FALSE
                        || ( $images = get_image_auto( $row[ 'idmediainfo' ], $type ) ) != FALSE
                    )
                ){
                    if( $debug ) echo "<br />TITLE: " . $row[ 'title' ];
                    if( $debug ) echo "<br />IMAGES: " . count( $images );
                    foreach( $images AS $img ){
                        if( getFileMimeTypeImg( $img )
                        && (
                            @link( $img, $FTARGET )
                            || @copy( $img, $FTARGET )
                            )
                        ){
                            $limit--;
                            if( $print ) echo "<br />IMAGE ADD: " . $row[ 'title' ] . ' - ' . $row[ 'idmediainfo' ];
                            if( array_key_exists( 'title', $row )
                            && ( $MIDATA2 = sqlite_mediainfo_search_title( $row[ 'title' ] ) ) != FALSE 
                            && count( $MIDATA2 ) > 0
                            ){
                                $q = 0;
                                foreach( $MIDATA2 AS $row ){
                                    $FTARGET2 = PPATH_MEDIAINFO . DS . $row[ 'idmediainfo' ] . '.' . $type;
                                    if( !file_exists( $FTARGET2 )
                                    && (
                                        @link( $img, $FTARGET2 )
                                        || @copy( $img, $FTARGET2 )
                                    )
                                    ){
                                        if( $print ) echo "<br />IMAGE ADD CHAPTER: " . $row[ 'title' ] . ' - ' . $row[ 'idmediainfo' ];
                                        $q++;
                                    }
                                }   
                            }else{
                                $result = TRUE;
                            }
                        }else{
                            $result = FALSE;
                        }
                    }
                }
                if( $limit <= 0 ){
                    break;
                }
            }
        }
        
        return $result;
	}
	
	function get_image_auto( $idmediainfo, $type = 'poster', $quantity = 1 ){
        $result = FALSE; //FALSE|array( fileimage, ... )
        global $G_MEDIADATA;
        
        if( ( $MEDIAINFO = sqlite_mediainfo_getdata( $idmediainfo ) ) != FALSE
        && is_array( $MEDIAINFO )
        && array_key_exists( 0, $MEDIAINFO )
        ){
            $MEDIAINFO = $MEDIAINFO[ 0 ];
            $fileimgpathrnd = getRandomString( 8 );
            $fileimgpath = PPATH_TEMP . DS . $fileimgpathrnd;
            @mkdir( $fileimgpath );
            $in_list = array();
            $filenum = 1;
            if( array_key_exists( $type, $G_MEDIADATA ) ){
                $search = $type . ' ' . $MEDIAINFO[ 'title' ] . ' ' . $MEDIAINFO[ 'year' ];
                if( is_numeric( $MEDIAINFO[ 'season' ] ) ){
                    $search .= ' serie';
                }else{
                    $search .= ' movie';
                }
                //var_dump( $search );
                $images_own = array();
                if( ( $images = searchImages( $search, $quantity, FALSE ) ) != FALSE
                && is_array( $images ) 
                && count( $images ) > 0
                ){
                    foreach( $images AS $key => $img ){
                        $fileimg = $fileimgpath . DS . $filenum;
                        if( array_key_exists( $img, $in_list ) ){
                            $images_own[] = $in_list[ $img ];
                        }elseif( downloadPosterToFile( $img, $fileimg ) 
                        && file_exists( $fileimg )
                        && getFileMimeTypeImg( $fileimg )
                        ){
                            $in_list[ $img ] = $fileimg;
                            $images_own[] = $fileimg;
                            $filenum++;
                        }
                    }
                    if( !is_array( $result ) ) $result = array();
                    $result = $images_own;
                }
            }
        }
        
        
        return $result;
	}
	
	function get_image_pymediaident( $idmediainfo, $type = 'poster', $quantity = 1 ){
        $result = FALSE; //FALSE|array( fileimage, ... )
        global $G_MEDIADATA;
        
        //ONLY POSTERS
        if( $type == 'poster'
        && ( $MEDIAINFO = sqlite_mediainfo_getdata( $idmediainfo ) ) != FALSE
        && is_array( $MEDIAINFO )
        && array_key_exists( 0, $MEDIAINFO )
        ){
            $MEDIAINFO = $MEDIAINFO[ 0 ];
            $fileimgpathrnd = getRandomString( 8 );
            $fileimgpath = PPATH_TEMP . DS . $fileimgpathrnd;
            @mkdir( $fileimgpath );
            $in_list = array();
            $filenum = 1;
            if( array_key_exists( $type, $G_MEDIADATA ) ){
                $search = $type . ' ' . $MEDIAINFO[ 'title' ] . ' ' . $MEDIAINFO[ 'year' ];
                if( is_numeric( $MEDIAINFO[ 'season' ] ) ){
                    $season = (int)$MEDIAINFO[ 'season' ];
                    $episode = (int)$MEDIAINFO[ 'episode' ];
                    $search .= ' ' . $MEDIAINFO[ 'season' ] . 'x' . $MEDIAINFO[ 'episode' ];
                    $movies = FALSE;
                }else{
                    //$search .= ' movie';
                    $movies = TRUE;
                    $season = FALSE;
                    $episode = FALSE;
                }
                //var_dump( $search );
                $images_own = array();
                $imdb = FALSE;
                $file = FALSE;
                $title = $MEDIAINFO[ 'title' ] . ' ' . $MEDIAINFO[ 'year' ];
                if( ( $md = ident_detect_file_pymi( $file, $title, $movies, $imdb, $season, $episode ) ) != FALSE 
                && array_key_exists( $type, $md )
                ){
                    $fileimg = $fileimgpath . DS . $filenum;
                    if( array_key_exists( 'url' . $type, $md )
                    && ident_download_pymi( $md[ 'url' . $type ], $fileimg ) 
                    && file_exists( $fileimg )
                    && getFileMimeTypeImg( $fileimg )
                    ){
                        $in_list[ $img ] = $fileimg;
                        $images_own[] = $fileimg;
                        $filenum++;
                    }
                    $result = $images_own;
                    if( !is_array( $result ) ) $result = array();
                }
            }
        }
        
        
        return $result;
	}
	
	function searchImagesPyMI( $search, $max = 5, $getthumb = TRUE ){
        $result = FALSE;
        
        if( stripos( $search, 'serie' ) !== FALSE ){
            $movies = FALSE;
            $season = 1;
            $episode = 1;
        }else{
            $movies = TRUE;
            $season = FALSE;
            $episode = FALSE;
        }
        $imdb = FALSE;
        
        //ONLY POSTER
        $type = 'poster';
        $pathtmpimg = PPATH_TEMP . DS . getRandomString();
        if(  stripos( $search, 'poster' ) !== FALSE
        && ( $md = ident_detect_file_pymi( FALSE, $search, $movies, $imdb, $season, $episode ) ) != FALSE 
        && array_key_exists( 'url' . $type, $md )
        ){
            $images_own = array();
            $fileimg = $pathtmpimg . DS . $filenum;
            if( ident_download_pymi( $md[ 'url' . $type ], $fileimg ) 
            && file_exists( $fileimg )
            && getFileMimeTypeImg( $fileimg )
            ){
                $in_list[ $img ] = $fileimg;
                $images_own[] = $fileimg;
                $filenum++;
            }
            $result = $images_own;
            if( !is_array( $result ) ) $result = array();
        }
        
        return $result;
	}
?>
