<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//ONLINE USER
	
	function check_user(){
        $result = FALSE;
        if( defined( 'USERNAME' ) ){
            $result = TRUE;
        }
        
        return $result;
	}
	
	function check_user_admin(){
        $result = FALSE;
        if( defined( 'USERNAMEADMIN' ) ){
            $result = TRUE;
        }
        
        return $result;
	}
	
	function check_mod_admin() {
		//admin
		if( check_user()
		&& check_user_admin()
		){
            
        }else{
			@header( "HTTP/1.1 401 Unauthorized" );
			echo "HTTP/1.1 401 Unauthorized";
			exit;
		}
	}
	
	//LOGINS
	
	function checkLoginsAttempts( $maxretryshour = 5 ) {
		$result = TRUE;
		
		if( ( $data = sqlite_log_checkloginstrys() ) != FALSE 
		&& count( $data ) > $maxretryshour
		){
			$result = FALSE;
		}
		
		return $result;
	}
	
	function checkLoginsBans( $maxretrysday = 20 ) {
		$result = TRUE;
		
		if( ( $data = sqlite_log_checkloginsbans() ) != FALSE 
		&& count( $data ) > $maxretrysday
		){
			$result = FALSE;
		}
		
		return $result;
	}
	
	
	//USER IP
	
	function getIP()
	{
		if( array_key_exists( 'HTTP_CLIENT_IP', $_SERVER )
		&& !empty( $_SERVER[ 'HTTP_CLIENT_IP' ] ) 
		//TODO ip gateway
		&& array_key_exists( 'HTTP_CLIENT_IP', $_SERVER )
		&& $_SERVER[ 'HTTP_CLIENT_IP' ] != '192.168.1.1' 
		){ //check ip from share internet
			$ip = $_SERVER[ 'HTTP_CLIENT_IP' ];
		}elseif( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER )
		&& !empty( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] )
		//TODO ip gateway
		&& array_key_exists( 'HTTP_CLIENT_IP', $_SERVER )
		&& $_SERVER[ 'HTTP_CLIENT_IP' ] != '192.168.1.1' 
		){ //to check ip is pass from proxy
			$ip = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
		}elseif( array_key_exists( 'REMOTE_ADDR', $_SERVER ) ){
			$ip = $_SERVER[ 'REMOTE_ADDR' ];
		}else{
			$ip = '127.0.0.1';
		}
		return $ip;
	}
	
	//USER IP COUNTRY
	
	//FALSE, not valid
	function check_ip_country( $IP ){
        $result = FALSE;
        $COUNTRY = O_COUNTRYALLOWED;
        $VALID_IPs = array( 
            '192.168.',
            '10.0.',
        );
        
        //Add external msg server ips if needed
        msgExternalIPs( $VALID_IPs );
        
        $ipcountry = '';
        $ipcountry2 = '';
        
        if( inString( $IP, $VALID_IPs ) ){
            $result = TRUE;
        }elseif( checkWhitedIP( $IP ) ){
            $result = TRUE;
        }elseif( ( $ipcountry = ip_info2( $IP, 'country' ) ) != FALSE 
        && in_array( $ipcountry, $COUNTRY )
        ){
            addWhitedIP( $IP );
            $result = TRUE;
        }elseif( ( $ipcountry2 = ip_info( $IP, 'country' ) ) != FALSE 
        && in_array( $ipcountry2, $COUNTRY )
        ){
            addWhitedIP( $IP );
            $result = TRUE;
        }else{
            $ipcountry .= $ipcountry2;
            if( addBannedIP( $IP, ' COUNTRY: ' . $ipcountry, ( 24 * 365 ) ) ){
            
            }else{
                die( 'error: ' . $IP );
            }
        }
        
        return $result;
	}
	
	function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
        $output = NULL;
        if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
        $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
        $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
        $continents = array(
            "AF" => "Africa",
            "AN" => "Antarctica",
            "AS" => "Asia",
            "EU" => "Europe",
            "OC" => "Australia (Oceania)",
            "NA" => "North America",
            "SA" => "South America"
        );
        if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
            $ipdat = @json_decode(file_get_contents_timed("http://www.geoplugin.net/json.gp?ip=" . $ip));
            if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
                switch ($purpose) {
                    case "location":
                        $output = array(
                            "city"           => @$ipdat->geoplugin_city,
                            "state"          => @$ipdat->geoplugin_regionName,
                            "country"        => @$ipdat->geoplugin_countryName,
                            "country_code"   => @$ipdat->geoplugin_countryCode,
                            "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                            "continent_code" => @$ipdat->geoplugin_continentCode
                        );
                        break;
                    case "address":
                        $address = array($ipdat->geoplugin_countryName);
                        if (@strlen($ipdat->geoplugin_regionName) >= 1)
                            $address[] = $ipdat->geoplugin_regionName;
                        if (@strlen($ipdat->geoplugin_city) >= 1)
                            $address[] = $ipdat->geoplugin_city;
                        $output = implode(", ", array_reverse($address));
                        break;
                    case "city":
                        $output = @$ipdat->geoplugin_city;
                        break;
                    case "state":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "region":
                        $output = @$ipdat->geoplugin_regionName;
                        break;
                    case "country":
                        $output = @$ipdat->geoplugin_countryName;
                        break;
                    case "countrycode":
                        $output = @$ipdat->geoplugin_countryCode;
                        break;
                }
            }
        }
        
        return $output;
    }
    
	function ip_info2($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
        $output = NULL;
        $apiKey = O_IPGEOLOCATIONIO_APIKEY;
        $lang = "en";
        //$fields = "*";
        $fields = "country_name,country_code2";
        $excludes = "";
        
        if( !is_string( $apiKey ) 
        || strlen( $apiKey ) <= 0
        ){
            return $output;
        }
        
        if( filter_var($ip, FILTER_VALIDATE_IP) === FALSE ) {
            $ip = $_SERVER["REMOTE_ADDR"];
            if ($deep_detect) {
                if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
        }
        
        $url = "https://api.ipgeolocation.io/ipgeo?apiKey=".$apiKey."&ip=".$ip."&lang=".$lang."&fields=".$fields."&excludes=".$excludes;
        $cURL = curl_init();

        curl_setopt($cURL, CURLOPT_URL, $url);
        curl_setopt($cURL, CURLOPT_HTTPGET, true);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        //return curl_exec($cURL);
        $location = curl_exec($cURL);
        $decodedLocation = json_decode($location, true);
        
        if( ( $location = curl_exec($cURL) ) != FALSE 
        && ( $decodedLocation = json_decode($location, true) ) != FALSE
        ){
            if( array_key_exists( 'country_name', $decodedLocation ) ){
                $output = $decodedLocation[ 'country_name' ];
            }elseif( array_key_exists( 'country_name', $decodedLocation ) ){
                $output = $decodedLocation[ 'country_code2' ];
            }
        }
        
        return $output;
    }
    
	//GET POST DATA
	
	function init_get_post(){
        $result = array();
        
        foreach( $_GET AS $k => $v ){
            $result[ $k ] = $v;
        }
        foreach( $_POST AS $k => $v ){
            $result[ $k ] = $v;
        }
        
        if( !array_key_exists( 'action', $result ) ){
            $result[ 'action' ] = O_ACTIONDEFAULT;
        }
        if( !array_key_exists( 'search', $result ) ){
            $result[ 'search' ] = '';
        }
        
        return $result;
	}
	
	//URL
	
	function getURL() {
        if( !isset( $_SERVER ) 
        || !is_array( $_SERVER )
        || !array_key_exists( 'REQUEST_URI', $_SERVER )
        ){
            $_SERVER[ 'REQUEST_URI' ] = 'https://127.0.0.1/';
            $_SERVER[ 'HTTP_REFERER' ] = '';
            $_SERVER[ 'HTTP_HOST' ] = '127.0.0.1';
            $_SERVER[ 'HTTPS' ] = 'https';
        }
        if( O_FORCE_HTTPS == TRUE ){
            $result = "https://" . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];
        }else{
            $result = (array_key_exists( 'HTTPS', $_SERVER ) ? "https" : "http") . "://" . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];
        }
		return $result;
	}
	
	function getURLBase() {
        if( !isset( $_SERVER ) 
        || !is_array( $_SERVER )
        || !array_key_exists( 'REQUEST_URI', $_SERVER )
        ){
            $_SERVER[ 'REQUEST_URI' ] = 'https://127.0.0.1/';
            $_SERVER[ 'HTTP_REFERER' ] = '';
            $_SERVER[ 'HTTP_HOST' ] = '127.0.0.1';
            $_SERVER[ 'HTTPS' ] = 'https';
        }
        $f = explode('?',$_SERVER['REQUEST_URI']);
        $f = $f[ 0 ];
        if( defined( 'O_FORCE_HTTPS' )
        && O_FORCE_HTTPS == TRUE 
        ){
            $result = "https://" . $_SERVER[ 'HTTP_HOST' ] . $f;
        }else{
            $result = (array_key_exists( 'HTTPS', $_SERVER ) ? "https" : "http") . "://" . $_SERVER[ 'HTTP_HOST' ] . $f;
        }
		return $result;
	}
	
	function getReferer() {
		$result = '';
		
		if( array_key_exists( 'HTTP_REFERER', $_SERVER ) ){
			$result = $_SERVER[ 'HTTP_REFERER' ];
		}
		
		return $result;
	}
	
	function getURLImg( $idmedia = FALSE, $idmediainfo = FALSE, $type = 'poster' ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?r=r&action=imgs';
        
        if( $idmedia !== FALSE ){
            $result .= '&idmedia=' . $idmedia;
        }
        
        if( $idmediainfo !== FALSE ){
            $result .= '&idmediainfo=' . $idmediainfo;
        }
        
        if( array_key_exists( $type, $G_MEDIADATA ) 
        || $type == 'back'
        || $type == 'next'
        || $type == 'iptv'
        || $type == 'livetv'
        ){
            $result .= '&type=' . $type;
        }else{
            $result .= '&type=poster';
        }
        
        return $result;
	}
	
	function getURLImgTmp( $filename ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?r=r&action=imgstmp';
        
        $result .= '&tfolder=' . basename( dirname( $filename ) );
        $result .= '&tfile=' . basename( $filename );
        
        return $result;
	}
	
	function getURLInfo( $idmedia = FALSE, $idmediainfo = FALSE ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?action=mediainfo';
        
        if( $idmedia ){
            $result .= '&idmedia=' . $idmedia;
        }
        
        if( $idmediainfo ){
            $result .= '&idmediainfo=' . $idmediainfo;
        }
        
        return $result;
	}
	
	function getURLNextInfo( $idmedia = FALSE, $idmediainfo = FALSE ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?action=medianext';
        
        if( $idmedia ){
            $result .= '&idmedia=' . $idmedia;
        }
        
        if( $idmediainfo ){
            $result .= '&idmediainfo=' . $idmediainfo;
        }
        
        return $result;
	}
	
	function getURLBackInfo( $idmedia = FALSE, $idmediainfo = FALSE ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?action=mediaback';
        
        if( $idmedia ){
            $result .= '&idmedia=' . $idmedia;
        }
        
        if( $idmediainfo ){
            $result .= '&idmediainfo=' . $idmediainfo;
        }
        
        return $result;
	}
	
	function getURLPlayer( $idmedia = FALSE, $idmediainfo = FALSE ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?action=player';
        
        if( $idmedia ){
            $result .= '&idmedia=' . $idmedia;
        }
        
        if( $idmediainfo ){
            $result .= '&idmediainfo=' . $idmediainfo;
        }
        
        return $result;
	}
	
	function getURLPlayerSafe( $idmedia = FALSE, $idmediainfo = FALSE ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?action=playersafe';
        
        if( $idmedia ){
            $result .= '&idmedia=' . $idmedia;
        }
        
        if( $idmediainfo ){
            $result .= '&idmediainfo=' . $idmediainfo;
        }
        
        return $result;
	}
	
	function getURLPlayerLive( $idmedialive ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?action=playerlive';
        
        if( $idmedialive ){
            $result .= '&idmedialive=' . $idmedialive;
        }
        
        return $result;
	}
	
	function getURLDownload( $idmedia = FALSE, $idmediainfo = FALSE ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?r=r&action=mediadownload';
        
        if( $idmedia ){
            $result .= '&idmedia=' . $idmedia;
        }
        
        if( $idmediainfo ){
            $result .= '&idmediainfo=' . $idmediainfo;
        }
        
        return $result;
	}
	
	function getURLChapterList( $idmedia = FALSE, $idmediainfo = FALSE ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?action=listepisodes';
        
        if( $idmedia ){
            $result .= '&idmedia=' . $idmedia;
        }
        
        if( $idmediainfo ){
            $result .= '&idmediainfo=' . $idmediainfo;
        }
        
        return $result;
	}
	
	function getURLActor( $name ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?r=r&action=imgsactor';
        
        if( strlen( $name ) > 0 ){
            $result .= '&actor=' . urlencode( $name );
        }
        
        return $result;
	}
	
	function getURLImgSearch( $idmediainfo ){
        global $G_MEDIADATA;
        $result = getURLBase() . '?action=mediainfosearchimgs&idmediainfo=' . $idmediainfo;
        
        return $result;
	}
	
	//STRINGS
	
	function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}
    
	function endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}
	
	function inString( $haystack, $needle )
	{
        $result = FALSE;
        
		if( is_string( $needle ) ){
            $needle = array( $needle );
		}
		
		foreach( $needle AS $n ){
            if( stripos( $haystack, $n ) !== FALSE ){
                $result = TRUE;
                break;
            }
		}
		
		return $result;
	}
	
	function secondsToTimeFormat( $seconds, $clean_hours = FALSE ) {
		$result = '00:00:00';
		if( $clean_hours 
		&& $seconds < ( 60 * 60 )
		){
			$result = gmdate( "i:s", $seconds );
		}else{
			$result = gmdate( "H:i:s", $seconds );
		}
		
		return $result;
	}
	
	function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
	}
	
	function getRandomString( $length = 10 ) {
		//$length = 10;
		$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
		$characters .= strtoupper( 'abcdefghijklmnopqrstuvwxyz' );
		$string = '';

		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, strlen($characters) -1 )];
		}

		return $string;
	}
	
	//FOLDER & FILES
	
    function get_dir_size($directory) {
        $size = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }
    
	function delTree( $dir ) {
        if( file_exists( $dir ) ){
            $files = array_diff(scandir($dir), array('.','..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? delTree("$dir/$file") : @unlink("$dir/$file");
            }
        }
		return @rmdir($dir);
	} 
	
	//RUN EXTERNAL COMMAND
	
	function runExtCommand( $cmd ){
		//return system( $cmd );
		return shell_exec( $cmd . ' 2>&1' );
	}
	
	function runExtCommandNoRedirect( $cmd ){
		//return system( $cmd );
		return shell_exec( $cmd . '' );
	}
	
	//DOWNLOAD BIG FILES
	
	function readfile_chunked($filename,$retbytes=true) {
		$chunksize = 1*(1024*1024); // how many bytes per chunk
		$buffer = '';
		$cnt =0;
		// $handle = fopen($filename, 'rb');
		$handle = fopen($filename, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			if ($retbytes) {
				$cnt += strlen($buffer);
			}
		}
			$status = fclose($handle);
		if ($retbytes && $status) {
			return $cnt; // return num. bytes delivered like readfile() does.
		}
		return $status;

	} 
	
	//LANGUAJE
	
	function get_msg( $ident, $prebr = TRUE ){
        $result = 'LANGUAJE ident not found: ' . $ident;
        $file1 = PPATH_LANG . DS . O_LANG . '.php';
        $file2 = PPATH_LANG . DS . 'def.php';
        if( file_exists( $file1 ) ){
            require( $file1 );
            if( array_key_exists( $ident, $G_LANGUAGE ) ){
                $result = $G_LANGUAGE[ $ident ];
            }elseif( file_exists( $file2 ) ){
                require( $file2 );
                if( array_key_exists( $ident, $G_LANGUAGE ) ){
                    $result = $G_LANGUAGE[ $ident ];
                }
            }
        }elseif( file_exists( $file2 ) ){
            require( $file2 );
            if( array_key_exists( $ident, $G_LANGUAGE ) ){
                $result = $G_LANGUAGE[ $ident ];
            }
        }
        if( $prebr ){
            $result = '<br />' . $result;
        }
        return $result;
	}
	
	//MIME
	
	function getFileMimeType($file) {
        $type = '';
        if( file_exists( $file ) ){
            $type = mime_content_type( $file );
        }
		return $type;
	}
	
	function getFileMimeTypeVideo( $file ) {
		$result = FALSE;
		if( file_exists( $file )
		&& ( $type = mime_content_type( $file ) ) != FALSE
		&& stripos( $type, 'video' ) !== FALSE 
		){
            $result = TRUE;
		}
		return $result;
	}
	
	function getFileMimeTypeImg( $file ) {
		$result = FALSE;
		if( file_exists( $file )
		&& ( $type = mime_content_type( $file ) ) != FALSE
		&& ( 
            stripos( $type, 'image' ) !== FALSE 
            || stripos( $type, 'img' ) !== FALSE 
            )
        ){
         
            $result = TRUE;
		}
		return $result;
	}
	
	function getDataMimeType( $data ){
        $finfo = new finfo( FILEINFO_MIME_TYPE );
        return $finfo->buffer( $data );
    }
    
    
	function getDataMimeTypeVideo( $data ) {
		$result = FALSE;
		if( ( $type = getDataMimeType( $data ) ) != FALSE
		&& stripos( $type, 'video' ) !== FALSE 
		){
            $result = TRUE;
		}
		return $result;
	}
	
	function getDataMimeTypeImg( $data ) {
		$result = FALSE;
		if( ( $type = getDataMimeType( $data ) ) != FALSE
		&& ( 
            stripos( $type, 'image' ) !== FALSE 
            || stripos( $type, 'img' ) !== FALSE 
            )
        ){
         
            $result = TRUE;
		}
		return $result;
	}
	
    
	//FILE EXTENSION
	
	function get_file_extension( $file ){
        return pathinfo($file, PATHINFO_EXTENSION);
	}
	
	//JAVASCRIPT
	
	function redirectJS( $url )
	{
		$result = '';
		
		$result .= '<script type="text/javascript">';
		$result .= 'window.location = "' . $url . '"';
		$result .= '</script>';

		return $result;
	}
	
	
	function reloadJS()
	{
		$result = '';
		
		$result .= '<script type="text/javascript">';
		$result .= 'location.reload();';
		$result .= '</script>';

		return $result;
	}
	
	//OBJECTTOARRAY
	
	function object2array($object) { 
		return @json_decode(@json_encode($object),1); 
	} 
	
	//Get biggest word
	
	function get_word_better( $str ){
        $result = '';
        $first = 5;
        
        if( ( $words = str_word_count( $str, 2 ) ) != FALSE ){
            foreach( $words AS $p => $w ){
                if( $p <= $first ){
                    if( strlen( $result ) < strlen( $w ) ){
                        $result = $w;
                    }
                }else{
                    break;
                }
            }
        }
        
        return $result;
	}
	
	//EXTERNAL MSG WARNINGS
	
	function sendMessage( $ident, $msg ){
        $result = FALSE;
        
        if( defined( 'O_SEND_EXT_MSG' ) 
        && ( $actions = O_SEND_EXT_MSG ) !== FALSE
        && $actions != FALSE
        && is_array( $actions )
        && in_array( $ident, $actions )
        ){
            if( defined( 'O_SEND_EXT_TELEGRAM_TOKEN' ) 
            && O_SEND_EXT_TELEGRAM_TOKEN != FALSE
            && defined( 'O_SEND_EXT_TELEGRAM_CHATID' ) 
            && O_SEND_EXT_TELEGRAM_CHATID != FALSE
            ){
                $result = sendMessageTelegram( $ident . ' - ' . $msg );
            }
        }
        
        return $result;
	}
	
	//External IPs for webhooks
	
	function msgExternalIPs( &$validips ){
        //BOT IP TELEGRAM        
        if( defined( 'O_SEND_EXT_MSG' ) 
        && ( $actions = O_SEND_EXT_MSG ) !== FALSE
        ){
            //'149.154.160.0' - '149.154.175.255'
            $telegram_ip = '149.154.';
            $telegram_ip_min = 160;
            $telegram_ip_max = 175;
            $telegram_ip_min2 = 0;
            $telegram_ip_max2 = 255;
            for( $x = $telegram_ip_min; $x <= $telegram_ip_max; $x++ ){
                for( $y = $telegram_ip_min2; $y <= $telegram_ip_max2; $y++ ){
                    $validips[] = $telegram_ip . $x . '.' . $y;
                }
            }
            //'91.108.4.0' - '91.108.7.255'
            $telegram_ip = '91.108.';
            $telegram_ip_min = 4;
            $telegram_ip_max = 7;
            $telegram_ip_min2 = 0;
            $telegram_ip_max2 = 255;
            for( $x = $telegram_ip_min; $x <= $telegram_ip_max; $x++ ){
                for( $y = $telegram_ip_min2; $y <= $telegram_ip_max2; $y++ ){
                    $validips[] = $telegram_ip . $x . '.' . $y;
                }
            }
        }
	}
	
	//TELEGRAM BOT
	
	function sendMessageTelegram( $msg ){
        $result = FALSE;
        
        $TELEGRAM = "https://api.telegram.org/bot" . O_SEND_EXT_TELEGRAM_TOKEN; 
        
        $query = http_build_query( array(
            'chat_id'=> O_SEND_EXT_TELEGRAM_CHATID,
            'text'=> $msg,
            'parse_mode'=> "Markdown", // Optional: Markdown | HTML
        ));
        
        if( ( $response = @file_get_contents( $TELEGRAM . "/sendMessage?" . $query ) ) != FALSE ){
            //file_put_contents( PPATH_CACHE . DS . 'telegram.log', $response, FILE_APPEND );
            $result = TRUE;
        }
        
        return $result;
    }
	
	function removeWebHookTelegram(){
        //https://api.telegram.org/bot[myauthorization-token]/setwebhook?url=[myboturl]
        $result = FALSE;
        
        $TELEGRAM = "https://api.telegram.org/bot" . O_SEND_EXT_TELEGRAM_TOKEN;
        
        $query = http_build_query( array(
            //'url'=> O_SEND_EXT_TELEGRAM_WEBHOOKURL,
        ));
        
        if( ( $response = @file_get_contents( $TELEGRAM . "/deleteWebhook?" . $query ) ) != FALSE ){
            logWebHookTelegram( $response );
            $result = TRUE;
        }
        
        return $result;
	}
	
	function setWebHookTelegram(){
        //https://api.telegram.org/bot[myauthorization-token]/setwebhook?url=[myboturl]
        $result = FALSE;
        
        $TELEGRAM = "https://api.telegram.org/bot" . O_SEND_EXT_TELEGRAM_TOKEN;
        
        $query = http_build_query( array(
            'url'=> O_SEND_EXT_TELEGRAM_WEBHOOKURL,
        ));
        
        if( ( $response = @file_get_contents( $TELEGRAM . "/setwebhook?" . $query ) ) != FALSE ){
            logWebHookTelegram( $response );
            $result = TRUE;
        }
        
        return $result;
	}
	
	function logWebHookTelegram( $data ){
        file_put_contents( PPATH_CACHE . DS . 'telegram.log', "\n" . date( 'Y-m-d H:i:s' ) . "\n" . $data, FILE_APPEND );
	}
	
	function logWebHookTelegramGet(){
        $result = 'EMPTY';
        if( file_exists( PPATH_CACHE . DS . 'telegram.log') ){
            $result = file_get_contents( PPATH_CACHE . DS . 'telegram.log' );
        }
        
        return $result;
	}
	
	function logWebHookTelegramDel(){
        if( file_exists( PPATH_CACHE . DS . 'telegram.log' ) ){
            unlink( PPATH_CACHE . DS . 'telegram.log' );
        }
	}
	
?>
