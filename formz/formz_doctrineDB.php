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
 * This Doctrine driver requires Doctrine version 1.1+
 *
 * @ingroup forms
 * @ingroup Formz
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class formz_doctrineDB implements formz_driver_interface {

	/**
	 * Doctrine table object associated with this formz.
	 *
	 * @var
	 */
	protected $table;
	protected $tablename;
	protected $_query        = null;
	protected $record        = null;
	
	protected $_pageNumber   = 1;
	protected $_pageLimit    = null;
	protected $_pager        = null;
	protected $_paginated    = false;
	protected $_searchToken  = null;
	protected $_searchTables = null;
	protected $_searchTablesets = null;
	protected $_tableAlias   = null;
	
	/**
	 * Values that are fixed for both querying and Create and Update 
	 * 
	 * @var array
	 * @access public
	 */
	protected $fixedValues = array();

	/**
	 * Set only when using nested sets (trees) 
	 * 
	 * @var mixed
	 * @access protected
	 */
	protected $_parentRecordName = null;
	protected $_parentRecord = null;
	protected $_tree = null;

	/**
	 * True if current form is soft deletable
	 *
	 * @var bool
	 * @access private
	 */
	protected $softdeletable = false;


	/**
	 * doctrineDB constructor.
	 *
	 * Can be used to instantiate the object, or if passed a type, handle the
	 * retrival of information from the database.
	 *
	 * @param string $tablename table name in the database
	 * @return void
	 * @access public
	 */
	function formz_doctrineDB($tablename) {
		$this->tablename = $tablename;
		$this->table = Doctrine::getTable($this->tablename);
	}
	
	/**
	 * Return the driver type for this formz driver.
	 *
	 * @access public
	 * @return int Formz::DoctrineDB or Formz::FormDB const.
	 */
	function getType() {
		return Formz::DoctrineDB;
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
	
	function getData($return_formz = false) {
		if(!$this->record) return null;
		$data = $this->record->toArray();
		
		if (!$return_formz) {
			$data = $data + $this->getRecordRelationsValues();
		} else {
			foreach ($this->getRecordRelations() as $relation) {
				$data[] = new Formz($relation);
			}
		}
		
		return $data;
	}

	/**
	 * Return the slug field for this table
	 * 
	 * @return $string slug field
	 */
	function getSlugField() {
		return $this->table->getTemplate('Doctrine_Template_Sluggable')->getOption('name');
	}

	/**
	 * Return the version field for this table
	 *
	 * Returns 'version' if not Doctrine 1.1 or greater
	 *
	 * @return string version field
	 */
	function getVersionField() {
		if ((float)substr(Doctrine::VERSION, 0, 3) >= 1.1) {
			return $this->table->getTemplate('Doctrine_Template_Versionable')->getOption('versionColumn');
		} else {
			return 'version';
		}
	}

	/**
	 * Returns the timestamp fields (created, updated) for this table
	 * 
	 * @return array Doctrine timestamp fields: array('created_field', 'updated_field')
	 */
	function getTimestampFields() {
		$options = $this->table->getTemplate('Doctrine_Template_Timestampable')->getOptions();
		return array($options['created']['name'], $options['updated']['name']);
	}

	/**
	 * Return the soft delete field for this table
	 * 
	 * @return string soft delete field
	 */
	function getSoftDeleteField() {
		return $this->table->getTemplate('Doctrine_Template_SoftDelete')->getOption('name');
	}

	/**
	 * getValue
	 * gets the value from the record object and returns it.
	 *
	 * @param string $fieldname
	 * @access public
	 * @return mixed $value
	 */
	function getValue($fieldname, $id = null) {
		if (strchr($fieldname, '.')) {
			$chunks = explode('.', $fieldname);
			
			if ($id !== null) {
				$val = $this->record($id);
			} else {
				$val = $this->record;
			}
			
			while ($field = array_shift($chunks)) {
				$val = $val->$field;
			}
			return $val;
/*

			if ($relation_record instanceof Doctrine_Record) {
				while ($r = $r->$chunks[0]) {
					
				}
				return $relation_record->$field;
			} else if (count((array)$relation_record)) {
				$retVal = array();
				foreach ($relation_record as $key => $value) {
					$retVal[] = $value->$field;
				}
				return $retVal;
			}
*/
		}
		
		if (isset($this->record($id)->$fieldname)) return $this->record($id)->$fieldname;
		else trigger_error("No value is set for the field: $fieldname");
	}

	/**
	 * getRecords
	 * Requests the necessary records from the database (as would be used in a listing).
	 * If a query is set, it will execute that query.
	 * If the table is a tree, it will either list the root nodes, or the children nodes
	 * depending on if a parent node is set
	 *
	 * @todo Fix pagination for tree data structures. This will currently break if you try to
	 * paginate a Tree.
	 *
	 * @param int $limit
	 * @access public
	 * @return array
	 */
	function getRecords($limit = null) {
		/* if ($limit !== null) $this->setParam("limit", $limit); */
		
		$this->_applyQueryConstraints();
		
		if ($this->isTree()) {
			return $this->_executeTree();
		} else {
			return $this->query()->execute()->toArray();
		}
	}
	
	/**
	 * Apply all query constraints in order
	 *
	 * This method grabs the query for this form's table and applies all constraints that
	 * have been specified, including fixed values, search tokens, pagination and page limits.
	 * In order for page limits to be enforced correctly, the constraints must be applied in
	 * the specified order (fixed values -> search token / relations -> pagination -> page limit)
	 *
	 * @access protected
	 */
	protected function _applyQueryConstraints() {
		// apply any fixed values
		if ($this->getFixedValues()) {
			$this->_applyFixedValuesToQuery();
		}
		// add search constraints
		if ($this->isSearchable() && !$this->isTree()) {
			$this->_applySearchToQuery();
		}
		// paginate this query
		if ($this->isPaginated()) {
			$this->_paginateQuery();
		} else if ($this->_pageLimit) {
			// if the form isn't paginated, but has a limit set, apply that limit.
			$this->_applyLimitToQuery();
		}
		
		// everything else will automatically do a findAll() style query
	}
	
	protected function _executeTree() {
		if (!$this->isTree()) {
			trigger_error('Unable to execute tree query on non-tree table.');
			return;
		}

		$this->tree()->setBaseQuery($this->query());
		$nodes = array();		
		if ($this->hasParentRecord()) {
			if ($parent = $this->getParentRecord()) {
				if ($children = $parent->getNode()->getChildren()) {
					$id_field = $this->getIdField();
					foreach ($children->toArray() as $key => $child) {
						$nodes[$key] = $this->table->find($child[$id_field])->toArray();
					}
				}
			}
		} else {
			$nodes = $this->getRootNodes();
		}
		$this->tree()->resetBaseQuery();
		
		return $nodes;
	}
	
	/**
	 * Apply search parameters to the query constraints
	 *
	 * This method grabs all table search sets that are stored and loops through them to
	 * apply parameters for the given search token.  Table search sets take the form of a
	 * 2D array where the top array is indexed with the name of the table/relation to search on
	 * and its value is an array of strings referencing which fields in that relation to search on.
	 *
	 * @access protected
	 */
	protected function _applySearchToQuery() {
		if (!isset($this->_searchToken) || empty($this->_searchToken)) return;

		foreach($this->getSearchTablesets() as $tableset => $search_fields) {
			foreach($search_fields as $search_field) {
				if (strtolower($tableset) === strtolower($this->getTableAlias())) {
					$this->query()->select('*') // try to optimize this, remove the select *?
								  ->from($tableset . ' c')
								  ->where('c.' . $search_field . ' like "%' . $this->_searchToken . '%"');
				} else {
					$this->query()->leftJoin('c.' . $tableset . ' b')
								  ->orWhere('b.' . $search_field . ' like "%' . $this->_searchToken . '%"');
				}
			}
		}
		
		
	}
	
	/**
	 * Applies a page limit to the query results
	 *
	 * If we've got a page limit for this query's results, go ahead and apply it
	 *
	 * @access protected
	 */
	protected function _applyLimitToQuery() {
		$this->query()->limit($this->_pageLimit);
	}

	/**
	 * When Working with Trees, get the root nodes in the tree 
	 * 
	 * @access private
	 * @return array Set of root node records (in array form)
	 */
	private function getRootNodes() {
		if ($this->isTree()) {
			return $this->table->findByLevel(0)->toArray();
		} else {
			return array();
		}
	}
	
	protected function &tree() {
		if (!$this->isTree()) {
			trigger_error('Unable to return tree on non-tree database.');
			return null;
		}
		
		if (!$this->_tree) {
			$this->_tree = $this->table->getTree();
		}
		return $this->_tree;
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
		} elseif ($this->getFixedValues()) {
			$record = $this->_applyFixedValuesToQuery()->fetchOne();
			
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
	
	protected function &record($id = null) {
		if ($id === null) {
			if ($this->_record) {
				return $this->_record;
			} else {
				trigger_error('No ID or record found');
				return null;
			}
		}
		if ($record = Doctrine::getTable($this->tablename)->find($id)) {
			return $record;
		} else {
			return null;
		}
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
		$records = $this->table->createQuery()
				->select($id_field)
				->addWhere('slug = ?', $slug)
				->execute()
				->toArray();
		if (count($records)) {
			return $records[0][$id_field];
		} else {
			return null;
		}
	}


	function &getDoctrineQuery() {
		return $this->query();
	}


	function &getDoctrineRecord($id = false) {
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

		if ($this->isTree()) {
			if ($this->hasParentRecord()) {
				$parent = $this->getParentRecord();
				$this->record->getNode()->insertAsLastChildOf($parent);
			} else {
				$this->record->root_id = 1;
				$this->tree()->createRoot($this->record);
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

			if (isset($submitted_relations[$rel])) {
				if (!empty($submitted_relations[$rel])) {
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
				} else {
					// No related records submitted. Giving doctrine an empty array/value removes all relations.
					$unlink_rel = array();
				}
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
		// get the record we want to delete...
		if ($id !== null) {
			if (!$this->getRecord($id)) {
				trigger_error("Unable to find record: " . $id);
				return false;
			}
		}
		
		if ($this->isTree()) {
			return $this->record->getNode()->delete();
		} else {
			return $this->record->delete();
		}
	}

	/**
	 * Create and attach a relation to the current record
	 *
	 * @param array $values values representing the new relation
	 * @access public
	 * @return void
	 */
	function createRelation($values, $id = null) {
		// get the record we want to save...
		if ($id !== null && !isset($this->record)) {
			if (!$this->isSluggable() && !$this->getRecord($id)) {
				trigger_error("Unable to initialize record: " . $id);
				return false;
			} else if ($this->isSluggable() && !$this->getRecordBySlug($id)) {
				trigger_error("Unable to initialize record: " . $id);
				return false;
			}
		}

		if (isset($values['child_table'])) {
			$child_table = $values['child_table'];
			unset($values['child_table']);
		}

		if (isset($values['parent_table'])) {
			$parent_table = $values['parent_table'];
			unset($values['parent_table']);
		}
		
		unset($values['id']);
		unset($values['parent_id']);
		
		$child_record = new $child_table();
		foreach ($values as $key => $val) {
			if (!is_array($val)) {
				$child_record->$key = $val;
			}
		}

		if ($child_record->save()) {
			$this->record->link($child_table, $child_record['id']);
		} else {
			trigger_error('Unable to save child record.');
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
	 * Get foreign fields that should be immutable for this form
	 *
	 * @param string $foreign_class name of the foreign relation
	 * @access public
	 * @return void
	 */
	function getImmutableForeignFields($foreign_class) {
		$relations = Doctrine::getTable($this->tablename)->getRelations();

		$retVal = array();
		
		foreach($relations as $name => $relation) {
			if ($name == $foreign_class) {
				$retVal[] = $relation->getLocalFieldName();
			}
		}

		return $retVal;
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
			$foreign_id_field = null;
			
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
				$local_field   = $name;
				$foreign_field = $foreign_class->getIdentifier();
				
				// This is the local field from the point of view of the other end of the relation
				$foreign_id_field = $relation->getLocalFieldName();
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
			
			if ($foreign_id_field !== null) {
				$ret[$local_field]['foreign_id_field'] = $foreign_id_field;
			}
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

	function getRecordRelationsValues($field = null, $searchToken = null) {
		$data = array();

		if ($field !== null) {
			$relations = array($this->getTableRelation($field));
		} else {
			$relations = $this->getTableRelations();
		}
		
		foreach ($relations as $name => $relation) {
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
	 * Returns true if this Doctrine table is searchable.
	 *
	 * @access public
	 * @return bool True if this is searchable.
	 */
	function isSearchable() {
		return $this->table->hasTemplate('Doctrine_Template_Searchable');
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
		$this->query()->orderBy("$fieldname $direction");
	}

	/**
	 * Returns the page count for this form (aka the index of the last page).
	 *
	 * Passing a limit (optional param) does nothing. Yet.
	 * 
	 * @access public
	 * @param int $limit. (default: null)
	 * @return int
	 */
	function getPageCount($limit=null) {
		/*
		if ($limit===null) {
			if ($this->_pageLimit===null) {
				$limit = $this->_pageLimit;
			} else {
				$limit = Config::get("zoop.formz.paginate.limit");
			}
		}
		*/

		if ($this->getPager()->getExecuted()) {
			return $this->getPager()->getLastPage();
		} else {
			// TODO: figure out the else clause on this...
		}
	}

	/**
	 * Set the current page. Used by Formz object to pass in GET params.
	 * 
	 * @access public
	 * @param int $pageNumber
	 * @return void
	 */
	function setPage($pageNumber) {
		$this->_pageNumber = $pageNumber;
	}
	
	/**
	 * Set the records returned per page.
	 * 
	 * @access public
	 * @param int $limit
	 * @return void
	 */
	function setLimit($limit) {
		$this->_pageLimit = $limit;
	}

	/**
	 * Get the table alias for this formz object
	 *
	 * @access public
	 * @return string $tableAlias
	 */
	protected function getTableAlias() {
		return $this->_tableAlias;
	}

	/**
	 * Set the table alias for this formz object
	 *
	 * @access public
	 * @param string $tableAlias
	 */
	function setTableAlias($tableAlias) {
		$this->_tableAlias = $tableAlias;
	}

	/**
	 * Set the token for searching
	 * 
	 * @access public
	 * @param string $token
	 * @return void
	 */
	function setSearchToken($token) {
		$this->_searchToken = $token;
	}

	/**
	 * Add table(s) for searching.
	 *
	 * Accepts either a single table name (string) or an array of table names to search on.
	 * 
	 * @access public
	 * @param mixed $tablename
	 * @return void
	 */
	function addSearchTable($tablename) {
		if ($this->_searchTables === null) {
			$this->_searchTables = array();
		}
		
		foreach ((array)$tablename as $name) {
			$this->_searchTables[] = $name;
		}
	}

	/**
	 * Get tables for searching
	 * 
	 * @access public
	 * @return void
	 */
	function getSearchTables() {
		return $this->_searchTables;
	}

	/**
	 * Add table sets for searching.
	 *
	 * Accepts either a single table set (tablename => search fields) or an array of table sets
	 * 
	 * @access public
	 * @param mixed $tableset(s)
	 * @return void
	 */
	function addSearchTableset($tablesets) {
		if ($this->_searchTablesets === null) {
			$this->_searchTablesets = array();
		}

		foreach ((array)$tablesets as $key => $tableset) {
			$this->_searchTablesets[$key] = $tableset;
		}
	}

	/**
	 * Get table sets for searching
	 * 
	 * @access public
	 * @return array table sets
	 */
	function getSearchTablesets() {
		return $this->_searchTablesets;
	}

	/**
	 * Return's this query. If it doesn't exist, create and apply fixed values to it.  
	 * 
	 * @access protected
	 * @return void
	 */
	protected function &query() {
		if (!$this->_query) {
			$this->_query = $this->table->createQuery('t');
		}

		return $this->_query;
	}

	/**
	 * Apply fixed values to the query
	 *
	 * Essentially, specify the key/value requirements in the WHERE clause of the query
	 *
	 * @access private
	 */
	private function &_applyFixedValuesToQuery() {
		$fixed = $this->getFixedValues();
		if ($fixed) {
			foreach ($fixed as $key => $value) {
				if (strpos($key, '.') !== false) {
					$relation = array_shift(explode('.', $key));
					$this->query()->leftJoin('t.' . $relation . ' r');
					$key = 'r.id';
				}
				if(is_null($value)) {
					$this->query()->addWhere($key . ' IS NULL');
				} else if (is_array($value)) {
					$this->query()->whereIn($key, $value);
				} else {
					$this->query()->addWhere("$key = ?", $value);
				}
			}
		}

		return $this->query;
	}
	
	/**
	 * Execute pagination to the query object for this doctrine table.
	 * 
	 * @access protected
	 * @return Doctrine_Query object
	 */
	private function _paginateQuery() {
		$this->getPager()->execute();
		$this->query = $this->getPager()->getQuery();
		return $this->query;
	}
	
	/**
	 * Get the Doctrine Pager object associated with this table. If this is the first call, create one.
	 * (singleton style).
	 * 
	 * @access protected
	 * @return Doctrine_Pager object
	 */
	private function &getPager() {
		if (!$this->isPaginated()) return null;
		
		if ($this->_pager === null) {
			$currentPage = $this->_pageNumber;
			if ($this->_pageLimit===null) {
				$resultsPerPage = Config::get('zoop.formz.paginate.limit');
			} else {
				$resultsPerPage = $this->_pageLimit;
			}
			$this->_pager = new Doctrine_Pager($this->query(), $currentPage, $resultsPerPage);
		}
		return $this->_pager;
	}
	
	/**
	 * Returns true if this form uses pagination.
	 * 
	 * @access public
	 * @return bool
	 */
	function isPaginated() {
		return $this->_paginated;
	}
	
	/**
	 * Enable pagination on this form.
	 * 
	 * @access public
	 * @param boolean $value. (default: true)
	 * @return void
	 */
	function setPaginated($value = true) {
		$this->_paginated = $value;
	}

	/**
	 * Set a fixed value to be used when seleting as well as updating 
	 *
	 * To add a SQL IN (x, y) style constraint, pass an array of values as 'val'.
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
	 * To add a SQL IN (x, y) style constraint, pass an array of values as 'val'.
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
	 * @access private
	 * @return bool
	 */
	private function hasParentRecord() {
		if ($this->_parentRecordName) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Add a parent record (by name), remove the current one if it exists.
	 * 
	 * @access public
	 * @param string $parent
	 * @return void
	 */
	function setParentRecordName($parent) {
		$this->_parentRecordName = $parent;
		$this->_parentRecord = null;
	}

	/**
	 * Get the parent node for this form 
	 * Used when using nested sets
	 * 
	 * @access private
	 * @return object
	 */
	private function &getParentRecord() {
		if ($this->hasParentRecord()) {
			if (!$this->_parentRecord) {
				$this->_parentRecord = $this->table->find($this->_parentRecordName);
			}
			return $this->_parentRecord;
		} else {
			return null;
		}
	}
	
/////////////////////////////////////////////////////////
// LEGACY CODE:                                        //
// THESE METHODS PROB'LY DON'T BELONG IN THE DRIVER... //
/////////////////////////////////////////////////////////
	
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
	function setValidationOptions($fieldname, $value) {
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
	function setValidationOption($fieldname, $innername, $value) {
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
}