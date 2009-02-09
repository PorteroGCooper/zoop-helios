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
 * component_spell class.
 * 
 * @ingroup components
 * @extends component
 */
class component_spell extends component {
	function __construct() {
		$this->requireComponent('db');
		$this->requireComponent('gui');
	}

	function getIncludes() {
		$base = $this->getBasePath();
		return array(
			'spellbase' => $base . "/spellBase.php",
			'spell'     => $base . "/spell.php",
			'guispell'  => $base . "/guispell.php"
		);
	}	

	function init() {
		$config = $this->getConfig();	
		if($config['seperate_db']) {
			$spelldb = &new database($config['dsn']);
		} else {
			$spelldb = &$defaultdb;
		}

	}
}