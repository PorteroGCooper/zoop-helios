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
 * Confirmation field GuiControl
 *
 * @ingroup gui
 * @ingroup guicontrol
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class ConfirmControl extends GuiContainer {

	/**
	 * validate
	 *
	 * @access public
	 * @return void
	 */
	function validate() {
		if ($this->params['primary'] != $this->params['secondary']) {
			return array('text' => $this->getLabel() . ' values do not match.', 'value' => '');
		} else {
			return parent::validate();
		}
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
	 * Render confirmation field GuiControl as an HTML string.
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string Confirmation field
	 */
	protected function render() {
		$attrs = array();
		$name = $this->getName();

		if (isset($this->params['type']) && $this->params['type'] != 'confirm') {
			$type = $this->params['type'];
		} else {
			$type = 'text';
		}
		
		$primary = GuiControl::get($type, 'primary')
			->setParams($this->params)
			->setParam('errorState', null)
			->setParent($name);
		$html = $primary->renderControl();
		
		if (isset($this->params['confirm_label'])) {
			$confirm_label = $this->params['confirm_label'];
		} else {
			$confirm_label = 'Confirm your '. strtolower($this->getLabel()) .' by typing it again:';
		}
		$html .= '<div class="confirm-control-label">'. $confirm_label .'</div>';

		$secondary = GuiControl::get($type, 'secondary')
			->setParams($this->params)
			->setParam('errorState', null)
			->setParent($name);
		$html .= $secondary->renderControl();

		$this->controls = array(&$primary, &$secondary);

		return $html;
	}
}
