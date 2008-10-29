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
class crudZone extends zone {

	// this should be in an init function or something :-/
	var $zoneParamNames = array('record_id');

	function crudZone() {
		die('foo');
/* 		$this->setZoneParams(array('record_id')); */
		$this->addAlias('create', 'new/update');
	}

	function pageIndex() {
		echo "Record: " . $this->getZoneParam('record_id') . "<br />";
		die('GET index (read)');
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
	function pageCreate() {
		echo "Record: " . $this->getZoneParam('record_id') . "<br />";
		die('GET create');
	}

	/**
	 * POST handler for CRUD Create action.
	 *
	 * @see pageCreate()
	 * @access public
	 * @return void
	 **/
	function postCreate() {
		echo "Record: " . $this->getZoneParam('record_id') . "<br />";
		die('POST create');
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
/* 		die('GET read'); */

		// 'read' is the same as 'default'...
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
		echo "Record: " . $this->getZoneParam('record_id') . "<br />";
		die('GET update');
	}

	/**
	 * POST handler for CRUD Update action.
	 *
	 * @see pageUpdate()
	 * @access public
	 * @return void
	 **/
	function postUpdate() {
		echo "Record: " . $this->getZoneParam('record_id') . "<br />";
		die('POST update');
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
	function pageDelete() {
		echo "Record: " . $this->getZoneParam('record_id') . "<br />";
		die('GET delete');
	}

	/**
	 * POST handler for CRUD Delete action.
	 *
	 * @see pageDelete()
	 * @access public
	 * @return void
	 **/
	function postDelete() {
		echo "Record: " . $this->getZoneParam('record_id') . "<br />";
		die('POST delete');
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
		echo "Record: " . $this->getZoneParam('record_id') . "<br />";
		die('GET destroy');	
	}

	/**
	 * POST handler for CRUD Destroy action.
	 *
	 * @see pageDestroy()
	 * @access public
	 * @return void
	 **/
	function postDestroy() {
		echo "Record: " . $this->getZoneParam('record_id') . "<br />";
		die('POST destroy');
	}
	
}
