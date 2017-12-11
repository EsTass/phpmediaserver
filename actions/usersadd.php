<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//user
	//pass1
	//pass2
	//useradmin
	
	if( array_key_exists( 'useradmin', $G_DATA )
	&& strlen( $G_DATA[ 'useradmin' ] ) > 0
	){
		$admin = $G_DATA[ 'user' ];
	}else{
		$admin = '';
	}
	
	if( !array_key_exists( 'admin', $G_DATA )
	){
		$HTMLDATA = 'Admin?.';
	}elseif( !array_key_exists( 'user', $G_DATA ) 
	|| strlen( $G_DATA[ 'user' ] ) < 2
	){
		$HTMLDATA = 'User invalid.';
	}elseif( !array_key_exists( 'pass1', $G_DATA ) 
	|| strlen( $G_DATA[ 'pass1' ] ) < 6
	){
		$HTMLDATA = 'Pass1 invalid.';
	}elseif( !array_key_exists( 'pass2', $G_DATA ) 
	|| strlen( $G_DATA[ 'pass2' ] ) < 6
	){
		$HTMLDATA = 'Pass2 invalid.';
	}elseif( $G_DATA[ 'pass1' ] != $G_DATA[ 'pass2' ]
	){
		$HTMLDATA = 'Pass1 != Pass2';
	}elseif( sqlite_users_insert( $G_DATA[ 'user' ], $G_DATA[ 'pass1' ], $admin ) ){
		
		//LOG
		sqlite_log_insert( basename( __FILE__ ), 'User add: ' . $G_DATA[ 'user' ] );
		
		$HTMLDATA = 'User add: ' . $G_DATA[ 'user' ];
	}else{
		$HTMLDATA = 'Error';
	}
	
	//header("Refresh: 2; url=");
	echo $HTMLDATA;
?>
