<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//PARAMS
	//search
	//remove
	//quantity
	//size
	//year1
	//year2
	//rating
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	
	if( array_key_exists( 'remove', $G_DATA ) 
	&& $G_DATA[ 'remove' ] == TRUE
	){
        $G_REMOVE = TRUE;
	}else{
        $G_REMOVE = FALSE;
	}
	
	if( array_key_exists( 'size', $G_DATA ) 
	&& is_numeric( $G_DATA[ 'size' ] )
	&& (float)$G_DATA[ 'size' ] > 0
	&& (float)$G_DATA[ 'size' ] < 100
	){
        $CUTSIZE = (float)$G_DATA[ 'size' ] * 1024 * 1024 * 1024;
	}else{
        $CUTSIZE = 3 * 1024 * 1024 * 1024;
	}
	
	if( array_key_exists( 'quantity', $G_DATA ) 
	&& is_numeric( $G_DATA[ 'quantity' ] )
	&& (int)$G_DATA[ 'quantity' ] > 1
	#&& (int)$G_DATA[ 'quantity' ] < 100
	){
        $MAXFILES = (int)$G_DATA[ 'quantity' ];
	}else{
        $MAXFILES = 1;
	}
	
	if( array_key_exists( 'rating', $G_DATA ) ){
        $G_RATING = (float)$G_DATA[ 'rating' ];
	}else{
        $G_RATING = 0;
	}
	
	if( array_key_exists( 'year1', $G_DATA ) ){
        $G_YEAR1 = (int)$G_DATA[ 'year1' ];
	}else{
        $G_YEAR1 = 0;
	}
	
	if( array_key_exists( 'year2', $G_DATA ) ){
        $G_YEAR2 = (int)$G_DATA[ 'year2' ];
	}else{
        $G_YEAR2 = 3000;
	}
	
	//Clean Top size for file search
	echo "<br />MAX FILESIZE: " . formatSizeUnits( $CUTSIZE );
	echo "<br />QUANTITY: " . $MAXFILES;
    echo "<br />MIN RATING: " . $G_RATING;
    echo "<br />MIN YEAR1: " . $G_YEAR1;
    echo "<br />MAX YEAR2: " . $G_YEAR2;
	if( $G_REMOVE ){
        echo "<br />!!!!!!!REMOVE!!!!!!!!!!!: " . $MAXFILES;
    }
    echo "<br />";
	
	if( ( $edata = sqlite_media_getdata_identify( $G_SEARCH, 1000000 ) ) ){
        foreach( $edata AS $lrow ){
            if( ( $mediainfodata =sqlite_mediainfo_getdata( $lrow[ 'idmediainfo' ] ) ) != FALSE 
            && is_array( $mediainfodata )
            && array_key_exists( 0, $mediainfodata )
            && is_array( $mediainfodata[ 0 ] )
            && array_key_exists( 'year', $mediainfodata[ 0 ] )
            && array_key_exists( 'rating', $mediainfodata[ 0 ] )
            && (int)$mediainfodata[ 0 ][ 'year' ] > $G_YEAR1
            && (int)$mediainfodata[ 0 ][ 'year' ] < $G_YEAR2
            && ( 
                (int)$mediainfodata[ 0 ][ 'rating' ] == 0
                || (int)$mediainfodata[ 0 ][ 'rating' ] < $G_RATING
                )
            ){
                $file = $lrow[ 'file' ];
                if( file_exists( $file ) ){
                    $filesize = filesize( $file );
                    if( $filesize > $CUTSIZE ){
                        echo "<br />FILE: " . $file;
                        echo "<br />FILESIZE: " . formatSizeUnits( $filesize ) . "<br />";
                        if( $G_REMOVE ){
                            if( @unlink( $file )
                            ){
                                echo "<br />---- FILE REMOVED: " . $file . "<br />";
                            }else{
                                echo "<br />!!!!! ERROR FILE REMOVED: " . $file . "<br />";
                            }
                        }
                        $MAXFILES--;
                    }else{
                        echo ".";
                    }
                    if( $MAXFILES <= 0 ){
                        break;
                    }
                }
            }
        }
        
        if( $G_REMOVE ){
            //CLEAN DOWNLOAD MEDIA NOT EXIST
            echo "<br />" . date( 'Y-m-d H:i:s' );
            echo "<br />Clean Inexistents Downloads: ";
            echo "<br />";
            media_clean_downloads( 500, TRUE );
        }
	}
	
?>
