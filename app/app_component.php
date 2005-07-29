<?
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

include_once(dirname(__file__) . "/error.php");
include_once(dirname(__file__) . "/xmlrpcClasses.php");

if(isset($_SERVER["HTTP_HOST"]))
{
	//globals.php only deals with http variables.
	include_once(dirname(__file__) . "/globals.php");
}
include_once(dirname(__file__) . "/utils.php");

class component_app extends component
{
	function component_app()
	{
		// set up error reporting right quick.
		//if output compression or buffering is on, we have to know for correct live error handling...
		define('__zoop_error_ob_start', ob_get_level());
		
		error_reporting(E_ALL);
		$debugmode = app_status;
		//$debugmode = 'test';
		

		if(php_sapi_name() != "cli")
		{
			if ($debugmode == "dev" || $debugmode == 'desktop')
				set_error_handler('error_debug_handler');
			else
				set_error_handler('error_live_handler');
		}
		else
			set_error_handler('error_debug_handler');
	}


}

?>