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

		$pwcontrol = GuiControl::get('password', 'password');
		$pwcontrol->setParams($this->params);
		$pwcontrol->setValue('', true);
		$pwcontrol->setParam('type', 'password');
		$pwcontrol->setParam('errorState', null);
		$pwcontrol->setParent($name);
		$html = $pwcontrol->renderControl();
		
		if (isset($this->params['confirm_label'])) {
			$confirm_label = $this->params['confirm_label'];
		} else {
			$confirm_label = 'Confirm your password by typing it again:';
		}
		$html .= '<div class="guicontrol-betterpassword-confirm">'. $confirm_label .'</div>';

		$pwccontrol = GuiControl::get('password', 'confirmpassword');
		$pwccontrol->setParams($this->params);
		$pwccontrol->setParam('type', 'password');
		$pwccontrol->setParam('errorState', null);
		$pwccontrol->setParent($name);
		$pwccontrol->setValue('', true);
		$html .= $pwccontrol->renderControl();

		$this->controls = array(&$pwcontrol, &$pwccontrol);

		return $html;
	}
}
