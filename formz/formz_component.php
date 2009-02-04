<?php

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
 * @group Formz
 * @endgroup
 * 
 * @ingroup components
 * @ingroup forms
 *
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Justin Hileman <justin@justinhileman.info> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}/
 */
class Component_Formz extends Component {
	
	function __construct() {
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
			"Formz"                => $base . "/Formz.php",
			
			"FormzField"           => $base . "/FormzField.php",
			"FormzFieldCollection" => $base . "/FormzFieldCollection.php",
			
			"FormzDriver"          => $base . "/FormzDriver.php",
			"FormzDriver_Doctrine" => $base . "/FormzDriver_Doctrine.php",
			"FormzDriver_FormDB"   => $base . "/FormzDriver_FormDB.php",
			
			"table"                => $base . "/table.php",
			"record"               => $base . "/record.php",
			"field"                => $base . "/field.php",
			"cell"                 => $base . "/cell.php",
			"xml_serializer"       => "XML/Serializer.php"
		);
	}
}