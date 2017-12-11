<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//links
	//pass
	
	if( array_key_exists( 'pass', $G_DATA ) ){
        $PASS = $G_DATA[ 'pass' ];
	}else{
        $PASS = 0;
	}
	
	if( array_key_exists( 'links', $G_DATA ) 
	&& strlen( $G_DATA[ 'links' ] ) > 3
	){
        $LINKS = $G_DATA[ 'links' ];
	}else{
        $LINKS = '';
	}
	
	if( ( $urls = extract_links_all( $LINKS ) ) != FALSE 
	&& count( $urls ) > 0
	){
        
?>
    <table class='tList'>
        <tr>
            <th colspan='100' class='tCenter'><?php echo get_msg( 'IDENT_DETECTED' ); ?></th>
        </tr>
        <?php  
            foreach( $urls AS $href ){
                $wsTitle = get_msg( 'WEBSCRAP_ADDKO', FALSE );
                $wsResult = get_msg( 'WEBSCRAP_NOTHING', FALSE );
                $filetitle = '';
                if( startsWith( $href, 'ed2k:' ) ){
                    amuleAdd( $href );
                    $wsResult = get_msg( 'WEBSCRAP_ADDOK', FALSE ) . $href;
                    $wsTitle = extract_elinks_title( $href );
                }elseif( startsWith( $href, 'magnet:' ) ){
                    magnetAdd( $href );
                    $wsResult = get_msg( 'WEBSCRAP_ADDOK', FALSE ) . $href;
                    $wsTitle = extract_magnets_title( $href );
                }else{
                
                    foreach( $G_WEBSCRAPPER AS $wsident => $wsdata ){
                        if( array_key_exists( 'searchdata', $wsdata ) 
                        && is_array( $wsdata[ 'searchdata' ] )
                        && array_key_exists( 'passdata', $wsdata ) 
                        && is_array( $wsdata[ 'passdata' ] )
                        &&
                        (
                            (
                            array_key_exists( 'linksappend', $wsdata[ 'searchdata' ] )
                            && strlen( $wsdata[ 'searchdata' ][ 'linksappend' ] ) > 0
                            && startsWith( $href, $wsdata[ 'searchdata' ][ 'linksappend' ] )
                            ) || (
                            array_key_exists( 'urlbase', $wsdata[ 'searchdata' ] )
                            && strlen( $wsdata[ 'searchdata' ][ 'urlbase' ] ) > 0
                            && startsWith( $href, $wsdata[ 'searchdata' ][ 'urlbase' ] )
                            ) || (
                            array_key_exists( 0, $wsdata[ 'passdata' ] )
                            && is_array( $wsdata[ 'passdata' ][ 0 ] )
                            && array_key_exists( 'linksappend', $wsdata[ 'passdata' ][ 0 ] )
                            && strlen( $wsdata[ 'passdata' ][ 0 ][ 'linksappend' ] ) > 0
                            && startsWith( $href, $wsdata[ 'searchdata' ][ 0 ][ 'linksappend' ] )
                            ) || (
                            array_key_exists( 0, $wsdata[ 'passdata' ] )
                            && is_array( $wsdata[ 'passdata' ][ 0 ] )
                            && array_key_exists( 'urlbase', $wsdata[ 'passdata' ][ 0 ] )
                            && strlen( $wsdata[ 'passdata' ][ 0 ][ 'urlbase' ] ) > 0
                            && startsWith( $href, $wsdata[ 'searchdata' ][ 0 ][ 'urlbase' ] )
                            )
                        )
                        ){
                            $wsTitle = $wsdata[ 'title' ];
                            $filetitle = $wsident . '_' . date( 'YmdHis' ) . '_' . getRandomString( 6 );
                            if( webscrapp_pass( $wsident, 0, $href, $filetitle ) ){
                                $wsResult = get_msg( 'WEBSCRAP_ADDOK', FALSE ) . $href;
                            }else{
                                $wsResult = get_msg( 'WEBSCRAP_ADDKO', FALSE ) . $href;
                            }
                            break;
                        }
                    }
                }
        ?>
        <tr>
            <td class='tCenter'>
                <?php echo $href; ?>
            </td>
            <td class='tCenter'>
                <?php echo $wsTitle; ?>
            </td>
            <td class='tCenter'>
                <?php echo $wsResult; ?>
            </td>
        </tr>
        <?php  
            }
        ?>
    </table>
<?php
        
	}elseif( is_array( $urls ) 
	&& count( $urls ) == 0
	){
        echo "<br />";
        echo get_msg( 'DEF_EMPTYLIST', FALSE );
        var_dump( $urls );
	}else{
        echo "<br />";
        echo get_msg( 'WEBSCRAP_SEARCH_ERROR', FALSE );
	}
?>
