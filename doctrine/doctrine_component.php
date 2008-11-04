<?php
/**
* @category zoop
* @package doctrine
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
 * @package doctrine
 * @uses component
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}/
 */
class component_doctrine extends component {
	function init() {
		$doctrinePath = Config::get('zoop.doctrine.include_path');
		require_once(($doctrinePath ? "$doctrinePath/" : '') . 'Doctrine.php'); 
		spl_autoload_register(array('Doctrine', 'autoload'));
	}
	
	function run() {
		$dsn = Config::get('zoop.doctrine.dsn');
		$model_dir = Config::get('zoop.doctrine.model_dir');
		Doctrine_Manager::connection($dsn);

		Doctrine_Manager::getInstance()->setAttribute('model_loading', 'conservative');
		Doctrine::loadModels($model_dir); // This call will not require the found .php files
	}
}
