<?php
/**
* Plugin Name: miniOrange 2 Factor Authentication
* Plugin URI: http://miniorange.com
* Description: This plugin enables login with mobile authentication as an additional layer of security.
* Version: 1.7
* Author: miniOrange
* Author URI: http://miniorange.com
* License: GPL2
*/
include_once dirname( __FILE__ ) . '/miniorange_2_factor_configuration.php';
include_once dirname( __FILE__ ) . '/miniorange_2_factor_mobile_configuration.php';
require('class-customer-setup.php');
require('class-two-factor-setup.php');
require('class-utility.php');
require('class-miniorange-2-factor-login.php');
require('miniorange_2_factor_support.php');
require('class-miniorange-2-factor-user-registration.php');

define('MOAUTH_PATH', plugins_url(__FILE__));

class Miniorange_Authentication {
	
	private $defaultCustomerKey = "16555";
	private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

	function __construct() {
		add_action( 'admin_menu', array( $this, 'miniorange_auth_menu' ) );
		add_action( 'admin_init',  array( $this, 'miniorange_auth_save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_script' ) );
		remove_action( 'admin_notices', array( $this, 'mo_auth_success_message') );
		remove_action( 'admin_notices', array( $this, 'mo_auth_error_message') );
		add_action('wp_logout', array( $this, 'mo_2_factor_endsession'));
		if(get_option( 'mo_2factor_admin_registration_status') == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' 
				&& get_user_meta(get_option( 'mo2f_miniorange_admin'),'mo_2factor_mobile_registration_status',true) == 'MO_2_FACTOR_SUCCESS' 
				&& (!get_option('mo2f_admin_disabled_status') || get_option('mo2f_disabled_status'))){

			$mobile_login = new Miniorange_Mobile_Login();
			add_action( 'login_form', array( $mobile_login, 'miniorange_login_form_fields' ),10 );
			add_action( 'login_footer', array( $mobile_login, 'miniorange_login_footer_form' ));
			add_action( 'init', array( $mobile_login, 'my_login_redirect') );
			remove_action('login_enqueue_scripts', array( $mobile_login, 'mo_2_factor_hide_login'));
			add_action( 'login_enqueue_scripts', array( $mobile_login,'mo_2_factor_hide_login') );
			add_action( 'login_enqueue_scripts', array( $mobile_login,'custom_login_enqueue_scripts') );
			add_filter('wp_authenticate_user', array($mobile_login, 'mo2fa_default_login'), 10, 3);
			add_filter('login_redirect', array($this,'mo2f_login_redirectto'), 10, 3);
			
		}
	}
	
	function mo2f_login_redirectto($redirect_to, $request, $user)
	{
		global $wpdb;
		global $current_user;
		get_currentuserinfo();
		if( !current_user_can( 'manage_options' ) && get_option('mo2f_disabled_status') && get_option( 'mo_2factor_admin_registration_status') == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' && get_option( 'mo2f_miniorange_admin') != $current_user->ID && get_user_meta($current_user->ID,'mo_2factor_user_registration_with_miniorange',true) != 'SUCCESS'){
			return admin_url().'/admin.php?page=miniOrange_2_factor_settings';
		}else if(current_user_can( 'manage_options' ) && get_option( 'mo_2factor_admin_registration_status') == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' && get_option( 'mo2f_miniorange_admin') != $current_user->ID && get_user_meta($current_user->ID,'mo_2factor_user_registration_with_miniorange',true) != 'SUCCESS'){
			return admin_url().'/admin.php?page=miniOrange_2_factor_settings';
		}else{
			return admin_url();
		}
	} 
	
	function mo_2_factor_endsession() {
		update_option('mo2f-login-message','You are now logged out');
		session_destroy();
	}
	

	function mo_auth_success_message() {
		$class = "error";
		$message = get_option('mo2f_message');
		echo "<div class='" . $class . "'> <p>" . $message . "</p></div>";
	}

	function mo_auth_error_message() {
		$class = "updated";
		$message = get_option('mo2f_message');
		echo "<div class='" . $class . "'> <p>" . $message . "</p></div>";
	}

