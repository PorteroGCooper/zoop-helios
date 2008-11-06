<?php
/**
 * @group forms
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
 * formDB is a forms2 based database connection for Formz.
 *
 * @version $id$
 * @author Justin Hileman <justin@justinhileman.info>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 * @copyright 1997-2008 Supernerd LLC
 */
class Formz_FormDB implements formz_driver_interface {

	/**
	 * Name of current table in the database
	 *
	 * @var string
	 * @access public
	 */
	var $tablename;
	/**
	 * Reference to the table object
	 *
	 * @var object
	 * @access public
	 */
	var $table;
	/**
	 * Reference to the form object
	 *
	 * @var object
	 * @access public
	 */
	var $form;
	
	/**
	 * Reference to the record object
	 *
	 * @var object
	 * @access public
	 */
	var $record;
	
	/**
	 * Id of the current record
	 *
	 * @var mixed
	 * @access public
	 */
	var $id = false;
	
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
	 * True if current form is timestampable
	 *
	 * @var bool
	 * @access private
	 */
	var $timestampable = false;
	
	/**
	 * True if current form is soft deletable
	 *
	 * @var bool
	 * @access private
	 */
	var $softdeletable = false;
	
	
	
	
	/**
	 * FormDB constructor.
	 *
	 * Can be used to instantiate the object, or if passed a type, handle the retrival of information from the database
	 *
	 * @param string $tablename  table name in the database
	 * @param string $type  OPTIONAL, can be 'list' or 'record'
	 * @param mixed  $int  OPTIONAL, if $type = 'list' than an int that represents the limit,
	 * if $type = 'record' then required and is the id of the record
	 * @return NULL
	 * @access public
	 */
	function Formz_FormDB($tablename) {
		$this->tablename = strtolower($tablename);
		$this->initTable($this->tablename);
/*
		$this->table = &$this->tables->$tablename;
		$this->table->setupenv($_GET);

*/
	}
	
	
		
	/**
	 * Return the name of the id field for this table.
	 *
	 * @access public
	 * @return string ID field name
	 */
	function getIdField() {
		return $this->table->idfield;
	}
	
	/**
	 * Return an array of fields in this class/table/form.
	 *
	 * @access public
	 * @return array Field data
	 */
	function getFields() {
/* 		return array_keys($this->table->fields); */
		return $this->fields;
	}
	
	/**
	 * Return all data associated with this form.
	 *
	 * @access public
	 * @return array An array of form field values for the record or records.
	 */
	function getData() {
		if (isset($this->record) && is_object($this->record)) {
			$ret = array();
			foreach ($this->record->values as $key => $field) {
				$ret[$key] = $field->value;
			}
			return $ret;
		}
		else {
			$this->table->getRecords();
			return $this->table->records;
		}
	}
	
	
	
	/**
	 * Get all db relations.
	 *
	 * @todo implement this function
	 * @access public
	 * @return array Array of relations associated with this class.
	 */
	function getRelations() {
/* 		trigger_error('getRelations() not implemented in formDB formz driver'); */
		return array();
	}
	
	/**
	 * Get the named relation data.
	 *
	 * @todo implement this function
	 * @param string $name Relation name
	 * @access public
	 * @return array relation data as an array.
	 */
	function getRelation($name) {
/* 		trigger_error('getRelation() not implemented in formDB formz driver'); */
		return array();
	}
	
	/**
	 * Is this table/form/relation timestampable?
	 *
	 * @todo implement this function
	 * @access public
	 * @return bool
	 */
	function isTimestampable() {
/* 		trigger_error('isTimestampable() not implemented in formDB formz driver'); */
		return false;
	}
	
	/**
	 * Is this table/form/relation soft deletable?
	 *
	 * @todo implement this function
	 * @access public
	 * @return bool
	 */
	function isSoftDeletable() {
		return false;
	}
	

