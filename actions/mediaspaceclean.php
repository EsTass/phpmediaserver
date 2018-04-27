<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	
	if( array_key_exists( 'remove', $G_DATA ) ){
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
	
	//Clean Top size for file search
	echo "<br />MAX FILESIZE: " . formatSizeUnits( $CUTSIZE );
	echo "<br />QUANTITY: " . $MAXFILES;
	if( $G_REMOVE ){
        echo "<br />!!!!!!!REMOVE!!!!!!!!!!!: " . $MAXFILES;
    }
    echo "<br />";
	
	if( ( $edata = sqlite_media_getdata_identify( $G_SEARCH, 1000000 ) ) ){
        foreach( $edata AS $lrow ){
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
        
        if( $G_REMOVE ){
            //CLEAN DOWNLOAD MEDIA NOT EXIST
            echo "<br />" . date( 'Y-m-d H:i:s' );
            echo "<br />Clean Inexistents Downloads: ";
            echo "<br />";
            media_clean_downloads( 500, TRUE );
        }
	}
	
?>
