<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	if( ( $config = file_get_contents( PPATH_BASE . DS . 'config.php' ) ) ){
?>

<script type="text/javascript">
$(function () {
    
});
function config_save(){
    var url = '<?php echo getURLBase(); ?>';
    var data = $( '#fElementConfig' ).serialize();
    loading_show();
    $.post( url, data )
    .done( function( data ){
        scrolltop();
        $( '#dResultConfig' ).html( data );
        loading_hide();
    });
    
    return false;
}
</script>

<div id='dResultConfig'></div>

<br />

<form id='fElementConfig'>
    
    <input type='hidden' id='r' name='r' value='r' />
    <input type='hidden' id='action' name='action' value='configa' />
    <table class='tList' style='width:100%;margin: auto;'>
        <tr>
            <td class='tCenter'>
                IMPORTANT: on error web cant load and need to access file directy, delete new and rename backup 'config.php.backup'
                <input onclick='config_save();' type='button' id='bConfigUpdate' name='bConfigUpdate' value='<?php echo get_msg( 'MENU_UPDATE', FALSE ); ?>' />
            </td>
        </tr>
        <tr>
            <td class='widthFull heightFull'>
                <textarea id='config' name='config' class='taConfig widthFull heightFull'><?php echo $config; ?></textarea>
            </td>
        </tr>
    </table>
	
</form>
<?php
    }
?>

