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

include_once(ZOOP_DIR . "/gui/plugins/function.html_options.php");
include_once(dirname(__file__) . "/select.php");

/**
 * jQuery Shuttle GuiControl
 *
 * The shuttle control overrides the default 'null_value' setting of a regular select, since
 * it makes no sense to show <none> as a selectable value in a shuttle control.
 * 
 * @ingroup gui
 * @ingroup GuiControl
 * 
 * @extends SelectControl
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Andy Nu <nuandy@gmail.com>
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class ShuttleControl extends SelectControl {
	function initControl() {
		global $gui;

		// all shuttle controls are, by default, multiple selects...
		$this->setParam('multiple', true);
		$this->setParam('null_label', false);

		$gui->add_jquery();
		$gui->add_js('/zoopfile/gui/js/jquery.comboselect.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/jquery.selso.js', 'zoop');
		$gui->add_css('/zoopfile/gui/css/shuttle.css', 'zoop');
		
		$gui->add_jquery('$("#' . $this->getId() . '").comboselect({sort:"both",addbtn:"Add >>",rembtn:"<< Remove"});');
	}
}