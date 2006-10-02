<?php
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

/**
 * date 
 * 
 * @uses GuiControl
 * @package 
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class date extends GuiControl
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
		$html = $this->renderViewState();
		$attrs = array();

		foreach ($this->params as $parameter => $value)
		{
			switch ($parameter) {   // Here we setup specific parameters that will go into the html
				case 'title':
				case 'maxlength':
				case 'width':
				case 'height':
				case 'size':
				case 'type':
					if ($value != '')
						$attrs[] = "$parameter=\"$value\"";
					break;
				case 'readonly':
					if ($value)
						$attrs[] = "readonly=\"true\"";
				case 'validate':
					$attrs[] = $this->getValidationAttr($this->params['validate']);
					break;
			}
		}

		$name = $this->getName();
		$value = $this->getValue();
		$attrs = implode(' ', $attrs);
		$label = $this->getLabelName();


		$html .= "<input name=\"{$label}\" $attrs value=\"$value\" id=\"{$label}\" onfocus=\"show_Calendar(id);\">"; // type=\"{$this->params['type']}\"
		$html .= "<img src=\"" . SCRIPT_URL . "/zoopfile/guicontrol/js/datechooser/cal2.gif\" onclick=\"toggle_Calendar('{$label}');\" style=\"cursor:pointer;\">";
		$html .= "<script src=\"" . SCRIPT_URL . "/zoopfile/guicontrol/js/datechooser/datechooser.js\"></script>"; 

		$html .= file_get_contents(zoop_dir . "/guicontrol/public/js/datechooser/cal_div.htm");

		if(isset($this->params['errorState']))
		{
			$errorState = $this->params['errorState'];
			$html .=" <span style=\"color: red;\">{$errorState['text']} {$errorState['value']}</span>";
		}

		return $html;
	}
}
?>
