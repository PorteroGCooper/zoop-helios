<?php

/**
 * Formz is the next generation Zoop form controller.
 *
 * Formz relies on either a Doctrine or a Zoop forms database backend.
 * It creates an easy to use link between a table and a html list,
 * or a record and an html form. It supports full validation of types
 * and requirements.
 *
 * @ingroup forms
 * @ingroup Formz
 * @see formz_doctrineDB
 * @see formz_formDB
 * @author Justin Hileman {@link http://justinhileman.com}
 */
class Formz {

	/**
	 * Constants for defining relationships.
	 *
	 */
	const ONE = 1;
	const MANY = 2;

	/**
	 * Constants for defining driver types.
	 *
	 */
	const DoctrineDB = 0;
	const FormDB = 1;

	/**
	 * Formz database driver object.
	 *
	 * At this point, this is either a formDB or a doctrineDB connector.
	 * 
	 * @access protected
	 */
	protected $_driver;

	var $tablename;
	var $type;
	var $title;
	var $zone;
	var $callback;
	var $listActionPosition;
	
	/**
	 * The fieldname prefix is used internally for embedded formz objects.
	 * Don't mess with it, as it will cause your formz to stop working.
	 * @access protected
	 */
	var $fieldnamePrefix;
	var $embedded = false;
	
	/**
	 * Private fields variable. Used by formz::getFields()
	 * @var array
	 * @access protected
	 */
	protected $_fields = array();
	
	/**
	 * Private relation fields variable. Used by formz::getFields()
	 * @var array
	 * @access protected
	 */
	protected $_relation_fields = array();
	protected $_searchableFields = array();
	
	/**
	 * Default sort direction for this Formz object
	 *
	 * @see formz::setDefaultSort
	 * @var string
	 * @access protected
	 */
	protected $_order = array();
	
	/**
	 * Default sort field for this Formz object
	 *
	 * @see formz::setDefaultSort
	 * @var string
	 * @access protected
	 */
	protected $_defaultSortField = null;

	/**
	 * Default sort direction for this Formz object
	 *
	 * @see formz::setDefaultSort
	 * @var string
	 * @access protected
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
	 * Actions for the rows for this form (Row actions may be 'edit' or 'delete')
	 *
	 * @access private
	 * @see Formz::addRowAction
	 * @see Formz::getRowActions
	 */
	protected $_formRowActions = array();

	/**
	 * Search form objects
	 *
	 * @access protected
	 */
	protected $_searchForms = null;
	
	var $errors = array();
	var $editable = false;

	var $record_id;
	var $slug_field;
	
	protected $timestampable = false;
	protected $sortable = true;
	protected $versionable = false;
	protected $searchable = false;

	/**
	 * Stores already populated strings to drastically reduce function calls and queries.
	 *
	 * @see Formz::populateString()
	 * @access private
	 */
	private $_populatedStrings = array();

