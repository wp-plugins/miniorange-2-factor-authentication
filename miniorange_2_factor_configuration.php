<?php
	function mo_2_factor_register() {
		?>
		<div class="mo2f_container">
			<table style="width:100%;">
				<tr>
					<td style="width:60%;vertical-align:top;">
						<?php
							if(get_option('mo_2factor_registration_status') == 'MO_2_FACTOR_OTP_DELIVERED_SUCCESS'){
								delete_option('mo_2factor_temp_status');
								mo2f_show_otp_validation_page();
							} else if(get_option('mo_2factor_registration_status') == 'MO_2_FACTOR_OTP_DELIVERED_FAILURE'){
								mo2f_show_otp_validation_page();
							} else if(get_option('mo_2factor_registration_status') == 'MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION'){
								initialize_mobile_registration();
							} else if(get_option('mo_2factor_registration_status') == 'MO_2_FACTOR_PLUGIN_SETTINGS'){
								show_2_factor_login_settings();
							}else if(get_option('mo_2factor_registration_status') == 'MO_2_FACTOR_VERIFY_CUSTOMER') {
								mo2f_show_verify_password_page();
							} else if(trim(get_option('mo2f_email')) != '' && trim(get_option('mo2f_api_key')) == '' && get_option('new_registration') != 'true'){
								mo2f_show_verify_password_page();
							} else if(!mo2f_is_customer_registered()){
								delete_option('password_mismatch');
								mo2f_show_new_registration_page();
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

	function mo2f_show_new_registration_page() {
		global $current_user;
		get_currentuserinfo();
		?>
			<!--Register with miniOrange-->
			<form name="f" method="post" action="">
				<input type="hidden" name="option" value="mo_auth_register_customer" />
				<div class="mo2f_table_layout">
					<div id="toggle1" class="panel_toggle"><h3>Register with miniOrange</h3></div>
					<div id="panel1">
						<table class="mo2f_settings_table">
							<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo2f_table_textbox" type="email" name="email" readonly required placeholder="person@example.com" value="<?php echo $current_user->user_email;?>"/></td>
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
	?>
		<div class="mo2f_table_layout">
			<div><h3>Test miniOrange 2-factor</h3></div>
			<div><b>Just logout to check how miniOrange 2 factor works.
			Follow these basic steps to login with miniOrange 2 factor authentication.</b></div>
				<br />
			<ul>
				<li><b>Step 1:</b> Enter your username.</li>
				<li><b>Step 2:</b> Click on <i>Login with miniOrange</i>.</li>
				<li><b>Step 3:</b> Scan QR code from your miniOrange mobile app. This requires internet connection.</li>
				<li><b>Step 4:</b> If your mobile is offline, click on <i>Click here if your phone is offline</i>.</li>
				<li><b>Step 5:</b> In your miniOrange mobile app, click on Soft Token and enter the OTP.</li>
				<li><b>Step 6:</b> Click on <i>Validate.</i></li>
			</ul>
			Once you are authenticated, you will be logged in.	<br /><br />	
			<table>
				<tr>
					<td style="vertical-align:top;"><a href="<?php echo wp_login_url() . '?action=logout' ?>" class="button button-primary button-large button-green">Log Out</a></td>
					<td><form name="f" method="post" action="">
							<input type="hidden" name="option" value="mo_auth_refresh_mobile_qrcode" />
							<input type="submit" name="submit" value="Reconfigure your mobile" class="button button-primary button-large"/><br /><br />				
						</form>
					</td>
				</tr>
			</table>
		</div>
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
	
	function initialize_mobile_registration() {
		$data = get_option('mo2f_qrCode');
		$url = get_option('mo2f_host_name');
		?>
		<div class="mo2f_table_layout">
			<div id="toggle1" class="panel_toggle"><h2>Register your mobile</h2></div>				
			<div class="col-sm-6 col-md-4">
				<div class="panel panel-success">
					<table>
					<div><h3> Step 1: Download the miniOrange <span style="color: #F78701;">i'm me</span> app</h3></div>
					<tr>
					<div class="panel-body">
						<td style="width:55%;">
						<p class="content_fonts" style="margin-bottom:2px!important;"><b>iPhone Users</b></p>
						<ol>
						<li>Go to App Store</li>
						<li>Search for <b>miniOrange</b></li>
						<li>Download and install the mobile app</li>
						</ol>
							<span><a target="_blank" href="https://itunes.apple.com/us/app/miniorange-authenticator/id796303566?ls=1"><img src="<?php echo plugins_url( 'includes/images/appstore.png' , __FILE__ );?>" style="width:120px; height:45px; margin-left:6px;"></a></span><br><br>
						</td>
						<td>
						<p class="content_fonts" style="margin-bottom:2px!important;margin-top:-10px;"><b>Android Users</b></p>
						<ol>
						<li> Go to Google Play Store.</li>
						<li> Search for <b>miniOrange.</b></li>
						<li> Download and install the mobile app.</li>
						</ol>
						<a target="_blank" href="https://play.google.com/store/apps/details?id=com.miniorange.authbeta"><img src="<?php echo plugins_url( 'includes/images/playStore.png' , __FILE__ );?>" style="width:120px; height:=45px; margin-left:6px;"></a>
						</td>
					</div>
					</tr>
					</table>
				</div>
			</div>

				<div><h3>Step 2: Scan QR code</h3></div>
				<div id="panel1">
					<p><b>Open your miniOrange i'm me app and click on Configure button to scan the QR code.</b></p>
					<table class="mo2f_settings_table">
						<div id="displayQrCode"> <?php echo '<img src="data:image/jpg;base64,' . $data . '" />'; ?>
						</div>
					</table>
					<br />
					<div id="refrsh_qrcode" style="display:none;">
					
					<form name="f" method="post" action="">
						<input type="hidden" name="option" value="mo_auth_refresh_mobile_qrcode" />
						<input type="submit" name="submit" value="Refresh to scan Qrcode again" class="button button-primary button-large button-green" />
					</form>
					</div>
					<br />
					<div id="mobile_registered" >
					<form name="f" method="post" action="">
						<input type="hidden" name="option" value="mo_auth_setting_configuration" />
						<input type="submit" name="submit" id="mo2f_config" value="Configure Your Settings" class="button button-primary button-large" />
					</form>
					<form name="f" method="post" id="mobile_register_form" action="">
						<input type="hidden" name="option" value="mo_auth_mobile_registration_complete" />
					</form>
					</div>
					<br />
				</div>
			</div>
			<script>
			jQuery("#phone").intlTelInput();
			jQuery('#refrsh_qrcode').hide();
			var timeout;
			pollMobileRegistration();
			function pollMobileRegistration()
			{
				var transId = "<?php echo get_option('mo2f_transactionId');  ?>";
				var jsonString = "{\"txId\":\""+ transId + "\"}";
				var postUrl = "<?php echo $url;  ?>" + "/moas/api/auth/registration-status";
				jQuery.ajax({
					url: postUrl,
					type : "POST",
					dataType : "json",
					data : jsonString,
					contentType : "application/json; charset=utf-8",
					success : function(result) {
						var status = JSON.parse(JSON.stringify(result)).status;
						if (status == 'SUCCESS') {
							var content = "<div id='success' style='margin-left: 20px; margin: top:23px;'><img src='" + "<?php echo plugins_url( 'includes/images/right.png' , __FILE__ );?>" + "' /></div>";
							jQuery("#displayQrCode").empty();
							jQuery("#displayQrCode").append(content);
							setTimeout(function(){jQuery("#mobile_register_form").submit();}, 1000);
						} else if (status == 'ERROR' || status == 'FAILED') {
							var content = "<div id='error' style='margin-left: 20px; margin: top:23px;'><img src='" + "<?php echo plugins_url( 'includes/images/wrong.png' , __FILE__ );?>" + "' /></div>";
							jQuery("#displayQrCode").empty();
							jQuery("#displayQrCode").append(content);
							jQuery('#refrsh_qrcode').show();
						} else {
							timeout = setTimeout(pollMobileRegistration, 3000);
						}
					}
				});
			}
</script>
		<?php
	}
	?>