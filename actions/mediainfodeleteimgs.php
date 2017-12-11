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
	}else{
        foreach( $G_MEDIADATA AS $k => $v ){
            if( is_string( $v ) ){
                @unlink( PPATH_MEDIAINFO . DS . $G_DATA[ 'idmediainfo' ] . '.' . $k );
            }
        }
		$HTMLDATA = get_msg( 'DEF_DELETED' );
	}
	
	//header("Refresh: 2");
	echo $HTMLDATA;
?>
