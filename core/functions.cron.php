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
    
?>
