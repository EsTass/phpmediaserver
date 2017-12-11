<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//user
	//pass1
	//pass2
	
	if( !array_key_exists( 'user', $G_DATA ) 
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
	}elseif( sqlite_users_update_pass( $G_DATA[ 'user' ], $G_DATA[ 'pass1' ] ) ){
		$HTMLDATA = 'User Updated: ' . $G_DATA[ 'user' ];
	}else{
		$HTMLDATA = 'Error';
	}
	
	//header("Refresh: 2; url=");
	echo $HTMLDATA;
?>
