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
 * @package gui
 * @subpackage plugins
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     guiwidget
 * Purpose:  instantiate a webcontrol and call render() on it.
 * -------------------------------------------------------------
 */
function smarty_function_guiwidget($params, &$smarty) {

	if (isset($params['guiwidget'])) {
		$control = $params['guiwidget'];
	} else {
		$type = $params['type'];
		$name = (isset($params['name'])) ? $params['name'] : null;
		$control = GuiWidget::get($type, $name);
	}

	if (isset($params['echo']))
		$echo = $params['echo'];
	else
		$echo = true;

	foreach($params as $key => $value)
	{
		if($key[0] != '_')
		{
			$control->setParam($key, $value);
		}
		else
		{
			$keys = explode('_', $key);
			array_shift($keys);
			$tmpkeys = $keys;
			$tmpval  = $value;
			while (!empty($tmpkeys))
			{
				$tmpval = array(array_pop($tmpkeys) => $tmpval);
			}

			if (isset($specialParams))
				$specialParams = array_merge_recursive($specialParams, $tmpval);
			else
				$specialParams = $tmpval;
		}
	}
	if(isset($specialParams))
	{
		$control->setParams($specialParams);
	}

	return $control->renderWidget($echo);
}

/* vim: set expandtab: */

?>
