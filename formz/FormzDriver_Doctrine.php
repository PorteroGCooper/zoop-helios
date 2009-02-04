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
 * A Doctrine driver library for formz.
 *
 * This Doctrine driver requires Doctrine version 1.1+
 *
 * @ingroup Formz
 * @ingroup FormzDriver
 * 
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class FormzDriver_Doctrine extends FormzDriver {

	// Doctrine objects associated with this form.
	protected $table;
	protected $tablename;
	protected $_query            = null;
	protected $_record           = null;

	// get rid of this.
	protected $_tableAlias       = null;

	// pagination variables
	protected $_pageNumber       = 1;
	protected $_pageLimit        = null;
	protected $_pager            = null;
	protected $_paginated        = false;
	
	// sorting
	protected $sorted            = false;
	
	// keeping track of joins
	protected $_joinTables       = array();
	protected $_joinCount        = 0;
	
	// search variables
	protected $_searchString     = null;
	protected $_searchFields     = null;
	
	// value constraints for creating and updating records.
	protected $_constraints      = array();
	
	// nested set & tree variables
	protected $_parentRecordName = null;
	protected $_parentRecord     = null;
	protected $_tree             = null;



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
	function __construct($tablename) {
		$this->tablename = $tablename;
		$this->table = Doctrine::getTable($this->tablename);
		$this->_joinTables['@this'] = 'this';
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
	
	/**
	 * Return an array of natively searchable fields on this form.
	 * 
	 * @access public
	 * @return array
	 */
	function getSearchableFields() {
		if (!$this->isSearchable()) return;
		return $this->table->getTemplate('Doctrine_Template_Searchable')->getPlugin()->getOption('fields');
	}
	
	/**
	 * Set a list of searchable fields for this formz object.
	 * 
	 * @access public
	 * @param array $fields
	 * @return void
	 */
	function setSearchFields($fields) {
		$relations = $this->getTableRelations();

		foreach ($fields as $field) {
			$chunks = explode('.', $field);
			if (count($chunks) > 2) {
				trigger_error('Formz has trouble searching relations more than one level deep... too many dots.');
				return;
			} else if (count($chunks) == 1) {
				// this is a searchable field on this table.
				$this->_searchFields[$this->_joinTables['@this']][] = $chunks[0];
			} else {
				// this is a searchable field on a relation.
				$rel_alias = $chunks[0];
				
				// find the relation.
				foreach ($relations as $_key => $_val) {
					if ($_val['alias'] == $rel_alias) {
						$rel_name = $_key;
						break;
					}
				}
				
				// skip this if there's no relation for this field name.
				if (!isset($rel_name) || !isset($relations[$rel_name])) continue;
				
				// make sure there's a join for this search
				if (!isset($this->_joinTables[$rel_alias])) $this->_joinTables[$rel_alias] = 'j' . $this->_joinCount++;
				
				// make sure there's a section in the search fields for this alias.
				$join_alias = $this->_joinTables[$rel_alias];
				if (!isset($this->_searchFields[$join_alias])) $this->_searchFields[$join_alias] = array();
				
				// add a search field for the join alias we just set up.
				$this->_searchFields[$join_alias][] = $chunks[1];
			}
		}
	}
	
	/**
	 * Return all local values for this record.
	 *
	 * This will not return values for relations. Get those with getRecordRelations.
	 *
	 * @see formz_doctrineDB::getRecordRelations
	 * @param mixed
	 * @return array
	 */
	function getData($return_formz = false) {
		if (!$this->_record) return null;
		$data = $this->_record->toArray();
		
		// THIS IS GHETTO HAX.
		foreach ($this->table->getRelations() as $_key => $_val) {
			if (isset($data[$_key])) {
				unset($data[$_key]);
			}
		}

		if ($return_formz) {
			foreach ($this->getRecordRelations() as $relation) {
				$data[] = new Formz($relation);
			}
		} else {
			$data = $data + $this->getRecordRelationsValues();
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
	 * Get the value for a field, or a field on a relation.
	 *
	 * @code
	 *    $form->getValue('username'); // returns $form->record->username;
	 *    $form->getValue('Person.first_name'); // returns $form->record->Person->first_name;
	 * @endcode
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
				$val = $this->_record;
			}
			
			while ($field = array_shift($chunks)) {
				if (!$val instanceof Doctrine_Record) return null;
				$val = $val->$field;
			}
			return $val;
		}
		
		if (isset($this->record($id)->$fieldname)) return $this->record($id)->$fieldname;
		else return null;
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
		
		$this->_applyJoinsToQuery();
		$this->_applyQueryConstraints();
		
		if ($this->isTree()) {
			return $this->_executeTree();
		} else {
			return $this->query()->execute()->toArray();
		}
	}
	
	protected function _applyJoinsToQuery() {
		$this_alias = $this->_joinTables['@this'];
		
		foreach ($this->_joinTables as $name => $alias) {
			if ($name == '@this') continue;
			$this->query()->leftJoin($this_alias . '.' . $name . ' ' . $alias);
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
		// apply user defined constraints
		if ($this->getConstraints()) {
			$this->_applyUserConstraintsToQuery();
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
		if (!isset($this->_searchString) || empty($this->_searchString)) return;
		
		// join on any necessary relations.
		// make sure fields are searched.
		$search_like = array();
		foreach ($this->_searchFields as $alias => $fields) {
			foreach ($fields as $field) {
				$search_like[] = $alias . '.' . $field . ' LIKE "%' . $this->_searchString . '%"';
			}
		}
		$where = '(' . implode(' OR ', $search_like) . ')';
		$this->query()->addWhere($where);
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
			$this->_record = new $classname();
			if ($this->getConstraints()) {
				$this->_record->fromArray($this->getConstraints());
			}
		} elseif ($this->getConstraints()) {
			// add $id as a constraint...
			$this->addConstraint($this->getIdField(), $id);
			$record = $this->_applyUserConstraintsToQuery()->fetchOne();
			
			// if you didn't find one, return.
			if (!$record && $record !== 0) return null;
			
			$this->type = 'record';
			$this->_record = $record;
		} elseif ($record = Doctrine::getTable($this->tablename)->find($id)) {
			$this->type = 'record';
			$this->_record = $record;
		} else {
			$id = null;
		}
		return $id;
	}
	
	protected function record($id = null) {
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
		if ($id && isset($this->_record)) {
			return $this->_record;
		} else {
			$this->getRecord($id);
			return $this->_record;
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
		
		if ($this->getConstraints()) {
			$values = array_merge($values, $this->getConstraints());
		}
		
		foreach ($values as $key => $val) {
			$this->_record->$key = $val;
		}

		if ($this->isTree()) {
			if ($this->hasParentRecord()) {
				$parent = $this->getParentRecord();
				$this->_record->getNode()->insertAsLastChildOf($parent);
			} else {
				$this->_record->root_id = 1;
				$this->tree()->createRoot($this->_record);
			}
		} else {
			try {
				$this->_record->save();
			} catch(Doctrine_Validator_Exception $e) {
				$validation_errors = $this->_record->getErrorStack();
				// getErrorStack() returns an object. Make an array of errors we can use.
				foreach($validation_errors as $field_name => $error_codes) {
					$errors[$field_name] = $error_codes;
					foreach ($error_codes as $code) {
						trigger_error("$field_name should be $code.");
					}
				}
				return false;
			}
		}

		// Get relation classes for the current table.
		$relationships = $this->getTableRelations();

		// Loop through relation classes and get the actual related records.
		foreach ($relationships as $rel => $foo) {

			// skip this one if no relation records are returned.
			if (!is_object($this->_record->$rel)) continue;
			
			// Unlinking related records can happen on each loop. $unlink_rel needs to be *unset*
			// in order to keep records from being unlinked when we don't want them to be.
			// Do *NOT* set $unlink_rel to '' or array()
			if (isset($unlink_rel)) unset($unlink_rel);

			// Obtain and loop through all the related records for the current class ($rel).
			$related_records = $this->_record->$rel->toArray();

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
				$this->_record->unlink($rel, $unlink_rel, true);
			}

		}

		// Link the now filtered submitted relations to their classes.
		// This is not done in foreach($relationships) because that doesn't work when
		// there's nothing currently in the database.
		if (isset($submitted_relations)) {
			foreach ($submitted_relations as $relation_class => $ids) {
				$this->_record->link($relation_class, $ids, true);
			}
		}
		
		return array_shift($this->_record->identifier());
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
			return $this->_record->getNode()->delete();
		} else {
			return $this->_record->delete();
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
		if ($id !== null && !isset($this->_record)) {
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
			$this->_record->link($child_table, $child_record['id']);
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
			
			// get the current relation values to put in the array
			$foreign_class = Doctrine::getTable($relation->getClass());
			if ($getValues) {
				$foreign_values = $this->getTableRelationValues($name);
			} else { 
				$foreign_values = array();
			}
			
			// Grab 'field names' for each side of this relation.
			if ($rel_type == Formz::ONE) {
				$local_alias   = $relation->getLocalFieldName();
				$foreign_field = $relation->getForeignFieldName();
			} else {
				// Formz::MANY requires that the local field be the class name.
				$local_alias   = $name;
				$foreign_field = $foreign_class->getIdentifier();
				
				// Skip if this relation is the join table.
				if (is_array($foreign_field)) continue;
				
				// this *will* be used later by GCooper.
				// $embeddedForm = new Formz($local_alias);
			}
			
			
			// figure out this foreign junk.
			unset($foreign_alias, $foreign_rel_type);
			
			// find the relation on the other end.
			foreach ($relation->getTable()->getRelations() as $f_name => $f_relation) {
				if ($f_relation->getClass() == $this->tablename) {
					$foreign_rel_type = ($f_relation->getType() == Doctrine_Relation::MANY) ? Formz::MANY : Formz::ONE;
					if ($foreign_rel_type == Formz::MANY) {
						$foreign_alias = $f_name;
						$foreign_foreign = $this->getIdField();
					} else {
						$foreign_foreign = $f_relation->getLocal();
					}
					break;
				}
			}
			
/* 			if ($name == 'Orders' || $name == 'AdministratorRole') echo $relation; */
			
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
			
			$ret[$local_alias] = array(
				'alias'            => $name,
				'class'            => $relation['class'],
				'rel_type'         => $rel_type,
				'local_alias'      => $local_alias,
				
				// TODO: foreign field is named wrong!
				'foreign_field'    => $foreign_field,
				'label_field'      => $label_field,
				'owning_side'      => $relation['owningSide'],
				'values'           => $foreign_values,
			);

			if (isset($foreign_foreign)) {
				$ret[$local_alias]['foreign_foreign'] = $foreign_foreign;
			}
			if (isset($foreign_rel_type)) {
				$ret[$local_alias]['foreign_rel_type'] = $foreign_rel_type;
			}
			if (isset($foreign_alias)) {
				$ret[$local_alias]['foreign_alias'] = $foreign_alias;
			}
		}
		
		return $ret;
	}
	
	function getRecordRelations() {
		$data = array();
		foreach ($this->table->getRelations() as $name => $relation) {
			$data[$name] = $this->_record->$name->toArray();
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
				$array = $this->_record->$name->toArray();
				foreach ($array as $value) {
					$data[$name][] = $value[$relation['foreign_field']]; // = $value[$relation['label_field']];
				}
			} else {
				$data[$name] = $this->_record->$name;
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
	 * Returns true if a sort has been applied to this table.
	 * 
	 * @access public
	 * @return void
	 */
	function isSorted() {
		return $this->sorted;
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
		$this->sorted = true;
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
	 * Set a string to search on.
	 * 
	 * @see formz_doctrineDB::setSearchFields()
	 * @access public
	 * @param string $query
	 * @return void
	 */
	function search($query) {
		$this->_searchString = $query;
	}

	/**
	 * Return's this query. If it doesn't exist, create and apply fixed values to it.  
	 * 
	 * @access protected
	 * @return void
	 */
	protected function &query() {
		if (!$this->_query) {
			$this->_query = $this->table->createQuery($this->_joinTables['@this']);
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
	private function &_applyUserConstraintsToQuery() {
		$constraints = $this->getConstraints();
		if ($constraints) {
			$joinCount = 0;
			foreach ($constraints as $key => $value) {
				if (strpos($key, '.') !== false) {
					$relation = array_shift(explode('.', $key));
					$identifier = 'r' . $joinCount;
					$this->query()->leftJoin($this->_joinTables['@this'] . '.' . $relation . ' ' . $identifier);
					$key = $identifier . '.id';
					$joinCount++;
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

		return $this->query();
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
/*
	function setFixedValues($array) {
		$this->fixedValues = $array;
	}
*/
	
	/**
	 * Adds new fixed value(s) to the existing ones.
	 * 
	 * To add a SQL IN (x, y) style constraint, pass an array of values as 'val'.
	 *
	 * @param array Fixed value to add, in the form array('key' => 'val')
	 * @access public
	 * @return void
	 */
	function addConstraint($name, $value) {
		$this->_constraints[$name] = $value;
	}
	
	function removeConstraint($name) {
		if (isset($this->_constraints[$name])) {
			unset($this->_constraints[$name]);
		}
	}

	/**
	 * Returns an array of fixed values to use when selecting as well as creating/updating 
	 * 
	 * @param mixed $key 
	 * @access public
	 * @return void
	 */
	function getConstraints($key = false) {
		if ($key) {
			if (isset($this->_constraints[$key])) {
				return $this->_constraints[$key];
			} else {
				return null;
			}
		} else {
			return $this->_constraints;
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
}
