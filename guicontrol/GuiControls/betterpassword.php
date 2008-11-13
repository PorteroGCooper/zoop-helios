<?php
/**
* Zoop Guicontrol
* @package gui
* @subpackage guicontrol
*
*/
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
 * @uses GuiControl
 * @package
 * @version $id$
 * @copyright 2008 Portero Inc
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class betterpasswordControl extends GuiControl
{
	/**
	 * validate
	 *
	 * @access public
	 * @return void
	 */
	function validate() {
		if ($this->params['password'] == $this->params['confirmpassword']) {
			return true;
		} else {
			return array('text' => 'passwords do not match', 'value' => '');
		}
	}

	/**
	 * getValue
	 *
	 * @access public
	 * @return void
	 */
	function getValue() {
		if (isset($this->params['encryption'])) {
			$encryptionMethod = $this->params['encryption'];
			if (is_callable($encryptionMethod)) {
				$value = $encryptionMethod($this->params['password']);
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
	 * render
	 *
	 * @access public
	 * @return void
	 */
	function render() {
		$attrs = array();
		//$value = $this->getValue();
		$name = $this->getName();

		$pwcontrol = &getGuiControl('password', 'password');
		$pwcontrol->setParams($this->params);
		$pwcontrol->setParam('type', 'password');
		$pwcontrol->setParent($name);
		$html = $pwcontrol->renderControl();

		$pwccontrol = &getGuiControl('password', 'confirmpassword');
		$pwccontrol->setParams($this->params);
		$pwccontrol->setParam('type', 'password');
		$pwccontrol->setParent($name);
		$html .= $pwccontrol->renderControl();

		$this->controls = array(&$pwcontrol, &$pwccontrol);

		return $html;
	}
}
