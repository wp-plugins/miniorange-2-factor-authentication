<?php
	//delete all stored key-value pairs which are available to all users
		delete_option('mo2f_email');
		delete_option('mo2f_host_name');
		delete_option('mo2f_phone');
		delete_option('mo2f_customerKey');
		delete_option('mo2f_api_key');
		delete_option('mo2f_customer_token');
		delete_option('mo2f_message');
		delete_option('mo_2factor_admin_registration_status');
		delete_option('mo2f-login-message');
		delete_option('mo_2f_login_type_enabled');
		delete_option('mo2f_admin_disabled_status');
		delete_option('mo2f_disabled_status');
		delete_option('mo2f_miniorange_admin');
		delete_option('mo2f_enable_forgotphone');
		delete_option('mo2f_show_loginwith_phone');
		
		//delete all stored key-value pairs for the roles
		global $wp_roles;
		if (!isset($wp_roles))
			$wp_roles = new WP_Roles();
		foreach($wp_roles->role_names as $id => $name) {	
			delete_option('mo2fa_'.$id);	
		}
		
		//delete user specific key-value pair
		$users = get_users( array() );
		foreach ( $users as $user ) {
			delete_user_meta($user->ID,'mo_2factor_user_registration_status');
			delete_user_meta($user->ID,'mo_2factor_mobile_registration_status');
			delete_user_meta($user->ID,'mo_2factor_user_registration_with_miniorange');
			delete_user_meta($user->ID,'mo_2factor_map_id_with_email');
		}
		
		//delete previous version key-value pairs
		delete_option('mo_2factor_admin_mobile_registration_status');
		delete_option('mo_2factor_registration_status');
		delete_option('mo_2factor_temp_status');
		delete_option('mo2f_login_username');
		delete_option('mo2f-login-qrCode');
		delete_option('mo2f-login-transactionId');
		delete_option('mo_2factor_login_status');
		delete_option('mo2f_mowplink');
?>