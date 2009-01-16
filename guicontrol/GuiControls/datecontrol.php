<?php

// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

include_once(dirname(__file__) . "/text.php");

/**
 * DateControl
 *
 * @ingroup gui
 * @ingroup GuiControl
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Andy Nu <nuandy@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class DateControlControl extends TextControl {

	function initControl() {
		global $gui;
		$gui->add_js('/zoopfile/gui/js/jquery.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/jquery.date_input.js', 'zoop');
		$gui->add_css('/zoopfile/gui/css/date_input.css', 'zoop');
		
		$this->setParam('size', 20);
		$this->setParam('maxlength', 20);
	}
	
	function validate() {
		if(isset($this->params['validate'])) {
			$date = Validator::validateDate($this->getValue(), $this->params['validate']);
			if($date['result'] !== true) {
				// @todo this shouldn't echo anything...
				echo_r($date['message']);
			}
		}
		return true;
	}
	
	/**
	 * Render GuiControl
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string Date selector
	 */
	 
	protected function render() {
		global $gui;
		
		$html = parent::render();
		
		$input_id = $this->getId();
		$gui->add_jquery('$("#'.$input_id.'").date_input({
			stringToDate: function(string) {
				var matches;
				if (matches = string.match(/^([1-2]{1}\d{3,3})-(\d{2,2})-(\d{2,2}) (\d{2,2}):(\d{2,2}):(\d{2,2})$/)) {
					return new Date(matches[1], matches[2] - 1, matches[3], matches[4], matches[5], matches[6]);
				} else {
					return null;
				};
			},
			dateToString: function(date) {
				var month = (date.getMonth() + 1).toString();
				var dom = date.getDate().toString();
				var hour = date.getHours().toString();
				var minute = date.getMinutes().toString();
				var second = date.getSeconds().toString();
				if (month.length == 1) month = "0" + month;
				if (dom.length == 1) dom = "0" + dom;
				if (hour.length == 1) hour = "0" + hour;
				if (minute.length == 1) minute = "0" + minute;
				if (second.length == 1) second = "0" + second;
				return date.getFullYear() + "-" + month + "-" + dom + " " + hour + ":" +  minute + ":" + second;
			}
		});');
		
		return $html;
	}
	
}