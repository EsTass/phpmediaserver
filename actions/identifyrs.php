<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	if( array_key_exists( 'idmedia', $G_DATA ) 
	&& is_numeric( $G_DATA[ 'idmedia' ] )
	){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        echo "Invalid ID: idmedia";
        die();
	}
	
	$SEASON = FALSE;
    $EPISODE = FALSE;
	
	if( defined( 'MEDIA_CHAPTERS_MAXSEASON' ) ){
        $maxseason = MEDIA_CHAPTERS_MAXSEASON;
	}else{
        $maxseason = 30;
	}
	
	if( defined( 'MEDIA_CHAPTERS_MAXCHAPTER' ) ){
        $maxchapter = MEDIA_CHAPTERS_MAXCHAPTER;
	}else{
        $maxchapter = 250;
	}
	
	if( is_numeric( $IDMEDIA )
	&& ( $media = sqlite_media_getdata( $IDMEDIA ) ) != FALSE 
	&& count( $media ) > 0
	){
        $FILENAME = basename( $media[ 0 ][ 'file' ] );
        $FILENAMEBASE = $FILENAME;
        $FOLDERNAME = basename( dirname( $media[ 0 ][ 'file' ] ) );
        $TITLE = clean_filename( $FILENAME );
        $TITLEFOLDER = clean_filename( $FOLDERNAME );
        $FILE = $media[ 0 ][ 'file' ];
        $SEASONV = '';
        $SEASONVRE = '';
        $EPISODEV = '';
        $EPISODEVRE = '';
        
        //CLEAN INEXISTENT
        if( !file_exists( $FILE ) ){
            echo get_msg( 'DEF_FILENOTEXIST' );
            if( sqlite_media_delete( $IDMEDIA ) ){
                echo get_msg( 'DEF_DELETED' );
            }else{
                echo get_msg( 'DEF_DELETED_ERROR' );
            }
            //echo reloadJS();
            //header("Refresh: 2");
        }else{
            //Check folder and title for owndb ident and select more valid
            $imdb = FALSE;
            $movies = TRUE;
            if( ( $d = get_media_chapter( $FOLDERNAME ) ) == FALSE ){
		$d = array();
                $d[ 0 ] = FALSE;
                $d[ 1 ] = FALSE;
                $movies = FALSE;
            }
            if( ( $d2 = get_media_chapter( $FILENAME ) ) != FALSE ){
                $d[ 0 ] = $d2[ 0 ];
                $d[ 1 ] = $d2[ 1 ];
                $movies = FALSE;
            }
            if( $FOLDERNAME != basename( PPATH_DOWNLOADS ) 
            && ( $dorw = ident_detect_file_db( $FILE, $TITLEFOLDER, $movies, $imdb, $d[ 0 ], $d[ 1 ] ) ) != FALSE
            && is_array( $dorw )
            && array_key_exists( 'data', $dorw )
            && array_key_exists( 'idmediainfo', $dorw[ 'data' ] )
            ){
                $TITLE = $TITLEFOLDER;
                if( $d[ 0 ] != FALSE ){
                    $SEASON = (int)$d[ 0 ];
                    $EPISODE = (int)$d[ 1 ];
                    $TITLE = clean_media_chapter( $TITLE, $SEASON . 'x' . sprintf( '%02d', $EPISODE ) );
                }
                //$TITLE .= ' ' . $SEASON . 'x' . sprintf( '%02d', $EPISODE );
            }elseif( ( $d = get_media_chapter( $FILENAME ) ) != FALSE
            ){
                $SEASON = (int)$d[ 0 ];
                $EPISODE = (int)$d[ 1 ];
                $TITLE = clean_media_chapter( $TITLE, $SEASON . 'x' . sprintf( '%02d', $EPISODE ) );
                //$TITLE .= ' ' . $SEASON . 'x' . sprintf( '%02d', $EPISODE );
            }
            
            //Combo data
            $combo_seasons_rules = scrapp_irules_seasons_list( $maxseason );
            $combo_episodes_rules = scrapp_irules_episodes_list( $maxchapter );
            
            //Search previous sets and force rules if finded
            if( ( $pdata = scrap_irules_prev( $FILENAME, $media[ 0 ][ 'file' ], $TITLE, FALSE ) ) != FALSE
            || ( $pdata = scrap_irules_prev_n( $FILENAME, $media[ 0 ][ 'file' ], $TITLE, FALSE ) ) != FALSE 
            ){
                $FILENAME = $pdata[ 'filename' ];
                $TITLE = $pdata[ 'atitle' ];
                $SEASONV = $pdata[ 'season' ];
                $SEASONVRE = $pdata[ 'seasonrel' ];
                $EPISODEV = $pdata[ 'episode' ];
                $EPISODEVRE = $pdata[ 'episoderel' ];
            }
            
?>
<script type="text/javascript">
$(function () {
    
});
function ident_rs_preview(){
    //$( '#fElementIdentRS #action' ).val( 'identifyrsa' );
    $( '#fElementIdentRS #preview' ).val( '1' );
    var url = '<?php getURL(); ?>?' + $( '#fElementIdentRS' ).serialize();
    $( '#fElementIdentRSR' ).html( '' );
    $( '#dResultidentE' ).html( '' );
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#fElementIdentRSR' ).html( data );
        loading_hide();
    });
    
    return false;
}
function ident_rs(){
    //$( '#fElementIdentRS #action' ).val( 'identifyrsa' );
    $( '#fElementIdentRS #preview' ).val( '0' );
    var url = '<?php getURL(); ?>?' + $( '#fElementIdentRS' ).serialize();
    $( '#fElementIdentRSR' ).html( '' );
    $( '#dResultidentE' ).html( '' );
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#fElementIdentRSR' ).html( data );
        loading_hide();
    });
    
    return false;
}
</script>

