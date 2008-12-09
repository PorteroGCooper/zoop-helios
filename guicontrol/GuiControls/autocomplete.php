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

include_once(dirname(__file__) . "/text.php");

/**
 * A jQuery Autocomplete guiControl.
 *
 * @ingroup gui
 * @ingroup GuiControl
 * @ingroup jquery
 *
 * @author Justin Hileman {@link http://justinhileman.com}
 */
class AutocompleteControl extends TextControl {
	
	function initControl() {
		global $gui;
		$gui->add_js('/zoopfile/gui/js/jquery.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/ui.core.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/ui.autocomplete.js', 'zoop');
		$gui->add_css('/zoopfile/gui/css/autocomplete.css', 'zoop');
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
	 * @return string HTML autocomplete input GuiControl
	 */
	protected function render() {
		global $gui;
		$autocomplete_id = $this->getId();
		
		if (!isset($this->params['url']) && !isset($this->params['index'])) {
			trigger_error('An index or callback url must be provided for autocomplete GuiControls.');
			return;
		}
		
		// give it a full url
		if (isset($this->params['url'])) {
			$this->params['url'] = url($this->params['url'], true);
		}
		
		if (isset($this->params['index'])) {
			$this->params['data'] = $this->params['index'];
			unset($this->params['index']);
		}
		
		$options = array();
		
		// Default value for 'result' option. Will be overridden if 'result' parameter is passed.
		$options['result'] = 'function(event, data, formatted){if(data) $("#'. $autocomplete_id .'_hidden").val(data[1]||data[0]);}';
		foreach ($this->params as $param => $val) {
			switch ($param) {
				// boolean
				case 'matchSubset':
				case 'matchCase':
				case 'matchContains':
				case 'mustMatch':
				case 'selectFirst':
				case 'multiple':
				case 'selectFirst':
				case 'autoFill':
				case 'highlight':
				case 'scroll':
					$options[$param] = ($this->params[$param]) ? 'true' : 'false';
					unset($this->params[$param]);
					break;
				// json data
				case 'data':
					$options[$param] = json_encode($this->params[$param]);
					unset($this->params[$param]);
					break;
				// strings
				case 'url':
				case 'multipleSeparator':
					$options[$param] = '"'. $this->params[$param] .'"';
					unset($this->params[$param]);
					break;
				// numbers, functions, variables
				case 'minChars':
				case 'delay':
				case 'cacheLength':
				case 'result':
				case 'formatMatch':
				case 'formatResult':
				case 'width':
				case 'max':
				case 'scrollHeight':
					$options[$param] = $this->params[$param];
					unset($this->params[$param]);
					break;
				// function or data...
				case 'extraParams':
					if (is_array($val)) {
						$new_val = array();
						foreach ($val as $i => $j) {
							$new_val[] = $i .':'. $j;
						}
						$options[$param] = '{'. implode(',', $new_val) .'}';
					} else {
						$options[$param] = $this->params[$param];
					}
					unset($this->params[$param]);
					break;
				// function or boolean
				case 'highlight':
					if (is_string($val)) {
						$options[$param] = $this->params[$param];
					} else {
						$options[$param] = ($this->params[$param]) ? 'true' : 'false';
					}
					unset($this->params[$param]);
					break;
			}
		}
		
		// create the autocomplete option string
		$tmp = array();
		foreach ($options as $opt => $val) {
			$tmp[] = $opt .':'. $val;
		}
		$options = implode(',', $tmp);
		if (!empty($options)) $options = '{' . $options . '}';
		
		// render the form item
		$html = parent::render();
		
		// add a hidden field and swap names (so that updating the text field can update the hidden field...)
		// initialize the autocomplete.
		// add a 'result' handler to update the hidden input when this badboy is selected.
		$gui->add_jquery('$("#'. $autocomplete_id .'").each(function(){field = $(this); field.after("<input type=hidden id=\'" + field.attr("id") + "_hidden\' name=\'" + field.attr("name") + "\'/>").attr("name", field.attr("name") + "_text");}).autocomplete('.$options.');');
			
		return $html;
	}
}