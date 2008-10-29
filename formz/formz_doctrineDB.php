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
 * doctrineDB is a Doctrine connector library for formz.
 *
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Justin Hileman <justin@justinhileman.info>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class formz_doctrineDB implements formz_driver_interface {

	/**
	 * Doctrine table object associated with this formz.
	 *
	 * @var
	 */
	var $table;
	var $tablename;

	/**
	 * doctrineDB constructor.
	 *
	 * Can be used to instantiate the object, or if passed a type, handle the
	 * retrival of information from the database.
	 *
	 * @param string $tablename table name in the database
	 * @param string $type OPTIONAL, can be 'list' or 'record'
	 * @param mixed  $int OPTIONAL, if $type = 'list' than an int that represents the limit,
	 * if $type = 'record' then required and is the id of the record
	 * @return void
	 * @access public
	 */
	function formz_doctrineDB($tablename) {
		$this->tablename = $tablename;
		$this->table = Doctrine::getTable($this->tablename);
	}
	
	
	
	
	
	
	
	function getFields() {
		return $this->table->getColumns();
	}
	
	function getData() {
		return $this->record->toArray();
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
	function setParam($name, $value)
	{
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
		if (isset($this->table->fields[$fieldname])) {
			$this->table->fields[$fieldname]->format = $format;
		}
		else {
			trigger_error("No field exists with the name: $fieldname");
		}
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
	function getValue($fieldname) {
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
	function getRecords($limit = false) {
/*
		if ($limit !== false)
			$this->setParam("limit", $limit);
*/

		return $this->table->findAll()->toArray();
	}

	/**
	 * getRecord
	 * Requests the requested record from the database (as would be used in a record).
	 *
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function getRecord($id = false) {
		if ($id == 'new') {
			$this->type = 'record';
			$classname  = $this->tablename;
			$classname[0] = strtoupper($classname[0]);
			$this->record = new $classname();
		} else if ($record = Doctrine::getTable($this->tablename)->find($id)) {
			$this->type = 'record';
			$this->record = $record;
		} else {
			$id = false;
		}
		return $id;
	}

	/**
	 * Save a record.
	 *
	 * Save an array of values to the specified record.
	 *
	 * @param array $values Array of col/value pairs to be saved.
	 * @param int $id Record ID.
	 * @access public
	 * @return int The db id of the saved record.
	 */
	function saveRecord($values, $id = null) {
	
		// get the record we want to save...
		if ($id !== null) {
			if (!$this->getRecord($id)) {
				trigger_error("Unable to initialize record: " . $id);
				return false;
			}
		}
		foreach ($values as $key => $val) {
			$this->record->$key = $val;
		}
			
		$this->record->save();
		return array_shift($this->record->identifier());
	}
	
	
	/**
	 * Return the record ID field name
	 *
	 * @return string Record ID field name
	 */
	function getIdField() {
		$id = $this->table->getIdentifier();
		if (is_array($id)) $id = array_shift($id);
		
		return $id;
	}

	/**
	 * deleteRecord
	 * Removes a record from the database
	 *
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function deleteRecord($id) {
		$this->_deleteRecord($this->tablename, $id);
	}
	
	function getRelation($name) {
		$rel = $this->getRelations();
		return (isset($rel[$name])) ? $rel[$name] : false;
	}
	
	function getRelations() {
		$ret = array();
				
		foreach ($this->table->getRelations() as $name => $relation) {
			$local_field = $relation->getLocalFieldName();
			$foreign_field = $relation->getForeignFieldName();

			// get the current relation values to put in the array
			$foreign_class = Doctrine::getTable($relation->getClass());
			$foreign_values = $foreign_class->findAll()->toArray();
			
			// guess which column to display in the select
			$foreign_fields = $foreign_class->getColumnNames();
			
			// try the defaults
			foreach(Config::get('zoop.formz.relations.display_field_priority') as $field_name){
				if (in_array($field_name, $foreign_fields)) {
					$label_field = $field_name;
					break;
				}
			}
			// then grab the first non-id field
			if (!isset($label_field)) {
				foreach($foreign_columns as $col_name) {
					if ($col_name != $foreign_field) {
						$label_field = $col_name;
						break;
					}
				}
			}
			if (!isset($label_field)){
				$label_field = $foreign_field;
			}
			
			$rel_type = ($relation->getType() == Doctrine_Relation::MANY_AGGREGATE) ? 'many' : 'one';
						
			$ret[$local_field] = array(
				'alias' => $name,
				'rel_type' => $rel_type,
				'local_field' => $local_field,
				'foreign_field' => $foreign_field,
				'label_field' => $label_field,
/* 				'foreign_class' => $foreign_class, */
				'values' => $foreign_values,
			);
		}
		
		return $ret;
	}
	
	
	function isTimestampable() {
		return $this->table->hasTemplate('Doctrine_Template_Timestampable');
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

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
