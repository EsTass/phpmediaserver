<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	//check_mod_admin();
	
	//webscrapper
	//url
	//title
	//pass
	
	if( array_key_exists( 'pass', $G_DATA ) ){
        $PASS = $G_DATA[ 'pass' ];
	}else{
        $PASS = 0;
	}
	
	if( array_key_exists( 'url', $G_DATA ) 
	&& strlen( $G_DATA[ 'url' ] ) > 3
	&& filter_var( $G_DATA[ 'url' ], FILTER_VALIDATE_URL )
	){
        $URL = $G_DATA[ 'url' ];
	}else{
        echo "Invalid URL: ";
        die();
	}
	
	if( array_key_exists( 'webscrapper', $G_DATA ) 
	&& array_key_exists( $G_DATA[ 'webscrapper' ], $G_WEBSCRAPPER )
	){
        $SCRAPPER = $G_DATA[ 'webscrapper' ];
	}else{
        echo "Invalid scrapper: ";
        die();
	}
	
	if( array_key_exists( 'title', $G_DATA ) 
	&& strlen( $G_DATA[ 'title' ] ) > 0
	){
        $TITLE = $G_DATA[ 'title' ];
	}else{
        echo "Invalid Title: ";
        die();
	}
	
	//echo "<br />";
	//echo get_msg( 'WEBSCRAP_ADD_URL', FALSE ) . ': ' . $SCRAPPER . ' => ' . $TITLE . ' => ' . $URL;
	
	if( webscrapp_pass( $SCRAPPER, $PASS, $URL, $TITLE, FALSE ) ){
        echo "<br />";
        //echo get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ': ' . $SCRAPPER . ' => ' . $TITLE . ' => ' . $URL;
        echo get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ': ' . $TITLE;
	}else{
        echo "<br />";
        //echo get_msg( 'WEBSCRAP_ADDKO', FALSE ) . ': ' . $SCRAPPER . ' => ' . $TITLE . ' => ' . $URL;
        echo get_msg( 'WEBSCRAP_ADDKO', FALSE ) . ': ' . $TITLE;
	}
	
?>
