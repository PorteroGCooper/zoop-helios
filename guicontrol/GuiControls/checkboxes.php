<?php
/**
* Zoop Guicontrol
* @package gui
* @subpackage guicontrol
*
*/
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
 * checkboxesControl
 *
 * @uses GuiControl
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class checkboxesControl extends GuiMultiValue
{
	/**
	 * validate
	 *
	 * @access public
	 * @return void
	 */
	function validate()
	{
		if(isset($this->params['validate']))
		{
			$value = $this->getValue();

			if (isset($this->params['validate']['required']) && $this->params['validate']['required'] == true)
			{

				if (!$value)
				{
					$errorState['text'] = "At least one field is required to be checked";
					$errorState['value'] = $this->getValue();
					return $errorState;
				}
			}

			if (isset($this->params['validate']['min']) || isset($this->params['validate']['max']))
			{
				if (is_array($value))
				{
					$validate = Validator::validateQuantity($value, $this->params['validate']);
				}
				elseif (!$value)
				{
					if ($this->params['validate']['min'] > 0)
						$validate = array('message' => "You must check at least ". $this->params['validate']['min'] ." field(s).", 'result'=> false);
				}
				else
				{
					if ($this->params['validate']['min'] > 1)
						$validate = array('message' => "You must check at least ". $this->params['validate']['min'] ." fields.", 'result'=> false);
				}

				if (!$validate['result'])
				{
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
	function getValue()
	{
		if (isset($this->params['value']))
			return $this->params['value'];
		else
			return NULL;
	}

	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return array
	 */
	function getPersistentParams()
	{
		return array('validate');
	}

	/**
	 * view
	 *
	 * @access public
	 * @return string
	 */
	function view()
	{
		$value = $this->getValue();

		$html = "";
		isset($this->params['separator']) ? $separator = $this->params['separator'] : $separator = " ";

		if (is_array($value))
		{
			foreach ($value as $val)
			{
				if (isset($this->params['index'][$val]))
				{
					$label = $this->params['index'][$val];
					$html .= $label . $separator;
				}
			}
		}
		elseif (isset($this->params['index'][$value]))
		{
			$html = $this->params['index'][$value];
		}

		return $html;
	}

	/**
	 * render
	 *
	 * @access public
	 * @return string
	 */
	function render()
	{
		global $gui;

		$smartyParams = array('options' => $this->params['index']);

		foreach ($this->params as $parameter => $value)
		{
			switch ($parameter) {   // Here we setup specific parameters that will go into the html
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
			}
		}

		$value = $this->getValue();
		$value ? $checked = "checked" : $checked = "";
		$label = $this->getLabelName();

		$smartyParams['selected'] = $value;
		$smartyParams['name'] = $label;

		$html = smarty_function_html_checkboxes($smartyParams, &$gui);

		return $html;
	}
}
?>
