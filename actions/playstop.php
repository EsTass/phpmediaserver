<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//idmedia
	//timeplayed = seconds from start
	//timetotal = seconds total
	
	$HTMLRESULT = '';
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        $IDMEDIA = '';
	}
	
	if( $IDMEDIA > 0
	&& ( $mi = sqlite_media_getdata( $IDMEDIA ) ) != FALSE 
	&& is_array( $mi )
	&& count( $mi ) > 0
	){
        $IDMEDIAINFO = $mi[ 0 ][ 'idmediainfo' ];
        $TIME = 0;
        if( array_key_exists( 'timeplayed', $G_DATA ) 
        && is_numeric( $G_DATA[ 'timeplayed' ] )
        ){
            $TIME = (int)$G_DATA[ 'timeplayed' ];
        }
        $TIMETOTAL = 0;
        if( array_key_exists( 'timetotal', $G_DATA ) 
        && is_numeric( $G_DATA[ 'timetotal' ] )
        ){
            $TIMETOTAL = (int)$G_DATA[ 'timetotal' ];
        }
        sqlite_played_update( $IDMEDIA, $TIME, $TIMETOTAL );
        echo get_msg( 'DEF_ELEMENTUPDATED' ) . $TIME;
	}else{
        echo get_msg( 'DEF_NOTEXIST' );
	}
?>

