<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
	
	//params
	//action=users
	//action2=add|pass|delete|admin|noadmin
	//#action2=add
	//username
	//pass1
	//pass2
	//useradmin
	//#action2=pass
	//username
	//pass1
	//pass2
	//#action2=delete
	//username
	//#action2=admin
	//username
	//#action2=noadmin
	//username
	
	//PARAMS
	if( array_key_exists( 'action2', $G_DATA )
	&& strlen( $G_DATA[ 'action2' ] ) > 0
	){
		$ACTION2 = $G_DATA[ 'action2' ];
	}else{
		$ACTION2 = '';
	}
	if( array_key_exists( 'username', $G_DATA )
	&& strlen( $G_DATA[ 'username' ] ) > 0
	){
		$USER = $G_DATA[ 'username' ];
	}else{
		$USER = '';
	}
	if( array_key_exists( 'useradmin', $G_DATA )
	&& $G_DATA[ 'useradmin' ] == TRUE
	){
		$ADMIN = $USER;
	}else{
		$ADMIN = '';
	}
	if( array_key_exists( 'pass1', $G_DATA )
	){
		$PASS1 = $G_DATA[ 'pass1' ];
	}else{
		$PASS1 = '';
	}
	if( array_key_exists( 'pass2', $G_DATA )
	){
		$PASS2 = $G_DATA[ 'pass2' ];
	}else{
		$PASS2 = '';
	}
	
	//VARS
	$HTMLMSG = '';
	
	//ACTIONS
	switch( $ACTION2 ){
        case 'add':
            if( strlen( $USER ) < 3 ){
                $HTMLMSG = 'Invalid USERNAME (MinSize:3)';
            }elseif( strlen( $PASS1 ) < 6 ){
                $HTMLMSG = 'Invalid PASS SIZE (6)';
            }elseif( $PASS1 != $PASS2 ){
                $HTMLMSG = 'Passwords not same (Pass1!=Pass2)';
            }elseif( sqlite_users_insert( $USER, $PASS1, $ADMIN ) ){
                $HTMLMSG = '+User added: ' . $USER;
                sqlite_log_insert( basename( __FILE__ ), 'User add: ' . $USER );
            }else{
                $HTMLMSG = '!User added ERROR: ' . $USER;
            }
        break;
        case 'pass':
            if( strlen( $USER ) < 3 ){
                $HTMLMSG = 'Invalid USERNAME (MinSize:3)';
            }elseif( ( $ud = sqlite_users_getdata( $USER ) ) == FALSE 
            || !is_array( $ud )
            || count( $ud ) != 1
            ){
                $HTMLMSG = 'Invalid USERNAME (Not Found)';
            }elseif( strlen( $PASS1 ) < 6 ){
                $HTMLMSG = 'Invalid PASS SIZE (6)';
            }elseif( $PASS1 != $PASS2 ){
                $HTMLMSG = 'Passwords not same (Pass1!=Pass2)';
            }elseif( sqlite_users_update_pass( $USER, $PASS1 ) ){
                $HTMLMSG = '+User password changed: ' . $USER;
                sqlite_log_insert( basename( __FILE__ ), 'User password changed: ' . $USER );
            }else{
                $HTMLMSG = '!User password changed ERROR: ' . $USER;
            }
        break;
        case 'delete':
            if( strlen( $USER ) < 3 ){
                $HTMLMSG = 'Invalid USERNAME (MinSize:3)';
            }elseif( ( $ud = sqlite_users_getdata( $USER ) ) == FALSE 
            || !is_array( $ud )
            || count( $ud ) != 1
            ){
                $HTMLMSG = 'Invalid USERNAME (Not Found)';
            }elseif( sqlite_users_delete( $USER ) ){
                $HTMLMSG = '+User deleted: ' . $USER;
                sqlite_log_insert( basename( __FILE__ ), 'User deleted: ' . $USER );
            }else{
                $HTMLMSG = '!User deleted ERROR: ' . $USER;
            }
        break;
        case 'admin':
            if( strlen( $USER ) < 3 ){
                $HTMLMSG = 'Invalid USERNAME (MinSize:3)';
            }elseif( ( $ud = sqlite_users_getdata( $USER ) ) == FALSE 
            || !is_array( $ud )
            || count( $ud ) != 1
            || !array_key_exists( 0, $ud )
            || !is_array( $ud[ 0 ] )
            || !array_key_exists( 'password', $ud[ 0 ] )
            ){
                $HTMLMSG = 'Invalid USERNAME (Not Found)';
            }elseif( sqlite_users_update( $USER, $ud[ 0 ][ 'password' ], $USER ) ){
                $HTMLMSG = '+User admin: ' . $USER;
                sqlite_log_insert( basename( __FILE__ ), 'User admin: ' . $USER );
            }else{
                $HTMLMSG = '!User admin ERROR: ' . $USER;
            }
        break;
        case 'noadmin':
            if( strlen( $USER ) < 3 ){
                $HTMLMSG = 'Invalid USERNAME (MinSize:3)';
            }elseif( ( $ud = sqlite_users_getdata( $USER ) ) == FALSE 
            || !is_array( $ud )
            || count( $ud ) != 1
            || !array_key_exists( 0, $ud )
            || !is_array( $ud[ 0 ] )
            || !array_key_exists( 'password', $ud[ 0 ] )
            ){
                $HTMLMSG = 'Invalid USERNAME (Not Found)';
            }elseif( sqlite_users_update( $USER, $ud[ 0 ][ 'password' ], '' ) ){
                $HTMLMSG = '+User NO admin: ' . $USER;
                sqlite_log_insert( basename( __FILE__ ), 'User admin: ' . $USER );
            }else{
                $HTMLMSG = '!User NO admin ERROR: ' . $USER;
            }
        break;
	}
	
