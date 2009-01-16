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

include_once(ZOOP_DIR . "/gui/plugins/function.html_checkboxes.php");

/**
 * HTML Checkboxes GuiControl
 *
 * The Checkboxes GuiControl accepts a 'checkall' param. If this is set to true, a jQuery
 * 'Check All' checkbox will be dynamically prepended on page load.
 *
 * @code
 *    $foo = GuiControl::get('checkboxes', 'foo')
 *       ->setParam('checkall', true)
 *       ->setParam('index', $index);
 * @endcode
 *
 * @ingroup gui
 * @ingroup guicontrol
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class CheckboxesControl extends GuiMultiValue {
	
	function initControl() {
		global $gui;
		$gui->add_jquery();
	}
	
	/**
	 * validate
	 *
	 * @access public
	 * @return void
	 */
	function validate() {
		if(isset($this->params['validate'])) {
			$value = $this->getValue();

			if (isset($this->params['validate']['required']) && $this->params['validate']['required'] == true) {

				if (!$value) {
					$errorState['text'] = "At least one field is required to be checked";
					$errorState['value'] = $this->getValue();
					return $errorState;
				}
			}

			if (isset($this->params['validate']['min']) || isset($this->params['validate']['max'])) {
				if (is_array($value)) {
					$validate = Validator::validateQuantity($value, $this->params['validate']);
				} elseif (!$value) {
					if ($this->params['validate']['min'] > 0)
						$validate = array('message' => "You must check at least ". $this->params['validate']['min'] ." field(s).", 'result'=> false);
				} else {
					if ($this->params['validate']['min'] > 1)
						$validate = array('message' => "You must check at least ". $this->params['validate']['min'] ." fields.", 'result'=> false);
				}

				if (!$validate['result']) {
					$errorState['text'] = $validate['message'];
					$errorState['value'] = "";
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
		if (isset($this->params['value'])) {
			return $this->params['value'];
		} else {
			return NULL;
		}
	}

	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return array
	 */
	function getPersistentParams() {
		return array('validate');
	}

	/**
	 * Render GuiControl
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string HTML checkboxes
	 */
	protected function render() {
		global $gui;

		$smartyParams = array('options' => $this->params['index']);
		$input_id = $this->getId();
		$class = array('checkboxes');
		
		foreach ($this->params as $parameter => $value) {
			switch ($parameter) {
				case 'class':
					if (!is_array($value)) $value = explode(' ', $value);
					$class = array_merge($class, $value);
					break;
				case 'title':
					if ($value != '')
						$smartyParams[$parameter] = "$value";
					break;
				case 'readonly':
				case 'disabled':
					if ($value)
						$smartyParams['disabled']="true";
					break;
				case 'separator':
					$smartyParams['separator'] = $value;
					break;
				case 'onClick':
					$smartyParams['onClick'] = $value;
					break;
				case 'checkall':
					$class[] = 'checkable';
					$gui->add_jquery('
						$("#'.$input_id.'").prepend("<label class=\"check-all\"><input type=\"checkbox\" class=\"check-all\" />Check All</label>");
						$("#'.$input_id.' input.check-all").change(function(){
							$("#'.$input_id.' input[@type=\'checkbox\']").attr("checked",this.checked).change(function(){
								if(!this.checked) {$("#'.$input_id.' input.check-all").attr("checked",false);}
							});
						});
					');
					break;
			}
		}

		$smartyParams['selected'] = $this->getValue();
		$smartyParams['name'] = $this->getLabelName();
		
		$html = '<div id="' . $this->getId() . '" class="' . implode(' ', $class) . '">';
		$html .= smarty_function_html_checkboxes($smartyParams, $gui);
		$html .= '</div>';

		return $html;
	}

}