<?php
    
    if( !defined( 'PHP_SAPI' ) 
    || PHP_SAPI != 'cli'
    ){
        header("HTTP/1.1 401 Unauthorized");
		echo "HTTP/1.1 401 Unauthorized";
		die();
    }
    
	define( 'ACCESS', TRUE );
	
    //DEBUG
    error_reporting( E_ALL );
    ini_set( 'display_errors', '1' );
	
	//FOLDERS
	
	define( 'PPATH_BASE', dirname( dirname( __FILE__ ) ) );
	define( 'DS', DIRECTORY_SEPARATOR );
	define( 'PPATH_ACTIONS', PPATH_BASE . DS . 'actions'  );
	define( 'PPATH_CORE', PPATH_BASE . DS . 'core'  );
	define( 'PPATH_CACHE', PPATH_BASE . DS . 'cache'  );
	define( 'PPATH_TEMP', PPATH_CACHE . DS . 'temp'  );
	define( 'PPATH_LANG', PPATH_BASE . DS . 'lang'  );
	define( 'PPATH_MEDIAINFO', PPATH_CACHE . DS . 'mediadata' );//file: idmedia.type (poster, landscape, nfo, etc)
	define( 'PPATH_IMGS', PPATH_BASE . DS . 'imgs' );
	
	//CORE BASE
	require( PPATH_CORE . DS . 'functions.php' );
	
	//CONFIG
	require( PPATH_BASE . DS . 'config.php' );
	
	//CORE EXT
	require( PPATH_CORE . DS . 'functions.bd.php' );
	require( PPATH_CORE . DS . 'functions.media.php' );
	require( PPATH_CORE . DS . 'functions.scrap.php' );
	require( PPATH_CORE . DS . 'functions.webscrap.php' );
	require( PPATH_CORE . DS . 'functions.html.php' );
	require( PPATH_CORE . DS . 'functions.ffmpeg.php' );
	require( PPATH_CORE . DS . 'functions.cron.php' );
	
	//SCRAPPERS
	if( is_array( O_SCRAPPERS_INCLUDES )
	){
        foreach( O_SCRAPPERS_INCLUDES AS $wscrapper ){
            $ws_file = PPATH_CORE . DS . 'functions.' . $wscrapper . '.php';
            if( file_exists( $ws_file ) ) require( $ws_file );
        }
	}
	
	//BAN SYSTEM
	if( checkBannedIP( USER_IP ) 
	|| check_ip_country( USER_IP ) == FALSE
	){
		header("HTTP/1.1 401 Unauthorized");
		echo "HTTP/1.1 401 Unauthorized";
		exit();
	}
	
	//END BASE
	
	//LOGIN CHECK
	define( 'USERNAME', O_CRON_ADMINUSER );
	define( 'USERNAMEADMIN', O_CRON_ADMINUSER );
	
	//START
	echo "<br />--START--" . date( 'Y-m-d H:i:s' );
	
	//SCAN DUPLICATES MEDIAINFO (only scrapped files)
	echo "<br />" . date( 'Y-m-d H:i:s' );
	echo "<br />Scan Duplicated Media Downloads (" . O_CRON_CLEAN_DUPLICATES_MEDIAINFO . " Days Old): ";
	echo "<br />";
	mediainfo_clean_duplicated_files( 100, TRUE );
	
	//SCAN DOWNLOADED NOT IDENTIFIED MEDIA OLD
	echo "<br />" . date( 'Y-m-d H:i:s' );
	echo "<br />Scan Not Auto Identified Media Downloads (" . O_CRON_CLEAN_NOTIDENT_MEDIA . " Days Old): ";
	echo "<br />";
	mediainfo_clean_not_ident_files( 100, TRUE );
	
	//FILL CHAPTERS IMGs
	echo "<br />" . date( 'Y-m-d H:i:s' );
	echo "<br />Fill Chapters IMGs: ";
	echo "<br />";
	media_fill_imgs( 1000, TRUE, TRUE );
	
	//DOWNLOAD NEW IMGS
	echo "<br />" . date( 'Y-m-d H:i:s' );
	echo "<br />Download MediaInfo IMGs: ";
	echo "<br />";
	get_medinfo_images( 10, 'poster', TRUE, FALSE );
	
	//HARDLINK DUPLI CACHE IMGs
	echo "<br />" . date( 'Y-m-d H:i:s' );
	echo "<br />Clean Duply IMGs: ";
	echo "<br />";
	media_clean_imgs( 1000, TRUE, TRUE );
	
	//SCAN DOWNLOADED EXTRACK FILES
	if( defined( 'O_CRON_EXTRACTFILES' ) 
	&& O_CRON_EXTRACTFILES == TRUE
	){
        echo "<br />" . date( 'Y-m-d H:i:s' );
        echo "<br />Scan And Extract Media Downloads: ";
        echo "<br />";
        media_extract_files( 20, TRUE );
    }
	
	//CLEAN MEDIAINFO DUPLICATES
	if( defined( 'O_CRON_EXTRACTFILES' ) 
	&& O_CRON_EXTRACTFILES == TRUE
	){
        echo "<br />" . date( 'Y-m-d H:i:s' );
        echo "<br />Clean and assing duplicates mediainfo: ";
        echo "<br />";
        mediainfo_clean_duplicates( TRUE );
    }
	
	//Search New Elements WebScrapp
	if( defined( 'O_WEBSCRAP_LIMIT_FREESPACE' ) 
	&& ( $freespace = disk_free_space( PPATH_DOWNLOADS ) ) != FALSE
	&& $freespace  < ( O_WEBSCRAP_LIMIT_FREESPACE * 1024 * 1024 * 1024 )
	){
        echo "<br />Search New Downloads Canceled, free space: " . formatSizeUnits( $freespace );
	}elseif( is_array( O_WEBSCRAP_CRON ) 
	&& count( O_WEBSCRAP_CRON ) > 0
	&& isset( $G_WEBSCRAPPER )
	&& is_array( $G_WEBSCRAPPER )
	){
        $num = 1;
        foreach( O_WEBSCRAP_CRON AS $ws_cron ){
            echo "<br />" . date( 'Y-m-d H:i:s' ) . ' ' . $num . '/' . count( O_WEBSCRAP_CRON );
            if( array_key_exists( $ws_cron, $G_WEBSCRAPPER ) ){
                echo "<br />Search New Download: " . $ws_cron;
                echo "<br />";
                webscrap_search_updated( $ws_cron, TRUE );
            }else{
                echo "<br />WebScrapper Not Found: " . $ws_cron;
                echo "<br />";
            }
            $num++;
        }
    }
	
	//LIVE TV CHECK
	
    $cronid = 'cron_7d';
    if( defined( 'O_CRON_VLONG_TIME' ) ){
        $crontime = O_CRON_VLONG_TIME;
    }else{
        //def 7 days
        $crontime = ( 60 * 24 * 7 );
    }
    if( ( $data = sqlite_log_check_cron( $cronid, $crontime ) ) != FALSE 
    && count( $data ) > 0
    ){
        
    }elseif( sqlite_log_insert( $cronid, 'Cron ' . $crontime .'mins launched: ' . date( 'Y-m-d H:s:i' ) ) !== FALSE  ){
        echo "<br />LIVETV Clean: " . $cronid;
        echo "<br />";
        //LiveTV Clean
        if( ( $da = sqlite_medialive_getdata( FALSE, 10000 ) ) 
        && is_array( $da )
        && array_key_exists( 0, $da )
        ){
            $URLs_OK = 0;
            $URLs_DEL = 0;
            $URLs_DEL_E = 0;
            foreach( $da AS $d ){
                if( ( $ldata = ffprobe_get_data( $d[ 'url' ] ) ) != FALSE 
                && is_array( $ldata )
                && array_key_exists( 'width', $ldata )
                && $ldata[ 'width' ] > 0
                ){
                    //echo get_msg( 'DEF_EXIST' );
                    $URLs_OK++;
                }else{
                    if( sqlite_medialive_delete( $G_IDMEDIALIVE )
                    ){
                        //echo get_msg( 'DEF_DELETED' );
                        $URLs_DEL++;
                    }else{
                        //echo get_msg( 'DEF_DELETED_ERROR' );
                        $URLs_DEL_E++;
                    }
                }
            }
            echo get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ' OKs: ' . $URLs_OK . '/Del:' . $URLs_DEL . '/DelError:' . $URLs_DEL_E;
        }else{
            echo get_msg( 'WEBSCRAP_ADDKO' );
        }
        
        //LiveTVUrls UPDATE
        echo "<br />LIVETVURLs Update: " . $cronid;
        echo "<br />";
        if( ( $da = sqlite_medialiveurls_getdata( FALSE, 10000 ) ) 
        && is_array( $da )
        && array_key_exists( 0, $da )
        ){
            $URLs_OK = 0;
            $URLs_DEL = 0;
            $URLs_DEL_E = 0;
            foreach( $da AS $d ){
                if( ( $ldata = @file_get_contents( $d[ 'url' ] ) ) != FALSE 
                && strlen( $ldata ) > 0
                ){
                    //echo get_msg( 'DEF_EXIST' );
                    //ADD URLS
                    $G_LISTLINKS = $ldata;
                    if( $G_LISTLINKS
                    && strlen( $G_LISTLINKS ) > 0
                    ){
                        $G_LISTLINKS = trim( $G_LISTLINKS );
                        $G_LISTLINKS = explode( PHP_EOL, $G_LISTLINKS );
                        $G_LISTLINKS = array_filter( $G_LISTLINKS, 'trim' );
                        $ltitle = '';
                        $URLs = 0;
                        $URLs_ERROR = 0;
                        $URLs_DUPLY = 0;
                        $LINES = count( $G_LISTLINKS );
                        foreach( $G_LISTLINKS AS $line ){
                            if( filter_var( $line, FILTER_VALIDATE_URL )
                            && sqlite_medialive_checkexist( $line ) != FALSE
                            ){
                                $URLs_DUPLY++;
                            }elseif( filter_var( $line, FILTER_VALIDATE_URL )
                            && sqlite_medialive_checkexist( $line ) == FALSE
                            && ( $ldata = ffprobe_get_data( $line ) ) != FALSE 
                            && is_array( $ldata )
                            && array_key_exists( 'width', $ldata )
                            && $ldata[ 'width' ] > 0
                            ){
                                //+1 url
                                if( strlen( $ltitle ) == 0 ){
                                    $ltitle = 'NO-TITLE';
                                }
                                if( sqlite_medialive_insert( 0, $ltitle, $line, '' ) ){
                                    //echo get_msg( 'DEF_ELEMENTUPDATED' );
                                }else{
                                    //echo get_msg( 'WEBSCRAP_ADDKO' );
                                    $URLs_ERROR++;
                                }
                                $URLs++;
                            }elseif( startsWith( $line, '#EXTINF' ) ){
                                //extract title
                                $tt = explode( ',', $line );
                                if( array_key_exists( 1, $tt ) ){
                                    $ltitle = $tt[ 1 ];
                                }else{
                                    $ltitle = $line;
                                    $ltitle = str_ireplace( '#EXTINF:', '', $ltitle );
                                    $ltitle = trim( $ltitle );
                                }
                            }elseif( filter_var( $line, FILTER_VALIDATE_URL ) ){
                                //no data valid
                                $URLs_ERROR++;
                            }else{
                                //no data valid
                            }
                        }
                        echo '<br />' . get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ' URL: ' . $d[ 'url' ] . ' STATUS ' . $URLs . '/ERRORs:' . $URLs_ERROR . '/DUPLYs:' . $URLs_DUPLY . '/LINES:' . $LINES;
                    }else{
                        echo '<br />' . get_msg( 'WEBSCRAP_ADDKO' ) . ' URL: ' . $d[ 'url' ];
                    }
                    //END ADD
                    $URLs_OK++;
                }else{
                    $URLs_DEL++;
                    echo '<br />' . get_msg( 'WEBSCRAP_ADDKO' ) . ' URL: ' . $d[ 'url' ];
                }
            }
            //echo '<br />' . get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ' OKs: ' . $URLs_OK . '/Del:' . $URLs_DEL . '/DelError:' . $URLs_DEL_E;
        }else{
            echo '<br />' . get_msg( 'WEBSCRAP_ADDKO' );
        }
    }
        
	
	//END
	echo "<br />--END--" . date( 'Y-m-d H:i:s' );
?>
