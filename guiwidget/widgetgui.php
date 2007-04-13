<?php
/**
* @package gui
*/
// Copyright (c) 2006 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/**
 * WidgetGui
 *
 * @uses Smarty
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class WidgetGui extends gui
{
	/**
	 * WidgetGui
	 *
	 * @access public
	 * @return void
	 */
	function WidgetGui()
	{
		parent::gui();
   		$this->template_dir = gui_base . "/widgets";
	}

	function fetch($tpl_file, $cache_id = null, $compile_id = null)
	{
		return Smarty::fetch($tpl_file, $cache_id, $compile_id);
	}
}
?>
