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
 * Confirmation field label can be changed by setting the confirm_label param:
 *
 * @code
 *    $myControl->setParam('confirm_label', 'Type it again, fool:');
 * @endcode
 *
 * JavaScript hide/show can be suppressed by setting the 'shiny' param to false:
 *
 * @code
 *    $myControl->setParam('shiny', false);
 * @endcode
 *
 * @ingroup gui
 * @ingroup guicontrol
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class ConfirmControl extends GuiContainer {

	function initControl() {
		global $gui;
		
		$gui->add_jquery();
	}

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
		global $gui;
		
		$attrs = array();
		$name = $this->getName();
		
		$confirm_wrapper_id = $this->getId() .'-confirm';

		if (isset($this->params['type']) && $this->params['type'] != 'confirm') {
			$type = $this->params['type'];
		} else {
			$type = 'text';
		}
		
		if (isset($this->params['shiny']) && !$this->params['shiny']) {
			$use_js = false;
		} else {
			$use_js = true;
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
		$html .= '<div id="'. $confirm_wrapper_id .'">';
		$html .= '<div class="confirm-control-label">'. $confirm_label .'</div>';

		$secondary = GuiControl::get($type, 'secondary')
			->setParams($this->params)
			->setParam('errorState', null)
			->setParent($name);
		$html .= $secondary->renderControl();
		
		$html .= '</div>';

		if ($use_js) {
			$gui->add_jquery('
				$("#'.$confirm_wrapper_id.'").hide();
				$("#'.$primary->getId().'").keydown(function(){
					$("#'.$confirm_wrapper_id.'").show("fast");
					$("#'.$secondary->getId().'").val("");
				});
			');
		}
		
		$this->controls = array(&$primary, &$secondary);

		return $html;
	}
}
