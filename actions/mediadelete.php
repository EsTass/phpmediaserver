<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmedia
	//user
	if( !array_key_exists( 'idmedia', $G_DATA ) 
	|| !is_numeric( $G_DATA[ 'idmedia' ] )
	|| $G_DATA[ 'idmedia' ] <= 0
	){
		$HTMLDATA = get_msg( 'DEF_NOTEXIST' ) . ' idmedia';
	}elseif( sqlite_media_delete( $G_DATA[ 'idmedia' ] ) ){
		$HTMLDATA = get_msg( 'DEF_DELETED' );
	}else{
		$HTMLDATA = get_msg( 'DEF_DELETED_ERROR' );
	}
	
	//header("Refresh: 2");
	echo $HTMLDATA;
?>
