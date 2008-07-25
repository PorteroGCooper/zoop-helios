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
 * smarty_function_forms2
 *
 * @param mixed $params
 * @param mixed $smarty
 * @access public
 * @return void
 */
function smarty_function_forms2($params, &$smarty)
{
	$form = $params['form'];
	if ($form->type == 'list' || isset($form->table->pages))
	{
		include_once(dirname(__file__) . "/function.forms_list.php");
		$params['form'] = $form->form;
		$params['table'] = $form->tablename;
		echo smarty_function_forms_list($params, $smarty);
	}
	else
	{
		include_once(dirname(__file__) . "/function.forms_form.php");
		if (isset($params['type']) && $params['type'] == "view")
		{
			$params['form'] = $form->record;
			$params['type'] = $view;
			echo smarty_function_forms_form($params, $smarty);
		}
		else
		{
			$params['form'] = $form->record;
			echo smarty_function_forms_form($params, $smarty);
		}

	}

}


/* vim: set expandtab: */
?>
