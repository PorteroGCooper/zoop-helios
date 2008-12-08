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
 * Zoop Smarty plugin
 * @ingroup gui
 * @ingroup plugins
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     guicontrol_label
 * Purpose:  privide a label tag for a guicontrol.
 * -------------------------------------------------------------
 */
function smarty_function_guicontrol_label($params, &$smarty) {
	if(isset($params['guicontrol'])) {
		$control = $params['guicontrol'];
		$name = $control->getDisplayName();
	} else {
		$type = $params['type'];
		$name = $params['name'];
		$control = GuiControl::get($type, $name);
	}

	$for = $control->getId();
	$lname = format_label($name);
	$html = "<label for=\"$for\"> $lname: </label>";
	return $html;
}