<br />

<form id='fElementIdentRS'>
    
    <input type='hidden' id='r' name='r' value='r' />
    <input type='hidden' id='action' name='action' value='identifyrsa' />
    <input type='hidden' id='preview' name='preview' value='1' />
    <input type='hidden' id='idmedia' name='idmedia' value='<?php echo $IDMEDIA; ?>' />
    
    <table class='tList' style='width:100%;margin: auto;'>
        <tr>
            <td colspan='100'></td>
        </tr>
        <tr>
            <th>Search Filename</th>
            <th>Assign Title/imdbid (my_db)</th>
            <th>Season</th>
            <th>Episode</th>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
        <tr>
            <td>
                <p><?php echo $FILENAMEBASE; ?></p>
                <input type='text' id='filename' name='filename' value='<?php echo $FILENAME; ?>' style='width:80%;height:60%;' onkeypress="return event.keyCode != 13;" />
            </td>
            <td>
                <input type='text' id='atitle' name='atitle' value='<?php echo $TITLE; ?>' 
                style='width:80%;height:60%;' 
                onkeyup="autocomplete_search( this, 'atitle-dlist' );return false;"
                list="atitle-dlist"
                autocomplete="off"
                />
                <datalist id="atitle-dlist"></datalist>
            </td>
            <td>
                <select id='season' name='season'>
                    <?php 
                        foreach( $combo_seasons_rules AS $k => $t ){  
                            if( $SEASONV == $k ){
                                $selected = ' selected';
                            }else{
                                $selected = '';
                            }
                    ?>
                    <option value='<?php echo $k; ?>' <?php echo $selected; ?>><?php echo $t; ?></option>
                    <?php } ?>
                </select>
                <br />
                <br />
                <input list="seasonrel" type='text' id='seasonre' name='seasonre' value='<?php echo $SEASONVRE; ?>' style='width:80%;height:60%;' onkeypress="return event.keyCode != 13;" placeholder="Forced RegExp" />
                <datalist id="seasonrel">
                    <option value="[sS](\d{1,2})[xXeE]\d{1,2}">
                    <option value="Season.(\d{2})">
                    <option value="S.{1,5}\d{1}(\d{2})">
                    <option value="Season.{1,2}(\d{2})">
                    <option value="(\d{1})\d{2}">
                </datalist>
            </td>
            <td>
                <select id='episode' name='episode'>
                    <?php 
                        foreach( $combo_episodes_rules AS $k => $t ){
                            if( $EPISODEV == $k ){
                                $selected = ' selected';
                            }else{
                                $selected = '';
                            }
                    ?>
                    <option value='<?php echo $k; ?>' <?php echo $selected; ?>><?php echo $t; ?></option>
                    <?php } ?>
                </select>
                <br />
                <br />
                <input list="episoderel" type='text' id='episodere' name='episodere' value='<?php echo $EPISODEVRE; ?>' style='width:80%;height:60%;' onkeypress="return event.keyCode != 13;" placeholder="Forced RegExp" />
                <datalist id="episoderel">
                    <option value="[sS]\d{1,2}[xXeE](\d{1,2})">
                    <option value="Cap\.(\d{2})">
                    <option value="Cap\.\d{1}(\d{2})">
                    <option value="Episode.{1,2}(\d{2})">
                    <option value="\d{1}(\d{2})">
                </datalist>
            </td>
            <td>
                <input onclick='ident_rs_preview();' type='button' id='bPreview' name='bPreview' value='Preview' />
                <input onclick='ident_rs();' type='button' id='bActionRS' name='bActionRS' value='Identify' />
            </td>
        </tr>
        <tr>
            <td colspan='100'><div id='fElementIdentRSR'></div></td>
        </tr>
        <tr>
            <td colspan='100'></td>
        </tr>
    </table>
       
</form>

<?php
        }
    }else{
        echo get_msg( 'DEF_NOTEXIST' );
    }
?>
