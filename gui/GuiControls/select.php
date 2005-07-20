<?
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

class select extends GuiControl
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
		if (!isset($this->params['index']))
			return 'you need to specify an index for this guiControl';

		$html = $this->renderViewState();
		$attrs = array();

		foreach ($this->params as $parameter => $value)
		{
			switch ($parameter) {   // Here we setup specific parameters that will go into the html
				case 'title':
				case 'size':
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
			/*	case 'validate':    // CAN'T PERFORM JS VALIDATION ON SELECTS
					$attrs[] = $this->getValidationAttr($this->params['validate']);
					break; */
			}
		}

		$name = $this->getName();
		$value = $this->getValue();
		$attrs = implode(' ', $attrs);
		$label = $this->getLabelName();

		$html .=  "<select name=\"{$label}\" $attrs>\r" ;
		foreach ($this->params['index'] as $pval => $label)
		{
			$pval == $value ? $selected = " selected " : $selected = " ";
			$html .=  "<option value=\"$pval\" label=\"$label\" $selected>$label</option>\r" ;
		}
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