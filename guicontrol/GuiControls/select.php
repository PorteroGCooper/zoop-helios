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
 * @section Null Value
 *
 * A 'null value' will be automatically prepended to select boxes based on a sane set of rules:
 * 
 * If this is a non-required multiselect, <none> will be shown at the top of the list unless
 * it's overridden on a config-level or individual basis.
 * 
 * If this is a required single select, '- Select Fieldname -' will be shown at the top of the list
 * unless it's overridden on a config-level or individual basis.
 *
 * @code
 *    // The following will force a specific null value to be shown.
 *    $myControl->setParam('null_value', 'Select None');
 *    
 *    // The following will suppress the null value from being automatically added.
 *    $otherControl->setParam('null_value', false);
 * @endcode
 *
 * The null value can also be overridden at the application level by setting the 'zoop.guicontrol.multiselect_null_value'
 * and 'zoop.gui.select_null_value' config parameters:
 * 
 * @code
 *    zoop:
 *      guicontrol:
 *        select_null_value: '- Select a %field% -'
 *        multiselect_null_value: 'Select None'
 * @endcode
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
 * @author Justin Hileman {@link http://justinhileman.com}
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
	 * Return true if this is a multiselect field.
	 * 
	 * @access public
	 * @return bool
	 */
	public function isMultiple() {
		return (isset($this->params['multiple']) && $this->params['multiple']);
	}

	/**
	 * Render an HTML select or multi-select field.
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
		
		// handle prepending a null value.
		$null_label = false;
		if ($this->isMultiple()) {
			// handle the 'null_value' on a multiple select
			
			if (isset($this->params['null_label'])) {
				if ($this->isRequired() || !empty($this->params['null_label'])) {
					$null_label = $this->params['null_label'];
				}
			} else if (!$this->isRequired()) {
				$null_label = true;
			}
			
			if ($null_label === true) {
				$null_label = str_replace('%field%', format_label($this->name), Config::get('zoop.guicontrol.multiselect_null_value'));
			}
		} else {
			// handle the 'null_value' on a single select
			
			if (isset($this->params['null_label'])) {
				if (!$this->isRequired() || !empty($this->params['null_label'])) {
					$null_label = $this->params['null_label'];
				}
			} else if ($this->isRequired()) {
				$null_label = true;
			}
			
			if ($null_label === true) {
				$null_label = str_replace('%field%', format_label($this->name), Config::get('zoop.guicontrol.select_null_value'));
			}
		}
		
		if ($null_label) {
			$this->params['index'] = array('' => $null_label) + $this->params['index'];
		}
		
		$name_id = $this->getNameIdString();
		$class = 'class="' . $this->getClass() . '"';
		$attrs = $this->renderHTMLAttrs();

		$html =  "<select $name_id $class $attrs>";
		$html .= $this->renderOptions();
		$html .= "</select>";

		return $html;
	}
	
	function getHTMLAttrs() {
		$attrs = parent::getHTMLAttrs();
		if ($multiple = $this->getParam('multiple')) $attrs[] = 'multiple="true"';
		return $attrs;
	}
	
	function getLabelName() {
		$name = parent::getLabelName();
		if ($this->isMultiple()) $name .= "[]";
		return $name;
	}
	
	function renderOptions() {
		global $gui;
		if (is_array($this->getParam('disabled'))) {
			return smarty_function_html_options(array(
				'options' => $this->params['index'],
				'selected' => $this->getValue(),
				'disabled' => $this->getParam('disabled')
			), $gui);
		} else {
			return smarty_function_html_options(array('options' => $this->params['index'], 'selected' => $this->getValue()), $gui);
		}
		
	}
}