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
        }
        sqlite_played_replace( $IDMEDIA, $playedtimebefore, $time );
        $PLAYERSKIPTIME = 10;
        $urlposter = getURLImg( FALSE, $IDMEDIAINFO, 'poster' );
        $urllogo = getURLImg( FALSE, $IDMEDIAINFO, 'logo' );
        $urllandscape = getURLImg( FALSE, $IDMEDIAINFO, 'landscape' );
        $inforul = getURLInfo( FALSE, $IDMEDIAINFO );
        $nextfileinfo = getURLNextInfo( FALSE, $IDMEDIAINFO );
        
        if( ( $mi = sqlite_mediainfo_getdata( $IDMEDIAINFO, 1 ) ) != FALSE 
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
        }else{
            $title = 'No Title';
        }
        
        $audiolist = array();
        $subslist = array();
        $AUDIOTRACK = 1;
        
        if( ( $videoinfo = ffprobe_get_data( $FMEDIA ) ) != FALSE 
        && is_array( $videoinfo )
        ){
            if( array_key_exists( 'audiotracks', $videoinfo )
            ){
                $audiolist = $videoinfo[ 'audiotracks' ];
                if( defined( 'O_LANG_AUDIO_TRACK' ) 
                && is_array( O_LANG_AUDIO_TRACK )
                ){
                    foreach( $audiolist AS $at ){
                        if( inString( $at, O_LANG_AUDIO_TRACK ) ){
                            $AUDIOTRACK = (int)$at;
                            break;
                        }
                    }
                }
            }
            
            if( array_key_exists( 'subtracks', $videoinfo )
            ){
                $subslist = $videoinfo[ 'subtracks' ];
            }
        }
?>

<script>	
$(function () {
	$( window ).on( 'beforeunload', function(){
		$.ajax({
			url : '?action=playstop&timeplayed=' + playedtotaltime + '&timetotal=<?php echo $time; ?>&idmedia=<?php echo $IDMEDIA; ?>',
			type : 'GET',
			dataType : 'json',
			success : function (result) {
				
			}
		});
		var videoElement = document.getElementById( 'my-player' );
		videoElement.pause();
		$( '#my-player' ).off();
		$( '#my-player source' ).off();
		videoElement.src =""; // empty source
		videoElement.load();
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
		$( '#playerControlPlay' ).html( '&#x25B7;' );
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
		playedtotaltime += playerskiptime;
		//whit errors try playsafe
		playerTimeChanged( playedtotaltime );
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
		$( '#playerControlPlay' ).html( '&#x02502;&#x02502;' );
	});
	//timeupdate
	$( '#my-player' ).on( "timeupdate", function( event ) {
        if( DEBUG ) console.log( 'VIDEO timeupdate: ' + parseInt( playedtotaltime ) + ' - ' + this.duration + ' - ' + this.networkState );
        var total = parseInt( this.currentTime ) + startplayedtotaltime;
        if( total != playedtotaltime ){
            playedtotaltime = total;
		}
		playerTimeBarSelectPlayed( playedtotaltime );
		$( '#playerControlTimeNowData' ).html( secondsTimeSpanToHMS( playedtotaltime ) );
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
	){
		var soundvalue = localStorage.getItem( "soundValue" );
	}else{
		var soundvalue = 0.5;
		localStorage.setItem( "soundValue" , soundvalue );
	}
	$( "#my-player" ).prop( 'volume', soundvalue );
	playerSoundBarSelectPlayed( ( $( "#my-player" ).prop( 'volume' ) ) );
	
	//loading
	loading_show();
});

//CHECK VIDEO

var mousemovetimeout = null;

var DEBUG = false;
var retrytimer = false;
var playedtotaltime = <?php echo $playedtimebefore; ?>;
var startplayedtotaltime = <?php echo $playedtimebefore; ?>;
var playerskiptime = <?php echo $PLAYERSKIPTIME; ?>;
var playererrors = 0;
var playererrors_max = 3;

function checkVideoUsable(){
	if( DEBUG ) console.log( 'video usable: ' + $( "#my-player" ).prop( 'readyState' ) );
	var next = ( parseInt( playedtotaltime ) + playerskiptime );
	if( $( "#my-player" ).prop( 'readyState' ) == 0 ){
		if( DEBUG ) console.log( 'played add time: ' + parseInt( playedtotaltime ) + ' - ' + next );
		playedtotaltime = next;
		playerTimeChanged( next );
	}else if( $( "#my-player" ).prop( 'readyState' ) == 1 ){
		if( DEBUG ) console.log( 'played only meta : ' + parseInt( playedtotaltime ) + ' - ' + next );
		if( retrytimer ){
			playedtotaltime = next;
			playerTimeChanged( next );
		}else{
			retrytimer = true;
		}
	}
	return true;
}

//TIME CHANGE

function playerTimeChanged( seconds, audiotrack, subtrack, quality ){
	audiotrack = typeof audiotrack !== 'undefined' ? audiotrack : audiotracknow;
	subtrack = typeof subtrack !== 'undefined' ? subtrack : subtracknow;
	quality = typeof quality !== 'undefined' ? quality : qualitynow;
	if( DEBUG ) console.log('changeTime ' + $( '#my-player' ).currentTime );
	var url = $( "#my-player source" ).attr( 'src' );
	if( typeof url != 'undefined' ){
		url = url.substring( 0, url.indexOf( '&timeplayed=' ) );
		url += '&timeplayed=' + seconds + '&audiotrack=' + audiotrack + '&subtrack=' + subtrack + '&quality=' + quality;
		playerTimeBarSelectPlayed( seconds );
		if( DEBUG ) console.log( 'attr: ' + $( "#my-player source" ).attr( 'src') );
		playedtotaltime = seconds;
		document.getElementById( 'my-player' ).load();
		$( "#my-player source" ).attr( 'src', url );
		document.getElementById( 'my-player' ).load();
		startplayedtotaltime = seconds;
	}
}

function playerTimeChangedSafe( seconds, audiotrack, subtrack ){
	audiotrack = typeof audiotrack !== 'undefined' ? audiotrack :audiotracknow;
	subtrack = typeof subtrack !== 'undefined' ? subtrack :subtracknow;
	quality = typeof quality !== 'undefined' ? quality :qualitynow;
	var url = $( "#my-player source" ).attr( 'src' );
	if( typeof url != 'undefined' ){
		url = url.substring( 0, url.indexOf( '&timeplayed=' ) );
		url += '&timeplayed=' + seconds + '&audiotrack=' + audiotrack + '&subtrack=' + subtrack + '&quality=' + quality;
		playerTimeBarSelectPlayed( seconds );
		if( DEBUG ) console.log( 'attr: ' + $( "#my-player source" ).attr( 'src') );
		playedtotaltime = seconds;
		document.getElementById( 'my-player' ).load();
		$( "#my-player source" ).attr( 'src', url );
		document.getElementById( 'my-player' ).load();
	}
}

//PLAYER BARS

var lasttimeclassedpayed = 0;
function playerTimeBarSelectPlayed( seconds ){
	if( seconds < lasttimeclassedpayed ){
		$( '.playerControlTimeInfo' ).removeClass( 'playerControlTimeInfoPlayed' );
		for( var x = 0; x <= seconds; x++ ){
			$( '.playerControlTimeInfo' + x ).addClass( 'playerControlTimeInfoPlayed' );
		}
	}else if( seconds > lasttimeclassedpayed ){
		for( var x = lasttimeclassedpayed; x <= seconds; x++ ){
			$( '.playerControlTimeInfo' + x ).addClass( 'playerControlTimeInfoPlayed' );
		}
	}
	lasttimeclassedpayed = seconds;
}

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
	$( "#my-player" ).prop( 'volume', value );
	if( localStorage ){
		localStorage.setItem( "soundValue" , value );
	}
	playerSoundBarSelectPlayed( value );
}

