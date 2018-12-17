<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmedia
	//user
	if( !array_key_exists( 'idmedia', $G_DATA ) 
	|| !is_numeric( $G_DATA[ 'idmedia' ] )
	|| $G_DATA[ 'idmedia' ] <= 0
	|| ( $mdata = sqlite_media_getdata( $G_DATA[ 'idmedia' ] ) ) == FALSE
	|| !is_array( $mdata )
	|| !array_key_exists( 0, $mdata )
	|| !is_array( $mdata[ 0 ] )
	|| !array_key_exists( 'file', $mdata[ 0 ] )
	|| !file_exists( $mdata[ 0 ][ 'file' ] )
	){
		$HTMLDATA = get_msg( 'DEF_NOTEXIST' ) . ' idmedia';
	}else{
        //create preview from 30secs to 40 min
        //$starttime = mt_rand( 30, ( 40 * 60 ) );
        $duration = 30;
        $starttime = 30;
        if( array_key_exists( 'starttime', $G_DATA ) 
        && is_numeric( $G_DATA[ 'starttime' ] )
        && $G_DATA[ 'starttime' ] >= 0
        ){
            $starttime = (int)$G_DATA[ 'starttime' ];
        }
        $beforetime = $starttime - $duration;
        if( $beforetime < 0 ){
            $beforetime = 0;
        }
        $aftertime = $starttime + $duration;
        $fileimgpathrnd = getRandomString( 8 );
        $fileimgpath = PPATH_TEMP . DS . $fileimgpathrnd;
        @mkdir( $fileimgpath );
        $filepreview = $fileimgpath . DS . $G_DATA[ 'idmedia' ] . '.preview.png';
        if( file_exists( $fileimgpath ) 
        && ffmpeg_preview_apng( $mdata[ 0 ][ 'file' ], $filepreview, $starttime, $duration )
        && file_exists( $filepreview )
        && filesize( $filepreview ) > 1024
        ){
            $filedata = base64_encode( file_get_contents( $filepreview ) );
            $HTMLDATA = "
        <table class='tList' style='text-align: center'>
        <tr>
            <th colspan='100'>Preview</th>
        </tr>
        </tr>
            <td>
                <input onclick='ident_preview_media( " . $G_DATA[ 'idmedia' ] . ", " . $beforetime . " );' type='button' id='bIdentifyP2' name='bIdentifyP2' value='<<' />
            </td>
            <td>
                <img src='data:image/png;base64, " . $filedata . "' />
            </td>
            <td>
                <input onclick='ident_preview_media( " . $G_DATA[ 'idmedia' ] . ", " . $aftertime . " );' type='button' id='bIdentifyP3' name='bIdentifyP3' value='>>' />
            </td>
        </tr>
        </table>
        ";
        }else{
            $HTMLDATA = "<p style='text-align: center'>Cant crete file preview.</p>";
        }
	}
	
	//header("Refresh: 2");
	echo $HTMLDATA;
?>
