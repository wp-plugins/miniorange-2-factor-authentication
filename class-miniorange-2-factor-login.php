<?Php
/** miniOrange enables user to log in through mobile authentication as an additional layer of security over password.
    Copyright (C) 2015  miniOrange

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange OAuth
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
/**
This library is miniOrange Authentication Service. 
Contains Request Calls to Customer service.

**/
class Miniorange_Mobile_Login{

	public function my_login_redirect() {
		if (isset($_POST['miniorange_login_submit']) && isset($_POST['miniorange_login_nonce'])){			
			$nonce = $_POST['miniorange_login_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' ) ) {
				update_option('mo2f-login-message','Invalid request');
				$this->mo_auth_show_error_message();
			} else {
				//validation and sanitization
				$username = '';
				if( $this->mo2f_check_empty_or_null( $_POST['mo2fa_username'] ) ) {
					update_option( 'mo2f-login-message', 'Please enter username to proceed');
					$this->mo_auth_show_error_message();
					return;
				} else{
					$username = sanitize_text_field( $_POST['mo2fa_username'] );
				}
				
				if ( username_exists( $username ) ){
					update_option( 'mo2f_login_username', $username);
					$user = new WP_User( $username );
					if(!strcasecmp(wp_sprintf_l( '%l', $user->roles ),'administrator')){
						if(get_option('mo_2factor_admin_mobile_registration_status') == 'MO_2_FACTOR_SUCCESS'){
							update_option( 'mo2f_login_email', $user->user_email);
							$challengeMobile = new Customer_Setup();
							$content = $challengeMobile->send_otp_token($user->user_email, 'MOBILE AUTHENTICATION');
							$response = json_decode($content, true);
							if(json_last_error() == JSON_ERROR_NONE) {
								update_option( 'mo2f-login-qrCode', $response['qrCode']);
								update_option( 'mo2f-login-transactionId' , $response['txId']);
								update_option('mo_2factor_login_status','MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION');
							}else{
								update_option('mo_2factor_login_status','MO_2_FACTOR_SHOW_LOGIN_FORM');
								update_option( 'mo2f-login-message','Invalid request');
								delete_option('mo2f_login_username');
								delete_option('mo2f_login_email');
								$this->mo_auth_show_error_message();
							}
						}else{
							remove_action('login_enqueue_scripts', array( $this, 'mo_2_factor_hide_login'));
							add_action('login_dequeue_scripts', array( $this, 'mo_2_factor_show_login'));
							update_option('mo_2factor_login_status','MO_2_FACTOR_SHOW_USERPASS_LOGIN_FORM');
						}
					}else{
						remove_action('login_enqueue_scripts', array( $this, 'mo_2_factor_hide_login'));
						add_action('login_dequeue_scripts', array( $this, 'mo_2_factor_show_login'));
						update_option('mo_2factor_login_status','MO_2_FACTOR_SHOW_USERPASS_LOGIN_FORM');
					}

			   }else{
					update_option( 'mo2f-login-message','Invalid Username.');
					update_option('mo_2factor_login_status','MO_2_FACTOR_SHOW_LOGIN_FORM');
					$this->mo_auth_show_error_message();
				}
			}	
		}
		if(isset($_POST['miniorange_mobile_validation_nonce'])){
			$nonce = $_POST['miniorange_mobile_validation_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-nonce' ) ) {
				update_option('mo2f-login-message','Invalid request.');
				$this->mo_auth_show_error_message();
			} else {
				$username = get_option('mo2f_login_username');
				$useremail = get_option('mo2f_login_email');
				if( username_exists( $username )) { // user is a member 
					$checkMobileStatus = new Two_Factor_Setup();
					$content = $checkMobileStatus->check_mobile_status(get_option('mo2f-login-transactionId'));
					$response = json_decode($content, true);
					if(json_last_error() == JSON_ERROR_NONE) {
						if($response['status'] == 'SUCCESS'){				
							remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
							add_filter('authenticate', array($this, 'mo2fa_login'), 20, 3);
						}else{
							$this->unknown_activity();
						}
					}else{
							$this->unknown_activity();
					}
				} else{
					$this->unknown_activity();
				}
			}
		}
		
		if (isset($_POST['miniorange_login_back']) && isset($_POST['miniorange_mobile_nonce'])){
			$nonce = $_POST['miniorange_mobile_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-nonce' ) ) {
				update_option('mo2f-login-message','Invalid request.');
				$this->mo_auth_show_error_message();
			} else {
				delete_option('mo2f_login_username');
				delete_option('mo2f_login_email');
				delete_option('mo2f-login-qrCode');
				update_option('mo_2factor_login_status','MO_2_FACTOR_SHOW_LOGIN_FORM');
			}
		}
		
		if (isset($_POST['miniorange_mobile_validation_failed_nonce'])){
			$nonce = $_POST['miniorange_mobile_validation_failed_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-failed-nonce' ) ) {
				update_option('mo2f-login-message','Invalid request.');
				$this->mo_auth_show_error_message();
			} else {
				delete_option('mo2f_login_username');
				delete_option('mo2f_login_email');
				delete_option('mo2f-login-qrCode');
				update_option('mo_2factor_login_status','MO_2_FACTOR_SHOW_LOGIN_FORM');
			}
		}
		
		if (isset($_POST['miniorange_wordpress_nonce'])){
			$nonce = $_POST['miniorange_wordpress_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-wordpress-nonce' ) ) {
				update_option('mo2f-login-message','Invalid request.');
				$this->mo_auth_show_error_message();
			} else {
				remove_action('login_enqueue_scripts', array( $this, 'mo_2_factor_hide_login'));
				add_action('login_dequeue_scripts', array( $this, 'mo_2_factor_show_login'));
				update_option('mo_2factor_login_status','MO_2_FACTOR_SHOW_USERPASS_LOGIN_FORM');
			}
		}
		if(isset($_GET['miniorange_softtoken'])){
			$nonce = $_GET['miniorange_softtoken'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-softtoken' ) ) {
				update_option('mo2f-login-message','Invalid request.');
				$this->mo_auth_show_error_message();
			} else{
				delete_option( 'mo2f-login-qrCode' );
				update_option('mo2f-login-message', 'Enter OTP shown in miniOrange mobile app.');
				$this->mo_auth_show_success_message();
				update_option('mo_2factor_login_status','MO_2_FACTOR_CHALLENGE_SOFT_TOKEN');
			}
		}
		if (isset($_POST['miniorange_soft_token_submit']) and isset($_POST['miniorange_soft_token_nonce'])){
			$nonce = $_POST['miniorange_soft_token_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-soft-token-nonce' ) ) {
				update_option('mo2f-login-message','Invalid request.');
				$this->mo_auth_show_error_message();
			} else {
				$softtoken = '';
				if( $this->mo2f_check_empty_or_null( $_POST[ 'mo2fa_softtoken' ] ) ) {
					update_option( 'mo2f-login-message', 'Please enter softtoken to proceed');
					$this->mo_auth_show_error_message();
					return;
				} else{
					$softtoken = sanitize_text_field( $_POST[ 'mo2fa_softtoken' ] );
				}
				$username = get_option('mo2f_login_username');
				$useremail = get_option('mo2f_login_email');
				$customer = new Customer_Setup();
				$content = json_decode($customer->validate_otp_token( 'SOFT TOKEN', $useremail, null, $softtoken),true);
				if( username_exists( $username )) { // user is a member 
					if(strcasecmp($content['status'], 'SUCCESS') == 0) {
						remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
						add_filter('authenticate', array($this, 'mo2fa_login'), 20, 3);
					}else{
						update_option( 'mo2f-login-message','Invalid OTP. Please try again.');
						update_option('mo_2factor_login_status','MO_2_FACTOR_CHALLENGE_SOFT_TOKEN');
						$this->mo_auth_show_error_message();
					}
				}else{
					$this->unknown_activity();
				}
			}
		}
	}
	
	public function mo2f_check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}
	
	function unknown_activity(){
		update_option( 'mo2f-login-message','Invalid request.');
		update_option('mo_2factor_login_status','MO_2_FACTOR_SHOW_LOGIN_FORM');
		delete_option('mo2f_login_username');
		delete_option('mo2f_login_email');
		delete_option('mo2f-login-qrCode');
		delete_option('mo2f-login-transactionId');
		$this->mo_auth_show_error_message();
	}
	
	function mo2fa_login(){
		$username = get_option('mo2f_login_username');
		$user = new WP_User( $username );
		$user_id = $user->ID;
		wp_set_current_user($user_id, $username);
		wp_set_auth_cookie( $user_id, true );
		wp_redirect(home_url());
		exit;
	}
	
	public function custom_login_enqueue_scripts()
	{
		wp_enqueue_script('jquery');
	}
	
	public function mo_2_factor_hide_login() {
		wp_register_style( 'hide-login', plugins_url( 'includes/css/hide-login.css', __FILE__ ) );
		wp_enqueue_style( 'hide-login' );
	}
	
	function mo_2_factor_show_login() {
		wp_register_style( 'show-login', plugins_url( 'includes/css/show-login.css', __FILE__ ) );
		wp_enqueue_style( 'show-login' );
	}
	
	function mo_auth_success_message() {
		$message = get_option('mo2f-login-message');
		return "<div> <p class='message'>" . $message . "</p></div>";
	}

	function mo_auth_error_message() {
		$id = "login_error";
		$message = get_option('mo2f-login-message');
		return "<div id='" . $id . "'> <p>" . $message . "</p></div>";
	}
	
	private function mo_auth_show_error_message() {
		remove_filter( 'login_message', array( $this, 'mo_auth_success_message') );
		add_filter( 'login_message', array( $this, 'mo_auth_error_message') );
	}
	
	private function mo_auth_show_success_message() {
		remove_filter( 'login_message', array( $this, 'mo_auth_error_message') );
		add_filter( 'login_message', array( $this, 'mo_auth_success_message') );
	}
	
	// login form fields
	public function miniorange_login_form_fields() {
		if(get_option('mo_2factor_login_status') == 'MO_2_FACTOR_SHOW_LOGIN_FORM') {
			$this->mo_2_factor_show_login_page();
		}else if(get_option('mo_2factor_login_status') == 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION'){
			$this->mo_2_factor_show_qr_code();
		}else if(get_option('mo_2factor_login_status') == 'MO_2_FACTOR_SHOW_USERPASS_LOGIN_FORM'){
			$this->mo_2_factor_show_login();
			$this->mo_2_factor_show_wp_login_form();
			?><script>
				jQuery('#user_login').val(<?php echo "'" . get_option('mo2f_login_username') . "'"; ?>);
			</script><?php
		}else if(get_option('mo_2factor_login_status') == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN'){
			$this->mo_2_factor_show_soft_token();
		}else{
			$this->mo_2_factor_show_login_page();
		}
	}
	
	function miniorange_login_footer_form(){
		
		?>
			<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo wp_login_url() ?>" hidden>
				<input type="hidden" name="miniorange_mobile_validation_failed_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-mobile-validation-failed-nonce'); ?>" />
			</form>
			<form name="f" id="mo2f_mobile_validation_form" method="post" action="" hidden>
				<input type="hidden" name="miniorange_mobile_validation_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-mobile-validation-nonce'); ?>" />
			</form>
			<form name="f" id="mo2f_backto_wp_form" method="post" action="" hidden>
				<input type="hidden" name="miniorange_wordpress_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-wordpress-nonce'); ?>" />
			</form>
		<?php
	}
	
	function mo_2_factor_show_wp_login_form(){
	
	?>
		<script>
			var content = '<a href="javascript:void(0)" id="backto_mo" onClick="mo2fa_backtomologin()" style="float:right">← Back To miniOrange Login</a>';
			jQuery('#login').append(content);
			function mo2fa_backtomologin(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
		</script>
	<?php
	}
	
	function mo_2_factor_show_login_page(){
		?>
			<div id="mo_2_factor_login_page">
			
			
				<a href="http://miniorange.com/strong_auth" target="_blank"><img src="<?php echo plugins_url( 'includes/images/miniorange_logo.png' , __FILE__ );?>" style="width:100px;"/></a><br /><br />
				<label style="color:#777;font-size:14px;">Username</label>
				<input type="text" name="mo2fa_username" required="true" autofocus="true" />
					
					<p>
						<input type="hidden" name="miniorange_login_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-login-nonce'); ?>" />
						<input type="submit" name="miniorange_login_submit" id="miniorange_login_submit" class="button button-primary button-large" value="Login with miniOrange" />
					</p><br /><br />
		<?php
	}
	
	public function mo_2_factor_show_soft_token(){
	?>
		<div id="mo_2_factor_soft_token_page">
			<a href="http://miniorange.com/strong_auth" target="_blank"><img src="<?php echo plugins_url( 'includes/images/miniorange_logo.png' , __FILE__ );?>" style="width:100px;"/></a><br />
				<br /><label style="color:#777;font-size:14px;">Validate OTP</label>
				<div id="displaySoftToken"><center><input type="text" name="mo2fa_softtoken" required="true" autofocus="true" /></center></div>
					
					<p>
						<input type="hidden" name="miniorange_soft_token_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-soft-token-nonce'); ?>" />
						<input type="submit" name="miniorange_soft_token_submit" id="miniorange_soft_token_submit" style="float:left;" class="button button-primary button-large" value="Validate" />
						<input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button button-primary button-large" value="Back To Login" style="float:right;background: #24890d;color: #ffffff;border: 1px solid #24890d;cursor: hand;" />
					</p><br />
		</div>
		<script>
			function mologinback(){
				<?php update_option('mo_2factor_login_status','MO_2_FACTOR_SHOW_LOGIN_FORM'); ?>
				jQuery('#mo2f_backto_mo_loginform').submit();
			 }
		</script>
	<?php
	}
	
	public function mo_2_factor_show_qr_code(){
		?>
		<div id="mo_2_factor_qr_code_page">
			
			
				<a href="http://miniorange.com/strong_auth" target="_blank"><img src="<?php echo plugins_url( 'includes/images/miniorange_logo.png' , __FILE__ );?>" style="width:100px;"/></a><br />
				<h3><center>Mobile Authentication</center></h3><br />
				<div id="showQrCode"><center> <?php echo '<img src="data:image/jpg;base64,' . get_option('mo2f-login-qrCode') . '" />'; ?></center></div>
					
					<p>
						<center><a href="<?php echo wp_login_url() . '/?miniorange_softtoken=' . wp_create_nonce('miniorange-2-factor-softtoken') ?>">Click here if your phone is offline</a></center><br />
						<center><input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button button-primary button-large" value="Back To Login" style="float:right;background: #24890d;color: #ffffff;border: 1px solid #24890d;cursor: hand;" /></center>
					</p><br />
			</div> 
			 
			 <script>
			var timeout;
			pollMobileValidation();
			function pollMobileValidation()
			{
				var transId = "<?php echo get_option('mo2f-login-transactionId');  ?>";
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
							var content = "<div id='success'><center><img src='" + "<?php echo plugins_url( 'includes/images/right.png' , __FILE__ );?>" + "' /></center></div>";
							jQuery("#showQrCode").empty();
							jQuery("#showQrCode").append(content);
							setTimeout(function(){jQuery("#mo2f_mobile_validation_form").submit();}, 1000);
						} else if (status == 'ERROR' || status == 'FAILED') {
							var content = "<div id='error'><center><img src='" + "<?php echo plugins_url( 'includes/images/wrong.png' , __FILE__ );?>" + "' /></center></div>";
							jQuery("#showQrCode").empty();
							jQuery("#showQrCode").append(content);
							<?php update_option('mo_2factor_login_status','MO_2_FACTOR_SHOW_LOGIN_FORM'); ?>
							setTimeout(function(){jQuery('#mo2f_backto_mo_loginform').submit();}, 1000);
						} else {
							timeout = setTimeout(pollMobileValidation, 3000);
						}
					}
				});
			}
			
			function mologinback(){
				<?php update_option('mo_2factor_login_status','MO_2_FACTOR_SHOW_LOGIN_FORM'); ?>
				jQuery('#mo2f_backto_mo_loginform').submit();
			 }
			 </script>
			 
	<?php
	}
}	