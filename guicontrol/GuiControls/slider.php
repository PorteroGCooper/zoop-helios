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

include_once(dirname(__file__) . "/select.php");

/**
 * Slider GuiControl class.
 *
 *
 * @ingroup gui
 * @ingroup guicontrol
 * @author Andy Nu <nuandy@gmail.com>
 */
 
class SliderControl extends SelectControl {
	
	function initControl() {
		global $gui;
		$gui->add_js('/zoopfile/gui/js/jquery.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/ui.core.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/ui.slider.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/ui.accessibleUISlider.js', 'zoop');
		$gui->add_css('/zoopfile/gui/css/slider.css', 'zoop');
	}
	
	function getPersistentParams() {
		return array('validate');
	}

	/**
	 * Render GuiControl
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string slider GuiControl
	 */
	protected function render() {
		global $gui;
		
		$select_id = $this->getId();
		
		$html = '<form><select name="' . $select_id . '-slider" id="' . $select_id . '-slider"><option value="None">None</option><option value="Surface">Surface</option><option value="Minor" selected="selected">Minor</option><option value="Moderate">Moderate</option><option value="Major">Major</option></select></form>';
		
		$gui->add_jquery('$("#' . $select_id . '-slider").accessibleUISlider({width: 400, labels: 5});');
		$gui->add_jquery('$("#' . $select_id . '-slider").hide();');
		return $html;
	}
}