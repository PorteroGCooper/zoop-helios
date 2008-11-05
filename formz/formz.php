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
	var $actions = array();

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
		$field_names = array_keys($this->getFields());
		foreach ($field_names as $name) {
			if (isset($values[$name])) {
				$save[$name] = $values[$name];
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

		// mix in info from foreign fields
		foreach ($this->getRelations() as $key => $relation) {
			
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
			
			foreach($relation['values'] as $item) {
				$values[$item[$relation['foreign_field']]] = $item[$relation_label_field];
			}
			
			// decide whether this should be single or multiple select
			if ($relation['rel_type'] == 'many') {
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
		$this->actions[$name] = $args;

	}
	
	function getActions() {
		if (count($this->actions)) {
			return $this->actions;
		}
		else {
			// set some default actions for this form.
			$this->addAction('submit');
			$this->addAction('cancel');
			
			// now remove them from the form... odd, but allows code reuse.
			$actions = $this->actions;
			$this->actions = array();
			
			// return the actions we added.
			return $actions;
		}
	}
	
	
	function isTimestampable() {
		$this->timestampable = $this->driver->isTimestampable();
		return $this->timestampable;
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