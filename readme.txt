=== 2 Factor authentication plugin for wordpress ===
Contributors: miniOrange
Tags: 2 factor authentication, 2 step verification, 2FA, single sign on, multi factor authentication, Google authenticator, login, authy, Clef, 2 Factor, yubico, Two Factor, Authentication, Mobile Authentication, otp, tfa, strong authentication, 2 step authentication, mobile, smartphone authentication, Multifactor authentication, no password, passwordless login, website security, android, iphone, one time passcode, soft token, miniorange, woocommerce
Requires at least: 3.0.1
Tested up to: 4.2.4
Stable tag: 1.0.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

2 Factor authentication for secure login.

== Description ==

A highly secure two-factor authentication for all users of your site. 

* It can either work in addition to the password or replaces it with mobile app based authentication
* It works for administrators and other users
* It can be deployed for your entire userbase in minutes
* Both iPhone and Android phones are supported.
* If your phone is lost or stolen or discharged, it offers an alternate login method
* If your phone is offline, you can use a soft token to login
* It offers inline registration of users so you can simply activate and configure the plugin and you are all set. 

miniOrange supports 15+ authentication methods. For a complete list of authentication methods please visit http://miniorange.com/strong_auth . If you want to have any other 2-factor for your WordPress site, <a href="http://miniorange.com/contact" target="_blank">Contact us</a>.

* One time passcodes (OTP) over SMS
* OTP over Email
* OTP over SMS and Email
* Out of Band SMS
* Out of Band Email
* Soft Token
* Push Notification
* USB based Hardware token (<b>yubico</b>)
* Security Questions
* Mobile Authentication
* Voice Authentication (Biometrics)
* Phone Verification
* Device Identification
* Location
* Time of Access
* User Behavior

You can choose from any of the above authentication methods to augment your password based authentication. miniOrange authentication service works with all phone types, from landlines to smart-phone platforms.

For support please email us at info@miniorange.com or call us at +1 978 658 9387


== Installation ==

= From your WordPress dashboard =
1. Visit `Plugins > Add New`
2. Search for `miniOrange 2 Factor Authentication`. Find and Install `miniOrange 2 Factor Authentication`
3. Activate the plugin from your Plugins page

= From WordPress.org =
1. Download miniOrange 2 Factor Authentication.
2. Unzip and upload the `miniorange-2-factor-authentication` directory to your `/wp-content/plugins/` directory.
3. Activate miniOrange 2 Factor Authentication from your Plugins page.

= Once Activated =
1. Select miniOrange 2-Factor from the left menu and follow the instructions.
2. Once, you complete your setup. Click on Log Out button.
3. Enter the username and click on `Login with miniOrange`.
4. Scan QRCode from your miniOrange mobile app.
5. If your mobile is offline, click on `Click here if your phone is offline`.
6. In your miniOrange mobile app, click on Soft Token and enter OTP.
7. Click on Validate

== Frequently Asked Questions ==

= I already have a login page and I want the look and feel to remain the same when I add 2 factor ? =
				
2-Factor login widget will adopt your login theme automatically. If you have a custom login form other than wp-login.php then we can give you our widget code that you can embed in your login form. If you need any help setting up 2-Factor for your custom login form, please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>.
				
= I want to enable 2-factor only for administrators ? =
				
2-Factor is enabled by default for administrators on plugin activation. You just need to complete your account setup and configure your mobile from <b>Configure Mobile Tab</b>. Once this is done administrators can login using 2-Factor and other users can still login with their password.
				
= I want to enable 2 factor for administrators and end users ? =
				
Go to <b>Login Settings Tab</b> and check <b>Enable 2-Factor for all other users</b>. Enable 2-Factor for admins is checked by default.
				
= I have enabled 2-factor for all users, what happens if an end user tries to login but has not yet registered ? =
				
If a user has not setup 2-Factor yet, he can still login using his password. After logging in, user can go to <b>miniOrage 2-Factor</b> Tab in left side menu and configure his 2-Factor.
				
= My users have different types of phones. What phones are supported? =
				
Currently we support smart phones only. If you need 2-Factor for basic phone, please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>.
				
= What if a user does not have a smart phone? =
				
Currently we support smart phone users only. If you need 2-Factor for basic phone, please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>.
				
= What if I am trying to login from my phone ? =
				
If you are logging in from your phone, you will ask to enter the one time passcode from miniOrange Authenticator App.Go to Soft Token Tab in the app to check one time passcode and enter it in the textbox.
				
