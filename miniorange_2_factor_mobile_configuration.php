<?php
	function mo2f_get_qr_code_for_mobile($email,$id){
		$registerMobile = new Two_Factor_Setup();
		$content = $registerMobile->register_mobile($email);
		$response = json_decode($content, true);
		if(json_last_error() == JSON_ERROR_NONE) {
			update_option( 'mo2f_message','Please scan the QR Code now.');
			$_SESSION[ 'mo2f_qrCode' ] = $response['qrCode'];
			$_SESSION[ 'mo2f_transactionId' ] = $response['txId'];
			update_user_meta($id,'mo_2factor_user_registration_status','MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION');
		}
	}
	
	function mo_2factor_is_curl_installed() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return 1;
		} else
			return 0;
	}
	
	function mo2f_show_instruction_to_allusers(){
	?>
	<div class="mo2f_table_layout">
		<div><h2 class="header2"><b>Read the instruction given below and test miniOrange 2-factor</b></h2></div>
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
				<td style="vertical-align:top;"><a href="<?php echo wp_logout_url(); ?>" class="button button-primary button-large button-green">Log Out</a></td>
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

	function initialize_mobile_registration($current_user) {
		$data = $_SESSION[ 'mo2f_qrCode' ];
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
			jQuery('#refrsh_qrcode').hide();
			var timeout;
			pollMobileRegistration();
			function pollMobileRegistration()
			{
				var transId = "<?php echo $_SESSION[ 'mo2f_transactionId' ];  ?>";
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