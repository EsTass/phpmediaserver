<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action
	//field
	//search
	//result = json idmedia => title
	
	if( array_key_exists( 'field', $G_DATA ) ){
        $FIELD = $G_DATA[ 'field' ];
	}else{
        $FIELD = 'search';
	}
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $SEARCH = $G_DATA[ 'search' ];
	}else{
        $SEARCH = '';
	}
	
	//search autocomplete
	if( strlen( $SEARCH ) > 2
	&& $FIELD == 'search'
	&& ( $datas = sqlite_media_getdata_filtered( $SEARCH, 20 ) )
	){
        $data = array();
        $inlist = array();
        foreach( $datas AS $row ){
            if( !in_array( $row[ 'title' ], $data ) ){
                $data[ $row[ 'idmedia' ] ] = str_replace( '"', '', $row[ 'title' ] );
                $inlist[] = $row[ 'title' ];
            }
        }
        header( 'Content-Type: application/json; charset=UTF-8' );
        echo json_encode( $data );
    }elseif( strlen( $SEARCH ) > 2
	&& $FIELD == 'atitle'
	&& ( $datas = sqlite_media_getdata_filtered( $SEARCH, 20 ) )
	){
        $data = array();
        $inlist = array();
        foreach( $datas AS $row ){
            if( !in_array( $row[ 'title' ], $data ) ){
                $data[ $row[ 'idmedia' ] ] = str_replace( '"', '', $row[ 'title' ] );
                $inlist[] = $row[ 'title' ] . ' ' . $row[ 'year' ];
            }
        }
        header( 'Content-Type: application/json; charset=UTF-8' );
        echo json_encode( $data );
    }

?>
