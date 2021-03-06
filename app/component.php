<?php

//Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/**
 * Main component file for component_app
 *
 * Class to initialize the app component. This is the main component in zoop,
 * and should almost always be included. Almost all of the other components depend
 * on this one.
 * 
 * @ingroup app
 * @ingroup components
 * @extends component
 */
class Component_App extends Component {
	function __construct() {
		$base = $this->getBasePath();

		//include errorhandling, as soon as possible
		include($base . "/error.php");
		include($base . "/xmlrpcClasses.php");
		include($base . "/utils.php");
		include($base . "/RequestUtils.php");
		include($base . "/request_utils.php");
		
		if(isset($_SERVER["HTTP_HOST"])) {
			//globals.php only deals with http variables.
			include($base . "/globals.php");
		}
		
		$globalTime = &$GLOBALS['globalTime'];
		logprofile($globalTime);
		
		// set up error reporting right quick.
		// if output compression or buffering is on, we have to know for correct live error handling...
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
	}
}