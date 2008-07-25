<?php
/**
* @category zoop
* @package forms
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
 * cell 
 * 
 * @package 
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class cell
{
	/**
	 * name 
	 * 
	 * @var mixed
	 * @access public
	 */
	var $name;
	/**
	 * value 
	 * 
	 * @var mixed
	 * @access public
	 */
	var $value;
	/**
	 * description 
	 * 
	 * @var mixed
	 * @access public
	 */
	var $description;

	/**
	 * cell 
	 * 
	 * @param mixed $name 
	 * @param mixed $value 
	 * @access public
	 * @return void
	 */
	function cell($name, $value)
	{
		$this->name = $name;
		$this->value = $value;
	}
}
?>
