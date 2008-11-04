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

	// this should be in an init function or something :-/
	var $zoneParamNames = array('record_id');
	var $wildcards = '';

	var $Aliases = array(
		'create' => 'new/update',
		'%id%/edit' => '%id%/update',
		'%id%/delete' => '%id%/destroy',
	);


	function __construct() {
		if (isset($this->Aliases) && count($this->Aliases)) {
			$this->addAliases($this->Aliases);
			$this->Aliases = array();
		}
		$this->form = new Formz($this->tableName);
	}


	function pageIndex() {
		global $gui;
		
		$record_id = $this->getZoneParam('record_id');
		if ($record_id != '' && !is_numeric($record_id)) {
			$this->responsePage404();
			return;
		}
		if ($record_id == 'new') {
			$this->form->getRecord();
		} else if ($record_id) {
			$this->form->getRecord($record_id);
		}
		else {
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
		}
		
		$this->form->setEditable(false);
		
		$this->form->guiAssign();
		$gui->display('forms/formz.tpl');
	}
	
	/**
	 * Page handler for CRUD Create action.
	 *
	 * Displays an empty edit form.
	 *
	 * @see postCreate()
	 * @access public
	 * @return void
	 **/		
/*
	function pageCreate() {
		echo "Record: " . $this->getZoneParam('record_id') . "<br />";
		die('GET create');
	}
*/

	/**
	 * POST handler for CRUD Create action.
	 *
	 * @see pageCreate()
	 * @access public
	 * @return void
	 **/
/*
	function postCreate() {
		echo "Record: " . $this->getZoneParam('record_id') . "<br />";
		die('POST create');
	}
*/
	
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
		$gui->display('forms/formz.tpl');
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
	
		BaseRedirect( $this->makeBasePath(), HEADER_REDIRECT );
	}
	
	/**
	 * Page handler for CRUD Delete action.
	 *
	 * Displays a 'delete' confirmation page.
	 *
	 * @see postDelete()
	 * @access public
	 * @return void
	 **/		
/*
	function pageDelete() {

	}
*/

	/**
	 * POST handler for CRUD Delete action.
	 *
	 * @see pageDelete()
	 * @access public
	 * @return void
	 **/
/*
	function postDelete() {

	}
*/
	
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
		$gui->display('forms/formz.tpl');
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
