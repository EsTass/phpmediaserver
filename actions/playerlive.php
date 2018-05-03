<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//admin
	//check_mod_admin();
	
	//action
	//idmedialive=X
	
	if( array_key_exists( 'idmedialive', $G_DATA ) ){
        $IDMEDIALIVE = (int)$G_DATA[ 'idmedialive' ];
	}else{
        $IDMEDIALIVE = 0;
	}
	
	
	if( $IDMEDIALIVE <= 0
	|| ( $mi = sqlite_medialive_getdata( $IDMEDIALIVE ) ) == FALSE 
	|| !is_array( $mi )
	|| !array_key_exists( 0, $mi )
	){
        echo get_msg( 'DEF_NOTEXIST' );
	}else{
        $CODECORDER = array(
            //url ident = header type
            'webm' => 'webm',
            'mp4' => 'mp4',
            'webm2' => 'webm',
        );
        
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

<script>

var mousemovetimeout = null;

$(function () {
    $( window ).on( 'beforeunload', function(){
        
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
    
});
</script>
	<video 
	id="my-player" class="videoplayer"
	width="100%" height="100%"
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
        <source id='my-player-source' src="?r=r&action=playlive&mode=<?php echo $urlident; ?>&idmedialive=<?php echo $IDMEDIALIVE; ?>" type="video/<?php echo $videoheader; ?>" <?php echo $extra_vdata; ?> preload="auto" >
        <?php
                $num++;
            }
        ?>
        Your browser does not support the video tag.
	</video>
	
<?php 
    }
?>
