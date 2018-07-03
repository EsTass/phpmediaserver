<?php

	defined( 'ACCESS' ) or die( 'HTTP/1.0 401 Unauthorized.<br />' );
	
	if( ( $userlist = sqlite_users_getdata() ) != FALSE ){
	
	}else{
		$userlist = array();
	}
?>

<script>	
$(function () {
	$( '.loginIconUser' ).click( function(){
		$( '#user' ).val( $( this ).attr( 'data-user' ) );
		$( '#pass' ).focus();
	});
});
</script>
<div class='loginBox'>
	
	<h1><?php echo get_msg( 'LOGIN_FORM_TITLE' ); ?></h1>

<?php
/*
	<div class='loginBoxIconUser'>
	<?php
		$x = 0;
		foreach( $userlist AS $row ){
			if( $x > 9 ) $x = 0;
			if( $row[ 'admin' ] != $row[ 'username' ] ){
	?>
		<div class='loginIconUser eColors0<?php echo $x; ?>' data-user='<?php echo $row[ 'username' ]; ?>' >
			<img src='imgs/u.png' />
			<span><?php echo $row[ 'username' ]; ?></span>
		</div>
	<?php
				$x++;
			}
		}
	?>
	</div>
*/
?>
		
	<form class='loginForm' id='formLogin' action='?action=login' method='post' autocomplete='off'>
	  	<fieldset>
			
			<br />
			
			<table class='loginT'>
                <tr>
                    <td colspan=100 class="tCenter">
                        <img src="imgs/logo/1.png" class="imgLogo" title="PHPMediaServer Logo" />
                    </td>
                </tr>
				<tr>
					<td>
						<label for='user'><?php echo get_msg( 'LOGIN_FORM_USER' ); ?></label>
					</td>
					<td>
						<input type='text' name='user' id='user' class='textLogin ui-widget-content ui-corner-all' />
					</td>
				</tr>
				<tr>
					<td>
						<label for='pass'><?php echo get_msg( 'LOGIN_FORM_PASS' ); ?></label>
					</td>
					<td>
						<input type="password" name="pass" id="pass" value="" class="textLogin ui-widget-content ui-corner-all" />
					</td>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						<label for='bSend'></label>
						<input type='submit' id='bSend' class='submit' value=" <?php echo get_msg( 'LOGIN_FORM_BUTTON', FALSE ); ?> " />
					</td>
				</tr>
				<tr>
					<td colspan=2 class='loginTMsg'>
						<p><?php echo $ACTIONINFO; ?></p>
					</td>
				</tr>
			</table>
			
		</fieldset>
	</form>
</div>
<?php
    //CRON   
?>