	/**
	 * setParam
	 *
	 * Set a table based parameter
	 *
	 * @param string $name
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setParam($name, $value) {
		$this->table->$name = $value;
	}

	/**
	 * setInnerParam
	 *
	 * Set a table based inner parameter
	 *
	 * @param string $name
	 * @param string $innername
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setInnerParam($name, $innername, $value)
	{
		$this->table->$name[$innername] = $value;
	}

	/**
	 * setFieldParam
	 *
	 * Set a field specific parameter
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param string $name
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setFieldParam($fieldname, $name, $value)
	{
		if (is_array($fieldname))
			foreach ($fieldname as $fieldn)
			{
				if (isset($this->table->fields[$fieldn]))
					$this->table->fields[$fieldn]->$name = $value;
				else
					trigger_error("No field exists with the name: $fieldn");
			}
		else
		{
			if (isset($this->table->fields[$fieldname]))
			{
				$this->table->fields[$fieldname]->$name = $value;
			}
			else
				trigger_error("No field exists with the name: $fieldname");
		}
	}
	
	/**
	 * setFieldWhere
	 *
	 * Set a field specific parameter
	 *
	 * @param mixed $fieldname must be the name of a field
	 * @param string $where is a sql statment predicate, like 'between 4 and 5' or '= 4'
	 * @param mixed $junction is 'AND' or 'OR'
	 * @access public
	 * @return void
	 */
	function addFieldWhere($fieldname, $where, $junction = 'AND')
	{
		if (isset($this->table->fields[$fieldname]))
		{
			$this->table->fields[$fieldname]->where[] = array('condition' => $where, 'junction' => $junction);
		}
		else
			trigger_error("No field exists with the name: $fieldname");
	}
	
	/**
	 * hideField
	 *
	 * Don't show the field in a list and don't show it in a form.
	 *
	 * @param mixed $fieldname must be the name of a field or an array of fields
	 * @access public
	 * @return void
	 */
	function hideField($fieldname)
	{
		if (is_array($fieldname))
		{
			foreach($fieldname as $name)
			{
				$this->hideField($fieldname);
			}
		}
		else
		{
			if (isset($this->table->fields[$fieldname]))
			{
				$this->table->fields[$fieldname]->listshow = false;
				$this->table->fields[$fieldname]->formshow = false;
			}
			else
				trigger_error("No field exists with the name: $fieldname");
		}
	}
	
	/**
	 * setFieldName
	 *
	 * The label to give the field when displaying.
	 *
	 * @param mixed $fieldname must be the name of a field or an array of fields
	 * @access public
	 * @return void
	 */
	function setFieldName($fieldname, $name)
	{
		if (isset($this->table->fields[$fieldname]))
		{
			$this->table->fields[$fieldname]->label = $name;
			
		}
		else
			trigger_error("No field exists with the name: $fieldname");
	}
	
	/**
	 * formatField
	 *
	 * A format string to use(especially on dates) when displaying in lists.
	 *
	 * @param mixed $fieldname must be the name of a field or an array of fields
	 * @access public
	 * @return void
	 */
	function formatField($fieldname, $format)
	{
		if (isset($this->table->fields[$fieldname]))
		{
			$this->table->fields[$fieldname]->format = $format;
		}
		else
			trigger_error("No field exists with the name: $fieldname");
	}
	
	/**
	 * showDelete
	 *
	 * show a column of delete links for each record in the listing
	 *
	 * @param mixed $path must be the path (not including the zoneUrl) to the delete page.
	 * An id will be added to the end of the path that is give as a page parameter.
	 * @access public
	 * @return void
	 */
	function showDelete($path)
	{
		$this->table->deleteColumn = true;
		$this->table->deletelink = $path;
	}
	
	/**
	 * setRowClasses
	 *
	 * set a map of css classes that will be used to display rows.
	 *
	 * @param string $field is the field that will be used to index the class map
	 * @param mixed $map is the map of field values to css classnames
	 * @access public
	 * @return void
	 */
	function setRowClasses($field, $map)
	{
		$this->table->rowclasses['field'] = $field;
		$this->table->rowclasses['classes'] = $map;
	}

	/**
	 * setFieldIndexTable
	 *
	 * Setup an index table for a specific field, especially useful for things like select boxes and the like.
	 * Used to grab a list of possible values and labels from another table in the database
	 *
	 * @param string $fieldname
	 * @param string $tablename
	 * @param string $id fieldname in the indexed table that has the values in it
	 * @param string $label fieldname in the indexed table that has the labels in it
	 * @param string $restriction something like "date = $date"
	 * @access public
	 * @return void
	 */
	function setFieldIndexTable($fieldname, $tablename, $id, $label, $restriction = null)
	{
		$this->table->fields[$fieldname]->setIndexTable($tablename, $id, $label, $restriction);
	}

