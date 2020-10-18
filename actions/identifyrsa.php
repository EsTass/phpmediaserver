<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//idmedia
	//filename
	//atitle
	//preview
	//season
	//seasonre
	//episode
	//seasonre
	
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        echo "Invalid ID: idmedia";
        die();
	}
	
	if( array_key_exists( 'atitle', $G_DATA ) 
	&& strlen( $G_DATA[ 'atitle' ] ) > 2
	){
        $TITLE = $G_DATA[ 'atitle' ];
	}else{
        echo "Invalid Search: ";
        die();
	}
	
	if( array_key_exists( 'filename', $G_DATA ) 
	&& strlen( $G_DATA[ 'filename' ] ) > 2
	){
        $FILENAME = $G_DATA[ 'filename' ];
	}else{
        echo "Invalid filename: ";
        die();
	}
	
	if( array_key_exists( 'preview', $G_DATA ) 
	&& $G_DATA[ 'preview' ] == '0'
	){
        $PREVIEW = FALSE;
	}else{
        $PREVIEW = TRUE;
	}
	
	if( array_key_exists( 'season', $G_DATA ) 
	&& strlen( $G_DATA[ 'season' ] ) > 2
	){
        $BSEASON = $G_DATA[ 'season' ];
	}else{
        $BSEASON = FALSE;
	}
	
	if( array_key_exists( 'seasonre', $G_DATA ) 
	&& strlen( $G_DATA[ 'seasonre' ] ) > 0
	){
        $BSEASONRE = $G_DATA[ 'seasonre' ];
	}else{
        $BSEASONRE = FALSE;
	}
	
	if( array_key_exists( 'episode', $G_DATA ) 
	&& strlen( $G_DATA[ 'episode' ] ) > 2
	){
        $BEPISODE = $G_DATA[ 'episode' ];
	}else{
        $BEPISODE = FALSE;
	}
	
	if( array_key_exists( 'episodere', $G_DATA ) 
	&& strlen( $G_DATA[ 'episodere' ] ) > 2
	){
        $BEPISODERE = $G_DATA[ 'episodere' ];
	}else{
        $BEPISODERE = FALSE;
	}
	
	if( ( $xid = getIMDB_ID( $TITLE ) ) != FALSE
	){
        $IMDB = $xid;
	}else{
        $IMDB = FALSE;
	}
	
	$HTML = scrapp_irules_ident( $IDMEDIA, $TITLE, $IMDB, $FILENAME, $PREVIEW, $BSEASON, $BSEASONRE, $BEPISODE, $BEPISODERE );
	
	//add to valid prev assings
	if( stripos( $HTML, get_msg( 'IDENT_DETECTEDOK' ) ) !== FALSE 
	&& ( $md = sqlite_media_getdata( $IDMEDIA ) ) != FALSE
    && sqlite_idents_checkexist_et( $md[ 0 ][ 'file' ], $TITLE, $IMDB ) == FALSE
	){
        $imdbcode = '';
        if( $IMDB !== FALSE ){
            $imdbcode = $IMDB;
        }
        $STYPE = 'bulk';
        $season = $BSEASON;
        if( $BSEASONRE != '' ){
            $season = $BSEASONRE;
        }
        $episode = $BEPISODE;
        if( $BEPISODERE != '' ){
            $episode = $BEPISODERE;
        }
        $datai = array(
            'ididents' => 0,
            'file' => $md[ 0 ][ 'file' ],
            'title' => $TITLE,
            'imdbid' => $FILENAME,
            'type' => $STYPE,
            'season' => $season,
            'episode' => $episode,
            'bulk' => TRUE,
        );
        sqlite_idents_insert( $datai );
	}
	
	echo "" . $HTML;
	
?>
