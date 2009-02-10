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
 * Text input GuiControl
 *
 * @ingroup gui
 * @ingroup GuiControl
 *  
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class TextControl extends GuiControl {
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
	 * @return string HTML text input
	 */
	protected function render($foo = false) {
		$type = 'type="' . $this->getType() . '"';
		
		$name_id = $this->getNameIdString();
		$class = 'class="' . $this->getClass() . '"';
		$attrs = $this->renderHTMLAttrs();
		$value = 'value="' . $this->getValue() . '"';
			
		$html = "<input $type $name_id $class $attrs $value />";
		return $html;
	}
}