	/**
	 * setFieldIndex
	 *
	 * Setup an index for a specific field, especially useful for things like select boxes and the like.
	 * here an array is passed in and key = value, value = label
	 *
	 * @param mixed $fieldname
	 * @param mixed $index
	 * @access public
	 * @return void
	 */
	function setFieldIndex($fieldname, $index)
	{
		$this->table->fields[$fieldname]->setIndex($index);
	}

	/**
	 * setAllFieldsParam
	 *
	 * Sets all existing fields to have a specific value for a specific property.
	 * Useful when you have a table with like 30 fields, but only want to show two in a list.
	 * setAllFieldsParam('listshow', false); then set the two necessary ones to true
	 *
	 * @param mixed $name
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setAllFieldsParam($name, $value)
	{
 		foreach ($this->table->fields as $field)
		{
			$array[] = $field->name;
		}

		$this->setFieldParam($array, $name, $value);
	}

	/**
	 * setAllFieldsInnerParam
	 *
	 * Sets all existing fields to have a specific value for a specific property's parameter.
	 * Some field parameters like html and validate have their own parameters, this function is used to
	 * change one of their parameters without changing the entire html or validate parameter.
	 *
	 * @param string $name
	 * @param string $innername
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setAllFieldsInnerParam($name, $innername, $value)
	{
		foreach ($this->table->fields as $field)
		{
			$array[] = $field->name;
		}

		$this->setFieldInnerParam($array, $name, $innername, $value);
	}

	/**
	 * setFieldInnerParam
	 *
	 * Some field parameters like html and validate have their own parameters, this function is used to
	 * change one of their parameters without changing the entire html or validate parameter.
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param string $name
	 * @param string $innername
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setFieldInnerParam($fieldname, $name, $innername, $value)
	{
		if (is_array($fieldname))
			foreach ($fieldname as $fieldn)
			{
				if (isset($this->table->fields[$fieldn]->$name))
					{
						$tmp = &$this->table->fields[$fieldn]->$name;
						$tmp[$innername] = $value;
					}
				else
					trigger_error("No field exists with the name: $fieldn OR No parameter is named $name");
			}
		else
		{
			if (isset($this->table->fields[$fieldname]->$name))
			{
				$tmp = &$this->table->fields[$fieldname]->$name;
				$tmp[$innername] = $value;
			}
			else
				trigger_error("No field exists with the name: $fieldname OR No parameter is named $name");
		}
	}

	/**
	 * setHTMLoptions
	 * Setup the html display options used when rendering the form for this field.
	 * Should be something like $value = array("type" => "text");
	 * Values of the array other than type are the parameters required/supported by the type of guicontrol.
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param array $value
	 * @access public
	 * @return void
	 * @see guicontrol
	 */
	function setHTMLoptions($fieldname, $value)
	{
		$this->setFieldParam($fieldname, "html", $value);
	}

	/**
	 * setHTMLoption
	 * Setup a specific html display options to be used when rendering the form for this field.
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param string $innername option like type
	 * @param mixed $value
	 * @access public
	 * @return void
	 * @see guicontrol
	 */
	function setHTMLoption($fieldname, $innername, $value)
	{
		$this->setFieldInnerParam($fieldname, "html", $innername, $value);
	}

	/**
	 * setValidationOptions
	 * Define the validation for this specific field, or these sepecific fields.
	 * Values of the array other than type are the parameters required/supported by the type of validation.
	 * type needs to be one supported by the validate class
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param array $value array needs 'type' set.
	 * @access public
	 * @return void
	 * @see validate
	 */
	function setValidationOptions($fieldname, $value)
	{
		$this->setFieldParam($fieldname, "validation", $value);
	}

	/**
	 * setValidationOption
	 * Define an individaul validation parameter for this specific field, or these sepecific fields.
	 * could be something like setValidationOption('name', 'type', 'alphanumeric');
	 * type needs to be one supported by the validate class
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param string $innername
	 * @param string $value
	 * @access public
	 * @return void
	 * @see validate
	 */
	function setValidationOption($fieldname, $innername, $value)
	{
		$this->setFieldInnerParam($fieldname, "validation", $innername, $value);
	}

