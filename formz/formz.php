<?php

/**
 * Formz is the next generation Zoop form controller.
 *
 * Formz relies on either a Doctrine or a Zoop forms database backend.
 * It creates an easy to use link between a table and a html list,
 * or a record and an html form. It supports full validation of types
 * and requirements.
 *
 * @author Justin Hileman <justin@justinhileman.info>
 * @package Formz
 * @access public
 * @copyright Supernerd LLC and Contributors
 *
 */
class Formz {

	/**
	 * Constants for defining relationships.
	 *
	 */
	const ONE = 0;
	const MANY = 1;

	/**
	 * Formz database driver object.
	 *
	 * At this point, this is either a formDB or a doctrineDB connector.
	 * 
	 * @access private
	 */
	protected $_driver;

	var $tablename;
	var $type;	
	var $title;
	var $zone;
	var $callback;
	
	/**
	 * Private fields variable. Used by formz::getFields()
	 * @var array
	 * @access private
	 */
	protected $_fields = array();
	
	/**
	 * Private relation fields variable. Used by formz::getFields()
	 * @var array
	 * @access private
	 */
	protected $_relation_fields = array();
	
	/**
	 * Default sort direction for this Formz object
	 *
	 * @see formz::setDefaultSort
	 * @var string
	 * @access private
	 */
	protected $_order = array();
	
	/**
	 * Default sort field for this Formz object
	 *
	 * @see formz::setDefaultSort
	 * @var string
	 * @access private
	 */
	protected $_defaultSortField = null;

	/**
	 * Default sort direction for this Formz object
	 *
	 * @see formz::setDefaultSort
	 * @var string
	 * @access private
	 */
	protected $_defaultSortDirection = 'ASC';

	/**
	 * Values that are fixed for both querying and Create and Update 
	 * 
	 * @var array
	 * @access public
	 */
	var $fixedValues = array();

	/**
	 * Actions for this form (Actions are things like 'save' and 'cancel')
	 *
	 * @access private
	 * @see Formz::addAction
	 * @see Formz::getActions
	 */
	protected $_formActions = array();

	/**
	 * Actions for the list view for this form (List actions might be 'add' or 'sort')
	 *
	 * @access private
	 * @see Formz::addListAction
	 * @see Formz::getListActions
	 */
	protected $_formListActions = array();

	/**
	 * Embedded formz objects
	 *
	 * @access protected
	 */
	protected $_embeddedFormz = array();

	var $errors = array();
	var $editable = true;

	var $record_id;
	var $slug_field;
	
	protected $timestampable = false;
	protected $sortable = true;
	protected $versionable = false;

	/**
	 * Formz constructor. Returns an object implementing the Formz interface.
	 *
	 * @param string $tablename  table name in the database
	 * @param string $type  OPTIONAL, can be 'list', 'search' or 'record'
	 * @param mixed  $int  OPTIONAL, if $type = 'list' than an int that represents the limit,
	 * if $type = 'record' then required and is the id of the record
	 * @return object implementing Formz interface
	 * @access public
	 */
	function Formz($tablename, $driver_type = 'default') {
		$this->valid_properties = get_class_vars(get_class($this));

/*
		@TODO finish clone logic...
		
		// clone the Formz object passed
		if (is_object($tablename) && get_class($tablename) == 'Formz') {
			$clone = $tablename;
			$tablename = $clone->getTablename();
			
		}
		
		else {
		
		}
*/
		
		// get the default Formz ORM driver
		if ($driver_type == 'default') $driver_type = Config::get('zoop.formz.driver');

		$this->tablename = $tablename;
		$this->type = Config::get('zoop.formz.type','list');
		
		switch ($driver_type) {
			case 'doctrine':
				$this->_driver = new Formz_DoctrineDB($tablename);
				break;
			case 'forms':
				$this->_driver = new Formz_FormDB($tablename);
				break;
			default:
				trigger_error($driver_type . " is not a valid Formz type.");
				break;

		}
		
		// grab the default field definitions, we'll mess with 'em later :)
		$this->_fields = $this->_driver->getFields();
		
		// set the sort field and order
		$sort = $this->getSortField();
		if ($sort) {
			$this->sort($sort, $this->getSortOrder());
		}
	}
	
	/**
	 * Return a new record object.
	 *
	 * @return mixed New record id.
	 */
	function newRecord() {
		return $this->getRecord('new');
	}
	
	
	/**
	 * getRecord
	 * Requests the requested record from the database (as would be used in a record).
	 *
	 * @param mixed $id
	 * @access public
	 * @return int Record ID
	 */
	function getRecord($id = 'new') {
		$this->record_id = $this->_driver->getRecord($id);
		if ($this->record_id !== null) {
			$this->type = 'record';
		}
		return $this->record_id;
	}
	
