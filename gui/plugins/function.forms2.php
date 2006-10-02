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

	if (isset($form->table->pages))
	{
		include_once(dirname(__file__) . "/function.forms_list.php");
		echo smarty_function_forms_list(array("form" => $form->form, "table" => $form->tablename), $smarty);
	}
	else
	{
		include_once(dirname(__file__) . "/function.forms_form.php");
		if (isset($params['type']) && $params['type'] == "view")
			echo smarty_function_forms_form(array("form" => $form->record, "form_type" => "view"), $smarty);
		else
			echo smarty_function_forms_form(array("form" => $form->record), $smarty);

	}

}


/* vim: set expandtab: */
?>
