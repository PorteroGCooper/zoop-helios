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
	var $driver;

	var $tablename;
	var $type;	
	var $title;
	var $zone;
	var $callback;
	
	var $fields = array();
	var $order = array();

	/**
	 * Actions for this form (Actions are things like 'save' and 'cancel')
	 *
	 * @access private
	 * @see Formz::addAction
	 * @see Formz::getActions
	 */
	var $_formActions = array();

	/**
	 * Actions for the list view for this form (List actions might be 'add' or 'sort')
	 *
	 * @access private
	 * @see Formz::addListAction
	 * @see Formz::getListActions
	 */
	var $_formListActions = array();

	var $errors = array();
	var $editable = true;

	var $record_id;
	
	var $timestampable = false;

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
				$this->driver = new Formz_DoctrineDB($tablename);
				break;
			case 'forms':
				$this->driver = new Formz_FormDB($tablename);
				break;
			default:
				trigger_error($driver_type . " is not a valid Formz type.");
				break;
		}
		
		// grab the default field definitions, we'll mess with 'em later :)
		$this->fields = $this->driver->getFields();
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
		$this->record_id = $this->driver->getRecord($id);
		if ($this->record_id) {
			$this->type = 'record';
		}
		return $this->record_id;
	}
	
	function getRecords($search = false) {
/* 		die_r($this->driver->getRecords($search)); */
		return $this->driver->getRecords($search);
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

		if ($id === null) {
			if (isset($this->id)) {
				$id = $this->id;
			} else if (isset($values['id'])) {
				$id = $values['id'];
				
				// strip out $values['id'] = 'new' to keep it from saving to db...
				if ($id == 'new') {
					unset($values['id']);
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
				if (isset($field_info['relation_alias'])) {
					$save['relations'][$field_info['relation_alias']] = $values[$name];
				} else {
					$save[$name] = $values[$name];
				} 
			}
		}
			
		return $this->driver->saveRecord($save, $id);
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
		return $this->driver->destroyRecord($id);
	}
	
	/**
	 * Set form field sort order.
	 *
	 * @param mixed $sort Field sort order as an array or comma separated string.
	 */
	function setOrder($sort) {
		if (!is_array($sort)) $sort = explode(',', $sort);
		$this->order = $sort;
		
		$newfields = array();
		
		// rearrange fields
		foreach ($this->order as $key) {
			$key = trim($key);
			
			if (isset($this->fields[$key])) {
				$newfields[$key] = $this->fields[$key];
				unset($this->fields[$key]);
			}
		}
		if (count($newfields)) {
			$oldfields = $this->fields;
			$this->fields = $newfields + $oldfields;
		}
	}
	
	function getIdField() {
		return $this->driver->getIdField();
	}
	
	/**
	 * Get an array of all relevant field information.
	 */
	function getFields() {
		$fields = $this->fields;
		
		// hide the record id by default.		
		$id = $this->driver->getIdField();
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
			}
			if (isset($fields['updated_at'])) {
				if (!isset($fields['updated_at']['formshow'])) {
					$fields['updated_at']['formshow'] = false;
				}
				if (!isset($fields['updated_at']['listshow'])) {
					$fields['updated_at']['listshow'] = false;
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
			}
		}
		
		// mix in info from foreign fields
		foreach ($this->getRelations() as $key => $relation) {
			
			$fields[$key]['relation_alias'] = $relation['alias'];
		
			if (!isset($fields[$key]['display']['label'])) {
				$fields[$key]['display']['label'] = $relation['alias'];
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
			
			// decide whether this should be single or multiple select
			if ($relation['rel_type'] == Formz::MANY) {
				$fields[$key]['display']['type'] = 'multiple';
			} else {
				$fields[$key]['display']['type'] = 'select';
			}
			$fields[$key]['values'] = $values;
			$fields[$key]['display']['index'] = $values;
			
		}

		return $fields;
	}
	
	function getFieldValues($name) {
		if ($relation = $this->getRelation($name)) {
			// figure out what field to display for this relation
			if (isset($this->fields[$name]['relationLabelField'])) {
				$relation_label_field = $this->fields[$name]['relationLabelField'];
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
		return $this->driver->getData();
	}
	
	function getRelation($name) {
		return $this->driver->getRelation($name);
	}
	
	/**
	 * Get an array of relation fields for this form.
	 */
	function getRelations() {
		return $this->driver->getRelations();
	}
	
	function getRelationLocalFields() {
		$ret = array();
		
		foreach ($this->driver->getRelations() as $key => $rel) {
			$ret[$rel['local_field']] = $key;
		}
		return $ret;
	}
	
	function getRelationForeignFields() {
		$ret = array();
		
		foreach ($this->driver->getRelations() as $key => $rel) {
			$ret[$rel['foreign_field']] = $key;
		}
		return $ret;
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
	
	function setDisplay($param, $value) {
		$this->display[$param] = $value;
	}
	
	function setFieldParam($property, $field, $value = true) {
		if ($field == '*') {
			$this->setFieldParam($property, array_keys($this->getFields()), $value);
		}
		else if (is_array($field)) {
			foreach($field as $f) {
				$this->setFieldParam($property, $f, $value);
			}
		}
		else {
			if (!isset($this->fields[$field])) {
				trigger_error("Field not defined: " . $field);
				return;
			}
			$this->fields[$field][$property] = $value;
		}
	}
	
	function setFieldDisplay($property, $field, $value) {
		if ($field == '*') {
			$this->setFieldDisplay($property, array_keys($this->getFields()), $value);
		}
		else if (is_array($field)) {
			foreach($field as $f) {
				$this->setFieldDisplay($property, $f, $value);
			}
		}
		else {
			if (!isset($this->fields[$field])) {
				trigger_error("Field not defined: " . $field);
				return;
			}
			$this->fields[$field]['display'][$property] = $value;
		}
	}
	
	function setFieldDisplayFromArray($field, $args) {
		foreach ($args as $key => $val) {
			$this->setFieldDisplay($key, $field, $val);
		}
	}
	
	function addField($name, $defaults = array()) {
		if (isset($this->fields[$name])) {
			trigger_error("Field " . $name . " already exists.");
			return false;
		}
		$this->fields[$name] = $defaults;
	}
	
	function setFieldFromArray($name, $args) {
		if (isset($this->fields[$name])) {
			$this->fields[$name] = array_merge_recursive($this->fields[$name], $args);
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
		$defaults = array(
			'label' => $name,
		);
		if (isset($args['link'])) $args['type'] = 'link';
		
		// capitalize default label...
		$defaults['label'] = Formz::format_label($defaults['label']);
		$defaults['value'] = $defaults['label'];
		
		switch (strtolower($name)) {
			case 'submit':
			case 'save':
			case 'update':
				$name = 'update';
				if (!isset($defaults['type'])) $defaults['type'] = 'submit';
				break;
			case 'delete':
			case 'destroy':
				$name = 'destroy';
				if (!isset($defaults['type'])) $defaults['type'] = 'submit';
				break;
			case 'cancel':
				if (!isset($defaults['link'])) $defaults['link'] = '%id%/read';
				if (!isset($defaults['type'])) $defaults['type'] = 'link';
				break;
			case 'preview':
			default:
				$defaults['type'] = 'button';
				break;
		}
		
		switch (strtolower($name)) {
		}
		
		$args = array_merge_recursive($defaults, $args);
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
		$defaults = array(
			'label' => $name,
		);
		if (isset($args['link'])) $args['type'] = 'link';
		
		// capitalize default label...
		$defaults['label'] = Formz::format_label($defaults['label']);
		$defaults['value'] = $defaults['label'];
		
		switch (strtolower($name)) {
			case 'add':
				if (!isset($defaults['link'])) $defaults['link'] = 'create';
				if (!isset($defaults['type'])) $defaults['type'] = 'link';
				break;
			default:
				$defaults['type'] = 'button';
				break;
		}
		
		switch (strtolower($name)) {
		}
		
		$args = array_merge_recursive($defaults, $args);
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
	 * Returns true if this Formz does timestamp magick.
	 *
	 * @access public
	 * @return bool True if this is timestampable.
	 */
	function isTimestampable() {
		$this->timestampable = $this->driver->isTimestampable();
		return $this->timestampable;
	}
	
	/**
	 * Returns true if this Formz uses soft delete.
	 *
	 * @access public
	 * @return bool True if this is soft deletable.
	 */
	function isSoftDeletable() {
		$this->softdeletable = $this->driver->isSoftDeletable();
		return $this->softdeletable;
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
			else return null;
		}
		else {
			trigger_error($method . " method undefined on Formz object.");
		}
	}
	
	/**
	 * tells the driver to sort the results by fieldname
	 *
	 * @param string $fieldname fieldname to sort on
	 * @param string $direction either ASC or DESC
	 * @access public
	 * @return void
	 */
	function sort($fieldname, $direction = "ASC") {
		$this->driver->sort($fieldname, $direction);
	}

	/**
	 * Convert a DB column key into a decent label.
	 *
	 * @param string $str Label to convert
	 * @return string Formatted form label
	 */
	static function format_label ($str) {
		$str = str_replace(array('_', '-'), array(' ', ' '), $str);
		return nv_title_case($str);
	}
	
}
