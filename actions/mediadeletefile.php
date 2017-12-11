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
	}elseif( ( $d = sqlite_media_getdata( $G_DATA[ 'idmedia' ] ) )
	&& is_array( $d )
	&& array_key_exists( 0, $d )
	&& array_key_exists( 'file', $d[ 0 ] )
	&& file_exists( $d[ 0 ][ 'file' ] )
	&& @unlink( $d[ 0 ][ 'file' ] )
	&& sqlite_media_delete( $G_DATA[ 'idmedia' ] ) 
	){
		$HTMLDATA = get_msg( 'DEF_DELETED' );
	}else{
		$HTMLDATA = get_msg( 'DEF_DELETED_ERROR' );
	}
	
	//header("Refresh: 2");
	echo $HTMLDATA;
?>
