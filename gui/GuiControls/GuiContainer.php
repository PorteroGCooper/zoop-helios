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
/**
* This class is meant to be extended when creating a guiControl that has children guiControls.
*
* @package gui
* @subpackage guicontrol
*
*/
class GuiContainer extends GuiControl
{
	function validate()
	{
		$valid = true;

		foreach ($this->params['controls'] as $type => $typeobj)
		{
			foreach ($typeobj as $control)
			{
				$validate = $control->validate();

				if ($validate !== true)
				{
					$varray[$control->name] = $validate;
					$control->setParam('errorState', $validate);
					$valid = false;
				}
			}
		}

		if ($valid === true)
			return $valid;
		else
			return array('text' => "This form failed validation, please examine and correct.", "value" => $varray);
	}
}
?>