function playerSoundBarSelectPlayed( value ){
	value = value * 10;
	$( '.playerControlSoundInfo' ).removeClass( 'playerControlSoundInfoSel' );
	for( var x = 0; x <= value; x++ ){
		$( '.playerControlSoundInfo' + x ).addClass( 'playerControlSoundInfoSel' );
	}
}

//AUDIO TRACKS

var audiotracknow = <?php echo $AUDIOTRACK; ?>;
function show_audio_tracks(){
	msgbox( $( '#playerControlAudioTrackList' ).html(), 10000 );
}

function setAudioTrack( track ){
	audiotracknow = track;
	playerTimeChanged( playedtotaltime, audiotracknow, subtracknow, qualitynow );
}

//SUBS TRACKS

var subtracknow = -1;
function show_sub_tracks(){
	msgbox( $( '#playerControlSubTrackList' ).html(), 10000 );
}

function setSubTrack( track ){
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
    document.getElementById( 'my-player' ).load();
    $( "#my-player source" ).attr( 'src', url );
    document.getElementById( 'my-player' ).load();
    //send error
    var url = '?action=playervideoerror&idmedia=<?php echo $IDMEDIA; ?>';
    var data = [];
    show_msgbox( url, data );
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
</style>
	
	<div id='playerBoxI' class='playerBoxI'>
		<div id='playerInfo' class='playerInfo'>
			<img class='playerInfoImg' style='max-height: 100px; max-width: 100px;' src='<?php echo $urllogo; ?>' title='<?php echo $title; ?>' />
			<div class='playerInfoTitle'><?php echo $title; ?></div>
			
		</div>
	</div>
	
	<video id="my-player" class="videoplayer"
	width="100%" height="100%"
	preload="auto"
	poster="<?php echo $urllandscape; ?>"
	autoplay
	>
        <source id='my-player-source' src="?r=r&action=playtime&mode=webm&idmedia=<?php echo $IDMEDIA; ?>&timeplayed=<?php echo $playedtimebefore; ?>&audiotrack=<?php echo $AUDIOTRACK; ?>" type="video/webm">
        <source id='my-player-source' src="?r=r&action=playtime&mode=webm2&idmedia=<?php echo $IDMEDIA; ?>&timeplayed=<?php echo $playedtimebefore; ?>&audiotrack=<?php echo $AUDIOTRACK; ?>" type="video/webm">
        <source id='my-player-source' src="?r=r&action=playtime&mode=mp4&idmedia=<?php echo $IDMEDIA; ?>&timeplayed=<?php echo $playedtimebefore; ?>&audiotrack=<?php echo $AUDIOTRACK; ?>" type="video/mp4" data-last='1'>
        Your browser does not support the video tag.
	</video>
	<div id='playerBoxC' class='playerBoxC'>
		<div id='playerControls' class='playerControls'>
			<div id='playerControlPlay' class='playerControlPlay playerControlsOv'>&#x25B7;</div>
			<div id="playerControlTime" class='playerControlTime'>
				<div id="playerBoxControlTime" class='playerBoxControlTime'>
			<?php 
				$MAXPOSITIONELEMENTS = 300;
				for( $x = 0; $x < $MAXPOSITIONELEMENTS; $x++ ){
					$posnow = (int)( ( $x * (int)$time ) / $MAXPOSITIONELEMENTS );
			?>
					<div id="playerControlTimeInfo" class="playerControlTimeInfo playerControlTimeInfo<?php echo $posnow; ?>" 
					title='<?php echo secondsToTimeFormat( $posnow, TRUE ); ?>'
					onclick='javascript:playerTimeChanged( <?php echo $posnow; ?> );return false;'
					>
					</div>
			<?php 
				}
			?>
				</div>
			</div>
			<div id='playerControlSoundIco' class='playerControlSoundIco'>&#x266A;</div>
			<div id="playerControlSound" class='playerControlSound'>
				<div id="playerBoxControlSound" class='playerBoxControlSound'>
			<?php 
				$MAXPOSITIONELEMENTS = 10;
				for( $x = 0; $x <= $MAXPOSITIONELEMENTS; $x++ ){
			?>
					<div id="playerControlSoundInfo" class="playerControlSoundInfo playerControlSoundInfo<?php echo $x; ?>" 
					title='<?php echo( ( $x * 10 ) . '%' ); ?>'
					onclick='javascript:playerSoundChanged( <?php if( $x < 10 ){ echo '0.' . $x; }else{ echo '1'; } ?> );return false;'
					>
					</div>
			<?php 
				}
			?>
				</div>
			</div>
			<div id='playerControlTimeNow' class='playerControlTimeNow'><span id='playerControlTimeNowData'>00:00</span>/<?php echo secondsToTimeFormat( $time, TRUE ); ?></div>
			<div id='playerControlQuality' class='playerControlQuality' title='Quality' onclick='return setQuality();'>SD</div>
			<div id='playerControlAudioTrackIco' class='playerControlAudioTrackIco' title='Audio' onclick='return show_audio_tracks();'>&#x266B;</div>
			<div id='playerControlSubTrackIco' class='playerControlSubTrackIco' title='Subs' onclick='return show_sub_tracks();' ><span>cc</span></div>
			<div id='playerControlFullScreenIco' class='playerControlFullScreenIco' title='Full Screen' onclick="toggleFullScreen();">&#9633;</div>
			<?php if( strlen( $nextfileinfo ) > 0 ){ ?>
			<div class='playerControlNewFile'><a href='<?php echo $nextfileinfo; ?>' title='Next' id='aNextFile'>&rsaquo;</a></div>
			<?php } ?>
			<div class='playerControlHome'><a href='<?php echo $inforul; ?>' title='Back' id='aFileInfo'>H</a></div>
		</div>
	</div>
	
	<div id='playerControlAudioTrackList' class='playerControlAudioTrackList hidden' title='Audio List' >
	<?php
		if( is_array( $audiolist ) ){
	?>
	
		<?php
			//first normaly video
			foreach( $audiolist AS $al ){
                $l = (int)$al;
                if( $AUDIOTRACK == $l ){
                    $atselected = '*';
                }else{
                    $atselected = '';
                }
		?>
			<div class='playerAudioTrack' onclick='setAudioTrack( <?php echo $l; ?> )'><?php echo $atselected . $al; ?></div>
		<?php
                
			}
			
		?>
	<?php
		}else{
			echo "1 - *" . 'Default';
		}
	?>
	</div>
	
	<div id='playerControlSubTrackList' class='playerControlSubTrackList hidden' title='Subs List' >
	<?php
		if( is_array( $subslist ) ){
	?>
	
		<?php
			
			foreach( $subslist AS $al ){
                $l = (int)$al;
		?>
			<div class='playerSubTrack' onclick='setSubTrack( <?php echo $l; ?> )'><?php echo $al; ?></div>
		<?php
                
			}
			
		?>
	<?php
		}else{
			echo "No Subs";
		}
	?>
	</div>
<?php 
    }
?>
