<?php
/**
* @category zoop
* @package sequence
*/
// Copyright (c) 2007 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.


include_once(dirname(__file__) . "/Storage.php");
include_once(dirname(__file__) . "/fileStorage.php");
include_once(dirname(__file__) . "/SqliteStorage.php");

class component_storage extends component
{
	function component_storage()
	{
		//$this->requireComponent('session');
	}

	function init()
	{
	}
}
?>
