<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	$G_LANGUAGE = array(
        'DEF_NOTSPECIFIED' => 'Not specified',
        'DEF_FILENOTEXIST' => 'File not exist ',
        'DEF_EXIST' => 'Element exist.',
        'DEF_NOTEXIST' => 'Element not exist.',
        'DEF_DELETED' => 'Element Deleted.',
        'DEF_DELETED_ERROR' => 'Error deleting element.',
        'DEF_COPYOK' => 'Element copy: ',
        'DEF_COPYKO' => 'Error, cant copy element: ',
        'DEF_EMPTYLIST' => 'Empty List',
        'DEF_ELEMENTUPDATED' => 'Element updated ',
        'DEF_LOADING' => 'Loading... ',
	
        'LOGIN_ERRUSERPASS' => 'Error user/pass.',
        'LOGIN_NEEDED' => 'Login Needed.',
        
        'LOGIN_FORM_TITLE' => 'Access',
        'LOGIN_FORM_USER' => 'User',
        'LOGIN_FORM_PASS' => 'Password',
        'LOGIN_FORM_BUTTON' => 'Login',
        
        'MENU_HOME_MS' => 'Menu',
        'MENU_HOME' => 'Home',
        'MENU_LOGOUT' => 'Logout',
        'MENU_SEARCH' => 'Search',
        'MENU_LOG' => 'Logs',
        'MENU_IDENTIFY' => 'Identify',
        'MENU_IDENTIFY_AUTO' => 'Identify Auto',
        'MENU_LOGMEDIA' => 'LogMedia',
        'MENU_LOGMEDIAINFO' => 'LogMediaInfo',
        'MENU_USERS' => 'Users',
        'MENU_DELETE' => 'Delete',
        'MENU_DELETE_FILE' => 'Delete File',
        'MENU_LOGPLAYED' => 'LogPlayed',
        'MENU_MEDIA_DELETE_ASSING' => 'Clean Media',
        'MENU_SETTITLE' => 'Set Title',
        'MENU_SETTITLE_FORCE' => 'Set Title Force Existent',
        'MENU_SEASON' => 'Season',
        'MENU_EPISODE' => 'Episode',
        'MENU_ELEMENT' => 'Element',
        'MENU_TITLE' => 'Title',
        'MENU_SCRAPPER' => 'Scrapper',
        'MENU_SCRAPPERWEB' => 'ScrapperWeb',
        'MENU_TYPE' => 'Type',
        'MENU_ACTION' => 'Action',
        'MENU_IMPORT' => 'Import',
        'MENU_FOLDER' => 'Folder',
        'MENU_CRON' => 'Cron',
        'MENU_QUANTITY' => 'Quantity',
        'MENU_IMDB' => 'IMDBid',
        'MENU_IP' => 'IP',
        'MENU_DESCRIPTION' => 'Description',
        'MENU_URL' => 'URL',
        'MENU_REFERER' => 'Referer',
        'MENU_DATE' => 'Date',
        'MENU_UPDATE' => 'Update',
        'MENU_EDIT' => 'Edit',
        'MENU_CONFIG' => 'Config',
        'MENU_GETEPISODES' => 'Get Episode List',
        'MENU_JOINMEDIA' => 'Join Media',
        'MENU_YEAR' => 'Year',
        'MENU_GENRE' => 'Genres',
        'MENU_RATING' => 'Rating',
        'MENU_ORDERBY' => 'Order by',
        'MENU_DELETE_IMGS' => 'Delete Images',
        'MENU_IMGS_SEARCH' => 'Search Images',
        'MENU_MEDIAINFO_NEW' => 'New MediaInfo',
        'MENU_HDDCLEAN' => 'HDD Clean',
        
        'MEDIA_TYPE_SERIE' => 'Series',
        'MEDIA_TYPE_MOVIES' => 'Movies',
        
        'IDENT_NOTDETECTED' => 'Empty Search, try diferent Title',
        'IDENT_DETECTED' => 'Detected Title: ',
        'IDENT_DETECTEDOK' => 'File Assigned To Tile: ',
        'IDENT_DETECTEDKO' => 'Error, cant assing file to Title: ',
        'IDENT_FILETODETECTED' => 'File to Detect: ',
        
        'MENU_LOGMEDIA' => 'LOGMEDIA',
        'MENU_LOGMEDIAINFO' => 'LOGMINFO',
        
        'LIST_TITLE_CONTINUE' => 'Continue',
        'LIST_TITLE_LAST' => 'Last Added',
        'LIST_SEARCH_RESULT' => 'Result:',
        'LIST_TITLE_PREMIERE' => 'Premiere',
        'LIST_TITLE_RECOMENDED' => 'Recomended',
        'LIST_TITLE_NEXTPAGE' => 'Next Page',
        'LIST_TITLE_PREVPAGE' => 'Prev Page',
        'LIST_TITLE_PAGE' => 'Page',
        
        'INFO_PLAY' => 'Play',
        'INFO_PLAY_LATER' => 'Play Later',
        'INFO_PLAY_SAFE' => 'Play SafeMode',
        'INFO_NEXT' => 'Next',
        'INFO_DOWNLOAD' => 'Download',
        'INFO_CHAPTERLIST' => 'Chapter List',
        'INFO_ACTORS' => 'Characters',
        'INFO_RELATED' => 'Related',
        'INFO_TIMEEND' => 'End: ',
        'INFO_FILELIST' => 'Files',
        
        'WEBSCRAP_SEARCH_ERROR' => 'Error, cant access to url: ',
        'WEBSCRAP_NOTHING' => 'Scrappers not defined.',
        'WEBSCRAP_FILEDOWNLOADED' => 'File Downloaded: ',
        'WEBSCRAP_FILEDOWNLOADED_ERROR' => 'Error In Download: ',
        'WEBSCRAP_CHECKSIZE_OK' => 'Size OK: ',
        'WEBSCRAP_CHECKSIZE_KO' => 'Size ERROR: ',
        'WEBSCRAP_ADD_URL' => 'Adding URL: ',
        'WEBSCRAP_ADDOK' => 'Element Added: ',
        'WEBSCRAP_ADDKO' => 'Element Error: ',
        'WEBSCRAP_PASS_INVALID' => 'Pass invalid: ',
        'WEBSCRAP_PASS_NEW_VALID' => 'New Valid Pass: ',
        'WEBSCRAP_PASTELINKS' => 'Send Links',
        
        'CONFIG_FILEOK' => 'Valid File Syntax: replacing file.',
        'CONFIG_FILEKO' => 'Invalid File Syntax: ',
        'CONFIG_REPLACE_FILEOK' => 'Config replaced.',
        'CONFIG_REPLACE_FILEKO' => 'Error replacing config.',
        'CONFIG_RECOVER_FILEOK' => 'Recover backup config completed.',
        'CONFIG_NOFILE' => 'System Broken NO CONFIG FILE.',
        'CONFIG_VALID' => 'Config File Valid',
        'CONFIG_NOTWRITABLE' => 'Config file not writable.',
        
        'JOIN_REPLACETHIS' => 'Replace this: ',
        'JOIN_WHITTHIS' => 'Whith this: ',
        'JOIN_BUTTONREPLACE' => 'Replace',
        
        'DOWNLOADS_USER_TITLE' => 'Search for Downloads',
        
        'LIVETV_TITLE' => 'LiveTV',
        
        'LIVETVURLS_TITLE' => 'LiveTV URLs',
	);
	
?>
