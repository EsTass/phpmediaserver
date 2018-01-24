<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//VARS
	$G_WEBSPIDER = array(
        //'all' => 'ALL (INFINITE)?',
        'self' => 'Own Web (5)',
        'self1' => 'Own Web (5) +1 External',
        '0' => '0',
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
        '5' => '5',
	);
	
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
function webspider_search(){
    var url = '<?php echo getURLBase(); ?>?r=r&' + $( '#fElementWebScrapp' ).serialize();
    loading_show();
    $( '#dResultWebScrapp' ).html( '' );
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
    <input type='hidden' id='action' name='action' value='webspidera' />
    <table class='tList' style='width:100%;margin: auto;'>
        <tr>
            <td colspan='100'></td>
        </tr>
        <tr>
            <th></th>
            <th>URL</th>
            <th>DEEP</th>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type='text' name='url' id='url' value='' onkeypress="return event.keyCode != 13;" style='width:90%;margin: auto;'/>
            </td>
            <td>
                <select id='deep' name='deep'>
                    <?php
                        foreach( $G_WEBSPIDER AS $k => $v ){
                            $selected = '';
                    ?>
                    <option <?php echo $selected; ?> value='<?php echo $k; ?>'><?php echo $v; ?></option>
                    <?php
                        }
                    ?>
                </select>
            </td>
            <td>
                <input onclick='webspider_search();' type='button' id='bWebScrapperSearch' name='bWebScrapperSearch' value='<?php echo get_msg( 'MENU_SEARCH', FALSE ); ?>' />
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
