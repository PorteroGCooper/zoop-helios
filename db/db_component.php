<?php
/**
* @category zoop
* @package db
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
 * component_db 
 * 
 * @uses component
 * @package 
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_db extends component
{
	/**
	 * init 
	 * 
	 * @access public
	 * @return void
	 */
	function init() {
		$config = Config::get('zoop.db');
		$rdbms = substr($config['dsn'], 0, strpos($config['dsn'], ":"));
		print_r($config['dsn']);
		echo($rdbms);
		include($this->getBasePath() . "/" . $config['rdbms']. ".php");
		include($this->getBasePath() . "/db_utils.php");
	}
	
	function getIncludes() {
		$includes = array();
		$config = Config::get('zoop.db');
		if(class_exists('PDO') && $config['use_pdo'] ) {
			$includes['database'] = $this->getBasePath() . '/PDO_database.php';
		} else	{
			$includes['db'] = 'DB.php';
			$includes['database'] =  $this->getBasePath() . "/database.php";
		}
		if(!version_compare(PHP_VERSION, '5.0', '<'))
			$includes['dbobject'] = $this->getBasePath() . '/dbobject.php';
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
	function run() {
		if (defined('sql_connect') && sql_connect)
			sql_connect();
	}
}
?>
