<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	//admin
	//check_mod_admin();
	
	//action
	//idmedia
	//idmediainfo
	
	$HTMLRESULT = '';
	if( array_key_exists( 'idmedia', $G_DATA ) ){
        $IDMEDIA = $G_DATA[ 'idmedia' ];
	}else{
        $IDMEDIA = '';
	}
	
	if( array_key_exists( 'idmediainfo', $G_DATA ) ){
        $IDMEDIAINFO = $G_DATA[ 'idmediainfo' ];
	}else{
        $IDMEDIAINFO = '';
	}
	
	if( $IDMEDIAINFO > 0
	&& ( $MEDIAINFO = sqlite_mediainfo_getdata( $IDMEDIAINFO ) ) != FALSE 
	&& is_array( $MEDIAINFO )
	&& count( $MEDIAINFO ) > 0
	){
        $MEDIAINFO = $MEDIAINFO[ 0 ];
        $HTMLRESULT = '';
	}elseif( $IDMEDIA > 0
	&& ( $m = sqlite_media_getdata( $IDMEDIA ) ) != FALSE 
	&& is_array( $m )
	&& count( $m ) > 0
	&& array_key_exists( 0, $m )
	&& array_key_exists( 'idmediainfo', $m[ 0 ] )
	&& $m[ 0 ][ 'idmediainfo' ] > 0
	&& ( $MEDIAINFO = sqlite_mediainfo_getdata( $m[ 0 ][ 'idmediainfo' ] ) ) != FALSE 
	&& is_array( $MEDIAINFO )
	&& count( $MEDIAINFO ) > 0
	){
        $MEDIAINFO = $MEDIAINFO[ 0 ];
        $IDMEDIAINFO = $MEDIAINFO[ 'idmediainfo' ];
        $HTMLRESULT = '';
	}else{
        $MEDIAINFO = FALSE;
        $HTMLRESULT = get_msg( 'DEF_NOTEXIST' );
	}
	
	if( strlen( $HTMLRESULT ) > 0 ){
        echo $HTMLRESULT;
	}else{
        //EXTRA VARS
        $urlposter = getURLImg( $IDMEDIA, $IDMEDIAINFO, 'poster' );
        $urlplayer = getURLPlayer( $IDMEDIA, $IDMEDIAINFO );
        $urlplayersafe = getURLPlayerSafe( $IDMEDIA, $IDMEDIAINFO );
        $urldowload = getURLDownload( $IDMEDIA, $IDMEDIAINFO );
        $urlchapters = getURLChapterList( $IDMEDIA, $IDMEDIAINFO );;
        $urllandscape = getURLImg( $IDMEDIA, $IDMEDIAINFO, 'landscape' );
        $duration = $MEDIAINFO[ 'runtime' ];
        if( (int)$MEDIAINFO[ 'season' ] > 0 ){
            $SHOW_CHAPTERS = TRUE;
        }else{
            $SHOW_CHAPTERS = FALSE;
        }
        $nextfileinfo = getURLNextInfo( FALSE, $IDMEDIAINFO );
        $ftitle = $MEDIAINFO[ 'title' ];
        $css_extra = '';
        $searchimages = getURLImgSearch( $IDMEDIAINFO );
?>

<style type='text/css'>
.boxInfoOverlayBg{
    background-image: url("<?php echo $urllandscape ?>");
}
</style>

<script>	
$(function () {
    //IMG LAZYLOAD
    $("img.lazy").Lazy();
});
<?php 
	if( check_user_admin()
	){
?>
function infovideo_delete( element ){
	var url = '?r=r&action=mediadelete';
	var data = { 
		"idmedia": element
	};
	show_msg( url, data, 'result' );
}
<?php } ?>

function infovideo_playlater( idmedia, idmediainfo ){
	var url = '?r=r&action=mediaplaylater';
	var data = { 
		"idmedia": idmedia,
		"idmediainfo": idmediainfo,
	};
	show_msg( url, data, 'result' );
}

</script>

<div class='boxInfo'>

	<table class='tListInfo'>
		<tr>
			<td rowspan='8'>
				<img class='listElementImg listElementImgInfoPoster <?php echo $css_extra; ?>' src='<?php echo $urlposter; ?>' 
				title='<?php $f = 'title'; if( array_key_exists( $f, $MEDIAINFO ) && is_string( $MEDIAINFO[ $f ] ) ) echo $MEDIAINFO[ $f ]; ?>' 
				/>
			</td>
			<th>
				<?php $f = 'title'; if( array_key_exists( $f, $MEDIAINFO ) ) echo $MEDIAINFO[ $f ]; ?>
				&nbsp;&nbsp;
				<?php $f = 'season'; if( array_key_exists( $f, $MEDIAINFO ) && $MEDIAINFO[ $f ] > 0 ) echo sprintf( '%02d', $MEDIAINFO[ $f ] ) . 'x'; ?><?php $f = 'episode'; if( array_key_exists( $f, $MEDIAINFO ) && $MEDIAINFO[ $f ] > 0 ) echo sprintf( '%02d', $MEDIAINFO[ $f ] ); ?>
				&nbsp;&nbsp;
				<?php $f = 'titleepisode'; if( array_key_exists( $f, $MEDIAINFO ) ) echo $MEDIAINFO[ $f ]; ?>
			</th>
		</tr>
		<tr>
			<td>
				<span>
				<?php 
					$f = 'year'; 
					if( array_key_exists( $f, $MEDIAINFO ) ){
						$g = $MEDIAINFO[ $f ];
						echo "<a href='?action=list&search=" . urlencode( $g ) . "' title='" . $g . "'>" . $g . "</a>";
					}
				?>
				</span>
				&nbsp;
				<span><?php echo $duration; ?> mins</span>
				&nbsp;
				&#x2605;
				<span><?php $f = 'rating'; if( array_key_exists( $f, $MEDIAINFO ) && is_string( $MEDIAINFO[ $f ] ) ) echo $MEDIAINFO[ $f ]; ?></span>
				&nbsp;
				<span><?php $f = 'mpaa'; if( array_key_exists( $f, $MEDIAINFO ) && is_string( $MEDIAINFO[ $f ] ) ) echo $MEDIAINFO[ $f ]; ?></span>
				&nbsp;
				<span>
				Termina a:
				<?php echo date( 'H:i:s', strtotime( 'NOW + ' . $duration . ' minute' ) ); ?>
				</span>
				&nbsp;
				<span style='background-color: lightgreen !important;'>
					<a href='<?php echo $urlplayer; ?>'>&#x25B7;&nbsp;<?php echo get_msg( 'INFO_PLAY', FALSE ); ?></a>
				</span>
				&nbsp;
				<span style='background-color: lightgreen !important;'>
					<a href='#' onclick='infovideo_playlater( <?php if( strlen( $IDMEDIA ) > 0 ){ echo $IDMEDIA; }else{ echo '0'; }; ?>, <?php echo $MEDIAINFO[ 'idmediainfo' ]; ?> )'>&#x25B7;&nbsp;<?php echo get_msg( 'INFO_PLAY_LATER', FALSE ); ?></a>
				</span>
				<?php
                    if( defined( 'O_VIDEO_PLAYSAFE' )
                    && O_VIDEO_PLAYSAFE
                    && strlen( $urlplayersafe ) > 0 
                    ){
				?>
				&nbsp;
				<span style='background-color: DarkSalmon !important;'>
					<a href='<?php echo $urlplayersafe; ?>'>&#x25B7;&nbsp;<?php echo get_msg( 'INFO_PLAY_SAFE', FALSE ); ?></a>
				</span>
				<?php
                    }
				?>
				&nbsp;
				<span style='background-color: lightblue !important;'>
					<a href='<?php echo $urldowload; ?>'>&#x21E3;&nbsp;<?php echo get_msg( 'INFO_DOWNLOAD', FALSE ); ?></a>
				</span>
				<?php if( $SHOW_CHAPTERS ){ ?>
				&nbsp;
				<span style='background-color: DarkKhaki !important;'>
					<a href='<?php echo $urlchapters; ?>'><?php echo get_msg( 'INFO_CHAPTERLIST', FALSE ); ?></a>
				</span>
				<?php } ?>
				<?php if( strlen( $nextfileinfo ) > 0 ){ ?>
				&nbsp;
				<span style='background-color: orange !important;'>
					<a href='<?php echo $nextfileinfo; ?>'><?php echo get_msg( 'INFO_NEXT', FALSE ); ?></a>
				</span>
				<?php } ?>
				&nbsp;
				<span style='background-color: blue !important;'>
					<a href='<?php echo $searchimages; ?>'><?php echo get_msg( 'MENU_IMGS_SEARCH', FALSE ); ?></a>
				</span>
			</td>
		</tr>
		<tr>
            <td>
				<?php 
                    if( check_user_admin()
					){
                        $listidmedia = array();
                        if( $IDMEDIAINFO > 0
                        && ( $medialist = sqlite_media_getdata_mediainfo( $IDMEDIAINFO, 3 ) ) != FALSE
                        && is_array( $medialist )
                        && count( $medialist ) > 0
                        ){
                            foreach( $medialist AS $row ){
                                if( !array_key_exists( $row[ 'idmedia' ], $listidmedia ) ){
                                    $listidmedia[ $row[ 'idmedia' ] ] = $row[ 'file' ];
                                }
                                if( count( $listidmedia ) >= 3 ){
                                    break;
                                }
                            }
                        }
                        
                        foreach( $listidmedia AS $ext_idmedia => $ext_title ){
				?>
				<span style='background-color: lowgray !important;font-size: 80%;'>
                    File: <?php echo $ext_title; ?>
				</span>
				&nbsp;
				<span style='background-color: red !important;'>
					 <a class='cursorPointer' href='?action=identifye&idmedia=<?php echo $ext_idmedia; ?>'><?php echo get_msg( 'MENU_IDENTIFY', FALSE ); ?></a>
				</span>
				&nbsp;
				<span style='background-color: red !important;'>
                    <a class='cursorPointer' onclick='infovideo_delete( <?php echo $ext_idmedia; ?> );return false;'><?php echo get_msg( 'MENU_DELETE', FALSE ); ?></a>
				</span>
				<br /><br />
				<?php 
                        }
                    }
                ?>
            </td>
		</tr>
		<tr>
			<td>
				<?php 
					$f = 'genre'; 
					if( array_key_exists( $f, $MEDIAINFO ) ){
                        if( ( $e_list = explode( ',', $MEDIAINFO[ $f ] ) ) != FALSE
                        && is_array( $e_list ) 
                        ){
							foreach( $e_list AS $g ){
								echo "&nbsp;<span><a href='?action=list&search=" . urlencode( $g ) . "' title='" . $g . "'>" . $g . "</a></span>&nbsp;&bull;";
							}
						}else{
							echo "&nbsp;<span><a href='?action=list&search=" . urlencode( $MEDIAINFO[ $f ] ) . "' title='" . $MEDIAINFO[ $f ] . "'>" . $MEDIAINFO[ $f ] . "</a></span>&nbsp;&bull;";
						}
					}
				?>
			</td>
		</tr>
		<tr>
			<td>
				<div><?php $f = 'tagline'; if( array_key_exists( $f, $MEDIAINFO ) && is_string( $MEDIAINFO[ $f ] ) ) echo $MEDIAINFO[ $f ]; ?></div>
			</td>
		</tr>
		<tr>
			<td>
				<div><?php $f = 'plot'; if( array_key_exists( $f, $MEDIAINFO ) && is_string( $MEDIAINFO[ $f ] ) ) echo $MEDIAINFO[ $f ]; ?></div>
			</td>
		</tr>
		<tr>
			<td>
				<?php
					if( array_key_exists( 'fileinfo', $MEDIAINFO )
					&& array_key_exists( 'streamdetails', $MEDIAINFO[ 'fileinfo' ] ) 
					&& array_key_exists( 'video', $MEDIAINFO[ 'fileinfo' ][ 'streamdetails' ] ) 
					){
						$MEDIAINFO2 = $MEDIAINFO[ 'fileinfo' ][ 'streamdetails' ][ 'video' ];
				?>
				<span><?php $f = 'height'; if( array_key_exists( $f, $MEDIAINFO2 ) && is_string( $MEDIAINFO2[ $f ] ) && $MEDIAINFO2[ $f ] > 700 ) echo 'HD'; else echo 'SD'; ?></span>
				&nbsp;
				<span><?php $f = 'codec'; if( array_key_exists( $f, $MEDIAINFO2 ) && is_string( $MEDIAINFO2[ $f ] ) ){ echo $MEDIAINFO2[ $f ]; } ?></span>
				<?php
					}
				?>
			</td>
		</tr>
		<tr>
			<td>
				<?php 
					$f = 'imdbid'; 
					if( array_key_exists( $f, $MEDIAINFO ) 
					&& strlen( $MEDIAINFO[ $f ] ) > 0
					){
						echo "&nbsp;<span><a href='" . O_ANON_LINK . "http://www.imdb.com/title/" . $MEDIAINFO[ $f ] . "' title='" . $ftitle . "' target='_blank'>IMDb</a></span>&nbsp";
					}
					$f = 'imdb'; 
					if( array_key_exists( $f, $MEDIAINFO ) 
					&& strlen( $MEDIAINFO[ $f ] ) > 0
					){
						echo "&nbsp;<span><a href='" . O_ANON_LINK . "" . $MEDIAINFO[ $f ] . "' title='" . $ftitle . "' target='_blank'>IMDb</a></span>&nbsp";
					}
					$f = 'tmdbid'; 
					if( array_key_exists( $f, $MEDIAINFO ) 
					&& strlen( $MEDIAINFO[ $f ] ) > 0
					){
						echo "&nbsp;<span><a href='" . O_ANON_LINK . "https://www.themoviedb.org/movie/" . $MEDIAINFO[ $f ] . "' title='" . $ftitle . "' target='_blank'>TheMovieDb</a></span>&nbsp";
					}
					$f = 'tmdb'; 
					if( array_key_exists( $f, $MEDIAINFO ) 
					&& strlen( $MEDIAINFO[ $f ] ) > 0
					){
						echo "&nbsp;<span><a href='" . O_ANON_LINK . "" . $MEDIAINFO[ $f ] . "' title='" . $ftitle . "' target='_blank'>TheMovieDb</a></span>&nbsp";
					}
					$f = 'tvdbid'; 
					if( array_key_exists( $f, $MEDIAINFO ) 
					&& strlen( $MEDIAINFO[ $f ] ) > 0
					){
						echo "&nbsp;<span><a href='" . O_ANON_LINK . "https://thetvdb.com/index.php?tab=episode&id=" . $MEDIAINFO[ $f ] . "' title='" . $ftitle . "' target='_blank'>TheTVDB</a></span>&nbsp";
					}
					$f = 'tvdb'; 
					if( array_key_exists( $f, $MEDIAINFO ) 
					&& strlen( $MEDIAINFO[ $f ] ) > 0
					){
						echo "&nbsp;<span><a href='" . O_ANON_LINK . "" . $MEDIAINFO[ $f ] . "' title='" . $ftitle . "' target='_blank'>TheTVDB</a></span>&nbsp";
					}
				?>
			</td>
		</tr>
	</table>
	
	<?php
        //RELATED
		$f = 'genre'; 
		if( array_key_exists( $f, $MEDIAINFO ) 
		&& strlen( $MEDIAINFO[ $f ] ) > 0
		&& ( $genres = explode( ',', $MEDIAINFO[ $f ] ) ) != FALSE
		&& is_array( $genres )
		&& count( $genres ) > 0
		&& ( $genreslist = sqlite_media_getdata_related( $genres, O_LIST_MINI_QUANTITY, $MEDIAINFO[ 'idmediainfo' ] ) ) != FALSE
		){
	?>
		<?php echo get_html_list( $genreslist, get_msg( 'INFO_RELATED', FALSE ), FALSE ); ?>
	
	<?php
		}
	?>
	
		<?php
			$f = 'actor'; 
			if( array_key_exists( $f, $MEDIAINFO ) 
			){
				$x = 0;
				if( ( $data_a = explode( ',', $MEDIAINFO[ $f ] ) ) != FALSE 
				&& count( $data_a ) > 1
				){
        ?>
        
	<h2><?php echo get_msg( 'INFO_ACTORS', FALSE ); ?></h2>
	
	<div class='tListInfoActors'>
        <?php
                    foreach( $data_a AS $actor ){
                        if( $x > 9 ){
                            $x = 0;
                            echo "<div class='tListInfoActorsSep'></div>";
                        }
                        $urlactor = getURLActor( $actor );
		?>
				<div class='tListInfoActorsE eColors0<?php echo $x; ?>' >
					<a href='?action=list&search=<?php echo urlencode( $actor ); ?>' title='<?php echo urlencode( $actor ); ?>'>
                        <img class='lazy' data-src='<?php echo $urlactor; ?>' src='' />
					</a>
					<span><?php echo $actor; ?></span>
				</div>
		<?php
						$x++;
                    }
				}elseif( strlen( $MEDIAINFO[ $f ] ) > 0 ){
                    $actor = $MEDIAINFO[ $f ];
                    $urlactor = getURLActor( $actor );
				?>
				<div class='tListInfoActorsE eColors0<?php echo $x; ?>' >
					<a href='?action=list&search=<?php echo urlencode( $actor ); ?>' title='<?php echo urlencode( $actor ); ?>'><img src='<?php echo $urlactor; ?>' />
					<span><?php echo $actor; ?></span>
				</div>
		<?php
				}
			}
		?>
	</div>
	
</div>

<?php 
    }
?>
