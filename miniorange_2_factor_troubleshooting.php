<?php 
	function mo2f_show_help_and_troubleshooting($current_user) {
	?>
	<div class="mo2f_table_layout">
		<?php echo mo2f_check_if_registered_with_miniorange($current_user); ?>
		<br>
		<ul class="mo2f_faqs">
			<?php if(current_user_can( 'manage_options' )) { ?>
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question14" aria-expanded="false" ><li>How to enable PHP cURL extension? (Pre-requisite)</li></a></h3>
				<div class="collapse" id="question14">
					<ol>
					<li>Open php.ini.</li>
					<li>Search for extension=php_curl.dll. Uncomment it by removing the semi-colon( ; ) in front of it.</li>
					<li>Restart the Apache Server.</li>
					</ol>
					For any further queries, please submit a query on right hand side in our <b>Support Section</b>.
				</div>
				<hr>
				<h3><a class="btn btn-link" data-toggle="collapse" href="#question1" aria-expanded="false" ><li>I already have a login page and I want the look and feel to remain the same when I add 2 factor ?</li></a></h3>
				<div class="collapse" id="question1">
					2-Factor login widget will adopt your login theme automatically. If you have a custom login form other than wp-login.php then we can give you our widget code that you can embed in your login form. If you need any help setting up 2-Factor for your custom login form, please submit a query in our <b>Support Section</b> on right hand side.
				</div>
				<hr>
				<h3><a class="btn btn-link" data-toggle="collapse" href="#question2" aria-expanded="false" ><li>I want to enable 2-factor only for administrators ?</li></a></h3>
				<div class="collapse" id="question2">
					2-Factor is enabled by default for administrators on plugin activation. You just need to complete your account setup and configure your mobile from <b>Configure Mobile Tab</b>. Once this is done administrators can login using 2-Factor and other users can still login with their password.
				</div>
				<hr>
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question3" aria-expanded="false" ><li>I want to enable 2 factor for administrators and end users ?</li></a></h3>
				<div class="collapse" id="question3">
					Go to <b>Login Settings Tab</b> and check <b>Enable 2-Factor for all other users</b>. Enable 2-Factor for admins is checked by default.
				</div>
				<hr>
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question4" aria-expanded="false" ><li>I have enabled 2-factor for all users, what happens if an end user tries to login but has not yet registered ?</li></a></h3>
				<div class="collapse" id="question4">
					If a user has not setup 2-Factor yet, he can still login using his password. After logging in, user can go to <b>miniOrage 2-Factor</b> Tab in left side menu and configure his 2-Factor.
				</div>
				<hr>
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question5" aria-expanded="false" ><li>My users have different types of phones. What phones are supported?</li></a></h3>
				<div class="collapse" id="question5">
					Currently we support smart phones only. If you need 2-Factor for basic phone submit a query in our <b>Support Section</b> on right hand side.
				</div>
				<hr>
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question6" aria-expanded="false" ><li>What if a user does not have a smart phone?</li></a></h3>
				<div class="collapse" id="question6">
					Currently we support smart phone users only. If you need 2-Factor for basic phone users submit a query in our <b>Support Section</b> on right hand side.
				</div>
				<hr>
			<?php }?>	
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question15" aria-expanded="false" ><li>What if I am trying to login from my phone ?</li></a></h3>
				<div class="collapse" id="question15">
					If you are logging in from your phone, just enter the one time passcode from miniOrange Authenticator App.
					Go to Soft Token Tab to see one time passcode.
				</div>
				<hr>
			<?php if(current_user_can( 'manage_options' )) { ?>
				
			
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question10" aria-expanded="false" ><li>I want to hide default login form and just want to show login with phone?</li></a></h3>
				<div class="collapse" id="question10">
					You should go to <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_login">Login Settings Tab</a> and check <b>Login with Phone Only</b> checkbox to hide the default login form. 
					
					
				</div>
				<hr>
			<?php }?>
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question7" aria-expanded="false" ><li>My phone has no internet connectivity, how can I login?</li></a></h3>
				<div class="collapse" id="question7">
				   You can login using our alternate login method. Please follow below steps to login or <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_demo#myCarousel2">click here</a> to see how it works.<br>
					<br><ol>
					 <li>Enter your username and click on login with your phone.</li>
					  <li>Click on <b>Phone is Offline?</b> button below QR Code.</li>
					   <li>You will see a textbox to enter one time passcode.</li>
					   <li>Open miniOrange Authenticator app and Go to Soft Token Tab.</li>
					   <li>Enter the one time passcode shown in miniOrange Authenticator app in textbox.</li>
					   <li>Click on submit button to validate the otp.</li>
					   <li>Once you are authenticated, you will be logged in.</li>
					  </ol>
				</div>
				<hr>
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question8" aria-expanded="false" ><li>My phone is lost, stolen or discharged. How can I login?</li></a></h3>
				<div class="collapse" id="question8">
				    You can login using our alternate login method. Please follow below steps to login or <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_demo#myCarousel3">click here</a> to see how it works.
					<br><br>
					<ol>
					<li>Enter your username and click on login with your phone.</li>
					  <li>Click on <b>Forgot Phone?</b> button below QR Code.</li>
					   <li>You will see a textbox to enter one time passcode.</li>
					   <li>Check your registered email and copy the one time passcode in this textbox.</li>
					   <li>Click on submit button to validate the otp.</li>
					   <li>Once you are authenticated, you will be logged in.</li>
					   </ol>
				</div>
				<hr>
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question9" aria-expanded="false" ><li>My phone has no internet connectivity and i am entering the one time passcode from miniOrange Authenticator App, it says Invalid OTP.</li></a></h3>
				<div class="collapse" id="question9">
					Click on the <b>Settings Icon</b> on top right corner in <b>miniOrange Authenticator App</b> and then press <b>Sync button</b> under 'Time correction for codes' to sync your time with miniOrange Servers. If you still can't get it right, submit a query here in our <b>support section</b>.<br><br>
				</div>
				<hr>
				<?php if(current_user_can( 'manage_options' )) { ?>
			
		
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question12" aria-expanded="false" ><li>I want to go back to default login with password.</li></a></h3>
				<div class="collapse" id="question12">
					You should go to <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mo2f_login">Login Settings Tab</a> and uncheck <b>Enable 2-Factor for admins</b> 
					and <b>Enable 2-Factor for all others users</b> checkbox. This will disable 2-Factor and you can login using default login form.
				</div>
				<hr>
			<?php }?>
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question11" aria-expanded="false" ><li>I am upgrading my phone.</li></a></h3>
				<div class="collapse" id="question11">
					You should go to <a href="admin.php?page=miniOrange_2_factor_settings&amp;mo2f_tab=mobile_configure">Configure Mobile Tab</a> and reconfigure 2-Factor with your new phone.
				</div>
				<?php if(current_user_can( 'manage_options' )) { ?>
			<hr>
		
			<h3><a class="btn btn-link" data-toggle="collapse" href="#question13" aria-expanded="false" ><li>What If I want to use any other second factor like OTP Over SMS, Security Questions, Device Id, etc ?</li></a></h3>
				<div class="collapse" id="question13">
					miniOrange authentication service has 15+ authentication methods.One time passcodes (OTP) over SMS, OTP over Email, OTP over SMS and Email, Out of Band SMS, Out of Band Email, Soft Token, Push Notification, 
					USB based Hardware token (yubico), Security Questions, Mobile Authentication, Voice Authentication (Biometrics), Phone Verification, Device Identification, Location, Time of Access User Behavior.
					To know more about authentication methods, please visit <a href="http://miniorange.com/strong_auth" target="_blank">http://miniorange.com/strong_auth </a>. If you want to have any other 2-factor for your 
					WordPress site, Submit your query here in <b>support section</b>.
				</div>
			
				<hr>
		
			<h3><a>For any other query/problem/request, please feel free to submit a query in our support section on right hand side. We are happy to help you and will get back to you as soon as possible.</a></h3>
			<?php }?>
		</ul>
					
	</div>
	<?php } ?>
