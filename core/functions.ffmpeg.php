<?php
	
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
    
	//VIDEO INFO FFMPEG
	
	//FILEINFO
	
	function ffmpeg_file_info_lenght_hms( $file ) {
		$result = '01:00:00';
		
        if( ( $data = ffprobe_get_data( $file ) ) != FALSE
        && is_array( $data )
        && array_key_exists( 'duration', $data )
        && $data[ 'duration' ] > 0
        ){
            $hours = (int)( $data[ 'duration' ] / 3600 );
            $min = (int)( ( $data[ 'duration' ] - ( $hours * 3600 ) ) / 60 );
            $sec = (int)( $data[ 'duration' ] % 60 );
            $result = sprintf( '%02d:%02d:%02d', $hours, $min, $sec );
        }
		
		return $result;
	}
	
	function ffmpeg_file_info_lenght_seconds( $file ) {
		$result = 3600;
        if( ( $data = ffprobe_get_data( $file ) ) != FALSE
        && is_array( $data )
        && array_key_exists( 'duration', $data )
        && $data[ 'duration' ] > 0
        ){
            $result = $data[ 'duration' ];
        }
		
		return $result;
	}
	
	function ffmpeg_file_info_lenght_minutes( $file ) {
		$result = 60;
		
        if( ( $data = ffprobe_get_data( $file ) ) != FALSE
        && is_array( $data )
        && array_key_exists( 'duration', $data )
        && $data[ 'duration' ] > 0
        ){
            $result = (int)( $data[ 'duration' ] / 60 );
        }
		
		return $result;
	}
	
    //return low quality video
	function ffmpeg_video_compare( $video1, $video2 ) {
        $result = FALSE;
        $file1hp = FALSE;
        $file2hp = FALSE;
        //high priority strings in filename
        if( defined( 'O_CRON_CLEAN_DUPLICATES_HIGHPRIORITY_STRING' ) 
        && is_array( O_CRON_CLEAN_DUPLICATES_HIGHPRIORITY_STRING )
        ){
            $hpstrings = O_CRON_CLEAN_DUPLICATES_HIGHPRIORITY_STRING;
            foreach( $hpstrings AS $re ){
                if( preg_match( $re, $video1 ) ){
                    $file1hp = TRUE;
                }
                if( preg_match( $re, $video2 ) ){
                    $file2hp = TRUE;
                }
            }
            if( $file1hp == TRUE
            && $file2hp == TRUE
            ){
                $file1hp = FALSE;
                $file2hp = FALSE;
            }
        }
        
		if( !file_exists( $video1 ) 
		|| ( $attr1 = ffprobe_get_data( $video1 ) ) === FALSE
		){
            $result = $video1;
		}elseif( !file_exists( $video2 ) 
		|| ( $attr2 = ffprobe_get_data( $video2 ) ) === FALSE
		){
            $result = $video2;
		}elseif( $file1hp == TRUE
		){
            $result = $video1;
		}elseif( $file2hp == TRUE
		){
            $result = $video2;
		}elseif( is_array( $attr1 )
		&& is_array( $attr2 )
		){
            if( $attr1[ 'width' ] > $attr2[ 'width' ] ){
                $result = $video2;
            }elseif( $attr1[ 'width' ] < $attr2[ 'width' ] ){
                $result = $video1;
            }elseif( $attr1[ 'height' ] > $attr2[ 'height' ] ){
                $result = $video2;
            }elseif( $attr1[ 'height' ] < $attr2[ 'height' ] ){
                $result = $video1;
            }elseif( $attr1[ 'audiotracks' ] > $attr2[ 'audiotracks' ] ){
                $result = $video2;
            }elseif( $attr1[ 'audiotracks' ] < $attr2[ 'audiotracks' ] ){
                $result = $video1;
            }elseif( filesize( $video1 ) > filesize( $video2 ) ){
                $result = $video2;
            }else{
                $result = $video1;
            }
		}else{
            if( filesize( $video1 ) > filesize( $video2 ) ){
                $result = $video2;
            }else{
                $result = $video1;
            }
		}
		
		return $result;
	}
	
    //return idmedia data low quality
	function ffmpeg_media_compare( $mediadata1, $mediadata2 ) {
        $result = FALSE;
        
        if( is_array( $mediadata1 )
        && array_key_exists( 'file', $mediadata1 )
        && is_array( $mediadata2 )
        && array_key_exists( 'file', $mediadata2 )
        && file_exists( $mediadata1[ 'file' ] )
        && file_exists( $mediadata2[ 'file' ] )
        ){
            $maxsize = O_CRON_CLEAN_DUPLICATES_MEDIAINFO_MAXSIZE * 1024 * 1024;
            $fs1 = filesize( $mediadata1[ 'file' ] );
            $fs2 = filesize( $mediadata2[ 'file' ] );
            if( $maxsize > 0 
            && $fs1 > $maxsize
            ){
                if( $fs1 < $fs2  ){
                    $result = $mediadata1;
                }else{
                    $result = $mediadata2;
                }
            }elseif( $maxsize > 0 
            && $fs2 > $maxsize
            ){
                $result = $mediadata2;
            }elseif( ( $lowq = ffmpeg_video_compare( $mediadata1[ 'file' ], $mediadata2[ 'file' ] ) ) != FALSE
            ){
                if( $mediadata1[ 'file' ] == $lowq ){
                    $result = $mediadata1;
                }elseif( $mediadata2[ 'file' ] == $lowq ){
                    $result = $mediadata2;
                }
            }
        }
        
        return $result;
	}
	
	//ffprobe
	
	function ffprobe_get_data( $file, $debug = FALSE ){
        $result = FALSE;
        //TEST -rw_timeout 5M max time urls
        $cmd = O_FFPROBE . " -rw_timeout 5M -i " . escapeshellarg( $file ) . " -v quiet -print_format xml -show_format -show_streams -hide_banner";
        $ocrsubs = array(
            'dvd_subtitle',
        );
        
        if( ( filter_var( $file, FILTER_VALIDATE_URL )  || file_exists( $file ) )
        && ( $data = runExtCommand( $cmd ) ) != FALSE
        && ( $info = new SimpleXMLElement( $data ) ) != FALSE
        ){
            if( $debug ){
                echo "<br />CMD: " . $cmd . "\n";
                echo "<br />Video duration: " . $info->format['duration'] . "\n";
                echo "<br />Video size: " . $info->format['size'] . "\n";
                echo "<br />Video resolution width: " . $info->streams->stream[0]['width'] . "\n";
                echo "<br />Video resolution height: " . $info->streams->stream[0]['height'] . "\n";
                echo "<br />Video aspect ratio: " . $info->streams->stream[0]['display_aspect_ratio'] . "\n";
                echo "<br />Video codec: " . $info->streams->stream[0]['codec_name'] . "\n";
                echo "<br />Audio codec: " . $info->streams->stream[1]['codec_name'] . "\n";
                echo "<br />Audio sample rate: " . $info->streams->stream[1]['sample_rate'] . "\n";
            }
            $duration = (int)$info->format['duration'];
            $audiotracks = array();
            $subtracks = array();
            $subtracksv = array();
            $subtracksc = array();
            for( $s = 0; $s < @count( $info->streams->stream ); $s++ ){
                if( (string)$info->streams->stream[ $s ][ 'codec_type' ] == 'audio'
                ){
                    $audio = '';
                    if( $debug ) echo "<br />AUDIOINFO: " . nl2br( print_r( $info->streams->stream[ $s ], TRUE ) );
                    for( $t = 0; $t < @count( $info->streams->stream[ $s ]->tag ); $t++ ){
                        if( (string)$info->streams->stream[ $s ]->tag[ $t ][ 'key' ] == 'language'
                        ){
                            $audio = (int)$info->streams->stream[ $s ][ 'index' ] . '-' . (string)$info->streams->stream[ $s ]->tag[ $t ][ 'value' ];
                            $audio .= ' (' . $info->streams->stream[ $s ][ 'channel_layout' ] . ')';
                            break;
                        }
                    }
                    for( $t = 0; $t < @count( $info->streams->stream[ $s ]->tag ); $t++ ){
                        if( (string)$info->streams->stream[ $s ]->tag[ $t ][ 'key' ] == 'title'
                        ){
                            $audio .= ' ' . (string)$info->streams->stream[ $s ]->tag[ $t ][ 'value' ];
                            break;
                        }
                    }
                    
                    if( strlen( $audio ) == 0 ){
                        $audio = 'Audio-' . count( $audiotracks );
                    }
                    $audiotracks[ $s ] = $audio;
                }elseif( (string)$info->streams->stream[ $s ][ 'codec_type' ] == 'subtitle'
                //EXCLUDE OCR SUBS
                && !in_array( strtolower( (string)$info->streams->stream[ $s ][ 'codec_name' ] ), $ocrsubs )
                ){
                    $sub = '';
                    if( $debug ) echo "<br />SUBINFO: " . nl2br( print_r( $info->streams->stream[ $s ], TRUE ) );
                    for( $t = 0; $t < @count( $info->streams->stream[ $s ]->tag ); $t++ ){
                        if( (string)$info->streams->stream[ $s ]->tag[ $t ][ 'key' ] == 'language'
                        ){
                            $sub = (int)$info->streams->stream[ $s ][ 'index' ] . '-' . (string)$info->streams->stream[ $s ]->tag[ $t ][ 'value' ];
                            $sub .= ' (' . $info->streams->stream[ $s ][ 'channel_layout' ] . ')';
                            break;
                        }
                    }
                    for( $t = 0; $t < @count( $info->streams->stream[ $s ]->tag ); $t++ ){
                        if( (string)$info->streams->stream[ $s ]->tag[ $t ][ 'key' ] == 'title'
                        ){
                            $sub .= ' ' . (string)$info->streams->stream[ $s ]->tag[ $t ][ 'value' ];
                            break;
                        }
                    }
                    if( strlen( $sub ) == 0 ){
                        $sub = 'Subs-' . count( $subtracks );
                    }
                    $subtracksc[] = (string)$info->streams->stream[ $s ][ 'codec_name' ];
                    $subtracks[ $s ] = $sub;
                }elseif( (string)$info->streams->stream[ $s ][ 'codec_type' ] == 'subtitle'
                //OCR SUBS
                ){
                    $sub = '';
                    if( $debug ) echo "<br />SUBINFOOCR: " . nl2br( print_r( $info->streams->stream[ $s ], TRUE ) );
                    for( $t = 0; $t < @count( $info->streams->stream[ $s ]->tag ); $t++ ){
                        if( (string)$info->streams->stream[ $s ]->tag[ $t ][ 'key' ] == 'language'
                        ){
                            $sub = (int)$info->streams->stream[ $s ][ 'index' ] . '-' . (string)$info->streams->stream[ $s ]->tag[ $t ][ 'value' ];
                            $sub .= ' (' . $info->streams->stream[ $s ][ 'channel_layout' ] . ')';
                            break;
                        }
                    }
                    for( $t = 0; $t < @count( $info->streams->stream[ $s ]->tag ); $t++ ){
                        if( (string)$info->streams->stream[ $s ]->tag[ $t ][ 'key' ] == 'title'
                        ){
                            $sub .= ' ' . (string)$info->streams->stream[ $s ]->tag[ $t ][ 'value' ];
                            break;
                        }
                    }
                    if( strlen( $sub ) == 0 ){
                        $sub = 'Subs-' . count( $subtracksv );
                    }
                    $stocr = count( $subtracksc );
                    $subtracksc[] = (string)$info->streams->stream[ $s ][ 'codec_name' ];
                    //OCR subs start on sub=0
                    $subtracksv[ $stocr ] = $sub;
                }
            }
            $result = array(
                'codec' => (string)$info->streams->stream[0]['codec_name'],
                'acodec' => ( (string)@$info->streams->stream[1]['codec_name'] || '' ),
                'width' => (int)$info->streams->stream[0]['width'],
                'height' => (int)$info->streams->stream[0]['height'],
                'duration' => (int)$duration,
                'audiotracks' => $audiotracks,
                'subtracks' => $subtracks,
                'subtracksv' => $subtracksv,
                'subscodecs' => $subtracksc,
            );
        }
        
        if( $debug ) echo "<br />RESULT: " . nl2br( print_r( $result, TRUE ) );
        return $result;
	}
	
	//extract subs
	
	function ffmpeg_extract_subfile( $filevideo, $filesubs, $track, $debug = FALSE ){
        $result = FALSE;
        
        $cmd = O_FFMPEG . " -i " . escapeshellarg( $filevideo ) . " -map 0:" . $track . " " . escapeshellarg( $filesubs );
        if( $debug ) echo "<br />" . $cmd;
        if( ( $data = runExtCommand( $cmd ) ) != FALSE ){
            $result = TRUE;
        }
        
        return $result;
	}
	
	//make apng preview
	
	function ffmpeg_preview_apng( $filevideo, $filepreview, $starttime = 30, $duration = 10, $debug = FALSE ){
        $result = FALSE;
        
        //preview size: 480x270, 240x135
        //fps:  -vf fps=fps=12
        //each second: -vf fps=1
        //keyframes: -vsync vfr
        $cmd = O_FFMPEG . " -i " . escapeshellarg( $filevideo ) . " -ss " . $starttime . " -t " . $duration . " -vf fps=1 -s 240x135  -f apng " . escapeshellarg( $filepreview );
        if( $debug ) echo "<br />" . $cmd;
        if( ( $data = runExtCommand( $cmd ) ) != FALSE ){
            $result = TRUE;
        }
        
        return $result;
	}
	
	//make 2 images from video with 5 secs step
	
	function ffmpeg_capture_img( $filevideo, $seconds = 5, $debug = FALSE ){
        $result = FALSE;
        
        $filepreview = PPATH_TEMP . DS . 'vcapture%02d.png'; //vcapture01.png, vcapture02.png
        $file1 = PPATH_TEMP . DS . 'vcapture01.png';
        $file2 = PPATH_TEMP . DS . 'vcapture02.png';
        
        @unlink( $file1 );
        @unlink( $file2 );
        
        //ffmpeg -i myvideo.avi -vf fps=1/60 img%03d.jpg
        $cmd = O_FFMPEG . " -i " . escapeshellarg( $filevideo ) . " -vf fps=1/" . $seconds . " -s 240x135  -vframes 2 " . escapeshellarg( $filepreview );
        if( $debug ) echo "<br />" . $cmd;
        if( ( $data = runExtCommand( $cmd ) ) != FALSE 
        && file_exists( $file1 )
        && file_exists( $file2 )
        ){
            $result = array( $file1, $file2 );
        }
        
        return $result;
	}
	
	//Compare 2 images GD same size and return same in % $samepc
	
	function sameImage( $img1, $img2, $samepc = 90, $debug = FALSE ){
        $result = TRUE;
        
        if( file_exists( $img1 )
        && file_exists( $img2 )
        && ( $a = imagecreatefrompng( $img1 ) ) != FALSE
        && ( $b = imagecreatefrompng( $img2 ) ) != FALSE
        ){
            $diff = 0;
            
            $width = imagesx( $a );
            $height = imagesy( $a );
            
            for( $x = 0; $x < $width; $x++ ){
                for( $y = 0; $y < $height; $y++ ){
                    // pixel color at (x, y)
                    $diff += abs( imagecolorat( $a, $x, $y ) - imagecolorat( $b, $x, $y ) );
                }
            }
            
            if( $debug ) echo "<br />WIDTH: " . $width;
            if( $debug ) echo "<br />HEIGHT: " . $height;
            if( $debug ) echo "<br />W*H: " . ( $height * $width );
            if( $debug ) echo "<br />DIFF: " . $diff;
            $div = ( 100 * $diff ) / ( $height * $width * 255 * 255 * 255 );
            if( $debug ) echo "<br />%: " . $div;
            
            if( (int)$div >= ( 100 - $samepc ) ){
                $result = FALSE;
            }
        }
        
        return $result;
    }
?>
