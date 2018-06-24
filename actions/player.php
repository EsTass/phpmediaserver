<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//admin
	//check_mod_admin();
	
	//action
	//idmedia
	//idmediainfo
	
	$HTMLRESULT = '';
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        $IDMEDIA = '';
	}
	
	if( array_key_exists( 'idmediainfo', $G_DATA ) ){
        $IDMEDIAINFO = $G_DATA[ 'idmediainfo' ];
	}else{
        $IDMEDIAINFO = '';
	}
	
	if( $IDMEDIA > 0
	&& ( $mi = sqlite_media_getdata( $IDMEDIA ) ) != FALSE 
	&& is_array( $mi )
	&& count( $mi ) > 0
	&& @file_exists( $mi[ 0 ][ 'file' ] )
	&& getFileMimeTypeVideo( $mi[ 0 ][ 'file' ] )
	){
        $FMEDIA = $mi[ 0 ][ 'file' ];
        $IDMEDIAINFO = $mi[ 0 ][ 'idmediainfo' ];
	}elseif( $IDMEDIAINFO > 0
	&& ( $mi = sqlite_media_getdata_mediainfo( $IDMEDIAINFO ) ) != FALSE 
	&& is_array( $mi )
	&& count( $mi ) > 0
	&& @file_exists( $mi[ 0 ][ 'file' ] )
	&& getFileMimeTypeVideo( $mi[ 0 ][ 'file' ] )
	){
        $FMEDIA = $mi[ 0 ][ 'file' ];
        $IDMEDIA = $mi[ 0 ][ 'idmedia' ];
	}else{
        $FMEDIA = FALSE;
	}
	
	if( $FMEDIA == FALSE ){
        echo get_msg( 'DEF_NOTEXIST' );
	}elseif( !file_exists( $FMEDIA ) ){
        echo get_msg( 'DEF_FILENOTEXIST' );
	}else{
        //EXTRA VARS
        $time = ffmpeg_file_info_lenght_seconds( $FMEDIA );
        if( ( $playedtimebefore = sqlite_played_status( $IDMEDIA ) ) <= 0 
        || !is_int( $playedtimebefore )
        ){
            $playedtimebefore = 0;
        }else{
            if( $playedtimebefore > ( $time - ( $time / 10 ) ) ){
                $playedtimebefore = 0;
            }
        }
        sqlite_played_replace( $IDMEDIA, $playedtimebefore, $time );
        $PLAYERSKIPTIME = 10;
        $urlposter = getURLImg( FALSE, $IDMEDIAINFO, 'poster' );
        $urllogo = getURLImg( FALSE, $IDMEDIAINFO, 'logo' );
        $urllandscape = getURLImg( FALSE, $IDMEDIAINFO, 'landscape' );
        $inforul = getURLInfo( FALSE, $IDMEDIAINFO );
        $nextfileinfo = getURLNextInfo( FALSE, $IDMEDIAINFO );
        $backfileinfo = getURLBackInfo( FALSE, $IDMEDIAINFO );
        
        if( $IDMEDIAINFO > 0
        && ( $mi = sqlite_mediainfo_getdata( $IDMEDIAINFO, 1 ) ) != FALSE 
        && is_array( $mi )
        && array_key_exists( 0, $mi )
        && is_array( $mi[ 0 ] )
        && array_key_exists( 'title', $mi[ 0 ] )
        ){
            $title = $mi[ 0 ][ 'title' ];
            if( array_key_exists( 'season', $mi[ 0 ] )
            && array_key_exists( 'episode', $mi[ 0 ] ) 
            && is_numeric( $mi[ 0 ][ 'season' ] )
            && $mi[ 0 ][ 'season' ] > -1
            ){
                $title .= ' ' . $mi[ 0 ][ 'season' ] . 'x' . sprintf( '%02d' , $mi[ 0 ][ 'episode' ] );
            }
            if( array_key_exists( 'titleepisode', $mi[ 0 ] )
            && strlen( $mi[ 0 ][ 'titleepisode' ] ) > 0
            ){
                $title .= ' ' . $mi[ 0 ][ 'titleepisode' ];
            }
            $year = $mi[ 0 ][ 'year' ];
            $rating = $mi[ 0 ][ 'rating' ];
        }else{
            $title = 'No Title';
            $year = '';
            $rating = '';
        }
        
        $audiolist = array();
        $subslist = array();
        $AUDIOTRACK = 1;
        $CODECORDER = array(
            //url ident = header type
            'webm' => 'webm',
            'mp4' => 'mp4',
            'webm2' => 'webm',
        );
        
        if( ( $videoinfo = ffprobe_get_data( $FMEDIA, FALSE ) ) != FALSE 
        && is_array( $videoinfo )
        ){
            if( array_key_exists( 'audiotracks', $videoinfo )
            ){
                $audiolist = $videoinfo[ 'audiotracks' ];
                if( defined( 'O_LANG_AUDIO_TRACK' ) 
                && is_array( O_LANG_AUDIO_TRACK )
                ){
                    $num = 1;
                    foreach( $audiolist AS $at ){
                        if( inString( $at, O_LANG_AUDIO_TRACK ) ){
                            $AUDIOTRACK = (int)$num;
                            break;
                        }
                        $num++;
                    }
                }
            }
            
            if( array_key_exists( 'subtracks', $videoinfo )
            ){
                $subslist = $videoinfo[ 'subtracks' ];
            }
            
            if( array_key_exists( 'codec', $videoinfo )
            && strlen( $videoinfo[ 'codec' ] ) > 0 
            ){
                //ORDER BY CODEC
                /*
                if( stripos( $videoinfo[ 'codec' ], 'mpeg' ) !== FALSE
                || stripos( $videoinfo[ 'codec' ], '264' ) !== FALSE
                ){
                    $CODECORDER = array(
                        //url ident = header type
                        'mp4' => 'mp4',
                        'webm' => 'webm',
                        'webm2' => 'webm',
                    );
                }
                */
            }
            
        }
?>

<script>	
$(function () {
	$( window ).on( 'beforeunload', function(){
		$.ajax({
			url : '?action=playstop&timeplayed=' + parseInt( $( '#slideTime' ).val() ) + '&timetotal=<?php echo $time; ?>&idmedia=<?php echo $IDMEDIA; ?>',
			type : 'GET',
			dataType : 'json',
			success : function (result) {
				
			}
		});
		var videoElement = document.getElementById( 'my-player' );
		if( videoElement ){
            videoElement.pause();
            $( '#my-player' ).off();
            $( '#my-player source' ).off();
            videoElement.src =""; // empty source
            videoElement.load();
        }
	});
	
	//player
	$( '#my-player' ).click( function(){
		if( this.paused ){
			this.play();
		}else{
			this.pause();
		}
	});
	//mouse move
	$(document).on('mousemove', function() {
		clearTimeout(mousemovetimeout);
		$( "#playerBoxI, #playerBoxC, .menuBox" ).show();
		$( '#my-player' ).removeClass( 'cursorTransparent' ); 
		$( '#my-player' ).css( 'cursor', 'pointer' ); 
		mousemovetimeout = setTimeout(function() {
			$( "#playerBoxI, #playerBoxC, .menuBox" ).hide();
			$( '#my-player' ).addClass( 'cursorTransparent' ); 
			$( '#my-player' ).css( 'cursor', 'none' ); 
		}, 2000);
	});
	//buttons
	$( '#playerControlStop' ).click( function(){
        goToURL( '<?php echo $inforul; ?>' );
	});
	//playerControlPlayBack
	$( '#playerControlPlayBack' ).click( function(){
        var seconds = parseInt( $( '#slideTime' ).val() ) - parseInt( playerskiptime );
        if( seconds < 0 ){
            seconds = 0;
        }
        playerTimeChanged( seconds );
	});
	//dSlider
	$( '.dSlider' ).click( function(e) {
        var posX = e.pageX - parseInt( $(this).position().left );
        var posY = e.pageY - parseInt( $(this).position().top );
        var size = parseInt( $(this).width() );
        var pos = parseInt( ( ( 100 * posX ) / size ) );
        var nowtime = parseInt( ( totaltime / 100 ) * pos );
        $( '.dSliderInner' ).css( 'width', pos + '%');
        if( DEBUG ) console.log( 'CHANGED TIME: ' + nowtime );
        playerTimeChanged( nowtime );
        //alert( posX + ' , ' + posY + ' - ' + size + ' - ' + pos + '%' + ' - ' + nowtime + '%' );
    });
    $( ".dSlider" ).mousemove( function(e){
        var posX = e.pageX - parseInt( $(this).position().left );
        var posY = e.pageY - parseInt( $(this).position().top );
        var size = parseInt( $(this).width() );
        var pos = parseInt( ( ( 100 * posX ) / size ) );
        var nowtime = parseInt( ( totaltime / 100 ) * pos );
        $( '.dSlider' ).prop( 'title', secondsTimeSpanToHMS( nowtime ) + ' (' + pos + '%)');
        if( DEBUG ) console.log( 'MOUSE CHANGED TIME: ' + nowtime );
    });
	//playerControlPause
	$( '#playerControlPause' ).click( function(){
        document.getElementById( 'my-player' ).pause();
	});
	//playerControlPlay
	$( '#playerControlPlay' ).click( function(){
        document.getElementById( 'my-player' ).play();
	});
	//playerControlPlayFor
	$( '#playerControlPlayFor' ).click( function(){
        var seconds = parseInt( $( '#slideTime' ).val() ) + parseInt( playerskiptime );
        playerTimeChanged( seconds );
	});
	
	//playerBoxBarControlsButton
	$( '.basecontrols .playerBoxBarControlsButton' ).click( function(){
        $( '.basecontrols .playerBoxBarControlsButton' ).removeClass( 'playerBoxBarControlsButtonSelected' );
		$( this ).addClass( 'playerBoxBarControlsButtonSelected' );
	});
	
	//video events SOURCES
	
	//error source
	$( '#my-player source' ).on( "error", function( event ) {
		if( DEBUG ) console.log( 'VIDEO SOURCE0 error: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState + ' S ' + $( this ).attr( 'data-last' ) );
		if( $( this ).attr( 'data-last' ) != '1' ){
			$( this ).remove();
		}
		playererrors++;
		if( playererrors > playererrors_max ){
            send_video_error();
		}else{
            playedtotaltime += playerskiptime;
            //whit errors try playsafe
            playerTimeChanged( playedtotaltime );
        }
	});
	//error source 1
	$( '#my-player source[1]' ).on( "error", function( event ) {
		if( DEBUG ) console.log( 'VIDEO SOURCE1 error: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState + ' S ' + $( this ).attr( 'data-last' ) );
		if( $( this ).attr( 'data-last' ) != '1' ){
			$( this ).remove();
		}
		playererrors++;
		if( playererrors > playererrors_max ){
            send_video_error();
		}else{
            playedtotaltime += playerskiptime;
            //whit errors try playsafe
            playerTimeChanged( playedtotaltime );
        }
	});
	//error source 2
	$( '#my-player source[2]' ).on( "error", function( event ) {
		if( DEBUG ) console.log( 'VIDEO SOURCE1 error: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState + ' S ' + $( this ).attr( 'data-last' ) );
		if( $( this ).attr( 'data-last' ) != '1' ){
			$( this ).remove();
		}
		playererrors++;
		if( playererrors > playererrors_max ){
            send_video_error();
		}else{
            playedtotaltime += playerskiptime;
            //whit errors try playsafe
            playerTimeChanged( playedtotaltime );
        }
	});
	//play
	$( '#my-player' ).on( "play", function( event ) {
		if( DEBUG ) console.log( 'VIDEO play: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
		$( '.basecontrols .playerBoxBarControlsButton' ).removeClass( 'playerBoxBarControlsButtonSelected' );
		$( '#playerControlPlay' ).addClass( 'playerBoxBarControlsButtonSelected' );
	});
	//playing
	$( '#my-player source' ).on( "playing", function( event ) {
		if( DEBUG ) console.log( 'VIDEO playing: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
	});
	//abort
	$( '#my-player source' ).on( "abort", function( event ) {
		if( DEBUG ) console.log( 'VIDEO abort: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
	});
	//stalled
	$( '#my-player source' ).on( "stalled", function( event ) {
		if( DEBUG ) console.log( 'VIDEO stalled: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
		playererrors++;
		if( playererrors > playererrors_max ){
            send_video_error();
		}else{
            playedtotaltime += playerskiptime;
            //whit errors try playsafe
            playerTimeChanged( playedtotaltime );
        }
	});
	//suspend
	$( '#my-player source' ).on( "suspend", function( event ) {
		if( DEBUG ) console.log( 'VIDEO suspend: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
	});
	//emtied
	$( '#my-player source' ).on( "emptied", function( event ) {
		if( DEBUG ) console.log( 'VIDEO emptied: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
	});
	
	//video events VIDEO
	
	//pause
	$( '#my-player' ).on( "pause", function( event ) {
		if( DEBUG ) console.log( 'VIDEO pause: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
		$( '.basecontrols .playerBoxBarControlsButton' ).removeClass( 'playerBoxBarControlsButtonSelected' );
		$( '.playerControlPause' ).addClass( 'playerBoxBarControlsButtonSelected' );
	});
	//timeupdate
	$( '#my-player' ).on( "timeupdate", function( event ) {
        var total = parseInt( this.currentTime ) + parseInt( playedtotaltime );
        if( DEBUG ) console.log( 'VIDEO timeupdate: ' + parseInt( total ) + ' - ' + this.duration + ' - ' + this.currentTime + ' - ' + parseFloat( playedtotaltime ) );
		$( '#slideTime' ).val( total );
		$( '#slideTime' ).attr( 'title', secondsTimeSpanToHMS( total ) );
		slideUpdate( total );
		$( '.playerControlTimeNowData' ).html( secondsTimeSpanToHMS( total ) );
		//SUBS CONTROL
		if( subtrack !== false 
		&& subtrack_data != false
		){
            //SUBS TIMER
            var total2 = parseFloat( this.currentTime ) + parseFloat( playedtotaltime );
            show_subs_timed( parseFloat( total2 ) );
		}
	});
	//error video
	$( '#my-player' ).on( "error", function( event ) {
		if( DEBUG ) console.log( 'VIDEO error: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState + ' S ' + $( this ).attr( 'data-last' ) );
		playerTimeChanged( playedtotaltime );
	});
	//durationchange
	$( '#my-player' ).on( "durationchange", function( event ) {
		if( DEBUG ) console.log( 'VIDEO durationchange: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
	});
	//ended
	$( '#my-player' ).on( "ended", function( event ) {
		if( DEBUG ) console.log( 'VIDEO ended: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
		$.ajax({
			url : '?action=playstop&timeplayed=<?php echo $time; ?>&timetotal=<?php echo $time; ?>&idmedia=<?php echo $IDMEDIA; ?>',
			type : 'GET',
			dataType : 'json',
			success : function (result) {
			}
		});
		if( $( '#aNextFile' ).length
		){
			location.replace( $( '#aNextFile' ).attr( 'href' ) );
		}else{
			location.replace( $( '#aFileInfo' ).attr( 'href' ) );
		}
	});
	//waiting
	$( '#my-player' ).on( "waiting", function( event ) {
		if( DEBUG ) console.log( 'VIDEO waiting: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
	});
	//canplay
	$( '#my-player' ).on( "canplay", function( event ) {
		if( DEBUG ) console.log( 'VIDEO canplay: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
		loading_hide();
	});
	//loadeddata
	$( '#my-player' ).on( "loadeddata", function( event ) {
		if( DEBUG ) console.log( 'VIDEO loadeddata: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
		loading_hide();
	});
	//loadstart
	$( '#my-player' ).on( "loadstart", function( event ) {
		if( DEBUG ) console.log( 'VIDEO loadstart: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
		loading_show();
	});
	//progress
	$( '#my-player' ).on( "progress", function( event ) {
		if( DEBUG ) console.log( 'VIDEO progress: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
	});
	//boxInfoOverlay
	
	//Quality
	$( '#playerControlQuality' ).click( function(){
		if( $( this ).hasClass( 'playerControlQualityHD' ) ){
			$( this ).removeClass( 'playerControlQualityHD' );
			$( this ).attr( 'title', 'Quality SD' );
			$( this ).html( 'SD' );
		}else{
			$( this ).addClass( 'playerControlQualityHD' );
			$( this ).attr( 'title', 'Quality HD' );
			$( this ).html( 'HD' );
		}
		setQuality();
	});
	
	//sound init
	if( localStorage
	&& ( 'soundValue' in localStorage )
	&& parseInt( localStorage.getItem( "soundValue" ) ) >= 1
	&& parseInt( localStorage.getItem( "soundValue" ) ) <= 100
	){
        var soundvalue = localStorage.getItem( "soundValue" );
	}else{
		var soundvalue = 50;
		localStorage.setItem( "soundValue" , soundvalue );
	}
    $( "#my-player" ).prop( 'volume', ( soundvalue / 100 ) );
	$( "#slideVolume" ).val( soundvalue );
	
	//loading
	loading_show();
});

//CHECK VIDEO

var mousemovetimeout = null;

var DEBUG = false;
var retrytimer = false;
var playedtotaltime = <?php echo $playedtimebefore; ?>;
var playererrors = 0;
var playererrors_max = 3;
var playerskiptime = <?php echo $PLAYERSKIPTIME; ?>;
var totaltime = parseInt( '<?php echo $time; ?>' );

//SLIDE UPDATE
function slideUpdate( nowtime ){
    var pos = parseInt( ( 100 * nowtime ) / totaltime );
    $( '.dSliderInner' ).css( 'width', pos + '%');
}

//TIME CHANGE

function playerTimeChanged( seconds, audiotrack, subtrack, quality ){
	audiotrack = typeof audiotrack !== 'undefined' ? audiotrack : audiotracknow;
	subtrack = typeof subtrack !== 'undefined' ? subtrack : subtracknow;
	quality = typeof quality !== 'undefined' ? quality : qualitynow;
	if( DEBUG ) console.log('changeTime ' + $( '#my-player' ).currentTime );
	playedtotaltime = seconds;
	var url = $( "#my-player source" ).attr( 'src' );
	if( typeof url != 'undefined' ){
		url = url.substring( 0, url.indexOf( '&timeplayed=' ) );
		url += '&timeplayed=' + seconds + '&audiotrack=' + audiotrack + '&subtrack=' + subtrack + '&quality=' + quality;
		//playerTimeBarSelectPlayed( seconds );
		if( DEBUG ) console.log( 'attr: ' + $( "#my-player source" ).attr( 'src') );
		document.getElementById( 'my-player' ).load();
		$( "#my-player source" ).attr( 'src', url );
		document.getElementById( 'my-player' ).load();
		//reset subs line
		subtrack_lastline = 0;
	}
}

//PLAYER BARS

function secondsTimeSpanToHMS(seconds) {

    var sec_num = parseInt(seconds, 10);
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    
    var result = '';
    if( hours > 0 ){
		result = hours+':'+minutes+':'+seconds;
    }else{
		result = minutes+':'+seconds;
    }
    
    return result;
}

//SOUND

function playerSoundChanged( value ){
    var value2 = value / 100;
	$( "#my-player" ).prop( 'volume', value2 );
	if( localStorage ){
		localStorage.setItem( "soundValue" , value );
	}
}

//AUDIO TRACKS

var audiotracknow = <?php echo $AUDIOTRACK; ?>;
function setAudioTrack( e, track ){
    $( '.playerControlAudioList .playerBoxBarControlsButton' ).removeClass( 'playerBoxBarControlsButtonSelected' );
    $( e ).addClass( 'playerBoxBarControlsButtonSelected' );
	audiotracknow = track;
	playerTimeChanged( playedtotaltime, audiotracknow, subtracknow, qualitynow );
}

//SUBS TRACKS INVIDEO

var subtracknow = -1;
function setSubTrack( e, track ){
	$( '.playerControlSubsList .playerBoxBarControlsButton' ).removeClass( 'playerBoxBarControlsButtonSelected' );
    $( e ).addClass( 'playerBoxBarControlsButtonSelected' );
	subtracknow = track;
	playerTimeChanged( playedtotaltime, audiotracknow, subtracknow, qualitynow );
}

//QUALITY

var qualitynow = 'sd';
function setQuality(){
	if( qualitynow == 'sd' ){
		qualitynow = 'hd';
	}else{
		qualitynow = 'sd';
	}
	
	playerTimeChanged( playedtotaltime, audiotracknow, subtracknow, qualitynow );
}

//FULLSCREEN

function toggleFullScreen() {
  if (!document.fullscreenElement &&    // alternative standard method
      !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement ) {  // current working methods
    if (document.documentElement.requestFullscreen) {
      document.documentElement.requestFullscreen();
    } else if (document.documentElement.msRequestFullscreen) {
      document.documentElement.msRequestFullscreen();
    } else if (document.documentElement.mozRequestFullScreen) {
      document.documentElement.mozRequestFullScreen();
    } else if (document.documentElement.webkitRequestFullscreen) {
      document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
    }
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen();
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen();
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  }
}

//VIDEO ERROR

function send_video_error(){
    //Stop Player and info
    $( "#my-player" ).remove();
    //send error
    var url = '?action=playervideoerror&idmedia=<?php echo $IDMEDIA; ?>';
    var data = [];
    show_msgbox( url, data );
    loading_hide();
}

//SUBS BASIC

var subtrack = false;
var subtrack_data = false;
//datasub = array( 'timestart', 'timeend', 'text' )
function loadSubTrack( e, id ){
    //subsTracks
    $( '.playerControlSubsList .subsTracks' ).removeClass( 'playerBoxBarControlsButtonSelected' );
    $( e ).addClass( 'playerBoxBarControlsButtonSelected' );
    subtrack = id;
    var url = '?r=r&action=playsubs&idmedia=<?php echo $IDMEDIA; ?>&subtrack=' + id;
    $.getJSON( url )
    .done( function( data ){
        if( DEBUG ) console.log( 'SUBS LOAD TRACK: ' + url );
        subtrack_data = data;
    });
}

var subtrack_lastline = 0;
function show_subs_timed( timenow ){
    var added = false;
    if( DEBUG ) console.log( 'SUBS CHECK TEXT: ' + timenow );
    if( DEBUG ) console.log( 'SUBS CHECK KEY: ' + subtrack_lastline );
    $.each( subtrack_data, function( key, data ){
        if( subtrack_lastline <= parseInt( key )
        && timenow >= parseFloat( data[ 'timestart' ] )
        && timenow <= parseFloat( data[ 'timeend' ] )
        ){
            subtrack_lastline = parseInt( key );
            if( data[ 'text' ] != $( '#subOverlay' ).html() ){
                $( '#subOverlay' ).html( data[ 'text' ] );
            }
            added = true;
            return false;
        }
    });
    if( added == false ){
        $( '#subOverlay' ).html( '' );
    }
}

</script>

<style type='text/css'>
html, body
{
    width: 100% !important;
    height: 100% !important;
    margin: 0px !important;
    padding: 0px !important;
    border: 0px !important;
    overflow: hidden;
}
.dBaseBox{
    width: 100% !important;
    height: 100% !important;
    margin: 0px !important;
    padding: 0px !important;
    border: 0px !important;
    background-color: black !important;
}

/* SUBS */

.subOverlay{
    text-align: center;
    font-size: 4em;
    color: yellow;
    text-shadow: -1px 0 white, 0 1px white, 1px 0 white, 0 -1px white;
    width: 100%;
    min-width: 100%;
    max-width: 100%;
    height: auto;
    position: fixed;
    bottom: 5%;
    left: 0px;
    z-index: 1001;
    background-color: transparent;
    padding: 2px;
}

</style>
	
	<video id="my-player" class="videoplayer"
	width="100%" height="100%"
	preload="auto"
	poster="<?php echo $urllandscape; ?>"
	autoplay
	>
        <?php
            $num = 1;
            $vtotal = count( $CODECORDER );
            $session = '&PHPSESSION=' . session_id();
            foreach( $CODECORDER AS $urlident => $videoheader ){
                if( $num == $vtotal ){
                    $extra_vdata = " data-last='1'";
                }else{
                    $extra_vdata = "";
                }
        ?>
        <source id='my-player-source' src="?r=r&action=playtime&mode=<?php echo $urlident; ?>&idmedia=<?php echo $IDMEDIA; ?>&timeplayed=<?php echo $playedtimebefore; ?>&audiotrack=<?php echo $AUDIOTRACK; ?><?php echo $session; ?>" type="video/<?php echo $videoheader; ?>" <?php echo $extra_vdata; ?> preload="auto" >
        <?php
                $num++;
            }
        ?>
        Your browser does not support the video tag.
	</video>
	
    <div id="subOverlay" class="subOverlay">
        
    </div>
	
	<div id='playerBoxC' class='playerBoxControls'>
        <div class='playerBoxBarInfo'>
            <img class='playerInfoImg' src='<?php echo $urllogo; ?>' title='<?php echo $title; ?>' />
        </div>
        <div class='playerBoxBarControls'>
            <div class='playerBoxBarControlsTitle'>
                <span><?php echo $title; ?> <?php echo $year; ?> &#x2605;<?php echo $rating; ?></span>
            </div>
            <div class='playerBoxBarControlsTimeBar'>
                <div class='tRow'>
                    <div class='tbTimer'>
                        <span class='playerControlTimeNowData'>00:00</span>/<?php echo secondsToTimeFormat( $time, TRUE ); ?>
                    </div>
                    <div class='tbSlider'>
                        <input class='hidden playerBoxBarControlsTimeBarSlide slider' id="slideTime" type="range" min="0" max="<?php echo $time; ?>" step="1" value="<?php echo $playedtimebefore; ?>" onchange="playerTimeChanged( this.value ); return false;" />
                        <div class='dSlider'>
                            <div class='dSliderInner'>
                                <span class='playerControlTimeNowData'>00:00</span>&nbsp;
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr />
            <div class='playerBoxBarControlsActions basecontrols'>
                <div class='tRow'>
                    <?php if( strlen( $backfileinfo ) > 0 ){ ?>
                    <div class='playerBoxBarControlsButton'><a href='<?php echo $backfileinfo; ?>' title='Next' id='aBackFile'>&#x23ee;</a></div>
                    <?php } ?>
                    <div id='playerControlPlayBack' class='playerBoxBarControlsButton'>&#9194;</div>
                    <div id='playerControlPause' class='playerBoxBarControlsButton'>&#10073;&#10073;</div>
                    <div id='playerControlPlay' class='playerBoxBarControlsButton'>&#x25B7;</div>
                    <div id='playerControlStop' class='playerBoxBarControlsButton'>&#9724;</div>
                    <div id='playerControlPlayFor' class='playerBoxBarControlsButton'>&#9193;</div>
                    <?php if( strlen( $nextfileinfo ) > 0 ){ ?>
                    <div class='playerBoxBarControlsButton'><a href='<?php echo $nextfileinfo; ?>' title='Next' id='aNextFile'>&#x23ed;</a></div>
                    <?php } ?>
                    <div id='playerControlQuality' class='playerBoxBarControlsButton' title='Quality' onclick='return setQuality();'>SD</div>
                    <div id='playerControlFullScreenIco' class='playerBoxBarControlsButton' title='Full Screen' onclick="toggleFullScreen();">&#9633;</div>
                    <div id='playerControlVolume' class='playerBoxBarControlsButton' title='Volume'>
                        &#x266A;
                    </div>
                    <div id='playerControlVolume' class='playerBoxBarControlsButton' title='Volume'>
                        <input class='playerBoxBarControlsVolumeSlide slider' id="slideVolume" type="range" min="0" max="100" step="5" value="50" 
                            onchange="playerSoundChanged( this.value ); return false;" />
                    </div>
                    <div class='playerBoxBarControlsButton videoinfo'><?php echo $videoinfo[ 'width' ]; ?>x<?php echo $videoinfo[ 'height' ]; ?> <?php echo $videoinfo[ 'codec' ]; ?> <?php echo $videoinfo[ 'acodec' ]; ?></div>
                </div>
            </div>
            <hr />
                <?php
                    if( is_array( $audiolist ) 
                    && count( $audiolist ) > 1
                    ){
                ?>
            <div class='playerBoxBarControlsActions playerControlAudioList'>
                <div class='tRow'>
                    <div class='playerBoxBarControlsButton text120'>
                        &#x266B; Audio: 
                    </div>
                            <?php
                                //first normaly video
                                $num = 1;
                                foreach( $audiolist AS $al ){
                                    if( $AUDIOTRACK == $num ){
                                        $atselected = 'playerBoxBarControlsButtonSelected';
                                    }else{
                                        $atselected = '';
                                    }
                            ?>
                    <div class='playerBoxBarControlsButton text120 <?php echo $atselected; ?>' onclick='setAudioTrack( this, <?php echo $num; ?> );'><?php echo $al; ?></div>
                            <?php
                                    $num++;
                                }
                                
                            ?>
                    <?php
                        }
                    ?>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php
                        if( is_array( $subslist ) 
                        && count( $subslist ) > 0
                        ){
                    ?>
                    <div class='playerBoxBarControlsButton text120'>
                        &#x225F; Subs: 
                    </div>
                            <?php
                                foreach( $subslist AS $l => $al ){
                            ?>
                        <div class='playerBoxBarControlsButton text120 subsTracks' onclick='loadSubTrack( this, <?php echo $l; ?> );'><?php echo $al; ?></div>
                            <?php   
                                }
                            ?>
                    </div>
                        <?php
                            }
                        ?>
                </div>
            </div>
        </div>
	</div>
	
<?php 
    }
?>
