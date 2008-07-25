<?php
/**
* @category zoop
* @package zone
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
 * component_zone 
 * 
 * @uses component
 * @package 
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_xml extends component
{
	
	function getIncludes()
	{
		$includes = array();
		if(version_compare(phpversion(), "5.0.0", "<"))
			$includes = array('xml_tree' => 'XML/Tree.php');
		return $includes + array(
						"xmldom" => $this->getBasePath() . "/XmlDom.php",
						"xmlnode" => $this->getBasePath() . "/XmlNode.php",
						"xmlnodelist" => $this->getBasePath() . "/XmlNodeList.php",
						"propertylist" => $this->getBasePath() . "/PropertyList.php",
						"xmlutils" => $this->getBasePath() . "/utils.php",
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
