<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idplaying
	//user
	if( !array_key_exists( 'idplaying', $G_DATA ) 
	|| !is_numeric( $G_DATA[ 'idplaying' ] )
	|| $G_DATA[ 'idplaying' ] <= 0
	){
		$HTMLDATA = get_msg( 'DEF_NOTEXIST' ) . ' idplaying';
	}elseif( !array_key_exists( 'user', $G_DATA ) 
	|| strlen( $G_DATA[ 'user' ] ) < 2
	|| sqlite_users_getdata( $G_DATA[ 'user' ] ) == FALSE
	){
		$HTMLDATA = get_msg( 'DEF_NOTEXIST' ) . ' user';
	}elseif( sqlite_playing_delete( (int)$G_DATA[ 'idplaying' ] ) ){
		$HTMLDATA = get_msg( 'DEF_DELETED' );
	}else{
		$HTMLDATA = get_msg( 'DEF_DELETED_ERROR' );
	}
	
	//header("Refresh: 2");
	echo $HTMLDATA;
?>
