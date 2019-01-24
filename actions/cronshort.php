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
	
	//CLEAN PLAYING MEDIA
	echo "<br />" . date( 'Y-m-d H:i:s' );
	echo "<br />Clean Playing Media Folders: " . sqlite_playing_clean();
	echo "<br />";
	
	//CLEAN TEMP FOLDER
	echo "<br />" . date( 'Y-m-d H:i:s' );
	echo "<br />Clean Temp Folders: " . media_clean_temp_folder( 20 );
	echo "<br />";
	
	//SCAN DOWNLOAD (slow on big lists)
	echo "<br />" . date( 'Y-m-d H:i:s' );
	echo "<br />Scan New Downloads: ";
	echo "<br />";
	media_scan_downloads( 100, TRUE, TRUE );
	
	//CLEAN DOWNLOAD MEDIA NOT EXIST
	echo "<br />" . date( 'Y-m-d H:i:s' );
	echo "<br />Clean Inexistents Downloads: ";
	echo "<br />";
	media_clean_downloads( 500, TRUE );
	
	//CLEAN DOWNLOAD MEDIA DUPLY
	echo "<br />" . date( 'Y-m-d H:i:s' );
	echo "<br />Clean Duply Downloads: ";
	echo "<br />";
	media_clean_duplicated( 100, TRUE );
	
	//CRON IMPORT FILEBOT FOLDER
	if( O_CRON_SHORT_IMPORT_FOLDER != FALSE 
	&& file_exists( O_CRON_SHORT_IMPORT_FOLDER )
	){
        echo "<br />" . date( 'Y-m-d H:i:s' );
        echo "<br />Import Folder (Filebot Format): ";
        echo "<br />";
        $G_DATA[ 'quantity' ] = 100;
        $G_DATA[ 'folder' ] = O_CRON_SHORT_IMPORT_FOLDER;
        require PPATH_ACTIONS . DS . 'importfoldera.php';
	}
	
	//GET SEARCHS DATA AND DOWNLOAD MEDIA
	if( defined( 'O_CRON_WEBSCRAP_SEARCH' ) 
	&& O_CRON_WEBSCRAP_SEARCH != FALSE
	){
        echo "<br />" . date( 'Y-m-d H:i:s' );
        echo "<br />Get Searchs data and download files: ";
        echo "<br />";
        cron_searchs_downloads( O_CRON_SHORT_TIME );
    }
	
	//AUTODETECT MEDIA (ALWAYS LAST, TOO MUCH TIME (1min*file aprox of wait))
	echo "<br />" . date( 'Y-m-d H:i:s' );
	echo "<br />Scrap Downloads: ";
	echo "<br />";
	media_scrap_downloads( 50, TRUE, TRUE );
	
	//Send Broadcast DLNA
	if( defined( 'DLNA_ACTIVE' ) 
	&& DLNA_ACTIVE
	){
        echo "<br />";
        echo "<br />" . date( 'Y-m-d H:i:s' );
        echo "<br />DLNA Broadcast: ";
        echo "<br />";
        dlna_sddpSend();
    }
	
	//END
	echo "<br /><br />--END--" . date( 'Y-m-d H:i:s' );
?>
