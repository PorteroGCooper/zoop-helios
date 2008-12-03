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

include_once(dirname(__file__) . "/select.php");

/**
 * A dependent dropdown guiControl.
 *
 * This guiControl fires an AJAX request when the value changes, updating another dropdown
 * with the json array returned by the specified url.
 *
 * Initialize this guicontrol with, at a minimum, values for the first select, and a callback url
 * that will generate the content for the second select. (params 'index' and 'url', respectively)
 *
 * The callback url should accept a single GET parameter, 'q', and return a json encoded array in
 * the form:
 *
 * @code
 *    [{oV: '1', oT: 'Title 1'}, {oV: '2', oT: 'Title 2'}]
 * @endcode
 *
 * See the code block below for an example page function.
 *
 * If desired, multiple select_update guiControls can be chained by passing an update_id for the
 * select in which to place the result of the callback url. If no update_id is specified, a
 * second select will be automagically generated.
 *
 * When chaining several select_update guiControls together, keep in mind that the last one
 * will have to be a regular 'select', since it won't have anything to update.
 *
 * @code
 *   pageFoo() {
 *       $first_select = getGuiControl('select_update', 'first');
 *       $second_select = getGuiControl('select_update', 'second');
 *       $third_select = getGuiControl('select', 'third');
 *       
 *       $first_select->setParams(array(
 *           'index' => $first_select_values,
 *           'url' => 'bar',
 *           'update_id' => $second_select->getId()
 *       ));
 *       $second_select->setParams(array(
 *           'url' => 'baz',
 *           'update_id' => $third_select->getId()
 *       ));
 *       
 *       global $gui;
 *       $gui->assign('first', $first_select);
 *       $gui->assign('second', $second_select);
 *       $gui->assign('third', $third_select);
 *       
 *       $gui->generate('selects.tpl');
 *   }
 *   
 *   function pageBar() {
 *       $id = getGetString('q');
 *       
 *       // do some magick with the id (a db lookup or something)
 *       $result = array(
 *           array('oV' => '1', 'oT' => 'stuff'),
 *           array('oV' => '2', 'oT' => 'junk'),
 *       );
 *       echo json_encode($results);
 *       exit();
 *   }
 *   
 *   function pageBaz() {
 *       $id = getGetString('q');
 *       
 *       // do some magick with the id (a db lookup or something)
 *       $result = array(
 *           array('oV' => 'a', 'oT' => 'more'),
 *           array('oV' => 'b', 'oT' => 'junk'),
 *       );
 *       echo json_encode($results);
 *       exit();
 *   }
 * @endcode
 *
 * In the above example, the 'selects.tpl' template looks something like this:
 *
 * @code
 *    <div>
 *        {guicontrol_label guicontrol=$first}
 *        {guicontrol guicontrol=$first}
 *    </div>
 *    <div>
 *        {guicontrol_label guicontrol=$second}
 *        {guicontrol guicontrol=$second}
 *    </div>
 *    <div>
 *        {guicontrol_label guicontrol=$third}
 *        {guicontrol guicontrol=$third}
 *    </div>
 * @endcode
 *
 * @todo Write non-js fallback for this guicontrol. It will prob'ly have to be
 * implemented in the guiControl validation: i.e. if there is no POST value set for
 * #update_id, the dependent dropdown wasn't selected and/or updated. In this case,
 * the second dropdown should be populated and sent back to the user to fill out.
 * (validation failed)
 *
 * @todo Make this actually use validation. This'll be fun since the persistent values
 * don't currently line up with the values in the updated select.
 *
 * @todo If something is already selected for the primary dropdown, fill the values
 * in the secondary dropdown. If the secondary dropdown also has a selected value,
 * hook that up as well.
 *
 * @ingroup gui
 * @ingroup GuiControl
 * @ingroup jquery
 *
 * @author Justin Hileman {@link http://justinhileman.com}
 */
class Select_updateControl extends SelectControl {
	
	function initControl() {
		global $gui;
		$gui->add_js('/zoopfile/gui/js/jquery.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/jquery.selectCombo.js', 'zoop');
	}
	
/*
	function validate() {
		// die_r($this->getValue());
		// die_r($GLOBALS);
	}
*/

	/**
	 * Render GuiControl
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string CAPTCHA GuiControl
	 */
	protected function render() {
		global $gui;
		
		$label = $this->getLabelName();
		if (isset($this->params['update_id'])) {
			$render_update_select = false;
			$update_id = $this->params['update_id'];
		} else {
			$render_update_select = true;
			$update_id = $this->name . '_update';
		}
		if (isset($this->params['update_label'])) {
			$update_control_label = $this->params['update_label'];
		} else {
			$update_control_label = null;
		}
		
		$url = $this->params['url'];
		$select_id = $this->getId();

		// add a null value to the top.
		if (isset($this->params['index'])) {
			$label = Config::get('zoop.gui.select_null_value');
			$label = str_replace('%field%', format_label($this->getDisplayName()), $label);
			$this->params['index'] = array('' => $label) + $this->params['index'];
		}
		
		$html = parent::render();
		
		// TODO if something is already selected, fill the initial values of the second dropdown.
		
		// add the dependent dropdown...
		if ($render_update_select) {
			$dependent = &getGuiControl('select', $update_id);
			$update_id = $dependent->getId();
			$dependent->setParams(array('index' => array()));
			if ($update_control_label) $html .= '<label for="' . $update_id . '">' . $update_control_label . '</label>';
			$html .= $dependent->renderControl();
		}

		$gui->add_js('jQuery(function($){$("#' . $select_id . '").selectCombo("' . $url . '", "#' . $update_id . '");});', 'inline');

		return $html;
	}
}