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

class openwysiwygControl extends GuiControl
{
	function getPersistentParams()
	{
		return array('validate');
	}

	function render()
	{
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
				case 'width':
				case 'height':
					if ($value != '')
						$Sattrs[] = "$parameter: {$value}px;";
					break;
				case 'readonly':
				case 'disabled':
					if ($value)
						$attrs[] = "disabled=\"true\"";
					break;
			}
		}

		if (isset($Sattrs))
			$attrs[] = "style=\"" . implode(" ", $Sattrs) . "\"";

		$attrs = implode(' ', $attrs);

		$vc = $this->getValidationClasses();
		$ni = $this->getNameIdString();
		$v = $this->getValue();
		$label = $this->getLabelName();

		$html = "<textarea class=\"$vc\"  $ni $attrs>$v</textarea>";

		$html .= "
				<script language=\"javascript1.2\">
				generate_wysiwyg('$label');
				</script>
				";

		return $html;
	}
}
?>