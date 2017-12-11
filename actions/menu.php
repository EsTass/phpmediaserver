<div class='menuBox'>
    <div class='listMenuElement'>
        <a href='?action='><?php echo get_msg( 'MENU_HOME', FALSE ) ?></a>
    </div>
    <div class='listMenuElement'>
        <a href='?action=listseries'><?php echo get_msg( 'MEDIA_TYPE_SERIE', FALSE ) ?></a>
    </div>
    <?php
        if( defined( 'O_MENU_GENRES' )
        && is_array( O_MENU_GENRES )
        ){
            foreach( O_MENU_GENRES AS $g => $extrasearch ){
    ?>
    <div class='listMenuElement'>
        <a href='?action=list&page=0&search=<?php echo urlencode( $g ); ?>'><?php echo $g; ?></a>
    </div>
    <?php
            }
        }
    ?>
    <div class='listMenuElement'>
        <a href='?action=search'><?php echo get_msg( 'MENU_SEARCH', FALSE ) ?></a>
    </div>
    <div class='listMenuElement'>
        <form methog='get'>
        <input type='text' id='search' name='search' placeholder='<?php echo get_msg( 'MENU_SEARCH', FALSE ) ?>'
        value='<?php echo $G_DATA[ 'search' ]; ?>'
        />
        <input type='hidden' id='action' name='action' value='<?php echo $G_DATA[ 'action' ]; ?>'>
        <input type='hidden' id='page' name='page' value='0'>
        </form>
    </div>
    <?php
        if( check_user_admin() ){
            if( file_exists( PPATH_BASE . DS . 'phpliteadmin.php' ) ){
    ?>
    <div class='listMenuElementAdmin'>
        <a href='phpliteadmin.php' target='_blank'>PHPLiteAdmin</a>
    </div>
    <?php
            }
            if( file_exists( PPATH_ACTIONS . DS . 'config.php' ) ){
    ?>
    <div class='listMenuElementAdmin'>
        <a href='?action=config'><?php echo get_msg( 'MENU_CONFIG', FALSE ) ?></a>
    </div>
    <?php
            }
            if( count( $G_WEBSCRAPPER ) > 0 ){
    ?>
    <div class='listMenuElementAdmin'>
        <a href='?action=webscrap'><?php echo get_msg( 'MENU_SCRAPPERWEB', FALSE ) ?></a>
    </div>
    <div class='listMenuElementAdmin'>
        <a href='?action=webscrappaste'><?php echo get_msg( 'WEBSCRAP_PASTELINKS', FALSE ) ?></a>
    </div>
    <?php
            }
    ?>
    <div class='listMenuElementAdmin'>
        <a href='?action=loglist'><?php echo get_msg( 'MENU_LOG', FALSE ) ?></a>
    </div>
    <div class='listMenuElementAdmin'>
        <a href='?action=mediainfojoin'><?php echo get_msg( 'MENU_JOINMEDIA', FALSE ) ?></a>
    </div>
    <div class='listMenuElementAdmin'>
        <a href='?action=identify'><?php echo get_msg( 'MENU_IDENTIFY', FALSE ) ?></a>
    </div>
    <div class='listMenuElementAdmin'>
        <a href='?action=users'><?php echo get_msg( 'MENU_USERS', FALSE ) ?></a>
    </div>
    <?php
        }
    ?>
    <div class='listMenuElement'>
        <a href='?r=r&action=logout'><?php echo get_msg( 'MENU_LOGOUT', FALSE ) ?></a>
    </div>
</div>
