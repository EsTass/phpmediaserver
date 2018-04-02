<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
    
    //config
    if( array_key_exists( 'config', $G_DATA ) 
    && strlen( $G_DATA[ 'config' ] ) > 10
    ){
        $configdata = $G_DATA[ 'config' ];
	}else{
        die( 'No config data.' );
	}
	
	$tempfile = PPATH_TEMP . DS . genRandomString( 10 ) . '.php';
	$configfile = PPATH_BASE . DS . 'config.php';
	$configfilebackup = PPATH_BASE . DS . 'config.php.backup';
	if( !is_writable( $configfile ) ){
        echo get_msg( 'CONFIG_NOTWRITABLE' );
	}elseif( file_put_contents( $tempfile, $configdata ) 
	&& ( $msg = CheckSyntax( $tempfile ) ) === TRUE
	){
        echo get_msg( 'CONFIG_FILEOK' );
        @unlink( $configfilebackup );
        if( file_put_contents( $configfilebackup, file_get_contents( $configfile ) ) 
        && file_put_contents( $configfile, $configdata ) 
        ){
            echo get_msg( 'CONFIG_REPLACE_FILEOK' );
        }else{
            echo get_msg( 'CONFIG_REPLACE_FILEKO' );
            if( !file_exists( $configfile )
            && file_exists( $configfilebackup ) 
            && copy( $configfilebackup, $configfile ) 
            ){
                echo get_msg( 'CONFIG_RECOVER_FILEOK' );
            }elseif( file_exists( $configfile ) ){
                echo get_msg( 'CONFIG_VALID' );
            }else{
                echo get_msg( 'CONFIG_NOFILE' );
            }
        }
	}else{
        echo get_msg( 'CONFIG_FILEKO', FALSE ) . $msg;
	}
	
	function CheckSyntax( $fileName ){
        $result = FALSE;
        $output = shell_exec( O_PHP . ' -l "'.$fileName.'"');
       
        $syntaxError = preg_replace("/Errors parsing.*$/", "", $output, -1, $count);
        
        if($count > 0){
            $result = $syntaxError;
        }else{
            $result = TRUE;
        }
        return $result;
    }
?>
