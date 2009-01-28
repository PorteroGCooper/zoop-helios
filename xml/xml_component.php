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
 * component_zone 
 * 
 * @ingroup components
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_xml extends component {
	
	function getIncludes() {
		$base = $this->getBasePath();
		
		$includes = array();
		if(version_compare(phpversion(), "5.0.0", "<"))
			$includes = array('xml_tree' => 'XML/Tree.php');
		return $includes + array(
						"xmldom"       => $base . "/XmlDom.php",
						"xmlnode"      => $base . "/XmlNode.php",
						"xmlnodelist"  => $base . "/XmlNodeList.php",
						"propertylist" => $base . "/PropertyList.php",
						"xmlutils"     => $base . "/utils.php",
					);
	}
}
