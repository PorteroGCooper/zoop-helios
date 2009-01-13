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
 * betterpassword (confirm, and supports encryption)
 *
 * Confirmation field label can be changed by setting the confirm_label param:
 *
 * @code
 *    $myControl->setParam('confirm_label', 'Confirm your password:');
 * @endcode
 *
 * @uses GuiControl
 * @ingroup gui
 * @ingroup guicontrol
 * @version $id$
 * @copyright 2008 Portero Inc
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class betterpasswordControl extends GuiContainer {
	private $primary;
	private $secondary;

	function initControl() {
		$this->primary = GuiControl::get('password', 'password');
		$this->primary->setParent($this->getName());

		$this->secondary = GuiControl::get('password', 'confirmpassword');
		$this->secondary->setParent($this->getName());
	}

	/**
	 * validate
	 *
	 * @access public
	 * @return void
	 */
	function validate() {
		if ($this->params['password'] != $this->params['confirmpassword']) {
			return array('text' => 'passwords do not match', 'value' => '');
		} else {
			return parent::validate();
		}
	}

	/**
	 * getValue
	 *
	 * @access public
	 * @return void
	 */
	function getValue() {
		if (!empty($this->params['password']) && isset($this->params['encryption'])) {
			$encryptionMethod = $this->params['encryption'];
			if (is_callable($encryptionMethod)) {
				$value = $encryptionMethod($this->params['password']);
			} else {
				$value = $this->params['password'];
			}
		} else {
			$value = $this->params['password'];
		}

		return $value;
	}

	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return void
	 */
	function getPersistentParams() {
		return array('validate', 'encryption');
	}
	
	/**
	 * Override the default 'for' value (used for labels). Return the 'for' value of the first
	 * password field inside this betterpassword guicontrol. This will focus the first field when
	 * the label is clicked.
	 *
	 * @return string
	 */
	function getFor() {
		return $this->primary->getFor();
	}

	/**
	 * Render Better Password GuiControl as an HTML string.
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string Password fields
	 */
	protected function render() {
		$attrs = array();
		// this seems like a bad idea, probably best to put the logic on the recieving end,
		// rather than here.. Don't update if both are '' .
		//$this->param['origPw'] = $this->params['value'];
		$name = $this->getName();
		$this->setValue('', true);

		$this->primary->setParams($this->params);
		$this->primary->setParam('type', 'password');
		$this->primary->setParam('errorState', null);
		$this->primary->setValue('', true);

		$html = $this->primary->renderControl();
		
		if (isset($this->params['confirm_label'])) {
			$confirm_label = $this->params['confirm_label'];
		} else {
			$confirm_label = 'Confirm your password by typing it again:';
		}
		$html .= '<div class="guicontrol-betterpassword-confirm">'. $confirm_label .'</div>';

		$this->secondary->setParams($this->params);
		$this->secondary->setParam('type', 'password');
		$this->secondary->setParam('errorState', null);
		$this->secondary->setValue('', true);

		$html .= $this->secondary->renderControl();

		$this->controls = array(&$pwcontrol, &$pwccontrol);

		return $html;
	}
}
