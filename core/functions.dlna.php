<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//DLNA BASE
	
    function dlna_udpSend($buf, $delay=3, $host="239.255.255.250", $port=1900)
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        
        // Bind the source address
        socket_bind($socket, DLNA_BINDIP );

        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
        socket_sendto($socket, $buf, strlen($buf), 0, $host, $port);
        socket_close($socket);
        usleep($delay * 1000);
    }
    
    function dlna_get_uuidStr()
    {
        // Create uuid based on host
        $key     = 'phpmediaserver_hash';
        $hash    = hash('md5', $key);
        $uuidstr = substr($hash, 0, 8) . '-' . substr($hash, 8, 4) . '-' . substr($hash, 12, 4) . '-' . substr($hash, 16, 4) . '-' . substr($hash, 20);
        return $uuidstr;
    }
    
    function dlna_sddpSend($delay=3, $host="239.255.255.250", $port=1900, $prefix="NT")
    {
        $strHeader  = 'NOTIFY * HTTP/1.1' . "\r\n";
        $strHeader .= 'HOST: ' . $host . ':' . $port . "\r\n";
        $strHeader .= 'LOCATION: ' . DLNA_WEB_BASEFOLDER_HTTP . 'dlna/rootDesc.php' . "\r\n";
        $strHeader .= 'SERVER: DLNADOC/1.50 UPnP/1.0 PHPMediaServer/0' . "\r\n";
        $strHeader .= 'CACHE-CONTROL: max-age=1800' . "\r\n";
        $strHeader .= 'NTS: ssdp:alive' . "\r\n";
        $uuidStr = dlna_get_uuidStr();
        
        $rootDevice = $prefix . ': upnp:rootdevice' . "\r\n";
        $rootDevice .= 'USN: uuid:' . $uuidStr . '::upnp:rootdevice' . "\r\n" . "\r\n";
        $buf = $strHeader . $rootDevice;
        dlna_udpSend($buf, $delay, $host, $port);
        $uuid = $prefix . ': uuid:' . $uuidStr . "\r\n";
        $uuid .= 'USN: uuid:' . $uuidStr . "\r\n" . "\r\n";
        $buf = $strHeader . $uuid;
        dlna_udpSend($buf, $delay, $host, $port);
        $deviceType = $prefix . ': urn:schemas-upnp-org:device:MediaServer:1' . "\r\n";
        $deviceType .= 'USN: uuid:' . $uuidStr . '::urn:schemas-upnp-org:device:MediaServer:1' . "\r\n" . "\r\n";
        $buf = $strHeader . $deviceType;
        dlna_udpSend($buf, $delay, $host, $port);
        $serviceCM = $prefix . ': urn:schemas-upnp-org:service:ConnectionManager:1' . "\r\n";
        $serviceCM .= 'USN: uuid:' . $uuidStr . '::urn:schemas-upnp-org:service:ConnectionManager:1' . "\r\n" . "\r\n";
        $buf = $strHeader . $serviceCM;
        dlna_udpSend($buf, $delay, $host, $port);
        $serviceCD = $prefix . ': urn:schemas-upnp-org:service:ContentDirectory:1' . "\r\n";
        $serviceCD .= 'USN: uuid:' . $uuidStr . '::urn:schemas-upnp-org:service:ContentDirectory:1' . "\r\n" . "\r\n";
        $buf = $strHeader . $serviceCD;
        echo "<br />" . $buf;
        dlna_udpSend($buf, $delay, $host, $port);
    }
        
    function get_dlna_user_session(){
        $result = '';
        $userdlna = DLNA_USERNAME;
        
        //get session for dlna or create
        //check user needed
        if( ( $ud = sqlite_users_getdata( $userdlna ) ) == FALSE 
        || !is_array( $ud )
        || count( $ud ) == 0
        ){
            sqlite_users_insert( $userdlna, getRandomString( 12 ) );
        }
        //get session
        if( ( $sd = sqlite_session_getdata( $userdlna ) ) == FALSE 
        || !is_array( $sd )
        || count( $sd ) == 0
        ){
            //Create new session
            $session = getRandomString( 26 );
            sqlite_session_insert( $session, $userdlna );
            $result = $session;
        }else{
            //reutilize session
            $result = $sd[ 0 ][ 'session' ];
        }
        
        return $result;
    }

?>