	function miniorange_auth_menu() {
		global $wpdb;
		global $current_user;
		get_currentuserinfo();
		if( !current_user_can( 'manage_options' ) && get_option('mo2f_disabled_status') && get_option( 'mo_2factor_admin_registration_status') == 'MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS' && get_option( 'mo2f_miniorange_admin') != $current_user->ID){
			$user_register = new Miniorange_User_Register();
			$page = add_menu_page ('miniOrange 2 Factor Auth', 'miniOrange 2-Factor', 'read', 'miniOrange_2_factor_settings', array( $user_register, 'mo2f_register_user'),plugin_dir_url(__FILE__) . 'includes/images/miniorange_icon.png');
		}else if(current_user_can( 'manage_options' )){
			$page = add_menu_page ('miniOrange 2 Factor Auth', 'miniOrange 2-Factor', 'manage_options', 'miniOrange_2_factor_settings', array( $this, 'mo_auth_login_options' ),plugin_dir_url(__FILE__) . 'includes/images/miniorange_icon.png');
		}
	}

	function  mo_auth_login_options () {
		global $wpdb;
		global $current_user;
		get_currentuserinfo();
		update_option('mo2f_host_name', 'https://auth.miniorange.com');
		mo_2_factor_register($current_user);
	}

	function plugin_settings_style() {
		wp_enqueue_style( 'mo_2_factor_admin_settings_style', plugins_url('includes/css/style_settings.css', __FILE__));
		wp_enqueue_style( 'mo_2_factor_admin_settings_phone_style', plugins_url('includes/css/phone.css', __FILE__));
	}

	function plugin_settings_script() {
		wp_enqueue_script( 'mo_2_factor_admin_settings_phone_script', plugins_url('includes/js/phone.js', __FILE__ ));
	}

	private function mo_auth_show_success_message() {
		remove_action( 'admin_notices', array( $this, 'mo_auth_success_message') );
		add_action( 'admin_notices', array( $this, 'mo_auth_error_message') );
	}

	private function mo_auth_show_error_message() {
		remove_action( 'admin_notices', array( $this, 'mo_auth_error_message') );
		add_action( 'admin_notices', array( $this, 'mo_auth_success_message') );
	}

