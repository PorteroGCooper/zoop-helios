<?php
/**
* Main component file for component_app
*
* Class to initialize the app component. This is the main component in zoop,
* and should almost always be included. Almost all of the other components depend
* on this one.
* @category zoop
* @package app
* @subpackage component_app
*/


//Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/**#@+
* include subpackages
*
*/
/**#@-*/
/**
* @package app
*/
class component_app extends component
{
	function component_app()
	{
		//inlude the define_once function
//		include($this->getBasePath() . "/define.php");

		// here we need the config for app, because error.php, and globals.php need it.
		// but since this is called in the contructor of zoop, we can't access the global zoop object use it to include the config/app.php file. 
		// easiest solution... Place the config lines from config/app.php into config.php 
		// then including the defaults here.
//		$this->defaultConstants();

		//include errorhandling, as soon as possible
		include($this->getBasePath() . "/error.php");
		include($this->getBasePath() . "/xmlrpcClasses.php");
		include($this->getBasePath() . "/utils.php");
		if(isset($_SERVER["HTTP_HOST"])) {	
		        //globals.php only deals with http variables.
		        include($this->getBasePath() . "/globals.php");
		}
		
		$globalTime = &$GLOBALS['globalTime'];
		logprofile($globalTime);
		include($this->getBasePath() . "/request_utils.php");
		// set up error reporting right quick.
		//if output compression or buffering is on, we have to know for correct live error handling...
		define('__zoop_error_ob_start', ob_get_level());

		//error_reporting(E_ALL);
		if (defined('APP_STATUS')) {
			$debugmode = APP_STATUS;
		} else {
			$debugmode = 'live';
		}

		if(php_sapi_name() != "cli") {
			if ($debugmode == "dev" || $debugmode == 'desktop') {
				set_error_handler('error_debug_handler');
			} else {
				set_error_handler('error_live_handler');
			}
		} else {
			set_error_handler('error_debug_handler');
		}
		
		/**************
		find the current timezone.....
		**************/
		global $tz;
		$tz = date('T');
		$dst = date('Z');
		if($dst)
		{
			$tz = str_replace('D', 'S', $tz);
		}
	}
}

?>
