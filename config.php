<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
    //CONFIG
    
	define( 'USER_IP', getIP() );
	define( 'APPNAME', 'PMS' );
	define( 'APPVERSION', '0.6' );
	define( 'AUTHOR', 'tass' );
	
	//HTTP folders
	define( 'HPATH_MEDIA', getURLBase() . '/imgs' );
	
	//Force HTTPS connection
	define( 'O_FORCE_HTTPS', TRUE );
	
	//List Quantity
	define( 'O_LIST_QUANTITY', 32 );
	
	//Mini List Quantity
	define( 'O_LIST_MINI_QUANTITY', 16 );
	
	//Maxi List Quantity
	define( 'O_LIST_BIG_QUANTITY', 128 );
	
	//action default
	define( 'O_ACTIONDEFAULT', 'list' );
	
	//LANGUAJE
	define( 'O_LANG', 'es' );
	
	//LANGUAJE AUDIO TRACK select channel on play
	define( 'O_LANG_AUDIO_TRACK', array( 'eng', 'en' ) );
	
	//Countrys allowed (http://www.geoplugin.net)
	define( 'O_COUNTRYALLOWED', array( '' ) );
	
	//Max size of Media Images (reduction disabled = 0): 250k
	define( 'O_MAX_IMG_FILES', 250 * 1024 );
	
	//Anon links
	define( 'O_ANON_LINK', 'https://anonym.to/?' );
	
	//Menu Genres: search title in lang => generic genre in eng (for generic scrapp data)
	define( 'O_MENU_GENRES', 
        array( 
            'Comedy' => 'Comedy',
            'Action' => 'Action',
            'ScyFy' => 'Fiction',
            'Mistery' => 'Mistery',
            'Horror' => 'Horror',
            'Family' => 'Family',
            'History' => 'Hist',
            'Documental' => 'Docu',
            'TV' => 'TV',
        )
    );
	
	//Timeblocks to video cache in seconds
	define( 'O_VIDEO_TIMEBLOCK', 10 );
	
	//MIN BITRATE SD
	define( 'O_VIDEO_SD_MINBRATE', '500k' );
	//MAX BITRATE SD
	define( 'O_VIDEO_SD_MAXBRATE', '1M' );
	//HEIGHT SD
	define( 'O_VIDEO_SD_HEIGHT', '480' );
	
	//MIN BITRATE HD
	define( 'O_VIDEO_HD_MINBRATE', '1M' );
	//MAX BITRATE HD
	define( 'O_VIDEO_HD_MAXBRATE', '2M' );
	//HEIGHT HD
	define( 'O_VIDEO_HD_HEIGHT', '720' );
	
	//VIDEO EXTRA VOLUME
	define( 'O_VIDEO_EXTRA_VOLUME', '2.0' );
	
	//Search Media Types
	$G_STYPE = array( 'movies', 'series' );
	
	//FILE SCRAPPERS
	
	//function.[scrapper].php to include
	define( 'O_SCRAPPERS_INCLUDES', array( 'filebot', 'pymi', 'omdb', 'thetvdb' ) );
	//Scrapper List ( title => array( function_search, function_add ) ) filled in each scrapper
	$G_SCRAPPERS = array();
	//SCRAPPERS API KEY ( title => function ) filled in each scrapper
	$G_SCRAPPERS_KEY = array( 
        'omdb' => 'YOURKEY',
        'thetvdb' => '120F8A1A0E6322F3',
    );
	//SCRAPPER FOR CRON FILE SCRAPPER filebot recomended
	define( 'O_SCRAP_CRON', 'filebot' );
	
	//EXTERNAL COMMANDS
	
	//PATH ffmpeg
	define( 'O_FFMPEG', 'ffmpeg' );
	//PATH ffprobe
	define( 'O_FFPROBE', 'ffprobe' );
	//PATH filebot
	define( 'O_FILEBOT', 'export HOME="' . PPATH_CACHE . '" && filebot' );
	//PATH PHP (run cron jobs)
	define( 'O_PHP', 'php' );
	//WGET (run cron jobs)
	define( 'O_WGET', 'wget' );
	//pymediaident
	define( 'O_PYMEDIAIDENT', 'pymediaident.py' );
	
	//FOLDERS
	
	//Downloads Folder
	define( 'PPATH_DOWNLOADS', PPATH_CACHE . DS . 'downloads' );
	//Exclude folder in downloads
	define( 'O_DOWNLOADS_FOLDERS_EXCLUDE', array(
        '/path/to/exclude',
        )
    );
    //Exclude Files to scan by Extension
	define( 'O_DOWNLOADS_FILES_EXCLUDE', 
        array( 
            '.part', 
            '.part.met',
        )
    );
	
	//WEBSCRAPPING
	
	//WebScrapp Download Torrents
	define( 'PPATH_WEBSCRAP_DOWNLOAD', PPATH_DOWNLOADS . DS . 'adds' );
	//CMD to Torrent Files (%FILE%=Torrent File)
	define( 'PPATH_WEBSCRAP_TORRENT_CMD', '' );
	//CMD to Amule Links Files (%ELINK%=Amule Link)
	define( 'PPATH_WEBSCRAP_EMULE_CMD', 'ed2k "%ELINK%"' );
	//CMD to Magnets Links Files (%MAGNET%=Magnet Link)
	define( 'PPATH_WEBSCRAP_MAGNETS_CMD', 'transmissioncli "%MAGNET%"' );
	
	//WEB SCRAPPERS CONFIG
	if( file_exists( PPATH_BASE . DS . 'config.ws.php' ) ){
        require( PPATH_BASE . DS . 'config.ws.php' );
	}
	//WEBSCRAPPERS ON CRON
	define( 'O_WEBSCRAP_CRON', array() );
	//WEBSCRAPPERS stop downloads if <free space on GB
	define( 'O_WEBSCRAP_LIMIT_FREESPACE', 500 );
	//Web Scrappers Show Debug Log
	define( 'PPATH_WEBSCRAP_DEBUG', FALSE );
	
	//CRON Enable
	
	define( 'O_CRON', FALSE );
	//CRON admin user (needed active)
	define( 'O_CRON_ADMINUSER', 'admin' );
	//CRON short cron minutes
	define( 'O_CRON_SHORT_TIME', ( 90 ) );
	//CRON short: import folder filebot format (*.nfo, thisfolder/title/video, thisfolder/titleserie/chapters)
	define( 'O_CRON_SHORT_IMPORT_FOLDER', FALSE );
	//CRON long cron minutes
	define( 'O_CRON_LONG_TIME', ( 60 * 6 ) );
	//CRON job: web petition to login shortcron + 10 secs
	define( 'O_CRON_JOB', 'sleep ' . ( ( O_CRON_SHORT_TIME * 60 ) + 10 ) . ' && wget --no-check-certificate -O - https://127.0.0.1/' );
	//CRON Logs Files
	define( 'PPATH_CRON_FILE', PPATH_CACHE . DS . 'cron' );
	define( 'PPATH_CRON_HOUR_FILE', PPATH_CACHE . DS . 'cronhour' );
	
	//CRON clean duplicated idmediainfo files min days (0 disable)
	//if film/chapters have >1 file delete low quality duplicates if have more than days modified (seeding safe and be safe detected)
	define( 'O_CRON_CLEAN_DUPLICATES_MEDIAINFO', 14 );
	//CRON clean not identified files min days (0 disable)
	//if file not identified for more than days, delete (seeding safe and be safe detected)
	define( 'O_CRON_CLEAN_NOTIDENT_MEDIA', 14 );
	//CRON extract compressed files in cron
	define( 'O_CRON_EXTRACTFILES', TRUE );
	//CRON extracted compressed files deleted after X days, 0 disabled (seeding safe)
	define( 'O_CRON_EXTRACTFILES_CLEAN', 14 );
	
	//TIMEZONE
	@date_default_timezone_set( 'Europe/Madrid' );
	@setlocale( LC_ALL, 'es_ES.utf8' );
	//setlocale( LC_ALL, 'en_US.utf8' );
	
	//CLEAN DOWNLOADED FILENAMES
	$G_CLEAN_FILENAME = array(
        //Languaje
        'English',
        'Spanish',
        'Latino',
        //Video Format
        '4K', 
        'FullBluRay', 
        'BDRemux',
        'bdScreener',
        'dvdScreener',
        'Screener',
        'TS-Screeener',
        'TSScreeener',
        'TV-Screeener',
        'TVScreeener',
        'La-Screeener',
        'LaScreeener',
        'Screeener',
        'TS-Screener',
        'TSScreener',
        'TV-Screener',
        'TVScreener',
        'La-Screener',
        'LaScreener',
        'Screener',
        'BluRay', 
        'BlueRay', 
        'MicroHD', 
        'DVD',
        'HD',
        'DVD',
        'DVDRip',
        'HDRip',
        'BRRip',
        '1080',
        '720',
        '1080p',
        '720p',
        'Subs',
        'FullBluRay', 
        'BDRemux',
        'BluRay', 
        'BlueRay', 
        'MicroHD', 
        'DVD',
        'HD',
        'DVD',
        '1080',
        '720',
        '1080p',
        '720p',
        'Br-Line',
        'XVID',
        'AC3',
        '.mp4a',
        '480p',
        '2ch',
        '3ch',
        '4ch',
        '5ch',
        '6ch',
        '7ch',
        ' Rip',
        '.avi',
        '.mpg',
        '.mpeg',
        '.mkv',
        '.mp4',
        //Web Words
        '(TV Serie)',
        '(Serie de TV)',
        'online',
        'torrent',
        'Movies',
        ' Rip',
        'p2p',
    );
    
?>
