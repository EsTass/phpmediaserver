<?php
    
    //CRON FUNCTIONS
    
    function initializeCron(){
        //Result
        $result = FALSE;
        global $G_DATA;
        $cronid = 'cron_hour';
        
        if( O_CRON 
        && array_key_exists( 'cronlaunch', $G_DATA )
        ){
        
            if( !file_exists( PPATH_CRON_FILE ) ){
                file_put_contents( PPATH_CRON_FILE , 'CREATED' );
            }
            if( !file_exists( PPATH_CRON_HOUR_FILE ) ){
                file_put_contents( PPATH_CRON_HOUR_FILE , 'CREATED' );
            }
            
            //O_CRON_LONG_TIME
            $cmd = O_PHP . ' -f "' . PPATH_ACTIONS . DS . 'cronlong.php"';
            $cronid = 'cron';
            if( ( $data = sqlite_log_check_cron( $cronid, ( O_CRON_LONG_TIME ) ) ) != FALSE 
            && count( $data ) > 0
            ){
                
            }elseif( file_exists( PPATH_CRON_FILE )
            && sqlite_log_insert( $cronid, 'Cron ' . O_CRON_LONG_TIME .'mins launched: ' . date( 'Y-m-d H:s:i' ) ) !== FALSE 
            && run_in_background( $cmd, 0, PPATH_CRON_FILE ) 
            ){
                
            }
            
            //O_CRON_SHORT_TIME
            $cmd = O_PHP . ' -f "' . PPATH_ACTIONS . DS . 'cronshort.php"';
            $cronid = 'cron_hour';
            if( ( $data = sqlite_log_check_cron( $cronid, O_CRON_SHORT_TIME ) ) != FALSE 
            && count( $data ) > 0
            ){
                
                
            }elseif( file_exists( PPATH_CRON_HOUR_FILE )
            && sqlite_log_insert( $cronid, 'Cron ' . O_CRON_SHORT_TIME . 'mins launched: ' . date( 'Y-m-d H:s:i' ) ) !== FALSE 
            && run_in_background( $cmd, 0, PPATH_CRON_HOUR_FILE ) 
            ){
                run_in_background( O_CRON_JOB, 0 );
            }
        }elseif( ( $data = sqlite_log_check_cron( $cronid, ( O_CRON_SHORT_TIME + 5 ) ) ) !== FALSE 
        && is_array( $data )
        && count( $data ) <= 0
        && sqlite_log_insert( $cronid, 'Cron ' . O_CRON_LONG_TIME .'mins launched: ' . date( 'Y-m-d H:s:i' ) ) !== FALSE 
        ){
            //first time, time*2
            //O_CRON_SHORT_TIME
            $cmd = O_PHP . ' -f "' . PPATH_ACTIONS . DS . 'cronshort.php"';
            run_in_background( $cmd, 0, PPATH_CRON_HOUR_FILE );
            run_in_background( O_CRON_JOB, 0 );
            
            //O_CRON_LONG_TIME
            $cmd = O_PHP . ' -f "' . PPATH_ACTIONS . DS . 'cronlong.php"';
            $cronid = 'cron';
            if( ( $data = sqlite_log_check_cron( $cronid, ( O_CRON_LONG_TIME ) ) ) != FALSE 
            && count( $data ) > 0
            ){
                
            }elseif( file_exists( PPATH_CRON_FILE )
            && sqlite_log_insert( $cronid, 'Cron ' . O_CRON_LONG_TIME .'mins launched: ' . date( 'Y-m-d H:s:i' ) ) !== FALSE 
            && run_in_background( $cmd, 0, PPATH_CRON_FILE ) 
            ){
                
            }
        }
        
        return $result;
    }
    
    function run_in_background( $Command, $Priority = 0,  $file = '/dev/null')
    {
        if( $Priority ){
            $cmd = "nice -n $Priority $Command > '" . $file . "' 2> /dev/null & echo $!";
        }else{
            $cmd = "$Command > '" . $file . "' 2> /dev/null & echo $!";
        }
        $PID = shell_exec( $cmd );
        
        return $PID;
    }
    
    //clean space X times Y files forced passed, no space check
	function cleanLowDiskSpace( $preview = TRUE, $maxpass = 10, $fileseachpass = 10 ){
        $G_SEARCH = '';
        
        //Autoclean Space on Low
        if( defined( 'O_WEBSCRAP_LIMIT_FREESPACE' ) 
        && defined( 'O_WEBSCRAP_LIMIT_FREESPACE_AUTOCLEAN' )
        && O_WEBSCRAP_LIMIT_FREESPACE_AUTOCLEAN != FALSE
        && ( $freespace = disk_free_space( PPATH_DOWNLOADS ) ) != FALSE
        && $freespace  < ( O_WEBSCRAP_LIMIT_FREESPACE * 1024 * 1024 * 1024 )
        ){
            $G_REMOVE = !$preview; //PREVIEW MODE
            $CLEANSIZE = ( O_WEBSCRAP_LIMIT_FREESPACE_AUTOCLEAN * 1024 * 1024 * 1024 );
            $MAXFILES = $fileseachpass; //delete X each time
            $MAXTIMES = $maxpass;
            while( ( $freespace = disk_free_space( PPATH_DOWNLOADS ) ) != FALSE
            && $freespace  < ( O_WEBSCRAP_LIMIT_FREESPACE * 1024 * 1024 * 1024 ) 
            ){
                echo "<br />(Pass:" . $MAXTIMES . ")LOW DISK FREE SPACE: " . formatSizeUnits( $freespace );
                echo "<br />Clean Size: " . formatSizeUnits( $CLEANSIZE );
                $MAXFILESNOW = $MAXFILES;
                
                if( ( $edata = sqlite_media_getdata_identify( $G_SEARCH, 1000000 ) ) ){
                    foreach( $edata AS $lrow ){
                        $file = $lrow[ 'file' ];
                        if( file_exists( $file ) ){
                            $filesize = filesize( $file );
                            if( $filesize > $CLEANSIZE ){
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
                                $MAXFILESNOW--;
                            }else{
                                //echo " .";
                            }
                            if( $MAXFILESNOW <= 0 ){
                                break;
                            }
                        }
                    }
                }
                
                //New Clean Size (-0.1)
                $CLEANSIZE -= ( 0.1 * 1024 * 1024 * 1024 );
                $MAXTIMES--;
                if( $MAXTIMES <= 0 
                || $CLEANSIZE <= 0
                ){
                    break;
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
	}
	
    //clean space forced to min space needed from old to new files
	function cleanLowDiskSpaceOldFiles( $preview = TRUE, $maxfiles = 25 ){
        $G_SEARCH = '';
        
        //Autoclean Space on Low
        if( defined( 'O_WEBSCRAP_LIMIT_FREESPACE' ) 
        && defined( 'O_WEBSCRAP_LIMIT_FREESPACE_AUTOCLEAN_OLD' )
        && O_WEBSCRAP_LIMIT_FREESPACE_AUTOCLEAN_OLD != FALSE
        ){
            $filenum = 0;
            $LOWSPACE = O_WEBSCRAP_LIMIT_FREESPACE;
            while( $filenum < $maxfiles
            && ( $freespace = disk_free_space( PPATH_DOWNLOADS ) ) != FALSE
            && $freespace  < ( $LOWSPACE * 1024 * 1024 * 1024 )
            ){
                echo "<br />LOW DISK FREE SPACE: " . formatSizeUnits( $freespace );
                if( ( $file = sqlite_media_getdata_old_file( 1 ) ) != FALSE ){
                    //Get last file
                    if( array_key_exists( 0, $file ) ){
                        $file = $file[ 0 ];
                    }
                    if( array_key_exists( 'file', $file ) 
                    && file_exists( $file[ 'file' ] )
                    ){
                        echo "<br />FILE(" . $filenum . '/' . $maxfiles . "): " . $file[ 'file' ];
                        echo "<br />SIZE: " . formatSizeUnits( filesize( $file[ 'file' ] ) );
                        if( $preview ){
                            echo "<br />PREVIEW DELETE: " . $file[ 'file' ];
                        }else{
                            if( @unlink( $file[ 'file' ] ) ){
                                sqlite_media_delete( $file[ 'idmedia' ] );
                                echo "<br />DELETED: " . $file[ 'file' ];
                            }else{
                                echo "<br />ERROR DELETING: " . $file[ 'file' ];
                            }
                        }
                    }else{
                        echo "<br />FILE NOT EXIST: " . $file[ 'file' ];
                    }
                }
                $filenum++;
            }
        }
	}
	
	function cronLiveTV( $cronid = 'cron_7d' ){
        echo "<br />LIVETV Clean: " . $cronid;
        echo "<br />";
        //LiveTV Clean
        if( ( $da = sqlite_medialive_getdata( FALSE, 10000 ) ) 
        && is_array( $da )
        && array_key_exists( 0, $da )
        ){
            $URLs_OK = 0;
            $URLs_DEL = 0;
            $URLs_DEL_E = 0;
            foreach( $da AS $d ){
                if( ( $ldata = ffprobe_get_data( $d[ 'url' ] ) ) != FALSE 
                && is_array( $ldata )
                && array_key_exists( 'width', $ldata )
                && $ldata[ 'width' ] > 0
                ){
                    //echo get_msg( 'DEF_EXIST' );
                    $URLs_OK++;
                }else{
                    if( sqlite_medialive_delete( $d[ 'idmedialive' ] )
                    ){
                        //echo get_msg( 'DEF_DELETED' );
                        $URLs_DEL++;
                    }else{
                        //echo get_msg( 'DEF_DELETED_ERROR' );
                        $URLs_DEL_E++;
                    }
                }
            }
            echo get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ' OKs: ' . $URLs_OK . '/Del:' . $URLs_DEL . '/DelError:' . $URLs_DEL_E;
        }else{
            echo get_msg( 'WEBSCRAP_ADDKO' );
        }
        
        //LiveTVUrls UPDATE
        echo "<br />LIVETVURLs Update: " . $cronid;
        echo "<br />";
        if( ( $da = sqlite_medialiveurls_getdata( FALSE, 10000 ) ) 
        && is_array( $da )
        && array_key_exists( 0, $da )
        ){
            $URLs_OK = 0;
            $URLs_DEL = 0;
            $URLs_DEL_E = 0;
            foreach( $da AS $d ){
                if( ( $ldata = @file_get_contents( $d[ 'url' ] ) ) != FALSE 
                && strlen( $ldata ) > 0
                ){
                    //echo get_msg( 'DEF_EXIST' );
                    //ADD URLS
                    $G_LISTLINKS = $ldata;
                    if( $G_LISTLINKS
                    && strlen( $G_LISTLINKS ) > 0
                    ){
                        $G_LISTLINKS = trim( $G_LISTLINKS );
                        $G_LISTLINKS = explode( PHP_EOL, $G_LISTLINKS );
                        $G_LISTLINKS = array_filter( $G_LISTLINKS, 'trim' );
                        $ltitle = '';
                        $URLs = 0;
                        $URLs_ERROR = 0;
                        $URLs_DUPLY = 0;
                        $LINES = count( $G_LISTLINKS );
                        foreach( $G_LISTLINKS AS $line ){
                            if( filter_var( $line, FILTER_VALIDATE_URL )
                            && sqlite_medialive_checkexist( $line ) != FALSE
                            ){
                                $URLs_DUPLY++;
                            }elseif( filter_var( $line, FILTER_VALIDATE_URL )
                            && sqlite_medialive_checkexist( $line ) == FALSE
                            && ( $ldata = ffprobe_get_data( $line ) ) != FALSE 
                            && is_array( $ldata )
                            && array_key_exists( 'width', $ldata )
                            && $ldata[ 'width' ] > 0
                            ){
                                //+1 url
                                if( strlen( $ltitle ) == 0 ){
                                    $ltitle = 'NO-TITLE';
                                }
                                if( sqlite_medialive_insert( 0, $ltitle, $line, '' ) ){
                                    //echo get_msg( 'DEF_ELEMENTUPDATED' );
                                }else{
                                    //echo get_msg( 'WEBSCRAP_ADDKO' );
                                    $URLs_ERROR++;
                                }
                                $URLs++;
                            }elseif( startsWith( $line, '#EXTINF' ) ){
                                //extract title
                                $tt = explode( ',', $line );
                                if( array_key_exists( 1, $tt ) ){
                                    $ltitle = $tt[ 1 ];
                                }else{
                                    $ltitle = $line;
                                    $ltitle = str_ireplace( '#EXTINF:', '', $ltitle );
                                    $ltitle = trim( $ltitle );
                                }
                            }elseif( filter_var( $line, FILTER_VALIDATE_URL ) ){
                                //no data valid
                                $URLs_ERROR++;
                            }else{
                                //no data valid
                            }
                        }
                        echo '<br />' . get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ' URL: ' . $d[ 'url' ] . ' STATUS ' . $URLs . '/ERRORs:' . $URLs_ERROR . '/DUPLYs:' . $URLs_DUPLY . '/LINES:' . $LINES;
                    }else{
                        echo '<br />' . get_msg( 'WEBSCRAP_ADDKO' ) . ' URL: ' . $d[ 'url' ];
                    }
                    //END ADD
                    $URLs_OK++;
                }else{
                    $URLs_DEL++;
                    echo '<br />' . get_msg( 'WEBSCRAP_ADDKO' ) . ' URL: ' . $d[ 'url' ];
                }
            }
            //echo '<br />' . get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ' OKs: ' . $URLs_OK . '/Del:' . $URLs_DEL . '/DelError:' . $URLs_DEL_E;
        }else{
            echo '<br />' . get_msg( 'WEBSCRAP_ADDKO' );
        }
    }
	
?>
