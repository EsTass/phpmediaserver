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
    require( PPATH_CORE . DS . 'functions.dlna.php' );
	
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
	
	//SCAN DOWNLOADED EXTRAC FILES
	if( defined( 'O_CRON_EXTRACTFILES' ) 
	&& O_CRON_EXTRACTFILES == TRUE
	){
        echo "<br />" . date( 'Y-m-d H:i:s' );
        echo "<br />Scan And Extract Media Downloads: ";
        echo "<br />";
        media_extract_files( 20, TRUE, TRUE );
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
	
	//AUTOCLEAN SPACE ON LOW (BIGFILES)
	
	if( defined( 'O_WEBSCRAP_LIMIT_FREESPACE' ) 
	&& defined( 'O_WEBSCRAP_LIMIT_FREESPACE_AUTOCLEAN' )
	&& O_WEBSCRAP_LIMIT_FREESPACE_AUTOCLEAN != FALSE
	&& ( $freespace = disk_free_space( PPATH_DOWNLOADS ) ) != FALSE
	&& $freespace  < ( O_WEBSCRAP_LIMIT_FREESPACE * 1024 * 1024 * 1024 )
	){
        //10 pass: from O_WEBSCRAP_LIMIT_FREESPACE_AUTOCLEAN to -1Gb (10*0.1)
        //10 files deleted on each pass
        cleanLowDiskSpace( FALSE, 10, 10 );
	}
	
	//AUTOCLEAN SPACE ON LOW (OLDFILES)
	
	if( defined( 'O_WEBSCRAP_LIMIT_FREESPACE' ) 
	&& defined( 'O_WEBSCRAP_LIMIT_FREESPACE_AUTOCLEAN_OLD' )
	&& O_WEBSCRAP_LIMIT_FREESPACE_AUTOCLEAN_OLD != FALSE
	&& ( $freespace = disk_free_space( PPATH_DOWNLOADS ) ) != FALSE
	&& $freespace  < ( O_WEBSCRAP_LIMIT_FREESPACE * 1024 * 1024 * 1024 )
	){
        //delete files while free space < O_WEBSCRAP_LIMIT_FREESPACE, with max of 50 files
        cleanLowDiskSpaceOldFiles( FALSE, 50 );
	}
	
	//CLEAN DOWNLOADS FOLDERS size < Mb
	if( defined( 'O_CRON_FOLDERS_CLEAN_LOWSIZE' ) 
	&& O_CRON_FOLDERS_CLEAN_LOWSIZE != FALSE
	){
        echo "<br />" . date( 'Y-m-d H:i:s' );
        echo "<br />Clean downloads folders lower: " . formatSizeUnits( ( O_CRON_FOLDERS_CLEAN_LOWSIZE * 1024 * 1024 ) );
        echo "<br />";
        cleanDownloadsFolders( TRUE, 1 );//1 day
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
        cronLiveTV( $cronid );
    }
    
	//END
	echo "<br />--END--" . date( 'Y-m-d H:i:s' );
?>
