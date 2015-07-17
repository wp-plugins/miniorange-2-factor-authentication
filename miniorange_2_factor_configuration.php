<?php
	function mo_2_factor_register($current_user) {
		if(mo_2factor_is_curl_installed()==0){ ?>
			<p style="color:red;">(Warning: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP CURL extension</a> is not installed or disabled)</p>
		<?php
		}
		?>
		<div class="mo2f_container">
			<table style="width:100%;">
				<tr>
					<td style="width:60%;vertical-align:top;">
						<?php
							
							if(get_option( 'mo_2factor_admin_registration_status') == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' && get_option( 'mo2f_miniorange_admin') != $current_user->ID){
								if(get_user_meta($current_user->ID,'mo_2factor_user_registration_with_miniorange',true) != 'SUCCESS'){
									mo2f_register_additional_admin($current_user);
									delete_user_meta($current_user->ID,'mo_2factor_user_registration_status');
								}
							}
							
							if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_OTP_DELIVERED_SUCCESS' || get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_OTP_DELIVERED_FAILURE'){
								mo2f_show_otp_validation_page();
							} else if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION'){
								if(isset($_SESSION[ 'mo2f_qrCode' ])){
									initialize_mobile_registration($current_user);
								}else{
									mo2f_get_qr_code_for_mobile(get_user_meta($current_user->ID,'mo_2factor_map_id_with_email',true),$current_user->ID);
									initialize_mobile_registration($current_user);
								}
								
							} else if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_PLUGIN_SETTINGS'){
								show_2_factor_login_settings();
							}else if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_VERIFY_CUSTOMER') {
								mo2f_show_verify_password_page();
							} else if(!mo2f_is_customer_registered()){
								delete_option('password_mismatch');
								mo2f_show_new_registration_page($current_user);
							} 
						?>
					</td>
					<td style="vertical-align:top;padding-left:1%;">
						<?php echo mo2f_support(); ?>	
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	function mo2f_show_new_registration_page($current_user) {
		?>
			<!--Register with miniOrange-->
			<form name="f" method="post" action="">
				<input type="hidden" name="option" value="mo_auth_register_customer" />
				<div class="mo2f_table_layout">
					<div id="toggle1" class="panel_toggle"><h3>Register with miniOrange</h3></div>
					<div id="panel1">
						<div><b>Please enter a valid email id that you have access to. You will be able to move forward after verifying an OTP that we will send to this email.</b></div><br />
						<table class="mo2f_settings_table">
							<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo2f_table_textbox" type="email" name="email" required placeholder="person@example.com" value="<?php echo $current_user->user_email;?>"/></td>
							</tr>

							<tr>
							<td><b><font color="#FF0000">*</font>Phone number:</b></td>
							 <td><input class="mo2f_table_textbox" type="text" name="phone" id="phone" autofocus="true" required title="Phone with courntry code eg. +1xxxxxxxxxx" placeholder="Phone with courntry code eg. +1xxxxxxxxxx" value="<?php echo get_option('mo2f_phone');?>" pattern="[\+]?[0-9]{1,4}\s?[0-9]{10}"/></td>
							</tr>
							<tr>
								<td></td>
								<td>We will call only if you need support.</td>
							</tr>
							<tr>
							<td><b><font color="#FF0000">*</font>Password:</b></td>
							 <td><input class="mo2f_table_textbox" type="password" required name="password" placeholder="Choose your password with minimun 6 characters" /></td>
							</tr>
							<tr>
							<td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
							 <td><input class="mo2f_table_textbox" type="password" required name="confirmPassword" placeholder="Confirm your password with minimum 6 characters" /></td>
							</tr>
							<tr><td>&nbsp;</td></tr>
						  <tr>
							<td>&nbsp;</td>
							<td><input type="submit" name="submit" value="Next" class="button button-primary button-large" /></td>
						  </tr>
						</table>
					</div>
				</div>
			</form>
						
			<script>
				jQuery("#phone").intlTelInput();
			</script>
		<?php
	}
	
	function mo2f_show_otp_validation_page(){
	?>
		<!-- Enter otp -->
		
		<div class="mo2f_table_layout">
			<div id="toggle1" class="panel_toggle"><h3>Validate OTP</h3></div>
			<div id="panel1">
				<table class="mo2f_settings_table">
					<form name="f" method="post" id="mo_2f_otp_form" action="">
						<input type="hidden" name="option" value="mo_2factor_validate_otp" />
							<tr>
								<td><b><font color="#FF0000">*</font>Enter OTP:</b></td>
								<td colspan="2"><input class="mo2f_table_textbox" autofocus="true" type="text" name="otp_token" required placeholder="Enter OTP" style="width:61%;"/></td>
							</tr>
							<tr><td colspan="3"></td></tr>
							<tr>
								<td>&nbsp;</td>
								<td style="width:17%">
								<input type="submit" name="submit" value="Validate OTP" class="button button-primary button-large" /></td>

						</form>
						<form name="f" method="post" action="">
							<td>
							<input type="submit" name="mo_resend" id="mo_resend_otp" value="Resend OTP" class="button button-primary button-large button-green" />
							<input type="hidden" name="option" value="mo_2factor_resend_otp"/>
							</td>
							</tr>
						</form>
				</table>
				<form name="f" method="post" action="">
					<input type="hidden" name="option" value="mo_2factor_gobackto_registration_page"/>
					<br /><br /><br />
					<div><center><input type="submit" name="mo2f_goback" id="mo2f_goback" value="Back" class="button button-primary button-large" /></center></div>
					
					
				</form>
			</div>
			<br/>
			<br/>
		</div>
					
	<?php
	}
	
	function miniorange_2_factor_user_roles() {
		global $wp_roles;
		if (!isset($wp_roles))
			$wp_roles = new WP_Roles();
		
		print '<div>';
		foreach($wp_roles->role_names as $id => $name) {	
			$setting = get_option('mo2fa_'.$id);
			$setting = $setting === false || $setting ? 1 : 0;
			print '<input type="checkbox" name="mo2fa_'.$id.'" value="1" '.($setting ? 'checked="checked"' :'').'> <b>'.$name."</b><br>\n";
		}
		print '</div>';
	}
	function miniorange_2_factor_get_login_form_settings() {	
		if(trim(get_option('mo_2f_login_type_enabled')) == 'mobile_only'){
			print '<input type="radio" name="mo_2f_enabled" value="mobile_only" checked="checked"><b>Enable miniOrange 2-factor</b><br >';
			print '<input type="radio" name="mo_2f_enabled" value="password_with_mobile" ><b>Enable miniOrange 2-factor with password</b><br>';
		}else if(trim(get_option(mo_2f_login_type_enabled)) == 'password_with_mobile'){
			print '<input type="radio" name="mo_2f_enabled" value="mobile_only" ><b>Enable miniOrange 2-factor</b><br>';
			print '<input type="radio" name="mo_2f_enabled" value="password_with_mobile" checked="checked" ><b>Enable miniOrange 2-factor with password</b><br >';
		}else{
			print '<input type="radio" name="mo_2f_enabled" value="mobile_only" checked="checked" >'."<b>Enable miniOrange 2-factor</b><br>";
			print '<input type="radio" name="mo_2f_enabled" value="password_with_mobile" >'."<b>Enable miniOrange 2-factor with password</b><br>";
		}
	}
	
	function show_2_factor_login_settings() {
		echo mo2f_show_instruction_to_allusers();
		?>
		<br />
		<div class="mo2f_small_layout">
			<form name="f" id="mowp_admin_activationform" method="post" action="">
				<h3>Plugin activation settings</h3>
				<input type="checkbox" id="mo2f_adminrole_activation" name="mo2f_adminrole_activation" value="1" <?php echo !get_option('mo2f_admin_disabled_status') ? 'checked="checked"' : ''; ?> />Enable plugin for admins.
				<input type="hidden" name="option" value="mo_auth_admin_activation" />
			</form>
			<form name="f" id="mowp_activationform" method="post" action="">
				<input type="checkbox" id="mo2f_role_activation" name="mo2f_role_activation" value="1" <?php echo get_option('mo2f_disabled_status') ? 'checked="checked"' : ''; ?> />Enable plugin for all other users.<br /><br />
				<div id="mo2f_note"><b>Note:</b> Admins are required to test 2 factor plugin and if they are successfully logged in then enable the plugin for other roles.</div>
				<input type="hidden" name="option" value="mo_auth_user_activation" />
			</form><br />
			<form name="f" id="mowp_forgotphone_form" method="post" action="">
				<input type="checkbox" id="mo2f_forgotphone" name="mo2f_forgotphone" value="1" <?php echo get_option('mo2f_enable_forgotphone') ? 'checked="checked"' : ''; ?> />Enable Forgot Phone.<br /><br /><div id="mo2f_note"><b>Note:</b> Checking this option will enable Forgot My Phone for all the users during Login. An OTP over registered email will be send to verify the user. User has to enter OTP to bypass mobile authentication.</div>
				<input type="hidden" name="option" value="mo2f_forgotphone_activation" />
			</form>
		</div>
		<script>
			 jQuery('#mo2f_role_activation').change(function() {
				jQuery('#mowp_activationform').submit();
			});
			jQuery('#mo2f_adminrole_activation').change(function() {
				jQuery('#mowp_admin_activationform').submit();
			});
			jQuery('#mo2f_forgotphone').change(function() {
				jQuery('#mowp_forgotphone_form').submit();
			});
		</script>
	<?php
	}

	function mo2f_show_verify_password_page() {
		?>
			<!--Verify password with miniOrange-->
			<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_auth_verify_customer" />
			<div class="mo2f_table_layout">
			<div id="toggle1" class="panel_toggle"><h3>Login with miniOrange</h3></div>
			<div id="panel1">
			<p>Enter your miniOrange Email and Password.</p>
			<br/>
			<table class="mo2f_settings_table">
				<tr>
				<td><b><font color="#FF0000">*</font>Email:</b></td>
				<td><input class="mo2f_table_textbox" type="email" name="email" required placeholder="person@example.com" value="<?php echo get_option('mo2f_email');?>"/></td>
				</tr>
				<tr>
				<td><b><font color="#FF0000">*</font>Password:</b></td>
				 <td><input class="mo2f_table_textbox" type="password" name="password" required placeholder="Enter your miniOrange password" /></td>
				</tr>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr>
				<td>&nbsp;</td>
				<td><input type="submit" name="submit" class="button button-primary button-large" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target="_blank" href="<?php echo get_option('mo2f_host_name') . "/moas/idp/userforgotpassword"; ?>">Forgot your password?</a></td>
			  </tr>
			</table>
			</div>
			</div>
			</form>
	<?php	}
	
	function mo2f_register_additional_admin($current_user){
	?>
		<form name="f" method="post" action="">
			<div class="mo2f_table_layout">
				<div id="toggle1" class="panel_toggle"><center><h3><b>miniOrange 2 Factor Authentication has been enabled for you. Please set up your account and register yourself by following the steps.</b></h3></center></div>
				<div id="panel1">
					<table class="mo2f_settings_table">
						
						<tr>
							<td><center><div class="alert-box"><input class="mo2f_table_textbox" type="email" autofocus="true" name="mo_useremail" style="text-align:center;height:40px;font-size:24px;" required placeholder="person@example.com" value="<?php echo $current_user->user_email;?>"/></div></center></td>
						</tr>
						<tr>
							<td><center><h4>Please enter a valid email id that you have access to. You will be able to login after verifying an OTP that we will send to this email in case you forgot or lost your phone.</h4></center></td>
						</tr>
						<tr><td></td></tr>
						<tr><td></td></tr>
						<tr><td></td></tr>
						<tr><td></td></tr>
						<tr><td></td></tr>
						<tr><td></td></tr>
						<tr><td></td></tr>
						<tr><td></td></tr>
						<tr>
							<td><input type="hidden" name="miniorange_user_reg_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-user-reg-nonce'); ?>" />
							<center><input type="submit" name="miniorange_get_started" id="miniorange_get_started" class="button button-primary button-large extra-large" value="Get Started" /></center> </td>
						</tr>
					</table>
				</div>
			</div>
		</form>
	<?php
	}