<?
/**
* @category zoop
* @package gui
*/
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

	require(dirname(__file__) . "/gui.php");
	include('GuiControls/GuiControl.php');
	include('GuiControls/GuiContainer.php');
	include('GuiControls/GuiMultiValue.php');

/**
 * component_gui
 *
 * @uses component
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class component_gui extends component
{
	/**
	 * component_gui
	 *
	 * @access public
	 * @return void
	 */
	function component_gui()
	{
		$this->requireComponent('session');
		$this->requireComponent('validate');
	}

	/**
	 * init
	 *
	 * @access public
	 * @return void
	 */
	function init()
	{
		$GLOBALS['gui'] = new gui();
		initGuiControls();
	}
}
?>
