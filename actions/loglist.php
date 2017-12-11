<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	$LOGLIST = array(
        'log' => get_msg( 'MENU_LOG', FALSE ),
        'medialog' => get_msg( 'MENU_LOGMEDIA', FALSE ),
        'mediainfolog' => get_msg( 'MENU_LOGMEDIAINFO', FALSE ),
        'playedlog' => get_msg( 'MENU_LOGPLAYED', FALSE ),
        'cronlog' => get_msg( 'MENU_CRON', FALSE ),
	);
	
?>

<script type="text/javascript">
$(function () {
    
});
function log_open( action ){
    goTo( action );
    
    return false;
}
</script>

<table class='tLogList'>
    <tr>
        <th><h2><?php echo get_msg( 'MENU_LOG', FALSE ); ?></h2></th>
    <?php
        foreach( $LOGLIST AS $a => $title ){
    ?>
        <th class='tCenter'>
            <input type='button' onclick='log_open( "<?php echo $a; ?>" );' value='<?php echo $title; ?>' />&nbsp;
        </th>
    <?php
        }
    ?>
    </tr>
    <tr>
        <td id='dResultLog' colspan='100'>
            
        </td>
    </tr>
</div>
