<?php
/**
* @category zoop
* @package cache
*/
// Copyright (c) 2007 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.


define_once('AUTH_USER_TABLE', "user");
define_once('AUTH_GROUP_TABLE', "group");
define_once('AUTH_ROLE_TABLE', "role");
define_once('AUTH_USER_GROUP_TABLE', "user_group");
define_once('AUTH_USER_ROLE_TABLE', "user_role");
define_once('AUTH_USERNAME_FIELD', "username");
define_once('AUTH_PASSWORD_FIELD', "password");
define_once('AUTH_PASSWORD_ENCRYPTION', "md5");
define_once('AUTH_SESSION_USER', 'activeUser');
define_once('AUTH_DENIED_LOCATION', '/denied');
define_once('AUTH_LOGIN_LOCATION', '/login');
define_once('AUTH_POSTLOGOUT_LOCATION', "/");

/** 
 * component_auth
 * Currently this component is database driven and dependant on sessions. It is configurable, but will use the default database connection
 *
 * @uses component
 * @package
 * @version $id$
 * @copyright 1997-2007 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class component_auth extends component
{
	function getIncludes()
	{
		$file = dirname(__file__);
		return array(
				"zauth" => $file . "/zauth.php"
		);
	}
	
	function component_auth()
	{
		$this->requireComponent('db');
	}
	
}
?>
