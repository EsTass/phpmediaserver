<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//search
	//action
	//action2
	//idmedialive
	//action2=modif
	//idmedialive
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
	
	if( array_key_exists( 'idmedialive', $G_DATA ) 
	&& is_numeric( $G_DATA[ 'idmedialive' ] )
	){
        $G_IDMEDIALIVE = $G_DATA[ 'idmedialive' ];
	}else{
        $G_IDMEDIALIVE = FALSE;
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
	
	if( array_key_exists( 'poster', $G_DATA ) ){
        $G_POSTER = $G_DATA[ 'poster' ];
	}else{
        $G_POSTER = FALSE;
	}
	
	if( array_key_exists( 'listlinks', $G_DATA ) ){
        $G_LISTLINKS = $G_DATA[ 'listlinks' ];
	}else{
        $G_LISTLINKS = FALSE;
	}
	
	
	$FIELDS = array(
        'idmedialive' => 'idmedialive',
        'title' => 'Title',
        'url' => 'URL',
        'poster' => 'Poster',
        'date' => 'DateAdded',
	);
	
	$SHOW_LIST = TRUE;
	$DATA = array();
	foreach( $FIELDS AS $f => $t ){
        $DATA[ $f ] = '';
    }
	if( $G_ACTION2 )
	switch( $G_ACTION2 ){
        case 'cleanall':
            if( ( $da = sqlite_medialive_getdata( FALSE, 10000 ) ) 
            && is_array( $da )
            && array_key_exists( 0, $da )
            ){
                $URLs_OK = 0;
                $URLs_DEL = 0;
                $URLs_DEL_E = 0;
                foreach( $da AS $d ){
                    if( ( $ldata = ffmpeg_capture_img( $d[ 'url' ] ) ) != FALSE 
                    && is_array( $ldata )
                    && array_key_exists( 0, $ldata )
                    && array_key_exists( 1, $ldata )
                    && sameImage( $ldata[ 0 ], $ldata[ 1 ] ) == FALSE
                    ){
                        //echo get_msg( 'DEF_EXIST' );
                        $URLs_OK++;
                    }else{
                        if( sqlite_medialive_delete( $d[ 'idmedialive' ] )
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
            if( $G_IDMEDIALIVE > 0
            && ( $d = sqlite_medialive_getdata( $G_IDMEDIALIVE, 1 ) ) 
            && is_array( $d )
            && array_key_exists( 0, $d )
            ){
                $d = $d[ 0 ];
                if( ( $ldata = ffmpeg_capture_img( $d[ 'url' ] ) ) != FALSE 
                && is_array( $ldata )
                && array_key_exists( 0, $ldata )
                && array_key_exists( 1, $ldata )
                && sameImage( $ldata[ 0 ], $ldata[ 1 ] ) == FALSE
                ){
                    echo get_msg( 'DEF_EXIST' );
                }else{
                    if( sqlite_medialive_delete( $G_IDMEDIALIVE )
                    ){
                        echo get_msg( 'DEF_DELETED' );
                    }else{
                        echo get_msg( 'DEF_DELETED_ERROR' );
                    }
                }
            }
            //check allways same image 
            $SHOW_LIST = FALSE;
            break;
        case 'check':
            if( $G_IDMEDIALIVE > 0
            && ( $d = sqlite_medialive_getdata( $G_IDMEDIALIVE, 1 ) ) 
            && is_array( $d )
            && array_key_exists( 0, $d )
            ){
                $d = $d[ 0 ];
                if( ( $ldata = ffmpeg_capture_img( $d[ 'url' ] ) ) != FALSE 
                && is_array( $ldata )
                && array_key_exists( 0, $ldata )
                && array_key_exists( 1, $ldata )
                && sameImage( $ldata[ 0 ], $ldata[ 1 ] ) == FALSE
                ){
                    echo get_msg( 'DEF_EXIST' );
                }else{
                    echo get_msg( 'DEF_DELETED' );
                }
                /*
                //old method check valid size
                if( ( $ldata = ffprobe_get_data( $d[ 'url' ] ) ) != FALSE 
                && is_array( $ldata )
                && array_key_exists( 'width', $ldata )
                && $ldata[ 'width' ] > 0
                ){
                    echo get_msg( 'DEF_EXIST' );
                }else{
                    echo get_msg( 'DEF_DELETED' );
                }
                */
            }
            $SHOW_LIST = FALSE;
            break;
        case 'paste':
            if( $G_LISTLINKS
            && strlen( $G_LISTLINKS ) > 0
            ){
                $G_LISTLINKS = trim( $G_LISTLINKS );
                $G_LISTLINKS = explode( PHP_EOL, $G_LISTLINKS );
                $G_LISTLINKS = array_filter( $G_LISTLINKS, 'trim' );
                $ltitle = FALSE;
                $URLs = 0;
                $URLs_ERROR = 0;
                $URLs_DUPLY = 0;
                foreach( $G_LISTLINKS AS $line ){
                    if( filter_var( $line, FILTER_VALIDATE_URL )
                    && sqlite_medialive_checkexist( $line ) != FALSE
                    ){
                        $URLs_DUPLY++;
                    }elseif( filter_var( $line, FILTER_VALIDATE_URL )
                    && sqlite_medialive_checkexist( $line ) == FALSE
                    && ( $ldata = ffprobe_get_data( $line, FALSE ) ) != FALSE 
                    && is_array( $ldata )
                    && array_key_exists( 'width', $ldata )
                    && array_key_exists( 'codec', $ldata )
                    && ( $ldata[ 'width' ] > 0 )
                    ){
                        //+1 url
                        if( $ltitle != FALSE ){
                            if( strlen( $ltitle[ 'titleg' ] ) > 0 ){
                                $lltitle = $ltitle[ 'titleg' ] . ': ' . $ltitle[ 'title' ];
                            }else{
                                $lltitle = $ltitle[ 'title' ];
                            }
                            $poster = $ltitle[ 'poster' ];
                            if( ( $lastid = sqlite_medialive_insert( 0, $lltitle, $line, $poster ) ) != FALSE ){
                                //download poster
                                if( filter_var( $poster, FILTER_VALIDATE_URL ) ){
                                    $fileimg = PPATH_MEDIAINFO . DS . $lastid . '.livetv';
                                    downloadPosterToFile( $poster, $fileimg );
                                }
                                //echo get_msg( 'DEF_ELEMENTUPDATED' );
                            }else{
                                //echo get_msg( 'WEBSCRAP_ADDKO' );
                                $URLs_ERROR++;
                            }
                            $ltitle = FALSE;
                            $URLs++;
                        }
                    }elseif( startsWith( $line, '#EXTINF' ) ){
                        //extract title data
                        $ltitle = liveTVLineData( $line );
                    }elseif( filter_var( $line, FILTER_VALIDATE_URL ) ){
                        //no data valid
                        $URLs_ERROR++;
                    }else{
                        //no data valid
                    }
                }
                echo get_msg( 'WEBSCRAP_ADDOK', FALSE ) . ' ' . $URLs . '/ERRORs:' . $URLs_ERROR . '/DUPLYs:' . $URLs_DUPLY;
            }else{
                echo get_msg( 'WEBSCRAP_ADDKO' );
            }
            $SHOW_LIST = FALSE;
            break;
        case 'delete':
            if( $G_IDMEDIALIVE > 0
            && sqlite_medialive_delete( $G_IDMEDIALIVE )
            ){
                echo get_msg( 'DEF_DELETED' );
            }else{
                echo get_msg( 'DEF_DELETED_ERROR' );
            }
            $SHOW_LIST = FALSE;
            break;
        case 'modif':
            if( $G_IDMEDIALIVE > 0
            && $G_TITLE 
            && $G_URL
            && $G_POSTER
            && ( $d = sqlite_medialive_getdata( $G_IDMEDIALIVE, 1 ) ) 
            && is_array( $d )
            && array_key_exists( 0, $d )
            ){
                if( sqlite_medialive_replace( $G_IDMEDIALIVE, $G_TITLE, $G_URL, $G_POSTER ) ){
                    echo get_msg( 'DEF_ELEMENTUPDATED' );
                }else{
                    echo get_msg( 'WEBSCRAP_ADDKO' );
                }
            }elseif( $G_TITLE 
            && $G_URL
            && $G_POSTER
            && ( $G_IDMEDIALIVE = sqlite_medialive_checkexist( $G_URL ) ) != FALSE
            ){
                if( sqlite_medialive_replace( $G_IDMEDIALIVE, $G_TITLE, $G_URL, $G_POSTER ) ){
                    echo get_msg( 'DEF_ELEMENTUPDATED' );
                }else{
                    echo get_msg( 'WEBSCRAP_ADDKO' );
                }
            }elseif( $G_TITLE 
            && $G_URL
            && $G_POSTER
            ){
                if( sqlite_medialive_insert( $G_IDMEDIALIVE, $G_TITLE, $G_URL, $G_POSTER ) ){
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
            if( $G_IDMEDIALIVE > 0
            && ( $d = sqlite_medialive_getdata( $G_IDMEDIALIVE, 1 ) ) 
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
	
	}elseif( ( $edata = sqlite_medialive_getdata_filter( $G_SEARCH, 10000 ) ) !== FALSE
	){
?>

<script type="text/javascript">
$(function () {
    
});
function log_delete_medialive( idmedialive ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialivelog&action2=delete&idmedialive=' + idmedialive;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_edit_medialive( idmedialive ){
    var url = '<?php getURL(); ?>';
    url += '?action=medialivelog&action2=edit&idmedialive=' + idmedialive;
    url += '&search=<?php echo urlencode( $G_SEARCH ); ?>';
    goToURL( url );
    return false;
}
function log_new_medialive(){
    var url = '<?php getURL(); ?>';
    url += '?action=medialivelog';
    url += '&search=<?php echo urlencode( $G_SEARCH ); ?>';
    goToURL( url );
    return false;
}
function log_add_medialive(){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialivelog&action2=modif';
    idmedialive = $( '#idmedialive' ).val();
    title = $( '#title' ).val();
    url2 = $( '#url' ).val();
    poster = $( '#poster' ).val();
    var data = { 
        "idmedialive": idmedialive,
        "title": title,
        "url": url2,
        "poster": poster,
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_paste_medialive(){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialivelog&action2=paste';
    listlinks = $( '#listlinks' ).val();
    var data = { 
        "listlinks": listlinks,
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_check_medialive( idmedialive ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialivelog&action2=check&idmedialive=' + idmedialive;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_clean_medialive( idmedialive ){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialivelog&action2=clean&idmedialive=' + idmedialive;
    var data = { 
        //"user": user
    };
    show_msg( url, data, 'result' );
    return false;
}
function log_cleanall_medialive(){
    var url = '<?php getURL(); ?>';
    url += '?r=r&action=medialivelog&action2=cleanall';
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
            <th>Poster</th>
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
                
            </td>
            <td>
                <input type='hidden' id='idmedialive' name='idmedialive' value="<?php echo $DATA[ 'idmedialive' ]; ?>" />
            </td>
            <td>
                <input type='text' id='title' name='title' value="<?php echo $DATA[ 'title' ]; ?>" />
            </td>
            <td>
                <input type='text' id='url' name='url' value="<?php echo $DATA[ 'url' ]; ?>" />
            </td>
            <td>
                <input type='text' id='poster' name='poster' value="<?php echo $DATA[ 'poster' ]; ?>" />
            </td>
            <td>
                
            </td>
            <td>
                <input onclick='log_add_medialive();' type='button' id='bLogAdd' name='bLogAdd' value='<?php echo get_msg( 'WEBSCRAP_PASTELINKS', FALSE ); ?>' />
                <input onclick='log_new_medialive();' type='button' id='bLogNew' name='bLogNew' value='New' />
                <input onclick='log_cleanall_medialive();' type='button' id='bLogCleanAll' name='bLogCleanAll' value='CheckAndCleanAll' />
            </td>
        </tr>
        
        <tr>
            <td colspan=6 >
                <textarea id="listlinks" name="listlinks" style="width:100%;min-heigth: 20%;">#MP3U8 LIST
                </textarea>
            </td>
            <td>
                <input onclick='log_paste_medialive();' type='button' id='bLogAdd' name='bLogAdd' value='<?php echo get_msg( 'WEBSCRAP_PASTELINKS', FALSE ); ?>' />
            </td>
        </tr>
                <?php
                    $css_extra = '';
                    foreach( $edata AS $lrow ){
                ?>
        <tr>
            <td>
                <img class='listElementImg listElementImgMini lazy' src='' data-src='<?php echo getURLImg( $lrow[ 'idmedialive' ], $lrow[ 'idmedialive' ], 'livetv' ); ?>' class='listElementPosterTiny' />
            </td>
                <?php
                        foreach( $lrow AS $field => $data ){
                            if( array_key_exists( $field, $FIELDS ) ){
                ?>
            <td class='<?php echo $css_extra; ?>' title='<?php echo $data; ?>'><?php echo substr( $data, 0, 60 ); ?></td>
                <?php
                            }
                        }
                ?>
            <td>
                <input onclick='log_edit_medialive( <?php echo $lrow[ 'idmedialive' ]; ?> );' type='button' id='bLogEdit' name='bLogEdit' value='<?php echo get_msg( 'MENU_EDIT', FALSE ); ?>' />
                <input onclick='log_check_medialive( <?php echo $lrow[ 'idmedialive' ]; ?> );' type='button' id='bLogCheck' name='bLogCheck' value='Check' />
                <input onclick='log_clean_medialive( <?php echo $lrow[ 'idmedialive' ]; ?> );' type='button' id='bLogClean' name='bLogClean' value='CheckAndClean' />
                <input onclick='log_delete_medialive( <?php echo $lrow[ 'idmedialive' ]; ?> );' type='button' id='bLogDelete' name='bLogDelete' value='<?php echo get_msg( 'MENU_DELETE', FALSE ); ?>' />
                <a href="?action=playerlive&idmedialive=<?php echo $lrow[ 'idmedialive' ]; ?>" target="_black" class="button">Open</a>
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
