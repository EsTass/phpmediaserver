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
        $FOLDERNAME = basename( dirname( $media[ 0 ][ 'file' ] ) );
        $TITLE = clean_filename( $FILENAME );
        $TITLEFOLDER = clean_filename( $FOLDERNAME );
        $FILE = $media[ 0 ][ 'file' ];
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
            
?>
<script type="text/javascript">
$(function () {
    
});
function ident_rs_preview(){
    //$( '#fElementIdentRS #action' ).val( 'identifyrsa' );
    $( '#fElementIdentRS #preview' ).val( '1' );
    var url = '<?php getURL(); ?>?' + $( '#fElementIdentRS' ).serialize();
    $( '#dResultidentS' ).html( '' );
    $( '#dResultidentE' ).html( '' );
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#dResultidentS' ).html( data );
        loading_hide();
    });
    
    return false;
}
function ident_rs(){
    //$( '#fElementIdentRS #action' ).val( 'identifyrsa' );
    $( '#fElementIdentRS #preview' ).val( '0' );
    var url = '<?php getURL(); ?>?' + $( '#fElementIdentRS' ).serialize();
    $( '#dResultidentS' ).html( '' );
    $( '#dResultidentE' ).html( '' );
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#dResultidentS' ).html( data );
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
            <th>Assign Title (my_db)</th>
            <th>Season</th>
            <th>Episode</th>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
        <tr>
            <td>
                <input type='text' id='filename' name='filename' value='<?php echo $FILENAME; ?>' style='width:80%;height:60%;' onkeypress="return event.keyCode != 13;" />
            </td>
            <td>
                <input type='text' id='atitle' name='atitle' value='<?php echo $TITLE; ?>' style='width:80%;height:60%;' onkeypress="return event.keyCode != 13;" />
            </td>
            <td>
                <select id='season' name='season'>
                    <option value=''>Movie (no season)</option>
                    <option value='firstnumber1'>First Number (0)</option>
                    <option value='firstnumber2'>First Number (00)</option>
                    <option value='firstnumber3'>First Number (000)</option>
                    <option value='firstnumber0'>First Number (XX0)</option>
                    <option value='secondnumber1'>Second Number (0)</option>
                    <option value='secondnumber2'>Second Number (00)</option>
                    <option value='secondnumber3'>Second Number (000)</option>
                    <option value='secondnumber0'>Second Number (XX0)</option>
                    <option value='thirdlynumber1'>Thirdly Number (0)</option>
                    <option value='thirdlynumber2'>Thirdly Number (00)</option>
                    <option value='thirdlynumber3'>Thirdly Number (000)</option>
                    <option value='thirdlynumber0'>Thirdly Number (XX0)</option>
                    <?php for( $x = 1; $x < $maxseason; $x++ ){  ?>
                    <option value='SFixed<?php echo $x; ?>'>Fixed <?php echo $x; ?></option>
                    <?php } ?>
                </select>
                <br />
                <br />
                <input list="seasonrel" type='text' id='seasonre' name='seasonre' value='' style='width:80%;height:60%;' onkeypress="return event.keyCode != 13;" placeholder="Forced RegExp" />
                <datalist id="seasonrel">
                    <option value="[sS]()\d{1,2})[xXeE]\d{1,2}">
                    <option value="Season.(\d{2})">
                    <option value="S.{1,5}\d{1}(\d{2})">
                    <option value="Season.{1,2}(\d{2})">
                </datalist>
            </td>
            <td>
                <select id='episode' name='episode'>
                    <option value=''>Movie (no chapter)</option>
                    <option value='firstnumber1'>First Number (0)</option>
                    <option value='firstnumber2'>First Number (00)</option>
                    <option value='firstnumber3'>First Number (000)</option>
                    <option value='firstnumber0'>First Number (XX0)</option>
                    <option value='secondnumber1'>Second Number (0)</option>
                    <option value='secondnumber2'>Second Number (00)</option>
                    <option value='secondnumber3'>Second Number (000)</option>
                    <option value='secondnumber0'>Second Number (XX0)</option>
                    <option value='thirdlynumber1'>Thirdly Number (0)</option>
                    <option value='thirdlynumber2'>Thirdly Number (00)</option>
                    <option value='thirdlynumber3'>Thirdly Number (000)</option>
                    <option value='thirdlynumber0'>Thirdly Number (XX0)</option>
                    <?php for( $x = 1; $x < $maxchapter; $x++ ){  ?>
                    <option value='EFixed<?php echo $x; ?>'>Fixed <?php echo $x; ?></option>
                    <?php } ?>
                </select>
                <br />
                <br />
                <input list="episoderel" type='text' id='episodere' name='episodere' value='' style='width:80%;height:60%;' onkeypress="return event.keyCode != 13;" placeholder="Forced RegExp" />
                <datalist id="episoderel">
                    <option value="[sS]\d{1,2}[xXeE](\d{1,2})">
                    <option value="Cap\.(\d{2})">
                    <option value="Cap\.\d{1}(\d{2})">
                    <option value="Episode.{1,2}(\d{2})">
                </datalist>
            </td>
            <td>
                <input onclick='ident_rs_preview();' type='button' id='bPreview' name='bPreview' value='Preview' />
                <input onclick='ident_rs();' type='button' id='bActionRS' name='bActionRS' value='Identify' />
            </td>
        </tr>
    </table>
       
</form>

<?php
        }
    }else{
        echo get_msg( 'DEF_NOTEXIST' );
    }
?>
