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
            //'mp4ios' => 'mp4',
            //'mp4' => 'mp4',
            //'webm' => 'webm',
            //'webm2' => 'webm',
            //'mov' => 'mp4',
            'tsplaylist' => 'x-mpegURL',
        );
        
        if( ( $videoinfo = ffprobe_get_data( $FMEDIA ) ) != FALSE 
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

<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
<script>

var mousemovetimeout = null;

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
    //PLAYER START
    var url = "?r=r&action=playlist&idmedia=<?php echo $IDMEDIA; ?>&audiotrack=<?php echo $AUDIOTRACK; ?>";
    var video = document.getElementById('my-player');
    if(Hls.isSupported()) {
        var hls = new Hls();
        hls.loadSource(url);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED,function() {
            video.play();
        });
    }
    // hls.js is not supported on platforms that do not have Media Source Extensions (MSE) enabled.
    // When the browser has built-in HLS support (check using `canPlayType`), we can provide an HLS manifest (i.e. .m3u8 URL) directly to the video element throught the `src` property.
    // This is using the built-in support of the plain video element, without using hls.js.
    else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = url;
        video.addEventListener('canplay',function() {
            video.play();
        });
    }
});
</script>
	<video 
	id="my-player" class="videoplayer"
	width="100%" height="100%"
	poster="<?php echo $urllandscape; ?>"
	controls="true" 
	autoplay
	>
        <?php
            $num = 1;
            $vtotal = count( $CODECORDER );
            foreach( $CODECORDER AS $urlident => $videoheader ){
                if( $num == $vtotal ){
                    $extra_vdata = " data-last='1'";
                }else{
                    $extra_vdata = "";
                }
        ?>
        <source id='mplayer' src="?r=r&action=playlist&idmedia=<?php echo $IDMEDIA; ?>&audiotrack=<?php echo $AUDIOTRACK; ?>" type="video/<?php echo $videoheader; ?>" type="video/<?php echo $videoheader; ?>" <?php echo $extra_vdata; ?> preload="auto" >
        <?php
                $num++;
            }
        ?>
        Your browser does not support the video tag.
	</video>
	
<?php 
    }
?>
