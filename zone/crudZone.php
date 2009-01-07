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
 * Create-Read-Update-Destroy zone, to be extended by zones implementing CRUD.
 * 
 * @group crud
 * 
 * CrudZone is an implementation of a basic, database independant, Formz based CRUD
 * controller.
 *
 * {@link http://en.wikipedia.org/wiki/Create,_read,_update_and_delete}
 * 
 * The Zoop CRUD zone consists of a Formz object (located at $this->form), which it manipulates
 * to perform each of the CRUD operations. It has a default set of actions, urls, aliases and
 * templates, but each can be overridden by extending classes.
 *
 *
 * @section Create
 *
 * Create operations in CrudZone are handled as a special case of 'Update'. Thus, there is no
 * 'Create' handler, only an alias to 'crud/new/update'.
 *
 * To modify the Create form, use the CrudZone::initCreateForm() hook.
 *
 *
 * @section Read
 *
 * To modify the Read form, use the CrudZone::initReadForm() hook.
 *
 *
 * @section Update
 *
 * To modify the Update form, use the CrudZone::initUpdateForm() hook.
 *
 *
 * @section Destroy
 *
 * To modify the Destroy form, use the CrudZone::initDestroyForm() hook.
 *
 *
 * @section List
 *
 * A fifth operation handled by the Zoop CRUD zone is record lists. These can be seen as a 
 * special case of Read, as in 'crud/all/read'
 *
 * To modify the List form, use the CrudZone::initListForm() hook.
 *
 * 
 * @section Security
 *
 * Before each CRUD operation, the CrudZone::checkAuth() hook is called, with the intended
 * operation passed as a parameter. Unless overridden, this hook always returns true.
 * 
 * An extending class might integrate the Auth component, or tie into an external library or
 * authentication system.
 *
 * @code
 *    function checkAuth($action) {
 *       switch ($action) {
 *          case 'create':
 *             return Auth::gi()->checkLoggedIn();
 *             break;
 *          case 'read':
 *             return true;
 *             break;
 *          case 'update':
 *             return Auth::gi()->checkGroup('admin');
 *             break;
 *          case 'destroy':
 *             return Auth::gi()->checkGroup('admin');
 *             break;
 *          case 'list':
 *             return true;
 *             break;
 *          default:
 *             return false;
 *             break;
 *       }
 *    }
 * @endcode
 *
 * 
 * @endgroup
 *
 * @ingroup zone
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 **/
class CrudZone extends zone {

	var $tableName;
	var $form;
	
	// TODO: make this more flexible: allow fieldname to do "find by..."
	var $indexFieldname = '';

	// this should be in an init function or something :-/
	var $zoneParamNames = array('record_id');
	var $wildcards = '';
	
	var $immutableFields = null;
	var $foreign_relation_key = 'parent_id';
	
	/**
	 * Default URL aliases for CRUD actions. This lets you use 'zonename/foo/edit' instead of
	 * 'zonename/foo/update', for example.
	 *
	 * Override this in an extending zone if you want anything other than the default set:
	 *
	 * @code
	 *   zone/
	 *   zone/create
	 *   zone/1/edit
	 *   zone/1/delete
	 * @endcode
	 *
	 * @see zone::addAlias
	 */
	var $Aliases = array(
		'create' => 'new/update',
		'%id%/edit' => '%id%/update',
		'%id%/delete' => '%id%/destroy',
	);

	/**
	 * CRUD Zone constructor.
	 *
	 * Use the CrudZone::construct() hook in extending classes rather than overloading this constructor.
	 *
	 * @see CrudZone::construct();
	 * @access public
	 * @return void;
	 */
	final function __construct() {
		if (isset($this->Aliases) && count($this->Aliases)) {
			$this->addAliases($this->Aliases);
			$this->Aliases = array();
		}
		$this->form = new Formz($this->tableName);
		
		$this->construct();
	}
	
	/**
	 * Initialize this CRUD Zone.
	 *
	 * Use the CrudZone::initCrudZone() hook in extending classes rather than overloading this method.
	 *
	 * @see CrudZone::initCrudZone();
	 */
	function initZone() {
		if (Config::get('zoop.zone.crud_zone.auto_paginate')) {
			$this->form->setPaginated();
		}
		$this->initCrudZone();
	}


	////////////////////////////////////////////////////////
	//
	//  HOOKS.
	//
	////////////////////////////////////////////////////////


	/**
	 * Hook so overloading can place things into the constructor without calling parent::__construct each time
	 * 
	 * @access public
	 * @return void
	 */
	function construct() { }


	/**
	 * Hook run inside initZone so Crud Zone can use it and extending classes can run code without
	 * calling parent::initZone each time.
	 * 
	 * @access public
	 * @return void
	 */
	function initCrudZone() { }


	/**
	 * initListForm is provided to extending classes, allowing CRUD zones to modify the form
	 * object just before executing and returning a List.
	 *
	 * This CRUD zone's form object is located at $this->form
	 *
	 * This is also a good place to overload the template used to generate Read forms.
	 * 
	 * @code
	 *    $this->setTemplate($this->canonicalizeTemplate('customListTemplate.tpl'));
	 * @endcode
	 *
	 * @see CrudZone::setTemplate
	 * @access public
	 * @return void
	 */
	function initListForm() { }

	/**
	 * initCreateForm is provided to extending classes, allowing CRUD zones to modify the form
	 * object just before executing and returning a Create form.
	 *
	 * This CRUD zone's form object is located at $this->form
	 *
	 * This is also a good place to overload the template used to generate Read forms.
	 * 
	 * @code
	 *    $this->setTemplate($this->canonicalizeTemplate('customCreateTemplate.tpl'));
	 * @endcode
	 *
	 * @see CrudZone::setTemplate
	 * @access public
	 * @return void
	 */
//	function initCreateForm() { }

	/**
	 * initReadForm is provided to extending classes, allowing CRUD zones to modify the form
	 * object just before executing and returning a Read form.
	 *
	 * This CRUD zone's form object is located at $this->form
	 *
	 * This is also a good place to overload the template used to generate Read forms.
	 * 
	 * @code
	 *    $this->setTemplate($this->canonicalizeTemplate('customReadTemplate.tpl'));
	 * @endcode
	 *
	 * @see CrudZone::setTemplate
	 * @access public
	 * @return void
	 */
	function initReadForm() { }
	
	/**
	 * initUpdateForm is provided to extending classes, allowing CRUD zones to modify the form
	 * object just before executing and returning an Update form.
	 *
	 * This CRUD zone's form object is located at $this->form
	 *
	 * This is also a good place to overload the template used to generate Read forms.
	 * 
	 * @code
	 *    $this->setTemplate($this->canonicalizeTemplate('customUpdateTemplate.tpl'));
	 * @endcode
	 *
	 * @see CrudZone::setTemplate
	 * @access public
	 * @return void
	 */
	function initUpdateForm() { }
	
	/**
	 * initDestroyForm is provided to extending classes, allowing CRUD zones to modify the form
	 * object just before executing and returning a Destroy confirmation form.
	 *
	 * This CRUD zone's form object is located at $this->form
	 *
	 * This is also a good place to overload the template used to generate Read forms.
	 * 
	 * @code
	 *    $this->setTemplate($this->canonicalizeTemplate('customDestroyTemplate.tpl'));
	 * @endcode
	 *
	 * @see CrudZone::setTemplate
	 * @access public
	 * @return void
	 */
	function initDestroyForm() { }


	/**
	 * Hook for adding authentication to CRUD.
	 *
	 * By default this method will *always* return true. This is on purpose. Otherwise, CrudZone would
	 * deny access to everything by default, and would be useless...
	 * 
	 * Override this method in an extending class so you can implement authentication. Implementing
	 * subclasses should either redirect to a 'denied' page, or return false if authentication is denied.
	 *
	 * @access public
	 * @param string $action
	 *   CRUD action to authenticate. Will be 'create', 'read', 'update', 'destroy' or 'list'.
	 * @return bool Return 'false' from overriding methods to stop the requested action from happening.
	 */
	function checkAuth($action) {
		return true;
	}



	////////////////////////////////////////////////////////
	//
	//  Page and Post handlers
	//
	////////////////////////////////////////////////////////


	/**
	 * CrudZone index page.
	 *
	 * Provides 'Read' functionality of CRUD. This zone has two functions: If an identifier is
	 * passed to the zone (first and only ZoneParam), display that record. This is the equivalent
	 * of CRUD/record_id/read.
	 *
	 * The second function of the index page is to provide a list view if a record id isn't provided.
	 * This is the equivalent of CRUD/(all)/read.
	 *
	 * @access public
	 * @return void
	 */
	function initIndex() {
		$record_id = $this->getZoneParam('record_id');
		if ($record_id === '' || 'index' == strtolower($record_id) || 'index' == strtolower(substr($record_id, 0, strrpos( $record_id, '.')) ) ) {
			if (!$this->checkAuth('list')) return;
			
			// show all the records.
			$this->_getRecords();
			$this->_listRecords();
		} else {
			if (!$this->checkAuth('read')) return;
			
			if ($this->form->isSluggable()) {
				$record_id = $this->getRecordIdBySlug($record_id);
				if ($record_id === null) {
					$this->responsePage(404);
					return;
				}
			} else if (!is_numeric($record_id)) {
				$this->responsePage(404);
				return;
			}
			
			$this->_detailRecord($record_id);
		}
	}

	/**
	 * Render this crudZone as HTML.
	 */
	function htmlIndex() {
		$this->_loadAndGenerateForm();
	}

	/**
	 * Set the editable flag of the form. 
	 * Assign the form to the gui under a given name, default 'form'
	 * Generate the gui using given template default to standard formz template 
	 * 
	 * @param mixed $name 
	 * @param string $template 
	 * @param mixed $editable 
	 * @access public
	 * @return void
	 */
	function _loadAndGenerateForm($name = 'form', $template = null, $editable = false) {
		global $gui;

		if (!$template) {
			// grab the default formz template
			$template = $this->getTemplate();
/*
			// check if there's a template for rendering this specific object.
			$template = Config::get('zoop.gui.crud_read_templates.objects.' . strtolower($this->tableName), $template);
			
			// check if there's a template for rendering formz in this zone.
			$parents = array_reverse(array_merge(array($this->getName()), $this->getAncestors()));
			array_shift($parents);
			$template = Config::get('zoop.gui.crud_read_templates.zones.' . implode('.', $parents), $template);
*/
		}
		$this->form->setEditable($editable);
		$this->form->guiAssign($name);
		$gui->generate($template);
	}

	/**
	 * Override the default zone::getTemplate() method to allow a default config fallback template.
	 * 
	 * @see ZoneZone::getTemplate
	 * @access protected
	 * @return string Template file
	 */
	protected function getTemplate() {
		if ($template = parent::getTemplate()) {
			return $template;
		} else {
			return Config::get('zoop.gui.templates.formz');
		}
	}

	/**
	 * Creates a form object for a given record_id 
	 *
	 * NOTE: CrudZone subclasses that override _detailRecord MUST handle 404 themselves.
	 * 
	 * @param mixed $record_id 
	 * @access public
	 * @return void
	 */
	function _detailRecord($record_id) {
		if ($record_id !== null) {
			if ($this->form->getRecord($record_id) === null) {
				$this->responsePage(404);
				return;
			}
		}
		$this->form->setEditable(false);
		
		$this->initReadForm();
	}

	function _getRecords() {
		$this->setData($this->form->getRecords());
	}

	/**
	 * Creates a form object for a list and sets some sane defaults 
	 * 
	 * @access public
	 * @return void
	 */
	function _listRecords() {
		$link = ($this->form->isSluggable()) ? '%slug%' : '%id%';
		$this->form->fields($this->form->getIdField(), $this->form->getTitleField())->setListlink($link);

		$this->form->addRowAction('edit');
		$this->form->addRowAction('delete');
		$this->form->setEditable(false);
		
		$this->initListForm();
	}
	
	/**
	 * Page handler for CRUD Read action.
	 *
	 * Displays the requested record.
	 *
	 * @access public
	 * @return void
	 **/		
	function pageRead() {
		// redirect to pageDefault instead? they're the same...
		$this->zoneRedirect('');
	}
	
	/**
	 * Page handler for CRUD Update action.
	 *
	 * Displays an 'edit' form prepopulated with the requested record.
	 *
	 * @see postUpdate()
	 * @access public
	 * @return void
	 **/
	function pageUpdate() {
		if (!$this->checkAuth('update')) return;
		
		global $gui;
		
		$record_id = $this->getZoneParam('record_id');
		if ($record_id) {
			$this->form->getRecord($record_id);
		} else {
			trigger_error('CRUD record id not found.');
		}
		
		$this->form->setEditable(true);
		
		$this->form->addAction('save');
/* 		$this->form->addAction('preview'); */

		if ($record_id == 'new') {
			$this->form->addAction('saveandnew');
			$this->form->addAction('cancel', array('link' => ''));
		} else {
			$this->form->addAction('delete', array('link' => '%id%/destroy'));
			$link = ($this->form->isSluggable()) ? '%slug%' : '%id%';
			$this->form->addAction('cancel', array('link' => $link));
		}

		if ($record_id == 'new' && method_exists($this, 'initCreateForm')) {
			$this->initCreateForm();
		} else {
			$this->initUpdateForm();
		}
		$this->form->guiAssign();
		
		$gui->generate($this->getTemplate());
	}

	/**
	 * POST handler for CRUD Update action.
	 *
	 * @see pageUpdate()
	 * @access public
	 * @return void
	 **/
	function postUpdate() {
		if (!$this->checkAuth('update')) return;
		
		if ($this->recordId() == 'new' && method_exists($this, 'initCreateForm')) {
			$this->initCreateForm();
		} else {
			$this->initUpdateForm();
		}

		if (getPostText('update') || getPostText('update_and_create')) {
			// save the posted values.
			$id = $this->saveUpdatePost();
		} else if (getPostText('destroy')) {
			// redirect to the destroy page if they're trying to delete this item...
			$this->zoneRedirect('destroy');
			return;
		}

		$this->_postUpdateRedirect();
	}

	/**
	 * After the Update Form is posted, determine where to send the user 
	 * 
	 * @access protected
	 * @return void
	 */
	protected function _postUpdateRedirect() {
		if (getPostText('update_and_create')) {
			BaseRedirect($this->getZoneBasePath() . '/create', HEADER_REDIRECT);
		} else {
			BaseRedirect($this->getZoneBasePath(), HEADER_REDIRECT);
		}
	}
	
	/**
	 * Save the POSTed values to this formz object's record.
	 *
	 * @access protected
	 * @return int Record ID
	 */
	protected function saveUpdatePost() {
		$values = $this->getRecordValues($this->form);
		return $this->form->saveRecord($values);
	}
	
	protected function getRecordValues(&$form, $post_prefix = '') {
		$values = array();
		foreach ($form->getFields() as $name => $field) {
			if ((!isset($field['editable']) || $field['editable'])
				&& (!isset($field['formshow']) || $field['formshow'])) {

				$controls = $GLOBALS['controls'];
				if ((isset($controls['betterpassword']) && isset($controls['betterpassword'][$post_prefix . $name]))
					|| (isset($controls['password']) && isset($controls['password'][$post_prefix . $name]))) {
						if (!getPostIsset($post_prefix . $name)) continue;
				}
				
				switch($field['type']) {
					case 'boolean':
					case 'bool':
						$values[$name] = getPostBool($post_prefix . $name);
						break;
					case 'relation':
						if (isset($field['embeddedForm']) && $field['embeddedForm']) {
							$values[$name] = $this->getRecordValues($field['embeddedForm'], $post_prefix . $name . '.');
						} else {
							switch ($field['rel_type']) {
								case Formz::MANY:
									if (getPostIsset($post_prefix . $name)) {
										$values[$name] = getPost($post_prefix . $name);
										if (!is_array($values[$name])) $values[$name] = array();
									}
									break;
								case Formz::ONE:
									if (getPostIsset($post_prefix . $name) && getPost($post_prefix . $name) !== '') {
										$values[$name] = getPost($post_prefix . $name);
									}
									break;
							}
						}
						break;
					default:
						$values[$name] = getPost($post_prefix . $name);
						break;
				}
			}
		}
		return $values;
	}
	
	/**
	 * Page handler for CRUD Destroy action.
	 *
	 * Displays 'delete' confirmation page.
	 *
	 * @see CrudZone::postDestroy
	 * @access public
	 * @return void
	 **/		
	function pageDestroy() {
		if (!$this->checkAuth('destroy')) return;
		
		global $gui;
		
		$record_id = $this->getZoneParam('record_id');
		if ($this->form->getRecord($record_id) == null) {
			$this->responsePage(404);
			return;
		}
		
		// Come up with a title for this bad boy.
		$record_data = $this->form->getData();
		$label_field = $this->form->getTitleField();
		$title_field = $record_data[$label_field];
		$id_field = $this->form->getIdField();
		
		$message = Config::get('zoop.zone.crud_zone.messages.confirm_delete');
		$message = str_replace(array('%id%', '%title%'), array($record_id, $title_field), $message);
		
		$this->form->field('*')->setFormshow(false);
		$this->form->field($id_field)->setFormshow();
		
		$this->form->setEditable(true);

		$this->form->addAction('delete');
		$link = ($this->form->isSluggable()) ? '%slug%' : '%id%';
		$this->form->addAction('cancel', array('link' => $link));

		$this->initDestroyForm();
		$this->form->guiAssign();
		
		$gui->assign('message', $message);
		$gui->generate($this->getTemplate());
	}

	/**
	 * POST handler for CRUD Destroy action.
	 *
	 * @see CrudZone::pageDestroy
	 * @access public
	 * @return void
	 **/
	function postDestroy() {
		if (!$this->checkAuth('destroy')) return;
		
		if (getPostText('destroy')) {
			$id = getPostInt($this->form->getIdField());
			$this->form->destroyRecord($id);
		}
		BaseRedirect($this->getZoneBasePath());
	}

	/**
	 * Page handler for CRUD add relation action.
	 *
	 * Displays an 'edit' form prepopulated with the requested record.
	 *
	 * @see postAddRelation()
	 * @access public
	 * @return void
	 **/
	function pageAddRelation() {
		if (!$this->checkAuth('update')) return;
		global $gui;

		$params = $this->getPageParams();		
		$parentTable = $this->form->tablename;
		$parentTableIdField = $this->form->getIdField();

		$record_id = $this->form->getRecord($this->recordId());
		
		if (isset($params[0]) && !empty($params[0])) {
			$this->form = new Formz($params[0]);
		} else {
			$this->responsePage(404);
			return;
		}

		if (!$this->immutableFields) {
			$immutableFields = $this->form->getImmutableForeignFields($parentTable);
		} else {
			$immutableFields = $this->form->getImmutableForeignFields($parentTable, $this->immutableFields);
		}

		if ($record_id) {
			$this->form->setParentId($record_id);
		} else {
			die;
			$this->responsePage(404);
			return;
		}

		$this->form->type = 'record';
		$this->form->setEditable(true);

		if (!empty($immutableFields)) {
			$this->form->fields($immutableFields)->setFormshow(false);
		}
		
		$this->form->addAction('save');
		if ($record_id == 'new') {
			$this->form->addAction('saveandnew');
			$this->form->addAction('cancel', array('link' => ''));
		} else {
			$this->form->addAction('delete', array('link' => '%id%/destroy'));
			$link = ($this->form->isSluggable()) ? '%slug%' : '%id%';
			$this->form->addAction('cancel', array('link' => $link));
		}

		$this->initUpdateForm();
		$this->form->guiAssign();
		$gui->generate($this->getTemplate());
	}

	/**
	 * POST handler for CRUD add relation action.
	 *
	 * @see pageAddRelation()
	 * @access public
	 * @return void
	 **/
	function postAddRelation() {
		if (!$this->checkAuth('update')) return;
		if (getPostText('update') || getPostText('update_and_create')) {
			$params = $this->getPageParams();

			if (!isset($params[0]) || empty($params[0])) {
				$this->responsePage(404);
				return;
			}

			$parentTable = $this->form->tablename;
			$parentTableIdField = $this->form->getIdField();
			
			$record_id = $this->getZoneParam('record_id');
			if ($this->form->isSluggable()) {
				$record_id = $this->form->getRecordBySlug($record_id);
			} else {
				$record_id = $this->form->getRecord($record_id);
			}

			if ($record_id === null) {
				$this->responsePage(404);
				return;
			}

			$values = array();

			$values['parent_table'] = $parentTable;
			$values['child_table'] = $params[0];

			$child_form = new Formz($params[0]);

			$fields = $child_form->getFields();
			unset($fields['id']);

			foreach ($fields as $name => $field) {
				if ((!isset($field['editable']) || $field['editable'])
					&& (!isset($field['formshow']) || $field['formshow'])) {
					switch($field['type']) {
						case 'boolean':
						case 'bool':
							$values[$name] = getPostBool($name);
							break;
						case 'relation':
							switch ($field['rel_type']) {
								case Formz::MANY:
									if (getPostIsset($name)) {
										$values[$name] = getPost($name);
										if (!is_array($values[$name])) $values[$name] = array();
									}
									break;
								case Formz::ONE:
									if (getPostIsset($name) && getPost($name) !== '') {
										$values[$name] = getPost($name);
									}
									break;
							}
							break;
						default:
							$values[$name] = getPost($name);
							break;
					}
				}
			}
			$id = $this->form->createRelation($values, $record_id);
		} else if (getPostText('destroy')) {
			// redirect to the destroy page if they're trying to delete this item...
			$this->zoneRedirect('destroy');
			return;
		}

		if (getPostText('update_and_create')) {
			BaseRedirect($this->getZonePath(0) . '/create', HEADER_REDIRECT);
		} else {
			BaseRedirect($this->getZonePath(0), HEADER_REDIRECT);
		}
	}

	/**
	 * Return a record id for a given slug
	 *
	 * @access public
	 * @param string $slug
	 * @return int Record id.
	 */
	function getRecordIdBySlug($slug) {
		return $this->form->getRecordIdBySlug($slug);
	}
	
	/**
	 * Return the record id for this CRUD zone (if set).
	 *
	 * Grabs the requested record from the zone param. If this is a sluggable CRUD zone,
	 * converts param from slug to id.
	 *
	 * @access public
	 * @return mixed Requested record ID
	 */
	function recordId() {
		$record_id = $this->getZoneParam('record_id');
		if ($this->form->isSluggable()) {
			$record_id = $this->getRecordIdBySlug($record_id);
		}
		return $record_id;
	}
}
