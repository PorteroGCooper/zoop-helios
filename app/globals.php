<?
/**
* set up global constants and variables
* 
* splites up the path info, prepares other variables for the application.
* 
* @package app
* @subpackage globals
*/

// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

//////////////////////////////////////////////
//
//	Globals.php
//
//	//	these comments need to be rewritten and distributed throughout the document
//
//	*	REQUEST_TYPE = "HTML" or "JSRS" depending on the request type.
//	*	SCRIPT_REF = base ref for the script (trailing /)
//	*	SCRIPT_URL = actual URL of the script (no path_info or querystring)
//	*	SCRIPT_BASE = the location of where the script is located
//	*	VIRTUAL_URL = Full URL to the current page.
//	*	ORIG_PATH = $PATH_INFO varible
//	*	RELATIVE_PATH = path to script relative to the virtual location
//			i.e. ../../../
//
//
//////////////////////////////////////////////

/**
*
*	ensure that PATH_INFO is getting to us in a format we like.
*	PATH_INFO is everything between the filename and the ? in the url
*
**/
	if(!isset($_SERVER["PATH_INFO"]))
	{
		$GLOBALS['PATH_INFO'] = "";
	}
	else
	{
		//	add a slash to the front if it's not already there
		if(substr($_SERVER["PATH_INFO"],0,1) == "/")
			$GLOBALS['PATH_INFO'] = $_SERVER["PATH_INFO"];
		else
			$GLOBALS['PATH_INFO'] = "/" . $_SERVER["PATH_INFO"];

	}

	$GLOBALS['Sname'] = $_SERVER["SCRIPT_NAME"];

	//	if $Sname ends with $PATH_INFO then strip $PATH_INFO from the end
	if(substr($GLOBALS['Sname'], strlen($GLOBALS['PATH_INFO']) * -1) == $GLOBALS['PATH_INFO'])
	{
		$GLOBALS['Sname'] = substr_replace ($GLOBALS['Sname'], "", strlen($GLOBALS['PATH_INFO']) * -1);
	}
	$GLOBALS['PATH_INFO'] = preg_replace("'\\s'", "", $GLOBALS['PATH_INFO']);
	//if (isset($PATH_INFO)) { $Sname = ereg_replace($PATH_INFO, "", $Sname); }



/**
*
*	Build SCRIPT_REF and SCRIPT_URL
*
*	SCRIPT_REF: base ref for the script (trailing /)
*	SCRIPT_URL: actual URL of the script (no path_info or querystring)
*
**/
	if($_SERVER["SERVER_PORT"] == 443)
	{
		$preht = "https://";
	}
	else
	{
		$preht = "http://";
	}

	if (strtoupper( substr($GLOBALS['Sname'],-4) ) != ".PHP" && substr($GLOBALS['Sname'], -2) != '.4' && substr($GLOBALS['Sname'],-1,1) != "/" )
	{

		define("SCRIPT_REF", $preht . $_SERVER["HTTP_HOST"] . $GLOBALS['Sname'] . "/");
		define("SCRIPT_URL", $preht . $_SERVER["HTTP_HOST"] . $GLOBALS['Sname']);
		define("HOME_URL", SCRIPT_URL);
		define("SCRIPT_BASE", SCRIPT_URL);
	}
	else
	{
		define("SCRIPT_REF", $preht . $_SERVER["HTTP_HOST"] . $GLOBALS['Sname']);
		define("SCRIPT_BASE",$preht . $_SERVER["HTTP_HOST"] . substr($GLOBALS['Sname'],0, strrpos($GLOBALS['Sname'], "/")));

		if(substr($GLOBALS['Sname'], -1, 1) == "/")
		{
			$GLOBALS['tSname'] = substr($GLOBALS['Sname'], 0, strlen($GLOBALS['Sname']) - 1);
		}
		else
		{
			$GLOBALS['tSname'] = $GLOBALS['Sname'];
		}

		define("SCRIPT_URL", $preht . $_SERVER["HTTP_HOST"] . $GLOBALS['Sname']);
		define("HOME_URL", dirname(SCRIPT_URL));
		//die(SCRIPT_URL);
	}

/**
*
*	Build SCRIPT_REF and SCRIPT_URL
*
*	ORIG_PATH: same as $PATH_INFO
*	VIRTUAL_URL: Full URL to the current page.
*	RELATIVE_PATH: path to script relative to the virtual location
*			i.e. ../../../    I'm not sure what use this is, and I'm pretty sure we don't use it in the framework anywhere
*
**/

	define("ORIG_PATH", $GLOBALS['PATH_INFO']);

	define("VIRTUAL_URL", SCRIPT_URL . ORIG_PATH);

	$GLOBALS['PATH_ARRAY'] = explode("/", ORIG_PATH);

	//$patharg = split("/", $GLOBALS['PATH_INFO']);
	//explode should work fine here, might be faster, not that we care much about speed,
	//but we know explode better, he's our friend.
	//$patharg = explode("/", $GLOBALS['PATH_INFO']);

	$xtrapath = "";
	foreach($GLOBALS['PATH_ARRAY'] as $key =>$val)
	{
		if ($val)
		{
			//echo "$key / $val <BR>";
			$xtrapath = "../" . $xtrapath;
		}
	}

	define("RELATIVE_PATH", $xtrapath);


/**
*
*	Guess the request type( and it better be a good guess).
*
**/
	if (isset($_REQUEST['jsrsContext']) && substr($_REQUEST['jsrsContext'], 0, 7) == "phpjsrs")
	{
		define("REQUEST_TYPE", "JSRS");
	}
	//*
	elseif (xmlrpc_server::isRequest())
	{
		define("REQUEST_TYPE", "XMLRPC");
		$GLOBALS["zoopXMLRPCServer"] = new xmlrpc_server();
		$GLOBALS["zoopXMLRPCServer"]->startServer();
	}
	//*/
	else
	{
		define("REQUEST_TYPE", "HTML");
	}

/**
*
*	For security reasons, hide direct access to the $_POST variable.
*
**/
	$GLOBALS['POSTCOPY'] = $_POST;

	if(defined("hide_post") && hide_post == 1)
	{
		unset($_POST);
	}

/******** URL Rewrite example ****************\
*
* This is an example of the mod_rewrite commands
* to put in your httpd.conf file to get rid of
* index.php in the URL line.
*
        RewriteEngine on

        RewriteCond   %{THE_REQUEST} !^(.*)/resources(.*)$
        RewriteRule   ^(/.*)$ /home/dir/path/to/index.php [E=PATH_INFO:$1]

        RewriteCond   %{THE_REQUEST} ^(.*)/resources/(.*)$
        RewriteRule   ^/resources/(.*)$ /home/dir/path/to/resources/$1

        RewriteLog logs/test-rewritelog
        RewriteLogLevel 9
*
* Cool, eh?  Change the paths apropriately.
* This assumes that all your normal files are in /resources/ somewhere, including images, css, js, etc
\******** End URL Rewrite example ************/

?>
