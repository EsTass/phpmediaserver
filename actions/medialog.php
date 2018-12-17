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
        'idmedia' => 'idmedia',
        'file' => 'file',
        'title' => 'Title',
        'langs' => 'Languajes',
        'subs' => 'Subs',
        'idmediainfo' => 'idmediainfo',
        'dateadded' => 'DateAdded',
        'season' => 'Season',
        'episode' => 'Episode',
        'year' => 'Year',
	);
	
	if( ( $edata = sqlite_media_getdata_filtered( $G_SEARCH, 100 ) ) ){
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
                        foreach( $FIELDS AS $field => $title ){
                            if( array_key_exists( $field, $lrow ) ){
                                $data = $lrow[ $field ];
                ?>
            <td class='<?php echo $css_extra; ?>' title='<?php echo $data; ?>'><?php echo substr( $data, 0, 100 ); ?></td>
                <?php
                            }
                        }
                ?>
            <td>
                <input onclick='ident_identify_media( <?php echo $lrow[ 'idmedia' ]; ?> );' type='button' id='bIdentify' name='bIdentify' value='<?php echo get_msg( 'MENU_IDENTIFY', FALSE ); ?>' />
                <input onclick='ident_preview_media( <?php echo $lrow[ 'idmedia' ]; ?>, 0 );' type='button' id='bIdentifyP' name='bIdentifyP' value='Preview' />
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
    }else{
        echo get_msg( 'DEF_EMPTYLIST' );
    }
?>
