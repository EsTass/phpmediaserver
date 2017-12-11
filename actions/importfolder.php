<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//folder
	//quantity
	
	if( array_key_exists( 'folder', $G_DATA ) ){
        $FOLDER = $G_DATA[ 'folder' ];
	}else{
        $FOLDER = '';
	}
	
	if( array_key_exists( 'quantity', $G_DATA ) ){
        $QUANTITY = $G_DATA[ 'quantity' ];
	}else{
        $QUANTITY = 10;
	}
	
?>

<script type="text/javascript">
$(function () {
    
});
function import_folder(){
    var url = '<?php echo getURLBase(); ?>?' + $( '#fElementImport' ).serialize();
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#dResultImport' ).html( data );
        loading_hide();
    });
    
    return false;
}
</script>

<br />

<form id='fElementImport'>
    
    <input type='hidden' id='r' name='r' value='r' />
    <input type='hidden' id='action' name='action' value='importfoldera' />
    <table class='tList'>
        <tr>
            <th><?php echo get_msg( 'MENU_FOLDER', FALSE ); ?> (SELECTEDFOLDER/Movie Name/files.[.nfo,mkv,jpg]) (SELECTEDFOLDER/Serie/ChaptersFiles[.nfo,mkv,jpg])</th>
            <th><?php echo get_msg( 'MENU_QUANTITY', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
        <tr>
            <td><input style='width:98%;' type='text' name='folder' id='folder' value='<?php echo PPATH_DOWNLOADS; ?>' onkeypress="return event.keyCode != 13;" /></td>
            <td><input style='width:98%;' type='number' name='quantity' id='quantity' value='<?php echo $QUANTITY; ?>' onkeypress="return event.keyCode != 13;" /></td>
            <td><input onclick='import_folder();' type='button' id='bImport' name='bImport' value='<?php echo get_msg( 'MENU_IMPORT', FALSE ); ?>' /></td>
        </tr>
        <tr>
            <td colspan='100' id='dResultImport'></td>
        </tr>
    </table>
	
</form>
<?php
    
?>
