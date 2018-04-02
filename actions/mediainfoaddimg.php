<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	//check_mod_admin();
	
	//action
	//idmediainfo
	//type
	//tfolder
	//tfile
	
	if( array_key_exists( 'idmediainfo', $G_DATA ) 
	&& ( $MIDATA = sqlite_mediainfo_getdata( $G_DATA[ 'idmediainfo' ] ) ) != FALSE 
	&& is_array( $MIDATA )
	&& array_key_exists( 0, $MIDATA )
	){
        $MIDATA = $MIDATA[ 0 ];
        $IDMEDIAINFO = $G_DATA[ 'idmediainfo' ];
	}else{
        echo "Invalid idmediainfo";
        die();
	}
	
	if( ( array_key_exists( 'type', $G_DATA ) 
        && array_key_exists( $G_DATA[ 'type' ], $G_MEDIADATA ) 
        )
	){
        $TYPE = $G_DATA[ 'type' ];
	}else{
        echo "Invalid type";
        die();
	}
	
	if( array_key_exists( 'tfolder', $G_DATA ) 
	&& strlen( $G_DATA[ 'tfolder' ] ) > 0
	&& file_exists( PPATH_TEMP . DS . $G_DATA[ 'tfolder' ] )
	){
        $TFOLDER = $G_DATA[ 'tfolder' ];
	}else{
        echo "Invalid tfolder";
        die();
	}
	
	if( array_key_exists( 'tfile', $G_DATA ) 
	&& strlen( $G_DATA[ 'tfile' ] ) > 0
	&& file_exists( PPATH_TEMP . DS . $G_DATA[ 'tfolder' ] . DS . $G_DATA[ 'tfile' ] )
	){
        $TFILE = $G_DATA[ 'tfile' ];
	}else{
        echo "Invalid tfile";
        die();
	}
	
	$FMEDIA = PPATH_TEMP . DS . $G_DATA[ 'tfolder' ] . DS . $G_DATA[ 'tfile' ];
	$FTARGET = PPATH_MEDIAINFO . DS . $IDMEDIAINFO . '.' . $TYPE;
	if( file_exists( $FTARGET ) ) @unlink( $FTARGET );
	if( getFileMimeTypeImg( $FMEDIA )
	&& ( @link( $FMEDIA, $FTARGET ) || @copy( $FMEDIA, $FTARGET ) )
	){
        if( array_key_exists( 'title', $MIDATA )
        && ( $MIDATA2 = sqlite_mediainfo_search_title( $MIDATA[ 'title' ] ) ) != FALSE 
        && count( $MIDATA2 ) > 0
        ){
            $q = 0;
            foreach( $MIDATA2 AS $row ){
                $FTARGET2 = PPATH_MEDIAINFO . DS . $row[ 'idmediainfo' ] . '.' . $TYPE;
                if( file_exists( $FTARGET2 ) ) @unlink( $FTARGET2 );
                if( ( @link( $FMEDIA, $FTARGET2 ) || @copy( $FMEDIA, $FTARGET2 ) ) ){
                    if( $q < 6 ){
                        echo "<br />" . get_msg( 'DEF_ELEMENTUPDATED' , FALSE ) . $row[ 'title' ] . ' ' . $row[ 'season' ] . 'x' . $row[ 'episode' ];
                    }else{
                        echo " +1";
                    }
                    $q++;
                }
            }   
        }else{
            echo "<br />" . get_msg( 'DEF_ELEMENTUPDATED' , FALSE ) . $MIDATA[ 'title' ];
        }
	}else{
        echo "<br />" . get_msg( 'DEF_COPYKO' , FALSE ) . $TFOLDER . DS . $TFILE;
	}
	
?>
