<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	
	if( ( $logdata = sqlite_log_getdata( $G_SEARCH ) ) 
	&& count( $logdata ) > 0
	){
		//var_dump( $logdata[ 0 ] );die();
		//echo "<br />";
		echo "<table class='tList'>";
		echo "<tr>";
		echo "<th>" . get_msg( 'MENU_DATE', FALSE ) . "</th>";
		echo "<th>" . get_msg( 'MENU_ACTION', FALSE ) . "</th>";
		echo "<th>" . get_msg( 'MENU_USERS', FALSE ) . "</th>";
		echo "<th>" . get_msg( 'MENU_IP', FALSE ) . "</th>";
		echo "<th>" . get_msg( 'MENU_DESCRIPTION', FALSE ) . "</th>";
		echo "<th>" . get_msg( 'MENU_URL', FALSE ) . "</th>";
		echo "<th>" . get_msg( 'MENU_REFERER', FALSE ) . "</th>";
		echo "</tr>";
		$css_extra = '';
		foreach( $logdata AS $lrow ){
			if( array_key_exists( 'user', $lrow )
			&& $lrow[ 'user' ] == 'nouser'
			){
				$css_extra = '';
			}else{
				$css_extra = 'eColors04';
			}
			echo "<tr>";
			foreach( $lrow AS $data ){
				echo "<td class='" . $css_extra . "'>";
				//echo "<span style='width:" . $pc . "%'>" . $data . "</span>";
				echo "" . substr( $data, 0, 100 ) . "";
				echo "</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}else{
        echo get_msg( 'DEF_EMPTYLIST' );
    }
?>
