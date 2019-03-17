<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmedia
	
	if( array_key_exists( 'idmediainfo', $G_DATA ) 
	&& is_numeric( $G_DATA[ 'idmediainfo' ] )
	&& ( $edata = sqlite_mediainfo_getdata( $G_DATA[ 'idmediainfo' ] ) ) != FALSE
	&& is_array( $edata )
	&& array_key_exists( 0, $edata )
	){
        $edata = $edata[ 0 ];
        $IDMEDIAINFO = $G_DATA[ 'idmediainfo' ];
	}else{
        echo "Invalid ID: idmediainfo";
        die();
	}
	
	//Check Update action
	if( array_key_exists( 'saction', $G_DATA ) ){
        switch ( $G_DATA[ 'saction' ] ) {
            case 'update':
                $midata = array();
                foreach( $G_MEDIAINFO AS $k => $v ){
                    if( array_key_exists( $k, $G_DATA ) 
                    && strlen( $G_DATA[ $k ] ) > 0
                    ){
                        $midata[ $k ] = $G_DATA[ $k ];
                    }elseif( array_key_exists( $k, $edata ) ){
                        $midata[ $k ] = $edata[ $k ];
                    }else{
                        $midata[ $k ] = '';
                    }
                }
                $midata[ 'dateadded' ] = date( 'Y-m-d H:i:s' );
                if( sqlite_mediainfo_update( $midata ) ){
                    echo "<br />Update Element: " . $midata[ 'title' ];
                }else{
                    echo "<br />Error updating data: " . $midata[ 'title' ];
                }
            break;
        }
        exit( 0 );
	}
	
	//Show edit form
	
?>

<script type="text/javascript">
$(function () {
    
});

function new_mediainfo_save(){
    var url = '<?php getURL(); ?>';
    var data = $( '#fElementMIEdit' ).serialize();
    loading_show();
    $.post( url, data )
    .done( function( data ){
        scrolltop();
        $( '#dResultMIEdit' ).html( data );
        loading_hide();
    });
    
    return false;
}
</script>

<form id='fElementMIEdit'>
    
    <input type='hidden' id='r' name='r' value='r' />
    <input type='hidden' id='action' name='action' value='mediainfoedit' />
    <input type='hidden' id='saction' name='saction' value='update' />
    <input type='hidden' id='idmediainfo' name='idmediainfo' value='<?php echo $IDMEDIAINFO; ?>' />
    <table class='tList' style='width:80%;margin: auto;'>
        <tr>
            <td colspan=100 id='dResultMIEdit'></td>
        </tr>
        <tr>
            <th><?php echo get_msg( 'MENU_TITLE', FALSE ); ?></th>
            <th>Plot</th>
            <th>Date Premier</th>
            <th><?php echo get_msg( 'MENU_SEASON', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_EPISODE', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_TITLE', FALSE ); ?> <?php echo get_msg( 'MENU_EPISODE', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_YEAR', FALSE ); ?></th>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
        <tr>
            <td>
                <input id='title' name='title' value='<?php echo $edata[ 'title' ]; ?>' />
            </td>
            <td>
                <textarea id='plot' name='plot'><?php echo $edata[ 'plot' ]; ?></textarea>
            </td>
            <td>
                <input type='string' id='sorttitle' name='sorttitle' value='<?php echo $edata[ 'sorttitle' ]; ?>' placeholder="<?php date( 'Y-m-d' ); ?>" />
            </td>
            <td>
                <input type='number' id='season' name='season' value='<?php echo $edata[ 'season' ]; ?>' placeholder="1" />
            </td>
            <td>
                <input type='number' id='episode' name='episode' value='<?php echo $edata[ 'episode' ]; ?>' placeholder="1" />
            </td>
            <td>
                <input id='titleepisode' name='titleepisode' value='<?php echo $edata[ 'titleepisode' ]; ?>' />
            </td>
            <td>
                <input type='number' id='year' name='year' value='<?php echo $edata[ 'year' ]; ?>' placeholder="<?php date( 'Y' ); ?>" />
            </td>
            <td>
                <input type='button' id='bMediaInfoUpdateA' name='bMediaInfoUpdateA' value='Update' onclick='new_mediainfo_save();' />
            </td>
        </tr>
        <tr>
            <th>Rating</th>
            <th>Votes</th>
            <th>mpaa</th>
            <th>tagline</th>
            <th><?php echo get_msg( 'MENU_GENRE', FALSE ); ?>(, separated)</th>
            <th><?php echo get_msg( 'INFO_ACTORS', FALSE ); ?>(, separated)</th>
        </tr>
        <tr>
            <td>
                <input type='number' id='rating' name='rating' value='<?php echo $edata[ 'rating' ]; ?>' placeholder="5.0" />
            </td>
            <td>
                <input type='number' id='votes' name='votes' value='<?php echo $edata[ 'votes' ]; ?>' placeholder="111" />
            </td>
            <td>
                <input type='number' id='mpaa' name='mpaa' value='<?php echo $edata[ 'mpaa' ]; ?>' placeholder="TV-14, TV-PG, ..." />
            </td>
            <td>
                <textarea type='text' id='tagline' name='tagline' placeholder="Tiny plot"><?php echo $edata[ 'tagline' ]; ?></textarea>
            </td>
            <td>
                <textarea type='text' id='genre' name='genre' placeholder="<?php if( is_array( O_MENU_GENRES ) ) echo implode( ', ', array_keys( O_MENU_GENRES ) ); ?>"><?php echo $edata[ 'genre' ]; ?></textarea>
            </td>
            <td>
                <textarea type='text' id='actor' name='actor' placeholder="Actor1, Actor2, ..."><?php echo $edata[ 'actor' ]; ?></textarea>
            </td>
        </tr>
    </table>
</form>