	function miniorange_auth_save_settings(){
		if( ! session_id() ) {
			session_start();
		}
		global $current_user;
		get_currentuserinfo();
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_register_customer"){	//register the admin to miniOrange
			//validate and sanitize
			$email = '';
			$phone = '';
			$password = '';
			$confirmPassword = '';
			if( MO2f_Utility::mo2f_check_empty_or_null( $_POST['email'] ) || MO2f_Utility::mo2f_check_empty_or_null( $_POST['phone'] ) || MO2f_Utility::mo2f_check_empty_or_null( $_POST['password'] ) || MO2f_Utility::mo2f_check_empty_or_null( $_POST['confirmPassword'] ) ) {
				update_option( 'mo2f_message', 'All the fields are required. Please enter valid entries.');
				$this->mo_auth_show_error_message();
				return;
			}else if( strlen( $_POST['password'] ) < 6 || strlen( $_POST['confirmPassword'] ) < 6){
				update_option( 'mo2f_message', 'Choose a password with minimum length 8.');
				$this->mo_auth_show_error_message();
				return;
			} else{
				$email = sanitize_email( $_POST['email'] );
				$phone = sanitize_text_field( $_POST['phone'] );
				$password = sanitize_text_field( $_POST['password'] );
				$confirmPassword = sanitize_text_field( $_POST['confirmPassword'] );
			}			
			
			update_option( 'mo2f_email', $email );
			update_option( 'mo2f_phone', $phone );
			
			if(strcmp($password, $confirmPassword) == 0) {
				update_option( 'mo2f_password', $password );
				$customer = new Customer_Setup();
				$customerKey = json_decode($customer->check_customer(), true);
				if( strcasecmp( $customerKey['status'], 'CUSTOMER_NOT_FOUND') == 0 ){
					$content = json_decode($customer->send_otp_token(get_option('mo2f_email'),'EMAIL',$this->defaultCustomerKey,$this->defaultApiKey), true);
					if(strcasecmp($content['status'], 'SUCCESS') == 0) {
						update_option( 'mo2f_message', 'An OTP has been sent to ' . ( get_option('mo2f_email') ) . '. Please enter the OTP below to verify your email. ');
						$_SESSION[ 'mo2f_transactionId' ] = $content['txId'];
						update_user_meta($current_user->ID, 'mo_2factor_user_registration_status','MO_2_FACTOR_OTP_DELIVERED_SUCCESS');
						$this->mo_auth_show_success_message();
					}else{
						update_option('mo2f_message','There was an error in sending OTP over email. Please click on Resend OTP to try again.');
						update_user_meta($current_user->ID, 'mo_2factor_user_registration_status','MO_2_FACTOR_OTP_DELIVERED_FAILURE');
						$this->mo_auth_show_error_message();
					}
				}else{
					$content = $customer->get_customer_key();
					$customerKey = json_decode($content, true);
					if(json_last_error() == JSON_ERROR_NONE) { /*Admin enter right credentials,if already exist */
						update_option( 'mo2f_customerKey', $customerKey['id']);
						update_option( 'mo2f_api_key', $customerKey['apiKey']);
						update_option( 'mo2f_customer_token', $customerKey['token']);
						update_option( 'mo2f_miniorange_admin',$current_user->ID);
						delete_option('mo2f_password');
						update_option( 'mo_2factor_admin_registration_status','MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS');
						update_user_meta($current_user->ID,'mo_2factor_map_id_with_email',get_option('mo2f_email'));
						$this->mo2f_get_qr_code_for_mobile(get_option('mo2f_email'),$current_user->ID);
						update_option( 'mo2f_message', 'Your account has been retrieved successfully.');
						$this->mo_auth_show_success_message();
					} else { /*Admin account exist but enter wrong credentials*/
						update_option( 'mo2f_message', 'You already have an account with miniOrange. Please enter a valid password.');
						update_user_meta( $current_user->ID,'mo_2factor_user_registration_status','MO_2_FACTOR_VERIFY_CUSTOMER');
						$this->mo_auth_show_error_message();
					}
				} 
			} else {
				update_option( 'mo2f_message', 'Password and Confirm password do not match.');
				$this->mo_auth_show_error_message();
			}
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_verify_customer"){	//register the admin to miniOrange if already exist
		
			//validation and sanitization
			$email = '';
			$password = '';
			if( MO2f_Utility::mo2f_check_empty_or_null( $_POST['email'] ) || MO2f_Utility::mo2f_check_empty_or_null( $_POST['password'] ) ) {
				update_option( 'mo2f_message', 'All the fields are required. Please enter valid entries.');
				$this->mo_auth_show_error_message();
				return;
			}else{
				$email = sanitize_email( $_POST['email'] );
				$password = sanitize_text_field( $_POST['password'] );
			}
		
			update_option( 'mo2f_email', $email );
			update_option( 'mo2f_password', $password );
			$customer = new Customer_Setup();
			$content = $customer->get_customer_key();
			$customerKey = json_decode($content, true);
			if(json_last_error() == JSON_ERROR_NONE) {
				update_option( 'mo2f_customerKey', $customerKey['id']);
				update_option( 'mo2f_api_key', $customerKey['apiKey']);
				update_option( 'mo2f_customer_token', $customerKey['token']);
				update_option('mo2f_phone', $customerKey['phone']);
				update_option( 'mo2f_miniorange_admin',$current_user->ID);
				delete_option('mo2f_password');
				update_option( 'mo_2factor_admin_registration_status','MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS');
				update_user_meta($current_user->ID,'mo_2factor_map_id_with_email',get_option('mo2f_email'));
				$this->mo2f_get_qr_code_for_mobile(get_option('mo2f_email'),$current_user->ID);
				update_option( 'mo2f_message', 'Your account has been retrieved successfully.');
				$this->mo_auth_show_success_message();
			} else {
				update_option( 'mo2f_message', 'Invalid email or password. Please try again.');
				update_user_meta($current_user->ID, 'mo_2factor_user_registration_status','MO_2_FACTOR_VERIFY_CUSTOMER');
				$this->mo_auth_show_error_message();
			}
			delete_option('mo2f_password');
		}
		if(isset($_POST['option']) and trim($_POST['option']) == "mo_2factor_resend_otp"){ //resend OTP
			$customer = new Customer_Setup();
			$content = json_decode($customer->send_otp_token(get_option('mo2f_email'),'EMAIL',$this->defaultCustomerKey,$this->defaultApiKey), true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
				update_option( 'mo2f_message', 'An OTP has been sent to ' . ( get_option('mo2f_email') ) . '. Please enter the OTP below to verify your email. ');
				$_SESSION[ 'mo2f_transactionId' ] = $content['txId'];
				update_user_meta($current_user->ID, 'mo_2factor_user_registration_status','MO_2_FACTOR_OTP_DELIVERED_SUCCESS');
				$this->mo_auth_show_success_message();
			}else{
				update_option('mo2f_message','There was an error in sending email. Please click on Resend OTP to try again.');
				update_user_meta($current_user->ID,'mo_2factor_user_registration_status','MO_2_FACTOR_OTP_DELIVERED_FAILURE');
				$this->mo_auth_show_error_message();
			}
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_2factor_validate_otp"){ //validate OTP
			
			//validation and sanitization
			$otp_token = '';
			if( MO2f_Utility::mo2f_check_empty_or_null( $_POST['otp_token'] ) ) {
				update_option( 'mo2f_message', 'All the fields are required. Please enter valid entries.');
				$this->mo_auth_show_error_message();
				return;
			} else{
				$otp_token = sanitize_text_field( $_POST['otp_token'] );
			}
			
			$customer = new Customer_Setup();
			$content = json_decode($customer->validate_otp_token( 'EMAIL', null, $_SESSION[ 'mo2f_transactionId' ], $otp_token ),true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) { //OTP validated and generate QRCode
				$this->mo2f_create_customer($current_user);
			}else{  // OTP Validation failed.
				update_option( 'mo2f_message','Invalid OTP. Please try again.');
				update_user_meta($current_user->ID,'mo_2factor_user_registration_status','MO_2_FACTOR_OTP_DELIVERED_FAILURE');
				$this->mo_auth_show_error_message();
			}
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_send_app_link"){ //send app link			
			update_option( 'mo2f_message','An SMS has been sent to ' . MO2f_Utility::get_hidden_phone( get_option('mo2f_phone') ) . ' with the mobile App link.');
			update_user_meta($current_user->ID, 'mo_2factor_user_registration_status','MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION');
			$this->mo_auth_show_success_message();
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_mobile_registration_complete"){ //mobile registration
			update_user_meta($current_user->ID,'mo_2factor_mobile_registration_status','MO_2_FACTOR_SUCCESS');
			update_user_meta($current_user->ID,'mo_2factor_user_registration_status','MO_2_FACTOR_PLUGIN_SETTINGS');
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_setting_configuration"){ // redirect to setings page
			update_user_meta($current_user->ID,'mo_2factor_user_registration_status','MO_2_FACTOR_PLUGIN_SETTINGS');
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_refresh_mobile_qrcode"){ // refrsh Qrcode
			$email = get_user_meta($current_user->ID,'mo_2factor_map_id_with_email',true);
			$this->mo2f_get_qr_code_for_mobile($email,$current_user->ID);
		}
		
