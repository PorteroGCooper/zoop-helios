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
 * WidgetGui
 *
 * @uses Smarty
 * @ingroup gui
 * @ingroup GuiWidget
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class WidgetGui extends gui {

	/**
	 * WidgetGui
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
		global $gui;

		parent::__construct();
		
		// figure out template directories
		$template_dirs = $gui->template_dir;
		foreach ($template_dirs as $_key => $_val) {
			$template_dirs[$_key] = $_val . "/widgets";
		}
		$this->template_dir = $template_dirs
	}
	
	function add_css($path, $scope = 'app') {
		global $gui;
		$gui->add_css($path, $scope);
	}
	
	function add_js($path, $scope = 'app') {
		global $gui;
		$gui->add_js($path, $scope);
	}
	
	function add_jquery($js = null) {
		global $gui;
		$gui->add_jquery($js);
	}
	
	function initRegions()  { trigger_error("This function doesn't do anything on WidgetGui objects."); }
	function addRegion()    { trigger_error("This function doesn't do anything on WidgetGui objects."); }
	function sortRegions()  { trigger_error("This function doesn't do anything on WidgetGui objects."); }
	function assignRegion() { trigger_error("This function doesn't do anything on WidgetGui objects."); }

	function fetch($tpl_file, $cache_id = null, $compile_id = null) {
		return Smarty::fetch($tpl_file, $cache_id, $compile_id);
	}

	function __call($method, $args) {
		trigger_error($method . " method undefined on WidgetGui object.", E_USER_ERROR);
	}
}