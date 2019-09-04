$(function () {
    $( window ).on( 'beforeunload', function(){
		$.ajax({
			url : '/media-play-settime/?id=' + idmedia + '&timeplayed=' + parseInt( $( '#slideTime' ).val() ) + "&timemax=" + totaltime,
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
		$( "#playerBoxI, #playerBoxC, .menuBoxContainer" ).show();
		$( '#my-player' ).removeClass( 'cursorTransparent' ); 
		$( '#my-player' ).css( 'cursor', 'pointer' ); 
		mousemovetimeout = setTimeout(function() {
			$( "#playerBoxI, #playerBoxC, .menuBoxContainer" ).hide();
			$( '#my-player' ).addClass( 'cursorTransparent' ); 
			$( '#my-player' ).css( 'cursor', 'none' ); 
		}, 2000);
	});
	//buttons
	$( '#playerControlStop' ).click( function(){
        window.history.back();
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
			url : '/media-play-settime/?id=' + idmedia + '&timeplayed=' + parseInt( $( '#slideTime' ).val() ) + "&timemax=" + totaltime,
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
        
        return false;
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
	
    //set total time to bar
    $( "#playerControlTimeTotalData" ).html( secondsTimeSpanToHMS( totaltime ) );
    
	//loading
	loading_show();
});

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

function setAudioTrack( e, track ){
    $( '.playerControlAudioList .playerBoxBarControlsButton' ).removeClass( 'playerBoxBarControlsButtonSelected' );
    $( e ).addClass( 'playerBoxBarControlsButtonSelected' );
	audiotracknow = track;
	playerTimeChanged( playedtotaltime, audiotracknow, subtracknow, qualitynow );
}

//SUBS TRACKS INVIDEO (imagesubs)

function setSubTrack( e, track ){
	$( '.subsTracks' ).removeClass( 'playerBoxBarControlsButtonSelected' );
    $( e ).addClass( 'playerBoxBarControlsButtonSelected' );
	subtracknow = track;
	//quit text subbed
	subtrack = false;
    subtrack_data = false;
    subtrack_lastline = false;
    $( '#subOverlay' ).html( '' );
	playerTimeChanged( playedtotaltime, audiotracknow, subtracknow, qualitynow );
}

//QUALITY

function setQuality(){
    if( DEBUG ) console.log( 'QUALITY NOW: ' + qualitynow );
	if( qualitynow == 'sd' ){
		qualitynow = 'hd';
	}else{
		qualitynow = 'sd';
	}
    if( DEBUG ) console.log( 'NEW URL QUALITY: ' + qualitynow );
	
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
    //var url = '?r=r&action=playervideoerror&idmedia=' + idmedia + '';
    //var data = [];
    //show_msgbox( url, data );
    loading_hide();
}

//SUBS BASIC

var subtrack = false;
var subtrack_data = false;
//datasub = array( 'timestart', 'timeend', 'text' )
function loadSubTrack( e, id ){
    //subsTracks
    $( '.subsTracks' ).removeClass( 'playerBoxBarControlsButtonSelected' );
    $( e ).addClass( 'playerBoxBarControlsButtonSelected' );
    subtrack = id;
    var url = '/media-subs-load/?&id=' + idmedia + '&subtrack=' + id;
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
