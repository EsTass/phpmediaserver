<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	
	//get_medinfo_images( 1, 'poster', TRUE, TRUE );
	
    //Check extra G_SQLITE_TABLES
    if( ( $dbhandle = sqlite_init() ) != FALSE ){
        echo "<br />SQLUPDATE:" . date( 'Y-m-d H:i:s' );
        foreach( $G_SQLITE_TABLES AS $table => $sqlt ){
            echo "<br />TABLE:" . $table;
            if( !sqlite_checktable_exist( $table ) ){
                echo "<br />NOT EXIST TABLE:" . $table;
                $dbhandle->exec( $sqlt );
            }
			sqlite_db_close();
        }
    }
    
?>
