<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	//check_mod_admin();
	
	//search
	//pass
	//webscrapper
	
	if( array_key_exists( 'pass', $G_DATA ) ){
        $PASS = $G_DATA[ 'pass' ];
	}else{
        $PASS = 0;
	}
	
	if( array_key_exists( 'search', $G_DATA ) 
	&& strlen( $G_DATA[ 'search' ] ) > 3
	){
        $SEARCH = $G_DATA[ 'search' ];
	}else{
        $SEARCH = '';
	}
	
	/*
	if( array_key_exists( 'webscrapper', $G_DATA ) 
	&& array_key_exists( $G_DATA[ 'webscrapper' ], $G_WEBSCRAPPER )
	){
        $SCRAPPER = $G_DATA[ 'webscrapper' ];
	}else{
        echo "Invalid scrapper: ";
        die();
	}
	*/
	$SCRAPPER = PPATH_WEBSCRAP_SEARCH;
	
	//echo "<br />";
	//echo get_msg( 'MENU_SEARCH', FALSE ) . ': ' . $SCRAPPER . ' => ' . $SEARCH;
	$links = array();
	if( isset( $G_WEBSCRAPPER )
	&& $SCRAPPER
	&& array_key_exists( $SCRAPPER, $G_WEBSCRAPPER )
	&& strlen( $SEARCH ) > 0
	&& ( $links = webscrapp_search( $SCRAPPER, $SEARCH ) ) != FALSE 
	&& count( $links ) > 0
	){
	
        $urlposter = getURLImg( FALSE, 1, 'poster' );
    
?>


<script type="text/javascript">
$(function () {
    
});
function webscrap_add( scrapper, eurl, title ){
    var url = '<?php echo getURLBase(); ?>?r=r';
    url += '&action=listdownloadsa';
    url += '&webscrapper=' + scrapper;
    url += '&title=' + title;
    url += '&pass=0';
    url += '&url=' + eurl;
    $( '#newdownloadsresult' ).html( '' );
    loading_show();
    $.get( url )
    .done( function( data ){
        scrolltop();
        $( '#newdownloadsresult' ).html( data );
        loading_hide();
    });
    
    return false;
}

</script>

    <div class='boxList'>
        <h2 class='tCenter'><?php echo $SEARCH; ?></h2>
        
    <?php  
        foreach( $links AS $t => $href ){
            $t = str_replace( "\t", '', $t );
            $t = str_replace( "\n", '', $t );
            $t = str_replace( "\r", '', $t );
            //remove exist
            if( stripos( $t, 'EXIST:' ) === FALSE ){
    ?>
        <div class='listElement' onclick='webscrap_add( "<?php echo addslashes( $SCRAPPER ); ?>", "<?php echo addslashes( $href ); ?>", "<?php echo addslashes( $t ); ?>" )'>
            <img class='listElementImg' src='<?php echo $urlposter; ?>' title='<?php echo $t; ?>' />
            <span class=''><?php echo $t; ?></span>
        </div>
    <?php  
            }
        }
    ?>
    
<?php
        
	}elseif( is_array( $links ) 
	&& count( $links ) == 0
	){
        echo "<br />";
        echo get_msg( 'DEF_EMPTYLIST', FALSE );
	}else{
        echo "<br />";
        //echo get_msg( 'WEBSCRAP_SEARCH_ERROR', FALSE ) . '' . $SCRAPPER;
        echo get_msg( 'DEF_EMPTYLIST', FALSE );
	}
?>
