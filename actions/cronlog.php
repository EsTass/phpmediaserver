<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	echo "<div style='margin: auto;float:left;width: 49%;height:90vh;max-height:90vh;overflow:auto;border: 1px solid white;background-color: gray;'>";
    echo "<div>";
	echo "<br />6H CRON";
    //var_dump( $_SERVER );
	if( file_exists( PPATH_CRON_FILE ) ){
        $data = file_get_contents( PPATH_CRON_FILE );
        $data = str_ireplace( PHP_EOL, '<br />', $data );
        $data = str_ireplace( '<br />', '--br--', $data );
        $data = htmlspecialchars( $data );
        $data = str_ireplace( '--br--', '<br />', $data );
        echo $data;
    }else{
        echo "<br />";
        echo get_msg( 'DEF_FILENOTEXIST' );
    }
    echo "</div>";
    echo "</div>";
    
    echo "<div style='margin: auto;float:right;width: 49%;height:90vh;max-height:90vh;overflow:auto;border: 1px solid white;background-color: gray;'>";
    echo "<div>";
	echo "<br />HOUR CRON";
    //var_dump( $_SERVER );
	if( file_exists( PPATH_CRON_HOUR_FILE ) ){
        $data = file_get_contents( PPATH_CRON_HOUR_FILE );
        $data = str_ireplace( PHP_EOL, '<br />', $data );
        $data = str_ireplace( '<br />', '--br--', $data );
        $data = htmlspecialchars( $data );
        $data = str_ireplace( '--br--', '<br />', $data );
        echo $data;
    }else{
        echo "<br />";
        echo get_msg( 'DEF_FILENOTEXIST' );
    }
    echo "</div>";
    echo "</div>";
?>
