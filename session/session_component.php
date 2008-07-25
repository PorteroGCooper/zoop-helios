<?php
/**
* @category zoop
* @package session
*/
// Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.



/**
 * component_session 
 * 
 * @uses component
 * @package 
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_session extends component
{
	/**
	 * init 
	 * 
	 * @access public
	 * @return void
	 */
	function init()
	{
		include($this->getBasePath() . "/session_handler_" . session_type . ".php");

		if (isset($HTTP_GET_VARS["cache_limiter"]))
		{
			session_cache_limiter($HTTP_GET_VARS["cache_limiter"]);
		}
		if (defined("session_path") && session_path == "server")
			session_set_cookie_params(ini_get('session.cookie_lifetime'), "/");
		else
			session_set_cookie_params(ini_get('session.cookie_lifetime'), $_SERVER['SCRIPT_NAME']);
		
		# starting sessions
		session_start();

		/* if the get var "session" == "destroy", reset the sessions. (debug purposes) */
		if (isset($_GET["session"]) && $_GET["session"] == "destroy")
		{
			session_destroy();
			foreach($_SESSION as $key => $value)
				unset($_SESSION[$key]);
		}

		// creating "$sGlobals" in session if not already there and assigning to global var.
		if(!isset($_SESSION["sGlobals"]))
		{
			session_register("sGlobals");
			$_SESSION["sGlobals"]->set = 1;
		}
		$GLOBALS['sGlobals'] =& $_SESSION["sGlobals"];

		/*
		For tracking users navigation through the site....
		*/
		if(!isset($_SESSION['sUrls']))
		{
			session_register('sUrls');
			$_SESSION['sUrls'] = array();
		}
		if(!isset($PATH_ARRAY[1]) || $PATH_ARRAY[1] != 'keepOpen')
			array_push($_SESSION['sUrls'], $_SERVER['REQUEST_METHOD'] . ' ' . VIRTUAL_URL);
		if(count($_SESSION['sUrls']) > 20)
		{
			array_shift($_SESSION['sUrls']);
		}
		$GLOBALS['sUrls'] = &$_SESSION['sUrls'];
	}
}
?>
