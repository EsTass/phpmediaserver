<?php
    
	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	if( array_key_exists( 'search', $G_DATA ) ){
        $G_SEARCH = $G_DATA[ 'search' ];
	}else{
        $G_SEARCH = '';
	}
	
	if( array_key_exists( 'saction', $G_DATA ) ){
        $G_SACTION = $G_DATA[ 'saction' ];
	}else{
        $G_SACTION = '';
	}
	
	if( array_key_exists( 'ip', $G_DATA ) ){
        $G_IP = $G_DATA[ 'ip' ];
	}else{
        $G_IP = '';
	}
	
	//Params
	//action = mod action
	//saction = base mod action
	//ip = ip for base mod action
	//SACTIONS
	//insert1 = insert on bans
	//insert2 = insert on whitelist
	//delete1 = delete on bans
	//delete2 = delete on whitelist
	
	//Vars
	$HTMLMSG = '';
	
	//saction
	switch ( $G_SACTION ) {
	    case 'insert1':
            //insert on bans
            if( !filter_var( $G_IP, FILTER_VALIDATE_IP ) ){
                $HTMLMSG = get_msg( 'IPCONTROL_MSG_INVALIDIP', FALSE ) . $G_IP;
            }elseif( sqlite_bans_insert( $G_IP ) ){
                $HTMLMSG = get_msg( 'IPCONTROL_MSG_ADDBANIP_OK', FALSE ) . $G_IP;
            }else{
                $HTMLMSG = get_msg( 'IPCONTROL_MSG_ADDBANIP_KO', FALSE ) . $G_IP;
            }
            break;
       
       case 'insert2':
            //insert on whitelist
            if( !filter_var( $G_IP, FILTER_VALIDATE_IP ) ){
                $HTMLMSG = get_msg( 'IPCONTROL_MSG_INVALIDIP', FALSE ) . $G_IP;
            }elseif( sqlite_whitelist_insert( $G_IP ) ){
                $HTMLMSG = get_msg( 'IPCONTROL_MSG_ADDWLIP_OK', FALSE ) . $G_IP;
            }else{
                $HTMLMSG = get_msg( 'IPCONTROL_MSG_ADDWLIP_KO', FALSE ) . $G_IP;
            }
            break;
        
        case 'delete1':
            //delete on bans
            if( !filter_var( $G_IP, FILTER_VALIDATE_IP ) ){
                $HTMLMSG = get_msg( 'IPCONTROL_MSG_INVALIDIP', FALSE ) . $G_IP;
            }elseif( sqlite_bans_delete( $G_IP ) ){
                $HTMLMSG = get_msg( 'DEF_DELETED', FALSE ) . $G_IP;
            }else{
                $HTMLMSG = get_msg( 'DEF_DELETED_ERROR', FALSE ) . $G_IP;
            }
            break;
        
        case 'delete2':
            //delete on whitelist
            if( !filter_var( $G_IP, FILTER_VALIDATE_IP ) ){
                $HTMLMSG = get_msg( 'IPCONTROL_MSG_INVALIDIP', FALSE ) . $G_IP;
            }elseif( sqlite_whitelist_delete( $G_IP ) ){
                $HTMLMSG = get_msg( 'DEF_DELETED', FALSE ) . $G_IP;
            }else{
                $HTMLMSG = get_msg( 'DEF_DELETED_ERROR', FALSE ) . $G_IP;
            }
            break;
        
	}
	
	
?>

<p style='border: 1px solid white;background-color: gray;color: black;padding: 1em;'>
    <?php echo $HTMLMSG; ?>
</p>

<div style='margin: auto;float:left;width: 49%;height:70vh;max-height:70vh;overflow:auto;border: 1px solid white;background-color: gray;'>
    <table class='tList'>
        <tr>
            <th colspan='100'><?php echo get_msg( 'IPCONTROL_BANS', FALSE ) ?></th>
        </tr>
        <tr>
            <td colspan='100' style='text-align: center;'>
                <!-- ACTIONS -->
                <form action='?action=ipcontrol'  method="post">
                    <input type='hidden' name='saction' value='insert1' />
                    <input type='text' name='ip' value='' />
                    <input type='submit' name='bSubmit' value='<?php echo get_msg( 'IPCONTROL_ADD', FALSE ) ?>' />
                </form>
            </td>
        </tr>
        <tr>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ) ?></th>
            <th><?php echo get_msg( 'MENU_IP', FALSE ) ?></th>
            <th><?php echo get_msg( 'MENU_DATE', FALSE ) ?></th>
        </tr>
        <?php
            if( ( $logdata = sqlite_bans_getdata( $G_SEARCH ) ) 
            && count( $logdata ) > 0
            ){
                foreach( $logdata AS $row ){
        ?>
        <tr>
            <td>
                <a href='?action=ipcontrol&saction=delete1&ip=<?php echo $row[ 'ip' ]; ?>' class='button'><?php echo get_msg( 'IPCONTROL_DELETE', FALSE ) ?></a>
            </td>
            <td>
                <?php echo $row[ 'ip' ]; ?>
            </td>
            <td>
                <?php echo $row[ 'date' ]; ?>
            </td>
        </tr>
        <?php
                }
            }else{
        ?>
        <tr>
            <td colspan=100><?php echo get_msg( 'DEF_EMPTYLIST', FALSE ) ?></td>
        </tr>
        <?php
            }
        ?>
    </table>
</div>
<div style='margin: auto;float:right;width: 49%;height:70vh;max-height:70vh;overflow:auto;border: 1px solid white;background-color: gray;'>
    <table class='tList'>
        <tr>
            <th colspan='100'><?php echo get_msg( 'IPCONTROL_WHITELIST', FALSE ) ?></th>
        </tr>
        <tr>
            <td colspan='100' style='text-align: center;'>
                <!-- ACTIONS -->
                <form action='?action=ipcontrol'  method="post">
                    <input type='hidden' name='saction' value='insert2' />
                    <input type='text' name='ip' value='' />
                    <input type='submit' name='bSubmit' value='<?php echo get_msg( 'IPCONTROL_ADD', FALSE ) ?>' />
                </form>
            </td>
        </tr>
        <tr>
            <th><?php echo get_msg( 'MENU_ACTION', FALSE ) ?></th>
            <th><?php echo get_msg( 'MENU_IP', FALSE ) ?></th>
            <th><?php echo get_msg( 'MENU_DATE', FALSE ) ?></th>
        </tr>
        <?php
            if( ( $logdata = sqlite_whitelist_getdata( $G_SEARCH ) ) 
            && count( $logdata ) > 0
            ){
                foreach( $logdata AS $row ){
        ?>
        <tr>
            <td style='padding: 2px;'>
                <a href='?action=ipcontrol&saction=delete2&ip=<?php echo $row[ 'ip' ]; ?>' class='button'><?php echo get_msg( 'IPCONTROL_DELETE', FALSE ) ?></a>
            </td>
            <td>
                <?php echo $row[ 'ip' ]; ?>
            </td>
            <td>
                <?php echo $row[ 'date' ]; ?>
            </td>
        </tr>
        <?php
                }
            }else{
        ?>
        <tr>
            <td colspan=100><?php echo get_msg( 'DEF_EMPTYLIST', FALSE ) ?></td>
        </tr>
        <?php
            }
        ?>
    </table>
</div>
