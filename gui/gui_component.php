<?php
/**
* @category zoop
* @package gui
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
 * component_gui
 * 
 * 
 * @group templates
 * @section Template Resources
 * 
 * When using a database as a template resource, either build your database,
 * using the default values in gui/config.yaml, or override them in your app's
 * config.yaml file.
 * 
 * @endgroup
 *
 * @uses component
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_gui extends component {
	/**
	 * component_gui
	 *
	 * @access public
	 * @return void
	 */
	function component_gui() {
		$this->requireComponent('session');
		$this->requireComponent('validate');
		if (in_array('db', (array)Config::get('zoop.gui.template_resources.drivers'))) {
			$this->requireComponent('db');
		}
		if (in_array('doctrine', (array)Config::get('zoop.gui.template_resources.drivers'))) {
			$this->requireComponent('doctrine');
		}
	}

	/**
	 * run
	 *
	 * @access public
	 * @return void
	 */
	function run() {
		$config = Config::get('zoop.gui');
		mkdirr($config['directories']['temp']);
		$GLOBALS['gui'] = new gui();
	}
	
	function getIncludes() {
		return array("gui" => $this->getBasePath() . "/gui.php");
						
	}
}
