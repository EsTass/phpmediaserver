<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	//check_mod_admin();
	
	//idmediainfo
	//user
	
	$HTMLDATA = get_msg( 'DEF_EMPTYLIST', FALSE );
	$search = '';
	$msearch = '';
	
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
        $HTMLDATA2 = array();
        foreach( $G_MEDIADATA AS $k => $v ){
            if( is_string( $v ) ){
                $HTMLDATA2[ $k ] = array();
            }
            
            if( is_string( $v ) 
            && $k != 'folder'
            && $k != 'fanart'
            && $k != 'landscape'
            && $k != 'logo'
            && $k != 'banner'
            ){
                $search = $k . ' ' . $MEDIAINFO[ 'title' ] . ' ' . $MEDIAINFO[ 'year' ];
                $msearch = ' ' . $MEDIAINFO[ 'title' ] . ' ' . $MEDIAINFO[ 'year' ];
                if( is_numeric( $MEDIAINFO[ 'season' ] ) ){
                    $search = 'serie ' . $search;
                }else{
                    $search = 'movie ' . $search;
                }
                //Check valid IMDBid
                if( array_key_exists( 'imdbid', $MEDIAINFO ) 
                && strlen( $MEDIAINFO[ 'imdbid' ] ) > 0
                ){
                    //not more accuracy
                    //$search .= ' ' . $MEDIAINFO[ 'imdbid' ] . ' related:imdb.com';
                //Check valid IMDBid
                }elseif( array_key_exists( 'imdb', $MEDIAINFO ) 
                && strlen( $MEDIAINFO[ 'imdb' ] ) > 0
                && ( $imdbid = getIMDB_ID( $MEDIAINFO[ 'imdb' ] ) ) != FALSE
                ){
                    //not more accuracy
                    //$search .= ' ' . $imdbid . ' related:imdb.com';
                //Check valid thetvdb.com
                }elseif( array_key_exists( 'tvdb', $MEDIAINFO ) 
                && strlen( $MEDIAINFO[ 'tvdb' ] ) > 0
                && ( $thetvdb = getTHETVDB_ID( $MEDIAINFO[ 'tvdb' ] ) ) != FALSE
                ){
                    //not more accuracy
                    //$search .= ' ' . $thetvdb . ' related:thetvdb.com';
                //Check valid themoviedb.com
                }elseif( array_key_exists( 'tmdb', $MEDIAINFO ) 
                && strlen( $MEDIAINFO[ 'tmdb' ] ) > 0
                && ( $themdb = getTHEMOVIEDB_ID( $MEDIAINFO[ 'tmdb' ] ) ) != FALSE
                ){
                    //not more accuracy
                    //$search .= ' ' . $themdb . ' related:themoviedb.com';
                }else{
                
                }
                
                //var_dump( $search );
                $images_own = array();
                $thumbsonly = FALSE;
                if( ( $images = searchImages( $search, 8, $thumbsonly ) ) != FALSE
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
                }
                if( !is_array( $HTMLDATA ) ) $HTMLDATA = array();
                $HTMLDATA[ $k ] = $images_own;
            }
        }
	}
	
	if( is_string( $HTMLDATA ) ){
        echo $HTMLDATA;
    }else{
        //URL manual search images
        $url_searchimg = 'https://duckduckgo.com/?q=' . urlencode( $search ) . '&iax=images&ia=images';
        
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
    foreach( $HTMLDATA2 AS $title => $links ) {
?>
        <th colspan='2'>URL Img <?php echo $title; ?></th>
<?php
    }
?>
    </tr>
    <tr>
<?php
    foreach( $HTMLDATA2 AS $title => $links ) {
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
    
    <h2><?php echo $MEDIAINFO[ 'title' ]; ?> (<?php echo $MEDIAINFO[ 'year' ]; ?>) - (<a rel='noreferrer' href="<?php echo O_ANON_LINK . $url_searchimg; ?>" target="_blank"><?php echo get_msg( 'MENU_SEARCH', FALSE ); ?></a>)</h2>
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
            <td class='pointer tdSearchImgResult' onclick='mediainfo_image_add( <?php echo $G_DATA[ 'idmediainfo' ]; ?>, "<?php echo $title; ?>", "<?php echo $fileimgpathrnd; ?>", "<?php echo basename( $img ); ?>" );'>
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
