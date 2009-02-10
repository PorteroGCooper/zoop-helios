<?php

define("APP_DIR", dirname(__file__));	// we should use this whenever including a file from our application
define("APP_STATUS", "dev");
define('ZOOP_DIR', APP_DIR . "/../"); // it can be relative
define("LOG_FILE", APP_DIR . "/log/errors.log.html");

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . ZOOP_DIR . '/lib/pear');

define("app_temp_dir", dirname(__file__) . '/tmp');

define('strip_url_vars' ,1); //disallow spaces in url parameters, helps prevent sql injection.
define("verify_queries", 1); //disallow multi statement queries, e.g. select * from person where id = '';delete from person;
//helps prevent some forms of sql injection.
define('filter_input', 1); //tells POST::get family of functions to filter post variables based on type.
define('hide_post', 1); //turns off access to post variables through anything but the POST::get family of functions
define('zone_saveinsession', 0); //determines whether zone objects are saved in sessions, allowing you to use zones to contain persistent variables
//defaults to true, for Backwards Compatibility.
define('show_warnings', 1); // determines whether the bug function is displayed or not.
define('app_url_rewrite', true);
//define("sequence_file", dirname(__file__) . '/sequence.xml');