	/**
	 * getRecordBySlug
	 * Requests the requested record from the database (as would be used in a record).
	 *
	 * @param mixed $id
	 * @access public
	 * @return int Record ID
	 */
	function getRecordBySlug($slug) {
		$this->record_id = $this->_driver->getRecordBySlug($slug);
		if ($this->record_id !== null) {
			$this->type = 'record';
		}
		return $this->record_id;
	}
	
	/**
	 * Return a record id for a given slug
	 *
	 * @access public
	 * @param string $slug
	 * @return int Record id.
	 */
	function getRecordIdBySlug($slug) {
		return $this->_driver->getRecordIdBySlug($slug);
	}
	
/*
	function getDoctrineRecord($id = false) {
		return $this->_driver->getDoctrineRecord($id);
	}
*/
	
	function getRecords($search = false) {
		// if we're doing a sort, do it!
		if ($this->_defaultSortField != null) {
			$this->sort($this->_defaultSortField, $this->_defaultSortDirection);
		}
		return $this->_driver->getRecords($search);
	}
	
	/**
	 * Save a record.
	 *
	 * Save the current record (or, optionally, any record with an ID) to the database.
	 * Takes the current record and writes its content to the database.
	 * If the record is new it will insert it, if not it will update it.
	 *
	 * @param array $POST Array of col/value pairs to be saved.
	 *   This is optional. If unspecified, will use the getPost(); data.
	 * @param int $id Record ID (optional)
	 * @access public
	 * @return int Saved record id.
	 */
	function saveRecord($values = false, $id = null) {
		if ($values === false) {
			$values = getPost();
		}

		$id_field = $this->getIdField();

		if ($id === null) {
			if (isset($this->$id_field)) {
				$id = $this->$id_field;
			} else if (isset($values[$id_field])) {
				$id = $values[$id_field];
				
				// strip out $values[$id_field] = 'new' to keep it from saving to db...
				if ($id == 'new') {
					unset($values[$id_field]);
				}
			}
			
			// fail if we still don't have an id...
			if ($id === null) {
				trigger_error("Formz element does not have a current record to save.");
				return null;
			}
		}
		
		// only save things that should be saved (no fake fields).
		$save = array();

		$field_names = $this->getFields();
		
		foreach ($field_names as $name => $field_info) { 
			if (isset($values[$name])) {
			
				if ($field_info['type'] == 'relation') {
					if ($field_info['rel_type'] == Formz::MANY) {
						$save['relations'][$field_info['relation_alias']] = $values[$name];
					} else {
						$save[$name] = $values[$name];
					}
				} else {
					$save[$name] = $values[$name];
				} 
			}
		}

		return $this->_driver->saveRecord($save, $id);
	}
	
	/**
	 * Delete a record.
	 *
	 * @param int $id Record ID (optional)
	 * @access public
	 * @return int Saved record id.
	 */
	function destroyRecord($id = null) {
		if ($id === null) {
			if (isset($this->id)) {
				$id = $this->id;
			}
			
			// fail if we still don't have an id...
			if ($id === null) {
				trigger_error("Formz element does not have a current record to destroy.");
				return null;
			}
		}
		return $this->_driver->destroyRecord($id);
	}
	
	/**
	 * Set form field sort order.
	 *
	 * @param mixed $sort Field sort order as an array or comma separated string.
	 */
	function setOrder($sort) {
		if (!is_array($sort)) $sort = explode(',', $sort);
		$this->_order = $sort;
		
		$newfields = array();
		
		// rearrange fields
		foreach ($this->_order as $key) {
			$key = trim($key);
			
			if (isset($this->_fields[$key])) {
				$newfields[$key] = $this->_fields[$key];
				unset($this->_fields[$key]);
			}
		}
		if (count($newfields)) {
			$oldfields = $this->_fields;
			$this->_fields = $newfields + $oldfields;
		}
	}
	
