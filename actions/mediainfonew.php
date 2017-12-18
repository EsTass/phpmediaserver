<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmedia
	
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        echo "Invalid ID: idmedia";
        die();
	}
	
	if( array_key_exists( 'title', $G_DATA ) ){
        $TITLE = $G_DATA[ 'title' ];
	}else{
        $TITLE = '';
	}
	
	//Show new form
	
?>

<script type="text/javascript">
$(function () {
    
});

function new_mediainfo_insert(){
    var url = '<?php getURL(); ?>';
    var data = $( '#fElementMINew' ).serialize();
    loading_show();
    $.post( url, data )
    .done( function( data ){
        scrolltop();
        $( '#dResultMINew' ).html( data );
        loading_hide();
    });
    
    return false;
}
</script>

<form id='fElementMINew'>
    
    <input type='hidden' id='r' name='r' value='r' />
    <input type='hidden' id='action' name='action' value='mediainfonewa' />
    <input type='hidden' id='idmedia' name='idmedia' value='<?php echo $IDMEDIA; ?>' />
    <table class='tList' style='width:80%;margin: auto;'>
        <tr>
            <td colspan=100 id='dResultMINew'></td>
        </tr>
        <tr>
            <th><?php echo get_msg( 'MENU_TITLE', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_TITLE', FALSE ); ?> <?php echo get_msg( 'MENU_EPISODE', FALSE ); ?></th>
            <th>Plot</th>
            <th>Date Premier</th>
            <th><?php echo get_msg( 'MENU_SEASON', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_EPISODE', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_YEAR', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_GENRE', FALSE ); ?>(, separated)</th>
            <th><?php echo get_msg( 'INFO_ACTORS', FALSE ); ?>(, separated)</th>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
        <tr>
            <td>
                <input id='title' name='title' value='<?php echo $TITLE; ?>' />
            </td>
            <td>
                <input id='episodetitle' name='episodetitle' value='' />
            </td>
            <td>
                <textarea id='plot' name='plot'></textarea>
            </td>
            <td>
                <input type='string' id='sorttitle' name='sorttitle' value='<?php echo date( 'Y-m-d' ); ?>' />
            </td>
            <td>
                <input type='number' id='season' name='season' value='' />
            </td>
            <td>
                <input type='number' id='episode' name='episode' value='' />
            </td>
            <td>
                <input type='number' id='year' name='year' value='' />
            </td>
            <td>
                <input type='text' id='genre' name='genre' value='' />
            </td>
            <td>
                <input type='text' id='actor' name='actor' value='' />
            </td>
            <td>
                <input type='button' id='bMediaInfoNewA' name='bMediaInfoNewA' value='Add' onclick='new_mediainfo_insert();' />
            </td>
        </tr>
    </table>
</form>
