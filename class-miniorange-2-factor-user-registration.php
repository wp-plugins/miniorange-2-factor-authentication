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
class Miniorange_User_Register{

	function __construct(){
		add_action( 'admin_init',  array( $this, 'miniorange_user_save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'plugin_settings_style' ) );
	}
	
	function plugin_settings_style() {
		wp_enqueue_style( 'mo_2_factor_admin_settings_style', plugins_url('includes/css/style_settings.css', __FILE__));
	}

	public function mo2f_register_user(){
		global $wpdb;
		global $current_user;
		get_currentuserinfo();
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
		
		if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION') {
			if(isset($_SESSION[ 'mo2f_qrCode' ] )){
				initialize_mobile_registration($current_user);
			}else{
				$this->mo2f_get_qr_code_for_mobile(get_user_meta($current_user->ID,'mo_2factor_map_id_with_email',true),$current_user->ID);
				initialize_mobile_registration($current_user);
			}	
		}else if(get_user_meta($current_user->ID,'mo_2factor_user_registration_status',true) == 'MO_2_FACTOR_PLUGIN_SETTINGS'){
			mo2f_show_instruction_to_allusers();
		}else{
			$this->show_user_welcome_page($current_user);
		}
		?>
					</td>
					<td style="vertical-align:top;padding-left:1%;">
					</td>
				</tr>
			</table>
		</div>
		<?php
	}
	
	function show_user_welcome_page($current_user){
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
	
	function miniorange_user_save_settings() {
		global $wpdb;
		global $current_user;
		get_currentuserinfo();
		
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
	}
	
	function mo2f_get_qr_code_for_mobile($email,$id){
		$enduser = new Two_Factor_Setup();
		$response = json_decode($enduser->register_mobile($email), true);
		if(json_last_error() == JSON_ERROR_NONE) {
			update_option( 'mo2f_message','Please scan the QR Code now.');
			$_SESSION[ 'mo2f_qrCode' ] = $response['qrCode'];
			$_SESSION[ 'mo2f_transactionId' ] = $response['txId'];
			update_user_meta($id,'mo_2factor_user_registration_status','MO_2_FACTOR_INITIALIZE_MOBILE_REGISTRATION');
		}
	}
}