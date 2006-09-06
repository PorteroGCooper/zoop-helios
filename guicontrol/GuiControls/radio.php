<?php
/**
* @package gui
* @subpackage guicontrol
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

include_once(zoop_dir . "/gui/plugins/function.html_radios.php");

/**
 * radio
 *
 * @uses GuiControl
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class radio extends GuiControl
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
	 * view
	 *
	 * @access public
	 * @return void
	 */
	function view()
	{
		$value = $this->getValue();

		if (isset($this->params['index'][$value]))
			return $this->params['index'][$value];
		else
			return $value;
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
		if (!isset($this->params['index']))
			return 'you need to specify an index for this guiControl';

		$html = $this->renderViewState();
		$attrs = array();


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
			}
		}

		$name = $this->getName();
		$value = $this->getValue();
		$attrs = implode(' ', $attrs);
		$label = $this->getLabelName();

		$smartyParams['selected'] = $value;
		$smartyParams['name'] = $label;

		$html .= smarty_function_html_radios($smartyParams, &$gui);

		if(isset($this->params['errorState']))
		{
			$errorState = $this->params['errorState'];
			$html .=" <span style=\"color: red;\">{$errorState['text']} {$errorState['value']}</span>";
		}

		return $html;
	}
}
?>
