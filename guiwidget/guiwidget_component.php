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
 * guiWidget Zoop component
 *
 * @uses component
 * @ingroup zoop
 * @ingroup components
 * @ingroup guiwidget
 * 
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_guiwidget extends component {
	function component_guiwidget() {
		$this->requireComponent('gui');
	}

	function getIncludes() {
		return array("GuiWidget" => Config::get('zoop.guiwidget.directories.zoop') . 'GuiWidget.php',
// 		"GuiContainer" => zoop_guiwidget_dir. 'GuiContainer.php',
		"WidgetGui" => dirname(__file__) . "/widgetgui.php");
	}

	/**
	 * Static function to include a guiWidget file.
	 *
	 * @param mixed $type
	 * @access public
	 * @return void
	 */
	static function includeGuiWidget($type) {
 		$filename = strtolower($type).".php";

		if(file_exists(Config::get('zoop.guiwidget.directories.app'). "$filename")) {
			include_once(Config::get('zoop.guiwidget.directories.app'). "$filename");
		} else if(file_exists(Config::get('zoop.guiwidget.directories.zoop'). "$filename")) {
			include_once(Config::get('zoop.guiwidget.directories.zoop'). "$filename");
		} else {
			trigger_error("Please Implement a $type widget and place it in " .
					Config::get('zoop.guiwidget.directories.app'). "$filename" . " or " .
					Config::get('zoop.guiwidget.directories.zoop'). "$filename");
		}
	}
}

/**
 * Get an instance of a guiWidget
 *
 * @param string $type
 * @param string $name
 * @param bool $useGlobal
 * @access public
 * @return void
 */
function &getGuiWidget($type, $name, $useGlobal = false) {
	if($useGlobal) {
		global $guiwidgets;
		if(isset($guiwidgets[$type][$name])) {
			return $guiwidgets[$type][$name];
		}
	}

	component_guiwidget::includeGuiWidget($type);

	$className = "guiwidget_{$type}";

	if($useGlobal) {
		$guiwidgets[$type][$name] = &new $className($name);
		return $guiwidgets[$type][$name];
	} else {
		$control = &new $className($name);
		return $control;
	}
}