	/**
	 * required
	 * set a field or fields to be required as part of the validation.
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function required($fieldname, $value = true)
	{
		$this->setValidationOption($fieldname, 'required',  $value);
	}

	/**
	 * getValue
	 * gets the value from the record object and returns it.
	 *
	 * @param string $fieldname
	 * @access public
	 * @return mixed $value
	 */
	function getValue($fieldname)
	{
		if ($this->id == 'new')
			return "";
			
		if (isset($this->record->values[$fieldname]->value))
			return $this->record->values[$fieldname]->value;
		else
			return NULL;
// 			trigger_error("No value is set for the field: $fieldname");
	}

	/**
	 * getRecords
	 * Requests the necessary records from the database (as would be used in a listing).
	 *
	 * @param mixed $limit
	 * @access public
	 * @return void
	 */
	function getRecords($limit = false)
	{
		if ($limit !== false)
			$this->setParam("limit", $limit);

		$this->table->getRecords();
		die_r($this->table->records);
		
		$ret = array();
		foreach ($this->table->records as $key => $record) {
			$rec = array();
			foreach ($record->values as $j => $field) {
				$rec[$j] = $field->value;
			}
			$ret[$key] = $rec;
		}
		return $ret;
	}

	/**
	 * Request the record with given ID from the database.
	 *
	 * @param mixed $id
	 * @access public
	 * @return int Record ID
	 */
	function getRecord($id = false) {
		if ($id === false)
			$id = $this->id;
		else $this->id = $id;

		$this->record = &$this->passRecord($this->tablename, $id);
		$this->DescIntoFields($this->tablename, $id);

		return $this->id;
	}

	/**
	 * Write the current record to the database.
	 * If the record is new it will insert it, if not it will update it.
	 *
	 * @param mixed $values A POSTlike array of values to save to this record.
	 * @param int $id Record ID
	 * @access public
	 * @return int Record ID
	 */
	function saveRecord($values, $id = null)
	{
		if (!isset($this->id))
			trigger_error("Forms2 does not have a current record to save");
		if ($values === false)
			$values = getPost();

		$this->setvaluesfrompost($values);
		$this->id = $this->storeRecord($this->tablename, $this->id);
		return $this->id;
	}

	/**
	 * deleteRecord
	 * Removes a record from the database
	 *
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function deleteRecord($id)
	{
		$this->_deleteRecord($this->tablename, $id);
	}

	/**
	 * sort
	 * tells the getRecords function a sorting to get the records in from the database.
	 *
	 * @param string $fieldname fieldname to sort on
	 * @param string $direction either ASC or DESC
	 * @access public
	 * @return void
	 */
	function sort($fieldname, $direction = "ASC")
	{
		$this->table->sort = $fieldname;
		$this->table->direction = $direction;
	}

	/**
	 * returns the current Record
	 *
	 * @access public
	 * @return mixed
	 */
	function &returnRecord()
	{
		return $this->record;
	}

	/**
	 * returns the current TableName
	 *
	 * @access public
	 * @return mixed
	 */
	function &returnTableName()
	{
		return $this->tablename;
	}
	
	
	
	function getOrder() {
		return $this->table->order;
	}
	
	function getField($field_name) {
	die_r($this->table);
		return (isset($this->table->fields[$field_name])) ? $this->table->fields[$field_name] : null;
	}




	
/***************************************//**
 * EVERYTHING FROM THIS POINT DOWN IS LEGACY
 * FORMS(1) STUFFS. SUBJECT TO DEPRECATION
 * OR DELETION AT ANY TIME.
 ******************************************/



	
	

	/**
	 * sql_connect
	 *
	 * @param mixed $dbconnname
	 * @access public
	 * @return void
	 */
	function sql_connect($dbconnname)
	{
		if (!isset($GLOBALS[$dbconnname]) && $dbconnname == 'defaultdb') {
			$dsn = Config::get('zoop.db.dsn');
			$GLOBALS[$dbconnname] = &new database($dsn);
		} elseif (!isset($GLOBALS[$dbconnname])) {
			trigger_error("You should have established a connection prior to this call if you are not using the defaultdb");
		}
	}