		if (isset($_POST['miniorange_get_started']) && isset($_POST['miniorange_user_reg_nonce'])){			
			$nonce = $_POST['miniorange_user_reg_nonce'];
			if ( ! wp_verify_nonce( $nonce, 'miniorange-2-factor-user-reg-nonce' ) ) {
				update_option('mo2f_message','Invalid request');
			} else {
				$email = '';
				if( MO2f_Utility::mo2f_check_empty_or_null( $_POST['mo_useremail'] )){
					update_option( 'mo2f_message', 'Please enter email-id to register.');
					return;
				}else{
					$email = sanitize_email( $_POST['mo_useremail'] );
				}
				
				$enduser = new Two_Factor_Setup();
				$check_user = json_decode($enduser->mo_check_user_already_exist($email),true);
				if(json_last_error() == JSON_ERROR_NONE){
					if(strcasecmp($check_user['status'], 'USER_FOUND') == 0){
						update_user_meta($current_user->ID,'mo_2factor_user_registration_with_miniorange','SUCCESS');
						update_user_meta($current_user->ID,'mo_2factor_map_id_with_email',$email);
						$this->mo2f_get_qr_code_for_mobile($email,$current_user->ID);
					}else if(strcasecmp($check_user['status'], 'USER_NOT_FOUND') == 0){
						$content = json_decode($enduser->mo_create_user($current_user,$email), true);
						if(json_last_error() == JSON_ERROR_NONE) {
							if(strcasecmp($content['status'], 'SUCCESS') == 0) {
								update_user_meta($current_user->ID,'mo_2factor_user_registration_with_miniorange','SUCCESS');
								update_user_meta($current_user->ID,'mo_2factor_map_id_with_email',$email);
								$this->mo2f_get_qr_code_for_mobile($email,$current_user->ID);
							}else{
								update_option( 'mo2f_message','Error occurred while adding the user. Please try again or contact your admin.');
							}
						}else{
							update_option( 'mo2f_message','Error occurred while adding the user. Please try again or contact your admin.');
						}
					}else{
						update_option( 'mo2f_message','Error occurred while adding the user. Please try again or contact your admin.');
					}
				}else{
					update_option( 'mo2f_message','Please try again.');
				}
			}
		}
		
