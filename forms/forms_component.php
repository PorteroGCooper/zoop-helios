<?php
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

//require(dirname(__file__) . "/forms2.php");
//require(dirname(__file__) . "/forms.php");
//require(dirname(__file__) . "/table.php");
//require(dirname(__file__) . "/record.php");
//require(dirname(__file__) . "/field.php");
//require(dirname(__file__) . "/cell.php");

// require_once('Cache/Lite.php');
//require_once("XML/Serializer.php");


/**
* @package forms
*/
class component_forms extends component
{
	/**
	 * component_forms 
	 * 
	 * @access public
	 * @return void
	 */
	 
	function getIncludes()
	{
		return array(
				"form2" => dirname(__file__) . "/forms2.php",
				"form" => dirname(__file__) . "/forms.php",
				"table" => dirname(__file__) . "/table.php",
				"record" => dirname(__file__) . "/record.php",
				"field" => dirname(__file__) . "/field.php",
				"cell" => dirname(__file__) . "/cell.php",
				"xml_serializer" => "XML/Serializer.php"
		);
	}
	 
	function component_forms()
	{
		$this->requireComponent('db');
		$this->requireComponent('gui');
		$this->requireComponent('guicontrol');
		$this->requireComponent('cache');
		$this->requireComponent('validate');
	}

	/**
	 * init 
	 * 
	 * @access public
	 * @return void
	 */
// 	function init()
// 	{
// 		mkdirr(app_temp_dir . '/cache/forms/', 0770);
// 	}
}
?>
