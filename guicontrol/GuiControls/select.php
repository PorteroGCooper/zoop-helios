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

include_once(ZOOP_DIR . "/gui/plugins/function.html_options.php");

/**
 * HTML Select GuiControl
 *
 * The select GuiControl can be passed a 'null_label' parameter. This will prepend a null value to
 * the options list for the GuiControl. This label defaults to something like:
 *
 * "- Select %field% -"
 *
 * The %field% string is automatically replaced with the name of this GuiControl. If the user doesn't
 * select anything, this field will post as $field === ''... Validation should ensure that this field
 * posts as anything !== '' if this field is required.
 * 
 * @ingroup gui
 * @ingroup GuiControl
 * 
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class SelectControl extends GuiMultiValue {
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
	 * @return string HTML (multi)select box
	 */
	protected function render() {
		global $gui;
		if (!isset($this->params['index'])) {
			$this->params['index'] = array();
		}

		$attrs = array();
		foreach ($this->params as $parameter => $value) {
			// Here we setup specific parameters that will go into the html
			switch ($parameter) {
				case 'title':
				case 'size':
				case 'onChange':
				case 'onBlur':
					if (!empty($value)) $attrs[] = $parameter .'="'. $value .'"';
					break;
				case 'readonly':
				case 'disabled':
					if (!empty($value)) $attrs[] = 'disabled="disabled"';
					break;
				case 'multiple':
					if ($value) $attrs[] = 'multiple="true"';
					break;
				// The 'null_label' is a "- Select an Option -" style value at the top of a single select list.
				// this param should not be supplied on a multi-select list.
				case 'null_label':
					if (empty($value) || $value === true) {
						$value = str_replace('%field%', format_label($this->name), Config::get('zoop.gui.select_null_value'));
					}
					$this->params['index'] = array('' => $value) + $this->params['index'];
					break;
			}
		}

		$attrs = implode(' ', $attrs);
		$id = $this->getId();
		$name = $this->getLabelName();

		if (isset($this->params['multiple']) && $this->params['multiple']) $name .= "[]";

		$html =  '<select name="' . $name . '" id="' . $id . '" ' . $attrs . '>';
		$html .= smarty_function_html_options(array('options' => $this->params['index'], 'selected' => $this->getValue()), $gui);
		$html .=  "</select>";

		return $html;
	}
}