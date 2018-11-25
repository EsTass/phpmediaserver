<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	
	$ORDERBY = array(
        'dateadded' => get_msg( 'DEF_ELEMENTUPDATED', FALSE ),
        'title' => get_msg( 'MENU_TITLE', FALSE ),
        'year' => get_msg( 'MENU_YEAR', FALSE ),
        'rating' => get_msg( 'MENU_RATING', FALSE ),
        'sorttitle' => get_msg( 'LIST_TITLE_PREMIERE', FALSE ),
	);
	
?>

<script type="text/javascript">
$(function () {
    
});
function ident_search_list(){
    var url = '<?php getURL(); ?>?' + $( '#fSearchExt' ).serialize();
    $( '#dResultSearchExt' ).html( '' );
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#dResultSearchExt' ).html( data );
        loading_hide();
        //IMG LAZYLOAD
        $("img.lazy").Lazy();
    });
    
    return false;
}
</script>

<form id='fSearchExt' autocomplete="off" onsubmit="return false;">
<div class='boxInfo'>
    <br />
    <br />
    <table class='widthFull'>
        <tr class='trSearch'>
            <td>
                <?php echo get_msg( 'MENU_SEARCH', FALSE ) ?>
                <input name='search' id='search' value='' 
                list="search-dlist2" onkeyup="autocomplete_search( this, 'search-dlist2' );return false;" autocomplete="off"
                />
                <datalist id="search-dlist2"></datalist>
            </td>
            <td>
                <?php echo get_msg( 'MENU_YEAR', FALSE ) ?>: 
                <select id='year' name='year'>
                    <option value=''></option>
                    <?php for( $y = (int)date( 'Y' ) + 1; $y >= 1900; $y-- ){ ?>
                    <option value='<?php echo $y; ?>'><?php echo $y; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td>
                <?php echo get_msg( 'MENU_YEAR', FALSE ) ?>: 
                <select id='year2' name='year2'>
                    <option value=''></option>
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
                <?php echo get_msg( 'MENU_GENRE', FALSE ) ?>: 
                <select id='genre1' name='genre1'>
                    <option value=''></option>
                    <?php 
                        if( defined( 'O_MENU_GENRES' )
                        && is_array( O_MENU_GENRES )
                        ){
                            foreach( O_MENU_GENRES AS $g => $extrasearch ){
                    ?>
                    <option value='<?php echo $g; ?>'><?php echo $g; ?></option>
                    <?php 
                            }
                        } 
                    ?>
                </select>
            </td>
            <td>
                <?php echo get_msg( 'MENU_GENRE', FALSE ) ?>: 
                <select id='genre2' name='genre2'>
                    <option value=''></option>
                    <?php 
                        if( defined( 'O_MENU_GENRES' )
                        && is_array( O_MENU_GENRES )
                        ){
                            foreach( O_MENU_GENRES AS $g => $extrasearch ){
                    ?>
                    <option value='<?php echo $g; ?>'><?php echo $g; ?></option>
                    <?php 
                            }
                        } 
                    ?>
                </select>
            </td>
            <td>
                <?php echo get_msg( 'MENU_GENRE', FALSE ) ?>: 
                <select id='genre3' name='genre3'>
                    <option value=''></option>
                    <?php 
                        if( defined( 'O_MENU_GENRES' )
                        && is_array( O_MENU_GENRES )
                        ){
                            foreach( O_MENU_GENRES AS $g => $extrasearch ){
                    ?>
                    <option value='<?php echo $g; ?>'><?php echo $g; ?></option>
                    <?php 
                            }
                        } 
                    ?>
                </select>
            </td>
            <td>
                <?php echo get_msg( 'MENU_ORDERBY', FALSE ) ?>: 
                <select id='orderby' name='orderby'>
                    <?php 
                        foreach( $ORDERBY AS $id => $title){
                    ?>
                    <option value='<?php echo $id; ?>'><?php echo $title; ?></option>
                    <?php 
                        } 
                    ?>
                </select>
            </td>
            <td>    
                <input type='hidden' id='action' name='action' value='searcha' />
                <input type='hidden' id='r' name='r' value='r' />
                <input onclick='ident_search_list();' type='button' id='bSearch' name='bSearch' value='<?php echo get_msg( 'MENU_SEARCH', FALSE ); ?>' />
            </td>
        </tr>
        <tr>
            <td colspan=100>
                <div id='dResultSearchExt'></div>
            </td>
        </tr>
    </table>
</div>
</form>
