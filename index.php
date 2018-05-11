<?php
	
	define( 'ACCESS', TRUE );
	
    //DEBUG
    error_reporting( E_ALL );
    ini_set( 'display_errors', '0' );
	
	//FOLDERS
	
	define( 'PPATH_BASE', dirname( __FILE__ ) );
	define( 'DS', DIRECTORY_SEPARATOR );
	define( 'PPATH_ACTIONS', PPATH_BASE . DS . 'actions'  );
	define( 'PPATH_CORE', PPATH_BASE . DS . 'core'  );
	define( 'PPATH_CACHE', PPATH_BASE . DS . 'cache'  );
	define( 'PPATH_TEMP', PPATH_CACHE . DS . 'temp'  );
	define( 'PPATH_LANG', PPATH_BASE . DS . 'lang'  );
	define( 'PPATH_MEDIAINFO', PPATH_CACHE . DS . 'mediadata' );//file: idmedia.type (poster, landscape, nfo, etc)
	define( 'PPATH_IMGS', PPATH_BASE . DS . 'imgs' );
	
	//CORE BASE
	require( PPATH_CORE . DS . 'functions.php' );
	
	//CONFIG
	require( PPATH_BASE . DS . 'config.php' );
	
	//CORE EXT
	require( PPATH_CORE . DS . 'functions.bd.php' );
	require( PPATH_CORE . DS . 'functions.media.php' );
	require( PPATH_CORE . DS . 'functions.scrap.php' );
	require( PPATH_CORE . DS . 'functions.webscrap.php' );
	require( PPATH_CORE . DS . 'functions.html.php' );
	require( PPATH_CORE . DS . 'functions.ffmpeg.php' );
	require( PPATH_CORE . DS . 'functions.cron.php' );
	
	//SCRAPPERS
	if( is_array( O_SCRAPPERS_INCLUDES )
	){
        foreach( O_SCRAPPERS_INCLUDES AS $wscrapper ){
            $ws_file = PPATH_CORE . DS . 'functions.' . $wscrapper . '.php';
            if( file_exists( $ws_file ) ) require( $ws_file );
        }
	}
	
	//BAN SYSTEM
	if( checkBannedIP( USER_IP ) 
	|| check_ip_country( USER_IP ) == FALSE
	){
		header("HTTP/1.1 401 Unauthorized");
		echo "HTTP/1.1 401 Unauthorized";
		exit();
	}
	
	header( 'Content-Type: text/html; charset=UTF-8' );
	header('Cache-control: max-age='.(60*60*24*365));
	header('Expires: '.gmdate(DATE_RFC1123,time()+60*60*24*365));
	
	//cookie & session timeout
	$SESS_MAX_TIME = 3600 * 24 * 365; //1 year
    ini_set( 'session.gc_maxlifetime', $SESS_MAX_TIME );
    session_set_cookie_params( $SESS_MAX_TIME );
    
    //INIT DATA GET POST
    $G_DATA = init_get_post();
    
	//USER CONTROL
	//DEFINE USERNAME && USERNAMEADMIN
	require( PPATH_ACTIONS . DS . 'logincheck.php' );
	
	if( check_user_admin()
	){
		//log_debug_add( 'index 132 check admin' );
		error_reporting( E_ALL );
		ini_set( 'display_errors', '1' );
		sqlite_db_update();
	}else{
		error_reporting( 0 );
		ini_set( 'display_errors', 0 );
	}
	
	//ACTION CONTROL AJAX
	$actionfile = PPATH_ACTIONS . DS . $G_DATA[ 'action' ] . '.php';
	if( check_user() 
	&& array_key_exists( 'r', $G_DATA ) 
	){
        if( file_exists( $actionfile ) ){
            require( $actionfile );
        }
		exit( 0 );
	}
	
	//TEMPLATE
?>

<?php defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' ); ?>

<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html lang='<?php echo O_LANG; ?>' xml:lang='<?php echo O_LANG; ?>' xmlns='http://www.w3.org/1999/xhtml'>
<head>
	
	<title><?php echo APPNAME; ?> <?php echo APPVERSION; ?></title>

	<base href="">
	
	<meta charset="utf-8">
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<meta http-equiv='X-UA-Compatible' content='IE=8' />
	<meta http-equiv='Pragma' content='no-cache' />
	<meta http-equiv='Expires' content='-1' />
	
	<meta name='Keywords' content='' />
	<meta name='Description' content='' />
	<meta name='robots' content='nofollow' />
	<meta name='author' content='<?php echo AUTHOR; ?>' />
	
	<script src="js/jq/jquery-3.2.1.min.js"></script>
	<link type='image/x-icon' href='favicon.ico' rel='shortcut icon' />
	
	<link rel="stylesheet" href="index.css" />
	
