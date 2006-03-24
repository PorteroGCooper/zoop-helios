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


class text extends GuiControl
{

	function setValue($value)
	{
		$this->params['text'] = $value;
	}

	function getPersistentParams()
	{
		return array('validate');
	}

	function render()
	{
		$html = $this->renderViewState();
		$attrs = array();
		$Sattrs = array();

		foreach ($this->params as $parameter => $value)
		{
			switch ($parameter) {   // Here we setup specific parameters that will go into the html
				case 'title':
				case 'maxlength':
				case 'size':
				case 'type':
					if ($value != '')
						$attrs[] = "$parameter=\"$value\"";
					break;
				case 'readonly':
				case 'disabled':
					if ($value)
						$attrs[] = "readonly=\"true\"";
					break;
				case 'validate':
					$attrs[] = $this->getValidationAttr($this->params['validate']);
					break;
				case 'width':
				case 'height':
					if ($value != '')
						$Sattrs[] = "$parameter:$value;";
					break;
			}
		}

		$name = $this->getName();
		$value = $this->getValue();
		$attrs[] = "style=\"" . implode(' ', $Sattrs) . "\"";
		$attrs = implode(' ', $attrs);
		$label = $this->getLabelName();

		$html .= "<input name=\"{$label}\" id=\"{$label}\" $attrs value=\"$value\">";

		if(isset($this->params['errorState']))
		{
			$errorState = $this->params['errorState'];
			$html .=" <br><span style=\"color: red;\">The value \"{$errorState['value']}\" {$errorState['text']} </span>";
		}

		return $html;
	}
}
?>