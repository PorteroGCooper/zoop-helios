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

/**
 *
 * @author   Steve Francia
 * @version  1.0
 *
 * @param array
 * @param Smarty
 * @return string
 */

/**
 * smarty_function_formz
 *
 * @param mixed $params
 * @param mixed $smarty
 * @access public
 * @return void
 */
function smarty_function_formz($params, &$smarty) {
	$form = $params['form'];

	if ($form instanceof Formz) {
		switch ($form->type) {
			case 'record':
				include_once(dirname(__file__) . '/function.formz_form.php');
				return smarty_function_formz_form($params, $smarty);
				break;
			default:
				include_once(dirname(__file__) . '/function.formz_list.php');
				return smarty_function_formz_list($params, $smarty);
				break;
		}
	}
}
