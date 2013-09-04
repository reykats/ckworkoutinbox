<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// echo "-config_workoutinbox start-";
/*
|--------------------------------------------------------------------------
| Down For Maintenance
|--------------------------------------------------------------------------
|
| You set this to true if you want :
|
|    * all apis to return this status
|    * all apis to return this message
*/
$config['down_for_maint'] = FALSE;
$config['down_for_maint_status'] = 401;
$config['down_for_maint_message'] = 'Maitenance happening, please try again in an hour.';
/*
|--------------------------------------------------------------------------
| workoutinbox.com backend version
|--------------------------------------------------------------------------
|
| The version of the frontend and backend must match
|
*/
$config['workoutinbox_backend_p_staff_version'] = '1.1';
$config['workoutinbox_backend_p_user_version'] = '1.1';
$config['workoutinbox_backend_t_staff_version'] = '1.1';
$config['workoutinbox_backend_t_user_version'] = '1.1';
$config['workoutinbox_backend_version'] = '1.1';
$config['invalid_version_status'] = 401;
$config['invalid_version_message'] = 'Update to the latest vesion';
/*
|--------------------------------------------------------------------------
| workoutinbox.com Server timezone
|--------------------------------------------------------------------------
|
| the timezone that the server is set to.  (in server file /etc/timezone)
|
*/
$config['server_timezone'] = 'America/Chicago';
/*
|--------------------------------------------------------------------------
| workoutinbox.com client_data folder name and location (relative to the site's public folder)
|--------------------------------------------------------------------------
|
| The version of the frontend and backend must match
|
*/
$config['workoutinbox_client_data'] = '../../../client_data';
/*
|--------------------------------------------------------------------------
| Determine if this is a test server or not
|--------------------------------------------------------------------------
|
| Is this a Test Server
|
*/
if ( isset($_SERVER['SERVER_NAME']) ) {
	$host = $_SERVER['SERVER_NAME'];
} else {
	$host = php_uname('n');
}

if ( $host == 'workoutinbox.com' || $host == 'www.workoutinbox.com' ) {
	$config['workoutinbox_test_server'] = false;
	$config['workoutinbox_server'] = 'prod';
} else {
	$config['workoutinbox_test_server'] = true;
	$temp = explode('.',$host);
	$config['workoutinbox_server'] = $temp[0];
	unset($temp);
}

// echo "-config_workoutinbox end-";

/* End of file */