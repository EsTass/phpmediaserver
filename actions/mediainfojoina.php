<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//join 2 mediainfo elements checking series/movies
	//replacethis
	//whiththis
	
	$HTML = '';
	if( array_key_exists( 'replacethis', $G_DATA ) 
	&& (int)$G_DATA[ 'replacethis' ] > 0
	){
        $REPLACETHIS = (int)$G_DATA[ 'replacethis' ];
	}else{
        $REPLACETHIS = FALSE;
        $HTML = get_msg( 'DEF_NOTEXIST' );
	}
	
	if( array_key_exists( 'whiththis', $G_DATA ) 
	&& (int)$G_DATA[ 'whiththis' ] > 0
	){
        $WHITHTHIS = (int)$G_DATA[ 'whiththis' ];
	}else{
        $WHITHTHIS = FALSE;
        $HTML = get_msg( 'DEF_NOTEXIST' );
	}
	
	if( $REPLACETHIS != FALSE 
	&& $WHITHTHIS != FALSE
	&& ( $rdata = sqlite_mediainfo_getdata( $REPLACETHIS ) ) != FALSE
	&& ( $wdata = sqlite_mediainfo_getdata( $WHITHTHIS ) ) != FALSE
	){
        if( $rdata[ 0 ][ 'season' ] == NULL ){
            //Movies
            $HTML .= '<br />Movie: ' . $rdata[ 0 ][ 'title' ];
            if( ( $medialist = sqlite_media_getdata_mediainfo( $REPLACETHIS ) ) != FALSE ){
                $allok = TRUE;
                foreach( $medialist AS $row ){
                    if( array_key_exists( 'idmedia', $row ) 
                    && sqlite_media_update_mediainfo( $row[ 'idmedia' ], $WHITHTHIS )
                    ){
                        $HTML .= '<br />Assigned: ' . basename( $row[ 'file' ] ) . ' -> ' . $wdata[ 0 ][ 'title' ];
                    }else{
                        $allok = FALSE;
                        $HTML .= '<br />ERROR assign: ' . basename( $row[ 'file' ] ) . ' -> ' . $wdata[ 0 ][ 'title' ];
                    }
                }
                if( $allok 
                && sqlite_mediainfo_delete( $REPLACETHIS )
                ){
                    $HTML .= '<br />MediaInfo deleted: ' . $rdata[ 0 ][ 'title' ];
                }
            }else{
                $HTML .= '<br />No files asigned: ' . $rdata[ 0 ][ 'title' ];
                if( sqlite_mediainfo_delete( $REPLACETHIS )
                ){
                    $HTML .= '<br />MediaInfo deleted: ' . $rdata[ 0 ][ 'title' ];
                }
            }
        }else{
            //Serie
            $HTML .= '<br />Serie: ' . $rdata[ 0 ][ 'title' ];
            if( ( $rdatalist = sqlite_mediainfo_search_title( $rdata[ 0 ][ 'title' ] ) ) != FALSE 
            && is_array( $rdatalist )
            && count( $rdatalist ) > 0
            ){
                foreach( $rdatalist AS $rowmi ){
                    $HTML .= '<br />Chapter: ' . $rowmi[ 'title' ] . ' ' . $rowmi[ 'season' ] . ' ' . $rowmi[ 'episode' ];
                    if( ( $medialist = sqlite_media_getdata_mediainfo( $rowmi[ 'idmediainfo' ] ) ) != FALSE ){
                        $allok = TRUE;
                        foreach( $medialist AS $row ){
                            if( array_key_exists( 'idmedia', $row ) 
                            && ( $idmi = sqlite_mediainfo_check_exist( $wdata[ 0 ][ 'title' ], $rowmi[ 'season' ], $rowmi[ 'episode' ] ) ) != FALSE
                            ){
                                $HTML .= '<br />Chapter Exist: ' . $wdata[ 0 ][ 'title' ] . ' ' . $rowmi[ 'season' ] . ' ' . $rowmi[ 'episode' ];
                            }else{
                                $HTML .= '<br />Chapter NOT Exist: ' . $wdata[ 0 ][ 'title' ] . ' ' . $rowmi[ 'season' ] . ' ' . $rowmi[ 'episode' ];
                                $copy_idmw = $wdata[ 0 ];
                                $copy_idmw[ 'idmediainfo' ] = 'NULL';
                                $copy_idmw[ 'season' ] = $rowmi[ 'season' ];
                                $copy_idmw[ 'episode' ] = $rowmi[ 'episode' ];
                                $copy_idmw[ 'titleepisode' ] = '';
                                if( ( $idmi = sqlite_mediainfo_insert( $copy_idmw ) ) != FALSE ){
                                    $HTML .= '<br />Chapter Created: ' . $wdata[ 0 ][ 'title' ] . ' ' . $rowmi[ 'season' ] . ' ' . $rowmi[ 'episode' ];
                                }else{
                                    $HTML .= '<br />Chapter ERROR creating: ' . $wdata[ 0 ][ 'title' ] . ' ' . $rowmi[ 'season' ] . ' ' . $rowmi[ 'episode' ];
                                    $idmi = FALSE;
                                }
                            }
                            if( $idmi
                            && array_key_exists( 'idmedia', $row ) 
                            && sqlite_media_update_mediainfo( $row[ 'idmedia' ], $idmi )
                            ){
                                $HTML .= '<br />Assigned: ' . basename( $row[ 'file' ] ) . ' -> ' . $wdata[ 0 ][ 'title' ] . ' ' . $rowmi[ 'season' ] . ' ' . $rowmi[ 'episode' ];
                            }else{
                                $allok = FALSE;
                                $HTML .= '<br />ERROR assign: ' . basename( $row[ 'file' ] ) . ' -> ' . $wdata[ 0 ][ 'title' ] . ' ' . $rowmi[ 'season' ] . ' ' . $rowmi[ 'episode' ];
                            }
                        }
                        if( $allok 
                        && sqlite_mediainfo_delete( $rowmi[ 'idmediainfo' ] )
                        ){
                            $HTML .= '<br />MediaInfo deleted: ' . $rdata[ 0 ][ 'title' ];
                        }
                    }else{
                        $HTML .= '<br />No files asigned: ' . $rdata[ 0 ][ 'title' ];
                        if( sqlite_mediainfo_delete( $rowmi[ 'idmediainfo' ] )
                        ){
                            $HTML .= '<br />MediaInfo deleted: ' . $rdata[ 0 ][ 'title' ];
                        }
                    }
                }
            }else{
                $HTML .= '<br />No chapters to replace: ' . $rdata[ 0 ][ 'title' ];
            }
        }
	}
	
	echo $HTML;
	
?>
