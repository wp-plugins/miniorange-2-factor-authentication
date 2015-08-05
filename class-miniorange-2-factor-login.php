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
		if( ! session_id() ) {
			session_start();
		}
	
		if (isset($_POST['miniorange_login_nonce'])){			
			$nonce = $_POST['miniorange_login_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request';
				$this->mo_auth_show_error_message();
			} else {
				//validation and sanitization
				$username = '';
				if( MO2f_Utility::mo2f_check_empty_or_null( $_POST['mo2fa_username'] ) ) {
					$_SESSION['mo2f-login-message'] = 'Please enter username to proceed';
					$this->mo_auth_show_error_message();
					return;
				} else{
					$username = sanitize_text_field( $_POST['mo2fa_username'] );
				}
				
				if ( username_exists( $username ) ){ /*if username exists in wp site */
					$user = new WP_User( $username );
					$_SESSION[ 'mo2f_current_user' ] = $user;
					if(!strcasecmp(wp_sprintf_l( '%l', $user->roles ),'administrator')){
						if(!get_option('mo2f_admin_disabled_status')){  /*checking if plugin is activated for admins */
							$this->mo2f_login_verification($user);
						}else{
							$_SESSION['mo2f-login-message'] = 'You can login into your account using password. To use \'Login with your phone\' feature, you have to enable it.';
							$this->mo_auth_show_success_message();
							$this->mo2f_redirectto_wp_login();
						}
					}else{
						if(get_option('mo2f_disabled_status')){ /*checking if plugin is activated for all other roles */
							$this->mo2f_login_verification($user);
						}else{
							$_SESSION['mo2f-login-message'] = 'You can login into your account using password. Your Administrator has not enabled \'Login with your phone\' feature for you. Please contact your Administrator.';
							$this->mo_auth_show_success_message();
							$this->mo2f_redirectto_wp_login();
						}
					}
			   }else{
					$this->remove_current_activity();
					$_SESSION['mo2f-login-message'] = 'Invalid Username.';
					$this->mo_auth_show_error_message();
				}
			}	
		}
		if(isset($_POST['miniorange_mobile_validation_nonce'])){ /*check mobile validation */
			$nonce = $_POST['miniorange_mobile_validation_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-nonce' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request.';
				$this->mo_auth_show_error_message();
			} else {
				$currentuser = $_SESSION[ 'mo2f_current_user' ];
				$username = $currentuser->user_login;
				if( username_exists( $username )) { // user is a member 
					$checkMobileStatus = new Two_Factor_Setup();
					$content = $checkMobileStatus->check_mobile_status($_SESSION[ 'mo2f-login-transactionId' ]);
					$response = json_decode($content, true);
					if(json_last_error() == JSON_ERROR_NONE) {
						if($response['status'] == 'SUCCESS'){				
							remove_filter('authenticate', 'wp_authenticate_username_password', 10, 3);
							add_filter('authenticate', array($this, 'mo2fa_login'), 10, 3);
						}else{
							$this->remove_current_activity();
							$_SESSION['mo2f-login-message'] = 'Invalid request.';
							$this->mo_auth_show_error_message();
						}
					}else{
						$this->remove_current_activity();
						$_SESSION['mo2f-login-message'] = 'Invalid request.';
						$this->mo_auth_show_error_message();
					}
				} else{
					$this->remove_current_activity();
					$_SESSION['mo2f-login-message'] = 'Invalid request.';
					$this->mo_auth_show_error_message();
				}
			}
		}
		
		if (isset($_POST['miniorange_mobile_validation_failed_nonce'])){ /*Back to miniOrange Login Page if mobile validation failed and from back button of mobile challenge, soft token and default login*/
			$nonce = $_POST['miniorange_mobile_validation_failed_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-mobile-validation-failed-nonce' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request.';
				$this->mo_auth_show_error_message();
			} else {
				$this->remove_current_activity();
			}
		}
		
		if(isset($_POST['miniorange_forgotphone'])){ /*Click on the link of forgotphone */
			$nonce = $_POST['miniorange_forgotphone'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-forgotphone' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request.';
				$this->mo_auth_show_error_message();
			} else{
				$customer = new Customer_Setup();
				$id = $_SESSION[ 'mo2f_current_user' ]->ID;
				$content = json_decode($customer->send_otp_token(get_user_meta($id,'mo_2factor_map_id_with_email',true),'EMAIL',get_option('mo2f_customerKey'),get_option('mo2f_api_key')), true);
				if(strcasecmp($content['status'], 'SUCCESS') == 0) {
					unset($_SESSION[ 'mo2f-login-qrCode' ]);
					unset($_SESSION[ 'mo2f-login-transactionId' ]);
					$_SESSION['mo2f-login-message'] =  'A one time passcode has been sent to <b>' . ( get_user_meta($id,'mo_2factor_map_id_with_email',true) ) . '</b>. Please enter the OTP to verify your identity.';
					$_SESSION[ 'mo2f-login-transactionId' ] = $content['txId'];
					$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL';
					$this->mo_auth_show_success_message();
				}else{
					$_SESSION['mo2f-login-message'] = 'Error:OTP over Email';
					$this->mo_auth_show_success_message();
				}
			}
		}
		
		if(isset($_POST['miniorange_softtoken'])){ /*Click on the link of phone is offline */
			$nonce = $_POST['miniorange_softtoken'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-softtoken' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request.';
				$this->mo_auth_show_error_message();
			} else{
				unset($_SESSION[ 'mo2f-login-qrCode' ]);
				unset($_SESSION[ 'mo2f-login-transactionId' ]);
				$_SESSION['mo2f-login-message'] = 'Please enter the one time passcode shown in the miniOrange authenticator app.';
				$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN';
			}
		}
		if (isset($_POST['miniorange_soft_token_nonce'])){ /*Validate Soft Token */
			$nonce = $_POST['miniorange_soft_token_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-soft-token-nonce' ) ) {
				$_SESSION['mo2f-login-message'] = 'Invalid request.';
				$this->mo_auth_show_error_message();
			} else {
				$softtoken = '';
				if( MO2f_utility::mo2f_check_empty_or_null( $_POST[ 'mo2fa_softtoken' ] ) ) {
					$_SESSION['mo2f-login-message'] = 'Please enter OTP to proceed';
					$this->mo_auth_show_error_message();
					return;
				} else{
					$softtoken = sanitize_text_field( $_POST[ 'mo2fa_softtoken' ] );
				}
				$currentuser = isset($_SESSION[ 'mo2f_current_user' ]) ? $_SESSION[ 'mo2f_current_user' ] : null;
				if(isset($_SESSION[ 'mo2f_current_user' ])){
					$customer = new Customer_Setup();
					$content ='';
					if(isset($_SESSION[ 'mo_2factor_login_status' ]) && $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL'){
						$content = json_decode($customer->validate_otp_token( 'EMAIL', null, $_SESSION[ 'mo2f-login-transactionId' ], $softtoken ),true);
					}else if(isset($_SESSION[ 'mo_2factor_login_status' ]) && $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN'){
						$content = json_decode($customer->validate_otp_token( 'SOFT TOKEN', get_user_meta($currentuser->ID,'mo_2factor_map_id_with_email',true), null, $softtoken),true);
					}else{
						$this->remove_current_activity();
						$_SESSION['mo2f-login-message'] = 'Invalid request.';
						$this->mo_auth_show_error_message();
					}
					
					if( username_exists( $currentuser->user_login )) { // user is a member 
						if(strcasecmp($content['status'], 'SUCCESS') == 0) {
							remove_filter('authenticate', 'wp_authenticate_username_password', 10, 3);
							add_filter('authenticate', array($this, 'mo2fa_login'), 10, 3);
						}else{
							$message = $_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' ? 'Invalid OTP. <b>Please try again after clicking on the Settings icon in the app and press Sync button.</b>' : 'Invalid OTP. Please try again';
							$_SESSION['mo2f-login-message'] = $message;
							$_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' ? $_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' : $_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL';
							$this->mo_auth_show_error_message();
						}
					}else{
						$this->remove_current_activity();
						$_SESSION['mo2f-login-message'] = 'Invalid request.';
						$this->mo_auth_show_error_message();
					}
				}else{
					$this->remove_current_activity();
					$_SESSION['mo2f-login-message'] = 'Invalid request.';
					$this->mo_auth_show_error_message();
				}
			}
		}
	}
	
	function remove_current_activity(){
		unset($_SESSION[ 'mo2f_current_user' ]);
		unset($_SESSION[ 'mo_2factor_login_status' ]);
		unset($_SESSION[ 'mo2f-login-qrCode' ]);
		unset($_SESSION[ 'mo2f-login-transactionId' ]);
		unset($_SESSION[ 'mo2f-login-message' ]);
	}
	
	function mo2fa_login(){
		if(isset($_SESSION[ 'mo2f_current_user' ])){
			$currentuser = $_SESSION[ 'mo2f_current_user' ];
			$user_id = $currentuser->ID;
			wp_set_current_user($user_id, $currentuser->user_login);
			wp_set_auth_cookie( $user_id, true );
			$this->remove_current_activity();
			$this->redirect_user_to($currentuser);
			exit;
		}else{
			$this->remove_current_activity();
		}
	}
	
	function redirect_user_to($user){
		if(!strcasecmp(wp_sprintf_l( '%l', $user->roles ),'administrator')){
			if(!get_option('mo2f_admin_disabled_status') && get_option( 'mo_2factor_admin_registration_status') == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' && get_option( 'mo2f_miniorange_admin') != $user->ID && get_user_meta($user->ID,'mo_2factor_mobile_registration_status',true) != 'MO_2_FACTOR_SUCCESS'){
		
				wp_redirect( admin_url().'admin.php?page=miniOrange_2_factor_settings');
			}else{
				wp_redirect( admin_url() );
			}
		}else{
			if( get_option('mo2f_disabled_status') && get_option( 'mo_2factor_admin_registration_status') == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' && get_option( 'mo2f_miniorange_admin') != $user->ID && get_user_meta($user->ID,'mo_2factor_mobile_registration_status',true) != 'MO_2_FACTOR_SUCCESS'){
				
				wp_redirect( admin_url().'admin.php?page=miniOrange_2_factor_settings');
			}else{
				
				wp_redirect( home_url());
			}
		}
	}
	
	function mo2fa_default_login($user,$password){
		if(!MO2f_Utility::mo2f_check_empty_or_null($user->user_login) && !MO2f_Utility::mo2f_check_empty_or_null($password)){
			$user_id = $user->ID;
			if(!strcasecmp(wp_sprintf_l( '%l', $user->roles ),'administrator')){
				if(!get_option('mo2f_admin_disabled_status')){  /*checking if plugin is activated for admins */
					$this->mo2f_verify_user_mobile_registration($user,$password);
				}else{
					$this->mo2f_verify_and_authenticate_userlogin($user,$password);
				}
			}else{
				if(get_option('mo2f_disabled_status')){ /*checking if plugin is activated for all other roles */
					$this->mo2f_verify_user_mobile_registration($user,$password);
				}else{
					$this->mo2f_verify_and_authenticate_userlogin($user,$password);
				}
			}
		}
	}
	
	function mo2f_verify_user_mobile_registration($user,$password){
		if(get_user_meta($user->ID,'mo_2factor_mobile_registration_status',true) == 'MO_2_FACTOR_SUCCESS'){
			unset($_SESSION[ 'mo_2factor_login_status' ]);
			?>
			<style>
				div#login_error{
					display:none !important;
				}
			</style>
			<?php
			$message = 'Login with password has been disabled for you. Please try login with your phone.';
			$_SESSION['mo2f-login-message'] = $message;
			$this->mo_auth_show_error_message();
		}else{
			$this->mo2f_verify_and_authenticate_userlogin($user,$password);
		}
	}
	
	function mo2f_verify_and_authenticate_userlogin($user,$password){
		if(wp_check_password( $password, $user->user_pass, $user->ID )){
			if( email_exists( $user->user_email ) ) { // user is a member
				$user = get_user_by('email', $user->user_email );
				$user_id = $user->ID;
				wp_set_auth_cookie( $user_id, true );
				$this->remove_current_activity();
				$this->redirect_user_to($user);
				exit;
			}
		}
	}


	
	function mo2f_login_verification($user){
		if(get_user_meta($user->ID,'mo_2factor_mobile_registration_status',true) == 'MO_2_FACTOR_SUCCESS'){ /* Allow only if user's mobile is configured */
			$useragent = $_SERVER['HTTP_USER_AGENT'];
			if(strpos($useragent,'Mobi') !== false){
				unset($_SESSION[ 'mo2f-login-qrCode' ]);
				unset($_SESSION[ 'mo2f-login-transactionId' ]);
				$_SESSION['mo2f-login-message'] = 'Please enter the one time passcode shown in the miniOrange authenticator app.';
				$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN';
			}else{
				$challengeMobile = new Customer_Setup();
				$content = $challengeMobile->send_otp_token(get_user_meta($user->ID,'mo_2factor_map_id_with_email',true), 'MOBILE AUTHENTICATION',get_option('mo2f_customerKey'),get_option('mo2f_api_key'));
				$response = json_decode($content, true);
				if(json_last_error() == JSON_ERROR_NONE) { /* Generate Qr code */
					$_SESSION[ 'mo2f-login-qrCode' ] = $response['qrCode'];
					$_SESSION[ 'mo2f-login-transactionId' ] = $response['txId'];
					$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION';
				}else{
					$_SESSION['mo2f-login-message'] = 'Invalid request';
					unset($_SESSION[ 'mo2f_current_user' ]);
					$this->mo_auth_show_error_message();
				}
			}
		}else{ /*if mobile is not configured then redirect the user to default login */
			$_SESSION['mo2f-login-message'] = 'Please login using password and configure your mobile.';
			$this->mo_auth_show_success_message();
			$this->mo2f_redirectto_wp_login();
		}
	}
	
	function mo2f_redirectto_wp_login(){
		remove_action('login_enqueue_scripts', array( $this, 'mo_2_factor_hide_login'));
		add_action('login_dequeue_scripts', array( $this, 'mo_2_factor_show_login'));
		if(get_option('mo2f_show_loginwith_phone')){
			$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_LOGIN_WHEN_PHONELOGIN_ENABLED';
		}else{
			$_SESSION[ 'mo_2factor_login_status' ] = 'MO_2_FACTOR_SHOW_USERPASS_LOGIN_FORM';
		}
	}
	
	public function custom_login_enqueue_scripts(){
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'bootstrap_script', plugins_url('includes/js/bootstrap.min.js', __FILE__ ));
	}
	
	public function mo_2_factor_hide_login() {
		wp_register_style( 'hide-login', plugins_url( 'includes/css/hide-login.css', __FILE__ ) );
		wp_register_style( 'bootstrap', plugins_url( 'includes/css/bootstrap.min.css', __FILE__ ) );
		
		wp_enqueue_style( 'hide-login' );
		wp_enqueue_style( 'bootstrap' );
		
	}
	
	function mo_2_factor_show_login() {
		if(get_option('mo2f_show_loginwith_phone')){
			wp_register_style( 'show-login', plugins_url( 'includes/css/hide-login-form.css', __FILE__ ) );
		}else{
			wp_register_style( 'show-login', plugins_url( 'includes/css/show-login.css', __FILE__ ) );
		}
		wp_enqueue_style( 'show-login' );
	}
	
	function mo_2_factor_show_login_with_password_when_phonelogin_enabled(){
		wp_register_style( 'show-login', plugins_url( 'includes/css/show-login.css', __FILE__ ) );
		wp_enqueue_style( 'show-login' );
	}
	
	function mo_auth_success_message() {
		$message = $_SESSION['mo2f-login-message'];
		return "<div> <p class='message'>" . $message . "</p></div>";
	}

	function mo_auth_error_message() {
		$id = "login_error1";
		$message = $_SESSION['mo2f-login-message'];
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
		if( !session_id() ){
			session_start();
		}
		if(!get_option('mo2f_show_loginwith_phone')){
			$login_status = isset($_SESSION[ 'mo_2factor_login_status' ]) ? $_SESSION[ 'mo_2factor_login_status' ] : null;
			if($this->miniorange_check_mobile_status($login_status)){			
				$this->mo_2_factor_show_qr_code();
			}else if($login_status == 'MO_2_FACTOR_SHOW_USERPASS_LOGIN_FORM'){
				$this->mo_2_factor_show_login();
				$this->mo_2_factor_show_wp_login_form();
			}else if($this->miniorange_check_status($login_status)){
				$this->mo_2_factor_show_soft_token();
			}else{
				$this->mo_2_factor_show_login();
				$this->mo_2_factor_show_wp_login_form();
			}
		}else{
			
			$login_status_phone_enable = isset($_SESSION[ 'mo_2factor_login_status' ]) ? $_SESSION[ 'mo_2factor_login_status' ] : '';
			if($this->miniorange_check_mobile_status($login_status_phone_enable)){			
				$this->mo_2_factor_show_qr_code();
			}else if($this->miniorange_check_status($login_status_phone_enable)){
				$this->mo_2_factor_show_soft_token();
			}else if($login_status_phone_enable == 'MO_2_FACTOR_LOGIN_WHEN_PHONELOGIN_ENABLED' && isset($_POST['miniorange_login_nonce']) && wp_verify_nonce( $_POST['miniorange_login_nonce'], 'miniorange-2-factor-login-nonce' )){
				$this->mo_2_factor_show_login_with_password_when_phonelogin_enabled();
				$this->mo_2_factor_show_wp_login_form_when_phonelogin_enabled();
				?><script>
					jQuery('#user_login').val(<?php echo "'" . $_SESSION[ 'mo2f_current_user' ]->user_login . "'"; ?>);
				</script><?php
			}else{
				$this->mo_2_factor_show_login();
				$this->mo_2_factor_show_wp_login_form();
			}
		}
	}
	
	function miniorange_check_mobile_status($login_status){
		if($login_status == 'MO_2_FACTOR_CHALLENGE_MOBILE_AUTHENTICATION'){
			$nonce = '';
			if(isset($_POST['miniorange_login_nonce']) ){
				$nonce = $_POST['miniorange_login_nonce'];
				if(wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' )){
					return true;
				}
			}else if(isset($_POST['miniorange_forgotphone'])){
				$nonce = $_POST['miniorange_forgotphone'];
				if(wp_verify_nonce($nonce,'miniorange-2-factor-forgotphone')){
					return true;
				}
			}
		}
		return false;
	}
	
	function miniorange_check_status($login_status){
		if($login_status == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN' || $login_status == 'MO_2_FACTOR_CHALLENGE_OTP_OVER_EMAIL'){
			$nonce = '';
			$useragent = $_SERVER['HTTP_USER_AGENT'];
			if(strpos($useragent,'Mobi') !== false){
				if(isset($_POST['miniorange_login_nonce']) ){
					$nonce = $_POST['miniorange_login_nonce'];
					if(wp_verify_nonce( $nonce, 'miniorange-2-factor-login-nonce' )){
						return true;
					}
				}else if(isset($_POST['miniorange_soft_token_nonce'])){
					$nonce = $_POST['miniorange_soft_token_nonce'];
					if(wp_verify_nonce($nonce,'miniorange-2-factor-soft-token-nonce')){
						return true;
					}
				}
				return false;
			}else{
				if(isset($_POST['miniorange_softtoken'])){
					$nonce = $_POST['miniorange_softtoken'];
					if(wp_verify_nonce($nonce,'miniorange-2-factor-softtoken')){
						return true;
					}		
				}else if(isset($_POST['miniorange_forgotphone'])){
					$nonce = $_POST['miniorange_forgotphone'];
					if(wp_verify_nonce($nonce,'miniorange-2-factor-forgotphone')){
						return true;
					}
				}else if(isset($_POST['miniorange_soft_token_nonce'])){
					$nonce = $_POST['miniorange_soft_token_nonce'];
					if(wp_verify_nonce($nonce,'miniorange-2-factor-soft-token-nonce')){
						return true;
					}
				}
				return false;
			}
		}
		return false;
	}
	
	function miniorange_login_footer_form(){
		
		?>
			<form name="f" id="mo2f_show_softtoken_loginform" method="post" action="" hidden>
				<input type="hidden" name="miniorange_softtoken" value="<?php echo wp_create_nonce('miniorange-2-factor-softtoken'); ?>" />
			</form>
			<form name="f" id="mo2f_show_forgotphone_loginform" method="post" action="" hidden>
				<input type="hidden" name="miniorange_forgotphone" value="<?php echo wp_create_nonce('miniorange-2-factor-forgotphone'); ?>" />
			</form>
			<form name="f" id="mo2f_backto_mo_loginform" method="post" action="<?php echo wp_login_url(); ?>" hidden>
				<input type="hidden" name="miniorange_mobile_validation_failed_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-mobile-validation-failed-nonce'); ?>" />
			</form>
			<form name="f" id="mo2f_mobile_validation_form" method="post" action="" hidden>
				<input type="hidden" name="miniorange_mobile_validation_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-mobile-validation-nonce'); ?>" />
			</form>
			<form name="f" id="mo2f_show_qrcode_loginform" method="post" action="" hidden>
				<input type="text" name="mo2fa_username" id="mo2fa_username" hidden/>
				<input type="hidden" name="miniorange_login_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-login-nonce'); ?>" />
			</form>
			<form name="f" id="mo2f_submitotp_loginform" method="post" action="" hidden>
				<input type="text" name="mo2fa_softtoken" id="mo2fa_softtoken" hidden/>
				<input type="hidden" name="miniorange_soft_token_nonce" value="<?php echo wp_create_nonce('miniorange-2-factor-soft-token-nonce'); ?>" />
			</form>

		<?php
	}
	
	function mo_2_factor_show_wp_login_form_when_phonelogin_enabled(){
	?>
		<script>
			var content = '<a href="javascript:void(0)" id="backto_mo" onClick="mo2fa_backtomologin()" style="float:right">← Back</a>';
			jQuery('#login').append(content);
			function mo2fa_backtomologin(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			}
		</script>
	<?php
	}
	
	function mo_2_factor_show_wp_login_form(){
	?>
		<div class="mo2f-login-container">
			<?php if(!get_option('mo2f_show_loginwith_phone')){ ?>
			<div style="position: relative" class="or-container">
				<div style="border-bottom: 1px solid #EEE; width: 90%; margin: 0 5%; z-index: 1; top: 50%; position: absolute;"></div>
				<h2 style="color: #666; margin: 0 auto 20px auto; padding: 3px 0; text-align:center; background: white; width: 20%; position:relative; z-index: 2;">or</h2>
			</div>
			<?php } ?>
			<div class="mo2f-button-container">
				<input type="text" name="mo2fa_usernamekey" id="mo2fa_usernamekey" autofocus="true" placeholder="Username"/>
					<p>
						<input type="button" name="miniorange_login_submit"  style="width:100%;" onclick="mouserloginsubmit();" id="miniorange_login_submit" class="miniorange-button button-add" value="Login with your phone" />
					</p>
					<?php if(!get_option('mo2f_show_loginwith_phone')){ ?><br /><br /><?php } ?>
			</div>
		</div>
		
		<script>
			function mouserloginsubmit(){
				var username = jQuery('#mo2fa_usernamekey').val();
				document.getElementById("mo2f_show_qrcode_loginform").elements[0].value = username;
				jQuery('#mo2f_show_qrcode_loginform').submit();
				
			 }
			 
			 jQuery('#mo2fa_usernamekey').keypress(function(e){
				  if(e.which == 13){//Enter key pressed
					e.preventDefault();
					var username = jQuery('#mo2fa_usernamekey').val();
					document.getElementById("mo2f_show_qrcode_loginform").elements[0].value = username;
					jQuery('#mo2f_show_qrcode_loginform').submit();
				  }
				 
			});
		</script>
	<?php
	}
	
	public function mo_2_factor_show_soft_token(){
	?>
		<div class="miniorange_soft_auth">
			<center>
			<div  id="otpMessage"> 
				<p class='mo2fa_display_message' style="margin:0px;width:360px;"><?php echo $_SESSION['mo2f-login-message']; ?></p>
			</div> 
			  </center>
			<br><br>
			<div id="mo_2_factor_soft_token_page" class="miniorange-inner-login-container" style="margin:0px auto !important;width:380px;">
				<div id="showOTP">
					<br />
					<center><a href="#showOTPHelp" id="otpHelpLink"><h3>See How It Works ?</h3></a></center>
					<br />
				
					<div id="displaySoftToken"><center><input type="text" name="mo2fa_softtokenkey" style="width:75%;" placeholder="Enter OTP" id="mo2fa_softtokenkey" required="true" autofocus="true" /></center></div>
							
					<span><input type="button" name="miniorange_soft_token_submit" onclick="mootploginsubmit();" id="miniorange_soft_token_submit" class="miniorange-button" style="margin-left:12%;" value="Validate" />
						
					<input type="button" name="miniorange_login_back" onclick="mologinback();" style="margin-left:26%;" id="miniorange_login_back" class="button-green" value="←Back To Login"/>
						
					</span><br /><br />
				</div>
				<div id="showOTPHelp" class="showOTPHelp" hidden>
				<br>
					<center><a href="#showOTP" id="otpLink"><h3>&#8592; Go Back</h3></a>
				<br>
				<div id="myCarousel" class="carousel slide" data-ride="carousel">
					<!-- Indicators -->
     
					<?php if($_SESSION[ 'mo_2factor_login_status' ] == 'MO_2_FACTOR_CHALLENGE_SOFT_TOKEN'){ ?>
						<ol class="carousel-indicators">
						<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
						<li data-target="#myCarousel" data-slide-to="1"></li>
						<li data-target="#myCarousel" data-slide-to="2"></li>
						<li data-target="#myCarousel" data-slide-to="3"></li>
						
						</ol>
						<div class="carousel-inner" role="listbox">
      
						
						   <div class="item active">
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
					<?php } else { ?>
						<ol class="carousel-indicators">
						<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
						<li data-target="#myCarousel" data-slide-to="1"></li>
						<li data-target="#myCarousel" data-slide-to="2"></li>
						
						</ol>
						<div class="carousel-inner" role="listbox">
							<div class="item active">
							  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/otp-help-1.png', __FILE__ )?>" alt="First slide">
							</div>
						   <div class="item">
						   <p>Check your email with which you registered and copy the one time passcode.</p><br>
							<img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/otp-help-2.png', __FILE__ )?>" alt="First slide">
							</div>
						  <div class="item">
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/otp-help-3.png', __FILE__ )?>" alt="First slide">
						  </div>
						 </div>
					<?php } ?>
				</div>
				</div>	
				
				<div class="mo2f_powered_by_div">Powered by <a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange"></div></a></div>
			
			</div>
		</div>
		<script>
			
		    jQuery('#otpHelpLink').click(function() {
				jQuery('#showOTPHelp').show();
				jQuery('#showOTP').hide();
				jQuery('#otpMessage').hide();
			});
			jQuery('#otpLink').click(function() {
				jQuery('#showOTPHelp').hide();
				jQuery('#showOTP').show();
				jQuery('#otpMessage').show();
			});
			jQuery("body.login div#login").before(jQuery('.miniorange_soft_auth'));
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			 }
			  function mootploginsubmit(){
				var otpkey = jQuery('#mo2fa_softtokenkey').val();
				document.getElementById("mo2f_submitotp_loginform").elements[0].value = otpkey;
				jQuery('#mo2f_submitotp_loginform').submit();
				
			 }
			 
			 jQuery('#mo2fa_softtokenkey').keypress(function(e){
				  if(e.which == 13){//Enter key pressed
					e.preventDefault();
					var otpkey = jQuery('#mo2fa_softtokenkey').val();
					document.getElementById("mo2f_submitotp_loginform").elements[0].value = otpkey;
					jQuery('#mo2f_submitotp_loginform').submit();
				  }
				 
			});
			
			

		</script>
	<?php
	}
	
	public function mo_2_factor_show_qr_code(){
		?>
		<div class="miniorange_mobile_auth">
			<?php if(isset($_SESSION['mo2f-login-message']) && $_SESSION['mo2f-login-message'] == 'Error:OTP over Email'){ ?>
			<div class="mo2fa_messages_container" id="otpMessage" style="margin-top:-5%;padding-bottom:1%;"> 
				<p class='mo2fa_display_message'><?php echo 'Error occurred while sending OTP over email. Please try again.'; ?></p>
			</div> 
			<?php } ?>
			
			<div id="mo_2_factor_qr_code_page" class="miniorange-inner-login-container">
				<div id="scanQRSection">
					<br>
						<center><a href="#showQRHelp" id="helpLink"><h3>See How It Works ?</h3></a></center>
					<div style="margin-bottom:10%;padding-top:6%;">
					<center>
					<h3>Identify yourself by scanning the QR code with miniOrange Authenticator app.</h3>
					</center></div>
						
					<div id="showQrCode" style="margin-bottom:10%;"><center> <?php echo '<img src="data:image/jpg;base64,' . $_SESSION[ 'mo2f-login-qrCode' ] . '" />'; ?></center>
					</div>
							
					
					<span style="padding-right:2%;"><center>
						<?php if(!get_option('mo2f_enable_forgotphone')){ ?>
						<input type="button" name="miniorange_login_forgotphone" onclick="mologinforgotphone();" id="miniorange_login_forgotphone" class="miniorange-button" value="Forgot Phone?" />
						<?php } ?>
						
						<input type="button" name="miniorange_login_offline" onclick="mologinoffline();" id="miniorange_login_offline" class="miniorange-button" value="Phone is Offline?" /></center></span>
						
						<div><center><input type="button" name="miniorange_login_back" onclick="mologinback();" id="miniorange_login_back" class="button-green" value="←Back To Login" /></center></div>
					<br />
				
				</div>
				<div id="showQRHelp" class="showQRHelp" hidden>
					<br>
					<center><a href="#showQRHelp" id="qrLink"><h3>&#8592;Back to Scan QR Code.</h3></a>
					<br>
						<div id="myCarousel" class="carousel slide" data-ride="carousel">
						  <!-- Indicators -->
						  <ol class="carousel-indicators">
							<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
							<li data-target="#myCarousel" data-slide-to="1"></li>
							<li data-target="#myCarousel" data-slide-to="2"></li>
							<li data-target="#myCarousel" data-slide-to="3"></li>
							<li data-target="#myCarousel" data-slide-to="4"></li>
						  </ol>
						<div class="carousel-inner" role="listbox">
							<div class="item active">
							  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-1.png', __FILE__ )?>" alt="First slide">
							</div>
						   <div class="item">
							<p>Open miniOrange Authenticator app and click on Authenticate.</p><br>
							<img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-2.png', __FILE__ )?>" alt="First slide">
						 
						  </div>
						  <div class="item">
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-3.png', __FILE__ )?>" alt="First slide">
						  </div>
						  <div class="item">
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-4.png', __FILE__ )?>" alt="First slide">
						  </div>
						  <div class="item">
						  <img class="first-slide" src="<?php echo plugins_url( 'includes/images/help/qr-help-5.png', __FILE__ )?>" alt="First slide">
						  </div>
						</div>
						</div>
					</center>
				</div>
				<div class="mo2f_powered_by_div">Powered by <a target="_blank" href="http://miniorange.com/2-factor-authentication"><div class="mo2f_powered_by_miniorange"></div></a></div>
			</div>
		</div>
		
		<script>
			
			
			jQuery("body.login div#login").before(jQuery('.miniorange_mobile_auth'));
			var timeout;
			pollMobileValidation();
			function pollMobileValidation()
			{
				var transId = "<?php echo $_SESSION[ 'mo2f-login-transactionId' ];  ?>";
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
							setTimeout(function(){jQuery('#mo2f_backto_mo_loginform').submit();}, 1000);
						} else {
							timeout = setTimeout(pollMobileValidation, 3000);
						}
					}
				});
			}
			jQuery('#myCarousel').carousel('pause');
			jQuery('#helpLink').click(function() {
				jQuery('#showQRHelp').show();
				jQuery('#scanQRSection').hide();
				
				jQuery('#myCarousel').carousel(0); 
			});
			jQuery('#qrLink').click(function() {
				jQuery('#showQRHelp').hide();
				jQuery('#scanQRSection').show();
				jQuery('#myCarousel').carousel('pause');
			});
			function mologinback(){
				jQuery('#mo2f_backto_mo_loginform').submit();
			 }
			 function mologinoffline(){
				jQuery('#mo2f_show_softtoken_loginform').submit();
			 }
			 function mologinforgotphone(){
				jQuery('#mo2f_show_forgotphone_loginform').submit();
			 }
			 </script>
			 
	<?php
	}
}	