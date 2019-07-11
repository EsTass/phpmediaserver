<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//DEFINED IDMEDIAINFO
	$G_MEDIAINFO = array(
        'idmediainfo' => 'NULL',
        'dateadded' => '',
        'title' => '',
        'sorttitle' => '', //this is dateaired
        'season' => 0,
        'episode' => 0,
        'year' => '',
        'rating' => '',
        'votes' => '',
        'mpaa' => '',
        'tagline' => '',
        'runtime' => '',
        'plot' => '',
        'height' => '',
        'width' => '',
        'codec' => '',
        'imdbid' => '',
        'imdb' => '',
        'tmdbid' => '',
        'tmdb' => '',
        'tvdbid' => '',
        'tvdb' => '',
        'genre' => '',
        'actor' => '',
        'audio' => '',
        'subtitle' => '',
        'titleepisode' => '',
    );
    // Media Files:
    // - fanart.*
    // - folder.*
    // - logo.*
    // - poster.*
    // - banner.*
    // - landscape.*
    // Data
    // - data => $G_MEDIAINFO 
    // array( poster, fanart, logo, poster, banner, landscape, data => $G_MEDIAINFO )
    $G_MEDIADATA = array(
        'fanart' => '',
        'folder' => '',
        'logo' => '',
        'poster' => '',
        'banner' => '',
        'landscape' => '',
        'data' => $G_MEDIAINFO,
    );
	
	//FILES & FOLDERS
	
	function getFilesFilterLimit( $dir, &$quantity = 100, $pattern = '/./', $debug = FALSE ){
		$result = array();
        $prefix = $dir . DS;
        $folders = array();
        if( $debug ) echo "<br />CHECKING: " . $dir;
        
        if( ( $dir = dir($dir) ) != FALSE )
        while( ( $file = $dir->read() ) !== FALSE ){
            if( $file === '.' 
            || $file === '..'
            ){
                continue;
            }
            if( $debug ) echo "<br />TEST: " . $file;
            $file = $prefix . $file;
            if( is_dir( $file )
            && media_scan_exclude_folders( $file ) == FALSE
            ){
                $folders[] = $file;
            }elseif( ( is_file( $file ) || is_link( $file ) )
            && preg_match( $pattern, $file ) 
            && media_scan_exclude_files( $file ) == FALSE
            ){
                if( $debug ) echo "<br />ADDING FILE: " . $file;
                $result[] = $file;
                $quantity--;
            }
            if( $quantity <= 0 ){
                break;
            }
        }
        //Subfolders
        foreach( $folders AS $folder ){
            if( ( $more = getFilesFilterLimit( $folder, $quantity, $pattern, $debug ) ) != FALSE
            && is_array( $more )
            && count( $more ) > 0
            ){
                if( $debug ) echo "<br />ADDING FOLDER RESULT: " . count( $more );
                $result = array_merge( $result, $more );
            }
        }
        
        return $result;
    }
	
	function getFilesFilterLimitAll( $dir, &$quantity = 100, $pattern = '/./', $recursive = FALSE, $debug = FALSE ){
		$result = array();
        $prefix = $dir . DS;
        $folders = array();
        if( $debug ) echo "<br />CHECKING: " . $dir;
        
        if( ( $dir = dir($dir) ) != FALSE )
        while( ( $file = $dir->read() ) !== FALSE ){
            if( $file === '.' 
            || $file === '..'
            ){
                continue;
            }
            if( $debug ) echo "<br />TEST: " . $file;
            $file = $prefix . $file;
            if( is_dir( $file )
            //&& media_scan_exclude_folders( $file ) == FALSE
            ){
                $folders[] = $file;
            }elseif( ( is_file( $file ) || is_link( $file ) )
            && preg_match( $pattern, $file ) 
            //&& media_scan_exclude_files( $file ) == FALSE
            ){
                if( $debug ) echo "<br />ADDING FILE: " . $file;
                $result[] = $file;
                $quantity--;
            }
            if( $quantity <= 0 ){
                break;
            }
        }
        //Subfolders
        if( $recursive ){
            foreach( $folders AS $folder ){
                if( ( $more = getFilesFilterLimitAll( $folder, $quantity, $pattern, $recursive, $debug ) ) != FALSE
                && is_array( $more )
                && count( $more ) > 0
                ){
                    if( $debug ) echo "<br />ADDING FOLDER RESULT: " . count( $more );
                    $result = array_merge( $result, $more );
                }
            }
        }
        
        return $result;
    }
	
	function getFiles( $directory, $recursive = TRUE ){
		$result = array();
		
		if( file_exists( $directory ) 
		&& is_dir( $directory ) 
		&& ( $dir = new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::FOLLOW_SYMLINKS | FilesystemIterator::SKIP_DOTS ) ) != FALSE
		&& ( $it = new RecursiveIteratorIterator( $dir, RecursiveIteratorIterator::SELF_FIRST ) ) != FALSE
		){
			if( !$recursive ){
                $it->setMaxDepth( 0 );
            }
			while( $it->valid() ) {
                if( !in_array( $it->key(), $result ) ){
                    $result[] = $it->key();
                }
				$it->next();
			}
		}
		
		return $result;
	}
	
	function getFolders( $path ){
		
		$dir = $path;
		$filter = '*';
		$files 	= glob( "" . $dir . "/" . $filter, GLOB_ONLYDIR );
		
		if( ( $ide = array_search( '.', $files ) ) != FALSE
		){
			unset( $files[ $ide ] );
		}
		if( ( $ide = array_search( '..', $files ) ) != FALSE
		){
			unset( $files[ $ide ] );
		}
		
		return $files;
	}
	
	//SCAN FILES IN DOWNLOAD
	
	function mediainfo_clean_duplicated_files( $days = 5, $quantity = 10, $echo = FALSE, $debug = FALSE ){
        $result = TRUE;
        
        if( $days > 0
        && ( $mdata = sqlite_media_getdata_order_mediainfo( FALSE, 100000 ) ) != FALSE 
        && is_array( $mdata )
        && count( $mdata ) > 0
        ){
            if( $debug ) echo "<br />CHECKING: " . count( $mdata );
            $allmedia = array();
            foreach( $mdata AS $media ){
                if( file_exists( $media[ 'file' ] ) ){
                    $allmedia[ $media[ 'idmediainfo' ] ][] = $media;
                }
            }
            unset( $mdata );
            if( $debug ) echo "<br />Check Duplicates quantity: " . count( $allmedia );
            foreach( $allmedia AS $idmedia => $files ){
                if( is_array( $files ) 
                && count( $files ) > 1
                ){
                    if( $debug ) echo "<br />Q: " . $quantity;
                    $ffiles = array();
                    foreach( $files AS $file ){
                        if( $days > 0
                        && ( time() - filemtime( $file[ 'file' ] ) ) > ( $days * 86400 ) ){
                            $ffiles[] = $file;
                        }
                    }
                    if( $debug ) echo "<br />FILES OLD: " . count( $ffiles );
                    $max = count( $ffiles ) - 1;
                    while( count( $ffiles ) > 1 ){
                        //Get qualitys and delete lowquality elements
                        if( ( $vq = ffmpeg_media_compare( $files[ 0 ], $files[ 1 ] ) ) != FALSE 
                        && is_array( $vq )
                        && array_key_exists( 'idmedia', $vq )
                        ){
                            if( $debug ) echo "<br />COMPARING: " . $files[ 0 ][ 'file' ] . '->' . $files[ 1 ][ 'file' ];
                            if( $debug ) echo "<br />UNLINK: " . $vq[ 'idmedia' ] . '->' . $vq[ 'file' ];
                            if( $echo ) echo '-';
                            $quantity--;
                            @unlink( $vq[ 'file' ] );
                            @sqlite_media_delete( $vq[ 'idmedia' ] );
                            $ffiles2 = $ffiles;
                            $ffiles = array();
                            foreach( $ffiles2 AS $row ){
                                if( $row[ 'idmedia' ] != $vq[ 'idmedia' ] ){
                                    $ffiles[] = $row;
                                }
                            }
                        }
                        $max--;
                        if( $max <= 0 ){
                            $ffiles = array();
                        }
                    }
                }
                if( $quantity <= 0 ){
                    break;
                }
            }
        }
        
        return $result;
	}
	
	function mediainfo_clean_not_ident_files( $quantity = 10, $echo = FALSE, $debug = FALSE ){
        $result = TRUE;
        
        if( defined( 'O_CRON_CLEAN_NOTIDENT_MEDIA' )
        && O_CRON_CLEAN_NOTIDENT_MEDIA > 0
        && ( $mdata = sqlite_media_getdata_identify_tryed( FALSE, 10000 ) ) != FALSE 
        && is_array( $mdata )
        && count( $mdata ) > 0
        ){
            if( $debug ) echo "<br />CHECKING: " . count( $mdata );
            foreach( $mdata AS $media ){
                if( is_array( $media ) 
                && array_key_exists( 'file', $media )
                ){
                    if( $debug ) echo "<br />Q: " . $quantity;
                    if( ( time() - filemtime( $media[ 'file' ] ) ) < ( O_CRON_CLEAN_NOTIDENT_MEDIA * 86400 ) 
                    ){
                        if( $debug ) echo "<br />UNLINK: " . $media[ 'idmedia' ] . '->' . $media[ 'file' ];
                        if( $echo ) echo '-';
                        $quantity--;
                        @unlink( $media[ 'file' ] );
                        @sqlite_media_delete( $media[ 'idmedia' ] );
                        
                    }
                }
                if( $quantity <= 0 ){
                    break;
                }
            }
        }
        
        return $result;
	}
	
	function media_scan_downloads( $quantity = 10, $recursive = FALSE, $echo = FALSE ){
        $result = TRUE;
        
        $all = 100000;
        if( ( $elements = getFilesFilterLimit( PPATH_DOWNLOADS, $all, '/./', FALSE ) ) != FALSE 
        ){
            foreach( $elements AS $file ){
                //var_dump( $file );echo '<br />';
                if( media_scan_exclude_files( $file ) == FALSE
                && media_scan_exclude_folders( $file ) == FALSE
                && sqlite_media_check_exist( $file ) == FALSE
                && file_exists( $file ) 
                && stripos( getFileMimeType( $file ), 'video' ) !== FALSE
                && sqlite_media_insert( $file )
                ){
                    $quantity--;
                    if( $echo ) echo "+";
                }
                if( $quantity <= 0 ){
                    break;
                }
            }
        }
        
        return $result;
	}
	
	function media_extract_files( $quantity = 10, $recursive = FALSE, $echo = FALSE ){
        $result = TRUE;
        
        $all = 100000000;
        
        if( ( $elements = getFilesFilterLimit( PPATH_DOWNLOADS, $all, '/.+\.((part01\.)*rar|\.r01|7z(\.\d{0,4})*|zip(\.\d{0,4}))$/' ) ) != FALSE 
        ){
            foreach( $elements AS $file ){
                
                $mpartvalid = checkCompressesMultipartValid( $file );
                $mpartcompleted = checkCompressesMultipartCompleted( $file );
                
                if( $mpartvalid == FALSE ){
                    //multipart >1
                    //if( $echo ) echo "<br />MPi: " . $file;
                    if( $echo ) echo "MP ";
                //check incomplete multiple download
                }elseif( $mpartvalid
                && $mpartcompleted == FALSE 
                ){
                    //incomplete multiple download
                    //if( $echo ) echo "<br />MIi: " . $file;
                    if( $echo ) echo "MIi ";
                //Exclude multipart files
                }elseif( !file_exists( $file ) ){
                    //deleted?
                    //if( $echo ) echo "<br />FNEi: " . $file;
                    if( $echo ) echo "FNEi ";
                //Generic extractor CMDs
                }elseif( ( $cmde = checkCompressesCMDValid( $file ) ) != FALSE
                && !file_exists( $file . '_' )
                ){
                    //echo "<br />" . $cmde;
                    //if( $echo ) echo "<br />GDi: " . $file;
                    if( $echo ) echo "GDi ";
                    runExtCommandNoRedirect( $cmde );
                    $quantity--;
                //Extension extractors
                }elseif( endsWith( $file, '.zip' )
                && !file_exists( $file . '_' )
                ){
                    extractZip( $file );
                    $quantity--;
                    //if( $echo ) echo "<br />GZi: " . $file;
                    if( $echo ) echo "GZi ";
                }elseif( endsWith( $file, '.rar' )
                && !file_exists( $file . '_' )
                ){
                    extractRar( $file );
                    $quantity--;
                    //if( $echo ) echo "<br />GRi: " . $file;
                    if( $echo ) echo "GRi ";
                }elseif( endsWith( $file, '.7z' )
                && !file_exists( $file . '_' )
                ){
                    //TODO
                    extract7z( $file );
                    $quantity--;
                    //if( $echo ) echo "<br />G7i: " . $file;
                    if( $echo ) echo "G7i ";
                }
                
                if( defined( 'O_CRON_EXTRACTFILES_CLEAN' ) 
                && O_CRON_EXTRACTFILES_CLEAN > 0
                && file_exists( $file . '_' )
                && is_dir( $file . '_' )
                //days from extracted
                && (
                    ( time() - filemtime( $file . '_' ) ) > ( O_CRON_EXTRACTFILES_CLEAN * 86400 )
                    //or base file time *2
                    || 
                        ( file_exists( $file )
                        && ( time() - filemtime( $file ) ) > ( O_CRON_EXTRACTFILES_CLEAN * 86400 * 2 )
                        )
                )
                ){
                    @unlink( $file );
                    //TEST remove all compressed files on folder
                    deletedCompressedMultipart( $file, 999, $echo );
                    //if( $echo ) echo "<br />DELETE: " . $file;
                    if( $echo ) echo "- ";
                }
                if( $quantity <= 0 ){
                    break;
                }
            }
        }
        
        return $result;
	}
	
	function checkCompressesMultipartCompleted( $file, $limit = 1000, $echo = FALSE ){
        //check on folder if multiple file have incomplete downloads
        $result = TRUE;
        $filename = basename( $file );
        $path = dirname( $file );
        
        if( defined( 'O_DOWNLOADS_FILES_EXCLUDE' ) 
        && is_array( O_DOWNLOADS_FILES_EXCLUDE )
        ){
            $extin = O_DOWNLOADS_FILES_EXCLUDE;
        }else{
            $extin = array();
        }
        
        //add basic tmp ext
        $extin[] = '.temp';
        $extin[] = '.tmp';
        
        //check file with temp extensions exist
        $fn = $filename;
        for( $x = 0; $x < 10; $x++ ){
            $fn = str_ireplace( $x, '?', $fn );
        }
        $filetest = $path . DS . $fn;
        foreach( $extin AS $ext ){
            if( count( glob( $filetest . $ext ) ) > 0 ){
                $result = FALSE;
                break;
            }
        }
        
        return $result;
	}
	
	function deletedCompressedMultipart( $file, $max = 999, $echo = FALSE ){
        //delete all compressed with same name on folder
        $limit = 1000;
        $result = TRUE;
        $filename = basename( $file );
        $path = dirname( $file );
        
        if( ( $pfile = explode( '.', $filename ) ) != FALSE 
        && is_array( $pfile )
        && count( $pfile ) > 0
        ){
            $pfilename = preg_quote( $pfile[ 0 ] ) . '.*(rar|7z|zip).*';
        }else{
            $pfilename = preg_quote( $filename );
        }
        $filter = '/' . $pfilename . '/';
        if( ( $elements = getFilesFilterLimit( $path, $limit, $filter ) ) != FALSE 
        ){
            foreach( $elements AS $f ){
                if( is_file( $f ) ){
                    if( $echo ) echo "-";
                    @unlink( $f );
                }
            }
        }
        
        return $result;
	}
	
	function checkCompressesMultipartValid( $file, $max = 999 ){
        //exclude multipart files != first file
        $result = TRUE;
        
        $rar = array( '/.+part\d{1,3}\.rar/', '/.+part0{0,2}1\.rar/' );
        $zip = array( '/.+zip\.\d{1,3}/', '/.+zip\.0{0,2}1/' );
        $z7z = array( '/.+7z\.\d{1,3}/', '/.+7z\.0{0,2]1/' );
        
        for( $x = 2; $x < $max; $x++ ){
            //rar: partX.rar
            //zip: zip.00X
            //7z: 7z.00X
            if( ( preg_match( $rar[ 0 ], $file ) && preg_match( $rar[ 1 ], $file ) == FALSE )
            || ( preg_match( $zip[ 0 ], $file ) && preg_match( $zip[ 1 ], $file ) == FALSE )
            || ( preg_match( $z7z[ 0 ], $file ) && preg_match( $z7z[ 1 ], $file ) == FALSE )
            ){
                $result = FALSE;
                break;
            }
        }
        
        return $result;
	}
	
	function checkCompressesCMDValid( $file ){
        $result = FALSE;
        
        if( defined( 'O_CRON_EXTRACTFILES_CMD' ) 
        && is_array( O_CRON_EXTRACTFILES_CMD )
        && count( O_CRON_EXTRACTFILES_CMD ) > 0
        && file_exists( $file )
        && is_file( $file )
        ){
            $cmds = O_CRON_EXTRACTFILES_CMD;
            foreach( $cmds AS $ext => $cmd ){
                if( endsWith( $file, $ext ) 
                || stripos( $file, $ext ) !== FALSE
                ){
                    //%FILE% %FOLDER% %PASS%
                    $cmd = str_replace( '%FILE%', $file, $cmd );
                    $foldere = dirname( $file ) . DS . basename( $file ) . '_';
                    $cmd = str_replace( '%FOLDER%', $foldere, $cmd );
                    //TODO
                    //$cmd = str_replace( '%PASS%', $file, $cmd );
                    $result = $cmd;
                    break;
                }
            }
        }
        
        return $result;
	}
	
	function mediainfo_clean_duplicates( $echo = FALSE ){
        $result = TRUE;
        
        if( ( $midata = sqlite_mediainfo_getdata_duplys( 1000 ) ) ){
            //all
            if( $echo ) echo '<br >Duplicates found: ' . count( $midata );
            foreach( $midata AS $row ){
                if( $row[ 'idmediainfo' ] > 0 
                && ( $milist = sqlite_mediainfo_search_duplys( $row[ 'title' ], $row[ 'season' ], $row[ 'episode' ], $row[ 'year' ] ) ) != FALSE
                && is_array( $milist )
                && count( $milist ) > 1
                ){
                    if( $echo ) echo '<br >Duplicates: ' . $row[ 'idmediainfo' ] . '/' . $row[ 'title' ] . '/' . $row[ 'year' ] . '/' . $row[ 'num' ];
                    $nowidmediainfo = 0;
                    foreach( $milist AS $n => $row2 ){
                        if( $n == 0 ){
                            if( $echo ) echo '<br >Base idmediainfo: ' . $row2[ 'idmediainfo' ];
                            $nowidmediainfo = $row2[ 'idmediainfo' ];
                        }else{
                            if( ( $m = sqlite_media_getdata_mediainfo( $row2[ 'idmediainfo' ] ) ) != FALSE 
                            && is_array( $m )
                            && count( $m ) > 0
                            ){
                                foreach( $m AS $r ){
                                    if( $echo ) echo '<br >File in delete: ' . $r[ 'file' ];
                                    if( sqlite_media_update_mediainfo( $r[ 'idmedia' ], $nowidmediainfo ) ){
                                        if( $echo ) echo '<br >File reasigned: ' . $r[ 'file' ] . ' -> ' . $nowidmediainfo;
                                    }else{
                                        if( $echo ) echo '<br >Error reasign: ' . $r[ 'file' ] . ' -> ' . $nowidmediainfo;
                                    }
                                }
                            }else{
                                if( $echo ) echo '<br >No files for duplicated: ' . $row2[ 'idmediainfo' ];
                            }
                            if( sqlite_mediainfo_delete( $row2[ 'idmediainfo' ] ) ){
                                if( $echo ) echo '<br >Duped mediainfo deleted: ' . $row2[ 'idmediainfo' ] . ' -> ' . $nowidmediainfo;
                            }else{
                                if( $echo ) echo '<br >ERROR Duped mediainfo deleted: ' . $row2[ 'idmediainfo' ] . ' -> ' . $nowidmediainfo;
                            }
                        }
                    }
                }
            }
        }
        
        return $result;
	}
	
	function media_scrap_downloads( $quantity = 10, $recursive = FALSE, $echo = FALSE ){
        global $G_SCRAPPERS;
        $result = TRUE;
        
        if( array_key_exists( O_SCRAP_CRON, $G_SCRAPPERS )
        && is_array( $G_SCRAPPERS[ O_SCRAP_CRON ] )
        && array_key_exists( 1, $G_SCRAPPERS[ O_SCRAP_CRON ] )
        && function_exists( $G_SCRAPPERS[ O_SCRAP_CRON ][ 1 ] )
        && ( $mdata = sqlite_media_getdata_identify_auto( '', $quantity ) ) != FALSE 
        ){
            foreach( $mdata AS $media ){
                //set to auto-updated: idmediainfo = -1
                @sqlite_media_update_mediainfo( $media[ 'idmedia' ], -1 );
                
                //use media_scrap_idmedia
                media_scrap_idmedia( $media[ 'idmedia' ], $echo );
                
                if( $echo ) echo "+";
            }
        }
        
        return $result;
	}
	
	function media_scrap_idmedia( $idmedia, $echo = FALSE ){
        global $G_SCRAPPERS;
        $result = TRUE;
        
        if( array_key_exists( O_SCRAP_CRON, $G_SCRAPPERS )
        && is_array( $G_SCRAPPERS[ O_SCRAP_CRON ] )
        && array_key_exists( 1, $G_SCRAPPERS[ O_SCRAP_CRON ] )
        && function_exists( $G_SCRAPPERS[ O_SCRAP_CRON ][ 1 ] )
        && ( $mdata = sqlite_media_getdata( $idmedia, 1 ) ) != FALSE 
        ){
            foreach( $mdata AS $media ){
                //set to auto-updated: idmediainfo = -1
                @sqlite_media_update_mediainfo( $media[ 'idmedia' ], -1 );
                $title = basename( $media[ 'file' ] );
                //ADD folder name
                if( ( $title2 = basename( dirname( $media[ 'file' ] ) ) ) != FALSE 
                && strlen( clean_filename( $title2 ) ) > 3
                && $title2 != basename( PPATH_DOWNLOADS )
                //&& strlen( clean_filename( $title2 ) ) > strlen( clean_filename( $title ) )
                ){
                    $title2 = clean_filename( $title2 );
                }else{
                    $title2 = '';
                }
                if( $echo ) echo '<br />TITLE1: ' . $title;
                if( $echo ) echo '<br />TITLE2: ' . $title2;
                $IDMEDIA = $media[ 'idmedia' ];
                $type = TRUE;//Movies
                $season = FALSE;
                $episode = FALSE;
                if( ( $season_e = get_media_chapter( $title ) ) != FALSE
                || ffmpeg_file_info_lenght_seconds( $media[ 'file' ] ) < 3600
                ){
                    if( $echo ) echo get_msg( 'IDENT_FILETODETECTED' ) . 'TV';
                    $type = FALSE;//Series
                    if( is_array( $season_e ) ){
                        $season = $season_e[ 0 ];
                        $episode = $season_e[ 1 ];
                        $se = $season . 'x' . sprintf( '%02d', $episode );
                        $title = clean_media_chapter( $title, $se );
                        $title2 = clean_media_chapter( $title2, $se );
                        //cut on seasonXchapter string
                        if( ( $d = explode( $se, $title ) ) != FALSE 
                        && count( $d ) > 1
                        && strlen( $d[ 0 ] ) > 5
                        ){
                            $title=$d[ 0 ];
                        }
                        if( ( $d = explode( $se, $title2 ) ) != FALSE 
                        && count( $d ) > 1
                        && strlen( $d[ 0 ] ) > 5
                        ){
                            $title2=$d[ 0 ];
                        }
                    }else{
                        //set out season and chapter
                        $season = 1;
                        $chapter = 99;
                    }
                }else{
                    if( $echo ) echo get_msg( 'IDENT_FILETODETECTED' ) . 'Movie';
                }
                //clean filename title
                $title = clean_filename( $title );
                //exclude filebot, not use of imdbid
                if( O_SCRAP_CRON == 'filebot' || O_SCRAP_CRON == 'pymi' ){
                    $imdbid = FALSE;
                }else{
                    //$imdbid = scrap_all_nearest_title( $title, $type );
                    $imdbid = FALSE;
                }
                
                if( $echo ) echo '<br />TITLEIDENT1: ' . $title;
                if( $echo ) echo '<br />TITLEIDENT2: ' . $title2;
                if( $echo ) echo get_msg( 'IDENT_FILETODETECTED' ) . ' ' . basename( $media[ 'file' ] ) . ' ' . $season . 'x' . $episode . ' - ' . $imdbid;
                
                if( 
                    
                //OWNDB foldername TEST more priority
                    (
                        strlen( $title2 ) > 0
                        && ( $info_data = ident_detect_file_db( $media[ 'file' ], $title2, $type, $imdbid, $season, $episode ) ) != FALSE 
                        && array_key_exists( 'data', $info_data )
                        && is_array( $info_data[ 'data' ] )
                        && array_key_exists( 'title', $info_data[ 'data' ] )
                        && array_key_exists( 'year', $info_data[ 'data' ] )
                        && strlen( $info_data[ 'data' ][ 'title' ] ) > 0
                    )
                //OWNDB filename
                || (
                        ( $info_data = ident_detect_file_db( $media[ 'file' ], $title, $type, $imdbid, $season, $episode ) ) != FALSE 
                        && array_key_exists( 'data', $info_data )
                        && is_array( $info_data[ 'data' ] )
                        && array_key_exists( 'title', $info_data[ 'data' ] )
                        && array_key_exists( 'year', $info_data[ 'data' ] )
                        && strlen( $info_data[ 'data' ][ 'title' ] ) > 0
                    )
                //SCRAP CRON filename
                || ( 
                        ( $info_data = $G_SCRAPPERS[ O_SCRAP_CRON ][ 1 ]( $media[ 'file' ], $title, $type, $imdbid, $season, $episode ) ) != FALSE 
                        && array_key_exists( 'data', $info_data )
                        && is_array( $info_data[ 'data' ] )
                        && array_key_exists( 'title', $info_data[ 'data' ] )
                        && array_key_exists( 'year', $info_data[ 'data' ] )
                        && strlen( $info_data[ 'data' ][ 'title' ] ) > 0
                    )
                //SCRAP CRON fodlername
                || (
                        strlen( $title2 ) > 0
                        && ( $info_data = $G_SCRAPPERS[ O_SCRAP_CRON ][ 1 ]( $media[ 'file' ], $title2, $type, $imdbid, $season, $episode ) ) != FALSE 
                        && array_key_exists( 'data', $info_data )
                        && is_array( $info_data[ 'data' ] )
                        && array_key_exists( 'title', $info_data[ 'data' ] )
                        && array_key_exists( 'year', $info_data[ 'data' ] )
                        && strlen( $info_data[ 'data' ][ 'title' ] ) > 0
                    )
                
                ){
                    if( $echo ) echo get_msg( 'IDENT_DETECTED' ) . $info_data[ 'data' ][ 'title' ] . ' ' . $info_data[ 'data' ][ 'year' ];
                    //check duplicates in idmediainfo
                    if( ( $idmediainfo = sqlite_mediainfo_check_exist( $info_data[ 'data' ][ 'title' ], $info_data[ 'data' ][ 'season' ], $info_data[ 'data' ][ 'episode' ] ) ) != FALSE 
                    ){
                        //finded, update and assign
                        if( $echo ) echo get_msg( 'DEF_EXIST' );
                        $info_data[ 'data' ][ 'idmediainfo' ] = $idmediainfo;
                        if( sqlite_mediainfo_update( $info_data[ 'data' ] ) 
                        && sqlite_media_update_mediainfo( $IDMEDIA, $idmediainfo )
                        ){
                            if( $echo ) echo get_msg( 'IDENT_DETECTEDOK' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $media[ 'file' ];
                        }else{
                            if( $echo ) echo get_msg( 'IDENT_DETECTEDKO' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $media[ 'file' ];
                        }
                    }else{
                        //NOT finded, update and assign
                        if( $echo ) echo get_msg( 'DEF_NOTEXIST' );
                        //var_dump( $info_data[ 'data' ] );
                        if( ( $idmediainfo = sqlite_mediainfo_insert( $info_data[ 'data' ] ) ) != FALSE
                        && sqlite_media_update_mediainfo( $IDMEDIA, $idmediainfo )
                        ){
                            //Copy needed files
                            foreach( $info_data AS $kif => $vif ){
                                if( $kif != 'data' 
                                && file_exists( $vif )
                                && getFileMimeTypeImg( $vif )
                                ){
                                    $numtimes = 3;
                                    if( O_MAX_IMG_FILES > 0 )
                                    while( filesize( $vif ) > O_MAX_IMG_FILES 
                                    && filesize( $vif ) > 0
                                    && $numtimes > 0
                                    ){
                                        if( !resize_img_div2( $vif ) 
                                        ){
                                            break;
                                        }
                                        $numtimes--;
                                    }
                                    //copy file to format idmediainfo.poster|landscape|...
                                    $imgfile = PPATH_MEDIAINFO . DS . $idmediainfo . '.' . $kif;
                                    if( file_exists( $imgfile ) ){
                                        @unlink( $imgfile );
                                    }
                                    if( @link( $vif, $imgfile ) 
                                    || @copy( $vif, $imgfile )
                                    ){
                                        if( $echo ) echo get_msg( 'DEF_COPYOK' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $kif;
                                    }else{
                                        if( $echo ) echo get_msg( 'DEF_COPYKO' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $kif;
                                    }
                                }
                            }
                            if( $echo ) echo get_msg( 'IDENT_DETECTEDOK' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $media[ 'file' ];
                        }else{
                            if( $echo ) echo get_msg( 'IDENT_DETECTEDKO' ) . ' ' . $info_data[ 'data' ][ 'title' ] . ' => ' . $media[ 'file' ];
                        }
                    }
                }else{
                    if( $echo ) echo get_msg( 'IDENT_NOTDETECTED' );
                }
                if( $echo ) echo "+";
            }
        }
        
        return $result;
	}
	
	function media_clean_downloads( $quantity = 10, $echo = FALSE ){
        $result = TRUE;
        
        if( ( $mdata = sqlite_media_getdata( FALSE, 100000 ) ) != FALSE 
        && is_array( $mdata )
        && count( $mdata ) > 0
        ){
            foreach( $mdata AS $media ){
                if( $quantity > 0
                && array_key_exists( 'file', $media )
                && !@file_exists( $media[ 'file' ] ) 
                ){
                    if( $echo ) echo "-";
                    sqlite_media_delete( $media[ 'idmedia' ] );
                    $quantity--;
                    if( $quantity <= 0 ){
                        //break;
                    }
                }
                //check valid idmediainfo
                if( array_key_exists( 'idmediainfo', $media ) 
                && $media[ 'idmediainfo' ] > 0
                && (
                    ( $minfo = sqlite_mediainfo_getdata( $media[ 'idmediainfo' ] ) ) == FALSE
                    || !is_array( $minfo )
                    || count( $minfo ) == 0
                    )
                ){
                    sqlite_media_update_mediainfo( $media[ 'idmedia' ], 0 );
                    if( $echo ) echo "+";
                }
            }
        }
        
        return $result;
	}
	
	function media_clean_duplicated( $quantity = 10, $echo = FALSE ){
        $result = TRUE;
        
        if( ( $mdata = sqlite_media_getdata( FALSE, 100000 ) ) != FALSE 
        && is_array( $mdata )
        && count( $mdata ) > 0
        ){
            $lastfile = '';
            foreach( $mdata AS $media ){
                if( $lastfile != $media[ 'file' ] ){
                    $lastfile = $media[ 'file' ];
                }elseif( $lastfile == $media[ 'file' ] ){
                    if( $echo ) echo "-";
                    sqlite_media_delete( $media[ 'idmedia' ] );
                    $quantity--;
                    if( $quantity <= 0 ){
                        break;
                    }
                }
            }
        }
        
        return $result;
	}
	
	function media_clean_imgs( $quantity = 100, $recursive = FALSE, $echo = FALSE ){
        $result = TRUE;
        $hash_list = array(); //hash => ( file1, file2, ...)
        
        if( ( $mdata = getFiles( PPATH_MEDIAINFO, TRUE ) ) != FALSE 
        && is_array( $mdata )
        && count( $mdata ) > 0
        ){
            foreach( $mdata AS $file ) {
                if( file_exists( $file )
                && is_file( $file )
                && !is_link( $file )
                && ( $temp_hash = crc32( file_get_contents( $file ) ) ) != FALSE
                ){
                    $hash_list[ $temp_hash ][] = $file;
                }
            }
            foreach( $hash_list AS $hsah => $files ){
                if( $quantity > 0
                && count( $files ) > 1 ){
                    $firstfile = FALSE;
                    foreach( $files AS $file ){
                        if( $firstfile == FALSE ){
                            $firstfile = $file;
                        }elseif( ( $filestat = stat( $file ) ) != FALSE 
                        && array_key_exists( 'nlink', $filestat )
                        && (int)$filestat[ 'nlink' ] == 1
                        && @unlink( $file )
                        && @link( $firstfile, $file )
                        ){
                            if( $echo ) echo "+";
                            $quantity--;
                        }
                        if( $quantity <= 0 ){
                            break;
                        }
                    }
                }
                if( $quantity <= 0 ){
                    break;
                }
            }
        }
        
        return $result;
	}
	
	function media_fill_imgs( $quantity = 100, $echo = FALSE ){
        $result = TRUE;
        global $G_MEDIADATA;
        $fullfiles = count( $G_MEDIADATA ) - 1;
        
        if( ( $midata = sqlite_media_getdata_filtered_grouped( '', 10000, 0, TRUE ) ) != FALSE ){
            foreach( $midata AS $row ){
                if( ( $miepisodes = sqlite_media_getdata_chapters( $row[ 'title' ] ) ) != FALSE ){
                    //search all files needed
                    $basefiles = array();
                    foreach( $miepisodes AS $row2 ){
                        foreach( $G_MEDIADATA AS $filetype => $value ){
                            if( is_string( $value ) ){
                                $file = PPATH_MEDIAINFO . DS . $row2[ 'idmediainfo' ] . '.' . $filetype;
                                if( file_exists( $file ) ){
                                    $basefiles[ $filetype ] = $file;
                                }
                                if( count( $basefiles ) == $fullfiles ) break;
                            }
                        }
                        if( count( $basefiles ) == $fullfiles ) break;
                    }
                    //create needed
                    foreach( $miepisodes AS $row2 ){
                        foreach( $basefiles AS $filetype => $filebase ){
                            $file = PPATH_MEDIAINFO . DS . $row2[ 'idmediainfo' ] . '.' . $filetype;
                            if( $file != $filebase 
                            && !file_exists( $file )
                            ){
                                if( !@link( $filebase, $file ) ){
                                    @copy( $filebase, $file );
                                }
                                if( $echo ) echo "+";
                                $quantity--;
                                if( $quantity <= 0 ) break;
                            }
                        }
                        if( $quantity <= 0 ) break;
                    }
                }
                if( $quantity <= 0 ) break;
            }
        }
        
        return $result;
	}
	
	
	function media_scan_exclude_files( $file ){
        $result = FALSE;
        
        if( defined( 'O_DOWNLOADS_FILES_EXCLUDE' ) 
        && is_array( O_DOWNLOADS_FILES_EXCLUDE )
        ){
            foreach( O_DOWNLOADS_FILES_EXCLUDE AS $e ){
                if( endsWith( $file, $e ) ){
                    $result = TRUE;
                    break;
                }
            }
        }
        
        return $result;
	}
	
	function media_scan_exclude_folders( $file ){
        $result = FALSE;
        
        if( defined( 'O_DOWNLOADS_FOLDERS_EXCLUDE' ) 
        && is_array( O_DOWNLOADS_FOLDERS_EXCLUDE )
        ){
            foreach( O_DOWNLOADS_FOLDERS_EXCLUDE AS $e ){
                if( stripos( $file, $e ) !== FALSE ){
                    $result = TRUE;
                    break;
                }
            }
        }
        
        return $result;
	}
	
	function get_year( $title, $DEBUG = FALSE ){
        $result = '';
        //extract year, not clean
        preg_match_all('!\d+!', $title, $years );
        
        if( $DEBUG ) var_dump( $years );
        
        if( is_array( $years ) 
        && count( $years ) > 0
        && array_key_exists( 0, $years )
        && is_array( $years[ 0 ] ) 
        ){
            foreach( $years[ 0 ] AS $y ){
                if( $y >= MEDIA_CHAPTERS_MINYEAR 
                && $y <= MEDIA_CHAPTERS_MAXYEAR
                ){
                    $result = $y;
                    break;
                }
            }
        }
        
        
        return $result;
	}
	
	function clean_filename( $file, $clean_year = FALSE ){
        //CLEAN NAME
        global $G_CLEAN_FILENAME;
        $filename = basename( $file );
        $CLEAN_NAME = $G_CLEAN_FILENAME;
        $DEBUG = FALSE;
        
        array_multisort(array_map('strlen', $CLEAN_NAME), $CLEAN_NAME);
        $CLEAN_NAME = array_reverse( $CLEAN_NAME );
        
        //extract year, not clean
        $YEAR = get_year( $filename, $DEBUG );
        
        //Clean all between: [] ()
        $CLEANNAME = $filename;
        $CLEANNAME = preg_replace('/\[[\s\S]+?\]/', '', $CLEANNAME);
        $CLEANNAME = preg_replace('/\([\s\S]+?\)/', '', $CLEANNAME);
        //clear excess (... [...
        $CLEANNAME = preg_replace('/\[[\s\S]+?/', '', $CLEANNAME);
        //$CLEANNAME = preg_replace('/\([\s\S]+?/', '', $CLEANNAME);
        if( $DEBUG ) var_dump( $CLEANNAME );
        
        //Clean domains
        $CLEANNAME = clean_string_domains( $CLEANNAME, $DEBUG );
        if( $DEBUG ) var_dump( $CLEANNAME );
        
        //Exclude strings
        foreach( $CLEAN_NAME AS $clean ){
            $CLEANNAME = str_ireplace( $clean, '', $CLEANNAME );
        }
        if( $DEBUG ) var_dump( $CLEANNAME );
        
        //CLEAN EXTRAS
        $CLEANNAME = str_ireplace( '  ', ' ', $CLEANNAME );
        $CLEANNAME = str_ireplace( '..', '.', $CLEANNAME );
        $CLEANNAME = str_ireplace( '.', ' ', $CLEANNAME );
        $CLEANNAME = str_ireplace( '_', ' ', $CLEANNAME );
        $CLEANNAME = trim( $CLEANNAME );
        if( $DEBUG ) var_dump( $CLEANNAME );
        
        //Put space before uppercases if not uppercase before
        // /(?<! )(?<!^)(?<![A-Z])[A-Z]/
        // /(?<!\ )[A-Z]/
        $preg = '/(?<! )(?<!^)(?<![A-Z])[A-Z]/';
        //$preg = '/(?<!\ )[A-Z]/';
        $CLEANNAME = preg_replace( $preg, ' $0', $CLEANNAME );
        if( $DEBUG ) var_dump( $CLEANNAME );
        
        if( $clean_year == FALSE ){
            $CLEANNAME .= ' ' . $YEAR;
        }
        
        $CLEANNAME = str_ireplace( '  ', ' ', $CLEANNAME );
        $CLEANNAME = trim( $CLEANNAME );
        
        if( strlen( $CLEANNAME ) == 0 ){
            $CLEANNAME = $filename;
        }
        
        return $CLEANNAME;
	}
	
    function clean_string_domains( $string, $debug = FALSE ){
        $pattern ='/([wW]{3,3}.|)[A-Za-z0-9]+?\.(aero|asia|biz|cat|com|coop|edu|gov|info|int|jobs|mil|mobi|museum|name|net|org|pro|tel|travel|ac|ad|ae|af|ag|ai|al|am|an|ao|aq|ar|as|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|cr|cu|cv|cx|cy|cz|cz|de|dj|dk|dm|do|dz|ec|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|io|iq|ir|is|it|je|jm|jo|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mk|ml|mn|mn|mo|mp|mr|ms|mt|mu|mv|mw|mx|my|mz|na|nc|ne|nf|ng|ni|nl|no|np|nr|nu|nz|nom|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|ps|pt|pw|py|qa|re|ra|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|yt|yu|za|zm|zw|arpa)[^\w\d\.]/i';
        
        $result = preg_replace($pattern, '', $string);
        
        return $result;
    }
    
    //SEASON && CHAPTERS
    
    function get_media_chapter( $title ){
        $result = FALSE;
        $min_year = MEDIA_CHAPTERS_MINYEAR;
        $max_year = MEDIA_CHAPTERS_MAXYEAR;
        $exclude = MEDIA_CHAPTERS_EXCLUDE;
        
        //Clean domains, can have numbers
        $title = clean_string_domains( basename( $title ), TRUE );
        
        $re = MEDIA_CHAPTERS;
        
        foreach( $re AS $r ){
            if( preg_match( $r, $title, $match ) 
            && is_array( $match )
            && count( $match ) > 2
            ){
                //0=>string, 1=>season ,2=>chapter
                //check != years
                if( is_numeric( $match[ 0 ] ) 
                && (int)$match[ 0 ] >= $min_year
                && (int)$match[ 0 ] <= $max_year
                ){
                    //exclude by valid year posibility
                }elseif( is_numeric( $match[ 0 ] ) 
                && in_array( (int)$match[ 0 ], $exclude )
                ){
                    //exclude by generic string for codecs or width-height
                }elseif( (
                    !is_numeric( $match[ 1 ] ) 
                    || (int)$match[ 1 ] < 1
                    || (int)$match[ 1 ] > MEDIA_CHAPTERS_MAXSEASON
                )
                || (
                    !is_numeric( $match[ 2 ] ) 
                    || (int)$match[ 2 ] < 1
                    || (int)$match[ 2 ] > MEDIA_CHAPTERS_MAXCHAPTER
                )
                ){
                    //exclude: invalid season or chapter
                }else{
                    $result = array( (int)$match[ 1 ], (int)$match[ 2 ], $match[ 0 ] );
                    break;
                }
            }
        }
        
        return $result;
    }
    
    function clean_media_chapter( $title, $replace = '' ){
        $result = $title;
        
        if( ( $mchapters = get_media_chapter( $title ) ) != FALSE
        && is_array( $mchapters )
        && count( $mchapters ) > 2
        ){
            $result = str_ireplace( $mchapters[ 2 ], $replace,  $title );
            $result = trim( $result );
        }else{
            $result = $title . ' ' . $replace;
            $result = trim( $result );
        }
        
        return $result;
    }
    
	//IMAGES
	
	function resize_img_div2( $img ){
        $result = FALSE;
        
        if( filesize( $img ) > 0
        && getFileMimeTypeImg( $img ) 
        && ( $size = @getimagesize( $img ) ) != FALSE
        ){
            $width = (int)( $size[ 0 ] / 2 );
            $height = (int)( $size[ 1 ] / 2 );
            if( $size[ 0 ] > $width ){
                $src = imagecreatefromstring( file_get_contents( $img ) );
                $dst = imagecreatetruecolor( $width, $height );
                imagecopyresampled( $dst, $src, 0, 0, 0, 0, $width, $height, $size[0], $size[1] );
                imagedestroy( $src );
                unlink( $img );
                $result = imagepng( $dst, $img, 6 ); // adjust format as needed
                imagedestroy( $dst );
            }
        }
		return $result;
	}
	
	//CLEAN TEMP FOLDER
	
	function media_clean_temp_folder( $minutes = 1 ){
        $result = 0;
        
        if( ( $folders = getFolders( PPATH_TEMP ) ) != FALSE ){
            foreach( $folders AS $f ){
                if( file_exists( $f ) 
                && ( time() - filemtime( $f ) ) > ( 60 * $minutes )
                ){
                    @delTree( $f );
                    $result++;
                }
            }
        }
        
        return $result;
	}
	
	//Get Related to USER
	
	function media_get_recomended( $quantity = 1000 ){
        $result = FALSE;
        $in_list = array();
        
        //get last played and get related
        if( ( $played = sqlite_played_getdata( FALSE, '', TRUE ) ) != FALSE 
        && is_array( $played )
        && count( $played ) > 0
        ){
            foreach( $played AS $pd ){
                if( ( $md = sqlite_media_getdata( $pd[ 'idmedia' ] ) ) != FALSE
                && count( $md ) > 0
                && (int)$md[ 0 ][ 'idmediainfo' ] > 0
                && ( $mid = sqlite_mediainfo_getdata( $md[ 0 ][ 'idmediainfo' ] ) ) != FALSE
                && count( $mid ) > 0
                && ( $rel = sqlite_media_getdata_related( $mid[ 0 ][ 'genre' ] ) ) != FALSE
                && count( $rel ) > 0
                ){
                    if( !is_array( $result ) ){
                        $result = array();
                    }
                    foreach( $rel AS $r ){
                        if( $r[ 'idmediainfo' ] > 0
                        && !in_array( $r[ 'idmediainfo' ], $in_list ) 
                        ){
                            $in_list[] = $r[ 'idmediainfo' ];
                            $result[] = $r;
                            $quantity--;
                            if( $quantity <= 0 ){
                                break;
                            }
                        }
                    }
                }
                if( $quantity <= 0 ){
                    break;
                }
            }
        }
        
        //Get Best Of all time
        if( $quantity > 0
        && ( $best = sqlite_media_getdata_best( FALSE, $quantity * 2 ) ) != FALSE 
        && is_array( $best )
        && count( $best ) > 0
        ){
            foreach( $best AS $pd ){
                if( !in_array( $pd[ 'idmediainfo' ], $in_list ) ){
                    $in_list[] = $pd[ 'idmediainfo' ];
                    if( !is_array( $result ) ){
                        $result = array();
                    }
                    $result[] = $pd;
                    $quantity--;
                    if( $quantity <= 0 ){
                        break;
                    }
                }
            }
        }
        
        return $result;
	}
	
	//FILES COMPRESS
	
	function extractZip( $filezip, $extractfolder = FALSE ){
        $result = FALSE;
        
        if( $extractfolder == FALSE ){
            $extractfolder = dirname( $filezip ) . DS . basename( $filezip ) . '_';
        }
        
        if( class_exists( 'ZipArchive' ) ){
            $zip = new ZipArchive;
            if( $zip->open( $filezip ) === TRUE 
            && $zip->extractTo( $extractfolder ) !== FALSE
            && $zip->close() !== FALSE
            ){
                $result = TRUE;
            } else {
                $result = FALSE;
            }
        }
        
        return $result;
        
	}
	
	function extractRar( $filerar, $extractfolder = FALSE ){
        $result = FALSE;
        
        if( $extractfolder == FALSE ){
            $extractfolder = dirname( $filerar ) . DS . basename( $filerar ) . '_';
        }
        
        if( function_exists( 'rar_open' )
        && ( $rar_file = rar_open( $filerar ) ) != FALSE
        && ( $list = rar_list( $rar_file ) ) != FALSE
        ){
            foreach( $list as $file ){
                $entry = rar_entry_get( $rar_file, $file );
                $entry->extract( $extractfolder );
            }
            @rar_close($rar_file);
        }
        
        return $result;
        
	}
	
	function extract7z( $file7z, $extractfolder = FALSE ){
        $result = FALSE;
        
        if( $extractfolder == FALSE ){
            $extractfolder = dirname( $file7z ) . DS . basename( $file7z ) . '_';
        }
        
        //remake
        
        return $result;
        
	}
	
?>
