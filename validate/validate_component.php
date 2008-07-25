<?php
/**
* @package storage
*/
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
* @package storage
*/
class component_validate extends component
{
	function getIncludes()
	{
		return array('Validator' => $this->getBasePath(). '/validate.php');
	}

	/**
	 * init
	 *
	 * @access public
	 * @return void
	 */
	function init()
	{

	}
}
?>
