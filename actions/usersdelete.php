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
	|| sqlite_users_getdata( $G_DATA[ 'user' ] ) == FALSE
	){
		$HTMLDATA = 'User invalid.';
	}elseif( sqlite_users_delete( $G_DATA[ 'user' ] ) ){
		
		//LOG
		sqlite_log_insert( basename( __FILE__ ), 'User delete: ' . $G_DATA[ 'user' ] );
		
		$HTMLDATA = 'User delete: ' . $G_DATA[ 'user' ];
	}else{
		$HTMLDATA = 'Error';
	}
	
	//header("Refresh: 2");
	echo $HTMLDATA;
?>
