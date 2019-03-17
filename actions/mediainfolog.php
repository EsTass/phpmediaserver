<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	
	$FIELDS = array(
        'idmediainfo' => 'idmediainfo',
        'dateadded' => 'DateAdded',
        'title' => 'Title',
        'season' => 'Season',
        'episode' => 'Episode',
        'year' => 'Year',
        'titleepisode' => 'TitleEpisode',
	);
	
	if( ( $edata = sqlite_mediainfo_search( $G_SEARCH, 100 ) ) ){
?>

<script type="text/javascript">
$(function () {
    
});
function log_edit_mediainfo( idmediainfo ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=mediainfoedit&idmediainfo=' + idmediainfo;
    $.get( url )
    .done( function( data ){
        $( '#dResultIdent' ).html( data );
        loading_hide();
    });
    return false;
}
function log_delete_mediainfo( idmediainfo ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=mediainfodelete&idmediainfo=' + idmediainfo;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
function ident_identify_media( idmedia ){
    var url = '<?php getURL(); ?>?' + $( '#fElementIdent' ).serialize();
    url += '&idmedia=' + idmedia;
    loading_show();
    $.get( url )
    .done( function( data ){
        $( '#dResultIdent' ).html( data );
        loading_hide();
    });
    
    return false;
}
function log_delete_mediainfo_imgs( idmediainfo ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=mediainfodeleteimgs&idmediainfo=' + idmediainfo;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_mediainfo_search_images( idmediainfo ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=mediainfosearchimgs&idmediainfo=' + idmediainfo;
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#dResultIdent' ).html( data );
        loading_hide();
    });
    
    return false;
}
</script>

<div id='dResultIdent'></div>

<br />

<form id='fElementIdent'>
    
    <input type='hidden' id='r' name='r' value='r' />
    <input type='hidden' id='action' name='action' value='identifye' />
    <table class='tList' style='width:100%;margin: auto;'>
        <tr>
            <td colspan='100'></td>
        </tr>
        <tr>
            <th>Poster</th>
            <?php
                foreach( $FIELDS AS $f => $t ){
            ?>
                <th><?php echo $t; ?></th>
            <?php
                }
            ?>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
                <?php
                    $css_extra = '';
                    foreach( $edata AS $lrow ){
                ?>
        <tr>
            <td>
                <img class='listElementImg listElementImgMini lazy' src='' data-src='<?php echo getURLImg( FALSE, $lrow[ 'idmediainfo' ], 'poster' ); ?>' class='listElementPosterTiny' />
            </td>
                <?php
                        foreach( $lrow AS $field => $data ){
                            if( array_key_exists( $field, $FIELDS ) ){
                ?>
            <td class='<?php echo $css_extra; ?>' title='<?php echo $data; ?>'><?php echo substr( $data, 0, 100 ); ?></td>
                <?php
                            }
                        }
                ?>
            <td>
                <input onclick='log_edit_mediainfo( <?php echo $lrow[ 'idmediainfo' ]; ?> );' type='button' id='bLogEdit' name='bLogEdit' value='<?php echo get_msg( 'MENU_EDIT', FALSE ); ?>' />
                <input onclick='log_delete_mediainfo( <?php echo $lrow[ 'idmediainfo' ]; ?> );' type='button' id='bLogDelete' name='bLogDelete' value='<?php echo get_msg( 'MENU_DELETE', FALSE ); ?>' />
                <input onclick='log_delete_mediainfo_imgs( <?php echo $lrow[ 'idmediainfo' ]; ?> );' type='button' id='bLogDeleteImgs' name='bLogDeleteImgs' value='<?php echo get_msg( 'MENU_DELETE_IMGS', FALSE ); ?>' />
                <input onclick='log_mediainfo_search_images( <?php echo $lrow[ 'idmediainfo' ]; ?> );' type='button' id='bLogSearchImgs' name='bLogSearchImgs' value='<?php echo get_msg( 'MENU_IMGS_SEARCH', FALSE ); ?>' />
            </td>
        </tr>
                <?php
                    }
                ?>
    </table>
	
</form>
<?php
    }else{
        echo get_msg( 'DEF_EMPTYLIST' );
    }
?>
