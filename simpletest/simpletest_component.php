<?php
/**
* Main component file for component_simpletest
*
* Class for unit testing in Zoop
* @category zoop
* @package simpletest
* @subpackage component_simpletest
*/


//Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/**#@+
* include subpackages
*
*/
/**#@-*/
/**
* @package simpletest
*/
class component_simpletest extends component
{
	function init()
	{
		$thisFilePath = dirname(__file__);
		require_once($thisFilePath . '/simpletest/unit_tester.php');
		require_once($thisFilePath . '/simpletest/reporter.php');
		require_once($thisFilePath . '/simpletest/web_tester.php');
	}
}
