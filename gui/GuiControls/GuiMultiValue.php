<?
/**
* Zoop Guicontrol
* @package gui
* @subpackage guicontrol
*
*/
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

include_once(zoop_dir . "/gui/plugins/function.html_checkboxes.php");

/**
 * GuiMultiValue
 * To be extended by controls that accept an array as values, ie select boxes with multiple=true
 * This attempts to handle the validation and viewing properly for such a guiControl.
 *
 * @uses GuiControl
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class GuiMultiValue extends GuiControl
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
					$errorState['text'] = "At least one field is required to be selected";
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
						$validate = array('message' => "You must select at least ". $this->params['validate']['min'] ." field(s).", 'result'=> false);
				}
				else
				{
					if ($this->params['validate']['min'] > 1)
						$validate = array('message' => "You must select at least ". $this->params['validate']['min'] ." fields.", 'result'=> false);
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
}
?>
