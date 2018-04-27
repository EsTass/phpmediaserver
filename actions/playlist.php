<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//action TS FILES
	//idmedia= file avi
	//idmediainfo
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
    
	
	//LIST VARS
	$TIMEBLOCK = 10; //10 sec to cache
	$FILEM3U8 = 'index_1500.m3u8';
	
	//FUNCTIONS
	
	//m3u8
	function createFilem3u8( $file, $cache, $timeblock, $listfilename, $IDMEDIA, $audiotrack, $subtrack = -1 ) {
		$result = '#EXTM3U
#EXT-X-VERSION:3
#EXT-X-MEDIA-SEQUENCE:0
#EXT-X-ALLOW-CACHE:YES
#EXT-X-TARGETDURATION:' . $timeblock;
		if( ( $dur = ffmpeg_file_info_lenght_seconds( $file, $cache ) ) != FALSE ){
			for( $x = 0; $x <= ( $dur / $timeblock ); $x++ ){
				$result .= '
#EXTINF:' . $timeblock . '.000000,
?r=1&action=playsegment&idmedia=' . $IDMEDIA . '&cache=' . basename( $cache ) . '&t=' . sprintf( '%05s', $x ) . '&mode=ts&audiotrack=' . $audiotrack;
			}
			$result .= '
#EXT-X-ENDLIST';
			//$result = file_put_contents( $cache . DS . $listfilename, $result );
		}else{
			$result = FALSE;
		}
		
		return $result;
	}
	
	
	if( $FMEDIA == FALSE ){
        echo get_msg( 'DEF_NOTEXIST' );
	}elseif( !file_exists( $FMEDIA ) ){
        echo get_msg( 'DEF_FILENOTEXIST' );
	}else{
		$cache_folder = PPATH_TEMP . DS . getRandomString(12);
		//@mkdir( $cache_folder );
		if( ( $fileinfo = ffmpeg_file_info_lenght_minutes( $FMEDIA ) ) != FALSE ){
			$time = $fileinfo;
		}else{
			$time = $fileinfo;
		}
		if( ( $listdata = createFilem3u8( $FMEDIA, $cache_folder, $TIMEBLOCK, $FILEM3U8, $IDMEDIA, $audiotrack ) ) != FALSE ){
			//die( 'NO Error creando la lista del video.' );
		}else{
			die( 'Error creando la lista del video.' );
		}
		$filesegment = $cache_folder . DS . $FILEM3U8;
		
		//time caching
		//sleep( 4 );
		
		/*
		Accept-Ranges:bytes
		Connection:Keep-Alive
		Content-Length:693156
		Content-Type:video/mp2t
		Date:Tue, 16 May 2017 10:24:52 GMT
		*/
		header( 'Accept-Ranges:bytes' );
		header( 'Connection:Keep-Alive' );
		header( 'Content-Type:application/x-mpegURL' );
		//header( 'Content-Length: ' . filesize( $filesegment ) );
		//readfile( $filesegment );
		header( 'Content-Length: ' . strlen( $listdata ) );
		echo( $listdata );
	}
	
?>
