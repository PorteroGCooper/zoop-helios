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

include_once(dirname(__file__) . "/text.php");

/**
 * A jQuery Autocomplete guiControl.
 *
 * @ingroup gui
 * @ingroup GuiControl
 * @ingroup jquery
 *
 * @author Justin Hileman {@link http://justinhileman.com}
 */
class AutocompleteControl extends TextControl {
	
	function initControl() {
		global $gui;
		$gui->add_js('/zoopfile/gui/js/jquery.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/jquery.autocomplete.js', 'zoop');
		$gui->add_css('/zoopfile/gui/css/autocomplete.css', 'zoop');
	}
	
/*
	function validate() {
		// die_r($this->getValue());
		// die_r($GLOBALS);
	}
*/

	/**
	 * Render GuiControl
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string HTML autocomplete input GuiControl
	 */
	protected function render() {
		global $gui;

		$autocomplete_id = $this->getId();
		
		if (isset($this->params['url'])) {
			$callback_url = url($this->params['url'], true);
		} else {
			trigger_error('An index or callback url must be provided for autocomplete GuiControls.');
			return;
		}

		$html = parent::render();
		$gui->add_jquery('$("#' . $autocomplete_id . '").autocomplete("' . $callback_url . '");');
		return $html;
	}
}