<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//user
	//pass1
	//pass2
	//admin
	if( !array_key_exists( 'user', $G_DATA )
	|| strlen( $G_DATA[ 'user' ] ) < 2
	|| ( $userdata = sqlite_users_getdata( $G_DATA[ 'user' ] ) ) == FALSE
	|| count( $userdata ) == 0
	|| !array_key_exists( 0, $userdata )
	){
		$HTMLDATA = 'User invalid.';
	}elseif( $userdata[ 0 ][ 'username' ] == $G_DATA[ 'user' ]
	&& sqlite_users_update( $userdata[ 0 ][ 'username' ], $userdata[ 0 ][ 'password' ], $userdata[ 0 ][ 'username' ] ) 
	){
		
		//LOG
		sqlite_log_insert( basename( __FILE__ ), 'User admin: ' . $G_DATA[ 'user' ] );
		
		$HTMLDATA = 'User admin: ' . $userdata[ 0 ][ 'username' ];
	}else{
		$HTMLDATA = 'Error';
	}
	
	header("Refresh: 2");
	echo $HTMLDATA;
?>
