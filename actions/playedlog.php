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
        'user' => 'Usuario',
        'date' => 'date',
        'now' => 'Now',
        'max' => 'Max',
        'title' => 'Title',
        'season' => 'Season',
        'episode' => 'Episode',
	);
	
	if( ( $edata = sqlite_played_getdata_ext( FALSE, $G_SEARCH, FALSE ) ) 
	&& count( $edata ) > 0
	){
?>

<script type="text/javascript">
$(function () {
    
});
function log_delete_played( idmedia, user ){
    var url = '<?php getURL(); ?>?' + $( '#fElementIdent' ).serialize();
    url += '&idmedia=' + idmedia;
    url += '&user=' + user;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
</script>

<div id='dResultIdent'></div>

<br />

<form id='fElementIdent'>
    
    <input type='hidden' id='r' name='r' value='r' />
    <input type='hidden' id='action' name='action' value='playeddelete' />
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
            <td><input onclick='log_delete_played( <?php echo $lrow[ 'idmedia' ]; ?>, "<?php echo $lrow[ 'user' ]; ?>" );' type='button' id='bLogDelete' name='bLogDelete' value='<?php echo get_msg( 'MENU_DELETE', FALSE ); ?>' /></td>
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
