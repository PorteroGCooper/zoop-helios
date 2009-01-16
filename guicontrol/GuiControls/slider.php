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
 * @ingroup gui
 * @ingroup GuiControl
 * @author Andy Nu <nuandy@gmail.com>
 * @author Justin Hileman {@link http://justinhileman.com}
 * @extends SelectControl
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
	
	/**
	 * Render slider GuiControl. Essentially, add some jQuery to magically convert it to a slider,
	 * then render parent (a regular select).
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string slider GuiControl
	 */
	protected function render() {
		global $gui;
		
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
		
		$options = array();
		$options['width'] = (isset($this->params['width'])) ? $this->params['width'] : 400;
		if (isset($this->params['labels'])) {
			$options['labels'] = $this->params['labels'];
		}
		
		$gui->add_jquery('$("#' . $this->getId() . '").hide().accessibleUISlider(' . json_encode($options) . ');');
		
		return parent::render();
	}
}