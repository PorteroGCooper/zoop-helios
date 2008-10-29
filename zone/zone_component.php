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
class component_zone extends component
{
	function component_zone()
	{
		$this->requireComponent('app');
		if(!defined('zone_saveinsession')  || zone_saveinsession) {
			$this->requireComponent('session');
		}
	}
		
	function getIncludes()
	{
		return array(
						"zone" => $this->getBasePath() . "/zone.php",
						"crudZone" => $this->getBasePath() . '/crudZone.php',
						"zone_sequence" => $this->getBasePath() . "/zone_sequence.php",
						"zone_zoopfile" => $this->getBasePath() . "/zone_zoopfile.php"
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
		global $PATH_ARRAY;
		if(defined('zone_saveinsession')  && zone_saveinsession) {
			if (!isset($_SESSION["thsZone"])) {
				if(!class_exists('zone_default')) {
					trigger_error("Please create zone_default");
				}
				session_register("thsZone");
				$_SESSION["thsZone"] = &new zone_default();
			}
			$thsZone = &$_SESSION['thsZone'];
			if(!is_a($thsZone, 'zone')) {
				trigger_error("Please include zone_default.php before calling run()");
			}
		} else {
			if(!class_exists('zone_default')) {
				trigger_error("Please create zone_default");
			}
			$thsZone = &new zone_default();
		}
		$thsZone->handleRequest($PATH_ARRAY);
	}
}
?>
