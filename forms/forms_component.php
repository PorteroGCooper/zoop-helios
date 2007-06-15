<?php
/**
* @category zoop
* @package forms
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

 /**
 * @package forms
 * @uses component
 * @version $id$
 * @copyright 1997-2007 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}/
 */
class component_forms extends component
{
	function getIncludes()
	{
		$file = $this->getBasePath();
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
	 
	function getRequiredComponents()
	{
		return array('db', 'gui', 'guicontrol', 'cache', 'validate');
	}
}
?>
