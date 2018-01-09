<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	//check_mod_admin();
	
	//idmediainfo
	//user
	
	$HTMLDATA = get_msg( 'DEF_EMPTYLIST', FALSE );
	if( !array_key_exists( 'idmediainfo', $G_DATA ) 
	|| !is_numeric( $G_DATA[ 'idmediainfo' ] )
	|| $G_DATA[ 'idmediainfo' ] <= 0
	|| ( $MEDIAINFO = sqlite_mediainfo_getdata( $G_DATA[ 'idmediainfo' ] ) ) == FALSE
	|| !is_array( $MEDIAINFO )
	|| !array_key_exists( 0, $MEDIAINFO )
	){
		$HTMLDATA = get_msg( 'DEF_NOTEXIST' ) . ' idmediainfo';
	}else{
        $MEDIAINFO = $MEDIAINFO[ 0 ];
        $fileimgpathrnd = getRandomString( 8 );
        $fileimgpath = PPATH_TEMP . DS . $fileimgpathrnd;
        @mkdir( $fileimgpath );
        $in_list = array();
        $filenum = 1;
        foreach( $G_MEDIADATA AS $k => $v ){
            if( is_string( $v ) 
            && $k != 'folder'
            && $k != 'fanart'
            //&& $k == 'poster'
            ){
                $search = $k . ' ' . $MEDIAINFO[ 'title' ] . ' ' . $MEDIAINFO[ 'year' ];
                if( is_numeric( $MEDIAINFO[ 'season' ] ) ){
                    $search .= ' serie';
                }else{
                    $search .= ' movie';
                }
                //var_dump( $search );
                $images_own = array();
                if( ( $images = searchImages( $search, 8, FALSE ) ) != FALSE
                && is_array( $images ) 
                && count( $images ) > 0
                ){
                    foreach( $images AS $key => $img ){
                        $fileimg = $fileimgpath . DS . $filenum;
                        if( array_key_exists( $img, $in_list ) ){
                            $images_own[] = $in_list[ $img ];
                        }elseif( downloadPosterToFile( $img, $fileimg ) 
                        && file_exists( $fileimg )
                        && getFileMimeTypeImg( $fileimg )
                        ){
                            $in_list[ $img ] = $fileimg;
                            $images_own[] = $fileimg;
                            $filenum++;
                        }
                    }
                    if( !is_array( $HTMLDATA ) ) $HTMLDATA = array();
                    $HTMLDATA[ $k ] = $images_own;
                }
            }
        }
	}
	
	if( is_string( $HTMLDATA ) ){
        echo $HTMLDATA;
    }else{
        
?>

<script type="text/javascript">
$(function () {
    
});

function mediainfo_image_add( idmediainfo, type, tfolder, tfile ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=mediainfoaddimg&idmediainfo=' + idmediainfo;
    url += '&type=' + type;
    url += '&tfolder=' + tfolder;
    url += '&tfile=' + tfile;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}

function mediainfo_url_add( idmediainfo, type, tfolder, ifield ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=mediainfoaddurl&idmediainfo=' + idmediainfo;
    url += '&type=' + type;
    url += '&tfolder=' + tfolder;
    url += '&url=' + encodeURI( $( '#' + ifield ).val() );
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}

</script>

<table class='tList'>
    <tr>
<?php
    foreach( $HTMLDATA AS $title => $links ) {
?>
        <th colspan='2'>URL Img <?php echo $title; ?></th>
<?php
    }
?>
    </tr>
    <tr>
<?php
    foreach( $HTMLDATA AS $title => $links ) {
?>
        <td>
            <input type='text' id="url<?php echo $title; ?>" name="url<?php echo $title; ?>" value='' />
        </td>
        <td>
            <input 
            onclick='mediainfo_url_add( <?php echo $G_DATA[ 'idmediainfo' ]; ?>, "<?php echo $title; ?>", "<?php echo $fileimgpathrnd; ?>", "url<?php echo $title; ?>" );'
            type='button' 
            value='<?php echo get_msg( 'MENU_UPDATE', FALSE ); ?>' 
            id='bAddImageURL<?php echo $title; ?>' 
            name='bAddImageURL<?php echo $title; ?>' 
            />
        </td>
<?php
    }
?>
    </tr>
</table>
    
    <h2><?php echo $MEDIAINFO[ 'title' ]; ?> (<?php echo $MEDIAINFO[ 'year' ]; ?>)</h2>
<?php
        foreach( $HTMLDATA AS $title => $links ) {
            
?>
    <table class='tList' style='width:100%;margin: auto;'>
        <tr>
            <td colspan='100'><?php echo $title; ?></td>
        </tr>
        <tr>
                <?php
                    $css_extra = '';
                    foreach( $links AS $img ){
                ?>
            <td class='pointer' onclick='mediainfo_image_add( <?php echo $G_DATA[ 'idmediainfo' ]; ?>, "<?php echo $title; ?>", "<?php echo $fileimgpathrnd; ?>", "<?php echo basename( $img ); ?>" );'>
                <img rel='noreferrer' class='listElementPosterTinyFreeSize' src='<?php echo getURLImgTmp( $img ); ?>' />
            </td>
                <?php
                    }
                ?>
        </tr>
    </table>
<?php
        }
    }
?>
