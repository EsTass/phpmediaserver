<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	
	if( isset( $G_WEBSCRAPPER )
	&& count( $G_WEBSCRAPPER ) > 0 
	){
?>

<script type="text/javascript">
$(function () {
    
});
function webscrapper_paste(){
    var url = '<?php echo getURLBase(); ?>?r=r&' + $( '#fElementWebScrapp' ).serialize();
    loading_show();
    $( '#dResultWebScrapp' ).html( '' );
    $( '#dResultWebScrappAdd' ).html( '' );
    $.get( url )
    .done( function( data ){
        scrollbottom();
        $( '#dResultWebScrapp' ).html( data );
        loading_hide();
    });
    
    return false;
}
</script>

<form id='fElementWebScrapp'>
    
    <input type='hidden' id='r' name='r' value='r' />
    <input type='hidden' id='pass' name='pass' value='0' />
    <input type='hidden' id='action' name='action' value='webscrappastea' />
    <table class='tList' style='width:100%;margin: auto;'>
        <tr>
            <td colspan='100'></td>
        </tr>
        <tr>
            <td>
                <?php echo get_msg( 'WEBSCRAP_PASTELINKS', FALSE ); ?>
            </td>
            <td class='tCenter'>
                <input onclick='webscrapper_paste();' type='button' id='bWebScrapperPaste' name='bWebScrapperPaste' value='<?php echo get_msg( 'WEBSCRAP_PASTELINKS', FALSE ); ?>' />
            </td>
        </tr>
        <tr>
            <td colspan=100>
                <textarea class='taWebScrapLinks' type='text' name='links' id='links' value=''></textarea>
            </td>
        </tr>
        <tr>
            <td colspan=100>            
                <div id='dResultWebScrapp'></div>
            </td>
        </tr>
    </table>
	
</form>
<?php
    }else{
        echo get_msg( 'WEBSCRAP_NOTHING' );
    }
?>
