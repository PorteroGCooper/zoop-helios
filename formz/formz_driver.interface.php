<?php
/**
 * @group Formz
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
 * @access public
 * @copyright Supernerd LLC and Contributors
 */
interface formz_driver_interface {

	/**
	 * Return the name of the id field for this table.
	 *
	 * @access public
	 * @return string ID field name
	 */
	function getIdField();

	/**
	 * Return an array of fields in this class/table/form.
	 *
	 * @access public
	 * @return array Field data
	 */
	function getFields();

	/**
	 * Return all data associated with this form.
	 *
	 * @access public
	 * @return array An array of form field values for the record or records.
	 */
	function getData();

	/**
	 * Requests all records from the database.
	 *
	 * This is generally used for a list or gridedit view.
	 *
	 * @param mixed $limit
	 * @access public
	 * @return ??
	 */
	function getRecords($limit = false);

	/**
	 * Requests the requested record from the database (as would be used in a record).
	 * If no ID is specified, return the record currently associated with this form.
	 *
	 * @param mixed $id (optional) record id
	 * @access public
	 * @return ??
	 */
	function getRecord($id = false);

	/**
	 * Save an array of POST formatted data to a record.
	 * If the record is new it will insert it, if not it will update it.
	 *
	 * @param mixed $values POST formatted values to save in this record.
	 * @access public
	 * @return int Save status: ID if successful, false if failed.
	 */
	function saveRecord($values, $id = null);

	/**
	 * Remove a record from the database
	 *
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function deleteRecord($id);

	/**
	 * Get all db relations.
	 *
	 * @access public
	 * @return array Array of relations associated with this class.
	 */
	function getTableRelations();

	/**
	 * Get the named relation data.
	 *
	 * @param string $name Relation name
	 * @access public
	 * @return array relation data as an array.
	 */
	function getTableRelation($name);


	/**
	 * Fetches the entire table for a relation 
	 * Use this for populating selects and drop downs
	 * 
	 * @param string $fieldName 
	 * @access public
	 * @return $array values
	 */
	function getTableRelationValues($fields);

	/**
	 * Is this table/form/relation timestampable?
	 *
	 * @access public
	 * @return bool
	 */
	function isTimestampable();

	/**
	 * Does this table use soft delete?
	 *
	 * @access public
	 * @return bool
	 */
	function isSoftDeletable();

	/**
	 * Does this table use slugs?
	 *
	 * @access public
	 * @return bool
	 */
	function isSluggable();

	/**
	 * Does this table use versioning?
	 *
	 * @access public
	 * @return bool
	 */
	function isVersionable();

	/**
	 * Order results by given column and direction.
	 *
	 * @param string $fieldname Table column on which to sort
	 * @param string $direction Either ASC or DESC
	 * @access public
	 * @return void
	 */
	function sort($fieldname, $direction = "ASC");





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
/* 	function setValidationOptions($fieldname, $value); */

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
/* 	function setValidationOption($fieldname, $innername, $value); */

	/**
	 * required
	 * set a field or fields to be required as part of the validation.
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
/* 	function required($fieldname, $value = true); */

	/**
	 * getValue
	 * gets the value from the record object and returns it.
	 *
	 * @param string $fieldname
	 * @access public
	 * @return mixed $value
	 */
/* 	function getValue($fieldname); */
	

	
}
