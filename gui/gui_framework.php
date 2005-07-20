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

	require_once(dirname(__file__) . "/gui.php");
	include_once('GuiControls/GuiControl.php');

class framework_gui extends framework
{
	function framework_gui()
	{
		$this->requireFramework('session');
		$this->requireFramework('validate');
	}

	function init()
	{
		$GLOBALS['gui'] = new gui();
		initGuiControls();
	}
}
?>
