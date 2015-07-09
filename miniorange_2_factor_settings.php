<?php
/**
* Plugin Name: miniOrange 2 Factor Authentication
* Plugin URI: http://miniorange.com
* Description: This plugin enables login with mobile authentication as an additional layer of security.
* Version: 1.0.0
* Author: miniOrange
* Author URI: http://miniorange.com
* License: GPL2
*/
include_once dirname( __FILE__ ) . '/miniorange_2_factor_configuration.php';
require('class-customer-setup.php');
require('class-two-factor-setup.php');
require('class-utility.php');
require('class-miniorange-2-factor-login.php');
require('miniorange_2_factor_support.php');

define('MOAUTH_PATH', plugins_url(__FILE__));

class Miniorange_Authentication {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'miniorange_auth_menu' ) );
		add_action( 'admin_init',  array( $this, 'miniorange_auth_save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_style' ) );
		register_deactivation_hook(__FILE__, array( $this, 'mo_auth_deactivate'));
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_script' ) );
		remove_action( 'admin_notices', array( $this, 'mo_auth_success_message') );
		remove_action( 'admin_notices', array( $this, 'mo_auth_error_message') );
		$mobile_login = new Miniorange_Mobile_Login();
        add_action( 'login_form', array( $mobile_login, 'miniorange_login_form_fields' ),100 );
		add_action( 'login_footer', array( $mobile_login, 'miniorange_login_footer_form' ));
		add_action( 'init', array( $mobile_login, 'my_login_redirect') );
		remove_action('login_enqueue_scripts', array( $mobile_login, 'mo_2_factor_hide_login'));
		add_action( 'login_enqueue_scripts', array( $mobile_login,'mo_2_factor_hide_login') );
		add_action( 'login_enqueue_scripts', array( $mobile_login,'custom_login_enqueue_scripts') );
		add_action('wp_logout', array( $this, 'mo_2_factor_endesession'));

	}
	
	function mo_2_factor_endesession() {
		delete_option('mo_2factor_login_status');
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

	public function mo_auth_deactivate() {
		//delete all stored key-value pairs
		delete_option('mo2f_email');
		delete_option('mo2f_host_name');
		delete_option('mo2f_phone');
		delete_option('mo2f_customerKey');
		delete_option('mo2f_api_key');
		delete_option('mo2f_customer_token');
		delete_option('mo2f_message');
		delete_option('mo_2factor_admin_mobile_registration_status');
		delete_option('mo2f_password');
		delete_option('mo_2factor_registration_status');
		delete_option('mo2f_transactionId');
		delete_option('mo_2factor_temp_status');
		delete_option('mo2f_qrCode');
		delete_option('mo_2f_login_type_enabled');
		delete_option('mo2f-login-message');
		delete_option('mo2f_login_username');
		delete_option('mo2f_login_email');
		delete_option('mo2f-login-qrCode');
		delete_option('mo2f-login-transactionId');
		delete_option('mo_2factor_login_status');
		delete_option('mo2f_mowplink');
		global $wp_roles;
		if (!isset($wp_roles))
			$wp_roles = new WP_Roles();
		foreach($wp_roles->role_names as $id => $name) {	
			delete_option('mo2fa_'.$id);	
		}
	}

	function miniorange_auth_menu() {
		$page = add_menu_page ('miniOrange 2 Factor Auth', 'miniOrange 2-Factor', 'manage_options', 'miniOrange_2_factor_settings', array( $this, 'mo_auth_login_options' ),plugin_dir_url(__FILE__) . 'includes/images/miniorange_icon.png');
	}

	function  mo_auth_login_options () {
		global $wpdb;
		update_option('mo2f_host_name', 'https://auth.miniorange.com');
		mo_2_factor_register();
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
	
	public function mo2f_check_empty_or_null( $value ) {
		if( ! isset( $value ) || empty( $value ) ) {
			return true;
		}
		return false;
	}

	function miniorange_auth_save_settings(){
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_register_customer"){	//register the admin to miniOrange
			global $current_user;
			get_currentuserinfo();
			//validate and sanitize
			$email = '';
			$phone = '';
			$password = '';
			$confirmPassword = '';
			if( $this->mo2f_check_empty_or_null( $_POST['email'] ) || $this->mo2f_check_empty_or_null( $_POST['phone'] ) || $this->mo2f_check_empty_or_null( 
			$_POST['password'] ) || $this->mo2f_check_empty_or_null( $_POST['confirmPassword'] ) ) {
				update_option( 'mo2f_message', 'All the fields are required. Please enter valid entries.');
				$this->mo_auth_show_error_message();
				return;
			} else if($_POST['email'] != $current_user->user_email){
				update_option( 'mo2f_message', 'Please do not change the email.');
				$this->mo_auth_show_error_message();
				return;
			}else if( strlen( $_POST['password'] ) < 8 || strlen( $_POST['confirmPassword'] ) < 8){
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
				$customerKey = json_decode($customer->create_customer(), true);
				if(strcasecmp($customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0) {	//admin already exists in miniOrange
					$content = $customer->get_customer_key();
					$customerKey = json_decode($content, true);
					if(json_last_error() == JSON_ERROR_NONE) {
						update_option( 'mo2f_customerKey', $customerKey['id']);
						update_option( 'mo2f_api_key', $customerKey['apiKey']);
						update_option( 'mo2f_customer_token', $customerKey['token']);
						update_option('mo2f_password', '');
						$this->mo2f_get_qr_code_for_mobile(get_option('mo2f_email'));
						update_option( 'mo2f_message', 'Your account has been retrieved successfully.');
						$this->mo_auth_show_success_message();
					} else {
						update_option( 'mo2f_message', 'You already have an account with miniOrange. Please enter a valid password.');
						update_option('mo_2factor_registration_status','MO_2_FACTOR_VERIFY_CUSTOMER');
						$this->mo_auth_show_error_message();
					}
				} else if(strcasecmp($customerKey['status'], 'SUCCESS') == 0) { // send otp after successful registration
					update_option( 'mo2f_customerKey', $customerKey['id']);
					update_option( 'mo2f_api_key', $customerKey['apiKey']);
					update_option( 'mo2f_customer_token', $customerKey['token']);
					update_option('mo2f_password', '');
					update_option('mo_2factor_registration_status','MO_2_FACTOR_CUSTOMER_REGISTERED_SUCCESS');
					$content = json_decode($customer->send_otp_token(get_option('mo2f_email'),'SMS'), true);
					if(strcasecmp($content['status'], 'SUCCESS') == 0) {
						update_option( 'mo2f_message', 'Your account has been created successfully. An SMS has been sent to ' . MO2f_Utility::get_hidden_phone( get_option('mo2f_phone') ) . ' with OTP.');
						update_option('mo2f_transactionId',$content['txId']);
						update_option('mo_2factor_temp_status',$content['phoneDelivery']['sendStatus']);
						update_option('mo_2factor_registration_status','MO_2_FACTOR_OTP_DELIVERED_SUCCESS');
						$this->mo_auth_show_success_message();
					}else{
						update_option('mo2f_message','Your account has been created successfully but there was an error in sending SMS to your phone. Please click on Resend OTP to try again.');
						update_option('mo_2factor_temp_status',$content['phoneDelivery']['sendStatus']);
						update_option('mo_2factor_registration_status','MO_2_FACTOR_OTP_DELIVERED_FAILURE');
						$this->mo_auth_show_error_message();
					}
				} //registration and otp send completed
			} else {
				update_option( 'mo2f_message', 'Password and Confirm password do not match.');
				delete_option('verify_customer');
				$this->mo_auth_show_error_message();
			}
			update_option('mo2f_password', '');
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_verify_customer"){	//register the admin to miniOrange
		
			//validation and sanitization
			$email = '';
			$password = '';
			if( $this->mo2f_check_empty_or_null( $_POST['email'] ) || $this->mo2f_check_empty_or_null( $_POST['password'] ) ) {
				update_option( 'mo2f_message', 'All the fields are required. Please enter valid entries.');
				$this->mo_auth_show_error_message();
				return;
			} else{
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
				update_option('mo2f_password', '');
				$this->mo2f_get_qr_code_for_mobile(get_option('mo2f_email'));
				update_option( 'mo2f_message', 'Your account has been retrieved successfully.');
				$this->mo_auth_show_success_message();
			} else {
				update_option( 'mo2f_message', 'Invalid email or password. Please try again.');
				update_option('mo_2factor_registration_status','MO_2_FACTOR_VERIFY_CUSTOMER');
				$this->mo_auth_show_error_message();
			}
			update_option('mo2f_password', '');
		}
		if(isset($_POST['option']) and trim($_POST['option']) == "mo_2factor_resend_otp"){
			$customer = new Customer_Setup();
			$content = json_decode($customer->send_otp_token(get_option('mo2f_email'),'SMS'), true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
				update_option( 'mo2f_message', 'An SMS has been sent to ' . MO2f_Utility::get_hidden_phone( get_option('mo2f_phone') ) . '. Please enter the OTP to verify your phone number.');
				update_option('mo2f_transactionId',$content['txId']);
				update_option('mo_2factor_temp_status',$content['phoneDelivery']['sendStatus']);
				update_option('mo_2factor_registration_status','MO_2_FACTOR_OTP_DELIVERED_SUCCESS');
				$this->mo_auth_show_success_message();
			}else{
				update_option('mo2f_message','Error in sending SMS to phone. Please click on Resend OTP to try again.');
				update_option('mo_2factor_temp_status',$content['phoneDelivery']['sendStatus']);
				update_option('mo_2factor_registration_status','MO_2_FACTOR_OTP_DELIVERED_FAILURE');
				$this->mo_auth_show_error_message();
			}
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_2factor_validate_otp"){
			
			//validation and sanitization
			$otp_token = '';
			if( $this->mo2f_check_empty_or_null( $_POST['otp_token'] ) ) {
				update_option( 'mo2f_message', 'All the fields are required. Please enter valid entries.');
				$this->mo_auth_show_error_message();
				return;
			} else{
				$otp_token = sanitize_text_field( $_POST['otp_token'] );
			}
			
			$customer = new Customer_Setup();
			$content = json_decode($customer->validate_otp_token( 'SMS', null, get_option('mo2f_transactionId'), $otp_token ),true);
			if(strcasecmp($content['status'], 'SUCCESS') == 0) {
				delete_option('mo_2factor_temp_status');
				$registerMobile = new Two_Factor_Setup();
				$content = $registerMobile->register_mobile(get_option('mo2f_email'));
				$response = json_decode($content, true);
				if(json_last_error() == JSON_ERROR_NONE) {
					update_option( 'mo2f_message','Your OTP is successfully validated.');
					update_option( 'mo2f_qrCode', $response['qrCode']);
					update_option( 'mo2f_transactionId', $response['txId']);
					update_option('mo_2factor_registration_status','MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION');
					$this->mo_auth_show_success_message();
				}
			}else{
				update_option( 'mo2f_message','Invalid OTP. Please try again.');
				update_option('mo_2factor_temp_status','FAILURE');
				update_option('mo_2factor_registration_status','MO_2_FACTOR_OTP_DELIVERED_FAILURE');
				$this->mo_auth_show_error_message();
			}
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_send_app_link"){			
			update_option( 'application_type', $application_type );
			update_option( 'app_link_phone', $app_link_phone );
			update_option( 'mo2f_message','An SMS has been sent to ' . MO2f_Utility::get_hidden_phone( get_option('mo2f_phone') ) . ' with the mobile App link.');
			update_option('mo_2factor_registration_status','MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION');
			$this->mo_auth_show_success_message();
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_mobile_registration_complete"){
			update_option('mo_2factor_admin_mobile_registration_status','MO_2_FACTOR_SUCCESS');
			update_option('mo_2factor_registration_status','MO_2_FACTOR_PLUGIN_SETTINGS');
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_setting_configuration"){
			update_option('mo_2factor_registration_status','MO_2_FACTOR_PLUGIN_SETTINGS');
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_refresh_mobile_qrcode"){
			$this->mo2f_get_qr_code_for_mobile(get_option('mo2f_email'));
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_2factor_enable_user_roles"){
			
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
		if(isset($_POST['option']) and $_POST['option'] == "mo_2factor_enable_login_form"){			
			$loginform = $_POST['mo_2f_enabled'];
			update_option('mo_2f_login_type_enabled',$loginform);
			update_option( 'mo2f_message','Your settings has been saved.');
			$this->mo_auth_show_success_message();
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_auth_logout"){
			do_action('logout');
		}
		if(isset($_POST['option']) and $_POST['option'] == "mo_2factor_send_query"){
			$query = '';
			if( $this->mo2f_check_empty_or_null( $_POST['query_email'] ) || $this->mo2f_check_empty_or_null( $_POST['query'] ) ) {
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
	}
	
	function mo2f_get_qr_code_for_mobile($email){
		$registerMobile = new Two_Factor_Setup();
		$content = $registerMobile->register_mobile($email);
		$response = json_decode($content, true);
		if(json_last_error() == JSON_ERROR_NONE) {
			update_option( 'mo2f_message','Please scan the QR Code now.');
			update_option( 'mo2f_qrCode', $response['qrCode']);
			update_option( 'mo2f_transactionId', $response['txId']);
			update_option('mo_2factor_registration_status','MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION');
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