= I want to hide default login form and just want to show login with phone? =
				
You should go to <b>Login Settings Tab</b> and check <b>Login with Phone Only</b> checkbox to hide the default login form. 
					
					
= My phone has no internet connectivity, how can I login? =
				
You can login using our alternate login method. Please follow below steps to login:

* Enter your username and click on login with your phone.
* Click on <b>Phone is Offline?</b> button below QR Code.
* You will see a textbox to enter one time passcode.
* Open miniOrange Authenticator app and Go to Soft Token Tab.
* Enter the one time passcode shown in miniOrange Authenticator app in textbox.
* Click on submit button to validate the otp.
* Once you are authenticated, you will be logged in.
					 
= My phone is lost, stolen or discharged. How can I login? =
					 
You can login using our alternate login method. Please follow below steps to login:

* Enter your username and click on login with your phone.
* Click on <b>Forgot Phone?</b> button below QR Code.
* You will see a textbox to enter one time passcode.
* Check your registered email and copy the one time passcode in this textbox.
* Click on submit button to validate the otp.
* Once you are authenticated, you will be logged in.
					   
= My phone has no internet connectivity and i am entering the one time passcode from miniOrange Authenticator App, it says Invalid OTP? =
					   
Click on the <b>Settings Icon</b> on top right corner in <b>miniOrange Authenticator App</b> and then press <b>Sync button</b> under 'Time correction for codes' to sync your time with miniOrange Servers. If you still can;t logged in then please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>.
					
= I want to go back to default login with password? =
					
You should go to <b>Login Settings Tab</b> and uncheck <b>Enable 2-Factor for admins</b> and <b>Enable 2-Factor for all others users</b> checkbox. This will disable 2-Factor and you can login using default login form.
				
= I am upgrading my phone. =
				
You should go to <b>Configure Mobile Tab</b> and reconfigure 2-Factor with your new phone.
				
= What If I want to use any other second factor like OTP Over SMS, Security Questions, Device Id, etc ? =
				
miniOrange authentication service has 15+ authentication methods.One time passcodes (OTP) over SMS, OTP over Email, OTP over SMS and Email, Out of Band SMS, Out of Band Email, Soft Token, Push Notification, USB based Hardware token (yubico), Security Questions, Mobile Authentication, Voice Authentication (Biometrics), Phone Verification, Device Identification, Location, Time of Access User Behavior. To know more about authentication methods, please visit <a href="http://miniorange.com/strong_auth" target="_blank">http://miniorange.com/strong_auth </a>. If you want to have any other 2-factor for your WordPress site, please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>.

== Screenshots ==

1. Download app and configure mobile.
2. Test abd reconfigure mobile.
3. 2 Factor plugin settings.
4. Login form option1 (Enter username)
5. Login form option2 (Enter username)
6. Mobile AUthentication Login Screen ( Authenticate your mobile )
7. OTP Login Screen (If your phone is offline then enter OTP from miniOrange app. If you forgot your phone then enter OTP sent to your registered email. )

== Changelog ==

= 1.8 = 
* Added feature of different login form choice,test authentication and help for configuration and setup.

= 1.7 =
* Bug Fixes: Modifying login screen adaptable to user's login form

= 1.6 =
* Bug Fixes: fetching 2 factor configuration when activating the plugin after deactivating it.

= 1.5 =
* Bug Fixes: Login issues and password save issues resolved

= 1.4 =
* Bug Fixes: Authentication was not working on some version of php. 

= 1.3 =
* Bug Fixes 

= 1.2 =
* Added 2 factor for all users along with forgot phone functionality.

= 1.1 =
* Added email ID verification during registration.

= 1.0.0 =
* First version supported with mobile auhthentication for admin only.

== Upgrade Notice ==

= 1.8 = 
* Added feature of different login form choice,test authentication and help for configuration and setup.

= 1.7 =
* Bug Fixes: Modifying login screen adaptable to user's login form

= 1.6 =
* Bug Fixes: fetching 2 factor configuration when activating the plugin after deactivating it.

= 1.5 =
* Bug Fixes: Login issues and password save issues resolved

= 1.4 =
* Bug Fixes: Authentication was not working on some version of php.

= 1.3 =
* Bug Fixes

= 1.2 =
* Added 2 factor for all users along with forgot phone functionality.

= 1.1 = 
* Added email ID verification during registration.

= 1.0.0 =
First version of plugin.