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
 * @ingroup crud
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
	 * Crud zone constructor.
	 *
	 * Add all aliases, initialize Formz object for the zone's table/model.
	 *
	 * Be sure to call parent::__construct() if you provide a constructor in any zone extending
	 * CrudZone.
	 *
	 */
	function __construct() {
		if (isset($this->Aliases) && count($this->Aliases)) {
			$this->addAliases($this->Aliases);
			$this->Aliases = array();
		}
		$this->form = new Formz($this->tableName);
		
		$this->construct();
	}

	/**
	 * Hook so overloading can place things into the constructor without calling parent::__construct each time
	 * 
	 * @access public
	 * @return void
	 */
	function construct() { } 

	function initZone() {
		if (Config::get('zoop.zone.crud_zone.auto_paginate')) {
			$this->form->setPaginated();
		}
		$this->initCrudZone();
	}

	/**
	 * Hook run inside initZone so Crud Zone can use it and extending classes can run code without calling parent::initZone each time 
	 * 
	 * @access public
	 * @return void
	 */
	function initCrudZone() { }

	/**
	 * CrudZone index page.
	 *
	 * Provides 'Read' functionality of CRUD. This zone has two functions: If an identifier is
	 * passed to the zone (first and only ZoneParam), display that record. This is the equivalent
	 * of CRUD/record_id/read.
	 *
	 * The second function of the index page is to provide a list view if a record id isn't provided.
	 * This is the equivalent of CRUD/(all)/read.
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
			$template = Config::get('zoop.gui.templates.formz');
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
	}

	function _getRecords() {
		$this->setData( $this->form->getRecords() );
	}

	/**
	 * Creates a form object for a list and sets some sane defaults 
	 * 
	 * @access public
	 * @return void
	 */
	function _listRecords() {
		$link = ($this->form->isSluggable()) ? '%slug%' : '%id%';
		$this->form->setFieldListlink(array($this->form->getIdField(), $this->form->getTitleField()), $link);

		$this->form->addRowAction('edit');
		$this->form->addRowAction('delete');
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

		$this->initUpdateForm();
		$this->form->guiAssign();
		$gui->generate('forms/formz.tpl');
	}

	/**
	 * Hook for editing the edit form before it is assigned  
	 * Form found at $this->form
	 */
	function initUpdateForm() { }
	
	/**
	 * Hook for adding authentication to CRUD.
	 *
	 * By default this method will *always* return true. This is on purpose. Otherwise, CrudZone would
	 * deny access to everything by default, and would be useless...
	 * 
	 * Override this method in an extending class so you can implement authentication. Implementing
	 * subclasses should either redirect to a 'denied' page, or return false if authentication is denied.
	 *
	 * @param string $action
	 *   CRUD action to authenticate. Will be 'create', 'read', 'update', 'destroy' or 'list'.
	 * @return bool Return 'false' from overriding methods to stop the requested action from happening.
	 */
	function checkAuth($action) {
		return true;
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

		if (getPostText('update') || getPostText('update_and_create')) {
			// save the posted values.
			$id = $this->saveUpdatePost();
		} else if (getPostText('destroy')) {
			// redirect to the destroy page if they're trying to delete this item...
			$this->zoneRedirect('destroy');
			return;
		}

		if (getPostText('update_and_create')) {
			BaseRedirect($this->getZoneBasePath() . '/create', HEADER_REDIRECT);
		} else {
			BaseRedirect($this->getZoneBasePath(), HEADER_REDIRECT);
		}
	}
	
	protected function saveUpdatePost() {
		$values = array();
		foreach ($this->form->getFields() as $name => $field) {
			if ((!isset($field['editable']) || $field['editable'])
				&& (!isset($field['formshow']) || $field['formshow'])) {

				$controls = $GLOBALS['controls'];
				if ( (isset($controls['betterpassword']) && isset($controls['betterpassword'][$name]) )  || 
					( isset($controls['password']) && isset($controls['password'][$name]) )) {
					if (!getPostIsset($name)) continue;
				}

				switch($field['type']) {
					case 'boolean':
					case 'bool':
						$values[$name] = getPostCheckbox($name);
						break;
					case 'relation':
						switch ($field['rel_type']) {
							case Formz::MANY:
								$values[$name] = getPost($name);
								if (!is_array($values[$name])) $values[$name] = array();
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
		return $this->form->saveRecord($values);
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

		$record_id = $this->getZoneParam('record_id');
		if ($this->form->isSluggable()) {
			$record_id = $this->form->getRecordBySlug($record_id);
		} else {
			$record_id = $this->form->getRecord($record_id);
		}

		if (isset($params[0]) && !empty($params[0])) {
			$this->form = new Formz($params[0]);
		} else {
			$this->responsePage(404);
			return;
		}

		$immutableFields = $this->form->getImmutableForeignFields($parentTable);

		if ($record_id) {
			$this->form->setParentId($record_id);
		} else {
			die;
			$this->responsePage(404);
			return;
		}

		$this->form->type = 'record';
		$this->form->setEditable(true);

		$this->form->setFieldFormshow($immutableFields, false);

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
		$gui->generate('forms/formz.tpl');
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
			$values['parent_id'] = $record_id;

			$child_form = new Formz($params[0]);

			$fields = $child_form->getFields();
			unset($fields['id']);

			foreach ($fields as $name => $field) {
				if ((!isset($field['editable']) || $field['editable'])
					&& (!isset($field['formshow']) || $field['formshow'])) {
					switch($field['type']) {
						case 'boolean':
						case 'bool':
							$values[$name] = getPostCheckbox($name);
							break;
						case 'relation':
							switch ($field['rel_type']) {
								case Formz::MANY:
									$values[$name] = getPost($name);
									if (!is_array($values[$name])) $values[$name] = array();
									break;
								case Formz::ONE:
									$posted_int = getPostInt($name);
									if (is_integer($posted_int)) {
										$values[$name] = $posted_int;
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
	 * Page handler for CRUD Destroy action.
	 *
	 * Displays 'delete' confirmation page.
	 *
	 * @see postDestroy()
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
		
		$this->form->setFieldFormshow('*', false);
		$this->form->setFieldFormshow($id_field);

		$this->form->addAction('delete');
		$link = ($this->form->isSluggable()) ? '%slug%' : '%id%';
		$this->form->addAction('cancel', array('link' => $link));

		$this->form->guiAssign();
		$gui->assign('message', $message);
		$gui->generate('forms/formz.tpl');
	}

	/**
	 * POST handler for CRUD Destroy action.
	 *
	 * @todo add handling for non-integer id types.
	 *
	 * @see pageDestroy()
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
	 * Return a record id for a given slug
	 *
	 * @access public
	 * @param string $slug
	 * @return int Record id.
	 */
	function getRecordIdBySlug($slug) {
		return $this->form->getRecordIdBySlug($slug);
	}
	
	function recordId() {
		$record_id = $this->getZoneParam('record_id');
		if ($this->form->isSluggable()) {
			$record_id = $this->getRecordIdBySlug($record_id);
		}
		return $record_id;
	}
}
