<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();

	//for join titles get titles starting with letter get var
	//letter

	$HTML = '';
	if( array_key_exists( 'letter', $G_DATA )
	&& strlen( $G_DATA[ 'letter' ] ) > 0
	){
        $LETTER = $G_DATA[ 'letter' ];
	}else{
        $LETTER = 'A';
	}

	//BASE FILTER LETTERS
	$FILTERLETTERS = array_merge( range( 0, 9 ), range( 'A', 'Z' ) );
    $FILTEREXTRA = 'ANY_OTHER';

	//Filter titles starting with letter and return data
	$MEDIAINFOLIST = array();

    if( $LETTER == $FILTEREXTRA ){
        if( ( $mediainfolist = sqlite_mediainfo_getdata_titles( FALSE, 100000 ) ) != FALSE ){
            foreach( $mediainfolist AS $row ){
                $title = $row[ 'title' ] . ' (' . $row[ 'year' ] . ')';
                if( !in_array( strtoupper( substr( $title, 0, 1 ) ), $FILTERLETTERS ) ){
                    $MEDIAINFOLIST[ $row[ 'idmediainfo' ] ] = $title;
                }
            }
        }
    }else{
        if( ( $mediainfolist = sqlite_mediainfo_getdata_titles( FALSE, 100000 ) ) != FALSE ){
            foreach( $mediainfolist AS $row ){
                $title = $row[ 'title' ] . ' (' . $row[ 'year' ] . ')';
                if( strtoupper( substr( $title, 0, 1 ) ) == $LETTER ){
                    $MEDIAINFOLIST[ $row[ 'idmediainfo' ] ] = $title;
                }
            }
        }
    }

    asort( $MEDIAINFOLIST );
	$HTML = json_encode( $MEDIAINFOLIST );

	echo $HTML;
	
?>
