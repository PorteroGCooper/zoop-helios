<?php
/**
* @category zoop
* @package cache
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

define_once("app_cache_dir", app_temp_dir . '/cache/'); // only used for file cache

/**
 * component_cache
 *
 * @uses component
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_cache extends component
{
	/**
	 * init
	 *
	 * @access public
	 * @return void
	 */
	function init()
	{
		// make sure the directories are writable and exist or are created properly
 		//mkdirr(app_cache_dir);
	}

	function getIncludes()
	{
		return array(
				"zcache_driver" =>  $this->getBasePath() . "/zcache_driver.php",
				"zcache" =>  $this->getBasePath() . "/zcache.php"
		);
	}
}
?>