		if(isset($_POST['option']) and $_POST['option'] == "mo_2factor_enable_user_roles"){ //user roles
			
			global $wp_roles;
			if (!isset($wp_roles))
			$wp_roles = new WP_Roles();
			foreach($wp_roles->role_names as $id => $name) {
			
				$setting = $role;
				if($setting){
					update_option('mo2fa_'.$id,$setting);
				}else{
					update_option('mo2fa_'.$id,0);
				}
			}
			update_option( 'mo2f_message','Your settings has been saved.');
			$this->mo_auth_show_success_message();
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_2factor_enable_login_form"){	//type of login to be enabled		
			$loginform = $_POST['mo_2f_enabled'];
			update_option('mo_2f_login_type_enabled',$loginform);
			update_option( 'mo2f_message','Your settings has been saved.');
			$this->mo_auth_show_success_message();
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_logout"){ //logout
			do_action('logout');
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_2factor_send_query"){ //Help me or support
			$query = '';
			if( MO2f_Utility::mo2f_check_empty_or_null( $_POST['query_email'] ) || MO2f_Utility::mo2f_check_empty_or_null( $_POST['query'] ) ) {
				update_option( 'mo2f_message', 'Please submit your query with email.');
				$this->mo_auth_show_error_message();
				return;
			} else{
				$query = sanitize_text_field( $_POST['query'] );
				$email = sanitize_text_field( $_POST['query_email'] );
				$phone = sanitize_text_field( $_POST['query_phone'] );
				$contact_us = new Customer_Setup();
				$submited = json_decode($contact_us->submit_contact_us($email, $phone, $query),true);
				if(json_last_error() == JSON_ERROR_NONE) {
					if ( $submited == false ) {
						update_option('mo2f_message', 'Your query could not be submitted. Please try again.');
						$this->mo_auth_show_error_message();
					} else {
						update_option('mo2f_message', 'Thanks for getting in touch! We shall get back to you shortly.');
						$this->mo_auth_show_success_message();
					}
				}

			}
		}
		if(isset($_POST['option']) and $_POST['option'] == 'mo_auth_user_activation'){
			get_option('mo2f_disabled_status') ? update_option('mo2f_disabled_status',0) : update_option('mo2f_disabled_status',1);
			update_option( 'mo2f_message', 'Your settings have been saved.');
			$this->mo_auth_show_success_message();
		}
		if(isset($_POST['option']) and $_POST['option'] == 'mo_auth_admin_activation'){
			get_option('mo2f_admin_disabled_status') ? update_option('mo2f_admin_disabled_status',0) : update_option('mo2f_admin_disabled_status',1);
			update_option( 'mo2f_message', 'Your settings have been saved.');
			$this->mo_auth_show_success_message();
		}
		if(isset($_POST['option']) and $_POST['option'] == 'mo2f_forgotphone_activation'){
			get_option('mo2f_enable_forgotphone') ? update_option('mo2f_enable_forgotphone',0) : update_option('mo2f_enable_forgotphone',1);
			update_option( 'mo2f_message', 'Your settings have been saved.');
			$this->mo_auth_show_success_message();
		}
		