<?php
    if( check_user() ){
?>
    
	<!-- LazyLoad -->
	<script src="js/ll/jquery.lazy.min.js"></script>
	
	<link rel="stylesheet" href="index_extra.css" />
	
<script type="text/javascript">
$(function () {
    //CLOSE INFO
    $( '.boxInfoOverlay' ).on( 'click', function(e){
        if($(e.target).is('a')){
            //e.preventDefault();
            return;
        }else if($(e.target).is('img')){
            //e.preventDefault();
            return;
        }else{
            list_info_hide();
            list_show();
        }
    });
    //IMG LAZYLOAD
    $("img.lazy").Lazy();
    //float header size
    $( 'body' ).css("padding-top", $( '.menuBox' ).height() + "px");
});

//MSGBOX

function show_msg( url, data, idelement ){
	data = typeof data !== 'undefined' ? data : {};
	idelement = typeof idelement !== 'undefined' ? idelement : '';
	//var url = "";
    loading_show();
	$.post( url, data )
	.done( function( data ){
        loading_hide();
		//$( "#" + idelement ).html( data );
		msgbox( data );
	});
	
	return false;
}

function show_msgbox( url, data ){
	data = typeof data !== 'undefined' ? data : {};
	//var url = "";
    loading_show();
	$.post( url, data )
	.done( function( data2 ){
        loading_hide();
		msgbox( data2 );
	});
	
	return false;
}

function msgbox( data, fadeofftime ){
	data = typeof data !== 'undefined' ? data : '';
	fadeofftime = typeof fadeofftime !== 'undefined' ? fadeofftime : 5000;
	if( data.length > 0 ){
		$( '#msgboxMsg' ).html( data );
		$( '#msgbox' ).fadeIn( 'fast' ).delay( fadeofftime ).fadeOut( 'fast' );
	}
}

//SHOW INFO

function list_show_info( element, idmediainfo ){
    var url = '<?php echo getURLBase(); ?>?r=r&action=mediainfo';
    url += '&idmediainfo=' + idmediainfo;
    loading_show();
    list_hide();
    $( '.boxInfoOverlay' ).html( '<?php echo get_msg( 'DEF_LOADING', FALSE ); ?>' );
    $.get( url )
    .done( function( data ){
        loading_hide();
        list_info_show();
        $( '.boxInfoOverlay' ).html( data );
    });
    
    return false;
}
var scrollposition = false;
function list_show(){
    if( $( '.dBaseBox' ).is(':hidden') ){
        $( '.dBaseBox' ).fadeIn( 'fast' );
        $(document).scrollTop( scrollposition );
    }
}
function list_hide(){
    if( $( '.dBaseBox' ).is(':visible') ){
        scrollposition = $(document).scrollTop();
        $( '.dBaseBox' ).fadeOut( 'fast' );
    }
}

function list_info_show(){
    if( $( '.boxInfoOverlay' ).is(':hidden') ){
        $( '.boxInfoOverlay' ).fadeIn( 'fast' );
    }
}
function list_info_hide(){	
    if( $( '.boxInfoOverlay' ).is(':visible') ){
        $( '.boxInfoOverlay' ).fadeOut( 'fast' );
    }
}

//LOADING

function loading_show(){
    $( '.boxLoadingOverlay' ).fadeIn( 'fast' );
}
function loading_hide(){
    $( '.boxLoadingOverlay' ).fadeOut( 'fast' );
}

//SCROL TOP

function scrolltop(){
    $( 'html, body' ).animate({scrollTop : 0}, 500 );
}

function scrollbottom(){
    $( 'html, body' ).animate({scrollTop: $(document).height()}, 500 );
}

//NEXT PAGE

function list_show_next( action, page, search ){
    var url = '?action=' + action + '&page=' + page + '&search=' + search;
    window.location.href = url;
}

//GO TO

function goTo( action ){
    var url = '?action=' + action;
    window.location.href = url;
}

function goToURL( url ){
    window.location.href = url;
}

//LOAD URL ON ID

function load_in_id( url, id ){
    loading_show();
    $.get( url )
    .done( function( data ){
        loading_hide();
        $( '#' + id ).html( data );
    });
    
    return false;
}

</script>

<?php
    }//check user
?>

<body>
	<!-- <div class='body-bg'></div> -->
	<div class='boxLoadingOverlay'></div>
	<div class='boxInfoOverlay boxInfoOverlayBg'></div>
	<div class='boxInfoOverlay'></div>
<?php
    
	//ACTIONS
    //var_dump( $G_DATA );
	
	//MENU
	$ACTION_FILE = PPATH_ACTIONS . DS . 'menu.php';
	if( check_user()
	&& file_exists( $ACTION_FILE ) ){
        //var_dump( $G_DATA );
		require( $ACTION_FILE );
	}
?>
<div class='dBaseBox'>
<?php
	
	//ACTION CONTROL
	$ACTION_FILE = PPATH_ACTIONS . DS . $G_DATA[ 'action' ] . '.php';
	if( file_exists( $ACTION_FILE ) ){
        //var_dump( $G_DATA );
        sqlite_log_insert( $G_DATA[ 'action' ], 'actionOK' );
		require( $ACTION_FILE );
	}else{
        sqlite_log_insert( $G_DATA[ 'action' ], 'actionERROR' );
	}
	
?>
</div>

	<div id='msgbox' class='msgbox'>
		<div class="top-msg">
			<div class="top-msg-ico">
				
			</div>
			<div class="top-msg-inner">
				<p id='msgboxMsg'>
					
				</p>
			</div>
			<div class="top-msg-close" onclick='$( "#msgbox" ).fadeOut( "fast" );'>&#10005;</div>
			
		</div>
    </div>

</body>
</html>

<?php
    //CRON
    initializeCron();
?>
