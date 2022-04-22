<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	set_time_limit(0);
	
	//action
	//idmedia= file avi
	//idmediainfo
	//timeplayed = seconds from start
	//timetotal 
	//quality = quality sd|hd
	//audiotrack = audio track list to ffmpeg
	//subtrack = sub track number
	//bitrate = bitrate 1500
	//acodec = audiocodec
	
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
	&& file_exists( $mi[ 0 ][ 'file' ] )
	&& getFileMimeTypeVideo( $mi[ 0 ][ 'file' ] )
	){
        $FMEDIA = $mi[ 0 ][ 'file' ];
        $IDMEDIAINFO = $mi[ 0 ][ 'idmediainfo' ];
	}elseif( $IDMEDIAINFO > 0
	&& ( $mi = sqlite_media_getdata_mediainfo( $IDMEDIAINFO ) ) != FALSE 
	&& is_array( $mi )
	&& count( $mi ) > 0
	&& file_exists( $mi[ 0 ][ 'file' ] )
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
        $title = '';
        $ACTIONINFO = '';
        $filename = $FMEDIA;
        $TIMEBLOCK = O_VIDEO_TIMEBLOCK;
        $G_MODE = 'webm';
        if( array_key_exists( 'mode', $G_DATA ) 
        && strlen( $G_DATA[ 'mode' ] ) > 0
        ){
            $G_MODE = $G_DATA[ 'mode' ];
        }
        $G_TIME = 0;
        if( array_key_exists( 'timeplayed', $G_DATA ) 
        && is_numeric( $G_DATA[ 'timeplayed' ] )
        && (int)$G_DATA[ 'timeplayed' ] > -1
        ){
            $G_TIME = (int)$G_DATA[ 'timeplayed' ];
        }elseif( array_key_exists( 'timeplayed', $G_DATA ) 
        && is_numeric( $G_DATA[ 'timeplayed' ] )
        && (int)$G_DATA[ 'timeplayed' ] == -1
        ){
            $G_TIME = sqlite_played_status( $IDMEDIA );
            $time = ffmpeg_file_info_lenght_seconds( $FMEDIA );
            if( $time > 0 
            && $G_TIME > ( $time - ( $time / 10 ) ) 
            ){
                $G_TIME = 0;
            }
        }
        $G_QUALITY = 'sd';
        if( array_key_exists( 'quality', $G_DATA ) 
        && $G_DATA[ 'quality' ] == 'hd'
        ){
            $G_QUALITY = 'hd';
        }
        $bitrate = 1500;
        if( array_key_exists( 'bitrate', $G_DATA ) 
        && is_numeric( $G_DATA[ 'bitrate' ] )
        ){
            $bitrate = (int)$G_DATA[ 'bitrate' ];
        }
        if( array_key_exists( 'audiotrack', $G_DATA ) 
        && is_numeric( $G_DATA[ 'audiotrack' ] )
        ){
            $audiotrack = (int)$G_DATA[ 'audiotrack' ];
        }else{
            $audiotrack = 1;
        }
        
        if( array_key_exists( 'subtrack', $G_DATA ) 
        && is_numeric( $G_DATA[ 'subtrack' ] )
        ){
            $subtrack = (int)$G_DATA[ 'subtrack' ];
        }else{
            $subtrack = -1;
        }
        
        if( !file_exists( $filename )
        ){
            $ACTIONINFO = get_msg( 'DEF_FILENOTEXIST' );
        }
        
        if( $ACTIONINFO != '' ){
            echo $ACTIONINFO;
        }else{
            
            $dir = $filename;
            
            //pregenerate $segment
            if( $G_TIME > 0 ){
                $extra_params = "-ss " . $G_TIME;
            }else{
                $extra_params = "";
                $G_TIME = 0;
            }
            
            //SET PLAYTIME
            if( !isset( $time ) ){
                $time = ffmpeg_file_info_lenght_seconds( $dir );
            }
            sqlite_played_replace( $IDMEDIA, $G_TIME, $time );
            
            //variable bitrate to max especified
            if( $G_QUALITY != 'hd' ){
                $G_FFMPEGLVL = '3.0';
                $minbitrate = O_VIDEO_SD_MINBRATE;
                $maxbitrate = O_VIDEO_SD_MAXBRATE;
                $QUALITY = '-vf scale=-2:' . O_VIDEO_SD_HEIGHT;
                $VIDEOHEIGHT = O_VIDEO_SD_HEIGHT;
                //$encoder = 'libvpx';
            }else{
                $G_FFMPEGLVL = '3.0';
                $minbitrate = O_VIDEO_HD_MINBRATE;
                $maxbitrate = O_VIDEO_HD_MAXBRATE;
                $QUALITY = '-vf scale=-2:' . O_VIDEO_HD_HEIGHT;
                //$encoder = 'libvpx-vp9';
                $VIDEOHEIGHT = O_VIDEO_HD_HEIGHT;
            }
            //$QUALITY = '';
            
            //audio +more vol
            $audiovol = O_VIDEO_EXTRA_VOLUME;
            
            //audio track (change 1 to num video tracks)
            $audiotrack = ' -map 0:0 -map 0:' . ( $audiotrack ) . ' ';
            
            //subs track (testing)
            if( is_numeric( $subtrack )
            && $subtrack > -1 
            ){
                //TESTING (check if video is resized/scaled with filter_complex)
                $subtrack = ' -filter_complex "[0:v][0:s:' . $subtrack . ']overlay=(main_w-overlay_w)/2:main_h-overlay_h,scale=trunc(iw/2)*2:trunc(ih/2)*2,scale=-2:' . $VIDEOHEIGHT . '"';
                //$subtrack = ' -vf subtitles="' . escapeshellarg( $dir ) . '":si=' . $subtrack . ' ';
                //$subtrack = ' -filter_complex "[0:v][0:s:0]overlay[v]" -map [v] ';
                //$subtrack = ' -vf subtitles=' . escapeshellarg( $dir ) . ' ';
                //$subtrack = ' -vf "[0:0][0:' . $subtrack . ']overlay[0]" -map [0] ';
                //$subtrack = ' -copyts -vf "subtitles=' . escapeshellarg( $dir ) . ',setpts=PTS-STARTPTS" -sn ';
                //$subtrack = '';
                
                //quality and scale in filter_complex
                $QUALITY = '';
                $SCALE = '';
                
                /*
                //with subs overlay cant change -vf size
                $width = round( ( O_VIDEO_SD_HEIGHT * 16 ) / 9 );
                //round to par
                if( ( $width % 2 ) == 1 ) $width++;
                //scale without video filters (need to add video filter in same filter_complex)
                //$SCALE = " -s " . $width . "x" . O_VIDEO_SD_HEIGHT;
                $SCALE = "";
                */
            }else{
                $subtrack = '';
                //basic Scale (no use in hardsubs)
                $SCALE = " -vf 'scale=trunc(iw/2)*2:trunc(ih/2)*2' ";
            }
            
            switch( $G_MODE ){
                //TEST IOS
                case 'mp4ios':
                    //testing
                    //$encoder_outformat = 'mpegts';
                    $encoder_outformat = 'mp4';
                    //$encoder = 'h264';
                    $encoder = 'libx264';
                    $AUDIOCODEC = 'aac';
                    //$AUDIOCODEC = 'mp3';
                    //$AUDIOCODEC = 'opus';
                    //EXTRA
                    $G_FFMPEGLVL = '3.0';
                    $minbitrate = '1M';
                    $maxbitrate = '1M';
                    $QUALITY = '';
                    //$cmd = O_FFMPEG . " -nostdin -re " . $extra_params . " -i " . escapeshellarg( $dir ) . " " . $subtrack . " " . $audiotrack . " -c:v " . $encoder . " -quality realtime -b:v " . $minbitrate . " -maxrate " . $minbitrate . " -movflags +faststart -bufsize 1000k -g 74 -strict experimental -pix_fmt yuv420p " . $SCALE . " -aspect 16:9 -level " . $G_FFMPEGLVL . " -profile:v baseline -level 3.0 -preset ultrafast -tune zerolatency -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -ab 128k -f " . $encoder_outformat . " -movflags frag_keyframe+empty_moov - ";
                    $cmd = O_FFMPEG . " -nostdin -re " . $extra_params . " -i " . escapeshellarg( $dir ) . " " . $subtrack . " " . $audiotrack . '-strict experimental -pix_fmt yuv420p -profile:v baseline -level 3.0 -acodec aac -ar 44100 -ac 2 -ab 128k -f ' . $encoder_outformat . " -movflags frag_keyframe+empty_moov - ";
                    
                    header('Content-type: video/mp4');
                break;
                //TEST KODI
                case 'direct':
                    //slow but 0 cpu
                    header( 'Content-type: ' . getFileMimeType( $dir ) );
                    $cmd = "cat " . escapeshellarg( $dir ) . "";
                    
                break;
                //TEST KODI
                case 'fast':
                    //fast way to kodi
                    $encoder_outformat = 'matroska';
                    $encoder = 'libx264'; //fastest ???
                    //all tracks ???
                    //" . $subtrack . " " . $audiotrack . "
                    $cmd = O_FFMPEG . " -nostdin " . $extra_params . " -i " . escapeshellarg( $dir ) . " -vcodec " . $encoder . " -crf 23 -preset ultrafast -c:a copy -f " . $encoder_outformat . " - ";
                    
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
                    $cmd = O_FFMPEG . " -nostdin -re " . $extra_params . " -i " . escapeshellarg( $dir ) . " " . $subtrack . " " . $audiotrack . " -c:v " . $encoder . " -quality realtime -b:v " . $minbitrate . " -maxrate " . $minbitrate . " -movflags +faststart -bufsize 1000k -g 74 -strict experimental -pix_fmt yuv420p " . $SCALE . " -aspect 16:9 -level " . $G_FFMPEGLVL . " -profile:v baseline -level 3.0 -preset ultrafast -tune zerolatency -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -ab 64k -f " . $encoder_outformat . " -movflags frag_keyframe+empty_moov - ";
                    //die( $cmd );
                    
                    header('Content-type: video/mp4');
                break;
                case 'mp4amdgpu':
                    //testing
                    //$encoder_outformat = 'mpegts';
                    $encoder_outformat = 'mp4';
                    //$encoder = 'h264';
                    //$encoder = 'libx264';
                    //AMDGPU
                    $encoder = 'h264_vaapi';
                    //$encoder = 'hevc_vaapi';
                    $AUDIOCODEC = 'aac';
                    //$AUDIOCODEC = 'mp3';
                    //$AUDIOCODEC = 'opus';
                    //FORCE NO FILTERS SCALE
                    $SCALE = '';
                    //$cmd = O_FFMPEG . " -nostdin -re " . $extra_params . " -hwaccel vaapi -hwaccel_device /dev/dri/renderD128 -hwaccel_output_format vaapi -i " . escapeshellarg( $dir ) . " " . $subtrack . " " . $audiotrack . " -c:v " . $encoder . " -quality realtime -b:v " . $minbitrate . " -maxrate " . $minbitrate . " -movflags +faststart -bufsize 1000k -g 74 -strict experimental -pix_fmt yuv420p " . $SCALE . " -aspect 16:9 -level " . $G_FFMPEGLVL . " -profile:v baseline -level 3.0 -preset ultrafast -tune zerolatency -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -ab 64k -f " . $encoder_outformat . " -movflags frag_keyframe+empty_moov - ";
                    //ONLY IF DECODER IS FULL COMPATIBLE
                    //$cmd = O_FFMPEG . " -nostdin -hwaccel vaapi -hwaccel_device /dev/dri/renderD128 -hwaccel_output_format vaapi -i " . escapeshellarg( $dir ) . " -c:v h264_vaapi -b:v 5M -maxrate 10M -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -ab 64k -f " . $encoder_outformat . " -movflags frag_keyframe+empty_moov - ";
                    //WITH OPTIONAL DECODER COMPATIBLE
                    $cmd = O_FFMPEG . " -nostdin " . $extra_params . " -init_hw_device vaapi=foo:/dev/dri/renderD128 -hwaccel vaapi -hwaccel_output_format vaapi -hwaccel_device foo -i " . escapeshellarg( $dir ) . " -filter_hw_device foo -vf 'format=nv12|vaapi,hwupload' " . $subtrack . " " . $audiotrack . " -c:v " . $encoder . " -b:v 5M -maxrate 10M " . $SCALE . " -aspect 16:9 -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -ab 128k -f " . $encoder_outformat . " -movflags frag_keyframe+empty_moov - ";
                    //die( $cmd );

                    header('Content-type: video/mp4');
                break;
                case 'mp4amdgpurel':
                    //testing, less decode on hard
                    //$encoder_outformat = 'mpegts';
                    $encoder_outformat = 'mp4';
                    //$encoder = 'h264';
                    //$encoder = 'libx264';
                    //AMDGPU
                    $encoder = 'h264_vaapi';
                    //$encoder = 'hevc_vaapi';
                    $AUDIOCODEC = 'aac';
                    //$AUDIOCODEC = 'mp3';
                    //$AUDIOCODEC = 'opus';
                    //FORCE NO FILTERS SCALE
                    $SCALE = '';
                    //WITH OPTIONAL DECODER COMPATIBLE
                    $cmd = O_FFMPEG . " -nostdin " . $extra_params . " -hwaccel vaapi -i " . escapeshellarg( $dir ) . " " . $subtrack . " " . $audiotrack . " -c:v " . $encoder . " -b:v 5M -maxrate 10M " . $SCALE . " -aspect 16:9 -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -ab 128k -f " . $encoder_outformat . " -movflags frag_keyframe+empty_moov - ";
                    //die( $cmd );

                    header('Content-type: video/mp4');
                break;
                case 'mp4nvidia':
                    //testing
                    //$encoder_outformat = 'mpegts';
                    $encoder_outformat = 'mp4';
                    //$encoder = 'h264';
                    //$encoder = 'libx264';
                    //AMDGPU
                    $encoder = 'h264_nvenc';
                    //$encoder = 'hevc_vaapi';
                    $AUDIOCODEC = 'aac';
                    //$AUDIOCODEC = 'mp3';
                    //$AUDIOCODEC = 'opus';
                    //FORCE NO FILTERS SCALE
                    $SCALE = '';
                    $cmd = O_FFMPEG . " -nostdin " . $extra_params . " -hwaccel cuvid -c:v h264_cuvid -i " . escapeshellarg( $dir ) . " " . $subtrack . " " . $audiotrack . " -c:v " . $encoder . " -b:v 5M -maxrate 10M " . $SCALE . " -aspect 16:9 -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -ab 128k -f " . $encoder_outformat . " -movflags frag_keyframe+empty_moov+faststart - ";
                    //die( $cmd );

                    header('Content-type: video/mp4');
                break;
                case 'webm2':
                    //slow
                    $AUDIOCODEC = 'libvorbis';
                    $encoder_outformat = 'webm';
                    $encoder = 'libvpx-vp9'; //webm 9
                    //better compatible
                    $G_FFMPEGLVL = '4.0';
                    //Forced
                    //-hwaccel cuvid -c:v h264_cuvid
                    //Decode on GPU
                    //-hwaccel cuda -hwaccel_output_format cuda
                    $cmd = O_FFMPEG . " -nostdin " . $extra_params . " -hwaccel cuda -i " . escapeshellarg( $dir ) . " " . $subtrack . " " . $audiotrack . " -c:v " . $encoder . " -b:v 5M -maxrate 10M " . $SCALE . " -aspect 16:9 -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -ab 128k -f " . $encoder_outformat . " -movflags frag_keyframe+empty_moov - ";
                    //die( $cmd );
                    
                    header('Content-type: video/webm');
                break;
                case 'webm':
                default:
                    //better option
                    $AUDIOCODEC = 'libvorbis';
                    $encoder_outformat = 'webm';
                    $encoder = 'libvpx';
                    $cmd = O_FFMPEG . " -nostdin " . $extra_params . " -i " . escapeshellarg( $dir ) . " " . $subtrack . " " . $audiotrack . " -c:v " . $encoder . " -quality realtime -b:v " . $minbitrate . " -maxrate " . $minbitrate . " -bufsize 1000k -pix_fmt yuv420p " . $SCALE . " -aspect 16:9 -preset baseline " . $QUALITY . " -level " . $G_FFMPEGLVL . " -af 'volume=" . $audiovol . "' -c:a " . $AUDIOCODEC . " -f " . $encoder_outformat . " - ";
                    //die( $cmd );
                    
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
            //die( $cmd );
            
            //passthru
            if( $_SERVER['REQUEST_METHOD'] != 'HEAD' ){
                
                //SET PLAYING NOW
                if( ( $idplaying = sqlite_playing_insert( USERNAME, $IDMEDIA, FALSE, 'WEB-' . $G_QUALITY . '-' . $G_MODE, getmypid() ) ) != FALSE 
                ){
                    $CHECKPLAYING = TRUE;
                    register_shutdown_function( function( $idplaying ){
                            sqlite_playing_delete( $idplaying );
                        }, 
                    $idplaying );
                }else{
                    $CHECKPLAYING = FALSE;
                    $idplaying = FALSE;
                }
                
                //ACTION
                passthru( $cmd, $cmdok );
                
                //REMOVE PLAYING NOW
                if( $CHECKPLAYING
                && $idplaying
                ){
                    sqlite_playing_delete( $idplaying );
                }
            }
        }
    }
    exit();
?>

