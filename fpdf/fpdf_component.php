<?php
/**
* @category zoop
* @package zone
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

/**
 * component_zone 
 * 
 * @uses component
 * @package 
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class component_fpdf extends component
{
	/**
	 * component_zone 
	 * 
	 * @access public
	 * @return void
	 */
	function component_fpdf()
	{
		
	}
	
	function getIncludes()
	{
		return array(
						"fpdf" => dirname(__file__) . "/fpdf.php"
					);
	}


	/**
	 * run 
	 * 
	 * @access public
	 * @return void
	 */
	function run()
	{
	}
}
?>
