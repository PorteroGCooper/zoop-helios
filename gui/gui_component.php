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
 * @ingroup components
 * @ingroup gui
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_gui extends component {

	function __construct() {
		$this->requireComponent('session');
		$this->requireComponent('validate');

		// Handle all the template resource driver requirements
		foreach ((array)Config::get('zoop.gui.template_resources.drivers') as $driver) {
			switch ($driver) {
				case 'db':
					$this->requireComponent('db');
					break;
				case 'doctrine':
					$this->requireComponent('doctrine');
					break;
			}
		}
	}

	function run() {
		global $gui;
		mkdirr(Config::get('zoop.gui.directories.temp'));
		$gui = new gui();
	}
	
	function getIncludes() {
		return array("gui" => $this->getBasePath() . "/gui.php");
	}
}
