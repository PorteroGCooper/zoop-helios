<?
/**
* @category zoop
* @package db
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
class component_db extends component
{
	function init()
	{
		require_once('DB.php');
		include(dirname(__file__) . "/database.php");
		include(dirname(__file__) . "/" . db_RDBMS . ".php");
		include(dirname(__file__) . "/ComplexUpdate.php");
		include(dirname(__file__) . "/ComplexInsert.php");
	}
}
?>