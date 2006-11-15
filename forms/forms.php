<?php
/**
* @package forms

// Copyright (c) 2006 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.
*/

/**
 * form
 *
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class form
{
	/**
	 * tables
	 *
	 * @var mixed
	 * @access public
	 */
	var $tables;
	/**
	 * db
	 *
	 * @var mixed
	 * @access public
	 */
	var $db;
	/**
	 * dbconnname
	 *
	 * @var mixed
	 * @access public
	 */
	var $dbconnname;

	/**
	 * sql_connect
	 *
	 * @param mixed $dbconnname
	 * @access public
	 * @return void
	 */
	function sql_connect($dbconnname)
	{
		if (!isset($GLOBALS[$dbconnname]) && $dbconnname == 'defaultdb')
			$GLOBALS[$dbconnname] = &new database(database::makeDSN(db_RDBMS, db_Server, db_Port, db_Username, db_Password, db_Database));
		elseif (!isset($GLOBALS[$dbconnname]))
			trigger_error("You should have established a connection prior to this call if you are not using the defaultdb");
	}

	/**
	 * initTable
	 *
	 * @param mixed $table
	 * @param string $dbconnname
	 * @access public
	 * @return void
	 */
	function initTable($table, $dbconnname = 'defaultdb')
	{
		$this->sql_connect($dbconnname);
		global $$dbconnname;

		$this->dbconnname = $dbconnname;

		if (!isset($this->tables->$table))
			$this->tables->$table = new table($table, $dbconnname);
 		else
 			$this->tables->$table->setDbconnname($dbconnname);
	}

	/**
	 * passIdfield
	 *
	 * @param mixed $table
	 * @access public
	 * @return void
	 */
	function passIdfield($table)
	{
		if (!isset($this->tables->$table))
			initTable($table, $this->dbconnname);

		return $this->tables->$table->idfield;
	}

	/**
	 * grabRecord
	 *
	 * @param mixed $table
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function grabRecord($table,  $id)
	{
		if (isset($this->tables->$table->records->$id))
			return;

		$this->initTable($table, $this->dbconnname);


		$idfield = $this->passIdfield($table);

		$this->tables->$table->records[$id] = new record ($table, $id, $idfield, $this->dbconnname);
	}

	/**
	 * &passRecord
	 *
	 * @param mixed $table
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function &passRecord($table, $id)
	{
		if (!isset($this->tables->$table->records[$id]))
				$this->grabRecord($table, $id);
		$this->tables->$table->records[$id]->order = &$this->tables->$table->order;

		$var =& $this->tables->$table->records[$id];

		return $var;
	}

	/**
	 * deleteRecord
	 *
	 * @param mixed $table
	 * @param mixed $id
	 * @param mixed $type
	 * @access public
	 * @return void
	 */
	function deleteRecord ($table, $id, $type = false)
	{
		$dbconnname = $this->dbconnname;
		global $$dbconnname;

		$idfield = $this->passIdfield($table);

		if (isset($this->tables->$table->deletedfield))
		{
			$deletedfield = $this->tables->$table->deletedfield;
			$type = 1;
		}

		if ($type == 0) # This means actually Delete the record
			$query = "DELETE FROM $table WHERE $idfield = $id";

		if ($type == 1) # this means set deleted flag to 1
			$query = "UPDATE $table set $deletedfield = 1 where $idfield = $id";

		$$dbconnname->query($query);
	}

	/**
	 * DescIntoFields
	 *
	 * @param mixed $table
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function DescIntoFields($table, $id)
	{
		if (isset($this->tables->$table->records[$id]->values) && is_array($this->tables->$table->records[$id]->values))
		{
			while(list ($key,$cell) = each($this->tables->$table->records[$id]->values))
			{
				$fieldname = $cell->name;
				$this->tables->$table->records[$id]->values[$fieldname]->description =& $this->tables->$table->fields[$fieldname];
			}
		}
		else
		{
			echo_r("this record does not exist"); die();
		}
	}

	/**
	 * saveRecord
	 *
	 * @param mixed $post
	 * @access public
	 * @return void
	 */
	function saveRecord($post)
	{
		$dbconnname = $this->dbconnname;

		$this->sql_connect($dbconnname);

		$this->setValuesFromPost($post);
		return $this->storeRecord($post["recordtable"], $post["recordid"]);
	}

	/**
	 * setValuesFromPost
	 *
	 * @param mixed $post
	 * @access public
	 * @return void
	 */
	function setValuesFromPost($post)
	{
		$table = $post["recordtable"];
		$id = $post["recordid"];

		foreach($post as $field => $value)
		{
			if (isset($this->tables->$table->records[$id]->values[$field]))
			{
				if (is_array($value))
				{

					if ($this->tables->$table->fields[$field]->datatype == "numeric")
					{
						$setval = 0;
						foreach ($value as $val)
						{
							$setval = $setval | $val;
						}
						$this->tables->$table->records[$id]->values[$field]->value = $setval;
					}
					else
						$this->tables->$table->records[$id]->values[$field]->value = implode(",", $value);
				}
				else
					$this->tables->$table->records[$id]->values[$field]->value = $value;
			}
		}
	}

	/**
	 * storeRecord
	 *
	 * @param mixed $table
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function storeRecord($table, $id)
	{
		$dbconnname = $this->dbconnname;

		$this->sql_connect($dbconnname);

		global $$dbconnname;

		$record =& $this->tables->$table->records[$id];

		$idfield = $this->tables->$table->idfield;

		if ($id == "new" && $this->tables->$table->fields[$idfield]->autoincrement)
			unset($record->values[$idfield]); // only unset this if the table is set to autoincrement

		if ($$dbconnname->db->phptype == "mysql" || $$dbconnname->db->phptype == "mysqli")
			$colquote = '`';
		else
			$colquote = '"';

		$record->error = false;

		if ($id == "new")
		{
			$columnstring = "";
			$valuestring = "";
			foreach ($record->values as $field)
			{
				if ($field->value)
				{
					$columnstring .= $colquote . $field->name . $colquote . ",";
					$value = "'" . $this->escapeValue($field->value) . "'";
					$valuestring .= "$value,";
				}
			}
			$columnstring = substr($columnstring, 0, -1);
			$valuestring = substr($valuestring, 0, -1);
			$query = "INSERT INTO $table ($columnstring) VALUES ($valuestring)";

			if (isset($this->tables->$table->sequence) && $this->tables->$table->sequence)
			{
				$$dbconnname->insert($query);
				return $$dbconnname->fetch_one_cell("SELECT currval('\"{$this->tables->$table->sequence}\"'::text)");
			}
			else
			{
				$$dbconnname->insert($query);
				return $$dbconnname->fetch_one_cell('select last_insert_id()');
			}
		}
		else
		{
			$setpart = "";
			foreach($record->values as $field){
				if ($field->value == null)
					$setpart .= $colquote . $field->name . $colquote  . "= null,";
				else
					$setpart .= $colquote . $field->name . $colquote  . "='" . $this->escapeValue($field->value) ."',";
			}

			$setpart = substr($setpart, 0, -1);
			$returnid = $id;

			$id = $$dbconnname->db->quoteSmart($id);

			$query = "UPDATE $table set $setpart where $idfield = $id";

			$$dbconnname->query($query);
			return $returnid;
		}
	}

	function escapeValue($value)
	{
		if(!ini_get('magic_quotes_sybase'))
			$value = preg_replace('/(\'|\\\')/', "\\\\'", $value);
		return $value;
	}
 }
?>