		if(isset($_POST['option']) and $_POST['option'] == 'mo_2factor_gobackto_registration_page'){
			delete_option('mo2f_email');
			delete_option('mo2f_phone');
			delete_option('mo2f_password');
			delete_option('mo2f_customerKey');
			unset($_SESSION[ 'mo2f_transactionId' ]);
			delete_user_meta($current_user->ID,'mo_2factor_user_registration_status');
		}
	}
	
	function mo2f_create_customer($current_user){
		$customer = new Customer_Setup();
		$customerKey = json_decode($customer->create_customer(), true);
		if(strcasecmp($customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0) {	//admin already exists in miniOrange
			$content = $customer->get_customer_key();
			$customerKey = json_decode($content, true);
			if(json_last_error() == JSON_ERROR_NONE) {
				update_option( 'mo2f_customerKey', $customerKey['id']);
				update_option( 'mo2f_api_key', $customerKey['apiKey']);
				update_option( 'mo2f_customer_token', $customerKey['token']);
				update_option( 'mo2f_miniorange_admin',$current_user->ID);
				delete_option('mo2f_password');
				update_option( 'mo_2factor_admin_registration_status','MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS');
				update_user_meta($current_user->ID,'mo_2factor_map_id_with_email',get_option('mo2f_email'));
				$this->mo2f_get_qr_code_for_mobile(get_option('mo2f_email'),$current_user->ID);
				update_option( 'mo2f_message', 'Your account has been retrieved successfully.');
				$this->mo_auth_show_success_message();
			} else {
				update_option( 'mo2f_message', 'Invalid email or password. Please try again.');
				update_user_meta($current_user->ID, 'mo_2factor_user_registration_status','MO_2_FACTOR_VERIFY_CUSTOMER');
				$this->mo_auth_show_error_message();
			}
		}else{
			update_option( 'mo2f_customerKey', $customerKey['id']);
			update_option( 'mo2f_api_key', $customerKey['apiKey']);
			update_option( 'mo2f_customer_token', $customerKey['token']);
			update_option( 'mo2f_miniorange_admin',$current_user->ID);
			delete_option('mo2f_password');
			update_option( 'mo_2factor_admin_registration_status','MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS');
			update_user_meta($current_user->ID,'mo_2factor_map_id_with_email',get_option('mo2f_email'));
			$this->mo2f_get_qr_code_for_mobile(get_option('mo2f_email'),$current_user->ID);
			update_option( 'mo2f_message', 'Your OTP Verified successfully. Please scan the QR-Code below to register your mobile.');
			$this->mo_auth_show_success_message();
		}
	}

	function mo2f_get_qr_code_for_mobile($email,$id){
		$registerMobile = new Two_Factor_Setup();
		$content = $registerMobile->register_mobile($email);
		$response = json_decode($content, true);
		if(json_last_error() == JSON_ERROR_NONE) {
			update_option( 'mo2f_message','Please scan the QR Code now.');
			$_SESSION[ 'mo2f_qrCode' ] = $response['qrCode'];
			$_SESSION[ 'mo2f_transactionId' ] = $response['txId'];
			update_user_meta($id,'mo_2factor_user_registration_status','MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION');
			$this->mo_auth_show_success_message();
		}
	}
}

	function mo2f_is_customer_registered() {
		$email = get_option('mo2f_email');
		$phone = get_option('mo2f_phone');
		$customerKey = get_option('mo2f_customerKey');
		if(!$email || !$phone || !$customerKey || !is_numeric(trim($customerKey))) {
			return 0;
		} else {
			return 1;
		}
	}

new Miniorange_Authentication;
?>