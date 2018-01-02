<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
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
	
	if( $IDMEDIAINFO > 0
	&& ( $MEDIAINFO = sqlite_mediainfo_getdata( $IDMEDIAINFO ) ) != FALSE 
	&& is_array( $MEDIAINFO )
	&& count( $MEDIAINFO ) > 0
	){
        $MEDIAINFO = $MEDIAINFO[ 0 ];
        $HTMLRESULT = '';
	}elseif( $IDMEDIA > 0
	&& ( $m = sqlite_media_getdata( $IDMEDIA ) ) != FALSE 
	&& is_array( $m )
	&& count( $m ) > 0
	&& array_key_exists( 0, $m )
	&& array_key_exists( 'idmediainfo', $m[ 0 ] )
	&& $m[ 0 ][ 'idmediainfo' ] > 0
	&& ( $MEDIAINFO = sqlite_mediainfo_getdata( $m[ 0 ][ 'idmediainfo' ] ) ) != FALSE 
	&& is_array( $MEDIAINFO )
	&& count( $MEDIAINFO ) > 0
	){
        $MEDIAINFO = $MEDIAINFO[ 0 ];
        $IDMEDIAINFO = $MEDIAINFO[ 'idmediainfo' ];
        $HTMLRESULT = '';
	}else{
        $MEDIAINFO = FALSE;
        $HTMLRESULT = get_msg( 'DEF_NOTEXIST' );
	}
	
	$NEXTID = FALSE;
	$URL = '';
	//Series Go to back Or To List
	if( is_numeric( $MEDIAINFO[ 'season' ] ) 
	&& is_numeric( $MEDIAINFO[ 'episode' ] )
	&& $MEDIAINFO[ 'season' ] > -1
	&& $MEDIAINFO[ 'episode' ] > -1
	&& ( $edata = sqlite_media_getdata_chapters( $MEDIAINFO[ 'title' ] ) ) != FALSE 
	&& count( $edata ) > 0
	){
        foreach( $edata AS $k => $e ){
            if( array_key_exists( 'idmediainfo', $e ) 
            && $IDMEDIAINFO == $e[ 'idmediainfo' ]
            ){
                if( array_key_exists( $k +  1, $edata ) 
                && array_key_exists( 'idmediainfo', $edata[ $k + 1 ] ) 
                ){
                    $NEXTID = $edata[ $k + 1 ][ 'idmediainfo' ];
                    if( stripos( getReferer(), 'player' ) ){
                        $URL = getURLPlayer( FALSE, $NEXTID );
                    }else{
                        $URL = getURLInfo( FALSE, $NEXTID );
                    }
                }
                break;
            }
        }
        //not next, list chapters
        if( $NEXTID === FALSE ){
            $URL = getURLChapterList( FALSE, $IDMEDIAINFO );
        }
    
    //Movies Go to next Related
	}elseif( ( $edata = sqlite_media_getdata_related( $MEDIAINFO[ 'genre' ], 1, $IDMEDIAINFO ) ) != FALSE 
	&& count( $edata ) > 0
	&& array_key_exists( 0, $edata )
	&& is_array( $edata[ 0 ] )
	&& array_key_exists( 'idmediainfo', $edata[ 0 ] )
	){
        //PlayNext Related || LIST
        $URL = getURLInfo( FALSE, (int)$edata[ 0 ][ 'idmediainfo' ] );
	}else{
        $URL = getURLBase();
	}
    //var_dump( $URL );
    if( strlen( $URL ) > 0 ){
        echo redirectJS( $URL );
    }
?>
