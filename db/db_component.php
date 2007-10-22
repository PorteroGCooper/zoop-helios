<?php
/**
* @category zoop
* @package db
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
 * component_db 
 * 
 * @uses component
 * @package 
 * @version $id$
 * @copyright 1997-2007 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class component_db extends component
{
	/**
	 * init 
	 * 
	 * @access public
	 * @return void
	 */
	function init()
	{
		include($this->getBasePath() . "/" . db_RDBMS . ".php");
		include($this->getBasePath() . "/db_utils.php");
	}
	
	function getIncludes()
	{
		$includes = array();
		if(class_exists('PDO'))
			$includes['database'] = $this->getBasePath() . '/PDO_database.php';
		else
		{
			$includes['db'] = 'DB.php';
			$includes['database'] =  $this->getBasePath() . "/database.php";
		}
		return $includes + array(
				"complexupdate" => $this->getBasePath() . "/ComplexUpdate.php",
				"complexinsert" => $this->getBasePath() . "/ComplexInsert.php"
				);
	}

	/**
	 * run 
	 * 
	 * @access public
	 * @return void
	 */
	function run()
	{
		if (defined('sql_connect') && sql_connect)
			sql_connect();
	}
}
?>
