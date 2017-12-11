<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//
	
	if( ( $tlist = sqlite_mediainfo_getdata_titles( FALSE, 10000 ) ) != FALSE ){
        $MEDIAINFOLIST = array();
        foreach( $tlist AS $row ){
            $MEDIAINFOLIST[ $row[ 'idmediainfo' ] ] = $row[ 'title' ];
        }
	}else{
        $MEDIAINFOLIST = array();
	}
	
?>

<script type="text/javascript">
$(function () {
    
});
function join_mediainfo(){
    var url = '<?php echo getURLBase(); ?>?' + $( '#fElementJoin' ).serialize();
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#dResultJoin' ).html( data );
        loading_hide();
    });
    
    return false;
}
</script>

<br />

<form id='fElementJoin'>
    
    <input type='hidden' id='r' name='r' value='r' />
    <input type='hidden' id='action' name='action' value='mediainfojoina' />
    <table class='tList'>
        <tr>
            <td class='tCenter' colspan=100>
                Join 2 series/movies splited by diferent language 'Title'.
            </td>
        </tr>
        <tr>
            <th><?php echo get_msg( 'JOIN_REPLACETHIS', FALSE ); ?></th>
            <th><?php echo get_msg( 'JOIN_WHITTHIS', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
        <tr>
            <td>
                <select id='replacethis' name='replacethis'>
                    <?php
                        foreach( $MEDIAINFOLIST AS $idmediainfo => $title ){
                    ?>
                        <option value='<?php echo $idmediainfo; ?>'><?php echo $title; ?></option>
                    <?php
                        }
                    ?>
                </select>
            </td>
            <td>
                <select id='whiththis' name='whiththis'>
                    <?php
                        foreach( $MEDIAINFOLIST AS $idmediainfo => $title ){
                    ?>
                        <option value='<?php echo $idmediainfo; ?>'><?php echo $title; ?></option>
                    <?php
                        }
                    ?>
                </select>
            </td>
            <td>
                <input onclick='join_mediainfo();' type='button' id='bJoin' name='bJoin' value='<?php echo get_msg( 'JOIN_BUTTONREPLACE', FALSE ); ?>' />
            </td>
        </tr>
        <tr>
            <td colspan='100' id='dResultJoin'></td>
        </tr>
    </table>
	
</form>
<?php
    
?>

