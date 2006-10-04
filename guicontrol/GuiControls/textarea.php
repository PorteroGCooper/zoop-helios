<?php
/**
* @package gui
* @subpackage guicontrol
*/
// Copyright (c) 2006 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

class textarea extends GuiControl
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
			}
		}

		$attrs = implode(' ', $attrs);

		$vc = $this->getValidationClasses();
		$ni = $this->getNameIdString();
		$v = $this->getValue();

		$html = "<textarea class=\"$vc\" $ni  $attrs>$v</textarea>";

		return $html;
	}
}
?>