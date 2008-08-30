<?php
/**
* @category zoop
* @package spell
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
* @package spell
*/
class component_spell extends component
{
	function component_spell()
	{
		$this->requireComponent('db');
		$this->requireComponent('gui');
	}

	function getIncludes() {
		return array(
			'spellbase' => $this->getBasePath() . "/spellBase.php",
			'spell' => $this->getBasePath() . "/spell.php",
			'guispell' => $this->getBasePath() . "/guispell.php"
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

?>
