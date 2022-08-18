<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//ADD SCRAPPER
	$G_SCRAPPERS[ 'filmaff' ] = array( '', 'ident_detect_file_filmaff' );
	$G_SCRAPPERS_KEY[ 'filmaff' ] = '';
	$G_SCRAPPERS_SEARCH[ 'filmaff' ] = array( 'filmaffinity.com/', 'filmaffinity.com', 'getFILMAFFINITY_ID' );
	
	//BASE
	function ident_detect_file_filmaff( $file, $title, $movies = TRUE, $imdb = FALSE, $season = FALSE, $episode = FALSE ){
        //autodetect filmaff
        //wait for changes
        //recolect NEEDED DATA
        // array( poster, fanart, logo, poster, banner, landscape, data => $G_MEDIAINFO )
        global $G_MEDIADATA;
        $result = $G_MEDIADATA;
        
        $titlewcs = $title;
        if( $season !== FALSE
        && get_media_chapter( $title ) == FALSE
        ){
            $title .= ' ' . $season . 'x' . $episode;
        }

        //check valid imdb id to exclude
        if( is_string( $imdb )
        && strlen( $imdb ) > 2
        && substr( $imdb, 0, 2 ) == 'tt'
        ){
            $imdb = FALSE;
        }
        
        if( ( $htmldata = ident_detect_filmaff( $file, $titlewcs, $movies, $imdb ) ) != FALSE
        && ( $data_nfo = ident_filmaff_read_data( $htmldata, $file ) ) != FALSE
        ){
            $result = $data_nfo;
            //EXTRA SEASON && EPISODE FROM FILENAME SSxCC
            if( is_numeric( $season ) ){
                $result[ 'data' ][ 'season' ] = (int)$season;
                $result[ 'data' ][ 'episode' ] = (int)$episode;
            }
            //Images URLs
            $k='poster';
            $TMP_FOLDER = PPATH_TEMP . DS . getRandomString( 8 );
            $FILENAME = $TMP_FOLDER . DS . getRandomString( 8 );
            if( array_key_exists( $k, $result ) 
            && filter_var( $result[ $k ], FILTER_VALIDATE_URL )
            && @mkdir( $TMP_FOLDER )
            && ident_download_filmaff( $result[ $k ], $FILENAME )
            && file_exists( $FILENAME )
            ){
                $result[ $k ] = $FILENAME;
            }else{
                $result[ $k ] = '';
            }
        }else{
            $result = FALSE;
        }
        
        return $result;
	}
	
	function ident_detect_filmaff( $file, $title, $movies, $imdbid = FALSE ){
        $result = FALSE;
        
        //set title or imdb url
        if( $imdbid ){
            //set url https://www.filmaffinity.com/' . O_LANG . '/filmXXXXXX.html
            $titlef = $title;
            $url = 'https://www.filmaffinity.com/' . O_LANG . '/film' . $imdbid . '.html';
            $result = file_get_contents_timed_basic( $url, 10, '' );
            //file_put_contents( PPATH_TEMP . DS . 'fa.html', $result );
        }else{
            $titlef = $title;
            if( ( $url = ident_filmaff_search( $title ) ) != FALSE ){
                //force lang
                $url = str_ireplace( '/en/', '/' . O_LANG . '/', $url );
                $result = file_get_contents_timed_basic( $url, 10, '' );
                //file_put_contents( PPATH_TEMP . DS . 'fa.html', $result );
            }else{
                $result = FALSE;
            }
        }
        
		return $result;
	}
	
	//SEARCH
	
	function ident_filmaff_search( $title ){
        //Search for title ident
        $l2 = array();
        $debug = FALSE;
        
        ////https://www.filmaffinity.com/' . O_LANG . '/search.php?stext=TITLE
        //OWN search on his web
        if( ( $links = ident_filmaff_search_own( $title ) ) != FALSE ){
            //Clean Title
            foreach( $links AS $t => $href ){
                if( getFILMAFFINITY_ID( $href ) != FALSE ){
                    $t = clean_filename( $t, TRUE );
                    $l2[ trim( $t ) ] = $href;
                }
            }
            $links = $l2;

            //TEST only first, compare better element
            $blink = '';
            $bsim = 0;
            foreach( $links AS $t => $l ){
                $ns = strSimilarity( $title, $t, $debug );
                if( $ns > $bsim ){
                    $blink = $l;
                    $bsim = $ns;
                }
            }
            $links = $blink;
        }elseif( ( $links = scrap_all( $title, 'filmaffinity.com/', 'filmaffinity.com' ) ) != FALSE
        && count( $links ) > 0
        ){
            //Clean Title
            foreach( $links AS $t => $href ){
                if( getFILMAFFINITY_ID( $href ) != FALSE ){
                    $t = clean_filename( $t, TRUE );
                    $l2[ trim( $t ) ] = $href;
                }
            }
            $links = $l2;
            
            //TEST only first, compare better element
            $blink = '';
            $bsim = 0;
            foreach( $links AS $t => $l ){
                $ns = strSimilarity( $title, $t, $debug );
                if( $ns > $bsim ){
                    $blink = $l;
                    $bsim = $ns;
                }
            }
            $links = $blink;
            
        }
        
        return $links;
	}
	
	function ident_filmaff_search_own( $title ){
        //Search for title ident
        $debug = FALSE;
        $links = array();

        $url = 'https://www.filmaffinity.com/' . O_LANG . '/search.php?stext=' . urlencode( $title );
        if( $debug ) echo '<br />(FILMAFFINITY) HTML URL: ' . $url;

        ////https://www.filmaffinity.com/es/search.php?stext=TITLE
        //OWN search on his web
        $time = 10;
        $sessiondata = FALSE;
        if( ( $htmldata = file_get_contents_timed_filma( $url, $time, $sessiondata, $debug ) ) != FALSE
        && is_string( $htmldata )
        ){
            //HTML mode
            if( $debug ) echo '<br />(FILMAFFINITY) HTML DATA: ' . strlen( $htmldata );
            $dom = new DOMDocument();
            @$dom->loadHTML( mb_convert_encoding( $htmldata, 'HTML-ENTITIES', 'UTF-8' ) );

            foreach ( $dom->getElementsByTagName( 'a' ) as $link ){
				$href = $link->getAttribute( "href" );

                //Title
                $title = $link->nodeValue;
                $title = trim( $title );
                if( strlen( $title ) == 0 ){
                    $title = $link->textContent;
                }
                $title = trim( $title );
                if( strlen( $title ) == 0 ){
                    $title = $link->getAttribute( 'title' );
                }
                $title = trim( $title );
                $links[ $title ] = $href;
                if( $debug ) echo '<br />(FILMAFFINITY)LINKs ADDED: ' . $title . ' => ' . $href;

            }
        }elseif( is_array( $htmldata )
        && array_key_exists( 0, $htmldata )
        ){
            //array mode with link to redirect
            $links = array( $title => $htmldata[ 0 ] );
            if( $debug ) echo '<br />(FILMAFFINITY)LINKs ADDED REDIRECT: ' . $title . ' => ' . $htmldata[ 0 ];
        }

        return $links;
	}

	//GET INFO
	
	function ident_filmaff_read_data( $data, $basefile ){
		global $G_MEDIAINFO;
		global $G_MEDIADATA;
		$result = $G_MEDIADATA;
		$rtesult = $G_MEDIAINFO;
		
		if( $data != FALSE
		){
            //TITLE
            $f = 'title';
            $fi = $f;

            $re = '/<h1 id="main-title">\s+<span itemprop=\"name\">(.+)(\(|<)/i';
            preg_match_all( $re, $data, $match);
            $re2 = '/<dd class="akas">\s+<ul>\s+<li>(.+)<\/li>\s+<\/ul>\s+<\/dd>/i';
            preg_match_all( $re2, $data, $match2);

            //echo nl2br( htmlentities( print_r( $match, TRUE ) ) );
            if( is_array( $match )
            && count( $match ) > 0
            && array_key_exists( 1, $match )
            && is_array( $match[ 1 ] )
            && array_key_exists( 0, $match[ 1 ] )
            && strlen( trim( $match[ 1 ][ 0 ] ) ) > 0
            ){
                $btitle = strip_tags( $match[ 1 ][ 0 ] );
                $rtesult[ $fi ] = clean_filename( trim( $btitle ) );
            }elseif( is_array( $match2 )
            && count( $match2 ) > 0
            && array_key_exists( 0, $match2 )
            && is_array( $match2[ 0 ] )
            && array_key_exists( 0, $match2[ 0 ] )
            && strlen( trim( $match2[ 0 ][ 0 ] ) ) > 0
            ){
                $btitle = strip_tags( $match[ 0 ][ 0 ] );
                $rtesult[ $fi ] = clean_filename( trim( $btitle ) );
            }else{
                //base title
                $re = '/<dt>(Título original|Original title)<\/dt>\s+<dd>\s+(.+)\s+<\/dd>/i';
                preg_match_all( $re, $data, $match);
                if( is_array( $match )
                && count( $match ) > 0
                && array_key_exists( 2, $match )
                && is_array( $match[ 2 ] )
                && array_key_exists( 0, $match[ 2 ] )
                ){
                    $btitle = strip_tags( $match[ 2 ][ 0 ] );
                    $rtesult[ $fi ] = clean_filename( trim( $btitle ) );
                }
            }
            
            //year
            $f = 'year';
            $fi = $f;
            $re = '/<dd itemprop="datePublished">([^<]+)<\/dd>/i';
            preg_match_all( $re, $data, $match);
            if( is_array( $match )
            && count( $match ) > 0
            && array_key_exists( 1, $match )
            && is_array( $match[ 1 ] )
            && array_key_exists( 0, $match[ 1 ] )
            ){
                $rtesult[ $fi ] = trim( $match[ 1 ][ 0 ] );
            }
            
            //sorttitle - CHECK TODO NO DATE
            $f = 'releasedate';
            $fi = 'sorttitle';
            //$re = '/<strong>(\d{4}-\d{2}-\d{2}|\d{2}-\d{2}-\d{4}|\d{2}\/\d{2}\/\d{4})<\/strong>/i';
            $re = FALSE;
            if( $re ){
                preg_match_all( $re, $data, $match);
            }else{
                $match = FALSE;
            }
            if( is_array( $match )
            && count( $match ) > 0
            && array_key_exists( 0, $match )
            && is_array( $match[ 0 ] )
            && array_key_exists( 0, $match[ 0 ] )
            && ( $temp_date = date( 'Y-m-d', strtotime( $match[ 0 ][ 0 ] ) ) ) !== FALSE
            ){
                $rtesult[ $fi ] = $temp_date;
            }else{
                if( $rtesult[ 'year' ] == date( 'Y' )
                ){
                    $rtesult[ $fi ] = date( 'Y-m' ) . '-01';
                }else{
                    //TODO middle oy year
                    $rtesult[ $fi ] = $rtesult[ 'year' ] . '-06-01';
                }
            }
            
            //ALTERNATIVE SEASON from filename SSxCC on main
            //SEASON
            $f = 'season';
            $fi = $f;
            $rtesult[ $fi ] = 0;
            //EPISODE
            $f = 'chapter';
            $fi = 'episode';
            $rtesult[ $fi ] = 0;
            
            //rating
            $f = 'rating';
            $fi = $f;
            $re = '/itemprop="ratingValue" content="(.+)">/i';
            preg_match_all( $re, $data, $match);
            if( is_array( $match )
            && count( $match ) > 0
            && array_key_exists( 1, $match )
            && is_array( $match[ 1 ] )
            && array_key_exists( 0, $match[ 1 ] )
            && is_numeric( trim( $match[ 1 ][ 0 ] ) )
            ){
                $rtesult[ $fi ] = trim( $match[ 1 ][ 0 ] );
            }
            
            //votes
            $f = 'votes';
            $fi = $f;
            $re = '/itemprop="ratingCount" content="(.+)">/i';
            preg_match_all( $re, $data, $match);
            if( is_array( $match )
            && count( $match ) > 0
            && array_key_exists( 1, $match )
            && is_array( $match[ 1 ] )
            && array_key_exists( 0, $match[ 1 ] )
            && is_numeric( trim( $match[ 1 ][ 0 ] ) )
            ){
                $rtesult[ $fi ] = trim( $match[ 1 ][ 0 ] );
            }
            
            //mpaa -- NOT FOUND?
            $f = 'rated';
            $fi = 'mpaa';
            $rtesult[ $fi ] = '';
            
            //tagline -- NOT FOUND?
            $f = 'plotshort';
            $fi = 'tagline';
            $rtesult[ $fi ] = '';
            
            //runtime
            $f = 'runtime';
            $fi = $f;
            $re = '/<dd itemprop="duration">(\d+) min\.<\/dd>/i';
            preg_match_all( $re, $data, $match);
            if( is_array( $match )
            && count( $match ) > 0
            && array_key_exists( 1, $match )
            && is_array( $match[ 1 ] )
            && array_key_exists( 0, $match[ 1 ] )
            && is_numeric( trim( $match[ 1 ][ 0 ] ) )
            ){
                $rtesult[ $fi ] = trim( $match[ 1 ][ 0 ] );
            }
            //re runtime to ffmpeg
            if( ( !is_numeric( $rtesult[ $fi ] ) 
            || $rtesult[ $fi ] == 0
            )
            && ( $mins = ffmpeg_file_info_lenght_minutes( $basefile ) ) != FALSE
            ){
                $rtesult[ $fi ] = $mins;
            }
            
            //plot
            $f = 'plot';
            $fi = $f;
            //$re = '/<dd itemprop="description">(.+)<\/dd>/i';
            $re = '/itemprop="description">(.+)<\/dd/i';
            preg_match_all( $re, $data, $match);
            if( is_array( $match )
            && count( $match ) > 0
            && array_key_exists( 1, $match )
            && is_array( $match[ 1 ] )
            && array_key_exists( 0, $match[ 1 ] )
            ){
                $rtesult[ $fi ] = trim( $match[ 1 ][ 0 ] );
            }
            
            //imdbid -- NOT EXIST
            $f = 'imdbid';
            $fi = $f;
            $rtesult[ $fi ] = '';
            
            //imdb
            $f = 'imdb';
            $fi = $f;
            $rtesult[ $fi ] = '';
            
            //tmdbid -- NO EXIST
            $f = 'tmdbid';
            $fi = $f;
            
            //tmdb -- NO EXIST
            $f = 'tmdb';
            $fi = $f;
            
            //tvdbid -- NO EXIST
            $f = 'tvdbid';
            $fi = $f;
            
            //tvdb -- NO EXIST
            $f = 'tvdb';
            $fi = $f;
            
            //titleepisode -- NO EXIST
            $f = 'chaptertitle';
            $fi = 'titleepisode';
            
            //GENRES
            $f = 'genres';
            $fi = 'genre';
            $re = '/<span itemprop="genre"><a href="[^"]+">([^"]+)<\/a>/i';
            preg_match_all( $re, $data, $match);
            $rtesult[ $fi ] = '';
            if( is_array( $match )
            && count( $match ) > 0
            && array_key_exists( 1, $match )
            && is_array( $match[ 1 ] )
            && array_key_exists( 0, $match[ 1 ] )
            ){
                foreach( $match[ 1 ] AS $g ){
                    if( strlen( $rtesult[ $fi ] ) > 0 ){
                        $rtesult[ $fi ] .= ', ';
                    }
                    $rtesult[ $fi ] .= trim( $g );
                }
            }
            
            //ACTORS getCast
            $f = 'actors'; 
            $fi = 'actor';
            if( ( $sdata = ident_filmaff_subdata( $data, '<dt>Reparto</dt>', '</dd>' ) ) != FALSE 
            || ( $sdata = ident_filmaff_subdata( $data, '<dt>Cast</dt>', '</dd>' ) ) != FALSE 
            ){
                $re = '/<span itemprop="name">([^<]+)<\/span>/i';
                preg_match_all( $re, $sdata, $match);
                $rtesult[ $fi ] = '';
                if( is_array( $match )
                && count( $match ) > 0
                && array_key_exists( 1, $match )
                && is_array( $match[ 1 ] )
                && array_key_exists( 0, $match[ 1 ] )
                ){
                    foreach( $match[ 1 ] AS $g ){
                        if( strlen( $rtesult[ $fi ] ) > 0 ){
                            $rtesult[ $fi ] .= ', ';
                        }
                        $rtesult[ $fi ] .= trim( $g );
                    }
                }
            }
            
            //AUDIO --NOEXIST
            $f = 'audio';
			
            //SUBS --NOEXIST
            $f = 'subtitle'; 
            
            //IMGs
            //POSTER getPoster
            $f = 'urlposter';
            $fi = 'poster';
            if( ( $sdata = ident_filmaff_subdata( $data, '<div id="movie-main-image-container">', '</div>' ) ) != FALSE 
            ){
                $re = '/src="(https:\/\/pics.filmaffinity.com\/[^"]+)"/i';
                preg_match_all( $re, $sdata, $match);
                $result[ $fi ] = '';
                if( is_array( $match )
                && count( $match ) > 0
                && array_key_exists( 1, $match )
                && is_array( $match[ 1 ] )
                && array_key_exists( 0, $match[ 1 ] )
                ){
                    $result[ $fi ] = trim( $match[ 1 ][ 0 ] );
                }
            }
            $result[ 'data' ] = $rtesult;
        }
        
        return $result;
    }
    
	function ident_download_filmaff( $url, $file ){
        $result = FALSE;
        
        //Get Data
        $cmd = O_WGET . ' -O "' . $file . '" "' . $url . '"';
        runExtCommand( $cmd );
        $result = TRUE;
		
		return $result;
	}
	
	function ident_filmaff_subdata( $data, $init, $end ){
        $result = FALSE;
        
        if( ( $a = stripos( $data, $init ) ) !== FALSE 
        && ( $b = stripos( $data, $end, ( $a + strlen( $init ) ) ) ) !== FALSE 
        && $a < $b
        ){
            $result = substr( $data, $a, ( $b - $a ) );
        }
        
		return $result;
	}
	
	//CURL HELPER

	function file_get_contents_timed_filma( $url, $time = 5, $sessiondata = FALSE, $debug = FALSE ){
        $response = FALSE;
        $curl = curl_init();

        $header = array();
        $header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8";
        //q=0.7,en;q=0.3
        $header[] = "Accept-Language: " . O_LANG . ",en-US;q=0.7,en;q=0.3";
        //Cookie
        //FSID=
        //FCD=
        if( $sessiondata == FALSE ){
            $sessiondata = array(
                'FSID' => getRandomString( 64 ),
                'FCD' => getRandomString( 87 ) . '-' . getRandomString( 25 ) . '-v-' . getRandomString( 17 ) . '_' . getRandomString( 29 ),
            );
        }
        //$sessiondata = FALSE;
        if( is_array( $sessiondata )
        && count( $sessiondata ) > 0
        ){
            $sdata = '';
            foreach( $sessiondata AS $n => $v ){
                $sdata .= $n . '=' . $v . '; ';
            }
            //Remove last ;space
            $sdata = trim( $sdata, ' ' );
            $sdata = trim( $sdata, ';' );
            $header[] = "Cookie: " . $sdata . "";
        }
        $header[] = "Cache-Control: max-age=0";
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: " . $time;
        $header[] = "Pragma: ";
        $header[] = "Referer: https://www.filmaffinity.com";
        $header[] = "Host: www.filmaffinity.com";
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $header );
        curl_setopt( $curl, CURLOPT_HEADER, FALSE );

        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_REFERER, 'https://www.filmaffinity.com' );
        //curl_setopt( $curl, CURLOPT_COOKIEJAR, "cookie.txt" );
        //curl_setopt( $curl, CURLOPT_COOKIEFILE, "cookie.txt" );
        curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, $time );
        curl_setopt( $curl, CURLOPT_COOKIESESSION, TRUE );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36 OPR/38.0.2220.41');
        curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, TRUE );
        curl_setopt( $curl, CURLOPT_MAXREDIRS, 1 );
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
            //Check redirect
            $httpStatus = curl_getinfo( $curl );
            var_dump( $httpStatus );
        }

        $httpStatus = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
        //var_dump( $httpStatus );
        //detect redirecction
        if( $httpStatus >= 300
        && $httpStatus < 400
        ){
            //send link as array element for location element
            //look for a location: header to find the target URL
            if( preg_match( '/location: (.*)/i', $response, $r ) ){
                $location = trim( $r[ 1 ] );

                // if the location is a relative URL, attempt to make it absolute
                if( preg_match( '/^\/(.*)/', $location ) ){
                    $urlParts = parse_url( $url );
                    if( $urlParts[ 'scheme' ] ){
                        $baseURL = $urlParts[ 'scheme' ] . '://';
                    }
                    if( $urlParts[ 'host' ] ){
                        $baseURL .= $urlParts[ 'host' ];
                    }
                    if( $urlParts[ 'port' ] ){
                        $baseURL .= ':' . $urlParts[ 'port' ];
                    }

                    $location =  $baseURL . $location;
                }
                $response = array( $relocurl );
            }
        }

        curl_close( $curl );

        return $response;
	}


?>
