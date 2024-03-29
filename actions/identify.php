<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	
	$fields = array(
        'idmedia',
        'file',
        'idmediainfo',
        'title',
        'season',
        'episode',
        'year',
	);
	
	//get bad idents, not idents and last elements added
	if( ( $edata = sqlite_media_getdata_identify_orderer( $G_SEARCH ) ) ){
?>

<script type="text/javascript">
$(function () {
    
});
function log_delete_media( idmedia ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=mediadelete&idmedia=' + idmedia;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_delete_media_file( idmedia ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=mediadeletefile&idmedia=' + idmedia;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
function ident_identify_media( idmedia ){
    var url = '<?php echo getURLBase(); ?>?' + $( '#fElementIdent' ).serialize();
    url += '&idmedia=' + idmedia;
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#dResultIdent' ).html( data );
        loading_hide();
    });
    
    return false;
}
function ident_rs_media( idmedia ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=identifyrs';
    url += '&idmedia=' + idmedia;
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#dResultIdent' ).html( data );
        loading_hide();
    });
    
    return false;
}
function ident_autoset_idmedia( idmedia ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=identifyauto&idmedia=' + idmedia;
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#dResultIdent' ).html( data );
        loading_hide();
    });
    
    return false;
}
function ident_preview_media( idmedia, starttime ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=mediapreview&idmedia=' + idmedia + '&starttime=' + starttime;
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
            <th>idmedia</th>
            <th>File</th>
            <th>idmediainfo</th>
            <th>Title</th>
            <th>Season</th>
            <th>Episode</th>
            <th>Year</th>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
                <?php
                    $css_extra = '';
                    foreach( $edata AS $lrow ){
                ?>
        <tr>
            <td>
                <img class='listElementImg listElementImgMini lazy' src='' data-src='<?php echo getURLImg( $lrow[ 'idmedia' ], FALSE, 'poster' ); ?>' class='listElementPosterTiny' />
            </td>
                <?php
                        foreach( $fields AS $field ){
                            if( array_key_exists( $field, $lrow ) ){
                                $data = $lrow[ $field ];
                                if( $data == NULL ){
                                    $data = '';
                                }
                ?>
            <td class='<?php echo $css_extra; ?>' title='<?php echo $data; ?>'><?php echo substr( $data, 0, 250 ); ?></td>
                <?php
                            }else{
                ?>
            <td class='<?php echo $css_extra; ?>' title=''></td>
                <?php
                            }
                        }
                ?>
            <td>
                <input onclick='ident_identify_media( <?php echo $lrow[ 'idmedia' ]; ?> );' type='button' id='bIdentify' name='bIdentify' value='<?php echo get_msg( 'MENU_IDENTIFY', FALSE ); ?>' />
                <input onclick='ident_rs_media( <?php echo $lrow[ 'idmedia' ]; ?> );' type='button' id='bIdentifyRS' name='bIdentifyRS' value='Bulk <?php echo get_msg( 'MENU_IDENTIFY', FALSE ); ?>' />
                <input onclick='ident_preview_media( <?php echo $lrow[ 'idmedia' ]; ?>, 0 );' type='button' id='bIdentifyP' name='bIdentifyP' value='Preview' />
                <input onclick='ident_autoset_idmedia( <?php echo $lrow[ 'idmedia' ]; ?> );' type='button' id='bSetAutoIdMedia' name='bSetAutoIdMedia' value='<?php echo get_msg( 'MENU_IDENTIFY_AUTO', FALSE ); ?>' />
                <input onclick='log_delete_media( <?php echo $lrow[ 'idmedia' ]; ?> );' type='button' id='bLogDelete' name='bLogDelete' value='<?php echo get_msg( 'MENU_DELETE', FALSE ); ?>' />
                <input onclick='log_delete_media_file( <?php echo $lrow[ 'idmedia' ]; ?> );' type='button' id='bLogDeleteFile' name='bLogDeleteFile' value='<?php echo get_msg( 'MENU_DELETE_FILE', FALSE ); ?>' />
            </td>
        </tr>
                <?php
                    }
                ?>
    </table>
	
</form>
<?php
    }
?>