	/**
	 * initTable
	 *
	 * @param mixed $table
	 * @param string $dbconnname
	 * @access public
	 * @return void
	 */
	function initTable($table, $dbconnname = 'defaultdb') {
		$this->sql_connect($dbconnname);
		global $$dbconnname;

		$this->dbconnname = $dbconnname;

		if (!isset($this->table))
			$this->table = new table($table, $dbconnname);
 		else
 			$this->table->setDbconnname($dbconnname);
 			
 		$fields = $this->table->fields;
 		$this->fields = array();
 		
 		foreach ($fields as $key => $field) {
 			$this->fields[$key] = array();
 			if ($field->type) $this->fields[$key]['type'] = $field->type;
 			else $this->fields[$key]['type'] = 'string'; // this is 100 percent ghetto :-/
 			if ($field->length) $this->fields[$key]['length'] = $field->length;
 			if ($field->autoincrement) $this->fields[$key]['autoincrement'] = $field->autoincrement;
 			if ($field->key) $this->fields[$key]['primary'] = $field->key;
/* 			$this->fields[$key]['required'] = $field->required; */
 		}
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
		if (!isset($this->table))
			$this->initTable($table, $this->dbconnname);

		return $this->table->idfield;
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
		if (isset($this->table->records->$id))
			return;

		$this->initTable($table, $this->dbconnname);


		$idfield = $this->passIdfield($table);

		$this->table->records[$id] = new record ($table, $id, $idfield, $this->dbconnname);
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
		if (!isset($this->table->records[$id]))
				$this->grabRecord($table, $id);
		$this->table->records[$id]->order = &$this->table->order;

		$var =& $this->table->records[$id];

		return $var;
	}

	/**
	 * _deleteRecord
	 *
	 * @todo remove this bad boy
	 * @param mixed $table
	 * @param mixed $id
	 * @param mixed $type
	 * @access public
	 * @return void
	 */
	function _deleteRecord ($table, $id, $type = false)
	{
		$dbconnname = $this->dbconnname;
		global $$dbconnname;

		$idfield = $this->passIdfield($table);

		if (isset($this->table->deletedfield))
		{
			$deletedfield = $this->table->deletedfield;
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
		if (isset($this->table->records[$id]->values) && is_array($this->table->records[$id]->values))
		{
			while(list ($key,$cell) = each($this->table->records[$id]->values))
			{
				$fieldname = $cell->name;
				$this->table->records[$id]->values[$fieldname]->description =& $this->table->fields[$fieldname];
			}
		}
		else
		{
			echo_r("this record does not exist"); die();
		}
	}

	/**
	 * _saveRecord
	 *
	 * @todo remove this bad boy
	 * @param mixed $post
	 * @access public
	 * @return void
	 */
	function _saveRecord($post)
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
			if (isset($this->table->records[$id]->values[$field]))
			{
				if (is_array($value))
				{

					if ($this->table->fields[$field]->datatype == "numeric")
					{
						$setval = 0;
						foreach ($value as $val)
						{
							$setval = $setval | $val;
						}
						$this->table->records[$id]->values[$field]->value = $setval;
					}
					else
						$this->table->records[$id]->values[$field]->value = implode(",", $value);
				}
				else
					$this->table->records[$id]->values[$field]->value = $value;
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

		$record =& $this->table->records[$id];

		$idfield = $this->table->idfield;

		if ($id == "new" && $this->table->fields[$idfield]->autoincrement)
			unset($record->values[$idfield]); // only unset this if the table is set to autoincrement

/* 		if ($$dbconnname->db->phptype == "mysql" || $$dbconnname->db->phptype == "mysqli") */
			$colquote = '`';
		/*
else
			$colquote = '"';
*/

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
			
			if ($this->timestampable) {
				$columnstring .= $colquote . 'created_at' . $colquote . ',' . $colquote . 'updated_at' . $colquote . ',';
				$valuestring .= 'NOW(),NOW(),';
			}
			
			$columnstring = substr($columnstring, 0, -1);
			$valuestring = substr($valuestring, 0, -1);
			$query = "INSERT INTO $table ($columnstring) VALUES ($valuestring)";

			if (isset($this->table->sequence) && $this->table->sequence)
			{
				$$dbconnname->insert($query);
				return $$dbconnname->fetch_one_cell("SELECT currval('\"{$this->table->sequence}\"'::text)");
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
			
			if ($this->timestampable) {
				$setpart .= $colquote . 'updated_at' . $colquote . "= NOW(),";
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
	
	/**
	 * Set a form as timestampable
	 *
	 * @todo Decide how much of this actually matters... should the created/updated fields be
	 * hidden by default?
	 *
	 * @param bool $value
	 */
/*
	function setTimestampable($value = true) {
		$this->timestampable = $value;

		if ($value) {
			$this->setFieldParam(array("created_at", "updated_at"), "formshow", false);
		}
	}

*/
}