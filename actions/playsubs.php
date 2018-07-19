<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//set_time_limit(0);
	
	//load identified subs and echo json datasub = array( 'timestart', 'timeend', 'text' ) converting supported formats
	//timestart and timeend in secods with decimals (4)
	//action
	//idmedia
	//subtrack
	
	
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        $IDMEDIA = FALSE;
	}
	
	if( array_key_exists( 'subtrack', $G_DATA ) ){
        $SUBTRACK = $G_DATA[ 'subtrack' ];
	}else{
        $SUBTRACK = FALSE;
	}
	
	
	
	//HELPERS
	
	//SRT
	
	function subs_import_srt( $data, $DEBUG = FALSE ){
        //Import str format
        /*
        1
        00:00:02,600 --> 00:00:08,869
        Al menos la mitad de esta historia
        está documentada como hecho histórico.
        
        2
        ...
        */
        $result = array( 0 => array( 'timestart' => 0, 'timeend' => 0, 'text' => '' ) );
        
        $x = 1;
        $nexttime = FALSE;
        $nexttext = FALSE;
        if( is_array( $data ) ){
            foreach( $data AS $row ){
                $row = trim( $row );
                if( $DEBUG ) echo "<br />-LINE: " . $row;
                if( $DEBUG ) echo "<br />-LINEPOS: " . $x;
                //Search $x pos
                if( ctype_digit( $row )
                && $x == (int)$row
                ){
                    if( $DEBUG ) echo "<br />SETX: " . $row;
                    $nexttime = TRUE;
                    $nexttext = FALSE;
                    $x++;
                }elseif( $nexttime ){
                    if( $DEBUG ) echo "<br />SETXNEXTTIME: " . $row;
                    //Get times and assign to result row
                    $timestart = subs_srt_convert_time( $row, 0, $DEBUG );
                    $timeend = subs_srt_convert_time( $row, 1, $DEBUG );
                    $result[ $x - 1 ][ 'timestart' ] = $timestart;
                    $result[ $x - 1 ][ 'timeend' ] = $timeend;
                    $nexttime = FALSE;
                    $nexttext = TRUE;
                }elseif( $nexttext ){
                    if( $DEBUG ) echo "<br />SETXNEXTTEXT: " . $row;
                    //Get text and assign to result row
                    if( !array_key_exists( 'text', $result[ $x - 1 ] ) ){
                        $result[ $x - 1 ][ 'text' ] = $row;
                    }else{
                        $result[ $x - 1 ][ 'text' ] .= '<br>' . $row;
                    }
                }elseif( strlen( $row ) == 0 ){
                    if( $DEBUG ) echo "<br />SETXEXMPTY: " . $row;
                    $nexttime = FALSE;
                    $nexttext = FALSE;
                }
                if( $DEBUG 
                && $x > 10
                ){
                    break;
                }
            }
        }
        
        return $result;
	}
	
	function subs_srt_convert_time( $row, $pos, $DEBUG = FALSE ){
        //Convert pos time to seconds with decimals
        //00:00:02,600 --> 00:00:08,869
        $result = 0.0000;
        
        if( $DEBUG ) echo "<br />GETTIMES: " . $row;
        if( ( $d = explode( ' --> ', $row ) ) != FALSE 
        && is_array( $d )
        && count( $d ) > 0
        && array_key_exists( $pos, $d )
        ){
            $t = $d[ $pos ];
            if( $DEBUG ) echo "<br />GETTIMESDATA: " . $t;
            //sometimes . or , localization
            if( inString( $t, ',' ) ){
                $t = str_ireplace( ',', '.', $t );
            }
            if( sscanf( $t, "%f:%f:%f", $hours, $minutes, $seconds) ){
                (float)$result = (float)( $hours * 3600 ) + (float)( $minutes * 60 ) + (float)$seconds;
                //adust to browser times -0.100
                (float)$result -= 0.100;
                if( $DEBUG ) echo "<br />GETTIMESDATARESULT: " . $result;
            }
        }
        
        return $result;
	}
	
	//BASE
	
	$DEBUG = FALSE;
	if( $IDMEDIA !== FALSE 
	&& $SUBTRACK !== FALSE
	&& ( $mi = sqlite_media_getdata( $IDMEDIA ) ) != FALSE 
	&& is_array( $mi )
	&& count( $mi ) > 0
	&& @file_exists( $mi[ 0 ][ 'file' ] )
	&& getFileMimeTypeVideo( $mi[ 0 ][ 'file' ] )
	){
        $FMEDIA = $mi[ 0 ][ 'file' ];
        //Set filename 'subs cache'
        $filesubs = PPATH_CACHE . DS . 'subs' . DS . $mi[ 0 ][ 'idmedia' ] . '.' . $SUBTRACK . '.srt';
        
        //check folder subs exist
        if( !file_exists( PPATH_CACHE . DS . 'subs' ) ){
            mkdir( PPATH_CACHE . DS . 'subs' );
        }
        //get file if exist or extract
        if( !file_exists( $filesubs ) 
        || filesize( $filesubs ) == 0
        ){
            ffmpeg_extract_subfile( $FMEDIA, $filesubs, $SUBTRACK, FALSE );
        }
        
        if( $DEBUG ) echo "<br />FILESUB: " . $filesubs;
        
        $result = array();
        if( file_exists( $filesubs ) ){
            $data = array();
            if( ( $data = file_get_contents( $filesubs ) ) != FALSE ){
                $data = explode( "\n", $data );
            }
            if( $DEBUG ) echo "<br />FILELINES: " . count( $data );
            
            //STR FORMAT
            if( is_array( $data )
            && endsWith( strtolower( $filesubs ), '.srt' ) 
            && ( $result = subs_import_srt( $data, $DEBUG ) ) != FALSE
            ){
                
            }else{
                $result = array();
            }
        }
        
        if( $DEBUG ) echo "<br />RESULT: " . nl2br( print_r( $result, TRUE ) );
        
        if( !$DEBUG ) header( 'Content-Type: application/json' );
        //, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
        echo '' . json_encode( $result );
    }
	
    exit();
?>

