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

class record
{
	var $id;
	var $dbconnname;
	var $table;
	var $idfield;
	var $tableref;
	var $values;
	var $order;
	var $error = false;
	var $submit = "Update";


	function record($table, $id, $idfield, $dbconnname, $array = "")
	{
		global $$dbconnname;

		$this->id = $id;
		$this->table = $table;
		$this->idfield = $idfield;
		$this->dbconnname = $dbconnname;

		$dbname = $$dbconnname->dsn['database'];

		$cacheoptions = array(
			'readControl' => false,
			'automaticSerialization' => true,
			'cacheDir' => app_temp_dir . '/cache/forms/table_info',
			'lifeTime' => NULL
		);

		if ($this->id == "new")
		{
			$cl = new Cache_Lite($cacheoptions);

			if (app_status == 'live' && $rows = $cl->get($table, $dbname))
			{
				$rows = $rows;
			}
			else
			{
				$rows = $$dbconnname->get_table_info($table);
				$cl->save($rows, $table, $dbname);
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