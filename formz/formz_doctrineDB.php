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
 * doctrineDB is a Doctrine connector library for formz.
 *
 * @ingroup forms
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
	var $query = null;
	var $record = null;
	
	/**
	 * Values that are fixed for both querying and Create and Update 
	 * 
	 * @var array
	 * @access public
	 */
	var $fixedValues = array();

	/**
	 * Set only when using nested sets (trees) 
	 * 
	 * @var mixed
	 * @access protected
	 */
	var $_parentRecord = false;

	/**
	 * True if current form is soft deletable
	 *
	 * @var bool
	 * @access private
	 */
	var $softdeletable = false;


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
	
	
	

	/**
	 * Return an array of fields in this class/table/form.
	 *
	 * @access public
	 * @return array Field data
	 */
	function getFields() {
		$columns       = $this->table->getColumns();
		$table_options = $this->table->getOptions();

		// Remove columns for nested sets. Theses fields may be different for different types
		// of tree implementations.
		if (isset($table_options['treeImpl']) && 'NestedSet' == $table_options['treeImpl']) {
			unset(
				$columns[$table_options['treeOptions']['rootColumnName']],
				$columns['lft'],
				$columns['rgt'],
				$columns['level']
			);
		}
		return $columns;
	}
	
	function getData() {
		if(!$this->record) return null;
		$data = $this->record->toArray();
		$data = $data + $this->getRecordRelationsValues();
		return $data;
		
	}
	

	/**
	 * Return the slug field for this table
	 * 
	 * @return $string slug field
	 */
	function getSlugField() {
		$options = $this->table->getTemplate('Doctrine_Template_Sluggable')->getOptions();
		return $options['name'];
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
	function required($fieldname, $value = true) {
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
	 * If a query is set, it will execute that query.
	 * If the table is a tree, it will either list the root nodes, or the children nodes
	 * 	depending on if a parent node is set
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
		if ($this->query || $this->getFixedValues()) {
			return $this->applyFixedValuesToQuery()->execute();
		} elseif ($this->table->isTree()) {
			if ($this->hasParentRecord()) {
				if ($children = $this->getParentRecord()->getNode()->getChildren()) {
					return $children;
				} else {
					return array();
				}
			} else {
				return $this->getRootNodes();
			}
		} else {
			return $this->table->findAll()->toArray();
		}
	}

	/**
	 * When Working with Trees, get the root nodes in the tree 
	 * 
	 * @access public
	 * @return array
	 */
	function getRootNodes() {
		if ($this->table->isTree()) {
			return $this->table->findByLevel(0)->toArray();
			//echo_r($this->table->getTree()->getBaseQuery()->execute()->toArray());
			//return $this->table->getTree()->getBaseQuery()->execute()->toArray();
			//echo_r($this->table->getTree()->fetchRoots()->toArray());
			// return $this->table->getTree()->fetchRoots()->toArray();
		} else {
			return array();
		}
	}

	/**
	 * When working with trees, fetch the entire tree 
	 * 
	 * @access public
	 * @return array
	 */
	function getTree() {
		if ($this->table->isTree()) {
			return $this->table->getTree()->toArray();
		} else {
			return array();
		}
	}

	/**
	 * getRecord
	 * Requests the requested record from the database (as would be used in a record).
	 *
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function getRecord($id = null) {
		if ($id == 'new') {
			$this->type = 'record';
			$classname  = $this->tablename;
			$classname[0] = strtoupper($classname[0]);
			$this->record = new $classname();
			if ($this->getFixedValues()) {
				$this->record->fromArray($this->getFixedValues());
			}
		} elseif ($this->getFixedValues()){
			$record = $this->applyFixedValuesToQuery()->fetchOne();

			// if you didn't find one, return.
			if (!$record && $record !== 0) return null;

			$this->type = 'record';
			$this->record = $record;
		} elseif ($record = Doctrine::getTable($this->tablename)->find($id)) {
			$this->type = 'record';
			$this->record = $record;
		} else {
			$id = null;
		}
		return $id;
	}
	
	/**
	 * Requests the requested record from the database by slug value.
	 *
	 * @param string $id slug
	 * @access public
	 * @return mixed Returns record id (if found) or null.
	 */
	function getRecordBySlug($slug) {
		if (empty($slug)) return null;
		return $this->getRecord($this->getRecordIdBySlug($slug));
	}
	
	/**
	 * Return a record id for a given slug
	 *
	 * @access public
	 * @param string $slug
	 * @return int Record id.
	 */
	function getRecordIdBySlug($slug) {
		$id_field = $this->getIdField();
		$records = $this->table->createQuery()->select($id_field)->addWhere('slug = ?', $slug)->execute()->toArray();
		if (count($records)) {
			return $records[0][$id_field];
		} else {
			return null;
		}
	}

	
	function getDoctrineRecord($id = false) {
		if ($id && isset($this->record)) {
			return $this->record;
		} else {
			$this->getRecord($id);
			return $this->record;
		}
	}

	/**
	 * Save a record.
	 *
	 * Save an array of values to the specified record.
	 * 
	 * This function assumes that related records should always be unique.
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

		if (isset($values['relations'])) {
			$submitted_relations = $values['relations'];
			unset($values['relations']);
		}
		
		if ($this->getFixedValues()) {
			$values = array_merge($values, $this->getFixedValues());
		}
		
		foreach ($values as $key => $val) {
			$this->record->$key = $val;
		}

		if ($this->table->isTree()) {
			if ($this->hasParentRecord()) {
				$parent = $this->getParentRecord();
				$this->record->getNode()->insertAsLastChildOf($parent);
			} else {
				$this->record->root_id = 1;
				$this->table->getTree()->createRoot($this->record);
			}
		} else {
			$this->record->save();
		}

		// Get relation classes for the current table.
		$relationships = $this->getTableRelations();

		// Loop through relation classes and get the actual related records.
		foreach ($relationships as $rel => $foo) {

			// skip this one if no relation records are returned.
			if (!is_object($this->record->$rel)) continue;
			
			// Unlinking related records can happen on each loop. $unlink_rel needs to be *unset*
			// in order to keep records from being unlinked when we don't want them to be.
			// Do *NOT* set $unlink_rel to '' or array()
			if (isset($unlink_rel)) unset($unlink_rel);

			// Obtain and loop through all the related records for the current class ($rel).
			$related_records = $this->record->$rel->toArray();

			if (isset($submitted_relations[$rel]) && !empty($submitted_relations[$rel])) {
				foreach ($related_records as $record) {
					if (in_array($record['id'], $submitted_relations[$rel])) {
						// Assume duplicate related records are a bad thing and don't try adding one.
						$dup_key = array_search($record['id'], $submitted_relations[$rel]);
						unset($submitted_relations[$rel][$dup_key]);
					} else {
						// Related record in database was not submitted, add to array for removal.
						$unlink_rel[] = $record['id'];
					}
				}
			}
			else {
				// No related records submitted. Giving doctrine an empty array/value removes all relations.
				$unlink_rel = array();
			}
			
			// Passing Doctrine an empty array or value unlinks all related records for the class.
			// Therefore, this checks isset() instead of is_array() or !empty()
			if (isset($unlink_rel)) {
				$this->record->unlink($rel, $unlink_rel);
			}

		}

		// Link the now filtered submitted relations to their classes.
		// This is not done in foreach($relationships) because that doesn't work when
		// there's nothing currently in the database.
		if (isset($submitted_relations)) {
			foreach ($submitted_relations as $relation_class => $ids) {
				// Doctrine 1.0.* assumes the array starts with an index of 0.
				// This fixes our array keys so Doctrine doesn't barf on $ids.
				if (is_array($ids)) sort($ids);
				$this->record->link($relation_class, $ids);
			}
		}
		
		return array_shift($this->record->identifier());
	}

	/**
	 * Delete a record.
	 *
	 * @param int $id Record ID.
	 * @access public
	 * @return int The db id of the saved record.
	 */
	function destroyRecord($id = null) {
	
		// get the record we want to save...
		if ($id !== null) {
			if (!$this->getRecord($id)) {
				trigger_error("Unable to initialize record: " . $id);
				return false;
			}
		}
			
		if ($this->table->isTree()) {
			return $this->record->getNode()->delete();
		} else {
			return $this->record->delete();
		}
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
	
	/**
	 * Get all rows from the named related table
	 *
	 * @param string $name Relation name
	 * @param bool $getValues Whether to fetch the values or not
	 * @access public
	 * @return array relation data as an array.
	 */
	function getTableRelation($fieldName, $getValues = false) {
		$rel = $this->getTableRelations($getValues);
		return (isset($rel[$fieldName])) ? $rel[$fieldName] : false;
	}

	/**
	 * Fetches the entire table for a relation 
	 * Use this for populating selects and drop downs
	 * 
	 * @param string $fieldName 
	 * @access public
	 * @return $array values
	 */
	function getTableRelationValues($fields) {
		$foreign_values = array();
		foreach ((array) $fields as $fieldName) {
			$relation = $this->getTableRelation($fieldName);
			$foreign_class = Doctrine::getTable($relation['class']);
		 	$set = $foreign_class->createQuery()->select($relation['foreign_field']. ", " . $relation['label_field'])->execute()->toArray();
		 	$temp_array = array();
		 	foreach ($set as $row) {
		 		$temp_array[$row[$relation['foreign_field']]] = $row[$relation['label_field']];
		 	}
		 	$foreign_values[$fieldName] = $temp_array;
		} 

		if (count($foreign_values) == 1) {
			return array_shift($foreign_values);
		} else {
			return $foreign_values;
		}
	}
	
	/**
	 * Get all rows from all tables related to the current table
	 *
	 * @param bool $getValues Whether to fetch the values or not
	 * @access public
	 * @return array Array of relations associated with this class.
	 */
	function getTableRelations($getValues = false) {
		$ret = array();
				
		foreach ($this->table->getRelations() as $name => $relation) {
			$rel_type = ($relation->getType() == Doctrine_Relation::MANY) ? Formz::MANY : Formz::ONE;
			
			$label_field = null;

			// get the current relation values to put in the array
			$foreign_class = Doctrine::getTable($relation->getClass());
			if ($getValues) {
				$foreign_values = $this->getTableRelationValues($name);
			} else { 
				$foreign_values = array();
			}
			
			// grab the id field names for each half of this relation
			if ($rel_type == Formz::ONE) {
				$local_field   = $relation->getLocalFieldName();
				$foreign_field = $relation->getForeignFieldName();
			} else {
				$local_field   = $relation->getClass();
				$foreign_field = $foreign_class->getIdentifier();
				if (is_array($foreign_field)) continue;
				
				// this *will* be used later by GCooper.
				// $embeddedForm = new Formz($local_field);
			}
			
			// guess which column to display in the select
			$foreign_fields = $foreign_class->getColumnNames();
			
			// try the defaults
			foreach(Config::get('zoop.formz.title_field_priority') as $field_name){
				if (in_array($field_name, $foreign_fields)) {
					$label_field = $field_name;
					break;
				}
			}
			// then grab the first non-id field
			if (!$label_field) {
				foreach($foreign_fields as $col_name) {
					if ($col_name != $foreign_field) {
						$label_field = $col_name;
						break;
					}
				}
			}

			if (!$label_field){
				$label_field = $foreign_field;
			}
			
			$ret[$local_field] = array(
				'alias' => $name,
				'class' => $relation['class'],
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
	
	function getRecordRelations() {
		$data = array();
		foreach ($this->table->getRelations() as $name => $relation) {
			$data[$name] = $this->record->$name->toArray();
		}

		return $data;
	}

	function getRecordRelationsValues() {
		$data = array();
		foreach ($this->getTableRelations() as $name => $relation) {
			if ($relation['rel_type'] == Formz::MANY) {
				$array = $this->record->$name->toArray();
				foreach ($array as $value) {
					$data[$name][] = $value[$relation['foreign_field']]; // = $value[$relation['label_field']];
				}
			} else {
				$data[$name] = $this->record->$name;
			}
		}

		return $data;
	}
	/**
	 * Returns true if this Formz does timestamp magick.
	 *
	 * @access public
	 * @return bool True if this is timestampable.
	 */
	function isTimestampable() {
		return $this->table->hasTemplate('Doctrine_Template_Timestampable');
	}
	
	/**
	 * Returns true if this table uses soft delete.
	 *
	 * @access public
	 * @return bool True if this is soft deletable.
	 */
	function isSoftDeletable() {
		return $this->table->hasTemplate('Doctrine_Template_SoftDelete');
	}

	/**
	 * Returns true if this table uses slugs.
	 *
	 * @access public
	 * @return bool True if this is sluggable.
	 */	
	function isSluggable() {
		return $this->table->hasTemplate('Doctrine_Template_Sluggable');
	}
	
	
	/**
	 * Returns true if this Doctrine table uses versions.
	 *
	 * @access public
	 * @return bool True if this is versionable.
	 */
	function isVersionable() {
		return $this->table->hasTemplate('Doctrine_Template_Versionable');
	}

	/**
	 * Returns true if table is a tree 
	 * 
	 * @access public
	 * @return void
	 */
	function isTree() {
		return $this->table->isTree();
	}

	
	/**
	 * Order results by given column and direction.
	 *
	 * @param string $fieldname Table column on which to sort
	 * @param string $direction Either ASC or DESC
	 * @access public
	 * @return void
	 */
	function sort($fieldname, $direction = "ASC") {
		//$this->query->orderBy("$fieldname $direction");
		$this->getQuery()->orderBy("$fieldname $direction");
	}

	/**
	 * Return's this query. If it doesn't exist, create and apply fixed values to it.  
	 * 
	 * @access public
	 * @return void
	 */
	function getQuery() {
		if (!$this->query) {
			$this->query = $this->table->createQuery();
		}

		return $this->query;
	}

	function &applyFixedValuesToQuery() {
		$fixed = $this->getFixedValues();
		if ($fixed) {
			foreach ($fixed as $key => $value) {
				if(is_null($value)) {
					$this->getQuery()->addWhere($key . ' IS NULL');
				} else {
					$this->getQuery()->addWhere("$key = ?", $value);
				}
			}
		}

		return $this->query;
	}

	/**
	 * Set a fixed value to be used when seleting as well as updating 
	 * 
	 * @param mixed $array 
	 * @access public
	 * @return void
	 */
	function setFixedValues($array) {
		$this->fixedValues = $array;
	}
	
	/**
	 * Adds new fixed value(s) to the existing ones.
	 *
	 * @param array Fixed value to add, in the form array('key' => 'val')
	 * @access public
	 * @return void
	 */
	function addFixedValue($array) {
		$this->fixedValues = $this->fixedValues + $array;
	}

	/**
	 * Returns an array of fixed values to use when selecting as well as creating/updating 
	 * 
	 * @param mixed $key 
	 * @access public
	 * @return void
	 */
	function getFixedValues($key = false) {
		if ($key) {
			if (isset($this->fixedValues[$key])) {
				return $this->fixedValues[$key];
			} else {
				return null;
			}
		} else {
			return $this->fixedValues;
		}
	}

	/**
	 * if the form has a parent node set, return true 
	 * 
	 * @access public
	 * @return bool
	 */
	function hasParentRecord() {
		if ($this->_parentRecord) {
			return true;
		} else {
			return false;
		}
	}

	//function getParentRecord() {
		//$record = $this->table->find($this->_parentRecord);

	//}

	/**
	 * Get the parent node for this form 
	 * Used when using nested sets
	 * 
	 * @access public
	 * @return objects
	 */
	function getParentRecord() {
		if ($this->hasParentRecord() ) {
			if (is_object($this->_parentRecord)) {
				return $this->_parentRecord;
			} else {
				return $this->table->find($this->_parentRecord);
			}
		} else {
			return false;
		}
	}

	/**
	 * returns the current Record
	 *
	 * @access public
	 * @return mixed
	 */
	function &returnRecord() {
		return $this->record;
	}

	/**
	 * returns the current TableName
	 *
	 * @access public
	 * @return mixed
	 */
	function &returnTableName() {
		return $this->tablename;
	}
	
	
}
