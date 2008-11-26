<?php
/**
* @package gui
* @subpackage guicontrol
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

include_once(ZOOP_DIR . "/gui/plugins/function.html_options.php");

/**
 * selectControl
 *
 * @uses GuiControl
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class selectControl extends GuiMultiValue
{
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
		global $gui;
		if (!isset($this->params['index'])) {
			$this->params['index'] = array();
/* 			trigger_error ('you need to specify an index for this guiControl'); */
		}

		$attrs = array();

		foreach ($this->params as $parameter => $value)
		{
			switch ($parameter) {   // Here we setup specific parameters that will go into the html
				case 'title':
				case 'size':
				case 'onChange':
				case 'onBlur':
					if ($value != '')
						$attrs[] = "$parameter=\"$value\"";
					break;
				case 'readonly':
				case 'disabled':
					if ($value)
						$attrs[] = "disabled=\"true\"";
					break;
				case 'multiple':
					if ($value)
						$attrs[] = "multiple=\"true\"";
			}
		}

		$value = $this->getValue();
		$attrs = implode(' ', $attrs);
		$name_and_id = $this->getNameIdString();

		if (isset($this->params['multiple']) && $this->params['multiple'])
			$label .= "[]";

		$html =  "<select $name_and_id $attrs>\r" ;
		$html .= smarty_function_html_options(array('options' => $this->params['index'], 'selected' => $value), $gui);
		$html .=  "</select>\r";

		return $html;
	}
}
?>
