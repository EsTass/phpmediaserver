<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmedia
	//title
	//scrapper
	//stype
	//season
	//episode
	
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        echo "Invalid ID: idmedia";
        die();
	}
	
	if( array_key_exists( 'title', $G_DATA ) 
	&& strlen( $G_DATA[ 'title' ] ) > 2
	){
        $TITLE = $G_DATA[ 'title' ];
	}else{
        echo "Invalid Search: ";
        die();
	}
	
	if( array_key_exists( 'scrapper', $G_DATA ) 
	&& array_key_exists( $G_DATA[ 'scrapper' ], $G_SCRAPPERS )
	){
        $SCRAPPER = $G_DATA[ 'scrapper' ];
	}else{
        echo "Invalid scrapper: ";
        die();
	}
	
	if( array_key_exists( 'stype', $G_DATA ) 
	&& in_array( $G_DATA[ 'stype' ], $G_STYPE )
	){
        $STYPE = $G_DATA[ 'stype' ];
	}else{
        echo "Invalid Type: ";
        die();
	}
	
	if( array_key_exists( 'season', $G_DATA ) 
	&& (int)$G_DATA[ 'season' ] > 0
	){
        $SEASON = $G_DATA[ 'season' ];
	}else{
        $SEASON = FALSE;
	}
	
	if( array_key_exists( 'episode', $G_DATA ) 
	&& (int)$G_DATA[ 'episode' ] > 0
	){
        $EPISODE = $G_DATA[ 'episode' ];
	}else{
        $EPISODE = FALSE;
	}
	
	//SEARCHING FOR DATA
	
	if( ( $list = filebot_get_list_episodes( $TITLE ) ) != FALSE 
	&& is_array( $list )
	&& count( $list ) > 0
	){
        
?>
    <table class='tList'>
        <tr>
            <th colspan='100'><?php echo get_msg( 'IDENT_DETECTED' ); ?></th>
        </tr>
        <?php  
            $q = 0;
            foreach( $list AS $t ){
        ?>
        <tr>
            <td><a class='aIdentSearchResult' href='#' onclick='ident_list_set_title( "<?php echo addslashes( $t ); ?>" )'><?php echo $t; ?></a></td>
        </tr>
        <?php } ?>
    </table>
<?php
    }else{
        echo get_msg( 'IDENT_NOTDETECTED' );
    }
?>