	/**
	 * Formz constructor. Returns an object implementing the Formz interface.
	 *
	 * Accepts a param defining which driver type to use. If the param is not passed,
	 * it will use the app or framework selected default formz driver. Please pass one
	 * of the Formz driver type constants to override: Formz::DoctrineDB or Formz::FormDB
	 *
	 * @param string $tablename  table name in the database
	 * @param string $driver_type  OPTIONAL, Driver type to use for this Formz object.
	 * @return object implementing Formz interface
	 * @access public
	 */
	function __construct($tablename, $driver_type = 'default') {
		$this->valid_properties = get_class_vars(get_class($this));

		// get the default Formz ORM driver
		if ($driver_type == 'default') $driver_type = Config::get('zoop.formz.driver');

		$this->tablename = $tablename;
		$this->type = Config::get('zoop.formz.type','list');
		
		switch ($driver_type) {
			case Formz::DoctrineDB:
			case 'doctrine':
				$this->_driver = new Formz_DoctrineDB($tablename);
				// Make this driver aware of its table alias
				$this->_driver->setTableAlias($tablename);
				break;
			case Formz::FormDB:
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
	/**
	 * Reaturn a list of records (for displaying in a formz list view).
	 *
	 * A default sort will be applied if one exists, and if the records haven't already been sorted.
	 * 
	 * @access public
	 * @param mixed $search. (default: false)
	 * @return void
	 */
	function getRecords($search = false) {
		// if we're doing a sort, do it!
		if (!$this->_driver->isSorted() && !empty($this->_defaultSortField)) {
			$this->sort($this->_defaultSortField, $this->_defaultSortDirection);
		}

		return $this->_driver->getRecords();
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
			$values = getRawPost();
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
				trigger_error("Formz object does not have a current record to save.");
				return null;
			}
		}

		// only save things that should be saved (no fake fields).
		$save = array();

		$field_names = $fields = $this->getFields();
		
		foreach ($values as $name => $value) {
			if (isset($fields[$name])) {
				if ($fields[$name]['type'] == 'relation') {
					if (isset($fields[$name]['embeddedForm'])) {
						$embedded_id_field = $fields[$name]['embeddedForm']->getIdField();
						$local_alias = $fields[$name]['rel']['local_alias'];
						if (isset($values[$name][$embedded_id_field])) {
							$embedded_id = $values[$name][$embedded_id_field];
							unset($values[$name][$embedded_id_field]);
						} else if (isset($values[$local_alias])) {
							$embedded_id = $values[$local_alias];
						} else {
							trigger_error("Unable to save embedded Formz without an ID field");
						}
						$save[$local_alias] = $fields[$name]['embeddedForm']->saveRecord($values[$name], $embedded_id);
					} else {
						if ($fields[$name]['rel']['rel_type'] == Formz::MANY) {
							$save['relations'][$fields[$name]['rel']['alias']] = $values[$name];
						} else {
							$save[$name] = $values[$name];
						}
					}					
				} else {
					$save[$name] = $values[$name];
				}
			} else if (strpos($name, '.') !== false) {
				$relation = substr($name, 0, strpos($name, '.'));
				$rel_field = substr($name, strpos($name, '.') - 1);
			} else {
				continue;
			}
		}
		
		/*
		foreach ($field_names as $name => $field_info) {
			if (isset($values[$name])) {
			
				if ($field_info['type'] == 'relation') {
					if ($field_info['rel']['rel_type'] == Formz::MANY) {
						$save['relations'][$field_info['rel']['alias']] = $values[$name];
					} else {
						$save[$name] = $values[$name];
					}
				} else {
					$save[$name] = $values[$name];
				} 
			}
		}
		*/

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
	 * Create and attach a relation to the current record
	 *
	 * @param array $values values representing the new relation
	 * @access public
	 * @return void
	 */
	function createRelation($values, $id = null) {
		if ($values === false) {
			$values = getPost();
		}

		$id_field = $this->getIdField();

		if ($id === null) {
			if (isset($this->parentId)) {
				$id = $this->parentId;
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
		
		return $this->_driver->createRelation($values, $id);
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
	
		
	/**
	 * Return the driver type for this formz driver.
	 *
	 * @access public
	 * @return int Formz::DoctrineDB or Formz::FormDB const.
	 */
//	function getDriverType() {
//		return $this->_driver->getType();
//	}
	
	/**
	 * Get the ID field for this formz object.
	 * 
	 * @access public
	 * @return int
	 */
	function getIdField() {
		return $this->_driver->getIdField();
	}
	
	/**
	 * Get the page count for this formz object.
	 *
	 * NOTE: passing a limit doesn't do anything right now.
	 * 
	 * @access public
	 * @param int $limit. (default: null)
	 * @return int
	 */
	function getPageCount($limit = null) {
		return $this->_driver->getPageCount($limit);
	}


	/**
	 * Returns an array of fixed values to use when selecting as well as creating/updating 
	 * 
	 * @param mixed $key 
	 * @access public
	 * @return void
	 */
/*
	function getConstraints($key = false) {
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
*/



	/**
	 * Append a fixed value to the fixed values 
	 * 
	 * @see self::setFixedValues
	 * @param mixed $array 
	 * @access public
	 * @return void
	 */
	function setFieldConstraint($fieldname, $value, $is_fixed = true) {
		$this->_fields[$fieldname]['override'] = $value;
		if ($is_fixed) {
			$this->_fields[$fieldname]['editable'] = false;
			
		}
		$this->_driver->addConstraint($fieldname, $value);
	}

	/**
	 * Set a fixed value to be used when seleting as well as updating 
	 * 
	 * @param mixed $array 
	 * @access public
	 * @return void
	 */
	function setFieldConstraints($fields, $is_fixed = true) {
		foreach ($fields as $_key => $_val) {
			$this->setFieldConstraint($_key, $_val, $is_fixed);
		}
	}
	
	/**
	 * removeFieldConstraint function.
	 * 
	 * @access public
	 * @param string $fieldname
	 * @return void
	 */
	function removeFieldConstraint($fieldname) {
		if (isset($this->_fields[$fieldname]['override'])) {
			unset($this->_fields[$fieldname]['override']);
		}
		if (isset($this->_fields[$fieldname]['editable'])) {
			unset($this->_fields[$fieldname]['editable']);
		}
		$this->_driver->removeConstraint($fieldname);
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
			list($created_at, $updated_at) = $this->_driver->getTimestampFields();
			if (isset($fields[$created_at])) {
				if (!isset($fields[$created_at]['formshow'])) {
					$fields[$created_at]['formshow'] = false;
				}
				if (!isset($fields[$created_at]['listshow'])) {
					$fields[$created_at]['listshow'] = false;
				}
				if (!isset($fields[$created_at]['editable'])) {
					$fields[$created_at]['editable'] = false;
				}				
			}
			if (isset($fields[$updated_at])) {
				if (!isset($fields[$updated_at]['formshow'])) {
					$fields[$updated_at]['formshow'] = false;
				}
				if (!isset($fields[$updated_at]['listshow'])) {
					$fields[$updated_at]['listshow'] = false;
				}
				if (!isset($fields[$updated_at]['editable'])) {
					$fields[$updated_at]['editable'] = false;
				}
			}
		}
		
		// Don't show the "deleted" field...
		if ($this->isSoftDeletable()) {
			$deleted_field = $this->_driver->getSoftDeleteField();
			if (isset($fields[$deleted_field])) {
				if (!isset($fields[$deleted_field]['formshow'])) {
					$fields[$deleted_field]['formshow'] = false;
				}
				if (!isset($fields[$deleted_field]['listshow'])) {
					$fields[$deleted_field]['listshow'] = false;
				}
				if (!isset($fields[$deleted_field]['editable'])) {
					$fields[$deleted_field]['editable'] = false;
				}
				
			}
		}
		
		// Don't show the 'slug' field...
		if ($this->isSluggable()) {
			$slug_field = $this->_driver->getSlugField();
			if (isset($fields[$slug_field])) {
				if (!isset($fields[$slug_field]['formshow'])) {
					$fields[$slug_field]['formshow'] = false;
				}
				if (!isset($fields[$slug_field]['listshow'])) {
					$fields[$slug_field]['listshow'] = false;
				}
				if (!isset($fields[$slug_field]['editable'])) {
					$fields[$slug_field]['editable'] = false;
				}
			}
		}
		
		// Don't show the 'version' field...
		if ($this->isVersionable()) {
			$version_field = $this->_driver->getVersionField();
			if (isset($fields[$version_field])) {
				if (!isset($fields[$version_field]['formshow'])) {
					$fields[$version_field]['formshow'] = false;
				}
				if (!isset($fields[$version_field]['listshow'])) {
					$fields[$version_field]['listshow'] = false;
				}
				if (!isset($fields[$version_field]['editable'])) {
					$fields[$version_field]['editable'] = false;
				}
			}
		}
		
		// mix in info from foreign fields
		foreach ($this->getTableRelations() as $key => $relation) {
			if ($return_relations) {
				if (strchr($key, '.') !== false) {
					$fields[$key]['type'] = 'relation_foreign_field';
				} else {
					$fields[$key]['type'] = 'relation';
				}
				
				$fields[$key]['rel'] = $relation;
				
/*
				$fields[$key]['relation_class']       = $relation['class'];
				$fields[$key]['relation_alias']       = $relation['alias'];
				$fields[$key]['relation_local_alias'] = $relation['local_alias'];
				$fields[$key]['rel_type']             = $relation['rel_type'];
*/
				
				if (!isset($fields[$key]['display']['label'])) {
					if (isset($relation_fields[$key]['display']['label'])) {
						$fields[$key]['display']['label'] = $relation_fields[$key]['display']['label'];
					} else {
						$fields[$key]['display']['label'] = format_label($relation['alias']);
					}
				}
				
				// figure out what field to display for this relation (i.e. in a dropdown)
				if (isset($fields[$key]['rel']['label_field'])) {
					$relation_label_field = $fields[$key]['rel']['label_field'];
				} else {
					$relation_label_field = $relation['label_field'];
				}
				
				if (isset($relation_fields[$key]['display'])) {
					$fields[$key]['display'] = Config::merge($relation_fields[$key]['display'], $fields[$key]['display']);
				}
				
			} else {
				$fields[$key]['listshow'] = false;
			}
		}
		
		// mix in validation options
		foreach ($fields as $name => $field) {
			if ($validate = $this->getFieldValidation($field)) $fields[$name]['validate'] = $validate;
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
	
	private function getFieldValidation($field) {
		$validate = (isset($field['validate'])) ? $field['validate'] : array();
		
		foreach ($field as $param => $value) {
			switch ($param) {
				case 'autoincrement':
					return;
					break;
				case 'type':
					switch($value) {
						case 'integer':
						case 'float':
							$value = 'numeric';
							break;
						case 'timestamp':
							$value = 'date';
							if (!isset($validate['format'])) $validate['format'] = 'timestamp';
							break;
						default:
							continue 2;
							break;
					}
					if (!isset($validate['type'])) $validate['type'] = $value;
					break;
				case 'email':
					if ($value && !isset($validate['type'])) $validate['type'] = 'email';
					break;
				case 'length':
					switch ($field['type']) {
						case 'integer':
						case 'float':
							$max = pow(2, $value * 8);
							if (false && isset($field['unsigned']) && $field['unsigned']) {
								$min = 0;
							} else {
								$max = floor($max / 2) - 1;
								$min = 0 - $max;
							}
							if (!isset($validate['max'])) $validate['max'] = $max;
							if (!isset($validate['min'])) $validate['min'] = $min;
							break;
						case 'string':
							// if (!isset($validate['max'])) $validate['max'] = $value;
							break;
					}
					break;
				case 'notnull':
				case 'required':
					if ($field['type'] == 'boolean') {
						break;
					} else {
						if (!isset($validate['required'])) $validate['required'] = (bool)$value;
						break;
					}
				default:
					break;
			}
		}

		return $validate;
	}
	
	/**
	 * Get an array of all field data for this form.
	 */
	function getData() {
		return $this->_driver->getData();
	}
	
	function getValue($fieldname, $id = null) {
		// if this is an aggregate field, we'll have to handle it a bit different.
		if (isset($this->_fields[$fieldname]) && isset($this->_fields[$fieldname]['type']) && $this->_fields[$fieldname]['type'] == 'aggregate') {
			return $this->populateString($this->_fields[$fieldname]['format_string'], $id);
		}
		// otherwise, pass it off to the driver.
		return $this->_driver->getValue($fieldname, $id);
	}

	/**
	 * Get foreign fields that should be immutable for this form
	 *
	 * @param string $foreign_class name of the foreign relation
	 * @access public
	 * @return void
	 */
	function getImmutableForeignFields($foreign_class) {
		return $this->_driver->getImmutableForeignFields($foreign_class);
	}

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
			$ret[$rel['local_alias']] = $key;
		}
		return $ret;
	}
		
	/**
	 * Return the relation field data (foreign/local fields, etc) for a given relation name.
	 * 
	 * @access public
	 * @param mixed $name A given relation name.
	 * @param bool $getValues Hydrate the array with values? (default: false)
	 * @return array An array of information about the requested relation.
	 */
	 
	 
	 
	/**
	 * getTableRelationForeignFields function.
	 * 
	 * @access public
	 * @return array relation fields
	 */
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
		
		// only set up the sorting magick if this is a single page formz list.
		if ($this->type == 'list' && (!$this->isPaginated() || $this->getPageCount() < 2)) {
			$gui->add_js('zoopfile/gui/js/jquery.js', 'zoop');
			$gui->add_js('zoopfile/gui/js/jquery.metadata.js', 'zoop');
			$gui->add_js('zoopfile/gui/js/jquery.tablesorter.js', 'zoop');

			// add a sortable() call for *this* sortable formz table.
			$formz_table_id = 'formz_' . strtolower($this->tablename) . '_list';
			$gui->add_jquery('$("#'. $formz_table_id .'.sortable table").tablesorter();', 'inline');
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
	function setDisplay($param, $value = true) {
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
		
			// special handling for interesting properties.
			switch($property) {
				case 'searchable':
					return $this->setFieldSearchable($field, $value);
					break;
			}
			
			if (!isset($this->_fields[$field])) {
				$relations = array();
				foreach ($this->getTableRelations() as $relation) {
					$relations[$relation['alias']] = $relation;
				}

				if (in_array($field, array_keys($relations))) {
					if (!isset($this->_relation_fields[$field])) {
						$this->_relation_fields[$field] = array();
					}
					$this->_relation_fields[$field][$property] = $value;
				} else if (strchr($field, '.') !== false) {
					$chunks = explode('.', $field);

					if (isset($relations[$chunks[0]])) {
						$this->_relations_fields[$field] = array();
						$this->_relation_fields[$field][$property] = $value;
					} else {
						trigger_error("Field not defined: " . $field);
						return;
					}
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
	 * Usually this function will be called through magic methods like setFieldDisplayClass('fieldname');
	 * Will accept an array of fields on which to set said parameter and value. A wildcard param ('*') may
	 * also be passed as the $field, which will set the display option on all fields in this table.
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
	 * @ingroup formzfield
	 * 
	 * @access public
	 * @param string $name
	 * @param array $defaults. (default: array()
	 * @return FormzField object for accessing this field.
	 */
	function addField($name, $defaults = array()) {
		if (isset($this->_fields[$name])) {
			trigger_error("Field " . $name . " already exists.");
			return false;
		}
		if (!isset($defaults['type'])) $defaults['type'] = 'text';
		$this->_fields[$name] = $defaults;
		return $this->field($name);
	}
	
	/**
	 * Add a formatted aggregate field.
	 *
	 * Aggregate fields are composed of values from other fields, arranged together in a string.
	 *
	 * @code
	 *   $recent_orders->addAggregateField('name', '%first_name% %last_name%')
	 *      ->setDisplayLabel('Full Name');
	 * @endcode
	 *
	 * Replacement fields are designated with %field%, and can be in the format %relation.field%
	 * or even %relation.newrelation.anotherrelation.field%
	 *
	 * @ingroup formzfield
	 *
	 * @access public
	 * @see Formz::addField
	 * @param string $name Aggregate field name.
	 * @param string $format Aggregate field value format.
	 * @see Formz::addListAction
	 */
	function addAggregateField($name, $format, $defaults = array()) {
		$defaults['type'] = 'aggregate';
		$defaults['format_string'] = $format;
		
		return $this->addField($name, $defaults);
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
	 * @ingroup formzaction
	 * @param string $name Action name.
	 * @param array $args Optional set of arguments for this action.
	 */
	function addAction($name, $args = array()) {
		// Default label, also capitalized...
		if (!isset($args['label'])) $args['label'] = format_label($name);
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
				if (!isset($args['link'])) {
					$args['link'] = ($this->isSluggable()) ? '%slug%' : '%id%';
				}
				if (!isset($args['type'])) $args['type'] = 'link';
				break;
			// nothing going yet for preview.
			case 'preview':
			default:
				if (!isset($args['type'])) $args['type'] = 'button';
				break;
		}
		if (!isset($args['value'])) $args['value'] = $args['label'];
		
		if ($args['type'] == 'link') {
			if (isset($args['class'])) {
				$args['class'] .= ' '. $name .'-link action-link';
			} else {
				$args['class'] = $name .'-link action-link';
			}
		}
		
		$this->_formActions[$name] = $args;
	}
	
	/**
	 * Return this form's actions.
	 *
	 * If no actions have been defined, this will return a default set of actions
	 * (namely 'save' and 'cancel')...
	 *
	 * @ingroup formzaction
	 * @access public
	 * @see Formz::addAction
	 */
	function getActions() {
		if (count($this->_formActions)) {
			return $this->_formActions;
		}
		else {
			// set some default actions for this form.
			if ($this->editable) {
				$this->addAction('submit');
				$this->addAction('cancel');
			}
			
			// now remove them from the form... odd, but allows code reuse.
			$actions = $this->_formActions;
			$this->_formActions = array();
			
			// return the actions we added.
			return $actions;
		}
	}
	
	/**
	 * Remove a form action.
	 *
	 * @ingroup formzaction
	 * @access public
	 * @param mixed $action Action (or array of actions) to remove.
	 */
	function removeAction($action) {
		foreach ((array)$action as $name) {
			if (isset($this->_formActions[$name])) {
				unset($this->_formActions[$name]);
			}
		}
	}
	
	/**
	 * Add an action to the list view for this form.
	 *
	 * Common form actions include 'add' or 'filter'. In fact, 'add' might be the only
	 * common list action. Who knows. We'll flesh this out further later...
	 *
	 * @ingroup formzaction
	 * @param string $name Action name.
	 * @param array $args Optional set of arguments for this action.
	 */
	function addListAction($name, $args = array()) {
		if (!isset($args['type']) && isset($args['link'])) $args['type'] = 'link';
		
		switch (strtolower($name)) {
			case 'more':
				if (!isset($args['label'])) $args['label'] = format_label($name) . ' &raquo;';
				break;
			case 'add':
				if (!isset($args['link'])) $args['link'] = 'create';
				if (!isset($args['type'])) $args['type'] = 'link';
				break;
			case 'paginate':
				if (!$this->isPaginated()) $this->setPaginated();
				if (!isset($args['page'])) $args['page'] = getGetInt('page');
				if (!$args['page']) $args['page']=1;
				if (!isset($args['type'])) $args['type'] = 'paginate';
				if (!isset($args['limit'])) $args['limit'] = Config::get('zoop.formz.paginate.limit');
				$this->_driver->setPage($args['page']);
				$this->_driver->setLimit($args['limit']);
				break;
			case 'search':
				// add the search field to this form
				$searchForm = array('name' => $this->tablename);
				
				// apply url query to this form
				if (isset($args['q'])) {
					$searchForm['q'] = $args['q'];
				} else {
					$searchForm['q'] = getGetText('q');
				}
				
				// apply the search!
				if (!empty($searchForm['q'])) $this->search($searchForm['q']);
				
				if (isset($args['redirect'])) $searchForm['redirect'] = $args['redirect'];
				$this->_searchForms[] = $searchForm;
				
				return;
				break;
			case 'filter':
				break;
			default:
				if (!isset($args['type'])) $args['type'] = 'button';
				break;
		}

		if (!isset($args['label'])) $args['label'] = format_label($name);
		if (!isset($args['value'])) $args['value'] = $args['label'];
		
		if ($args['type'] == 'link') {
			if (isset($args['class'])) {
				$args['class'] .= ' '. $name .'-link action-link';
			} else {
				$args['class'] = $name .'-link action-link';
			}
		}
		
		$this->_formListActions[$name] = $args;
	}
	
	/**
	 * Return this form's list view actions.
	 *
	 * Unlike Formz::getActions, if no actions have been defined, this function will
	 * return an empty set of actions.
	 *
	 * @ingroup formzaction
	 * @access public
	 * @see Formz::addListAction
	 */
	function getListActions() {
		return $this->_formListActions;
	}
	
	/**
	 * Return this form's list view actions.
	 *
	 * Unlike Formz::getActions, if no actions have been defined, this function will
	 * return an empty set of actions.
	 *
	 * @ingroup formzaction
	 * @access public
	 * @see Formz::addListAction
	 * @param string List action name
	 * @return void
	 */
	function removeListAction($action) {
		foreach ((array)$action as $name) {
			if (isset($this->_formListActions[$name])) {
				unset($this->_formListActions[$name]);
			}
		}
	}

	/**
	 * Add a row action. Analogous to list actions or form actions, but apply to a single row.
	 * 
	 * @ingroup formzaction
	 * @access public
	 * @param string $name Action name
	 * @param mixed $args Action arguments. (default: array())
	 * @return void
	 */
	function addRowAction($name, $args = array()) {
		// Default label, also capitalized...
		if (!isset($args['label'])) $args['label'] = format_label($name);
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
				if (!isset($args['class'])) $args['class'] = 'save';
				break;
			// all save actions need a submit button.
			case 'submit':
			case 'save':
			// more synonyms. this time for the D in CRUD.
			case 'delete':
			case 'destroy':
				if (!isset($args['link'])) $args['link'] = '%id%/destroy';
				if (!isset($args['type'])) $args['type'] = 'link';
				if (!isset($args['class'])) $args['class'] = 'delete-link';
				if (!isset($args['title'])) $args['title'] = 'Delete this record.';
				break;
			case 'edit':
			case 'update':
				if (!isset($args['link'])) $args['link'] = '%id%/update';
				if (!isset($args['type'])) $args['type'] = 'link';
				if (!isset($args['class'])) $args['class'] = 'edit-link';
				if (!isset($args['title'])) $args['title'] = 'Edit this record.';
				break;
			// nothing going yet for preview.
			case 'preview':
			default:
				if (!isset($args['type'])) $args['type'] = 'button';
				if (!isset($args['class'])) $args['class'] = 'preview';
				break;
		}

		$this->_formRowActions[$name] = $args;
	}

	/**
	 * Get the set of row actions for this formz list.
	 *
	 * @ingroup formzaction
	 * @access public
	 * @return array Row actions on this form
	 */
	function getRowActions() {
		return $this->_formRowActions;
	}
	
	/**
	 * Remove Row action
	 *
	 * @ingroup formzaction
	 * @access public
	 * @param mixed $action An action name (or array of names) to remove.
	 * @return void
	 */
	function removeRowAction($action) {
			foreach ((array)$action as $name) {
			if (isset($this->_formRowActions[$name])) {
				unset($this->_formRowActions[$name]);
			}
		}
	}

	
	/**
	 * Get the table name for this form
	 *
	 * @return $string tablename
	 */
	function getParentTablename() {
		return $this->parentTablename;
	}
	
	/**
	 * Set the parent table name for this form.
	 *
	 * This function is protected, since it's only used by formz when embedding other formz objects.
	 * You shouldn't ever need to call this function.
	 *
	 * @param string $parentTablename
	 * @return void
	 */
	protected function setParentTablename($parentTablename) {
		$this->parentTablename = $parentTablename;
	}

	/**
	 * Get the id for this form's parent
	 *
	 * @return $string parent id
	 */
	function getParentId() {
		return $this->parentId;
	}

	/**
	 * Set the parent id for this form.
	 *
	 * @param string $parentId
	 * @return void
	 */
	function setParentId($parentId) {
		$this->parentId = $parentId;
	}
	
	/**
	 * Add an embedded form for the given field.
	 *
	 * Access using a FormField object. Optionally, pass a Formz object to embed.
	 *
	 * @code
	 *    $form->field('person')->setEmbeddedForm();
	 * @endcode
	 *
	 * Returns the formz object which has been embedded in the parent form. This can then
	 * be used to manipulate fields of the child form.
	 * 
	 * @access public
	 * @param string $fieldname
	 * @param mixed $form. (default: true)
	 * @return Formz
	 */
	function setFieldEmbeddedForm($fieldname, $form = true) {
		if ($form && !($form instanceof Formz)) {
			$fields = $this->getFields($fieldname);
			if (isset($fields[$fieldname]) && $fields[$fieldname]['type'] == 'relation') {
				$form = new Formz($fields[$fieldname]['rel']['class']);
			} else {
				trigger_error("Formz field $fieldname is not a relation field, unable to embed Formz object.");
			}
		}
		$form->setEmbedded(true);
		$this->setFieldParam('embeddedForm', $fieldname, $form);
		// if you embed the form, you wants to show it.
		$this->setFieldParam('formshow', $fieldname, true);
		return $form;
	}
	
	function setFieldnamePrefix($prefix) {
		$this->fieldnamePrefix = $prefix;
	}
	
	/**
	 * Populate placeholders in a string with values from an actual record.
	 *
	 * Much like sprintf, you can pass a formatted string with placeholders, and this formz object
	 * will convert the placeholders to values from db records. This is mostly used for rendering
	 * listlinks, by setting them to something like: '/user/%id%/create'
	 *
	 * If this is used in the context of a record view, don't pass a record id, as this formz object
	 * already has a record. When used in a list view, a record id must be supplied for each call
	 * to this function. If available, passing an array of the record is recommended to reduce the
	 * number of database queries necessary for a list view.
	 *
	 * This function also accepts a $urlencode_values parameter. {@see formz::populateURL}
	 * 
	 * @access public
	 * @param string $string 
	 * @param mixed $record_id Optionally, supply a record id to retrieve values from.
	 * @param array $record Optionally, supply a record array to grab values from.
	 * @param bool $urlencode_values
	 * @return string Formatted string.
	 */
	function populateString($string, $record_id = null, $record = array(), $urlencode_values = false) {
		if (isset($this->_populatedStrings[$record_id][$string][$urlencode_values])) {
			return $this->_populatedStrings[$record_id][$string][$urlencode_values];
		}
		
		$matches = array();
		preg_match_all('#%([a-zA-Z0-9_\.]+?)%#', $string, $matches);

		if (count($matches)) {
			$id_field = $this->getIdField();
			if ($sluggable = $this->isSluggable()) {
				$slug_field = $this->getSlugField();
			}

			$fields = $matches[1];
			$from = $matches[0];
			$to = array();

			foreach ($from as $i => $match) {
				// if we have a $record array, use value from that if possible.
				if (isset($record[$fields[$i]])) {
					$to[$i] = $record[$fields[$i]];
					if ($urlencode_values) $to[$i] = urlencode($to[$i]);
					break;
				}

				// replace with this table's id field, if applicable.
				if ($fields[$i] == 'id') {
					$fields[$i] = $id_field;
					if (!is_null($record_id)) {
						$to[$i] = $record_id;
						if ($urlencode_values) $to[$i] = urlencode($to[$i]);
						break;
					}
				}
				if ($sluggable && $fields[$i] == 'slug') $fields[$i] = $slug_field;
				
				$to[$i] = $this->getValue($fields[$i], $record_id);
				if ($urlencode_values) $to[$i] = urlencode($to[$i]);
			}
			$new_string = str_replace($from, $to, $string);
		}

		if (!is_null($record_id)) $this->_populatedStrings[$record_id][$string][$urlencode_values] = $new_string;
		return $new_string;
	}
	
	/**
	 * A shortcut for URL encoding string population values.
	 * 
	 * @access public
	 * @return void
	 */
	function populateURL($string, $record_id = null, $record = array()) {
		return $this->populateString($string, $record_id, $record, true);
	}

	/**
	 * Get search forms. Search forms are a special case of ListAction, and are used by the
	 * formz_list guiplugin.
	 *
	 * @access public
	 */
	function getSearchForms() {
		return $this->_searchForms;
	}
	
	/**
	 * Get the set of searchable fields on this form.
	 * 
	 * @access public
	 * @return array Searchable fields.
	 */
	private function getSearchableFields() {
		if (!$this->isSearchable()) return array();
		return array_merge($this->_searchableFields, $this->_driver->getSearchableFields());
	}
	

	/**
	 * Helper function to set fields as searchable. This will generally only be used to set things
	 * as searchable (true), but can be used to unset searchability as well...
	 * 
	 * @code
	 *    $form->field('User.first_name')->setSearchable();
	 * @endcode
	 *
	 * @access private
	 * @param mixed $fieldname
	 * @param mixed $searchable. (default: true)
	 * @return void
	 */
	private function setFieldSearchable($fieldname, $searchable = true) {
		if ($searchable) {
			$this->_searchableFields[$fieldname] = $fieldname;
		} else if (isset($this->_searchableFields[$fieldname])) {
			unset($this->_searchableFields[$fieldname]);
		}
	}

	/**
	 * Set a search constraint for this formz list.
	 *
	 * @access public
	 */
	function search($query) {
		$this->_driver->setSearchFields($this->getSearchableFields());
		$this->_driver->search($query);
	}
	
	function setSearchToken($search_token) {
		deprecated("setSearchToken has been deprecated. call \$form->search('$search_token'); instead.");
		return $this->search($search_token);
	}
	function addSearchForm($name, $args = array()) {
		deprecated("addSearchForm has been deprecated. call \$form->addListAction('search', \$args); instead.");
		return $this->addListAction('search', $args);
	}
	function addSearchFormset($args) {
		$fields = array();
		foreach ($args as $table => $val) {
			foreach ($val as $field) {
				if (strtolower($table) == strtolower($this->tablename)) $fields[] = $field;
				else $fields[] = $table . '.' . $field;
			}
		}
		$s = (count($fields) > 1) ? 's' : '';
		$field_string = implode("', '", $fields);
		
		deprecated("addSearchFormset has been deprecated. call \$form->field" . $s . "('" . $field_string . "')->setSearchable(); instead.");
		$this->fields($fields)->setSearchable();
	}

	/**
	 * Set the results limit
	 *
	 * Set the limit on the number of results to return
	 *
	 * @access public
	 */
	function setLimit($limit) {
		$this->_driver->setLimit($limit);
	}

	/**
	 * Returns true if this Formz is searchable.
	 *
	 * @access public
	 * @return bool True if this is searchable.
	 */
	function isSearchable() {
		$this->searchable = $this->_driver->isSearchable();
		return $this->searchable;
	}
	
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
	 * @ingroup sluggable
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
	 * Returns true if this form uses pagination.
	 * 
	 * @access public
	 * @return bool
	 */
	function isPaginated() {
		return $this->_driver->isPaginated();
	}
	
	/**
	 * Enable pagination on this form.
	 * 
	 * @access public
	 * @param boolean $value. (default: true)
	 * @return bool current paginated value
	 */
	function setPaginated($value = true) {
		if ($this->isTree()) {
			return false;
		}
		$this->_driver->setPaginated($value);
		if ($value) $this->addListAction('paginate');
		return $this->isPaginated();
	}
	
	/**
	 * Set a parent record (used in tree data sets).
	 *
	 * This is currently a Doctrine specific call. If using a FormDB based Formz object, this
	 * call will not work.
	 * 
	 * @param string $parent Name of parent record.
	 * @access public
	 * @return void
	 */
	function setParentRecord($parent = null) {
		if ($parent === null) {
			trigger_error('Parent record name required when using "setParentRecord"');
		}
		return $this->_driver->setParentRecordName($parent);
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
	 * @ingroup sluggable
	 * @access public
	 * @return bool True if this is sluggable.
	 */
	function getSlugField() {
		$this->slug_field = $this->_driver->getSlugField();
		return $this->slug_field;
	}
	
	/**
	 * Returns the slug for this table (if applicable).
	 *
	 * @see Formz::isSluggable()
	 * @ingroup sluggable
	 * @access public
	 * @param mixed $id Optionally, supply an ID for which to grab the slug.
	 * @return string Slug value for this record
	 */
	function getSlug($id = null) {
		if (!$this->isSluggable()) return false;
		return $this->getValue($this->getSlugField(), $id);
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
	
	/**
	 * Return the best guess at a title field for this formz object.
	 *
	 * Title field priority is set by the config parameter zoop.formz.title_field_priority.
	 *
	 * @access public
	 * @return string Database title field
	 */
	function getTitleField() {
		$label_field = $this->getIdField();

		$fields = $this->getFields();
		foreach(Config::get('zoop.formz.title_field_priority') as $field_name){
			if (isset($fields[$field_name])) {
				$label_field = $field_name;
				break;
			}
		}

		return $label_field;
	}
	
	/**
	 * Return the best guess at a title for this formz object. Returns the value of the column
	 * returned by Formz::getTitleField()
	 *
	 * @access public
	 * @see Formz::getTitleField
	 * @return string Record title
	 */
	function getTitle($id = null) {
		return $this->getValue($this->getTitleField(), $id);
	}

	function &getDoctrineQuery() {
		return $this->_driver->getDoctrineQuery();
	}

	function &getDoctrineRecord() {
		return $this->_driver->getDoctrineRecord();
	}
	
	function __dump() {
		$ret = array();
		
		$ret['values'] = ($this->type == 'record') ? $this->getData(): $this->getRecords();
		
		foreach ($this as $_key => $_val) {
			switch ($_key) {
				case '_fields':
					$ret[$_key] = $this->getFields();
					foreach ($ret[$_key] as $field => $field_val) {
						if (isset($ret[$_key][$field]['embeddedForm'])) {
							$ret[$_key][$field]['embeddedForm'] = $ret[$_key][$field]['embeddedForm']->__dump();
						}
					}
					break;
				case '_relation_fields':
					$ret[$_key] = $this->getTableRelations();
					break;
				case '_driver':
					$ret[$_key] = get_class($_val);
					break;
				case '_order':
					if (empty($_val)) $_val = array_keys($this->getFields());
					$ret[$_key] = $_val;
					break;
				case 'valid_properties':
					continue;
					break;
				case '_searchableFields':
					$ret[$_key] = $this->getSearchableFields();
				default:
					$ret[$_key] = $_val;
					break;
			}
		}
		
		return $ret;
	}
	
	/**
	 * Get a FormzField object for the given field name.
	 *
	 * This is super rad, since it lets you do things like this:
	 *
	 * @code
	 *    $myform->field('password')
	 *       ->setDisplayType('betterpassword')
	 *       ->setRequired()
	 *       ->setEditable()
	 *       ->setLabel('New Password');
	 * @endcode
	 *
	 * @ingroup formzfield
	 *
	 * @access public
	 * @param string $name Field name
	 * @return FormzField
	 */
	function field($name) {
		return new FormzField($name, $this);
	}
	
	/**
	 * Get a FormzField collection for the given field names.
	 *
	 * Like Formz::field, but returns multiple fields, like so:
	 *
	 * @code
	 *    $myform->fields('foo', 'bar')
	 *       ->setListshow()
	 *       ->setRequired()
	 *       ->setEditable()
	 *       ->setLabel('SAME LABEL!');
	 *    $myform->fields(array('foo', 'bar'));
	 *    $myform->fields('foo', 'bar');
	 * @endcode
	 *
	 * Names can be passed as an array, or just a list. The following are equivalent:
	 *
	 * @code
	 *    $myform->fields(array('foo', 'bar'));
	 *    $myform->fields('foo', 'bar');
	 * @endcode
	 *
	 * @ingroup formzfield
	 * @see Formz::field
	 *
	 * @access public
	 * @param mixed $names Field names to chunk into a collection.
	 * @return FormzFieldCollection
	 */
	function fields($names) {
		if ($names == '*') {
			$fields = array_keys($this->getFields());
		} else {
			$fields = array_smash(func_get_args());
		}
		return new FormzFieldCollection($fields, $this);
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
			$param_name = lcfirst(substr($method, 15));
			if (is_array($args[0])) {
				deprecated('$form->setFieldFoo() calls are deprecated. Use `$form->fields("'.implode('", "', $args[0]).'")->setDisplay'.ucfirst($param_name).'($args);`');
			} else {
				deprecated('$form->setFieldFoo() calls are deprecated. Use `$form->field("'.$args[0].'")->setDisplay'.ucfirst($param_name).'($args);`');
			}
			array_unshift($args, $param_name);
			return call_user_func_array(array($this, 'setFieldDisplay'), $args);
		}
		else if (substr($method, 0, 8) == 'setField') {
			$param_name = lcfirst(substr($method, 8));
			
			if (is_array($args[0])) {
				deprecated('$form->setFieldFoo() calls are deprecated. Use `$form->fields("'.implode('", "', $args[0]).'")->set'.ucfirst($param_name).'($args);`');
			} else {
				deprecated('$form->setFieldFoo() calls are deprecated. Use `$form->field("'.$args[0].'")->set'.ucfirst($param_name).'($args);`');
			}
			
			array_unshift($args, $param_name);
			return call_user_func_array(array($this, 'setFieldParam'), $args);
		}
		else if (substr($method, 0, 10) == 'setDisplay') {
			$param_name = lcfirst(substr($method, 10));
			
			array_unshift($args, $param_name);
			return call_user_func_array(array($this, 'setDisplay'), $args);
		}
		else if (substr($method, 0, 3) == 'set') {
			// @TODO we need to get rid of this setter. no me gusta.
			$param_name = lcfirst(substr($method, 3));
			
			array_unshift($args, $param_name);
			return call_user_func_array(array($this, 'setParam'), $args);
		}
		else if (substr($method, 0, 3) == 'get') {
			$param_name = lcfirst(substr($method, 3));
			
			if (isset($this->$param_name)) return $this->$param_name;
			else trigger_error($method . " method undefined on Formz object.");
		}
		else {
			trigger_error($method . " method undefined on Formz object.");
		}
	}
}
