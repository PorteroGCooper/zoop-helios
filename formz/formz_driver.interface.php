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
	 * Return the driver type for this formz driver.
	 *
	 * @access public
	 * @return int Formz::DoctrineDB or Formz::FormDB const.
	 */
	function getType();

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
	 * @param $bool A flag to indicate if getData should return relations as Formz objects
	 * @access public
	 * @return array An array of form field values for the record or records.
	 */
	function getData($return_formz = false);

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
	 * @return mixed Returns record id (if found) or null.
	 */
	function getRecord($id = null);

	/**
	 * Requests the requested record from the database by slug value.
	 *
	 * @param string $id slug
	 * @access public
	 * @return mixed Returns record id (if found) or null.
	 */
	function getRecordBySlug($slug);
	
	/**
	 * Requests the requested record id from the database by slug value.
	 *
	 * @param string $id slug
	 * @access public
	 * @return mixed Returns record id (if found) or null.
	 */
	function getRecordIdBySlug($slug);

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
	 * Fetches the slug field for this table
	 * 
	 * @access public
	 * @return $string slug field
	 */
	function getSlugField();

	/**
	 * Fetches the version field for this table
	 * 
	 * @access public
	 * @return $string version field
	 */
	function getVersionField();

	/**
	 * Fetches the timestamp fields (created, updated) for this table
	 * 
	 * @access public
	 * @return $array values
	 */
	function getTimestampFields();

	/**
	 * Fetches the soft delete field for this table
	 * 
	 * @access public
	 * @return $string soft delete field
	 */
	function getSoftDeleteField();

	/**
	 * Fetches a reference to this driver's Doctrine query or
	 * triggers an error if this is not a Doctrine driver
	 * 
	 * @access public
	 * @return &DoctrineQuery
	 */
	function &getDoctrineQuery();

	/**
	 * Fetches a reference to this driver's Doctrine record or
	 * triggers an error if this is not a Doctrine driver
	 * 
	 * @access public
	 * @return &DoctrineRecord
	 */	
	function &getDoctrineRecord();
	
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
	 * Returns true if this Doctrine table is searchable.
	 *
	 * @access public
	 * @return bool True if this is searchable.
	 */
	function isSearchable();

	/**
	 * Does this table use versioning?
	 *
	 * @access public
	 * @return bool
	 */
	function isVersionable();

	/**
	 * Returns true if table is a tree 
	 * 
	 * @access public
	 * @return void
	 */
	function isTree();

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
	 * Return the total number of pages if the form is paginated according to current page limit.
	 *
	 * Optionally, pass a limit and it'll tell you how many pages that would make. NOTE: this doesn't
	 * actually work.
	 * 
	 * @access public
	 * @param int $limit. (default: null)
	 * @return int Total page count.
	 */
	function getPageCount($limit=null);
	
	/**
	 * Set the current page number. This is used by the Formz object to set pagination based
	 * on GET parameters.
	 * 
	 * @access public
	 * @param int $pageNumber
	 * @return void
	 */
	function setPage($pageNumber);
	
	/**
	 * Set record per page for pagination on this Formz object. If none is set, will fall back to
	 * default set in config.yaml.
	 * 
	 * @access public
	 * @param int $limit
	 * @return void
	 */
	function setLimit($limit);

	/**
	 * Returns true if this form uses pagination.
	 * 
	 * @access public
	 * @return bool
	 */
	function isPaginated();
	
	/**
	 * Enable pagination on this form.
	 * 
	 * @access public
	 * @param boolean $value. (default: true)
	 * @return void
	 */
	function setPaginated($value = true);

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
