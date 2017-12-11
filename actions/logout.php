<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//session_start();
    @session_unset();
    @session_destroy();
    @session_write_close();
    @setcookie(session_name(),'',0,'/');
    @session_start();
    @session_regenerate_id(true);
	
	@header( 'Location: ?action=login' );
	
?>
