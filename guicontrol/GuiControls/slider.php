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
		
		// set up the options
		if (!isset($this->params['index'])) {
			if (!isset($this->params['validate']['max']) || !isset($this->params['validate']['max'])) {
				trigger_error('This GuiControl requires an index or min/max validation options.');
				return;
			}
			
			$index = array();
			$max = $this->params['validate']['max'];
			$min = $this->params['validate']['min'];
			
			for ($i = $min; $i <= $max; $i++) {
				$index[$i] = $i;
			}
			
			$this->params['index'] = $index;
		}
		
		$html = '<select name="' . $this->getName() . '" id="' . $select_id . '">';
		$html .= smarty_function_html_options(array('options' => $this->params['index'], 'selected' => $this->getValue()), $gui);
		$html .= '</select>';
		
		$options = array();
		$options['width'] = (isset($this->params['width'])) ? $this->params['width'] : 400;
		if (isset($this->params['labels'])) $options['labels'] = $this->params['labels'];
		
		$gui->add_jquery('$("#' . $select_id . '").hide().accessibleUISlider(' . json_encode($options) . ');');
/* 		$gui->add_jquery('$("#' . $select_id . '").hide();'); */
		return $html;
	}
}