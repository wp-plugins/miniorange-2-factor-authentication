<?php
	delete_option('mo2f_host_name');
	delete_option('mo2f_email');
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
?>