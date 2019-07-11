<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//SQLITE
	
	$G_SQLITE_TABLES = array(
        //'table' => 'CREATE TABLE',
        'medialive' => "CREATE TABLE 'medialive' ('idmedialive' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'title' TEXT, 'url' TEXT, 'poster' TEXT, 'date' DATETIME)",
        'medialiveurls' => "CREATE TABLE 'medialiveurls' ('idmedialiveurls' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'title' TEXT, 'url' TEXT, 'date' DATETIME)",
        'playing' => "CREATE TABLE 'playing' ('idplaying' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 'user' TEXT, 'idmedia' INTEGER DEFAULT 0, 'date' DATETIME, 'mode' TEXT, 'pid' TEXT)",
	);
	
	$G_DB = FALSE;
	function sqlite_init(){
		//Vars
		global $G_DB;
		$result = FALSE;
		$filedb = PPATH_CACHE . DS . 'data.db';
		
		if( $G_DB != FALSE ){
			//$result = $G_DB;
		}else{
			$error = '';
			$G_DB = new SQLite3( $filedb, SQLITE3_OPEN_READWRITE );
			$G_DB->busyTimeout( 10000 );
			// WAL mode has better control over concurrency.
			// Source: https://www.sqlite.org/wal.html
			$G_DB->exec( 'PRAGMA journal_mode = MEMORY;' );
			$G_DB->exec( "PRAGMA synchronous = OFF;" );
			$G_DB->exec( "PRAGMA temp_store = MEMORY;" );
			$G_DB->exec( "PRAGMA case_sensitive_like = false;" );
			if( !$G_DB ){
				//echo "<br />SQLITE3: " . $filedb;
			}
		}
		
		$result = $G_DB;
		
		return $result;
	}
	
	function sqlite_getarray( $handle ){
		$result = array();
		if( $handle )
		while( ( $row = $handle->fetchArray( SQLITE3_ASSOC ) ) != FALSE ){
			$result[] = $row;
		}
		return $result;
	}
	
	function sqlite_db_close(){
		global $G_DB;
		
		if( $G_DB != FALSE ){
			$G_DB->close();
			$G_DB = FALSE;
		}
		
		return TRUE;
	}
	
	function sqlite_lastid() {
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE 
		){
            $result = $dbhandle->lastInsertRowid();
            sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_checktable_exist( $table, $create = FALSE ){
        $result = FALSE;
        
        $sql = "SELECT name FROM sqlite_master WHERE type='table' AND name='" . $table . "'";
        if( ( $dbhandle = sqlite_init() ) != FALSE ){
            $result = sqlite_getarray( $dbhandle->query( $sql ) );
			if( count( $result ) > 0 
			&& array_key_exists( 0, $result )
			&& array_key_exists( 'name', $result[ 0 ] )
			){
                $result = TRUE;
			}else{
                $result = FALSE;
			}
			sqlite_db_close();
        }
        
        return $result;
	}
	
	function sqlite_db_update(){
        $result = FALSE;
		global $G_SQLITE_TABLES;
        
		//Check extra G_SQLITE_TABLES
		foreach( $G_SQLITE_TABLES AS $table => $sqlt ){
            if( !sqlite_checktable_exist( $table ) 
            && ( $dbhandle = sqlite_init() ) != FALSE 
            ){
                @$dbhandle->exec( $sqlt );
                sqlite_db_close();
            }
		}
		
        return $result;
	}
	
	//SQLITE ACTIONS
	
	function sqlite_insert( $table, $data ) {
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'REPLACE INTO ' . $table . ' VALUES(';
			$sql .= '';
			$first = TRUE;
			foreach( $data AS $f => $v ){
                if( $first ){
                    $first = FALSE;
                }else{
                    $sql .= ' ,';
                }
                $sql .= ' "' . $v . '" ';
			}
			
			$sql .= ')';
			//die( $sql );
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_select( $table, $filters = array(), $order = FALSE ) {
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM ' . $table;
			$sql .= ' WHERE 1 = 1 ';
			foreach( $filters AS $f => $v ){
                $sql .= ' AND ' . $f . ' = "' . $dbhandle->escapeString( $v ) . '" ';
			}
			if( $order ){
                $sql .= ' ORDER BY ' . $order . ' ASC';
			}
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			if( count( $result ) > 0 ){
                $result = TRUE;
			}
			
			//die( $sql );
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_rows_total( $table, $filters = array(), $order = FALSE ) {
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT COUNT( * ) AS total FROM ' . $table;
			$sql .= ' WHERE 1 = 1 ';
			foreach( $filters AS $f => $v ){
                $sql .= ' AND ' . $f . ' = "' . $dbhandle->escapeString( $v ) . '" ';
			}
			if( $order ){
                $sql .= ' ORDER BY ' . $order . ' ASC';
			}
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			if( count( $result ) > 0 
			&& array_key_exists( 0, $result )
			&& array_key_exists( 'total', $result[ 0 ] )
			){
                $result = (int)$result[ 0 ][ 'total' ];
			}else{
                $result = FALSE;
			}
		}
		
		return $result;
	}
	
	//BAN SYSTEM
	//bans: ip, date
	function checkBannedIP( $ip, $hours = 24 ) {
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM bans ';
			$sql .= ' WHERE 1 = 1 ';
			$sql .= ' AND ip = "' . $dbhandle->escapeString( $ip ) . '" ';
			$date = date('Y-m-d H:i:s',strtotime('-' . $hours . ' hour',strtotime(date('Y-m-d H:i:s'))));
			$sql .= ' AND date > "' . $date . '" ';
			$sql .= ' ORDER BY date DESC LIMIT 1000';
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			if( count( $result ) > 0 ){
                $result = TRUE;
			}
		}
		
		return $result;
	}
	
	function addBannedIP( $ip, $extra_info = '', $extra_time = 0 ) {
		//Vars
		$result = FALSE;
		
        sqlite_log_insert( 'BAN IP', 'IP: ' . $ip . ' ' . $extra_info );
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'REPLACE INTO bans VALUES(';
			$sql .= '';
			$sql .= ' "' . $dbhandle->escapeString( $ip ) . '", ';
			$date = date('Y-m-d H:i:s',strtotime('+' . $extra_time . ' hour',strtotime(date('Y-m-d H:i:s'))));
			$sql .= ' "' . $date . '" ';
			$sql .= ')';
			//die( $sql );
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	//IPs WHITELIST/BANS
	
	//BANS
	
	function sqlite_bans_insert( $ip, $date = FALSE ){
		//Vars
		$result = FALSE;
		
		//1 year of ban
		if( $date == FALSE ){
            //$date = date( 'Y-m-d H:i:s' );
            $date = date('Y-m-d H:i:s',strtotime('+1 year',strtotime(date('Y-m-d H:i:s'))));
		}
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'REPLACE INTO bans VALUES(';
			$sql .= '';
			$sql .= ' "' . $dbhandle->escapeString( $ip ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $date ) . '" ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_bans_delete( $ip ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'DELETE FROM bans ';
			$sql .= '';
			$sql .= ' WHERE ip LIKE "' . $dbhandle->escapeString( $ip ) . '" ';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_bans_getdata( $search = '' ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM bans ';
			if( strlen( $search ) > 0 ){
				$sql .= ' WHERE ip LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
				$sql .= ' OR date LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
			}
			$sql .= ' ORDER BY date DESC LIMIT 10000';
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	//WHITELIST
	
	function sqlite_whitelist_insert( $ip, $date = FALSE ){
		//Vars
		$result = FALSE;
		
		if( $date == FALSE ){
            $date = date( 'Y-m-d H:i:s' );
		}
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'REPLACE INTO whitelist VALUES(';
			$sql .= '';
			$sql .= ' "' . $dbhandle->escapeString( $ip ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $date ) . '" ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_whitelist_delete( $ip ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'DELETE FROM whitelist ';
			$sql .= '';
			$sql .= ' WHERE ip LIKE "' . $dbhandle->escapeString( $ip ) . '" ';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_whitelist_getdata( $search = '' ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM whitelist ';
			if( strlen( $search ) > 0 ){
				$sql .= ' WHERE ip LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
				$sql .= ' OR date LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
			}
			$sql .= ' ORDER BY date DESC LIMIT 10000';
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	//SQLITE LOGS
	
	function sqlite_log_insert( $action, $description, $url = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			if( defined( 'USERNAME' ) ){
				$log_user = USERNAME;
			}else{
				$log_user = 'nouser';
			}
			
			if( $url == FALSE ){
                $url = getURL();
			}
            
			$sql = 'REPLACE INTO logs VALUES(';
			$sql .= '';
			$sql .= ' "' . date( 'Y-m-d H:i:s' ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $action ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $log_user ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( USER_IP ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $description ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $url ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( getReferer() ) . '" ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_log_getdata( $search = '' ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM logs ';
			if( strlen( $search ) > 0 ){
				$sql .= ' WHERE user LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
				$sql .= ' OR date LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
				$sql .= ' OR action LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
				$sql .= ' OR description LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
				$sql .= ' OR ip LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
				$sql .= ' OR url LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
				$sql .= ' OR referer LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
			}
			$sql .= ' ORDER BY date DESC LIMIT 1000';
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_log_getdata_action( $action, $date = '' ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM logs ';
            $sql .= ' WHERE action LIKE "' . $dbhandle->escapeString( $action ) . '" ';
            if( strlen( $date ) > 0 ){
                $sql .= ' AND date LIKE "' . $dbhandle->escapeString( $action ) . '%" ';
            }
			$sql .= ' ORDER BY date DESC LIMIT 1';
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_log_check_cron( $action = 'cron', $minutes = 60 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$date = date('Y-m-d H:i:s',strtotime('-' . $minutes . ' minutes',strtotime(date('Y-m-d H:i:s'))));
			$sql = 'SELECT * FROM logs ';
            $sql .= ' WHERE action LIKE "' . $action . '" ';
            $sql .= ' AND date > "' . $date . '" ';
			$sql .= ' ORDER BY date DESC LIMIT 1';
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_log_checkloginstrys(){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$date = date('Y-m-d H:i:s',strtotime('-1 hour',strtotime(date('Y-m-d H:i:s'))));
			$sql = 'SELECT * FROM logs ';
			$sql .= ' WHERE ip LIKE "' . $dbhandle->escapeString( USER_IP ) . '" ';
			$sql .= ' AND date > "' . $date . '" ';
			$sql .= ' AND action LIKE "loginerr" ';
			$sql .= ' ORDER BY date DESC LIMIT 1000';
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		//var_dump( $result );
		return $result;
	}
	
	function sqlite_log_checkloginsbans(){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$date = date('Y-m-d H:i:s',strtotime('-24 hour',strtotime(date('Y-m-d H:i:s'))));
			$sql = 'SELECT * FROM logs ';
			$sql .= ' WHERE ip LIKE "' . $dbhandle->escapeString( USER_IP ) . '" ';
			$sql .= ' AND date > "' . $date . '" ';
			$sql .= ' AND action LIKE "loginerr" ';
			$sql .= ' ORDER BY date DESC LIMIT 1000';
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		//var_dump( $result );
		return $result;
	}
	
	function sqlite_log_getsearchs( $minutes = 60 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$date = date('Y-m-d H:i:s',strtotime('-' . $minutes . ' minutes',strtotime(date('Y-m-d H:i:s'))));
			$sql = 'SELECT * FROM logs ';
			$sql .= ' WHERE date > "' . $date . '" ';
			$sql .= ' AND url LIKE "%search=%" ';
			$sql .= ' AND action LIKE "list%" ';
			$sql .= ' ORDER BY date DESC LIMIT 1000';
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		//var_dump( $result );
		return $result;
	}
	
	//SQLITE USERS
	
	function sqlite_users_insert( $user, $pass, $admin = '' ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'INSERT INTO users VALUES(';
			$sql .= '';
			$sql .= ' "' . $dbhandle->escapeString( $user ) . '", ';
			$sql .= ' "' . sha1( $pass ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $admin ) . '" ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_users_update( $user, $pass, $admin = '' ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'UPDATE users SET ';
			$sql .= '';
			$sql .= ' username = "' . $dbhandle->escapeString( $user ) . '", ';
			$sql .= ' password = "' . sha1( $pass ) . '", ';
			$sql .= ' admin = "' . $dbhandle->escapeString( $admin ) . '" ';
			$sql .= ' WHERE username = "' . $user . '" ';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_users_update_pass( $user, $pass ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'UPDATE users SET ';
			$sql .= '';
			$sql .= ' password = "' . sha1( $pass ) . '" ';
			$sql .= ' WHERE username = \'' . $dbhandle->escapeString( $user ) . '\' ';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_users_delete( $user ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'DELETE FROM users ';
			$sql .= '';
			$sql .= ' WHERE username LIKE "' . $dbhandle->escapeString( $user ) . '" ';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_users_getdata( $username = '' ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM users ';
			if( strlen( $username ) > 0 ){
				$sql .= ' WHERE username LIKE \'' . $dbhandle->escapeString( $username ) . '\' ';
				//$sql .= ' OR password LIKE "%' . $dbhandle->escapeString( $username ) . '%" ';
				//$sql .= ' OR admin LIKE "%' . $dbhandle->escapeString( $username ) . '%" ';
			}
			$sql .= ' ORDER BY username DESC LIMIT 1000';
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_users_initadmin(){
		//Vars
		$result = FALSE;
		$username = 'admin';
		$password = 'admin01020304';
		
		if( ( $udata = sqlite_users_getdata( $username ) ) == FALSE 
		|| !is_array( $udata )
		|| count( $udata ) == 0
		){
			sqlite_users_insert( $username, $password, $username );
		}
		
		return $result;
	}
	
	function sqlite_users_checkuser( $user, $password ){
		//Vars
		$result = FALSE;
		
		if( ( $udata = sqlite_users_getdata( $user ) ) != FALSE 
		&& count( $udata ) > 0
		&& array_key_exists( 0, $udata )
		&& array_key_exists( 'password', $udata[ 0 ] )
		){
			if( $udata[ 0 ][ 'password' ] == sha1( $password ) 
			|| $udata[ 0 ][ 'password' ] == $password
			){
				$result = TRUE;
			}
		}
		
		return $result;
	}
	
	function sqlite_users_checkuseradmin( $user ){
		//Vars
		$result = FALSE;
		
		if( ( $udata = sqlite_users_getdata( $user ) ) != FALSE 
		&& count( $udata ) > 0
		&& array_key_exists( 0, $udata )
		&& array_key_exists( 'admin', $udata[ 0 ] )
		){
			if( $udata[ 0 ][ 'admin' ] == $user
			){
				$result = TRUE;
			}
		}
		
		return $result;
	}
	
	//SQLITE SESSIONS
	//sessions = date, session, user, ip
	
	function sqlite_session_insert( $session = FALSE, $user = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			if( $session == FALSE ){
				$session = session_id();
			}
			if( is_string( $user ) ){
				
			}elseif( $user == FALSE
			&& defined( 'USERNAME' ) 
			){
				$user = USERNAME;
			}else{
				$user = '';
			}
			
			$sql = 'INSERT INTO sessions VALUES(';
			$sql .= '';
			$sql .= ' \'' . date( 'Y-m-d H:i:s' ) . '\', ';
			$sql .= ' \'' . $dbhandle->escapeString( $session ) . '\', ';
			$sql .= ' \'' . $dbhandle->escapeString( $user ) . '\', ';
			$sql .= ' \'' . $dbhandle->escapeString( USER_IP ) . '\' ';
			$sql .= ')';
			
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_session_update( $session = FALSE, $user = FALSE, $ip = FALSE ){
		//Vars
		$result = FALSE;
		
		if( $session == FALSE ){
			$session = session_id();
		}
		
		if( $ip == FALSE ){
			$ip = USER_IP;
		}
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'UPDATE sessions SET ';
			$sql .= '';
			if( $user == FALSE 
			){
				$sql .= ' user = \'' . $dbhandle->escapeString( $user ) . '\', ';
			}
			$sql .= ' ip = \'' . $dbhandle->escapeString( USER_IP ) . '\', ';
			$sql .= ' session = \'' . $dbhandle->escapeString( $session ) . '\', ';
			$sql .= ' date = "' . date( 'Y-m-d H:i:s' ) . '" ';
			$sql .= ' WHERE session LIKE \'' . $dbhandle->escapeString( $session ) . '\' ';
			
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_session_replace( $session = FALSE, $user = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			if( $session == FALSE ){
				$session = session_id();
			}
			if( is_string( $user ) ){
				
			}elseif( $user == FALSE
			&& defined( 'USERNAME' ) 
			){
				$user = USERNAME;
			}else{
				$user = '';
			}
			
			$sql = 'REPLACE INTO sessions VALUES(';
			$sql .= '';
			$sql .= ' \'' . date( 'Y-m-d H:i:s' ) . '\', ';
			$sql .= ' \'' . $dbhandle->escapeString( $session ) . '\', ';
			$sql .= ' \'' . $dbhandle->escapeString( $user ) . '\', ';
			$sql .= ' \'' . $dbhandle->escapeString( USER_IP ) . '\' ';
			$sql .= ')';
			
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_session_getdata( $search = '', $session = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM sessions ';
			if( strlen( $search ) > 0 ){
				$sql .= ' WHERE ( ';
				$sql .= ' date LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR session LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR user LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR ip LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ) ';
				if( is_string( $session ) ){
					$sql .= ' AND session LIKE \'' . $dbhandle->escapeString( $session ) . '\' ';
				}
			}else{
				if( is_string( $session ) ){
					$sql .= ' WHERE session = \'' . $dbhandle->escapeString( $session ) . '\' ';
				}
			}
			$sql .= ' ORDER BY date DESC LIMIT 1000';
			//echo $sql;
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_session_getdata_user( $user ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM sessions ';
			$sql .= ' WHERE user LIKE \'' . $dbhandle->escapeString( $user ) . '\' ';
			$sql .= ' ORDER BY date DESC LIMIT 1000';
			//echo $sql;
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_session_delete( $session = FALSE ){
		//Vars
		$result = FALSE;
		
		if( $session == FALSE ){
			$session = session_id();
		}
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'DELETE FROM sessions ';
			$sql .= '';
			$sql .= ' WHERE session LIKE \'' . $dbhandle->escapeString( $session ) . '\' ';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function get_session_admin(){
		//Vars
		$result = '' . getRandomString();
		
		if( ( $sessiond = sqlite_session_getdata( 'admin' ) ) != FALSE 
		&& count( $sessiond ) > 0
		){
            if( array_key_exists( 0, $sessiond ) 
            && is_array( $sessiond[ 0 ] )
            && array_key_exists( 'session', $sessiond[ 0 ] )
            ){
                $result = $sessiond[ 0 ][ 'session' ];
            }elseif( array_key_exists( 'session', $sessiond ) ){
                $result = $sessiond[ 'session' ];
            }elseif( sqlite_session_insert( $result, 'admin' )
            ){
                
            }
		}
		
		return $result;
	}
	
	function sqlite_sessions_clean( $echo = FALSE ){
		//Vars
		$result = FALSE;
		
		if( defined( 'O_USERS_MAX_SESSIONS' ) 
		&& defined( 'O_USERSADMIN_MAX_SESSIONS' ) 
		&& (int)O_USERS_MAX_SESSIONS > 0
		&& defined( 'USERNAME' )
		&& strlen( USERNAME ) > 0
		&& ( $d = sqlite_session_getdata_user( USERNAME ) ) != FALSE
		&& is_array( $d )
		&& count( $d ) > 0
		){
            
            if( !defined( 'USERNAMEADMIN' )
            && (int)O_USERS_MAX_SESSIONS == 0
            ){
                $max = 10;
            }elseif( defined( 'USERNAMEADMIN' )
            && (int)O_USERSADMIN_MAX_SESSIONS == 0
            ){
                $max = 10;
            }elseif( !defined( 'USERNAMEADMIN' )
            ){
                $max = (int)O_USERS_MAX_SESSIONS;
            }else{
                $max = (int)O_USERSADMIN_MAX_SESSIONS;
            }
            foreach( $d AS $row ){
                if( $max > 0 ){
                    $max--;
                }else{
                    sqlite_session_delete( $row[ 'session' ] );
                    if( $echo ) echo "-";
                    $result = TRUE;
                }
            }
		}
		
		return $result;
	}
	
	//WHITELIST IP SYSTEM
	//whitelist: ip, date
	function checkWhitedIP( $ip, $hours = 8760 ) { //1YEAR
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM whitelist ';
			$sql .= ' WHERE 1 = 1 ';
			$sql .= ' AND ip = "' . $dbhandle->escapeString( $ip ) . '" ';
			$date = date('Y-m-d H:i:s',strtotime('-' . $hours . ' hour',strtotime(date('Y-m-d H:i:s'))));
			$sql .= ' AND date > "' . $date . '" ';
			$sql .= ' ORDER BY date DESC LIMIT 1';
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			if( count( $result ) > 0 ){
                $result = TRUE;
			}
		}
		
		return $result;
	}
	
	function addWhitedIP( $ip ) {
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'REPLACE INTO whitelist VALUES(';
			$sql .= '';
			$sql .= ' "' . $dbhandle->escapeString( $ip ) . '", ';
			$sql .= ' "' . date( 'Y-m-d H:i:s' ) . '" ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	//MEDIA ELEMENTS
	//idmedia file langs subs idmediainfo
	
	function sqlite_media_insert( $file ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'INSERT INTO media VALUES(';
			$sql .= '';
			$sql .= ' NULL, ';
			$sql .= ' "' . $dbhandle->escapeString( $file ) . '", ';
			$sql .= ' "", ';
			$sql .= ' "", ';
			$sql .= ' 0 ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			if( $result
			&& ( $lastid = sqlite_lastid() ) 
            ){
                $result = $lastid;
			}
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_update( $idmedia, $file, $langs, $subs, $idmediainfo ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'UPDATE media SET ';
			$sql .= '';
			$sql .= ' file = "' . $dbhandle->escapeString( $file ) . '", ';
			$sql .= ' langs = "' . $dbhandle->escapeString( $langs ) . '", ';
			$sql .= ' subs = "' . $dbhandle->escapeString( $subs ) . '" ';
			$sql .= ' idmediainfo = ' . $idmediainfo . ' ';
			$sql .= ' WHERE idmedia = "' . $idmedia . '" ';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_update_mediainfo( $idmedia, $idmediainfo ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'UPDATE media SET ';
			$sql .= '';
			$sql .= ' idmediainfo = ' . $idmediainfo . ' ';
			$sql .= ' WHERE idmedia = "' . $idmedia . '" ';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_delete( $idmedia ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'DELETE FROM media ';
			$sql .= '';
			$sql .= ' WHERE idmedia = ' . $idmedia . ' ';
			//die( $sql );
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata( $idmedia = FALSE, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			if( $idmedia != FALSE
			&& is_numeric( $idmedia )
			&& (int)$idmedia > 0
			){
				$sql .= ' WHERE idmedia = ' . $idmedia . ' ';
			}
			$sql .= ' ORDER BY file DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_file_search( $search, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			if( strlen( $search ) > 0
			){
				$sql .= ' WHERE file LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
			}
			$sql .= ' ORDER BY file DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_order_mediainfo( $idmedia = FALSE, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' WHERE idmediainfo > 0 ';
			if( $idmedia != FALSE
			&& is_numeric( $idmedia )
			&& (int)$idmedia > 0
			){
				$sql .= ' AND idmedia = ' . $idmedia . ' ';
			}
			$sql .= ' GROUP BY idmedia ORDER BY idmediainfo ASC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_filtered( $search, $limit = 1000, $page = 0, $onlygenre = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			if( strlen( $search ) > 0 ){
                if( $onlygenre == FALSE ){
                    $sql .= ' AND ( mediainfo.title LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR media.file LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.plot LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.year LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.actor LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.plot LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.titleepisode LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ')';
                }else{
                    $sql .= ' AND ( mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    //Add genres search O_MENU_GENRES
                    $lan_genres = O_MENU_GENRES;
                    if( array_key_exists( $search, $lan_genres ) 
                    && strlen( $lan_genres[ $search ] ) > 0
                    ){
                        $sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $lan_genres[ $search ] ) . '%\' ';
                    }
                    if( ( $tk = array_search( $search, $lan_genres ) ) !== FALSE
                    && strlen( $lan_genres[ $tk ] ) > 0
                    && strlen( $tk ) > 0
                    ){
                        $sql .= ' OR mediainfo.genre LIKE \'%' . $tk . '%\' ';
                    }
                    $sql .= ')';
                }
			}
			$sql .= ' GROUP BY media.idmediainfo ORDER BY media.idmedia DESC LIMIT ' . $limit;
			if( $page !== FALSE ){
                $sql .= ' OFFSET ' . ( $page * $limit );
            }
			//if( check_user_admin() ) var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_filtered_ext( $search, $year = FALSE, $year2 = FALSE, $rating = FALSE, $genres = FALSE, $orderby = FALSE, $limit = 1000, $page = 0 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			if( strlen( $search ) > 0 ){
				$sql .= ' AND ( mediainfo.title LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR media.file LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.plot LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.year LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.actor LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.plot LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.titleepisode LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ')';
			}
			if( $year > 0 
			&& $year2 == FALSE
			){
                $sql .= ' AND mediainfo.year = \'' . $year . '\' ';
			}elseif( $year > 0 
			&& $year2 > 0
			){
                $sql .= ' AND mediainfo.year > \'' . $year . '\' ';
                $sql .= ' AND mediainfo.year < \'' . $year2 . '\' ';
			}
			if( $rating > 0 ){
                $sql .= ' AND mediainfo.rating > \'' . $rating . '\' ';
			}
			if( is_array( $genres ) 
			&& count( $genres ) > 0
			){
                foreach( $genres AS $genre ){
                    $sql .= ' AND mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $genre ) . '%\' ';
                }
			}
			$sql .= ' GROUP BY mediainfo.title ';
			if( $orderby != FALSE ){
                $sql .= ' ORDER BY mediainfo.' . $orderby . ' DESC ';
            }
			$sql .= ' LIMIT ' . $limit;
			if( $page !== FALSE ){
                $sql .= ' OFFSET ' . ( $page * $limit );
            }
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_filtered_grouped( $search, $limit = 1000, $page = 0, $force_series = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			if( strlen( $search ) > 0 ){
				$sql .= ' AND ( mediainfo.title LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR media.file LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.plot LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.year LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.actor LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.plot LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR mediainfo.titleepisode LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				//Add genres search O_MENU_GENRES
				$lan_genres = O_MENU_GENRES;
				if( array_key_exists( $search, $lan_genres ) 
				&& strlen( $lan_genres[ $search ] ) > 0
				){
                    $sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $lan_genres[ $search ] ) . '%\' ';
				}
				if( ( $tk = array_search( $search, $lan_genres ) ) !== FALSE
				&& strlen( $lan_genres[ $tk ] ) > 0
				&& strlen( $tk ) > 0
				){
                    $sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $tk ) . '%\' ';
				}
				$sql .= ')';
			}
			if( $force_series ){
                $sql .= ' AND mediainfo.season != \'\' ';
                $sql .= ' AND mediainfo.episode != \'\' ';
                $sql .= ' AND mediainfo.season > 0 ';
                $sql .= ' AND mediainfo.episode > 0 ';
			}
			$sql .= ' GROUP BY mediainfo.title ORDER BY media.idmedia DESC LIMIT ' . $limit;
			if( $page !== FALSE ){
                $sql .= ' OFFSET ' . ( $page * $limit );
            }
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_filtered_grouped_pages_total( $search, $limit = 1000, $force_series = FALSE, $onlygenre = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT COUNT( idmedia ) AS quantity FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			if( strlen( $search ) > 0 ){
                if( $onlygenre == FALSE ){
                    $sql .= ' AND ( mediainfo.title LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR media.file LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.plot LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.year LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.actor LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.plot LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.titleepisode LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                }else{
                    $sql .= ' AND ( mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    //Add genres search O_MENU_GENRES
                    $lan_genres = O_MENU_GENRES;
                    if( array_key_exists( $search, $lan_genres ) 
                    && strlen( $lan_genres[ $search ] ) > 0
                    ){
                        $sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $lan_genres[ $search ] ) . '%\' ';
                    }
                    if( ( $tk = array_search( $search, $lan_genres ) ) !== FALSE
                    && strlen( $lan_genres[ $tk ] ) > 0
                    && strlen( $tk ) > 0
                    ){
                        $sql .= ' OR mediainfo.genre LIKE \'%' . $tk . '%\' ';
                    }
                }
				$sql .= ')';
			}
			if( $force_series ){
                $sql .= ' AND mediainfo.season != \'\' ';
                $sql .= ' AND mediainfo.episode != \'\' ';
                $sql .= ' AND mediainfo.season > 0 ';
                $sql .= ' AND mediainfo.episode > 0 ';
                //GROUP BY mediainfo.title 
                //$sql .= ' GROUP BY mediainfo.title ';
			}
			$sql .= ' ORDER BY media.idmedia DESC'; 
			
			// LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			if( is_array( $result ) 
			&& array_key_exists( 0, $result )
			&& is_array( $result[ 0 ] ) 
			&& array_key_exists( 'quantity', $result[ 0 ] )
			&& $result[ 0 ][ 'quantity' ] > 0
			){
                $result = (int)$result[ 0 ][ 'quantity' ];
			}else{
                $result = FALSE;
			}
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_filtered_grouped_pages_total_g( $search, $limit = 1000, $force_series = FALSE, $onlygenre = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT idmedia AS quantity FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			if( strlen( $search ) > 0 ){
                if( $onlygenre == FALSE ){
                    $sql .= ' AND ( mediainfo.title LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR media.file LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.plot LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.year LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.actor LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.plot LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    $sql .= ' OR mediainfo.titleepisode LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                }else{
                    $sql .= ' AND ( mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
                    //Add genres search O_MENU_GENRES
                    $lan_genres = O_MENU_GENRES;
                    if( array_key_exists( $search, $lan_genres ) 
                    && strlen( $lan_genres[ $search ] ) > 0
                    ){
                        $sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $lan_genres[ $search ] ) . '%\' ';
                    }
                    if( ( $tk = array_search( $search, $lan_genres ) ) !== FALSE
                    && strlen( $lan_genres[ $tk ] ) > 0
                    && strlen( $tk ) > 0
                    ){
                        $sql .= ' OR mediainfo.genre LIKE \'%' . $tk . '%\' ';
                    }
                }
				$sql .= ')';
			}
			if( $force_series ){
                $sql .= ' AND mediainfo.season != \'\' ';
                $sql .= ' AND mediainfo.episode != \'\' ';
                $sql .= ' AND mediainfo.season > 0 ';
                $sql .= ' AND mediainfo.episode > 0 ';
                //GROUP BY mediainfo.title 
                $sql .= ' GROUP BY mediainfo.title ';
			}
			$sql .= ' ORDER BY media.idmedia DESC'; 
			
			// LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			if( is_array( $result ) 
			){
                $result = (int)count( $result );
			}else{
                $result = FALSE;
			}
		}
		
		return $result;
	}
	
	//last 3 months
	function sqlite_media_getdata_premiere( $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			$strdate = date( 'Y-m-d', strtotime( 'NOW - 3 months' ) );
			$sql .= ' AND mediainfo.sorttitle > \'' . $strdate . '\'';
			$sql .= ' ORDER BY mediainfo.sorttitle DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	//last in sorttitle
	function sqlite_media_getdata_premiere_ex( $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			$sql .= ' GROUP BY mediainfo.title ';
			$sql .= ' ORDER BY mediainfo.sorttitle DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	//last 3 months better rating
	function sqlite_media_getdata_premiere_ex2( $limit = 1000, $months = 3 ){
		//Vars
		$result = FALSE;
		
		//min quantity
		if( $limit < 100 ){
            $limit2 = 100;
		}else{
            $limit2 = $limit;
		}
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			$strdate = date( 'Y-m-d', strtotime( 'NOW - ' . $months . ' months' ) );
			$sql .= ' AND mediainfo.sorttitle > \'' . $strdate . '\'';
			$sql .= ' GROUP BY mediainfo.title ';
			$sql .= ' ORDER BY mediainfo.sorttitle DESC LIMIT ' . $limit2;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			//Rearder array by rating
			sqlite_reorder_mediainfo( $result );
			//cut array
			$result = array_slice($result, 0, $limit);
		}
		
		return $result;
	}
	
	//last 3 months better rating only movies
	function sqlite_media_getdata_premiere_ex3( $limit = 1000, $months = 3 ){
		//Vars
		$result = FALSE;
		
		//min quantity
		if( $limit < 100 ){
            $limit2 = 100;
		}else{
            $limit2 = $limit;
		}
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			$strdate = date( 'Y-m-d', strtotime( 'NOW - ' . $months . ' months' ) );
			$sql .= ' AND mediainfo.sorttitle > \'' . $strdate . '\'';
			$sql .= ' AND mediainfo.season == \'\'';
			$sql .= ' GROUP BY mediainfo.title ';
			$sql .= ' ORDER BY mediainfo.sorttitle DESC LIMIT ' . $limit2;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			//Rearder array by rating
			sqlite_reorder_mediainfo( $result );
			//cut array
			$result = array_slice($result, 0, $limit);
		}
		
		return $result;
	}
	
	//last 3 months random elements
	function sqlite_media_getdata_premiere_ex4( $limit = 1000, $months = 3 ){
		//Vars
		$result = FALSE;
		
		//min quantity
		if( $limit < 100 ){
            $limit2 = 100;
		}else{
            $limit2 = $limit;
		}
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			$strdate = date( 'Y-m-d', strtotime( 'NOW - ' . $months . ' months' ) );
			$sql .= ' AND mediainfo.sorttitle > \'' . $strdate . '\'';
			$sql .= ' AND mediainfo.season == \'\'';
			$sql .= ' GROUP BY mediainfo.title ';
			$sql .= ' ORDER BY mediainfo.sorttitle DESC LIMIT ' . $limit2;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			//Rearder array by rating
            shuffle( $result );
			//cut array
			$result = array_slice($result, 0, $limit);
		}
		
		return $result;
	}
	
	//last 3 months series year + season + weeks*episode
	function sqlite_media_getdata_premiere_ex5( $limit = 1000, $months = 3 ){
		//Vars
		$result = FALSE;
		
		//min quantity
		if( $limit < 100 ){
            $limit2 = 100;
		}else{
            $limit2 = $limit;
		}
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			$strdate = date( 'Y-m-d', strtotime( 'NOW - ' . $months . ' months' ) );
			$sql .= ' AND mediainfo.episode != \'\'';
			$sql .= ' AND mediainfo.season != \'\'';
			$sql .= ' AND ( mediainfo.sorttitle > \'' . $strdate . '\' ';
			$sql .= '  OR ( date( date( mediainfo.sorttitle, printf( \'+%d years\', mediainfo.season ) ), printf( \'+%d days\', ( mediainfo.episode * 7 ) ) ) ) >  \'' . $strdate . '\' ) ' ;
			$sql .= ' GROUP BY mediainfo.title ';
			$sql .= ' ORDER BY mediainfo.sorttitle DESC, ';
			$sql .= ' mediainfo.season DESC, ';
			$sql .= ' mediainfo.episode DESC ';
			$sql .= ' LIMIT ' . $limit2;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			//Rearder array by rating
            shuffle( $result );
			//cut array
			$result = array_slice($result, 0, $limit);
		}
		
		return $result;
	}
	
	//function reorder array mediainfo by rating
	function sqlite_reorder_mediainfo(&$array, $key = 'rating') {
        $sorter=array();
        $ret=array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii]=$va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii]=$array[$ii];
        }
        $array=$ret;
    }
	
	function sqlite_media_getdata_best( $yearsold = FALSE, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			if( $yearsold !== FALSE ){
                $strdate = date( 'Y-m-d', strtotime( 'NOW - ' . $yearsold . ' years' ) );
                $sql .= ' AND mediainfo.sorttitle > \'' . $strdate . '\'';
            }
			$sql .= ' ORDER BY mediainfo.rating, mediainfo.votes DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_related( $genres, $limit = 1000, $idmediainfo = FALSE ){
		//Vars
		$result = FALSE;
		
		if( is_string( $genres ) 
		&& stripos( $genres, ',' ) === FALSE
		){
            $genres = array( $genres );
		}elseif( is_string( $genres ) 
		&& stripos( $genres, ',' ) !== FALSE
		&& ( $genres2 = explode( ',', $genres ) ) !== FALSE
		&& is_array( $genres2 )
		&& count( $genres2 ) > 0
		){
            $genres = $genres2;
		}
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo > 0 ';
			if( $idmediainfo != FALSE
			&& is_numeric( $idmediainfo )
			&& $idmediainfo > 0 
			){
                $sql .= ' AND media.idmediainfo != ' . $idmediainfo . ' ';
			}
			foreach( $genres AS $g ){
                $sql .= ' AND mediainfo.genre LIKE \'%' . $dbhandle->escapeString( trim( $g ) ) . '%\' ';
                //Add genres search O_MENU_GENRES
				$lan_genres = O_MENU_GENRES;
				if( array_key_exists( $g, $lan_genres ) 
				&& strlen( $lan_genres[ $g ] ) > 0
				){
                    $sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $lan_genres[ $g ] ) . '%\' ';
				}
				if( ( $tk = array_search( $g, $lan_genres ) ) !== FALSE
				&& strlen( $lan_genres[ $tk ] ) > 0
				&& strlen( $tk ) > 0
				){
                    $sql .= ' OR mediainfo.genre LIKE \'%' . $dbhandle->escapeString( $tk ) . '%\' ';
				}
            }
			$sql .= ' GROUP BY mediainfo.title ORDER BY RANDOM() LIMIT ' . $limit;
			//die( $sql );
			//var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_chapters( $idmediainfo_title, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE mediainfo.title LIKE \'' . $dbhandle->escapeString( $idmediainfo_title ) . '\' ';
			$sql .= ' ORDER BY mediainfo.season, mediainfo.episode DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_mediainfo( $idmediainfo, $limit = 100 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'SELECT * FROM media ';
			$sql .= ' WHERE idmediainfo = ' . $idmediainfo;
			$sql .= ' ORDER BY idmedia ASC LIMIT ' . $limit;
			//var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_identify_orderer( $search = '', $limit = 100 ){
		//Vars
		$result = array();
		
		if( ( $data = sqlite_media_getdata_identify_tryed( $search, $limit ) ) != FALSE
		&& is_array( $data )
		){
            $result = array_merge( $result, $data );
		}
		if( ( $data = sqlite_media_getdata_identify_auto( $search, $limit ) ) != FALSE 
		&& is_array( $data )
		){
            $result = array_merge( $result, $data );
		}
		if( ( $data = sqlite_media_getdata_identify_badchapter( $search, $limit ) ) != FALSE 
		&& is_array( $data )
		){
            $result = array_merge( $result, $data );
		}
		if( ( $data = sqlite_media_getdata_identify_added( $search, $limit ) ) != FALSE 
		&& is_array( $data )
		){
            $result = array_merge( $result, $data );
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_identify_badchapter( $search = '', $limit = 100 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'SELECT *, media.idmediainfo AS idmediainfo FROM media ';
			$sql .= ' LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
            $sql .= ' WHERE media.idmediainfo > 0';
            $sql .= ' AND mediainfo.season != ""';
            $sql .= ' AND mediainfo.episode = ""';
            
			if( strlen( $search ) > 0 ){
                $sql .= ' AND file LIKE \'%' . $dbhandle->escapeString( $search ) . '%\'';
			}
			$sql .= ' ORDER BY media.idmedia DESC LIMIT ' . $limit;
			//var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_identify_added( $search = '', $limit = 100, $addnoident = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'SELECT *, media.idmediainfo AS idmediainfo FROM media ';
			$sql .= ' LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			if( $addnoident == FALSE ){
                $sql .= ' WHERE media.idmediainfo > 0';
            }else{
                $sql .= ' WHERE 1 = 1';
            }
			if( strlen( $search ) > 0 ){
                $sql .= ' AND file LIKE \'%' . $dbhandle->escapeString( $search ) . '%\'';
			}
			$sql .= ' ORDER BY media.idmedia DESC LIMIT ' . $limit;
			//var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_identify( $search = '', $limit = 100 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'SELECT *, media.idmediainfo AS idmediainfo FROM media ';
			$sql .= ' LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			//$sql .= ' WHERE idmediainfo <= 0';
			$sql .= ' WHERE 1 = 1';
			if( strlen( $search ) > 0 ){
                $sql .= ' AND file LIKE \'%' . $dbhandle->escapeString( $search ) . '%\'';
			}
			$sql .= ' ORDER BY media.idmediainfo ASC LIMIT ' . $limit;
			//var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_identify_tryed( $search = '', $limit = 100 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'SELECT * FROM media ';
			//$sql .= ' LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE media.idmediainfo < 0';
			if( strlen( $search ) > 0 ){
                $sql .= ' AND file LIKE \'%' . $dbhandle->escapeString( $search ) . '%\'';
			}
			$sql .= ' ORDER BY idmedia ASC LIMIT ' . $limit;
			//var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_identify_auto( $search = '', $limit = 100 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'SELECT * FROM media ';
			$sql .= ' WHERE idmediainfo = 0'; //-1 have auto-checked
			if( strlen( $search ) > 0 ){
                $sql .= ' AND file LIKE \'%' . $dbhandle->escapeString( $search ) . '%\'';
			}
			$sql .= ' ORDER BY idmedia ASC LIMIT ' . $limit;
			//var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_check_exist( $file ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'SELECT * FROM media ';
			$sql .= ' WHERE file LIKE \'' . $dbhandle->escapeString( $file ) . '\'';
			$sql .= ' ORDER BY idmedia ASC';
			//var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			if( is_array( $result ) 
			&& count( $result ) > 0
			&& is_array( $result[ 0 ] )
			&& array_key_exists( 'idmedia', $result[ 0 ] )
			){
                $result = $result[ 0 ][ 'idmedia' ];
			}
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_check_exist_search( $search ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'SELECT * FROM media ';
			$sql .= ' WHERE file LIKE \'%' . $search . '%\'';
			//Add Exlude VOSE
			$sql .= ' AND file NOT LIKE \'%VOSE%\' ';
			$sql .= ' AND file NOT LIKE \'%V.O.S.E%\'';
			$sql .= ' AND file NOT LIKE \'%Subt%\'';
			$sql .= ' ORDER BY idmedia ASC';
			//var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			if( is_array( $result ) 
			&& count( $result ) > 0
			&& is_array( $result[ 0 ] )
			&& array_key_exists( 'idmedia', $result[ 0 ] )
			){
                $result = $result[ 0 ][ 'idmedia' ];
			}
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_media_getdata_old_file( $limit = 1 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM media ';
			$sql .= ' ORDER BY idmedia ASC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	//MEDIAINFO ELEMENTS
	//functions.media.php::$G_MEDIAINFO array
	
	function sqlite_mediainfo_insert( $data ){
		//Vars
		$result = FALSE;
		global $G_MEDIAINFO;
		
		if( array_key_exists( 'dateadded', $data ) 
		&& strlen( $data[ 'dateadded' ] ) == 0
		){
            $data[ 'dateadded' ] = date( 'Y-m-d H:i:s' );
		}
		
		$data2 = array();
		foreach( $data AS $dk => $dv ){
            if( array_key_exists( $dk, $G_MEDIAINFO ) ){
                $data2[ $dk ] = $dv;
            }
		}
		
		if( count( $data2 ) == count( $G_MEDIAINFO )
		&& ( $dbhandle = sqlite_init() ) != FALSE 
		){
			$sql = 'INSERT INTO mediainfo VALUES(';
			$sql .= '';
			//$sql .= ' NULL ';
			$first = TRUE;
			foreach( $data2 As $k => $v ){
                if( $first ){
                    $first = FALSE;
                }else{
                    $sql .= ',';
                }
                if( $k == 'idmediainfo' ){
                    $sql .= ' ' . $dbhandle->escapeString( $v ) . ' ';
                }elseif( $v == NULL 
                || $v == 'NULL'
                ){
                    $sql .= ' \'\' ';
                }else{
                    $sql .= ' \'' . $dbhandle->escapeString( $v ) . '\' ';
                }
			}
			$sql .= ')';
			//var_dump( $sql );die();
			$result = $dbhandle->exec( $sql );
			if( $result
			&& ( $lastid = sqlite_lastid() ) 
            ){
                $result = $lastid;
			}
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_mediainfo_update( $data ){
		//Vars
		$result = FALSE;
		global $G_MEDIAINFO;
		
		if( array_key_exists( 'dateadded', $data ) 
		&& strlen( $data[ 'dateadded' ] ) == 0
		){
            $data[ 'dateadded' ] = date( 'Y-m-d H:i:s' );
		}
		
		$data2 = array();
		foreach( $data AS $dk => $dv ){
            if( array_key_exists( $dk, $G_MEDIAINFO ) ){
                $data2[ $dk ] = $dv;
            }
		}
		
		if(  count( $data2 ) == count( $G_MEDIAINFO )
		&& ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'UPDATE mediainfo SET ';
			$sql .= '';
			
			$first = TRUE;
			foreach( $data2 As $k => $v ){
                if( $first ){
                    $first = FALSE;
                }else{
                    $sql .= ',';
                }
                $sql .= ' ' . $k . ' = \'' . $dbhandle->escapeString( $v ) . '\' ';
			}
			$sql .= ' WHERE idmediainfo = ' . $data2[ 'idmediainfo' ] . ' ';
			//die( $sql );
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_mediainfo_delete( $idmediainfo ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'DELETE FROM mediainfo ';
			$sql .= '';
			$sql .= ' WHERE idmediainfo = ' . $idmediainfo . ' ';
			//die( $sql );
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_mediainfo_getdata( $idmediainfo = FALSE, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM mediainfo ';
			if( $idmediainfo ){
				$sql .= ' WHERE idmediainfo = ' . $idmediainfo . ' ';
			}
			$sql .= ' ORDER BY idmediainfo DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_mediainfo_search( $search = '', $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM mediainfo ';
			if( strlen( $search ) > 0 ){
				$sql .= ' WHERE title LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR plot LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR tagline LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR genre LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR actor LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
				$sql .= ' OR titleepisode LIKE \'%' . $dbhandle->escapeString( $search ) . '%\' ';
			}
			$sql .= ' ORDER BY idmediainfo DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_mediainfo_search_imdb( $imdb, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM mediainfo ';
			$sql .= ' WHERE imdbid LIKE \'' . $dbhandle->escapeString( $imdb ) . '\' ';
			$sql .= ' OR imdb LIKE \'%' . $dbhandle->escapeString( $imdb ) . '\' ';
			$sql .= ' ORDER BY idmediainfo DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_mediainfo_search_title( $title, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM mediainfo ';
			$sql .= ' WHERE title LIKE \'' . $dbhandle->escapeString( $title ) . '\' ';
			$sql .= ' ORDER BY idmediainfo DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_mediainfo_search_file( $file, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT mediainfo.*, media.* FROM media ';
			$sql .= ' LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			$sql .= ' WHERE file LIKE \'' . $dbhandle->escapeString( $file ) . '\' ';
			$sql .= ' AND media.idmediainfo > 0 ';
			$sql .= ' ORDER BY mediainfo.idmediainfo DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_mediainfo_getdata_titles( $idmediainfo = FALSE, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM mediainfo ';
			if( $idmediainfo ){
				$sql .= ' WHERE idmediainfo = ' . $idmediainfo . ' ';
			}
			$sql .= ' GROUP BY title, year ORDER BY title ASC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_mediainfo_check_exist( $title, $season = FALSE, $episode = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'SELECT * FROM mediainfo ';
			$sql .= ' WHERE title LIKE \'' . $dbhandle->escapeString( $title ) . '\'';
			if( $season !== FALSE 
            && is_numeric( $season )
			){
                $sql .= ' AND season = ' . $dbhandle->escapeString( $season ) . ' ';
            }
            if( $episode !== FALSE 
            && is_numeric( $episode )
            ){
                $sql .= ' AND episode = ' . $dbhandle->escapeString( $episode ) . ' ';
            }
			$sql .= ' ORDER BY idmediainfo ASC';
			//var_dump( $sql );die();
			$result2 = sqlite_getarray( $dbhandle->query( $sql ) );
			if( is_array( $result2 ) 
			&& count( $result2 ) > 0
			&& is_array( $result2[ 0 ] )
			&& array_key_exists( 'idmediainfo', $result2[ 0 ] )
			){
                $result = $result2[ 0 ][ 'idmediainfo' ];
			}
			sqlite_db_close();
		}
		
		return $result;
	}
	
	//PLAYED ELEMENTS
	//idmedia, user, date, now, max
	
	function sqlite_played_insert( $idmedia, $time, $totaltime ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'INSERT INTO played VALUES(';
			$sql .= '';
			$sql .= ' ' . $idmedia . ', ';
			$sql .= ' "' . $dbhandle->escapeString( USERNAME ) . '", ';
			$sql .= ' "' . date( 'Y-m-d H:i:s' ) . '", ';
			$sql .= ' ' . $time . ', ';
			$sql .= ' ' . $totaltime . ' ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_played_update( $idmedia, $time, $totaltime ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'UPDATE played SET ';
			$sql .= '';
			$sql .= ' user = "' . $dbhandle->escapeString( USERNAME ) . '", ';
			$sql .= ' idmedia = "' . $idmedia . '", ';
			$sql .= ' date = "' . date( 'Y-m-d H:i:s' ) . '", ';
			$sql .= ' now = ' . $time . ', ';
			$sql .= ' max = ' . $totaltime . ' ';
			$sql .= ' WHERE user LIKE "' . $dbhandle->escapeString( USERNAME ) . '" ';
			$sql .= ' AND idmedia = ' . $idmedia . ' ';
			//die( $sql );
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_played_delete( $idmedia, $user = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'DELETE FROM played ';
			$sql .= '';
			if( $user == FALSE ){
                $sql .= ' WHERE user LIKE "' . $dbhandle->escapeString( USERNAME ) . '" ';
            }else{
                $sql .= ' WHERE user LIKE "' . $dbhandle->escapeString( $user ) . '" ';
            }
			$sql .= ' AND idmedia = ' . $idmedia . ' ';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_played_getdata( $idmedia = FALSE, $search = '', $force_user = TRUE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM played ';
			if( $force_user ){
				$sql .= ' WHERE user LIKE "' . $dbhandle->escapeString( USERNAME ) . '" ';
			}else{
				$sql .= ' WHERE user LIKE "%" ';
			}
			if( $idmedia > 0 ){
				$sql .= ' AND idmedia = ' . $idmedia . ' ';
			}
			if( strlen( $search ) > 0 ){
                $sql .= 'AND (';
				$sql .= ' user LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
				$sql .= ')';
			}
			$sql .= ' ORDER BY date DESC';
			$sql .= ' LIMIT 1000';
			//var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_played_getdata_ext( $idmedia = FALSE, $search = '', $force_user = TRUE, $quantity = 1000, $grouped = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM played ';
			$sql .= ' INNER JOIN media ON played.idmedia = media.idmedia ';
			$sql .= ' INNER JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
			if( $force_user ){
				$sql .= ' WHERE user LIKE "' . $dbhandle->escapeString( USERNAME ) . '" ';
			}else{
				$sql .= ' WHERE user LIKE "%" ';
			}
			if( $idmedia > 0 ){
				$sql .= ' AND idmedia = ' . $idmedia . ' ';
			}
			if( strlen( $search ) > 0 ){
                $sql .= 'AND (';
				$sql .= ' user LIKE "%' . $dbhandle->escapeString( $search ) . '%" ';
				$sql .= ')';
			}
			if( $grouped )$sql .= ' GROUP BY mediainfo.title ';
			$sql .= ' ORDER BY date DESC';
			$sql .= ' LIMIT ' . $quantity;
			//var_dump( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_played_replace( $idmedia, $time, $totaltime ){
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'REPLACE INTO played VALUES(';
			$sql .= '';
			$sql .= ' ' . $idmedia . ', ';
			$sql .= ' "' . $dbhandle->escapeString( USERNAME ) . '", ';
			$sql .= ' "' . date( 'Y-m-d H:i:s' ) . '", ';
			$sql .= ' ' . $time . ', ';
			$sql .= ' ' . $totaltime . ' ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
			
		}
		
		return $result;
	}
	
	//$tile = $G_ELEMENT
	//result: FALSE(not played)|$max full played|int seconds
	function sqlite_played_status( $idmedia ){
		$result = FALSE;
		
		//var_dump( $title );
		if( ( $data = sqlite_played_getdata( $idmedia ) ) != FALSE
		&& is_array( $data )
		&& count( $data ) > 0
		&& array_key_exists( 0, $data )
		&& array_key_exists( 'max', $data[ 0 ] )
		&& array_key_exists( 'now', $data[ 0 ] )
		){
			//var_dump( $data[ 0 ] );
			$max = (int)$data[ 0 ][ 'max' ];
			$now = (int)$data[ 0 ][ 'now' ];
			if( $max > 0
			&& $now < ( $max - (int)( ( 10 * $max ) / 100 ) )
			){
				$result = $now;
			}elseif( $max > 0 ){
				$result = $max;
			}
		}else{
			//var_dump( $data );
			$result = FALSE;
		}
		
		return $result;
	}
	
	//MEDIALIVE
	//idmedialive, title, url, poster, date
	
	function sqlite_medialive_getdata( $idmedialive = FALSE, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM medialive ';
			if( $idmedialive != FALSE
			&& is_numeric( $idmedialive )
			&& (int)$idmedialive > 0
			){
				$sql .= ' WHERE idmedialive = ' . $idmedialive . ' ';
			}
			$sql .= ' ORDER BY title ASC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_medialive_getdata_filter( $title, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM medialive ';
			if( strlen( $title ) > 0
			){
				$sql .= ' WHERE title LIKE "%' . $dbhandle->escapeString( $title ) . '%" ';
			}
			$sql .= ' ORDER BY title ASC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_medialive_insert( $idmedialive, $title, $url, $poster ){
		//Vars
		$result = FALSE;
		$idmedialive = 0;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'INSERT INTO medialive VALUES(';
			$sql .= '';
			$sql .= ' NULL, ';
			$sql .= ' "' . $dbhandle->escapeString( $title ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $url ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $poster ) . '", ';
			$sql .= ' "' . date( 'Y-m-d H:i:s' ) . '" ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			if( $result
			&& ( $lastid = sqlite_lastid() ) != FALSE
            ){
                $result = $lastid;
			}
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_medialive_replace( $idmedialive, $title, $url, $poster ){
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'REPLACE INTO medialive VALUES(';
			$sql .= '';
			$sql .= ' ' . $idmedialive . ', ';
			$sql .= ' "' . $dbhandle->escapeString( $title ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $url ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $poster ) . '", ';
			$sql .= ' "' . date( 'Y-m-d H:i:s' ) . '" ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
			
		}
		
		return $result;
	}
	
	function sqlite_medialive_delete( $idmedialive ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'DELETE FROM medialive ';
			$sql .= '';
			$sql .= ' WHERE idmedialive = ' . $idmedialive . ' ';
			//die( $sql );
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		$file = PPATH_MEDIAINFO . DS . $idmedialive . '.livetv';
		if( $result 
		&& file_exists( $file )
		){
            @unlink( $file );
		}
		
		return $result;
	}
	
	function sqlite_medialive_checkexist( $url ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT idmedialive FROM medialive ';
            $sql .= ' WHERE url LIKE "' . $dbhandle->escapeString( $url ) . '" ';
			//$sql .= ' ORDER BY title ASC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			if( is_array( $result ) 
			&& array_key_exists( 0, $result )
			&& is_array( $result[ 0 ] ) 
			&& array_key_exists( 'idmedialive', $result[ 0 ] )
			){
                $result = $result[ 0 ][ 'idmedialive' ];
			}else{
                $result = FALSE;
			}
		}
		
		return $result;
	}
	
	//MEDIALIVEURLS
	//idmedialiveurls, title, url, date
	
	function sqlite_medialiveurls_getdata( $idmedialiveurls = FALSE, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM medialiveurls ';
			if( $idmedialiveurls != FALSE
			&& is_numeric( $idmedialiveurls )
			&& (int)$idmedialiveurls > 0
			){
				$sql .= ' WHERE idmedialiveurls = ' . $idmedialiveurls . ' ';
			}
			$sql .= ' ORDER BY title ASC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_medialiveurls_getdata_filter( $title, $limit = 1000 ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM medialiveurls ';
			if( strlen( $title ) > 0
			){
				$sql .= ' WHERE title LIKE "%' . $dbhandle->escapeString( $title ) . '%" ';
			}
			$sql .= ' ORDER BY title ASC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_medialiveurls_insert( $idmedialiveurls, $title, $url ){
		//Vars
		$result = FALSE;
		$idmedialiveurls = 0;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'INSERT INTO medialiveurls VALUES(';
			$sql .= '';
			$sql .= ' NULL, ';
			$sql .= ' "' . $dbhandle->escapeString( $title ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $url ) . '", ';
			$sql .= ' "' . date( 'Y-m-d H:i:s' ) . '" ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_medialiveurls_replace( $idmedialiveurls, $title, $url ){
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'REPLACE INTO medialiveurls VALUES(';
			$sql .= '';
			$sql .= ' ' . $idmedialiveurls . ', ';
			$sql .= ' "' . $dbhandle->escapeString( $title ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $url ) . '", ';
			$sql .= ' "' . date( 'Y-m-d H:i:s' ) . '" ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
			
		}
		
		return $result;
	}
	
	function sqlite_medialiveurls_delete( $idmedialiveurls ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'DELETE FROM medialiveurls ';
			$sql .= '';
			$sql .= ' WHERE idmedialiveurls = ' . $idmedialiveurls . ' ';
			//die( $sql );
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_medialiveurls_checkexist( $url ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT idmedialiveurls FROM medialiveurls ';
            $sql .= ' WHERE url LIKE "' . $dbhandle->escapeString( $url ) . '" ';
			//$sql .= ' ORDER BY title ASC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			if( is_array( $result ) 
			&& array_key_exists( 0, $result )
			&& is_array( $result[ 0 ] ) 
			&& array_key_exists( 'idmedialiveurls', $result[ 0 ] )
			){
                $result = $result[ 0 ][ 'idmedialiveurls' ];
			}else{
                $result = FALSE;
			}
		}
		
		return $result;
	}
	
	//PLAYING NOW
	//idplaying, user, idmedia, date, mode, pid
	
	function sqlite_playing_getdata( $idplaying = FALSE, $limit = 1000, $extdata = FALSE ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT * FROM playing ';
			if( $idplaying != FALSE
			&& is_numeric( $idplaying )
			&& (int)$idplaying > 0
			){
				$sql .= ' WHERE idplaying = ' . $idplaying . ' ';
			}
			if( $extdata ){
                $sql .= ' LEFT JOIN media ON media.idmedia = playing.idmedia';
                $sql .= ' LEFT JOIN mediainfo ON media.idmediainfo = mediainfo.idmediainfo ';
            }
			$sql .= ' ORDER BY date DESC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_playing_insert( $user, $idmedia, $date = FALSE, $mode = 'WEB', $pid = FALSE ){
		//Vars
		$result = FALSE;
		$idmedialiveurls = 0;
		
		if( $date == FALSE ){
            $date = date( 'Y-m-d H:i:s' );
		}
		if( $pid == FALSE ){
            $pid = getmypid();
		}
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'INSERT INTO playing VALUES(';
			$sql .= '';
			$sql .= ' NULL, ';
			$sql .= ' "' . $dbhandle->escapeString( $user ) . '", ';
			$sql .= ' ' . $idmedia . ', ';
			$sql .= ' "' . $date . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $mode ) . '", ';
			$sql .= ' "' . $dbhandle->escapeString( $pid ) . '" ';
			$sql .= ')';
			$result = $dbhandle->exec( $sql );
			if( $result
			&& ( $lastid = sqlite_lastid() ) 
            ){
                $result = $lastid;
			}
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_playing_delete( $idplaying ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			$sql = 'DELETE FROM playing ';
			$sql .= '';
			$sql .= ' WHERE idplaying = ' . $idplaying . ' ';
			//die( $sql );
			$result = $dbhandle->exec( $sql );
			sqlite_db_close();
		}
		
		return $result;
	}
	
	function sqlite_playing_checkexist( $idplaying ){
		//Vars
		$result = FALSE;
		
		if( ( $dbhandle = sqlite_init() ) != FALSE ){
			
			$sql = 'SELECT idplaying FROM playing ';
            $sql .= ' WHERE idplaying = ' . $idplaying . ' ';
			//$sql .= ' ORDER BY title ASC LIMIT ' . $limit;
			//die( $sql );
			$result = sqlite_getarray( $dbhandle->query( $sql ) );
			sqlite_db_close();
			if( is_array( $result ) 
			&& array_key_exists( 0, $result )
			&& is_array( $result[ 0 ] ) 
			&& array_key_exists( 'idplaying', $result[ 0 ] )
			){
                $result = $result[ 0 ][ 'idplaying' ];
			}else{
                $result = FALSE;
			}
		}
		
		return $result;
	}
	
	function sqlite_playing_clean(){
		//Vars
		$result = TRUE;
		
		if( ( $data = sqlite_playing_getdata() ) != FALSE 
		&& is_array( $data )
		&& count( $data ) > 0
		){
            foreach( $data AS $row ){
                if( array_key_exists( 'pid', $row )
                && strlen( $row[ 'pid' ] ) > 0 
                ){
                    if( posix_getsid( $row[ 'pid' ] ) === FALSE ){
                        sqlite_playing_delete( $row[ 'idplaying' ] );
                    }
                }elseif( array_key_exists( 'date', $row )
                && strlen( $row[ 'date' ] ) > 0 
                && strtotime( $row[ 'date' ] ) !== FALSE
                && strtotime( 'NOW -200 minutes' ) > strtotime( $row[ 'date' ] )
                ){
                    sqlite_playing_delete( $row[ 'idplaying' ] );
                }
            }
		}
		
        return $result;
    }
	
?>
