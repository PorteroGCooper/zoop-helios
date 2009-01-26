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
 * Textarea GuiControl
 *
 * Textarea GuiControls can be given a 'grow' param, which will cause the GuiControl to
 * automatically grow to fit contents.
 *
 * @code
 *    $myControl->setParam('grow', true);
 * @endcode
 *
 * @ingroup gui
 * @ingroup GuiControl
 */
class TextareaControl extends GuiControl {
	function getPersistentParams() {
		return array('validate');
	}

	/**
	 * Render GuiControl
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return string HTML textarea
	 */
	protected function render() {
		global $gui;
		
		$attrs = array();
		
		$name_id = $this->getNameIdString();
		$class = 'class="' . $this->getClass() . '"';
		$attrs = $this->renderHTMLAttrs();
		
		$html = "<textarea $name_id $class $attrs>";
		$html .= htmlentities($this->getValue());
		$html .='</textarea>';
		
		if (isset($this->params['grow']) && $this->params['grow']) {
			$gui->add_jquery();
			$gui->add_js('/zoopfile/gui/js/jquery.jgrow.js');
			$gui->add_jquery('$("#' . $this->getId() . '").jGrow();');
		}

		return $html;
	}
	
	/**
	 * Override default getHTMLAttrs method.
	 *
	 * Remove style attr--it'll get reset here, unless it was a width/height value
	 * those need to be removed since they're used here as aliases for
	 * rows and columns.
	 *	
	 * @access public
	 * @return string
	 */
	function getHTMLAttrs() {
		$attrs = parent::getHTMLAttrs();
		
		if (isset($attrs['style'])) unset($attrs['style']);
		
		foreach ($this->params as $param => $value) {
			switch ($param) {
				case 'style':
				case 'wrap':
					if (!empty($value)) $attrs[$parameter] = "$parameter=\"$value\"";
					break;
				case 'width': // alias for cols
				case 'cols':
					if (!empty($value)) $attrs['cols'] = 'cols="' . $value . '"';
					break;
				case 'height': // alias for rows
				case 'rows':
					if (!empty($value)) $attrs['rows'] = 'rows="' . $value . '"';
					break;
			}
		}
		
		return $attrs;
	}
}