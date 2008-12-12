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
 * Smarty plugin to generate app/page title.
 *
 * Page title format is defined by 'zoop.gui.page_title.format' config variable.
 * instances of %title%, %page_title%, and %app_title% in the format string
 * will be automatically replaced first with Smarty variables, then with Config
 * defaults.
 *
 * @ingroup gui
 * @ingroup plugins
 * @author Justin Hileman {@link http://justinhileman.com}
 *
 * @param array $params This plugin takes no params...
 * @param Smarty $smarty
 * @access public
 * @return string A page title.
 */
function smarty_function_page_title($params, &$smarty) {

	if (isset($smarty->_tpl_vars['title'])) {
		$page_title = $smarty->_tpl_vars['title'];
	} else {
		$page_title = Config::get('zoop.app.title');
	}

	if (isset($smarty->_tpl_vars['app_title'])) {
		$app_title = $smarty->_tpl_vars['app_title'];
	} else {
		$app_title = Config::get('zoop.app.title');
	}
	
	// don't replace if they're the same.
	if ($page_title == $app_title) $app_title = '';

	return str_replace(
		array(
			'%title%',
			'%page_title%',
			'%app_title%'
		),
		array(
			$page_title,
			$page_title,
			$app_title
		),
		Config::get('zoop.gui.page_title.format')
	);
}
