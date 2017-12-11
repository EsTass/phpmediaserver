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
function webscrapper_search(){
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
function webscrap_add( scrapper, eurl, title ){
    var url = '<?php echo getURLBase(); ?>?r=r';
    url += '&action=webscrapa';
    url += '&webscrapper=' + scrapper;
    url += '&title=' + title;
    url += '&pass=0';
    url += '&url=' + eurl;
    $( '#dResultWebScrappAdd' ).html( '' );
    loading_show();
    $.get( url )
    .done( function( data ){
        scrollbottom();
        $( '#dResultWebScrappAdd' ).html( data );
        loading_hide();
    });
    
    return false;
}

</script>

<form id='fElementWebScrapp'>
    
    <input type='hidden' id='r' name='r' value='r' />
    <input type='hidden' id='pass' name='pass' value='0' />
    <input type='hidden' id='action' name='action' value='webscraps' />
    <table class='tList' style='width:100%;margin: auto;'>
        <tr>
            <td colspan='100'></td>
        </tr>
        <tr>
            <th></th>
            <th><?php echo get_msg( 'MENU_SEARCH', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_SCRAPPER', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type='text' name='search' id='seach' value='' onkeypress="return event.keyCode != 13;" />
            </td>
            <td>
                <select id='webscrapper' name='webscrapper'>
                    <?php
                        foreach( $G_WEBSCRAPPER AS $k => $v ){
                            $selected = '';
                    ?>
                    <option <?php echo $selected; ?> value='<?php echo $k; ?>'><?php echo $v[ 'title' ]; ?></option>
                    <?php
                        }
                    ?>
                </select>
            </td>
            <td>
                <input onclick='webscrapper_search();' type='button' id='bWebScrapperSearch' name='bWebScrapperSearch' value='<?php echo get_msg( 'MENU_SEARCH', FALSE ); ?>' />
            </td>
        </tr>
        <tr>
            <td colspan=100>
                <div id='dResultWebScrapp'></div>
            </td>
        </tr>
        <tr>
            <td colspan=100>            
                <div id='dResultWebScrappAdd'></div>
            </td>
        </tr>
    </table>
	
</form>
<?php
    }else{
        echo get_msg( 'WEBSCRAP_NOTHING' );
    }
?>
