<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	/*
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	*/
	
    //dlna_sddpSend();
    //echo "<br /><br />SSDP SEND";
    
	//Send Broadcast DLNA
	if( defined( 'DLNA_ACTIVE' ) 
	&& DLNA_ACTIVE
	){
        //Create socket.
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if (!$socket) { die("socket_create failed.\n"); }
        
        //Set socket options.
        socket_set_nonblock($socket);
        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        if (defined('SO_REUSEPORT')){
            //echo "<br />REUSE PORT";
            socket_set_option($socket, SOL_SOCKET, SO_REUSEPORT, 1);
        }

        //Bind to any address & port 55554.
        if(!socket_bind($socket, '0.0.0.0', 1900))
            die("socket_bind failed.\n");

        //Wait for data.
        $read = array($socket); 
        $write = NULL; 
        $except = NULL;
        while(socket_select($read, $write, $except, NULL)) {

            //Read received packets with a maximum size of 5120 bytes.
            while(is_string($data = socket_read($socket, 5120))) {
                echo $data;
                sleep( 3 );
                dlna_sddpSend();
                echo "<br /><br />SSDP SEND";
                die();
            }

        }
    }else{
        echo "<br />DLNA Inactive, change DLNA_ACTIVE in config.";
    }
?>
