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
 * HTML checkbox GuiControl
 *
 * @ingroup gui
 * @ingroup GuiControl
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class CheckboxControl extends GuiControl {

	function initControl() {
		$this->type = 'checkbox';
	}

	/**
	 * validate
	 *
	 * @access public
	 * @return void
	 */
	function validate() {
		if(isset($this->params['validate'])) {

			if (isset($this->params['validate']['required']) && $this->params['validate']['required'] == true) {
				$value = $this->getValue();
				if (!$value) {
					$errorState['text'] = "This box must be checked";
					$errorState['value'] = $this->getValue();
					return $errorState;
				}
			}
		}
		return true;
	}

	/**
	 * getValue
	 *
	 * @access public
	 * @return void
	 */
	function getValue() {
		if (isset($this->params['value']) && $this->params['value']) {
			return 1;
		} else {
			return 0;
		}
	}

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
	 * Render GuiControl
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string HTML checkbox
	 */
	protected function render() {
		$type = (isset($this->params['type'])) ? $this->params['type'] : $this->getType();
		$type = 'type="' . $type . '"';
	
		$name_id = $this->getNameIdString();
		$class = 'class="' . $this->getClass() .'"';
		$attrs = $this->renderHTMLAttrs();
		$checked = $this->getValue() ? 'checked="true"' : '';

		$html = "<input $type $name_id $class $attrs $checked />";

		return $html;
	}
}
