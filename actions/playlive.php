<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	set_time_limit(0);
	
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
        $mi=$mi[ 0 ];
        //EXTRA VARS
        $title = $mi[ 'title' ];
        $ACTIONINFO = '';
        $TIMEBLOCK = O_VIDEO_TIMEBLOCK;
        $G_TIME = 0;
        
        $bitrate = 1500;
        $audiotrack = 1;
        $subtrack = -1;
        $extra_params = '';
        $extra_params2 = '';
        $dir = $mi[ 'url' ];
        
        $G_MODE = 'webm';
        if( array_key_exists( 'mode', $G_DATA ) 
        && strlen( $G_DATA[ 'mode' ] ) > 0
        ){
            $G_MODE = $G_DATA[ 'mode' ];
        }
        
        $G_QUALITY = 'sd';
        if( array_key_exists( 'quality', $G_DATA ) 
        && $G_DATA[ 'quality' ] == 'hd'
        ){
            $G_QUALITY = 'hd';
        }
        
        //variable bitrate to max especified
        if( $G_QUALITY != 'hd' ){
            $G_FFMPEGLVL = '3.0';
            $minbitrate = O_VIDEO_SD_MINBRATE;
            $maxbitrate = O_VIDEO_SD_MAXBRATE;
            $QUALITY = '-vf scale=-1:' . O_VIDEO_SD_HEIGHT;
            //$encoder = 'libvpx';
        }else{
            $G_FFMPEGLVL = '3.0';
            $minbitrate = O_VIDEO_HD_MINBRATE;
            $maxbitrate = O_VIDEO_HD_MAXBRATE;
            $QUALITY = '-vf scale=-1:' . O_VIDEO_HD_HEIGHT;
            //$encoder = 'libvpx-vp9';
        }
        //$QUALITY = '';
            
        //audio +more vol
        $audiovol = O_VIDEO_EXTRA_VOLUME;
            
        //audio track (change 1 to num video tracks)
        $audiotrack = ' -map 0:0 -map 0:' . ( $audiotrack ) . ' ';
        
        //TEST Extra OPTION on .ts file, loop infinite: -fflags +genpts -stream_loop -1 
        if( endsWith( $dir, '.ts' ) 
        ){
             $extra_params .= ' -fflags +genpts -stream_loop -1 ';
        }else{
            //$extra_params2 .= '';
            //$extra_params .= '';
        }
            
        $subtrack = '';
    
        switch( $G_MODE ){
            //TEST KODI
            case 'direct':
                //slow
                $cmd = "cat " . escapeshellarg( $dir ) . "";
                
                header( 'Content-type: ' . getFileMimeType( $dir ) );
            break;
            //TEST KODI
            case 'fast':
                //fast way to kodi
                $encoder_outformat = 'matroska';
                $encoder = 'libx264'; //fastest ???
                //" . $subtrack . " " . $audiotrack . "
                $cmd = O_FFMPEG . " -cookie '' -referer '' -nostdin " . $extra_params . " -i " . escapeshellarg( $dir ) . " " . $extra_params2 . " -vcodec " . $encoder . " -crf 23 -preset ultrafast -c:a copy -f " . $encoder_outformat . " - ";
                
                header('Content-type: video/matroska');
            break;
            case 'mp4':
                //testing
                //$encoder_outformat = 'mpegts';
                $encoder_outformat = 'mp4';
                //$encoder = 'h264';
                $encoder = 'libx264';
                $AUDIOCODEC = 'aac';
                //$AUDIOCODEC = 'mp3';
                //$AUDIOCODEC = 'opus';
                $cmd = O_FFMPEG . " -cookie '' -referer '' -nostdin -re " . $extra_params . " -i " . escapeshellarg( $dir ) . " " . $extra_params2 . "  " . $subtrack . " " . $audiotrack . " -c:v " . $encoder . " -quality realtime -b:v " . $minbitrate . " -maxrate " . $minbitrate . " -movflags +faststart -bufsize 1000k -g 74 -strict experimental -pix_fmt yuv420p -vf 'scale=trunc(iw/2)*2:trunc(ih/2)*2' -aspect 16:9 -level " . $G_FFMPEGLVL . " -profile:v baseline -level 3.0 -preset ultrafast -tune zerolatency -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -ab 64k -f " . $encoder_outformat . " -movflags frag_keyframe+empty_moov - ";
                
                header('Content-type: video/mp4');
            break;
            case 'webm2':
                //slow
                $AUDIOCODEC = 'libvorbis';
                $encoder_outformat = 'webm';
                $encoder = 'libvpx-vp9'; //webm 9
                //better compatible
                $G_FFMPEGLVL = '4.0';
                $cmd = O_FFMPEG . " -cookie '' -referer '' -nostdin " . $extra_params . " -i " . escapeshellarg( $dir ) . " " . $extra_params2 . "  " . $subtrack . " " . $audiotrack . " -c:v " . $encoder . " -threads 4 -speed 8 -quality realtime -b:v " . $minbitrate . " -maxrate " . $minbitrate . " -bufsize 1000k -pix_fmt yuv420p -vf 'scale=trunc(iw/2)*2:trunc(ih/2)*2' -aspect 16:9 -preset baseline " . $QUALITY . " -level " . $G_FFMPEGLVL . " -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -f " . $encoder_outformat . " - ";
                
                header('Content-type: video/webm');
            break;
            case 'webm':
            default:
                //better option
                $AUDIOCODEC = 'libvorbis';
                $encoder_outformat = 'webm';
                $encoder = 'libvpx';
                $cmd = O_FFMPEG . " -cookie '' -referer '' -nostdin " . $extra_params . " -i " . escapeshellarg( $dir ) . " " . $extra_params2 . "  " . $subtrack . " " . $audiotrack . " -c:v " . $encoder . " -quality realtime -b:v " . $minbitrate . " -maxrate " . $minbitrate . " -bufsize 1000k -pix_fmt yuv420p -vf 'scale=trunc(iw/2)*2:trunc(ih/2)*2' -aspect 16:9 -preset baseline " . $QUALITY . " -level " . $G_FFMPEGLVL . " -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -f " . $encoder_outformat . " - ";
                
                header('Content-type: video/webm');
        }
            
        //headers
        if( function_exists( 'apache_setenv' ) ) @apache_setenv('no-gzip', 1);
        @ini_set('zlib.output_compression', 'Off');
        //header('Content-disposition: inline');
        header('Content-disposition: attachment');
        header("Content-Transfer-Encoding: ­binary");
        
        //force close db
        sqlite_db_close();
        
        //passthru
        if( $_SERVER['REQUEST_METHOD'] != 'HEAD' ){
            passthru( $cmd, $cmdok );
        }
    }
    exit();
?>

