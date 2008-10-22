<?php
/**
* Zoop Guicontrol
* @package gui
* @subpackage guicontrol
*
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

include_once(ZOOP_DIR . "/gui/plugins/function.loadmanager.php");

class filemanagerControl extends GuiControl
{
	function getPersistentParams()
	{
		return array('validate');
	}

	function render()
	{
		global $smarty;

		$this->params['name'] = $this->getName();
		$this->params['value'] = $this->getValue();

		$html = smarty_function_loadmanager($this->params, $smarty);

		return $html;
	}
}
?>