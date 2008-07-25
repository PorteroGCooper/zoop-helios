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

/**
 * checkbox
 *
 * @uses GuiControl
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class checkbox extends GuiControl
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

			if (isset($this->params['validate']['required']) && $this->params['validate']['required'] == true)
			{
				$value = $this->getValue();
				if (!$value)
				{
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
	function getValue()
	{
		if (isset($this->params['value']) && $this->params['value'])
		{

			return 1;
		}
		else
			return 0;
	}

	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return void
	 */
	function getPersistentParams()
	{
		return array('validate');
	}

	/**
	 * render
	 *
	 * @access public
	 * @return void
	 */
	function render()
	{
		if(!isset($this->params['type']))
			$this->params['type'] = 'checkbox';
		$attrs = array();
		$Sattrs = array();
		foreach ($this->params as $parameter => $value)
		{
			switch ($parameter) {   // Here we setup specific parameters that will go into the html
				case 'title':
				case 'size':
				case 'type':
					if ($value != '')
						$attrs[] = "$parameter=\"$value\"";
					break;
				case 'readonly':
				case 'disabled':
					if ($value)
						$attrs[] = "readonly=\"true\"";
				case 'width':
				case 'height':
					if ($value != '')
						$Sattrs[] = "$parameter:$value;";
					break;
			}
		}

		$value = $this->getValue();
		$value ? $checked = "checked" : $checked = "";

		$attrs[] = "style=\"" . implode(' ', $Sattrs) . "\"";
		$attrs = implode(' ', $attrs);
		$vc = $this->getValidationClasses();
		$ni = $this->getNameIdString();

		$html = "<input class=\"$vc\"  $ni $attrs $checked>";

		return $html;
	}
}
?>
