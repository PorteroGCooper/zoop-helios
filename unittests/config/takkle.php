<?php

require_once ( "templates.php" );

/*
 * SET UP VARIOUS CONSTANTS AND DATABASE CONNECTION PARAMETERS.
 *
 */

if ( APP_STATUS == "dev" || APP_STATUS == "test") {
	$site['server_type'] = 'DEV';
	$ROOT_PATH = '/var/www';
	$SITE_DIR  = '';

	// MSSQL CONFIG
	$site['dbhost'] = "192.168.200.226";
	$site['dbuser'] = 'takkle_test';
	$site['dbpass'] = 'eH8Ie007';
	$site['db']     = 'takkle_test';
	$site['video_host_path_dir'] = 'takkle_dev';

	// MYSQL CONFIG
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_hostname'] = '72.32.48.228';
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_username'] = 'takkle_dev';
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_password'] = 'takkle#9497#';
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_database'] = 'takkle_ads';
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_port']     = '3306';
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_is_local'] = false;	// use local connection using sockets

	$site['affiliate_root'] = 'http://dev.affiliate.takkle.com';
	$site['ad_zones'] = array('top' => 14, 'bottom' => 17, 'si_photo_contest_left' => 18, 'si_photo_contest_bottom' => 20, 'si_photo_contest_top' => 19);
}
else  // THIS IS THE LIVE ENVIRONMENT.
{
	$site['server_type'] = 'LIVE';
	$ROOT_PATH = '/var/www/html';
	$SITE_DIR  = 'takkle.com';

	// MSSQL CONFIG
	$site['dbhost'] = '192.168.200.171'; // 192.168.200.200
	$site['dbuser'] = 'takkle_web';
	$site['dbpass'] = 'eH8Ie007';
	$site['db']     = 'takkle';
	$site['dsn']    = $site['dbhost'];
	$site['video_host_path_dir'] = 'takkle';

	// MYSQL CONFIG
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_hostname'] = '192.168.200.229'; // local IP of mysql001.takkle.com
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_username'] = 'takkle_ads';
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_password'] = 'PuR312';
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_database'] = 'takkle_ads';
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_port']     = '3306';
	$TAKKLE_CONF_AD_SERVER_DB_SETTINGS['db_is_local'] = false;      // use local connection using sockets

	$site['affiliate_root'] = 'http://affiliate.takkle.com';
	$site['ad_zones'] = array('top' => 11, 'bottom' => 12, 'si_photo_contest_left' => 10, 'si_photo_contest_bottom' => 13, 'si_photo_contest_top' => 14);
}

$SITE_ROOT = $ROOT_PATH.'/'.$SITE_DIR;
$PHOTO_ROOT		= $SITE_ROOT;

$site['ADMIN_EMAIL']="info@takkle.com";
$site['ADMIN_TO_NAME']="TAKKLE";
$site['ADMIN_HELP_EMAIL']="help@takkle.com";
$site['ADMIN_FEEDBACK_EMAIL']="feedback@takkle.com";
$site['ADMIN_DONOTREPLY_EMAIL']="donotreply@takkle.com";
$site['ADMIN_NEWCOACH_EMAIL']="newcoach@takkle.com";
$site['ADMIN_NEWSCHOOL_EMAIL']="newschool@takkle.com";
$site['ADMIN_FROM'] = "update@takkle.com";
$site['ADMIN_FROM_NAME'] = "TAKKLE";

/*
 * SET UP THE USER TYPE ARRAY GLOBALLY
 */

$site['user_type']['Admin'] = 1;
$site['user_type']['Student'] = 2;
$site['user_type']['Alumn'] = 3;
$site['user_type']['Statistician'] = 4;
$site['user_type']['Teacher'] = 5;
$site['user_type']['Coach'] = 6;
$site['user_type']['Fan'] = 7;
$site['user_type']['Recruiter'] = 8;

// <comment desc="DEFINE KNOWN CONTEXTS">
$commentContexts['default'] = 1;
$commentContexts['boards'] = 1;
$commentContexts['ratings'] = 2;
$commentContexts['si_nomination'] = 3;
// </comment>

?>
