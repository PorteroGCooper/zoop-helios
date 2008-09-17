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
 * Interface for formz database connectors.
 *
 * This interface is currently implemented by doctrineDB and
 * formDB (a combination of Zoop 1.x forms and forms2).
 *
 * @author Justin Hileman <justin@justinhileman.info>
 * @package formz
 * @access public
 * @copyright Supernerd LLC and Contributors
 */
interface formz_driver_interface {
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
	function setParam($name, $value);
	
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
	function setInnerParam($name, $innername, $value);

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
	function setFieldParam($fieldname, $name, $value);
	
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
	function addFieldWhere($fieldname, $where, $junction = 'AND');
	
	/**
	 * hideField
	 *
	 * Don't show the field in a list and don't show it in a form.
	 *
	 * @param mixed $fieldname must be the name of a field or an array of fields
	 * @access public
	 * @return void
	 */
	function hideField($fieldname);
	
	/**
	 * setFieldName
	 *
	 * The label to give the field when displaying.
	 *
	 * @param mixed $fieldname must be the name of a field or an array of fields
	 * @access public
	 * @return void
	 */
/* 	function setFieldName($fieldname, $name); */
	
	/**
	 * formatField
	 *
	 * A format string to use(especially on dates) when displaying in lists.
	 *
	 * @param mixed $fieldname must be the name of a field or an array of fields
	 * @access public
	 * @return void
	 */
	function formatField($fieldname, $format);
	
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
	function showDelete($path);
	
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
	function setRowClasses($field, $map);

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
	function setFieldIndexTable($fieldname, $tablename, $id, $label, $restriction = null);

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
	function setFieldIndex($fieldname, $index);

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
	function setAllFieldsParam($name, $value);

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
	function setAllFieldsInnerParam($name, $innername, $value);

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
	function setFieldInnerParam($fieldname, $name, $innername, $value);

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
	function setHTMLoptions($fieldname, $value);

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
	function setHTMLoption($fieldname, $innername, $value);

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
	function setValidationOptions($fieldname, $value);

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
	function setValidationOption($fieldname, $innername, $value);

	/**
	 * required
	 * set a field or fields to be required as part of the validation.
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function required($fieldname, $value = true);

	/**
	 * getValue
	 * gets the value from the record object and returns it.
	 *
	 * @param string $fieldname
	 * @access public
	 * @return mixed $value
	 */
	function getValue($fieldname);

	/**
	 * getRecords
	 * Requests the necessary records from the database (as would be used in a listing).
	 *
	 * @param mixed $limit
	 * @access public
	 * @return void
	 */
	function getRecords($limit = false);

	/**
	 * getRecord
	 * Requests the requested record from the database (as would be used in a record).
	 *
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function getRecord($id = false);

	/**
	 * saveRecord
	 * Takes the current record and writes its content to the database.
	 * If the record is new it will insert it, if not it will update it.
	 *
	 * @param mixed $POST
	 * @access public
	 * @return void
	 */
	function saveRecord($values, $id = null);

	/**
	 * deleteRecord
	 * Removes a record from the database
	 *
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function deleteRecord($id);

	/**
	 * sort
	 * tells the getRecords function a sorting to get the records in from the database.
	 *
	 * @param string $fieldname fieldname to sort on
	 * @param string $direction either ASC or DESC
	 * @access public
	 * @return void
	 */
	function sort($fieldname, $direction = "ASC");

	/**
	 * returns the current Record
	 *
	 * @access public
	 * @return mixed
	 */
	function &returnRecord();

	/**
	 * returns the current TableName
	 *
	 * @access public
	 * @return mixed
	 */
	function &returnTableName();

}
