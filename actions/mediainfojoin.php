<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//Show to join 2 titles by titles and year on mediainfo
	
	if( ( $tlist = sqlite_mediainfo_getdata_titles( FALSE, 100 ) ) != FALSE ){
        $MEDIAINFOLIST = array();
        foreach( $tlist AS $row ){
            $MEDIAINFOLIST[ $row[ 'idmediainfo' ] ] = $row[ 'title' ] . ' (' . $row[ 'year' ] . ')';
        }
	}else{
        $MEDIAINFOLIST = array();
	}

	//Show list of letters to filter combo titles
	//MODE extraction from titles
	//TEST titles with extra languajes
	/*
	$FILTERLETTERS = array();
    foreach( $MEDIAINFOLIST AS $idmediainfo => $title ){
        $l = substr( $title, 0, 1 );
        $l = strtoupper( $l );
        if( !in_array( $l, $FILTERLETTERS )
        ){
            $FILTERLETTERS[] = $l;
        }
    }
    asort( $FILTERLETTERS );
    */

	//Show list of letters to filter combo titles
	//MODE basic letters and any others
	$FILTERLETTERS = array_merge( range( 0, 9 ), range( 'A', 'Z' ) );
    $FILTERLETTERS[] = 'ANY_OTHER';

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
function join_load_titles( ide, letter ){
    var url = '<?php echo getURLBase(); ?>?r=r&action=mediainfojoint&letter=' + letter;
    loading_show();
    $.getJSON( url )
    .done( function( data ){
        scrolltop();
        $( '#' + ide ).find( 'option' ).remove();
        var listitems = '';
        $.each( data, function( key, value ){
            listitems += '<option value=' + key + '>' + value + '</option>';
        });
        $( '#' + ide ).append( listitems );
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
                Join 2 series/movies splited by diferent languaje 'Title'.
            </td>
        </tr>
        <tr>
            <th><?php echo get_msg( 'JOIN_REPLACETHIS', FALSE ); ?></th>
            <th><?php echo get_msg( 'JOIN_WHITTHIS', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
        <tr>
            <td>
                <?php
                    foreach( $FILTERLETTERS AS $letter ){
                ?>
                    <a href="#" onclick="join_load_titles( 'replacethis', '<?php echo $letter; ?>' );return false;"><?php echo $letter; ?></a>&nbsp;
                <?php
                    }
                ?>
            </td>
            <td>
                <?php
                    foreach( $FILTERLETTERS AS $letter ){
                ?>
                    <a href="#" onclick="join_load_titles( 'whiththis', '<?php echo $letter; ?>' );return false;"><?php echo $letter; ?></a>&nbsp;
                <?php
                    }
                ?>
            </td>
            </td>
            <td>

            </td>
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

