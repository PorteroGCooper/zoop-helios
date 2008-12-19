<?php
/**
 * @ingroup forms
 * @ingroup components
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

/**
 * The Formz component.
 *
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Justin Hileman <justin@justinhileman.info> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}/
 */
class component_formz extends component {
	function component_formz() {
		$this->requireComponent('db');
		$this->requireComponent('doctrine');
		$this->requireComponent('gui');
		$this->requireComponent('guicontrol');
		$this->requireComponent('cache');
		$this->requireComponent('validate');
	}
	
	function getIncludes() {
		$base = $this->getBasePath();
		return array(
				"formz" => $base . "/formz.php",
				"formz_driver_interface" => $base . "/formz_driver.interface.php",
				"formz_doctrineDB" => $base . "/formz_doctrineDB.php",
				"formz_formDB" => $base . "/formz_formDB.php",
				"FormzField" => $base . "/FormzField.php",
				"FormzFieldCollection" => $base . "/FormzFieldCollection.php",
				
				"table" => $base . "/table.php",
				"record" => $base . "/record.php",
				"field" => $base . "/field.php",
				"cell" => $base . "/cell.php",
				"xml_serializer" => "XML/Serializer.php"
		);
	}
}


