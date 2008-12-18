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

include_once(dirname(__file__) . "/hidden.php");

/**
 * Form Token GuiControl
 *
 * @ingroup gui
 * @ingroup guicontrol
 *
 * @version $id$
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class FormTokenControl extends hiddenControl {

	/**
	 * validate
	 *
	 * @access public
	 * @return void
	 */
	function validate() {
		return parent::validate();
		
/*
		die_r($GLOBALS['controls']);
	
		if (!$this->_checkGuid()) {
			return array('text' => 'There was an error with your form submission. Please resubmit.', 'value' => '');
		} else {
			// set a new token, even if the old one was right. Every render of this page should set a Guid.
			$this->setValue($this->_registerGuid());
			return parent::validate();
		}
*/
	}

	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return void
	 */
	function getPersistentParams() {
		return array('validate', 'guid');
	}

	/**
	 * render
	 *
	 * @access public
	 * @return void
	 */
	function render() {
		$this->setParam('type', 'hidden');
		$this->setValue($this->_registerGuid());
		
		return parent::render();
	}
	
	private function _registerGuid() {
		$guid = com_create_guid();
		
		// register this as a valid guid.
		$guids = $this->getParam('guid');
		if (empty($guids)) $guids = array();
		
		$guids[$guid] = strtotime(Config::get('zoop.app.security.token_expiration'));
		$this->setParam('guid', $guids);
		
		return $guid;
	}
	
	private function _checkGuid() {
		$guid = $this->getValue();

die_r($this->params);

		if (isset($this->params['guid'][$guid]) && $this->params['guid'][$guid] > time()) {
			die_r(date('c', $this->params['guid'][$guid]));
			// use up this guid.
			unset($this->params['guid'][$guid]);
			return true;
		} else {
			return false;
		}
	}
}