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

class text extends GuiControl
{
/*  Here is an example of creating a specific validation routine for a guicontrol,
	though a general use one has already been created that is quite good, you
	may need to write a specific one for a specific need.
********************************
	function validate()
	{
//		die("textvalidate");
		$errorState = parent::validate();
//		echo_r($errorState);
//		die();
		if($errorState !== true)
		{
			$errorState['text'] = 'Invalid';
			$errorState['value'] = $this->getValue();
		}
		return $errorState;
	}
*/

	function setValue($value)
	{
		$this->params['text'] = $value;
	}

	function getLabelName()
	{
		$label = $this->getName() . "[text]";
		return $label;
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
					if ($value)
						$attrs[] = "readonly=\"true\"";
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

		$html .= "<input name=\"{$label}\" id=\"{$label}\" $attrs value=\"$value\">"; // type=\"{$this->params['type']}\"

// 		echo_r($this->params);
		if(isset($this->params['errorState']))
		{
			$errorState = $this->params['errorState'];
			$html .=" <span style=\"color: red;\">{$errorState['text']} {$errorState['value']}</span>";
		}

		return $html;
	}
}
?>