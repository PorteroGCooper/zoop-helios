<?
/**
* @category zoop
* @package forms
*/
// Copyright (c) 2006 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/**
 * record
 *
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class record
{
	/**
	 * id
	 *
	 * @var mixed
	 * @access public
	 */
	var $id;
	/**
	 * dbconnname
	 *
	 * @var mixed
	 * @access public
	 */
	var $dbconnname;
	/**
	 * table
	 *
	 * @var mixed
	 * @access public
	 */
	var $table;
	/**
	 * idfield
	 *
	 * @var mixed
	 * @access public
	 */
	var $idfield;
	/**
	 * tableref
	 *
	 * @var mixed
	 * @access public
	 */
	var $tableref;
	/**
	 * values
	 *
	 * @var mixed
	 * @access public
	 */
	var $values;
	/**
	 * order
	 *
	 * @var mixed
	 * @access public
	 */
	var $order;
	/**
	 * error
	 *
	 * @var mixed
	 * @access public
	 */
	var $error = false;
	/**
	 * submit
	 *
	 * @var string
	 * @access public
	 */
	var $submit = "Update";


	/**
	 * record
	 *
	 * @param mixed $table
	 * @param mixed $id
	 * @param mixed $idfield
	 * @param mixed $dbconnname
	 * @param string $array
	 * @access public
	 * @return void
	 */
	function record($table, $id, $idfield, $dbconnname, $array = "")
	{
		global $$dbconnname;

		$this->id = $id;
		$this->table = $table;
		$this->idfield = $idfield;
		$this->dbconnname = $dbconnname;

		$dbname = $$dbconnname->dsn['database'];

		if ($this->id == "new")
		{
			if (app_status == 'live' && $rows = zcache::getData($table, array('base'=> 'forms/table_info/', 'group' => $dbname))) 
			{
				$rows = $rows;
			}
			else
			{
				$rows = $$dbconnname->get_table_info($table);
				zcache::cacheData($table, $rows, array('base'=> 'forms/table_info/', 'group' => $dbname)); 
			}
			foreach ($rows as $row)
			{
				$name = $row["name"];
				$cell = new cell($name, NULL);
				$this->values[$name] = $cell;
			}
		$this->submit = "Add";
		}
		else
		{
			if (!$array)
			{
				// STILL NEED TO WRITE DATABASE INDEPENDENT
				$Query = "SELECT * FROM $table WHERE $idfield = '$id'";

				$row = $$dbconnname->fetch_one($Query);

				foreach( $row as $key => $value)
				{
					$cell = new cell($key, $value);
					$this->values[$key] = $cell;
				}
			}
			else
			{
				foreach( $array as $key => $value)
				{
					$cell = new cell($key, $value);
					$this->values[$key] = $cell;
				}
			}
		}
	}
// // WHY DO WE HAVE THIS
// 	function setDb(&$db)
// 	{
// 		$$dbconnname = &$db;
// 	}

	/**
	 * xmlExport
	 *
	 * @access public
	 * @return void
	 */
	function xmlExport()
	{
		if (substr($this->idfield, -3) == "_id")
			$root = substr($this->idfield, 0, -3);
		elseif (substr($this->table, -1) == "s")
			$root = substr($this->table, 0, -1);
		else
			$root = $this->table;

		$valarray = array();

		foreach ($this->values as $value)
		{
// 				if (!is_array($value->value) && strpos($value->value,">") > 0)
// 					$valarray[$value->name] = "<![CDATA[$value->value]]>";
// 				else
					$valarray[$value->name] = $value->value;
		}

		if (!isset($serializer))
			$serializer = new XML_Serializer();

		// perform serialization
		$serializer->setOption("indent", "     ");
		$serializer->setOption("rootName", $root);
//		$serializer->setOption("replaceEntities",XML_UTIL_ENTITIES_NONE);
		$serializer->setOption("replaceEntities",XML_UTIL_CDATA_SECTION);
		$result = $serializer->serialize($valarray);

		// check result code and display XML if success

		if($result === true)
			$XML = $serializer->getSerializedData();

	return $XML;
	}
}
?>