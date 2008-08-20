<?
/////////////////////////////////////////////////////////////
// Email configuration
/////////////////////////////////////////////////////////////

	define("app_system_email_address", 'your email address');
	/* System uses this as the from address as you send mail if no address is specified  */
	/*  USED ONLY FOR CONVIENCE IN YOUR OWN CODE, NOT IN THE FRAMEWORK */


        define("mail_type", "smtp"); // can be 'smtp' or 'sendmail'
        // define("mail_smtp_host", "communitymail.takkle.com");
        define("mail_smtp_host", "192.168.200.227");
        define("mail_smtp_port", 25);
        define("mail_smtp_auth_use", false);
        define("mail_smtp_auth_username", "info@takkle.com");
        define("mail_smtp_auth_password", "g0pl4y");
        // this is the directory within the templates/default(or other if so define) where the message templates reside
        define("gui_messages", "messages");



/*
$site['smtp_host'] = "smtp.emailsrvr.com";
$site['smtp_port'] = 587;
$site['smtp_mode'] = 'enabled';
$site['smtp_username'] = 'info@takkle.com';
$site['smtp_password'] = 'takkle#this';


	define("mail_type", "smtp"); // can be 'smtp' or 'sendmail'
	define("mail_smtp_host", "smtp.emailsrvr.com");
	define("mail_smtp_port", 587);
	define("mail_smtp_auth_use", true);
	define("mail_smtp_auth_username", "info@takkle.com");
	define("mail_smtp_auth_password", "g0pl4y");
	// this is the directory within the templates/default(or other if so define) where the message templates reside
	define("gui_messages", "messages");
*/
//	define("dev_email_address",'youremailaddress');

?>
