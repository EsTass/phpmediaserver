<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//CHECK BASIC USER
	sqlite_users_initadmin();
	
	//_SESSION
	session_start();
	//SESSION FROM GET DATA PHPSESSION
	if( array_key_exists( 'PHPSESSION', $G_DATA ) 
	&& strlen( $G_DATA[ 'PHPSESSION' ] ) > 12
	){
        session_commit();
        session_id( $G_DATA[ 'PHPSESSION' ] );
        session_commit();
	}
	$sessionid = session_id();
	
	//Check Valid IP for Telegram webhook
	$validips = array();
	msgExternalIPs( $validips );
	
	//Check webhookt
	if( in_array( USER_IP, $validips ) 
	&& array_key_exists( 'action', $G_DATA )
	&& $G_DATA[ 'action' ] == 'msghookt'
	&& array_key_exists( 'r', $G_DATA )
	&& $G_DATA[ 'r' ] == 'r'
	){
        //msghookt
        $ACTIONINFO = 'WebHook From Valid IP';
		sqlite_log_insert( $G_DATA[ 'action' ], $ACTIONINFO );
        require( PPATH_ACTIONS . DS . $G_DATA[ 'action' ] . '.php' );
        exit();
	}elseif( ( $sessiondata = sqlite_session_getdata( '', $sessionid ) ) != FALSE 
	&& count( $sessiondata ) > 0
	&& array_key_exists( 0, $sessiondata ) > 0
	&& array_key_exists( 'user', $sessiondata[ 0 ] ) > 0
	&& strlen( $sessiondata[ 0 ][ 'user' ] ) > 0
	&& ( $userdata = sqlite_users_getdata( $sessiondata[ 0 ][ 'user' ] ) ) != FALSE
	&& count( $userdata ) > 0
	&& sqlite_session_update( $sessionid, $sessiondata[ 0 ][ 'user' ] ) != FALSE
	){
        if( array_key_exists( 'user', $G_DATA ) ){
            $G_DATA[ 'user' ] = strtolower( $G_DATA[ 'user' ] );
        }
		define( 'USERNAME', $sessiondata[ 0 ][ 'user' ] );
		if( sqlite_users_checkuseradmin( $sessiondata[ 0 ][ 'user' ] )
		){
			define( 'USERNAMEADMIN', $sessiondata[ 0 ][ 'user' ] );
		}
		if( $G_DATA[ 'action' ] == '' 
		|| $G_DATA[ 'action' ] == 'login'
		){
            $G_DATA[ 'action' ] = O_ACTIONDEFAULT;
		}
		sqlite_sessions_clean();
	}elseif( $G_DATA[ 'action' ] == 'login'
	&& checkLoginsAttempts() == FALSE
	){
		$ACTIONINFO = 'Max. Trys reached.';
		$G_DATA[ 'action' ] = '';
		//ban system
		if( checkLoginsBans() ){
            addBannedIP( USER_IP );
            sendMessage( 'LOGINMAXTRYS', USER_IP );
            sqlite_log_insert( 'bannedip', $ACTIONINFO . $_POST[ 'user' ] . ' ' . $_POST[ 'pass' ] );
        }
	}elseif( $G_DATA[ 'action' ] == 'login'
	&& array_key_exists( 'user', $G_DATA )
	&& strlen( $G_DATA[ 'user' ] ) > 0
	&& array_key_exists( 'pass', $G_DATA )
	&& strlen( $G_DATA[ 'pass' ] ) > 0 
	){
		//test user
		$G_DATA[ 'user' ] = strtolower( $G_DATA[ 'user' ] );
		if( sqlite_users_checkuser( $G_DATA[ 'user' ] , $G_DATA[ 'pass' ] )
		){
			
			sqlite_session_replace( $sessionid, $G_DATA[ 'user' ] );
			define( 'USERNAME', $G_DATA[ 'user' ] );
			if( sqlite_users_checkuseradmin( $G_DATA[ 'user' ] )
			){
				define( 'USERNAMEADMIN', $G_DATA[ 'user' ] );
			}
			//login valid
			$G_DATA[ 'action' ] = O_ACTIONDEFAULT;
            sqlite_sessions_clean();
            sendMessage( 'LOGINOK', USER_IP . ' - ' . USERNAME );
		}else{
			$ACTIONINFO = get_msg( 'LOGIN_ERRUSERPASS' );
			$G_DATA[ 'action' ] = 'login';
			sqlite_log_insert( 'loginerr', $ACTIONINFO . $G_DATA[ 'user' ] . ' ' . strlen( $G_DATA[ 'pass' ] ) );
			sendMessage( 'LOGINBAD', USER_IP . ' - ' . $G_DATA[ 'user' ] . ' ' . strlen( $G_DATA[ 'pass' ] ) );
		}
	}else{
		
		//sqlite_session_update( $sessionid, '' );
		$ACTIONINFO = get_msg( 'LOGIN_NEEDED' );
		$G_DATA[ 'action' ] = 'login';
		sqlite_log_insert( 'loginneeded', $ACTIONINFO );
	}
	
	//SESSION CLOSE NOT MORE WRITE
    session_write_close();
	
?>
