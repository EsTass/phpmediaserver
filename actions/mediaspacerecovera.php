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
	//minutes
	//seriesonly
	
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
	
	if( array_key_exists( 'minutes', $G_DATA ) 
	&& (int)$G_DATA[ 'minutes' ] > 0
	){
        $G_MAXTIME = (int)$G_DATA[ 'minutes' ];
	}else{
        $G_MAXTIME = 0;
	}
	
	if( array_key_exists( 'seriesonly', $G_DATA ) 
	&& (int)$G_DATA[ 'seriesonly' ] > 0
	){
        $G_SERIESONLY = TRUE;
	}else{
        $G_SERIESONLY = FALSE;
	}
	
	//Clean Top size for file search
	echo "<br />SEARCH: " . $G_SEARCH;
	echo "<br />MAX FILESIZE: " . formatSizeUnits( $CUTSIZE );
	echo "<br />QUANTITY: " . $MAXFILES;
    echo "<br />MIN RATING: " . $G_RATING;
    echo "<br />MIN YEAR1: " . $G_YEAR1;
    echo "<br />MAX YEAR2: " . $G_YEAR2;
    echo "<br />MIN DURATION: " . $G_MAXTIME;
    if( $G_SERIESONLY ){
        echo "<br />SERIES ONLY ";
    }
	if( $G_REMOVE ){
        echo "<br />!!!!!!!REMOVE!!!!!!!!!!!: " . $MAXFILES;
    }
    echo "<br />";
	
	if( ( $edata = sqlite_media_getdata_identify( $G_SEARCH, 1000000 ) ) ){
        foreach( $edata AS $lrow ){
            $duration = 0;
            if( ( $mediainfodata =sqlite_mediainfo_getdata( $lrow[ 'idmediainfo' ] ) ) != FALSE 
            && is_array( $mediainfodata )
            && array_key_exists( 0, $mediainfodata )
            && is_array( $mediainfodata[ 0 ] )
            && array_key_exists( 'year', $mediainfodata[ 0 ] )
            && array_key_exists( 'rating', $mediainfodata[ 0 ] )
            && (int)$mediainfodata[ 0 ][ 'year' ] > $G_YEAR1
            && (int)$mediainfodata[ 0 ][ 'year' ] < $G_YEAR2
            && ( 
                $G_RATING == 0
                || (
                    (int)$mediainfodata[ 0 ][ 'rating' ] > 0
                    && (int)$mediainfodata[ 0 ][ 'rating' ] < $G_RATING
                    )
                )
            && 
                (
                $G_MAXTIME <= 0
                ||
                    (
                        ( $duration = ffmpeg_file_info_lenght_minutes( $lrow[ 'file' ] ) ) != FALSE
                        && $G_MAXTIME > $duration
                    )
                )
            && (
                    strlen( $G_SEARCH ) == 0
                    || inString( $lrow[ 'file' ], $G_SEARCH )
                )
            && 
                (
                $G_SERIESONLY == FALSE
                || $mediainfodata[ 0 ][ 'season' ] > 0
                )
            ){
                $file = $lrow[ 'file' ];
                if( file_exists( $file ) ){
                    $filesize = filesize( $file );
                    if( $filesize > $CUTSIZE ){
                        echo "<br />FILE: " . $file;
                        echo "<br />FILESIZE: " . formatSizeUnits( $filesize ) . "";
                        echo "<br />YEAR: " . $mediainfodata[ 0 ][ 'year' ];
                        echo "<br />RATING: " . $mediainfodata[ 0 ][ 'rating' ];
                        echo "<br />DURATION: " . $duration;
                        if( $G_REMOVE ){
                            if( @unlink( $file )
                            ){
                                echo "<br />---- FILE REMOVED: " . $file . "<br />";
                            }else{
                                echo "<br />!!!!! ERROR FILE REMOVED: " . $file . "<br />";
                            }
                        }
                        echo "<br />";
                        $MAXFILES--;
                    }else{
                        echo " .";
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
