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
 * Shuttle GuiControl
 *
 * 
 * @ingroup gui
 * @ingroup GuiControl
 * 
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Andy Nu <nuandy@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class ShuttleControl extends GuiMultiValue {
	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return void
	 */
	 
	function initControl() {
		global $gui;
		$gui->add_js('/zoopfile/gui/js/jquery.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/jquery.comboselect.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/jquery.selso.js', 'zoop');
		$gui->add_css('/zoopfile/gui/css/shuttle.css', 'zoop');
	}
	
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

		$html = '<select name="' . $name . '" id="' . $id . '" ' . $attrs . '>';
		$html .= smarty_function_html_options(array('options' => $this->params['index'], 'selected' => $this->getValue()), $gui);
		$html .= '</select>';

		$gui->add_jquery('$("#'.$id.'").comboselect({ sort: "both", addbtn: "Add >>",  rembtn: "<< Remove" });');
				
		return $html;
	}
}