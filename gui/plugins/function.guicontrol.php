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

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     guicontrol
 * Purpose:  instantiate a webcontrol and call render() on it.
 * -------------------------------------------------------------
 */
function smarty_function_guicontrol($params, &$smarty)
{
	if(isset($params['from']))
	{
		$control = $params['from'];
	}
	else
	{
		$type = $params['type'];
		$name = $params['name'];
		$control = &getGuiControl($type, $name);
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
			$specialParams[$keys[0]][$keys[1]] = $value;
		}

	}
	if(isset($specialParams))
	{
		$control->setParams($specialParams);
	}

	return $control->render($echo);
}

/* vim: set expandtab: */

?>
