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
class component_xml extends component
{
	/**
	 * component_zone 
	 * 
	 * @access public
	 * @return void
	 */
	function component_xml()
	{
		
	}
	
	function getIncludes()
	{
		if(version_compare(phpversion(), "5.0.0", "<"))
			$includes = array('xml_tree' => 'XML/Tree.php');
		return $includes + array(
						"xmldom" => dirname(__file__) . "/XmlDom.php",
						"xmlnode" => dirname(__file__) . "/XmlNode.php",
						"xmlnodelist" => dirname(__file__) . "/XmlNodeList.php",
						"propertylist" => dirname(__file__) . "/PropertyList.php",
						"xmlutils" => dirname(__file__) . "/utils.php",
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
