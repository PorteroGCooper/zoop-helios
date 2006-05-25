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

class TextArea extends GuiControl
{
	function getPersistentParams()
	{
		return array('validate');
	}

	function render()
	{
		$html = $this->renderViewState();
		$attrs = array();

		foreach ($this->params as $parameter => $value)
		{
			switch ($parameter) {   // Here we setup specific parameters that will go into the html
				case 'title':
				case 'rows':
				case 'cols':
				case 'wrap':
					if ($value != '')
						$attrs[] = "$parameter=\"$value\"";
					break;
				case 'width': // alias for cols
					if ($value != '')
						$attrs[] = "cols='$value'";
					break;
				case 'height': // alias for rows
					if ($value != '')
						$attrs[] = "rows='$value'";
					break;
				case 'readonly':
				case 'disabled':
					if ($value)
						$attrs[] = "disabled=\"true\"";
					break;
				case 'validate':
					$attrs[] = $this->getValidationAttr($this->params['validate']);
					break;
			}
		}

		$name = $this->getName();
		$value = $this->getValue();
		$attrs = implode(' ', $attrs);
		$label = $this->getLabelName();

		$html .= "<textarea name=\"{$label}\" id=\"{$label}\" $attrs>$value</textarea>";

		if(isset($this->params['errorState']))
		{
			$errorState = $this->params['errorState'];
			$html .=" <span style=\"color: red;\">{$errorState['text']} {$errorState['value']}</span>";
		}

		return $html;
	}
}
?>