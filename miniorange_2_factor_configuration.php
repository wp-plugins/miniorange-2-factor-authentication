<?php
	function mo_2_factor_register($current_user) {
		if(mo_2factor_is_curl_installed()==0){ ?>
			<p style="color:red;">(Warning: <a href="http://php.net/manual/en/curl.installation.php" target="_blank">PHP CURL extension</a> is not installed or disabled)</p>
		<?php
		}
		
		$mo2f_active_tab = isset($_GET['mo2f_tab']) ? $_GET['mo2f_tab'] : '2factor_setup';
		?>
		<div id="tab">
			<h2 class="nav-tab-wrapper">
				<a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=2factor_setup" class="nav-tab <?php echo $mo2f_active_tab == '2factor_setup' ? 'nav-tab-active' : ''; ?>" id="mo2f_tab1">
				<?php if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION' || get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_PLUGIN_SETTINGS'){ ?>User Profile <?php }else{ ?> Account Setup <?php } ?></a> 
				<a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mobile_configure" class="nav-tab <?php echo $mo2f_active_tab == 'mobile_configure' ? 'nav-tab-active' : ''; ?>" id="mo2f_tab3">Configure Mobile</a>
				<a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_login" class="nav-tab <?php echo $mo2f_active_tab == 'mo2f_login' ? 'nav-tab-active' : ''; ?>" id="mo2f_tab2">Login Settings</a>
			    <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_demo" class="nav-tab <?php echo $mo2f_active_tab == 'mo2f_demo' ? 'nav-tab-active' : ''; ?>" id="mo2f_tab4">How It Works</a>
			    <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_help" class="nav-tab <?php echo $mo2f_active_tab == 'mo2f_help' ? 'nav-tab-active' : ''; ?>" id="mo2f_tab5">Help & Troubleshooting</a>
			</h2>
		</div>
		<div class="mo2f_container">
			<table style="width:100%;">
				<tr>
					<td style="width:60%;vertical-align:top;">
						<?php
							
							if($mo2f_active_tab == 'mobile_configure') {
								instruction_for_mobile_registration($current_user);
							}else if($mo2f_active_tab == 'mo2f_help'){
								mo2f_show_help_and_troubleshooting($current_user);
							}else if($mo2f_active_tab == 'mo2f_demo'){
								show_2_factor_login_demo($current_user);
							}else if(current_user_can( 'manage_options' ) && $mo2f_active_tab == 'mo2f_login'){
								show_2_factor_login_settings($current_user);
							}else{
							
							
								if(get_option( 'mo_2factor_admin_registration_status') == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' && get_option( 'mo2f_miniorange_admin') != $current_user->ID){
									if(get_user_meta($current_user->ID,'mo_2factor_user_registration_with_miniorange',true) != 'SUCCESS'){
										mo2f_register_additional_admin($current_user);
										delete_user_meta($current_user->ID,'mo_2factor_user_registration_status');
									}
								}
								
								if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_OTP_DELIVERED_SUCCESS' || get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_OTP_DELIVERED_FAILURE'){
									mo2f_show_otp_validation_page();
								} else if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION'){
									mo2f_show_instruction_to_allusers($current_user);
								} else if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_PLUGIN_SETTINGS'){
									mo2f_show_instruction_to_allusers($current_user);
									
								}else if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_VERIFY_CUSTOMER') {
									mo2f_show_verify_password_page();
								} else if(!mo2f_is_customer_registered()){
									delete_option('password_mismatch');
									mo2f_show_new_registration_page($current_user);
								} 
							
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
					<h3>Register with miniOrange</h3><hr>
					<div id="panel1">
						<div><b>Please enter a valid email id that you have access to. You will be able to move forward after verifying an OTP that we will be sending to this email.</b></div><br />
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
							<td><input type="submit" name="submit" value="Submit" class="button button-primary button-large" /></td>
						  </tr>
						</table>
						<br>
						
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
			<h3>Validate OTP</h3><hr>
			<div id="panel1">
				<table class="mo2f_settings_table">
					<form name="f" method="post" id="mo_2f_otp_form" action="">
						<input type="hidden" name="option" value="mo_2factor_validate_otp" />
							<tr>
								<td><b><font color="#FF0000">*</font>Enter OTP:</b></td>
								<td colspan="2"><input class="mo2f_table_textbox" autofocus="true" type="text" name="otp_token" required placeholder="Enter OTP" style="width:95%;"/></td>
								<td><a href="#resendotplink">Resend OTP ?</a></td>
							</tr>
							
							<tr>
								<td>&nbsp;</td>
								<td style="width:17%">
								<input type="submit" name="submit" value="Validate OTP" class="button button-primary button-large" /></td>

						</form>
						<form name="f" method="post" action="">
						<td>
						<input type="hidden" name="option" value="mo_2factor_gobackto_registration_page"/>
							<input type="submit" name="mo2f_goback" id="mo2f_goback" value="Back" class="button button-primary button-large" /></td>
						</form>
						</td>
						</tr>
						<form name="f" method="post" action="" id="resend_otp_form">
							<input type="hidden" name="option" value="mo_2factor_resend_otp"/>
						</form>
						
				</table>
				</div>
				<div>	
					<script>
						jQuery('a[href="#resendotplink"]').click(function(e) {
							jQuery('#resend_otp_form').submit();
						});
					</script>
		
			<br><br>
			</div>
			
			
						
		</div>
					
	<?php
	}
	
	function show_2_factor_login_settings($current_user) {
		
		?>
	<div class="mo2f_table_layout">
			<?php echo mo2f_check_if_registered_with_miniorange($current_user); ?>
			<form name="f" id="mowp_admin_activationform" method="post" action="">
				<h3>Login Settings</h3>
				<hr><br>
				<input type="checkbox" id="mo2f_adminrole_activation" name="mo2f_adminrole_activation" value="1" <?php echo !get_option('mo2f_admin_disabled_status') ? 'checked="checked"' : ''; if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) != 'MO_2_FACTOR_PLUGIN_SETTINGS'){ echo 'disabled';} ?> />Enable 2-Factor for admins.
				<br><br>
				<div id="mo2f_note"><b>Note:</b> This option is checked by default. It will enable 2-Factor only for admins, other users can still login with their password.</div>
				<input type="hidden" name="option" value="mo_auth_admin_activation" />
			</form><br/>
			<form name="f" id="mowp_activationform" method="post" action="">
				<input type="checkbox" id="mo2f_role_activation" name="mo2f_role_activation" value="1" <?php echo get_option('mo2f_disabled_status') ? 'checked="checked"' : ''; if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) != 'MO_2_FACTOR_PLUGIN_SETTINGS'){ echo 'disabled';}?> />Enable 2-Factor for all other users.<br /><br />
				<div id="mo2f_note"><b>Note:</b> Checking this option will enable 2-Factor for all users. Make sure you have tested login with 2-factor before enabling it for all users.</div>
				<input type="hidden" name="option" value="mo_auth_user_activation" />
			</form><br />
			<form name="f" id="mowp_forgotphone_form" method="post" action="">
				<input type="checkbox" id="mo2f_forgotphone" name="mo2f_forgotphone" value="1" <?php echo !get_option('mo2f_enable_forgotphone') ? 'checked="checked"' : ''; if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) != 'MO_2_FACTOR_PLUGIN_SETTINGS'){ echo 'disabled';} ?> />Enable Forgot Phone.<br />
				<br /><div id="mo2f_note"><b>Note:</b> This option is checked by default so that if anybody has forgotten his phone or phone is lost/stolen/discharged. An OTP over registered email will be send to verify the user. User has to enter OTP to bypass login with your phone.</div>
				<input type="hidden" name="option" value="mo2f_forgotphone_activation" />
			</form><br />
			<form name="f" id="mowp_disable_defaultform" method="post" action="">
				<input type="checkbox" id="mo2f_loginwith_phone" name="mo2f_loginwith_phone" value="1" <?php echo get_option('mo2f_show_loginwith_phone') ? 'checked="checked"' : ''; if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) != 'MO_2_FACTOR_PLUGIN_SETTINGS'){ echo 'disabled';} ?> />Show Login with phone only. (Hide default login form)&nbsp;&nbsp;
				<a class="btn btn-link" data-toggle="collapse" href="#preview" aria-expanded="false">See preview</a><br>
				<br><div id="mo2f_note"><b>Note:</b> Checking this option will hide the default login form. Click above link to see the preview. Users who have not setup 2-Factor can still login with their password.</div>
				<input type="hidden" name="option" value="mo2f_loginphone_activation" /><br>
				<div class="collapse" id="preview" style="height:300px;">
				<center>
				<img style="height:300px;" src="<?php echo plugins_url('includes/images/help/login-help-3.png', __FILE__ )?>" >
				 </center>
				 </div>
			</form>
			<br><br>
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
			jQuery('#mo2f_loginwith_phone').change(function() {
				jQuery('#mowp_disable_defaultform').submit();
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
			<h3>Login with miniOrange</h3><hr>
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
				<td><input type="submit" name="submit" value="Submit" class="button button-primary button-large" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target="_blank" href="<?php echo get_option('mo2f_host_name') . "/moas/idp/userforgotpassword"; ?>">Forgot your password?</a></td>
			  </tr>
			</table>
		
			</div><br><br>
			</div>
			
						
			
					
			</form>
	<?php	}
	
	function mo2f_register_additional_admin($current_user){
	?>
		<form name="f" method="post" action="">
			<div class="mo2f_table_layout">
				<div><center><p style="font-size:17px;">miniOrange 2 Factor Authentication has been enabled for you. Please set up your account and register yourself by following the steps.</p></center></div>
				<div id="panel1">
					<table class="mo2f_settings_table">
						
						<tr>
							<td><center><div class="alert-box"><input type="email" autofocus="true" name="mo_useremail" style="width:48%;text-align: center;height: 40px;font-size:18px;border-radius:5px;" required placeholder="person@example.com" value="<?php echo $current_user->user_email;?>"/></div></center></td>
						</tr>
						<tr>
							<td><center><p>Please enter a valid email id that you have access to. We need this email to send OTP in case you forgot or lost your phone.</p></center></td>
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