<?
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

include_once(zoop_dir . "/gui/plugins/function.html_options.php");

/**
 * select 
 * 
 * @uses GuiControl
 * @package 
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class select extends GuiControl
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

		$name = $this->getName();
		$value = $this->getValue();
		$attrs = implode(' ', $attrs);
		$label = $this->getLabelName();

		$html .=  "<select name=\"{$label}\" id=\"{$label}\" $attrs>\r" ;

		$html .= smarty_function_html_options(array('options' => $this->params['index'], 'selected' => $value), &$gui);

		$html .=  "</select>\r";

		if(isset($this->params['errorState']))
		{
			$errorState = $this->params['errorState'];
			$html .=" <span style=\"color: red;\">{$errorState['text']} {$errorState['value']}</span>";
		}

		return $html;
	}
}
?>
