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

include_once(ZOOP_DIR . "/gui/plugins/function.html_radios.php");

/**
 * Radio buttons GuiControl
 *
 * @ingroup gui
 * @ingroup GuiControl
 *
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class RadioControl extends GuiControl {

	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return void
	 */
	function getPersistentParams() {
		return array('validate');
	}

	/**
	 * view
	 *
	 * @access public
	 * @return void
	 */
	function view() {
		$value = $this->getValue();

		if (isset($this->params['index'][$value])) {
			return $this->params['index'][$value];
		} else {
			return $value;
		}
	}

	/**
	 * Render GuiControl
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string Radio buttons
	 */
	protected function render() {
		if (!isset($this->params['index'])) {
			trigger_error('An index must be specified for radio button GuiControls.');
			return
		}
		
		global $gui;

		$smartyParams = array('options' => $this->params['index']);

		foreach ($this->params as $parameter => $value) {
			switch ($parameter) {   // Here we setup specific parameters that will go into the html
				case 'title':
					if ($value != '')
						$smartyParams[$parameter] = "$value";
					break;
				case 'readonly':
				case 'disabled':
					if ($value)
						$smartyParams['disabled'] = "true";
					break;
				case 'separator':
					$smartyParams['separator'] = $value;
					break;
			}
		}

		$smartyParams['selected'] = $this->getValue();
		$smartyParams['name'] = $this->getLabelName();

		$html = smarty_function_html_radios($smartyParams, $gui);

		return $html;
	}
}