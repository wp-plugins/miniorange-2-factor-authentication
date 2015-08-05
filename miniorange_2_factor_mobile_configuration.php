<?php

	function mo2f_check_if_registered_with_miniorange($current_user){
		if(!(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION' || get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_PLUGIN_SETTINGS')) { ?>
			<br/><div style="display:block;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">Please <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=2factor_setup">Register with miniOrange</a> to configure miniOrange 2 Factor plugin.</div>
	<?php } else {
			if(get_user_meta($current_user->ID,'mo_2factor_mobile_registration_status',true) != 'MO_2_FACTOR_SUCCESS'){ 
				?>
	
				<br />
					<div style="display:block;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">Please <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mobile_configure">click here</a> to configure your mobile to complete the 2 Factor setup.</div>
		
				<?php }
		}
	}
	
	function mo2f_get_qr_code_for_mobile($email,$id){
		$registerMobile = new Two_Factor_Setup();
		$content = $registerMobile->register_mobile($email);
		$response = json_decode($content, true);
		if(json_last_error() == JSON_ERROR_NONE) {
			if($response['statusCode'] == 'ERROR'){
				update_option( 'mo2f_message', $response['statusMessage']);
				unset($_SESSION[ 'mo2f_qrCode' ]);
				unset($_SESSION[ 'mo2f_transactionId' ]);
				$this->mo_auth_show_error_message();
			}else{
				update_option( 'mo2f_message','Please scan the QR Code now.');
				$_SESSION[ 'mo2f_qrCode' ] = $response['qrCode'];
				$_SESSION[ 'mo2f_transactionId' ] = $response['txId'];
				update_user_meta($id,'mo_2factor_user_registration_status','MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION');
			}
		}
	}
	
	function mo_2factor_is_curl_installed() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return 1;
		} else
			return 0;
	}
	function show_2_factor_login_demo($current_user){
	?> 
	<div class="mo2f_table_layout">
		<?php echo mo2f_check_if_registered_with_miniorange($current_user); ?>
	<h3>How to login with your phone?</h3><hr>
	<center>
	<div class="mo2f_help_container">
	<div id="myCarousel1" class="carousel slide" data-ride="carousel" >
						  <!-- Indicators -->
						  <ol class="carousel-indicators">
							<li data-target="#myCarousel1" data-slide-to="0" class="active"></li>
							<li data-target="#myCarousel1" data-slide-to="1"></li>
							<li data-target="#myCarousel1" data-slide-to="2"></li>
							<li data-target="#myCarousel1" data-slide-to="3"></li>
							<li data-target="#myCarousel1" data-slide-to="4"></li>
							<li data-target="#myCarousel1" data-slide-to="5"></li>
							<li data-target="#myCarousel1" data-slide-to="6"></li>
						  </ol>
						<div class="carousel-inner" role="listbox">
						<div class="item active">
						      <p>Enter your username and click on login with your phone.</p>
							  <p style="margin-left: -40px;" >Login Form Option 1<span style="margin-left:90px;">Login Form Option 2</span></p>  
							  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/login-help-1.png', __FILE__ )?>" alt="First slide">
							  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/login-help-3.png', __FILE__ )?>" alt="First slide">
						</div>
						<div class="item">
							   <p><br></p>
							 <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-1.png', __FILE__ )?>" alt="First slide">
							 
							</div>
						   <div class="item">
							<p>Open miniOrange Authenticator app and click on Authenticate.</p>
							<img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-2.png', __FILE__ )?>" alt="First slide">
						 
						  </div>
						  <div class="item">
						  <p><br></p>
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-3.png', __FILE__ )?>" alt="First slide">
						  </div>
						  <div class="item">
						  <p><br></p>
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-4.png', __FILE__ )?>" alt="First slide">
						  </div>
						  <div class="item">
						  <p><br></p>
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-5.png', __FILE__ )?>" alt="First slide">
						  </div>
						   <div class="item">
						   <p>Once you are authenticated, you will be logged in.</p>
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/login-help-2.png', __FILE__ )?>" alt="First slide" style="height:400px;">
						  </div>
						</div>
						</div>
						</div>
						</center>
						<br>
		<h3>How to login in offline mode (no internet connectivity) ?</h3><hr>
		<center>
	    <div class="mo2f_help_container">
		<div id="myCarousel2" class="carousel slide" data-ride="carousel" >
		<ol class="carousel-indicators">
						<li data-target="#myCarousel2" data-slide-to="0" class="active"></li>
						<li data-target="#myCarousel2" data-slide-to="1"></li>
						<li data-target="#myCarousel2" data-slide-to="2"></li>
						<li data-target="#myCarousel2" data-slide-to="3"></li>
						<li data-target="#myCarousel2" data-slide-to="4"></li>
						<li data-target="#myCarousel2" data-slide-to="5"></li>
						</ol>
						<div class="carousel-inner" role="listbox">
						 
							<div class="item active">
							      <p>Enter your username and click on login with your phone.</p>
							  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/login-help-1.png', __FILE__ )?>" alt="First slide">
							 </div>
							<div class="item">
							   <p>Click on <b>Phone is Offline?</b> button.</p>
							 <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-1.png', __FILE__ )?>" alt="First slide">
							 </div>
						
						   <div class="item">
						   <p>Open miniOrange Authenticator app and click on settings icon on top right corner.</p><br>
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-2.png', __FILE__ )?>" alt="First slide">
						  </div>
						   <div class="item">
						   <p>Click on Sync button below to sync your time with miniOrange Servers. This is a one time sync to avoid otp validation failure.</p><br>
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/token-help-3.png', __FILE__ )?>" alt="First slide">
						  </div>
						   <div class="item">
						   <p>Go to Soft Token tab.</p><br>
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/token-help-2.png', __FILE__ )?>" alt="First slide">
						  </div>
						  <div class="item">
						   <p>Enter the one time passcode shown in miniOrange Authenticator app here.</p><br>
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/token-help-4.png', __FILE__ )?>" alt="First slide">
						  </div>
						</div>
			</div>
			</div>
			</center>
			<br>
		<h3>How to login if your phone is not with you or lost/stolen/discharged ?</h3><hr>
		<center>
	    <div class="mo2f_help_container">
		<div id="myCarousel3" class="carousel slide" data-ride="carousel" >
		<ol class="carousel-indicators">
						<li data-target="#myCarousel3" data-slide-to="0" class="active"></li>
						<li data-target="#myCarousel3" data-slide-to="1"></li>
						<li data-target="#myCarousel3" data-slide-to="2"></li>
						<li data-target="#myCarousel3" data-slide-to="3"></li>
						<li data-target="#myCarousel3" data-slide-to="4"></li>
						
						</ol>
						<div class="carousel-inner" role="listbox">
						<div class="item active">
							      <p>Enter your username and click on login with your phone.</p>
							  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/login-help-1.png', __FILE__ )?>" alt="First slide">
							 </div>
							<div class="item">
							   <p>Click on <b>Forgot Phone?</b> button.</p>
							 <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-1.png', __FILE__ )?>" alt="First slide">
							 </div>
							<div class="item">
							 <p><br></p>
							  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/otp-help-1.png', __FILE__ )?>" alt="First slide">
							</div>
						   <div class="item">
						   <p>Check your email with which you registered and copy the one time passcode.</p><br>
							<img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/otp-help-2.png', __FILE__ )?>" alt="First slide">
							</div>
						  <div class="item">
						   <p><br></p>
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/otp-help-3.png', __FILE__ )?>" alt="First slide">
						  </div>
						</div>
			</div>
			</div>
			</center>
	</div>
	<?php 
	}
	function mo2f_show_instruction_to_allusers($current_user){
		 ?>
	
			<div class="mo2f_table_layout">
				<?php if(get_user_meta($current_user->ID,'mo_2factor_mobile_registration_status',true) != 'MO_2_FACTOR_SUCCESS'){ 
				?>
					<br /><div style="display:block;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">Please <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mobile_configure">click here</a> to configure your mobile to complete the 2 Factor setup.</div>
				<?php } ?>
				
					<h4>Thank you for registering with miniOrange.</h4>
					<h3>Your Profile</h3>
					<table border="1" style="background-color:#FFFFFF; border:1px solid #CCCCCC; border-collapse: collapse; padding:0px 0px 0px 10px; margin:2px; width:100%">
						<tr>
							<td style="width:45%; padding: 10px;"><b>2 Factor Registered Email</b></td>
							<td style="width:55%; padding: 10px;"><?php echo get_user_meta($current_user->ID,'mo_2factor_map_id_with_email',true);?></td>
						</tr>
						<?php if(current_user_can('manage_options')){ ?>
						<tr>
							<td style="width:45%; padding: 10px;"><b>miniOrange Account Email</b></td>
							<td style="width:55%; padding: 10px;"><?php echo get_option('mo2f_email');?></td>
						</tr>
						<tr>
							<td style="width:45%; padding: 10px;"><b>Customer ID</b></td>
							<td style="width:55%; padding: 10px;"><?php echo get_option('mo2f_customerKey');?></td>
						</tr>
						<tr>
							<td style="width:45%; padding: 10px;"><b>API Key</b></td>
							<td style="width:55%; padding: 10px;"><?php echo get_option('mo2f_api_key');?></td>
						</tr>
						<tr>
							<td style="width:45%; padding: 10px;"><b>Token Key</b></td>
							<td style="width:55%; padding: 10px;"><?php echo get_option('mo2f_customer_token');?></td>
						</tr>
						<?php } ?>
					</table><br>
					
				
			</div>	
		
		<br><br>
	
	<?php
	}
	
	function download_instrauction_for_mobile_app(){
	?>
		<table>
			<h3> Download the miniOrange <span style="color: #F78701;">i'm me</span> app</h3><hr>
			<tr>
			<div class="panel-body">
				<td style="width:55%;">
				<p class="content_fonts" style="margin-bottom:2px!important;"><b>iPhone Users</b></p>
				<ol>
				<li>Go to App Store</li>
				<li>Search for <b>miniOrange</b></li>
				<li>Download and install <span style="color: #F78701;"><b>miniOrange authenticator</b></span> app (<b>NOT MOAuth</b>)</li>
				</ol>
					<span><a target="_blank" href="https://itunes.apple.com/us/app/miniorange-authenticator/id796303566?ls=1"><img src="<?php echo plugins_url( 'includes/images/appstore.png' , __FILE__ );?>" style="width:120px; height:45px; margin-left:6px;"></a></span><br><br>
				</td>
				<td>
				<p class="content_fonts" style="margin-bottom:2px!important;margin-top:-10px;"><b>Android Users</b></p>
				<ol>
				<li> Go to Google Play Store.</li>
				<li> Search for <b>miniOrange.</b></li>
				<li>Download and install miniOrange <span style="color: #F78701;"><b>i'm me</b></span> app (<b>NOT i'm me (alpha) </b>)</li>
				</ol>
				<a target="_blank" href="https://play.google.com/store/apps/details?id=com.miniorange.authbeta"><img src="<?php echo plugins_url( 'includes/images/playStore.png' , __FILE__ );?>" style="width:120px; height:=45px; margin-left:6px;"></a>
				</td>
			</div>
			</tr>
		</table>
	<?php
	}
	
	function instruction_for_mobile_registration($current_user){ 
	?>
	<div class="mo2f_table_layout">	
			<div class="col-sm-6 col-md-4">
				<div class="panel panel-success">
					<?php if(!(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION' || get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_PLUGIN_SETTINGS')) { ?>
					
					<br/><div style="display:block;color:red;background-color:rgba(251, 232, 0, 0.15);padding:5px;border:solid 1px rgba(255, 0, 9, 0.36);">Please <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=2factor_setup">Register with miniOrange</a> to configure miniOrange 2 Factor plugin.</div>
					
					<?php }
					if(get_user_meta($current_user->ID,'mo_2factor_mobile_registration_status',true) != 'MO_2_FACTOR_SUCCESS'){
					echo download_instrauction_for_mobile_app(); ?>
					<h3>Scan QR code</h3><hr>
					
					<form name="f" method="post" action="">
						<input type="hidden" name="option" value="mo_auth_refresh_mobile_qrcode" />
						
							<div><h4>Please click on 'Configure your phone' button below to see QR Code.</h4></div>
							<input type="submit" name="submit" class="button button-primary button-large" value="Configure your phone" 
							<?php if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_PLUGIN_SETTINGS' || get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION' ){ 
									} else{ echo 'disabled'; }
							?> />
					</form>
					 <?php }else{
					 ?>
					<h3>Test Mobile Configuration</h3><hr>
					<p>Click on <b>Test Configuration</b> button below to test your mobile authentication.</p>
					
						<form name="f" method="post" action="">
							<input type="hidden" name="option" value="mo_2factor_test_mobile_authentication" />
							<input type="submit" name="submit" class="button button-primary button-large" value="Test Configuration" />
						</form><br>
						
					<?php if(isset($_SESSION[ 'mo2f_show_qr_code' ]) && $_SESSION[ 'mo2f_show_qr_code' ] == 'MO_2_FACTOR_SHOW_QR_CODE' && isset($_POST['option']) && $_POST['option'] == 'mo_2factor_test_mobile_authentication'){
						test_mobile_authentication();
					} ?><br>
					<h3>Reconfigure your mobile</h3><hr>
					<p>You have already configured your phone using <b><?php echo get_user_meta($current_user->ID,'mo_2factor_map_id_with_email',true); ?></b> email. 
					If you want to reconfigure your phone, click on <b>Reconfigure your phone</b> button below.</b></div>
					<form name="f" method="post" action="">
						<input type="hidden" name="option" value="mo_auth_refresh_mobile_qrcode" />
						<input type="submit" name="submit" class="button button-primary button-large" value="Reconfigure your phone" />
					</form>
					<?php } 
					
					if(isset($_SESSION[ 'mo2f_show_qr_code' ]) && $_SESSION[ 'mo2f_show_qr_code' ] == 'MO_2_FACTOR_SHOW_QR_CODE' && isset($_POST['option']) && $_POST['option'] == 'mo_auth_refresh_mobile_qrcode'){
							
							initialize_mobile_registration();
						} 
					 
					?>
					<br><br>
				</div>
			</div>
		
	<?php
	}

	function initialize_mobile_registration() {
		$data = $_SESSION[ 'mo2f_qrCode' ];
		$url = get_option('mo2f_host_name');
		?>
		
			<p><b>Open your miniOrange i'm me app and click on Configure button to scan the QR code. Your phone should have internet connectivity to scan QR code.</b></p>
		
		
			<table class="mo2f_settings_table">
				<div id="displayQrCode"> <?php echo '<img src="data:image/jpg;base64,' . $data . '" />'; ?>
				</div>
			</table>
			<br />
			<div id="mobile_registered" >
			<form name="f" method="post" id="mobile_register_form" action="">
				<input type="hidden" name="option" value="mo_auth_mobile_registration_complete" />
			</form>
			</div>
	
				
		</div>
			<script>
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
	
	function test_mobile_authentication() {
		?>
				
	
			<p><b>Open your miniOrange Authenticator App and click on Authenticate button to scan the QR code.</b></p>
			
			<table class="mo2f_settings_table">
				<div id="displayQrCode"> <?php echo '<img src="data:image/jpg;base64,' . $_SESSION[ 'mo2f_qrCode' ] . '" />'; ?>
				</div>
			</table>
			
			<div id="mobile_registered" >
			<form name="f" method="post" id="mo2f_mobile_authenticate_success_form" action="">
				<input type="hidden" name="option" value="mo2f_mobile_authenticate_success" />
			</form>
			<form name="f" method="post" id="mo2f_mobile_authenticate_error_form" action="">
				<input type="hidden" name="option" value="mo2f_mobile_authenticate_error" />
			</form>
			</div>
	
				
		</div>
			<script>
			var timeout;
			pollMobileValidation();
			function pollMobileValidation()
			{
				var transId = "<?php echo $_SESSION[ 'mo2f_transactionId' ];  ?>";
				var jsonString = "{\"txId\":\""+ transId + "\"}";
				var postUrl = "<?php echo get_option('mo2f_host_name');  ?>" + "/moas/api/auth/auth-status";
				
				jQuery.ajax({
					url: postUrl,
					type : "POST",
					dataType : "json",
					data : jsonString,
					contentType : "application/json; charset=utf-8",
					success : function(result) {
						var status = JSON.parse(JSON.stringify(result)).status;
						if (status == 'SUCCESS') {
							var content = "<div id='success'><img src='" + "<?php echo plugins_url( 'includes/images/right.png' , __FILE__ );?>" + "' /></div>";
							jQuery("#displayQrCode").empty();
							jQuery("#displayQrCode").append(content);
							setTimeout(function(){jQuery("#mo2f_mobile_authenticate_success_form").submit();}, 1000);
						} else if (status == 'ERROR' || status == 'FAILED') {
							var content = "<div id='error'><img src='" + "<?php echo plugins_url( 'includes/images/wrong.png' , __FILE__ );?>" + "' /></div>";
							jQuery("#displayQrCode").empty();
							jQuery("#displayQrCode").append(content);
							setTimeout(function(){jQuery('#mo2f_mobile_authenticate_error_form').submit();}, 1000);
						} else {
							timeout = setTimeout(pollMobileValidation, 3000);
						}
					}
				});
			}
</script>
		<?php
	}
	?>