?>
<script>	
$(function () {
    
	$( '.userdelete' ).click( function(){
		var user = $( this ).attr( 'data-name' );
		$( '#formtemp #taction2' ).val( 'delete' );
		$( '#formtemp #tusername' ).val( user );
		$( '#formtemp #tpass1' ).val( '' );
		$( '#formtemp #tpass2' ).val( '' );
		$( '#formtemp #tuseradmin' ).val( '' );
		$( '#formtemp' ).submit();
	});
	
	$( '.usernoadmin' ).click( function(){
		var user = $( this ).attr( 'data-name' );
		$( '#formtemp #taction2' ).val( 'noadmin' );
		$( '#formtemp #tusername' ).val( user );
		$( '#formtemp #tpass1' ).val( '' );
		$( '#formtemp #tpass2' ).val( '' );
		$( '#formtemp #tuseradmin' ).val( '' );
		$( '#formtemp' ).submit();
	});
	
	$( '.useradmin' ).click( function(){
		var user = $( this ).attr( 'data-name' );
		$( '#formtemp #taction2' ).val( 'admin' );
		$( '#formtemp #tusername' ).val( user );
		$( '#formtemp #tpass1' ).val( '' );
		$( '#formtemp #tpass2' ).val( '' );
		$( '#formtemp #tuseradmin' ).val( '' );
		$( '#formtemp' ).submit();
	});
	
	$( '.userchangepass' ).click( function(){
		var user = $( this ).attr( 'data-name' );
		var pass1 = $( '#' + user + '_passa' ).val();
		var pass2 = $( '#' + user + '_passb' ).val();
		$( '#formtemp #taction2' ).val( 'pass' );
		$( '#formtemp #tusername' ).val( user );
		$( '#formtemp #tpass1' ).val( pass1 );
		$( '#formtemp #tpass2' ).val( pass2 );
		$( '#formtemp #tuseradmin' ).val( '' );
		$( '#formtemp' ).submit();
	});
});
</script>
<?php
	
	//
	if( ( $userdata = sqlite_users_getdata() ) ){
?>
		<br />
		<table class='tList'>
            <tr>
                <td colspan=100 style='background-color: red; text-align: center;font-size: 120%;'><?php echo "" . $HTMLMSG; ?></td>
            <tr>
            <tr>
                <th>User</th>
                <th>Pass</th>
                <th>NewPass</th>
                <th>Delete</th>
                <th>Admin</th>
            <tr>
    <?php
		foreach( $userdata AS $row ){
			if( strlen( $row[ 'username' ] ) > 0 ){
				if( $row[ 'username' ] == $row[ 'admin' ] ){
					$admin = TRUE;
				}else{
					$admin = FALSE;
				}
        ?>
            <tr>
                <td>
                    <?php echo "" . substr( $row[ 'username' ], 0, 100 ) . ""; ?>
                </td>
                <td>
                    <?php echo "" . substr( $row[ 'password' ], 0, 100 ) . ""; ?>
                </td>
                <td>
                    <input placeholder='password1' type='password' name='passa' id='<?php echo $row[ 'username' ]; ?>_passa' 
                        class='<?php echo $row[ 'username' ]; ?>' />
                    <input placeholder='password2' type='password' name='passb' id='<?php echo $row[ 'username' ]; ?>_passb' 
                        class='<?php echo $row[ 'username' ]; ?>' />
                    <input type='button' class='userchangepass' id='userchangepass' value='Change' data-name='<?php echo $row[ 'username' ]; ?>' />
                </td>
                <td>
                    <input type='button' class='userdelete' id='userdelete' name='userdelete' value='Delete' data-name='<?php echo $row[ 'username' ]; ?>' />
                </td>
                <td>
                    <?php 
                        if( $admin ){
                    ?>
                    <input type='button' class='usernoadmin' id='usernoadmin' name='usernoadmin' value='-Admin' data-name='<?php echo $row[ 'username' ]; ?>' />
                    <?php 
                        }else{
                    ?>
                    <input type='button' class='useradmin' id='useradmin' name='useradmin' value='+Admin' data-name='<?php echo $row[ 'username' ]; ?>' />
                    <?php 
                        }
                    ?>
                </td>
            </tr>
        <?php
			}
		}
		?>		
		<tr>
			<td>
			</td>
			<td>
                <form class='' id='usersadd' action='?action=users&action2=add' method='post' autocomplete='off'>
                <input placeholder='username' type='input' id='username' name='username' />
            </td>
			<td>
                <input placeholder='password1' type='password' id='pass1' name='pass1' />
                <input placeholder='password2' type='password' id='pass2' name='pass2' />
			</td>
			
			<td>
                <input type="checkbox" name="useradmin" id="useradmin" value="1" />Admin
			</td>
			<td>
                <input type='submit' id='buseradd' value='Add' />
                </form>
			</td>
		</tr>
    </table>
    <form class='hidden' id='formtemp' action='?action=users' method='post' autocomplete='off'>
        <input type='hidden' id='taction2' name='action2' />
        <input type='hidden' id='tusername' name='username' />
        <input type='hidden' id='tpass1' name='pass1' />
        <input type='hidden' id='tpass2' name='pass2' />
        <input type='hidden' id='tuseradmin' name='useradmin' />
    </form>
<?php
	}
?>
	<div id='result'></div>
	
