<?
	define("app_dir", dirname(__file__));	// we should use this whenever including a file from our application
	define("app_status", "dev");
	define('zoop_dir', app_dir . "/../"); // it can be relative
	define("LOG_FILE", app_dir . "/log/errors.log.html");
//  	uncomment one of the following two lines if using the zoop/libs instead of your systemwide pear libraries.
	ini_set('include_path',ini_get('include_path').':'. zoop_dir . '/lib/pear:'); // FOR UNIX
//	ini_set('include_path',ini_get('include_path').';'. zoop_dir . '/lib/pear:'); // FOR WINDOWS

	define("app_temp_dir", dirname(__file__) . '/tmp');

	define('strip_url_vars' ,1); //disallow spaces in url parameters, helps prevent sql injection.
	define("verify_queries", 1); //disallow multi statement queries, e.g. select * from person where id = '';delete from person;
	//helps prevent some forms of sql injection.
	define('filter_input', 1); //tells getPost family of functions to filter post variables based on type.
	define('hide_post', 1); //turns off access to post variables through anything but the getPost family of functions
	define('zone_saveinsession', 0); //determines whether zone objects are saved in sessions, allowing you to use zones to contain persistent variables
	//defaults to true, for Backwards Compatibility.
	define('show_warnings', 1); // determines whether the bug function is displayed or not.
	define('app_url_rewrite', true);
	//define("sequence_file", dirname(__file__) . '/sequence.xml');
?>
