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

class guispell extends gui
{
	var $title = "Spell Checker";
	/*
	var $showLogout = 1;
	var $showBack = 1;
	var $showNext = 1;
	*/

	function display($tpl_file)
	{

		$this->assign("title", $this->title);

		return Smarty::display($tpl_file);
	}
};
?>