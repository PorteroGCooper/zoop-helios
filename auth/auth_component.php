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
		return array(
				"auth" => $this->getBasePath() . "/auth.php"
		);
	}

	function getRequiredComponents()
	{
		return array('db');
	}
}
?>