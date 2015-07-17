=== 2 Factor authentication plugin for wordpress ===
Contributors: miniOrange
Tags: 2 factor authentication, 2 step verification, 2FA, single sign on, multi factor authentication, Google authenticator, login, authy, Clef, 2 Factor, yubico, Two Factor, Authentication, Mobile Authentication, otp, tfa, strong authentication, 2 step authentication, mobile, smartphone authentication, Multifactor authentication, no password, passwordless login, website security, android, iphone, one time passcode, soft token, miniorange
Requires at least: 3.0.1
Tested up to: 4.2.2
Stable tag: 1.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

2 Factor authentication for secure login.

== Description ==

A highly secure two-factor authentication for all users of your site. 
. It can either work in addition to the password or replaces it with mobile app based authentication
. It works for administrators and other users
. It can be deployed for your entire userbase in minutes
. Both iPhone and Android phones are supported.
. If your phone is lost or stolen or discharged, it offers an alternate login method
. If your phone is offline, you can use a soft token to login
. It offers inline registration of users so you can simply activate and configure the plugin and you are all set. 

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
= I need to customize the plugin or I need support and help =
Please email us at info@miniorange.com

= My phone is offline, how do I login? =
Use one time passcode login by clicking on 'click here if your phone is offline'

= When I enter OTP from app after clicking on `click here if your phone is offline`, it says `Invalid OTP` =
Click on the Settings icon in the app and then press <b>Sync</b> button under 'Time correction for codes:'.

= For any other query/problem/request =
Please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>

= My phone is lost, stolen or discharged. =
You should click on `Click here if you forgot your phone` link given after you click on login button. An OTP over your registered email will be send. Use that OTP to login into account. 

= I am upgrading my phone. =
You should go and reconfigure with your new phone after logging in. 

= I shot myself in the foot, I cant login? =
Remove the plugin from the plugins directory. Copy it out and you should be all set.

= I can login to wordpress but i want to go back to normal login. =
Deactivate the plugin

= I verified the OTP received over my email and entering the same password that I registered with, but I am still getting the error message - "Invalid password." =
Please write to us at info@miniorange.com and we will get back to you very soon.

= What other authentication methods do you suppport? =
miniOrange authentication service has 15+ authentication methods.

One time passcodes (OTP) over SMS, OTP over Email, OTP over SMS and Email, Out of Band SMS, Out of Band Email, Soft Token, Push Notification, USB based Hardware token (<b>yubico</b>), Security Questions, Mobile Authentication, Voice Authentication (Biometrics), Phone Verification, Device Identification, Location, Time of Access
User Behavior.

To know more about authentication methods, please visit http://miniorange.com/strong_auth . If you want to have any other 2-factor for your WordPress site, <a href="http://miniorange.com/contact" target="_blank">Contact us</a>.

== Screenshots ==

1. Validate your OTP.
2. Configure your mobile
3. Username Login Screen (Enter username)
4. Mobile AUthentication Login Screen ( Authenticate your mobile )
5. OTP Login Screen (If your phone is offline then enter OTP from miniOrange app.)

== Changelog ==

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