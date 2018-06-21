<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//search
	//action
	//action2
	//idmedialiveurls
	//action2=modif
	//idmedialiveurls
	//title
	//url
	//poster
	//action2=paste
	//listlinks
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	
	if( array_key_exists( 'action2', $G_DATA ) ){
        $G_ACTION2 = $G_DATA[ 'action2' ];
	}else{
        $G_ACTION2 = FALSE;
	}
	
	if( array_key_exists( 'idmedialiveurls', $G_DATA ) 
	&& is_numeric( $G_DATA[ 'idmedialiveurls' ] )
	){
        $G_IDMEDIALIVEURLS = $G_DATA[ 'idmedialiveurls' ];
	}else{
        $G_IDMEDIALIVEURLS = FALSE;
	}
	
	if( array_key_exists( 'title', $G_DATA ) ){
        $G_TITLE = $G_DATA[ 'title' ];
	}else{
        $G_TITLE = FALSE;
	}
	
	if( array_key_exists( 'url', $G_DATA ) ){
        $G_URL = $G_DATA[ 'url' ];
	}else{
        $G_URL = FALSE;
	}
	
	$FIELDS = array(
        'idmedialiveurls' => 'idmedialiveurls',
        'title' => 'Title',
        'url' => 'URL',
        'date' => 'DateAdded',
	);
	
	$SHOW_LIST = TRUE;
	$DATA = array();
	foreach( $FIELDS AS $f => $t ){
        $DATA[ $f ] = '';
    }
	if( $G_ACTION2 )
	switch( $G_ACTION2 ){
        case 'import':
            if( $G_IDMEDIALIVEURLS > 0
            && ( $dl = sqlite_medialiveurls_getdata( $G_IDMEDIALIVEURLS, 1 ) ) 
            && is_array( $dl )
            && array_key_exists( 0, $dl )
            ){
                $URLs_OK = 0;
                $URLs_DEL = 0;
                $URLs_DEL_E = 0;
                foreach( $dl AS $d ){
                    if( ( $ldata = @file_get_contents( $d[ 'url' ] ) ) != FALSE 
                    && strlen( $ldata ) > 0
                    ){
                        //echo get_msg( 'DEF_EXIST' );
                        //ADD URLS
                        $G_LISTLINKS = $ldata;
                        if( $G_LISTLINKS
                        && strlen( $G_LISTLINKS ) > 0
                        ){
                            $G_LISTLINKS = trim( $G_LISTLINKS );
                            $G_LISTLINKS = explode( PHP_EOL, $G_LISTLINKS );
                            $G_LISTLINKS = array_filter( $G_LISTLINKS, 'trim' );
                            $ltitle = '';
                            $URLs = 0;
                            $URLs_ERROR = 0;
                            $URLs_DUPLY = 0;
                            $LINES = count( $G_LISTLINKS );
                            foreach( $G_LISTLINKS AS $line ){
                                if( filter_var( $line, FILTER_VALIDATE_URL )
                                && sqlite_medialive_checkexist( $line ) != FALSE
                                ){
                                    $URLs_DUPLY++;
                                }elseif( filter_var( $line, FILTER_VALIDATE_URL )
                                && sqlite_medialive_checkexist( $line ) == FALSE
                                && ( $ldata = ffprobe_get_data( $line ) ) != FALSE 
                                && is_array( $ldata )
                                && array_key_exists( 'width', $ldata )
                                && $ldata[ 'width' ] > 0
                                ){
                                    //+1 url
                                    if( strlen( $ltitle ) == 0 ){
                                        $ltitle = 'NO-TITLE';
                                    }
                                    if( sqlite_medialive_insert( 0, $ltitle, $line, '' ) ){
                                        //echo get_msg( 'DEF_ELEMENTUPDATED' );
                                    }else{
                                        //echo get_msg( 'WEBSCRAP_ADDKO' );
                                        $URLs_ERROR++;
                                    }
                                    $URLs++;
                                }elseif( startsWith( $line, '#EXTINF' ) ){
                                    //extract title
                                    $tt = explode( ',', $line );
                                    if( array_key_exists( 1, $tt ) ){
                                        $ltitle = $tt[ 1 ];
                                    }else{
                                        $ltitle = $line;
                                        $ltitle = str_ireplace( '#EXTINF:', '', $ltitle );
                                        $ltitle = trim( $ltitle );
                                    }
                                }elseif( filter_var( $line, FILTER_VALIDATE_URL ) ){
                                    //no data valid
                                    $URLs_ERROR++;
                                }else{
                                    //no data valid
                                }
                            }
                            echo '<br />' . get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ' URL: ' . $d[ 'url' ] . ' STATUS ' . $URLs . '/ERRORs:' . $URLs_ERROR . '/DUPLYs:' . $URLs_DUPLY . '/LINES:' . $LINES;
                        }else{
                            echo '<br />' . get_msg( 'WEBSCRAP_ADDKO' ) . ' URL: ' . $d[ 'url' ];
                        }
                        //END ADD
                        $URLs_OK++;
                    }else{
                        $URLs_DEL++;
                        echo '<br />' . get_msg( 'WEBSCRAP_ADDKO' ) . ' URL: ' . $d[ 'url' ];
                    }
                }
                //echo '<br />' . get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ' OKs: ' . $URLs_OK . '/Del:' . $URLs_DEL . '/DelError:' . $URLs_DEL_E;
            }else{
                echo '<br />' . get_msg( 'WEBSCRAP_ADDKO' );
            }
            $SHOW_LIST = FALSE;
            break;
        case 'cleanall':
            if( ( $da = sqlite_medialiveurls_getdata( FALSE, 10000 ) ) 
            && is_array( $da )
            && array_key_exists( 0, $da )
            ){
                $URLs_OK = 0;
                $URLs_DEL = 0;
                $URLs_DEL_E = 0;
                foreach( $da AS $d ){
                    if( ( $ldata = @file_get_contents( $d[ 'url' ] ) ) != FALSE 
                    && strlen( $ldata ) > 0
                    ){
                        //echo get_msg( 'DEF_EXIST' );
                        $URLs_OK++;
                    }else{
                        if( sqlite_medialiveurls_delete( $G_IDMEDIALIVEURLS )
                        ){
                            //echo get_msg( 'DEF_DELETED' );
                            $URLs_DEL++;
                        }else{
                            //echo get_msg( 'DEF_DELETED_ERROR' );
                            $URLs_DEL_E++;
                        }
                    }
                }
                echo get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ' OKs: ' . $URLs_OK . '/Del:' . $URLs_DEL . '/DelError:' . $URLs_DEL_E;
            }else{
                echo get_msg( 'WEBSCRAP_ADDKO' );
            }
            $SHOW_LIST = FALSE;
            break;
        case 'clean':
            if( $G_IDMEDIALIVEURLS > 0
            && ( $d = sqlite_medialiveurls_getdata( $G_IDMEDIALIVEURLS, 1 ) ) 
            && is_array( $d )
            && array_key_exists( 0, $d )
            ){
                $d = $d[ 0 ];
                if( ( $ldata = @file_get_contents( $d[ 'url' ] ) ) != FALSE 
                && strlen( $ldata ) > 0
                ){
                    echo get_msg( 'DEF_EXIST' );
                }else{
                    if( sqlite_medialiveurls_delete( $G_IDMEDIALIVEURLS )
                    ){
                        echo get_msg( 'DEF_DELETED' );
                    }else{
                        echo get_msg( 'DEF_DELETED_ERROR' );
                    }
                }
            }
            $SHOW_LIST = FALSE;
            break;
        case 'check':
            if( $G_IDMEDIALIVEURLS > 0
            && ( $d = sqlite_medialiveurls_getdata( $G_IDMEDIALIVEURLS, 1 ) ) 
            && is_array( $d )
            && array_key_exists( 0, $d )
            ){
                $d = $d[ 0 ];
                if( ( $ldata = @file_get_contents( $d[ 'url' ] ) ) != FALSE 
                && strlen( $ldata ) > 0
                ){
                    echo get_msg( 'DEF_EXIST' );
                }else{
                    echo get_msg( 'DEF_DELETED' );
                }
            }
            $SHOW_LIST = FALSE;
            break;
        case 'delete':
            if( sqlite_medialiveurls_delete( $G_IDMEDIALIVEURLS )
            ){
                echo get_msg( 'DEF_DELETED' );
            }else{
                echo get_msg( 'DEF_DELETED_ERROR' );
            }
            $SHOW_LIST = FALSE;
            break;
        case 'modif':
            if( $G_IDMEDIALIVEURLS > 0
            && $G_TITLE 
            && $G_URL
            && ( $d = sqlite_medialiveurls_getdata( $G_IDMEDIALIVEURLS, 1 ) ) 
            && is_array( $d )
            && array_key_exists( 0, $d )
            ){
                if( sqlite_medialiveurls_replace( $G_IDMEDIALIVEURLS, $G_TITLE, $G_URL ) ){
                    echo get_msg( 'DEF_ELEMENTUPDATED' );
                }else{
                    echo get_msg( 'WEBSCRAP_ADDKO' );
                }
            }elseif( $G_TITLE 
            && $G_URL
            && ( $G_IDMEDIALIVEURLS = sqlite_medialiveurls_checkexist( $G_URL ) ) != FALSE
            ){
                if( sqlite_medialiveurls_replace( $G_IDMEDIALIVEURLS, $G_TITLE, $G_URL ) ){
                    echo get_msg( 'DEF_ELEMENTUPDATED' );
                }else{
                    echo get_msg( 'WEBSCRAP_ADDKO' );
                }
            }elseif( $G_TITLE 
            && $G_URL
            ){
                if( sqlite_medialiveurls_insert( $G_IDMEDIALIVEURLS, $G_TITLE, $G_URL ) ){
                    echo get_msg( 'DEF_ELEMENTUPDATED' );
                }else{
                    echo get_msg( 'WEBSCRAP_ADDKO' );
                }
            }else{
                echo get_msg( 'DEF_NOTEXIST' );
            }
            $SHOW_LIST = FALSE;
            break;
        case 'edit':
            if( ( $d = sqlite_medialiveurls_getdata( $G_IDMEDIALIVEURLS, 1 ) ) 
            && is_array( $d )
            && array_key_exists( 0, $d )
            ){
                $DATA = $d[ 0 ];
            }else{
            
            }
            break;
        default:
            echo "NO ACTION";
	}
	
	if( !$SHOW_LIST ){
	
	}elseif( ( $edata = sqlite_medialiveurls_getdata_filter( $G_SEARCH, 10000 ) ) !== FALSE
	){
?>

<script type="text/javascript">
$(function () {
    
});
function log_delete_medialive( idmedialiveurls ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialiveurlslog&action2=delete&idmedialiveurls=' + idmedialiveurls;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_edit_medialive( idmedialiveurls ){
    var url = '<?php getURL(); ?>';
    url += '?action=medialiveurlslog&action2=edit&idmedialiveurls=' + idmedialiveurls;
    url += '&search=<?php echo urlencode( $G_SEARCH ); ?>';
    goToURL( url );
    return false;
}
function log_new_medialive(){
    var url = '<?php getURL(); ?>';
    url += '?action=medialiveurlslog';
    url += '&search=<?php echo urlencode( $G_SEARCH ); ?>';
    goToURL( url );
    return false;
}
function log_add_medialive(){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialiveurlslog&action2=modif';
    idmedialiveurls = $( '#idmedialiveurls' ).val();
    title = $( '#title' ).val();
    url2 = $( '#url' ).val();
    poster = $( '#poster' ).val();
    var data = { 
        "idmedialiveurls": idmedialiveurls,
        "title": title,
        "url": url2,
        "poster": poster,
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_check_medialive( idmedialiveurls ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialiveurlslog&action2=check&idmedialiveurls=' + idmedialiveurls;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_clean_medialive( idmedialiveurls ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialiveurlslog&action2=clean&idmedialiveurls=' + idmedialiveurls;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_cleanall_medialive(){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialiveurlslog&action2=cleanall';
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_import_medialive( idmedialiveurls ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialiveurlslog&action2=import&idmedialiveurls=' + idmedialiveurls;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
</script>

<div id='dResultIdent'></div>

<br />

<div id='fElementIdent'>
    
    <table class='tList' style='width:100%;margin: auto;'>
        <tr>
            <td colspan='100'></td>
        </tr>
        <tr>
            <?php
                foreach( $FIELDS AS $f => $t ){
            ?>
                <th><?php echo $t; ?></th>
            <?php
                }
            ?>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ); ?></th>
        </tr>
        <tr>
            <td>
                <input type='hidden' id='idmedialiveurls' name='idmedialiveurls' value="<?php echo $DATA[ 'idmedialiveurls' ]; ?>" />
            </td>
            <td>
                <input type='text' id='title' name='title' value="<?php echo $DATA[ 'title' ]; ?>" />
            </td>
            <td>
                <input type='text' id='url' name='url' value="<?php echo $DATA[ 'url' ]; ?>" />
            </td>
            <td>
                
            </td>
            <td>
                <input onclick='log_add_medialive();' type='button' id='bLogAdd' name='bLogAdd' value='<?php echo get_msg( 'WEBSCRAP_PASTELINKS', FALSE ); ?>' />
                <input onclick='log_new_medialive();' type='button' id='bLogNew' name='bLogNew' value='New' />
                <input onclick='log_cleanall_medialive();' type='button' id='bLogCleanAll' name='bLogCleanAll' value='CheckAndCleanAll' />
            </td>
        </tr>
        
                <?php
                    $css_extra = '';
                    foreach( $edata AS $lrow ){
                ?>
        <tr>
                <?php
                        foreach( $lrow AS $field => $data ){
                            if( array_key_exists( $field, $FIELDS ) ){
                ?>
            <td class='<?php echo $css_extra; ?>' title='<?php echo $data; ?>'><?php echo substr( $data, 0, 100 ); ?></td>
                <?php
                            }
                        }
                ?>
            <td>
                <input onclick='log_edit_medialive( <?php echo $lrow[ 'idmedialiveurls' ]; ?> );' type='button' id='bLogEdit' name='bLogEdit' value='<?php echo get_msg( 'MENU_EDIT', FALSE ); ?>' />
                <input onclick='log_check_medialive( <?php echo $lrow[ 'idmedialiveurls' ]; ?> );' type='button' id='bLogCheck' name='bLogCheck' value='Check' />
                <input onclick='log_clean_medialive( <?php echo $lrow[ 'idmedialiveurls' ]; ?> );' type='button' id='bLogClean' name='bLogClean' value='CheckAndClean' />
                <input onclick='log_delete_medialive( <?php echo $lrow[ 'idmedialiveurls' ]; ?> );' type='button' id='bLogDelete' name='bLogDelete' value='<?php echo get_msg( 'MENU_DELETE', FALSE ); ?>' />
                <input onclick='log_import_medialive( <?php echo $lrow[ 'idmedialiveurls' ]; ?> );' type='button' id='bLogUpdate' name='bLogUpdate' value='<?php echo get_msg( 'MENU_IMPORT', FALSE ); ?>' />
                <a href="<?php echo O_ANON_LINK . $lrow[ 'url' ]; ?>" target="_black">Open</a>
            </td>
        </tr>
                <?php
                    }
                ?>
    </table>
	
</div>
<?php
    }else{
        echo get_msg( 'DEF_EMPTYLIST' );
    }
?>
