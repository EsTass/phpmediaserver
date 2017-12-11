<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	//admin
	check_mod_admin();
?>
<script>	
$(function () {
	$( '#useradd' ).click( function(){
		var name = $( '#username' ).val();
		var pass1 = $( '#pass1' ).val();
		var pass2 = $( '#pass2' ).val();
		var admin = $( '#useradmin' ).attr( 'checked' ) ? true : false;
		var cont = true;
		if( name.lenght < 4 ){
			$( '#username' ).focus();
			msgbox( 'Min 3' );
			cont = false;
		}
		if( pass1.lenght < 8 ){
			$( '#pass1' ).focus();
			msgbox( 'Min 8' );
			cont = false;
		}
		if( pass2.lenght < 8 ){
			$( '#pass2' ).focus();
			msgbox( 'Min 8' );
			cont = false;
		}
		if( pass1 != pass2 ){
			$( '#pass1' ).focus();
			msgbox( 'pass1 <> pass2' );
			cont = false;
		}
		if( cont ){
			var url = '?r=r&action=usersadd';
			var data = { 
				"user": name, 
				"pass1": pass1,
				"pass2": pass2,
				"admin": admin
			};
			show_msg( url, data, 'result' );
		}
	});
	
	$( '.userdelete' ).click( function(){
		var url = '?r=r&action=usersdelete';
		var user = $( this ).attr( 'data-name' );
		var data = { 
			"user": user
		};
		show_msg( url, data, 'result' );
	});
	
	$( '.useradmin' ).click( function(){
		var url = '?r=r&action=usersadmin';
		var user = $( this ).attr( 'data-name' );
		var data = { 
			"user": user
		};
		show_msg( url, data, 'result' );
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
				echo "<tr>";
				$pc = 20;
					echo "<td>";
					echo "" . substr( $row[ 'username' ], 0, 100 ) . "";
					echo "</td>";
					
					echo "<td>";
					echo "" . substr( $row[ 'password' ], 0, 100 ) . "";
					echo "</td>";
					
					echo "<td>";
					echo "<input placeholder='password1' type='password' id='passa' id='passa' class='" . $row[ 'username' ] . "' />";
					echo "<input placeholder='password2' type='password' id='passb' id='passb' class='" . $row[ 'username' ] . "' />";
					echo "<input type='button' id='userchangepass' value='Change' data-name='" . $row[ 'username' ] . "' />";
					echo "</td>";
					
					echo "<td>";
					echo "<input type='button' class='userdelete' id='userdelete' name='userdelete' value='Delete' data-name='" . $row[ 'username' ] . "' />";
					echo "</td>";
					if( $admin ){
						echo "<td>";
						echo "Admin";
						echo "</td>";
					}else{
						echo "<td>";
						echo "<input type='button' class='useradmin' id='useradmin' name='useradmin' value='Admin' data-name='" . $row[ 'username' ] . "' />";
						echo "</td>";
					}
				echo "</tr>";
			}
		}
		?>		
		<tr>
			<td>
                <input placeholder='username' type='input' id='username' name='pass1' />
            </td>
			
			<td></td>
			
			<td>
                <input placeholder='password1' type='password' id='pass1' name='pass1' />
                <input placeholder='password2' type='password' id='pass2' name='pass2' />
			</td>
			
			<td>
			<input type='button' id='useradd' value='Add' />
			</td>
			<td>
                <input placeholder='useradmin' type='text' id='useradmin' name='useradmin' />
			</td>
		</tr>
    </table>
<?php
	}
?>
	<div id='result'></div>
	
