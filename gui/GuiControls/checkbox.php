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

class checkbox extends GuiControl
{
	function validate()
	{
		if(isset($this->params['validate']))
		{

			if (isset($this->params['validate']['required']) && $this->params['validate']['required'] == true)
			{
				$value = $this->getValue();
				if (!$value)
				{
					$errorState['text'] = "This field is required to be checked";
					$errorState['value'] = $this->getValue();
					return $errorState;
				}
			}
		}
		return true;
	}

	function getValue()
	{
		if (isset($this->params['value']) && $this->params['value'] != 0)
			return 1;
		else
			return 0;
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

		$name = $this->getName();
		$value = $this->getValue();
		$value ? $checked = "checked" : $checked = "";
		$attrs[] = "style=\"" . implode(' ', $Sattrs) . "\"";
		$attrs = implode(' ', $attrs);
		$label = $this->getLabelName();

		$html .= "<input name=\"{$label}\" $attrs $checked>";

		if(isset($this->params['errorState']))
		{
			$errorState = $this->params['errorState'];
			$html .=" <span style=\"color: red;\">{$errorState['text']} {$errorState['value']}</span>";
		}

		return $html;
	}
}
?>