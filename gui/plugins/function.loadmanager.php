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

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 *
 * @author   Steve Francia
 * @version  1.0
 *
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_loadmanager($params, &$smarty)
{
	global $sGlobals;

	$conf = "default";
	$path = '';

	foreach ($params as $_key=>$_value) {
		switch ($_key) {
			case 'conf':
			case 'value':
			case 'name':
			case 'path':
				$$_key = $_value;
				break;
		}
	}

	$formpart =  "<input type=\"text\" id=\"$name\" class=\"selectFile\" value=\"". htmlspecialchars($value) ."\" name=\"$name\" />\r";
	$formpart .= "<input type=\"button\" name=\"select_$name\" value=\" File \" onclick=\"ImageSelector.select('$name','$conf', '$path');\"/>\r";


	return $formpart;
}
?>
