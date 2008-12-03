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
 * Email GuiControl
 *
 * @ingroup gui
 * @ingroup GuiControl
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class EmailControl extends GuiControl {

	/**
	 * Validate
	 *
	 * @todo Ummm... Should this actually do some validation?
	 *
	 * @access public
	 * @return bool
	 */
	function validate() {
		return true;
	}

	/**
	 * getValue
	 *
	 * @access public
	 * @return void
	 */
	function getValue() {
		if (isset($this->params['email'])) {
			$email = trim($this->params['email']) . "@" . $this->params['domain'];
		} else {
			$email = "";
		}
		return $email;
	}

	/**
	 * getLabelName
	 *
	 * @access public
	 * @return void
	 */
	function getLabelName() {
		$label = $this->getName() . "[controls][text][email][text]";
		return $label;
	}

	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return void
	 */
	function getPersistentParams() {
		return array('validate');
	}

	/**
	 * Render GuiControl
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string Email input field
	 */
	protected function render() {
		$attrs = array();
		$value = $this->getValue();
		$name = $this->getName();


		$newvalue = explode('@', $value);
		if (isset($newvalue[1])) {
			$emailvalue = htmlspecialchars($newvalue[0]);
			$domainvalue = htmlspecialchars($newvalue[1]);
		} else {
			$emailvalue = "";
			$domainvalue = "";
		}

		$usercontrol = &getGuiControl('text', 'email');
		$usercontrol->setParam('text', $emailvalue);
		$usercontrol->setParent($name);
		$usercontrol->setParams($this->params);
		$html = $usercontrol->renderControl();

		$html .= " @ ";

		$domaincontrol = &getGuiControl('select', 'domain');
		$domaincontrol->setParam('value', $domainvalue);
		$domaincontrol->setParams($this->params);
		$domaincontrol->setParent($name);
		$html .= $domaincontrol->renderControl();

		$this->controls = array(&$usercontrol, &$domaincontrol);

		return $html;
	}

}
