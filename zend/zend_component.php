<?php

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
 * This component will load Components From the Zend Framework.
 *
 * To use, define the location of the zend framework in your config at 'zoop.zend.dir'
 * The default for this value is APP_DIR/lib/zend/library
 * Wherever you want to use a zend component, require it,
 *
 * @code
 *   require_once('Zend/Service/Flickr.php');
 *   $flickr = new Zend_Service_Flickr('YOUR_FLICKR_API_KEY HERE');
 * @endcode
 *
 * @group Zend
 *   
 * @endgroup
 *
 * @ingroup components
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}/
 */
class component_zend extends component {
	function init() {
		$dir = Config::get('zoop.zend.dir');

		/**
		 * @todo Find correct environment variable when you get off the plane.
		 */
		$sys = getenv('operating system');
		if ($sys == 'Windows') {
			ini_set('include_path', ini_get('include_path').';'. $dir );
		} else {
			ini_set('include_path', ini_get('include_path').':'. $dir );
		}

	}
}
