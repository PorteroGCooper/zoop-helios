<?
/**
* @category zoop
* @package forms
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

require_once(dirname(__file__) . "/forms.php");
/**
* @package forms
*/
class component_forms extends component
{
	function component_forms()
	{
		$this->requireComponent('db');
		$this->requireComponent('gui');
		$this->requireComponent('validate');
	}

	function init()
	{
	}
}
?>