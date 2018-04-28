<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
?>

<script type="text/javascript">
$(function () {
    
});
function action_spacerecover(){
    var url = '<?php getURL(); ?>?' + $( '#fRecoverSpace' ).serialize();
    $( '#dSpaceRecoverResult' ).html( '' );
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#dSpaceRecoverResult' ).html( data );
        loading_hide();
        //IMG LAZYLOAD
        $("img.lazy").Lazy();
    });
    
    return false;
}
</script>

<form id='fRecoverSpace'>
<div class='boxInfo'>
    <table class='widthFull'>
        <tr class='trSearch'>
            <td>
                Free Space: <?php echo formatSizeUnits( disk_free_space( PPATH_DOWNLOADS ) ); ?>
            </td>
        </tr>
        <tr class='trSearch'>
            <td>
                Mode: 
                <select id='action' name='action'>
                    <option value='mediaspaceclean'>Only Bigger files than</option>
                    <option value='mediaspacerecovera'>All Options</option>
                </select>
            </td>
            <td>
                <?php echo get_msg( 'MENU_SEARCH', FALSE ); ?>:
                <input type="text" name='search' id='search' value='' />
            </td>
            <td>
                Num. Files
                <input type="number" name='quantity' id='quantity' value='10' step="1" />
            </td>
            <td>
                Min. Size (Gb)
                <input type="number" name='size' id='size' value='4.0' step="0.1" />
            </td>
            <td>
                Min. Timelength (Minutes)
                <input type="number" name='minutes' id='minutes' value='60' step="10" />
            </td>
            <td>
                <?php echo get_msg( 'MENU_YEAR', FALSE ) ?>: 
                <select id='year1' name='year1'>
                    <option value='1900'>1900</option>
                    <?php for( $y = (int)date( 'Y' ) + 1; $y >= 1900; $y-- ){ ?>
                    <option value='<?php echo $y; ?>'><?php echo $y; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td>
                <?php echo get_msg( 'MENU_YEAR', FALSE ) ?>: 
                <select id='year2' name='year2'>
                    <option value='<?php echo ( date( 'Y' ) + 1 ); ?>'><?php echo ( date( 'Y' ) + 1 ); ?></option>
                    <?php for( $y = (int)date( 'Y' ) + 1; $y >= 1900; $y-- ){ ?>
                    <option value='<?php echo $y; ?>'><?php echo $y; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td>
                <?php echo get_msg( 'MENU_RATING', FALSE ) ?>: 
                <select id='rating' name='rating'>
                    <option value=''></option>
                    <?php for( $y = 10; $y > 0; $y-- ){ ?>
                    <option value='<?php echo $y; ?>'><?php echo $y; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td>
                <input type="checkbox" name="remove" id="remove" value="1" />Remove 
            </td>
            <td>
                <input type='hidden' id='r' name='r' value='r' />
                <input onclick='action_spacerecover();' type='button' id='bAction' name='bAction' value='<?php echo get_msg( 'MENU_HDDCLEAN', FALSE ) ?>' />
            </td>
        </tr>
        <tr>
            <td colspan=100>
                <div id='dSpaceRecoverResult'></div>
            </td>
        </tr>
    </table>
</div>
</form>
