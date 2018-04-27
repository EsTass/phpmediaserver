<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action TS FILES
	//idmedia
	//idmediainfo
	//cache=cache folder
	//t=file position
	//mode=ts
	//audiotrack = audio track list to ffmpeg
	//subtrack = sub track number
	//TODO
	//audiotrack
	//quality
	
	$HTMLRESULT = '';
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        $IDMEDIA = '';
	}
	
	if( array_key_exists( 'idmediainfo', $G_DATA ) ){
        $IDMEDIAINFO = $G_DATA[ 'idmediainfo' ];
	}else{
        $IDMEDIAINFO = FALSE;
	}
	
	if( array_key_exists( 'cache', $G_DATA ) ){
        $CACHEFOLDER = PPATH_TEMP . DS . $G_DATA[ 'cache' ];
	}else{
        $CACHEFOLDER = FALSE;
	}
	
	if( array_key_exists( 't', $G_DATA ) ){
        $SEGMENT = (int)$G_DATA[ 't' ];
	}else{
        $SEGMENT = -1;
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
	
	//LIST VARS
	$TIMEBLOCK = 10; //10 sec to cache
	$FILEM3U8 = 'index_1500.m3u8';
	$MAXTRYS = 20; //max trys to wait for file
	
	//FUNCTIONS
	
	if( $SEGMENT < 0
	|| $CACHEFOLDER == FALSE
	//|| !file_exists( $CACHEFOLDER ) 
	){
        echo get_msg( 'DEF_FILENOTEXIST' ) . '-1';
	}elseif( $FMEDIA == FALSE ){
        echo get_msg( 'DEF_NOTEXIST' ) . '-2';
	}elseif( !file_exists( $FMEDIA ) ){
        echo get_msg( 'DEF_FILENOTEXIST' ) . '-3';
	}else{
	
		$cache_folder = PPATH_TEMP . DS . $CACHEFOLDER;
		//check file segment exist
		$filesegment = $cache_folder . DS . 'segment' . sprintf( '%05d', $SEGMENT ) . '.ts';
		
        //audio track (change 1 to num video tracks)
        $audiotrack = ' -map 0:0 -map 0:' . ( $audiotrack ) . ' ';
        $G_FFMPEGLVL = '3.0';
        $G_FFMPEGVIDEWIDTH = 480;
        
        //subs track (testing)
        if( $subtrack > -1 
        && is_numeric( $subtrack )
        ){
            //TESTING
            //$subtrack = ' -filter_complex "[0:v][0:s:' . $subtrack . ']overlay" ';
            //$subtrack = ' -vf subtitles="' . escapeshellarg( $dir ) . '":si=' . $subtrack . ' ';
            //$subtrack = ' -filter_complex "[0:v][0:s:0]overlay[v]" -map [v] ';
            //$subtrack = ' -vf subtitles=' . escapeshellarg( $dir ) . ' ';
            //$subtrack = ' -vf "[0:0][0:' . $subtrack . ']overlay[0]" -map [0] ';
            //$subtrack = ' -copyts -vf "subtitles=' . escapeshellarg( $dir ) . ',setpts=PTS-STARTPTS" -sn ';
            $subtrack = '';
        }else{
            $subtrack = '';
        }
        
        //pregenerate $SEGMENT
        $extra_params = " -ss " . ( $TIMEBLOCK * ( $SEGMENT - 0 ) );
        
        //DIRECT MODE
        
		//encoders webm
		//$encoder = 'mpeg4';
		$encoder = 'mpeg2video';
		//$encoder = 'libx264';
		//$encoder = 'h264';
		//$encoder = 'libtheora';
		
		//$encoder_outformat = 'mp4'; //NO SEEKABLE
		//$encoder_outformat = 'm4v'; //NO SEEKABLE
		$encoder_outformat = 'mpegts';
		//$encoder_outformat = 'ogg';
		
		//process
		$process = '0';
		
		//audio codec aac mp3 ac5
		//$audiocodec = 'aac';
		$audiocodec = 'mp2';
		//$audiocodec = 'libfaac';
		//$audiocodec = 'mp3';
		//$audiocodec = 'vorbis';
		
		$cmd = O_FFMPEG . " -nostdin " . $extra_params . " -t " . $TIMEBLOCK . " -i '" . $FMEDIA . "' " . $subtrack . " " . $audiotrack . " -codec:0 " . $encoder . " -codec:1 ac3 -map_metadata -1 -map_chapters -1 -threads 0 -codec:v:0 libx264 -pix_fmt yuv420p -preset ultrafast -crf 23 -maxrate 1498981 -bufsize 2997962 -profile:v baseline -level " . $G_FFMPEGLVL . " -x264opts:0 subme=0:me_range=4:rc_lookahead=10:me=dia:no_chroma_me:8x8dct=0:partitions=none -force_key_frames 'expr:gte(t,n_forced*3)' -vf 'scale=trunc(min(max(iw\,ih*dar)\," . $G_FFMPEGVIDEWIDTH . ")/2)*2:trunc(ow/dar/2)*2' -copyts -vsync -1 -codec:a:0 " . $audiocodec . " -strict experimental -ac 2 -ab 128000 -f " . $encoder_outformat . " - ";
		
		@apache_setenv('no-gzip', 1);
		@ini_set('zlib.output_compression', 'Off');
		//header( 'Accept-Ranges:bytes' );
		//header( 'Connection:Keep-Alive' );
		//header('Content-type: video/mp4');
		//header('Content-type: video/mpeg');
 		header('Content-type: video/MP2T'); //video/MP2T
 		//header( 'Content-Type:video/mp2t' );
		//header('Content-disposition: inline');
		header('Content-disposition: attachment');
		header("Content-Transfer-Encoding: ­binary");
		//header("Content-Length: ".filesize($out));
		
		//var_dump( $cmd );die();
		passthru( $cmd, $cmdok );
		
	}
	
?>