	function getIdField() {
		return $this->_driver->getIdField();
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
	 * Set a fixed value to be used when seleting as well as updating 
	 * 
	 * @param mixed $array 
	 * @access public
	 * @return void
	 */
	function setFixedValues($array) {
		$this->fixedValues = $array;
		$this->_driver->setFixedValues($this->getFixedValues());
	}

	/**
	 * Append a fixed value to the fixed values 
	 * 
	 * @see self::setFixedValues
	 * @param mixed $array 
	 * @access public
	 * @return void
	 */
	function addFixedValue($array) {
		$this->fixedValues += $array;
		$this->_driver->setFixedValues($this->getFixedValues());
	}
	
	/**
	 * Get an array of all relevant field information.
	 */
	function getFields($return_relations = true) {
		$fields = $this->_fields;
		$relation_fields = $this->_relation_fields;
		
		// hide the record id by default.		
		$id = $this->_driver->getIdField();
		if (!isset($fields[$id]['display']['type'])) {
			$fields[$id]['display']['type'] = 'hidden';
		}
		
		// default not to show timestampable fields
		if ($this->isTimestampable()) {
			if (isset($fields['created_at'])) {
				if (!isset($fields['created_at']['formshow'])) {
					$fields['created_at']['formshow'] = false;
				}
				if (!isset($fields['created_at']['listshow'])) {
					$fields['created_at']['listshow'] = false;
				}
				if (!isset($fields['created_at']['editable'])) {
					$fields['created_at']['editable'] = false;
				}				
			}
			if (isset($fields['updated_at'])) {
				if (!isset($fields['updated_at']['formshow'])) {
					$fields['updated_at']['formshow'] = false;
				}
				if (!isset($fields['updated_at']['listshow'])) {
					$fields['updated_at']['listshow'] = false;
				}
				if (!isset($fields['updated_at']['editable'])) {
					$fields['updated_at']['editable'] = false;
				}
			}
		}
		
		// Don't show the "deleted" field...
		if ($this->isSoftDeletable()) {
			if (isset($fields['deleted'])) {

				// TODO not sure whether this should hide by default?
				// unset($fields['deleted']);
				
				if (!isset($fields['deleted']['formshow'])) {
					$fields['deleted']['formshow'] = false;
				}
				if (!isset($fields['deleted']['listshow'])) {
					$fields['deleted']['listshow'] = false;
				}
				if (!isset($fields['deleted']['editable'])) {
					$fields['deleted']['editable'] = false;
				}
				
			}
		}
		
		// Don't show the 'slug' field...
		if ($this->isSluggable()) {
			if (isset($fields['slug'])) {
				if (!isset($fields['slug']['formshow'])) {
					$fields['slug']['formshow'] = false;
				}
				if (!isset($fields['slug']['listshow'])) {
					$fields['slug']['listshow'] = false;
				}
				if (!isset($fields['slug']['editable'])) {
					$fields['slug']['editable'] = false;
				}
			}
		}
		
		// Don't show the 'version' field...
		if ($this->isVersionable()) {
			if (isset($fields['version'])) {
				if (!isset($fields['version']['formshow'])) {
					$fields['version']['formshow'] = false;
				}
				if (!isset($fields['version']['listshow'])) {
					$fields['version']['listshow'] = false;
				}
				if (!isset($fields['version']['editable'])) {
					$fields['version']['editable'] = false;
				}
			}
		}

		// Something like this needs to happen here.. This isn't it.. placeholder
		foreach ($this->getFixedValues() as $key => $val) {
			if (isset($fields[$key])) {
				$fields[$key]['editable'] = false;
			}
		}
		
		// mix in info from foreign fields
		foreach ($this->getTableRelations() as $key => $relation) {
			if ($return_relations) {
				$fields[$key]['type'] = 'relation';
				$fields[$key]['relation_alias'] = $relation['alias'];
				$fields[$key]['rel_type'] = $relation['rel_type'];
				if (isset($relation['foreign_id_field'])) {
					// TODO find a much better way of doing this...
					$fields[$key]['foreign_id_field'] = $relation['label_field'];
				}

				if (!isset($fields[$key]['display']['label'])) {
					if (isset($relation_fields[$key]['display']['label'])) {
						$fields[$key]['display']['label'] = $relation_fields[$key]['display']['label'];
					} else {
						$fields[$key]['display']['label'] = Formz::format_label($relation['alias']);
					}
				}
				
				// figure out what field to display for this relation
				if (isset($fields[$key]['relation_label_field'])) {
					$relation_label_field = $fields[$key]['relation_label_field'];
				} else {
					$relation_label_field = $relation['label_field'];
				}

				$values = array();
				foreach ($relation['values'] as $item) {
					$values[$item[$relation['foreign_field']]] = $item[$relation_label_field];
				}
				
				if (isset($relation_fields[$key]['display']['type'])) {
					$fields[$key]['display']['type'] = $relation_fields[$key]['display']['type'];
				} else {
					// decide whether this should be single or multiple select
					if ($relation['rel_type'] == Formz::MANY) {
						$fields[$key]['display']['type'] = 'multiple';
					} else {
						$fields[$key]['display']['type'] = 'select';
					}
				}

				$fields[$key]['values'] = $values;
				
	    		if (isset($relation_fields[$key]['display'])) {
	    			$fields[$key]['display'] = $relation_fields[$key]['display'];
	    		}
	    		
	    		if (!isset($fields[$key]['display']['index'])) {
	    			$fields[$key]['display']['index'] = $values;
	    		}

			} else {
				$fields[$key]['listshow'] = false;
			}
		}

		return $fields;
	}
	
	function getFieldValues($name) {
		if ($relation = $this->getTableRelation($name, true)) {
			// figure out what field to display for this relation
			if (isset($this->_fields[$name]['relationLabelField'])) {
				$relation_label_field = $this->_fields[$name]['relationLabelField'];
			} else {
				$relation_label_field = $relation['label_field'];
			}
			
			$values = array();
			
			foreach($relation['values'] as $item) {
				$values[$item[$relation['foreign_field']]] = $item[$relation_label_field];
			}
			
			return $values;
		} else {
			return false;
		}
		
	}
	
	/**
	 * Get an array of all field data for this form.
	 */
	function getData() {
		return $this->_driver->getData();
	}
	
	
	/**
	 * Return the relation field data (foreign/local fields, etc) for a given relation name.
	 * 
	 * @access public
	 * @param mixed $name A given relation name.
	 * @param bool $getValues Hydrate the array with values? (default: false)
	 * @return array An array of information about the requested relation.
	 */
	function getTableRelation($name, $getValues = false) {
		return $this->_driver->getTableRelation($name, $getValues);
	}
	
	/**
	 * Get an array of relation fields for this form.
	 */
	function getTableRelations($getValues = false) {
		return $this->_driver->getTableRelations($getValues);
	}
	
	/**
	 * getTableRelationLocalFields function.
	 * 
	 * @access public
	 * @return array of local fields
	 */
	function getTableRelationLocalFields() {
		$ret = array();
		
		foreach ($this->_driver->getTableRelations() as $key => $rel) {
			$ret[$rel['local_field']] = $key;
		}
		return $ret;
	}
	
	function getTableRelationForeignFields() {
		$ret = array();
		
		foreach ($this->_driver->getTableRelations() as $key => $rel) {
			$ret[$rel['foreign_field']] = $key;
		}
		return $ret;
	}
	
	/**
	 * Return a set of Formz objects for each of this table's relations.
	 * 
	 * @access public
	 * @return array Related Formz
	 */
	function getTableRelationForms() {
		$relations = $this->getTableRelations();
		$relation_forms = array();
		 
		foreach ($relations as $relation) {
			$relation_forms[] = new Formz($relation['alias']);
		}
		return $relation_forms;
	}

	/**
	 * Fetches the entire table for a relation 
	 * Use this for populating selects and dropdowns
	 * 
	 * @param string $fieldName 
	 * @access public
	 * @return $array values
	 */
	function getTableRelationValues($fields) {
		return $this->_driver->getTableRelationValues($fields);
	}

	/**
	 * Assign the form object into the gui.
	 *
	 * Used to assign a portion of the form into the gui object so that the smarty functions
	 * can use it to draw the form or listing. This step is necessary if you want to actually
	 * view anything.
	 *
	 * @param string $name
	 * @access public
	 * @return void
	 */
	function guiAssign($name = 'form') {
		global $gui;
		$gui->add_css('zoopfile/formz/css/formz.css', 'zoop');
		
		if ($this->type == 'list') {
			$gui->add_js('zoopfile/gui/js/jquery.min.js', 'zoop');
			$gui->add_js('zoopfile/gui/js/jquery.metadata.min.js', 'zoop');
			$gui->add_js('zoopfile/gui/js/jquery.tablesorter.min.js', 'zoop');
/* 			$gui->add_js('zoopfile/formz/js/sortable.js', 'zoop');			 */
			$gui->add_js('jQuery(function($){$(".formz-list.sortable table").tablesorter();});', 'inline');
		}
		$gui->assign($name, $this);
	}

	/**
	 * Set form parameters. Will throw an error if you try to set something that's not a form
	 * property. You've been warned :)
	 *
	 * @param string $param The form parameter to set.
	 * @param mixed $value Defaults to true (for things like $form->setEditable() ... )
	 */
	function setParam($param, $value = true) {
		if (!in_array($param, array_keys($this->valid_properties))) {
			trigger_error("Unknown property: " . $param . " on " . get_class($this));
			return false;
		}
		$this->$param = $value;
	}
	
	/**
	 * setDisplay function.
	 * 
	 * @access public
	 * @param mixed $param
	 * @param mixed $value
	 * @return void
	 */
	function setDisplay($param, $value) {
		$this->display[$param] = $value;
	}
	
	/**
	 * A magic utility function, used by all of the setFieldRequired('id'), etc.
	 *
	 * @access public
	 * @param string $property Property name (called 'Required' above)
	 * @param mixed $field A field, array of fields, or wildcard (*)
	 * @param mixed $value Any value to set for this param. (default: true)
	 * @return void
	 */
	function setFieldParam($property, $field, $value = true) {
		if ($field == '*') {
			$this->setFieldParam($property, array_keys($this->getFields()), $value);
		} else if (is_array($field)) {
			foreach($field as $f) {
				$this->setFieldParam($property, $f, $value);
			}
		} else {
			if (!isset($this->_fields[$field])) {
				$relations = $this->getTableRelations();

				if (in_array($field, array_keys($relations))) {
					if (!isset($this->_relation_fields[$field])) {
						$this->_relation_fields[$field] = array();
					}
					$this->_relation_fields[$field][$property] = $value;
				} else {
					trigger_error("Field not defined: " . $field);
					return;
				}
			}
			$this->_fields[$field][$property] = $value;
		}
	}
	
	/**
	 * Set Display properties of a field.
	 *
	 * These display options are almost identical to the ones used by GuiControls. In fact, most things
	 * set here will be passed directly to a guiControl.
	 *
	 * Usually this function will be called through magic methods
	 * like setFieldDisplayClass('fieldname'). Will accept an array of fields on which to set said
	 * parameter and value. A wildcard param ('*') may also be passed as the $field, which will set the
	 * display option on all fields in this table.
	 * 
	 * @access public
	 * @param string $property Display property to set: Class, etc.
	 * @param mixed $field Fieldname(s) to set property on. String or array of strings.
	 * @param mixed $value Display property value: class name, etc.
	 * @return void
	 */
	function setFieldDisplay($property, $field, $value) {
		if ($field == '*') {
			$this->setFieldDisplay($property, array_keys($this->getFields()), $value);
		} else if (is_array($field)) {
			foreach($field as $f) {
				$this->setFieldDisplay($property, $f, $value);
			}
		} else {
			if (!isset($this->_fields[$field])) {
				$relations = $this->getTableRelations();

				if (in_array($field, array_keys($relations))) {
					if (!isset($this->_relation_fields[$field])) {
						$this->_relation_fields[$field] = array();
					}
					$this->_relation_fields[$field]['display'][$property] = $value;
				} else {
					trigger_error("Field not defined: " . $field);
					return;
				}
			}
			$this->_fields[$field]['display'][$property] = $value;
		}
	}
	
	/**
	 * Pass a whole lot of display parameters at once, formatted as an associative array.
	 *
	 * @see formz::setFieldDisplay
	 * @access public
	 * @param string $field
	 * @param array $args
	 * @return void
	 */
	function setFieldDisplayFromArray($field, $args) {
		foreach ($args as $key => $val) {
			$this->setFieldDisplay($key, $field, $val);
		}
	}
	
	/**
	 * Add a fake field to this table.
	 *
	 * Useful for adding 'edit' links, etc. Will throw an error if the fieldname already
	 * exists on this form.
	 * 
	 * @access public
	 * @param string $name
	 * @param array $defaults. (default: array()
	 * @return void
	 */
	function addField($name, $defaults = array()) {
		if (isset($this->_fields[$name])) {
			trigger_error("Field " . $name . " already exists.");
			return false;
		}
		$this->_fields[$name] = $defaults;
	}
	
	/**
	 * Utility function to set all sorts of field things all at once by passing an array.
	 * I'd avoid it.
	 * 
	 * @access public
	 * @param string $name
	 * @param array $args
	 * @return void
	 */
	function setFieldFromArray($name, $args) {
		if (isset($this->_fields[$name])) {
			$this->_fields[$name] = array_merge_recursive($this->_fields[$name], $args);
		}
		else {
			return $this->addField($name, $args);
		}
	}
	
	/**
	 * Add an action to this form.
	 *
	 * Common form actions include 'save', 'add', 'delete' and 'cancel'.
	 * Optionally, define a lot more things about the action. For example,
	 * 'delete', 'save', and 'cancel' have predefined defaults. Other actions don't
	 * know what to do magically, so you'll have to define them further.
	 *
	 * @param string $name Action name.
	 * @param array $args Optional set of arguments for this action.
	 */
	function addAction($name, $args = array()) {

		// Default label, also capitalized...
		if (!isset($args['label'])) $args['label'] = Formz::format_label($name);
		if (!isset($args['value'])) $args['value'] = $args['label'];
		if (!isset($args['type']) && isset($args['link'])) $args['type'] = 'link';
		
		switch (strtolower($name)) {
			// these are all synonyms... prob'ly don't need quite this many.
			case 'saveandnew':
			case 'save_and_new':
			case 'saveandadd':
			case 'save_and_add':
			case 'saveandaddnew':
			case 'updateandcreate':
			case 'update_and_create':
				$name = 'update_and_create';
				if (!isset($args['value'])) $args['value'] = 'Save and Add New';
				if (!isset($args['label'])) $args['label'] = 'Save and Add New';
				if (!isset($args['type'])) $args['type'] = 'submit';
				break;
			// all save actions need a submit button.
			case 'submit':
			case 'save':
			case 'update':
				$name = 'update';
				if (!isset($args['type'])) $args['type'] = 'submit';
				break;
			// more synonyms. this time for the D in CRUD.
			case 'delete':
			case 'destroy':
				$name = 'destroy';
				
				if (!isset($args['type'])) $args['type'] = 'submit';
				break;
			// cancel should be a link, not a button, by default.
			case 'cancel':
/* 				if (!isset($args['type'])) $args['type'] = 'submit'; */
				if (!isset($args['link'])) $args['link'] = '/read';
				if (!isset($args['type'])) $args['type'] = 'link';
				break;
			// nothing going yet for preview.
			case 'preview':
			default:
				if (!isset($args['type'])) $args['type'] = 'button';
				break;
		}
		
		$this->_formActions[$name] = $args;
	}
	
	/**
	 * Return this form's actions.
	 *
	 * If no actions have been defined, this will return a default set of actions
	 * (namely 'save' and 'cancel')...
	 *
	 * @access public
	 * @see Formz::addAction
	 */
	function getActions() {
		if (count($this->_formActions)) {
			return $this->_formActions;
		}
		else {
			// set some default actions for this form.
			$this->addAction('submit');
			$this->addAction('cancel');
			
			// now remove them from the form... odd, but allows code reuse.
			$actions = $this->_formActions;
			$this->_formActions = array();
			
			// return the actions we added.
			return $actions;
		}
	}
	
	
	/**
	 * Add an action to the list view for this form.
	 *
	 * Common form actions include 'add' or 'filter'. In fact, 'add' might be the only
	 * common list action. Who knows. We'll flesh this out further later...
	 *
	 * @param string $name Action name.
	 * @param array $args Optional set of arguments for this action.
	 */
	function addListAction($name, $args = array()) {
		if (!isset($args['label'])) $args['label'] = Formz::format_label($name);
		if (!isset($args['type']) && isset($args['link'])) $args['type'] = 'link';
		if (!isset($args['value'])) $args['value'] = $args['label'];
		
		switch (strtolower($name)) {
			case 'add':
				if (!isset($args['link'])) $args['link'] = 'create';
				if (!isset($args['type'])) $args['type'] = 'link';
				break;
			default:
				if (!isset($args['type'])) $args['type'] = 'button';
				break;
		}
		
		$this->_formListActions[$name] = $args;
	}
	
	/**
	 * Return this form's list view actions.
	 *
	 * Unlike Formz::getActions, if no actions have been defined, this function will
	 * return an empty set of actions.
	 *
	 * @access public
	 * @see Formz::addListAction
	 */
	function getListActions() {
		return $this->_formListActions;
	}

	/**
	 * Add a row action. Analogous to list actions or form actions, but apply to a single row.
	 * 
	 * @access public
	 * @param string $name Action name
	 * @param mixed $args Action arguments. (default: array())
	 * @return void
	 */
	function addRowAction($name, $args = array()) {
		// Default label, also capitalized...
		if (!isset($args['label'])) $args['label'] = Formz::format_label($name);
		if (!isset($args['value'])) $args['value'] = $args['label'];
		if (!isset($args['type']) && isset($args['link'])) $args['type'] = 'link';

		switch (strtolower($name)) {
			// these are all synonyms... prob'ly don't need quite this many.
			case 'saveandnew':
			case 'save_and_new':
			case 'saveandadd':
			case 'save_and_add':
			case 'saveandaddnew':
			case 'updateandcreate':
			case 'update_and_create':
				$name = 'update_and_create';
				if (!isset($args['value'])) $args['value'] = 'Save and Add New';
				if (!isset($args['label'])) $args['label'] = 'Save and Add New';
				if (!isset($args['type'])) $args['type'] = 'submit';
				break;
			// all save actions need a submit button.
			case 'submit':
			case 'save':
			case 'update':
				$name = 'update';
				if (!isset($args['type'])) $args['type'] = 'submit';
				break;
			// more synonyms. this time for the D in CRUD.
			case 'delete':
			case 'destroy':
				$name = 'destroy';

				if (!isset($args['type'])) $args['type'] = 'submit';
				break;
			// cancel should be a link, not a button, by default.
			case 'cancel':
	/* 				if (!isset($args['type'])) $args['type'] = 'submit'; */
				if (!isset($args['link'])) $args['link'] = '/read';
				if (!isset($args['type'])) $args['type'] = 'link';
				break;
			// nothing going yet for preview.
			case 'preview':
			default:
				if (!isset($args['type'])) $args['type'] = 'button';
				break;
		}

		$this->_formActions[$name] = $args;
	}	

	function addEmbeddedForm($tablename, $form = null) {
		if ($form !== null) {
			$this->_embeddedFormz[$tablename] = $form;
		} else {
			$this->_embeddedFormz[$tablename] = new Formz($tablename);
		}
	}
	
	function getEmbeddedFormz($name = null) {
		if ($name === null) {
			return $this->_embeddedFormz;
		} else {
			if (isset($this->_embeddedFormz[$name])) {
				return $this->_embeddedFormz[$name];
			} else {
				trigger_error('Requested embedded form does not exist');
			}
		}
	}
	
	/**
	 * Add a row action. Analogous to list actions or form actions, but apply to a single row.
	 * 
	 * @access public
	 * @param string $name Action name
	 * @param mixed $args Action arguments. (default: array())
	 * @return void
	 */
	// function addRowAction($name, $args = array()) {
	// 	trigger_error("addRowAction not yet implemented");
	// }
	
	/**
	 * Returns true if this Formz does timestamp magick.
	 *
	 * @access public
	 * @return bool True if this is timestampable.
	 */
	function isTimestampable() {
		$this->timestampable = $this->_driver->isTimestampable();
		return $this->timestampable;
	}
	
	/**
	 * Returns true if this Formz uses soft delete.
	 *
	 * @access public
	 * @return bool True if this is soft deletable.
	 */
	function isSoftDeletable() {
		$this->softdeletable = $this->_driver->isSoftDeletable();
		return $this->softdeletable;
	}
	
	/**
	 * Returns true if this Formz uses slugs.
	 *
	 * @access public
	 * @return bool True if this is sluggable.
	 */
	function isSluggable() {
		$this->sluggable = $this->_driver->isSluggable();
		return $this->sluggable;
	}

	/**
	 * Returns true if this Formz uses versions.
	 *
	 * @access public
	 * @return bool True if this is versionable.
	 */
	function isVersionable() {
		$this->versionable = $this->_driver->isVersionable();
		return $this->versionable;
	}
		
	/**
	 * Returns true if this Formz is sortable.
	 *
	 * @access public
	 * @return bool True if this is sortable.
	 */
	function isSortable() {
		return $this->sortable;
	}
	
   	/**
	 * Returns true if table is a tree 
	 * 
	 * @access public
	 * @return void
	 */
	function isTree() {
		return $this->_driver->isTree();
	}
	
	/**
	 * When using a nestedSet, used to define the parent. 
	 * 
	 * @param mixed $node 
	 * @access public
	 * @return void
	 */
	function setParentRecord($node = false) {
		if ($node === false) {
			trigger_error('node required when using "setParentRecord"');
		}

		$this->_driver->_parentRecord = $node;
	}

	/**
	 * Sort the results by a given field (column).
	 *
	 * @param string $fieldname Field name to sort by
	 * @param string $direction Optionally specifiy sort direction: ASC or DESC (case insensitive)
	 * @access public
	 * @return void
	 */
	function sort($fieldname, $direction = "ASC") {
		$direction = strtoupper($direction);
		
		// If the fieldname isn't valid, don't even give them the courtesy of a response.
		if (!in_array($fieldname, array_keys($this->_fields))) {
			return;
		}

		// unset default sort info (since we've given it an explicit one)
		$this->_defaultSortField = null;
		$this->_defaultSortDirection = null;
				
		// If the direction isn't desc, it's gotta be asc. Make sure that's how it really works out.
		if ($direction !== 'DESC') {
			$direction == 'ASC';
		}
		$this->_driver->sort($fieldname, $direction);
	}
	
	/**
	 * Get the current sort field (from url parameters, for now).
	 *
	 * @access public
	 * @return mixed String value for sort field, or 'false' if sort is unspecified.
	 */
	function getSortField() {
		if ($sort = getGetText('sort')) {
			return $sort;
		} else {
			return false;
		}
	}
	
	/**
	 * Get the current sort direction (from url parameters, for now).
	 *
	 * @access public
	 * @return string 'ASC' or 'DESC'.
	 */
	function getSortOrder() {		
		$order = strtoupper(getGetText('order'));
		if ($order != 'DESC') {
			$order = 'ASC';
		}
		return $order;
	}
		
	/**
	 * Returns true if this Formz uses slugs.
	 *
	 * @access public
	 * @return bool True if this is sluggable.
	 */
	function getSlugField() {
		$this->slug_field = $this->_driver->getSlugField();
		return $this->slug_field;
	}

	/**
	 * Sets the default sort field (and optionally direction) on a Formz object.
	 *
	 * @param string $fieldname
	 * @param string $direction 'ASC' or 'DESC' (optional).
	 */
	function setDefaultSort($fieldname, $direction = 'ASC') {
		$this->_defaultSortField = $fieldname;
		$this->_defaultSortDirection = strtoupper($direction);
	}
	
	
	function getTitleField() {
		$label_field = $this->getIdField();

		foreach(Config::get('zoop.formz.title_field_priority') as $field_name){
			$fields = $this->getFields();
			if (isset($fields[$field_name])) {
				$label_field = $field_name;
				break;
			}
		}

		return $label_field;
	}
	
	/**
	 * __call magic method.
	 *
	 * This method simply passes off all calls to the current forms db driver (forms or doctrine).
	 *
	 * If the method doesn't exist on the forms db driver, it will try a magic setter as well.
	 * i.e. ->setSortable(true) would call ->setParam('sortable',true)
	 *
	 * @access private
	 */
	function __call($method, $args) {

		if (substr($method, 0, 15) == 'setFieldDisplay') {
			$param_name = substr($method, 15);
			
			//lowercasify the first letter...
			$param_name[0] = strtolower($param_name[0]);
			
			array_unshift($args, $param_name);
			return call_user_func_array(array($this, 'setFieldDisplay'), $args);
		}
		else if (substr($method, 0, 8) == 'setField') {
			$param_name = substr($method, 8);
			
			//lowercasify the first letter...
			$param_name[0] = strtolower($param_name[0]);
			
			array_unshift($args, $param_name);
			return call_user_func_array(array($this, 'setFieldParam'), $args);
		}
		else if (substr($method, 0, 10) == 'setDisplay') {
			$param_name = substr($method, 10);
			
			//lowercasify the first letter...
			$param_name[0] = strtolower($param_name[0]);
			
			array_unshift($args, $param_name);
			return call_user_func_array(array($this, 'setDisplay'), $args);
		}
		else if (substr($method, 0, 3) == 'set') {
			// @TODO we need to get rid of this setter. no me gusta.
			$param_name = substr($method, 3);
			
			// lowercasify the first letter...
			$param_name[0] = strtolower($param_name[0]);
			
			array_unshift($args, $param_name);
			return call_user_func_array(array($this, 'setParam'), $args);
		}
		else if (substr($method, 0, 3) == 'get') {
			$param_name = substr($method, 3);
			
			//lowercasify the first letter...
			$param_name[0] = strtolower($param_name[0]);

			if (isset($this->$param_name)) return $this->$param_name;
			else trigger_error($method . " method undefined on Formz object.");
		}
		else {
			trigger_error($method . " method undefined on Formz object.");
		}
	}

	/**
	 * Convert a DB column key into a decent label.
	 *
	 * @param string $str Label to convert
	 * @return string Formatted form label
	 */
	static function format_label ($str) {
		$str = str_replace(array('_', '-'), array(' ', ' '), $str);
		$str = preg_replace('#(?<=[a-z])([A-Z])#', ' $1', $str);
		return nv_title_case($str);
	}
	
}
