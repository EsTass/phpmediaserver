<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmediainfo
	//user
	if( !array_key_exists( 'idmediainfo', $G_DATA ) 
	|| !is_numeric( $G_DATA[ 'idmediainfo' ] )
	|| $G_DATA[ 'idmediainfo' ] <= 0
	){
		$HTMLDATA = get_msg( 'DEF_NOTEXIST' ) . ' idmediainfo';
	}elseif( sqlite_mediainfo_delete( $G_DATA[ 'idmediainfo' ] ) ){
		$HTMLDATA = get_msg( 'DEF_DELETED' );
	}else{
		$HTMLDATA = get_msg( 'DEF_DELETED_ERROR' );
	}
	
	//header("Refresh: 2");
	echo $HTMLDATA;
?>
