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
	}

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
	 * @todo: remove the " if $record_id = 'new'" chunk...
	 */
	function pageIndex() {
		global $gui;
		
		$record_id = $this->getZoneParam('record_id');
		if ($record_id != '' && !is_numeric($record_id)) {
			$this->responsePage404();
			return;
		}

		if ($record_id) {
			$this->_detailRecord($record_id);
		} else {
			$this->_listRecords();
		}

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
	 * @param mixed $record_id 
	 * @access public
	 * @return void
	 */
	function _detailRecord($record_id) {
		if ($record_id == 'new') {
			$this->form->getRecord();
		} else if ($record_id) {
			$this->form->getRecord($record_id);
		}
		$this->_loadAndGenerateForm();
	}

	/**
	 * Creates a form object for a list and sets some sane defaults 
	 * 
	 * @access public
	 * @return void
	 */
	function _listRecords() {
		$this->form->getRecords();
		$this->form->setFieldListlink('id', '%id%/read');
		
		// add a fake column called "edit", give it an edit link...
		$this->form->setFieldFromArray('edit', array(
			// the %id% will automatically be replaced by the contents of the record id field
			'listlink' => '%id%/update',
			'display' => array('label' => '', 'override' => 'edit')
		));
		// add a fake column called "delete", give it a destroy link...
		$this->form->setFieldFromArray('delete', array(
			// the %id% will automatically be replaced by the contents of the record id field
			'listlink' => '%id%/destroy',
			'display' => array('label' => '', 'override' => 'delete')
		));
		$this->_loadAndGenerateForm();
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
		global $gui;
		
		$record_id = $this->getZoneParam('record_id');
		if ($record_id) {
			$this->form->getRecord($record_id);
		} else {
			trigger_error('CRUD record id not found.');
		}
		
		$this->form->setEditable(true);
		
		// add a fake column called "delete", give it a destroy link...
		$this->form->addAction('save');
/* 		$this->form->addAction('preview'); */
		$this->form->addAction('delete');
/* 		$this->form->addAction('cancel'); */
		
		$this->form->guiAssign();
		$gui->generate('forms/formz.tpl');
	}

	/**
	 * POST handler for CRUD Update action.
	 *
	 * @see pageUpdate()
	 * @access public
	 * @return void
	 **/
	function postUpdate() {
		$post = getPost();
		if (isset($post['update'])) {
			$id = $this->form->saveRecord(getPost());
		} else if (isset($post['destroy'])) {
			$id = $post['id'];
			$this->form->destroyRecord($id);
		}
	
		BaseRedirect( substr($this->getZonePath(), 0, strrpos($this->getZonePath(), '/') ), HEADER_REDIRECT );
		//BaseRedirect( $this->makeBasePath(), HEADER_REDIRECT );
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
		global $gui;
		
		$record_id = $this->getZoneParam('record_id');
		$this->form->getRecord($record_id);
		
		$message = 'Are you sure you want to delete record: ' . $record_id . '?';
		
		$id_field = $this->form->getIdField();
		foreach ($this->form->getFields() as $fieldname => $field) {
			if ($fieldname != $id_field) {
				$this->form->setFieldFormshow($fieldname, false);
			}
		}
		$this->form->addAction('delete');
		
		$this->form->guiAssign();
		$gui->assign('message', $message);
		$gui->generate('forms/formz.tpl');
	}

	/**
	 * POST handler for CRUD Destroy action.
	 *
	 * @see pageDestroy()
	 * @access public
	 * @return void
	 **/
	function postDestroy() {
		$post = getPost();
		
		if (isset($post['destroy'])) {
			$id = $post['id'];
			$this->form->destroyRecord($id);
		}
		$this->zoneRedirect('');
	}
	
}
