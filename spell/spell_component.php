<?php
/**
* @category zoop
* @package spell
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


	include(dirname(__file__) . "/spellBase.php");
	include(dirname(__file__) . "/spell.php");
	include(dirname(__file__) . "/guispell.php");
/**
* @package spell
*/
class component_spell extends component
{
	function spell_component()
	{
		$this->requireComponent('db');
		$this->requireComponent('gui');
	}

	function init()
	{
		if(spell_DB_separate)
		{
			$GLOBALS['spelldsn'] = database::makeDSN(spellDB_RDBMS, spellDB_Server, spellDB_Port, spellDB_Username, spellDB_Password, spellDB_Database);
			$spelldb = &new database($GLOBALS['spelldsn']);
		}
		else
		{
			//$GLOBALS['spelldsn'] = $GLOBALS['defaultdsn'];
			$spelldb = &$defaultdb;
		}

	}
}

?>