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
		$file = dirname(__file__);
		return array(
				"form2" => $file . "/forms2.php",
				"form" => $file . "/forms.php",
				"table" => $file . "/table.php",
				"record" => $file . "/record.php",
				"field" => $file . "/field.php",
				"cell" => $file . "/cell.php",
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
}
?>