<?
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.


include_once(dirname(__file__) . "/zone.php");
include_once(dirname(__file__) . "/zone_sequence.php");


class framework_zone extends framework
{
	function framework_zone()
	{
		$this->requireFramework('app');
		if(!defined('zone_saveinsession')  || zone_saveinsession)
			$this->requireFramework('session');
	}

	function run()
	{
		global $PATH_ARRAY;
		if(!defined('zone_saveinsession')  || zone_saveinsession)
		{
			if (!isset($_SESSION["thsZone"]))
			{
				if(!class_exists('zone_default'))
				{
					trigger_error("Please create zone_default");
				}
				session_register("thsZone");
				$_SESSION["thsZone"] = &new zone_default();
			}
			$thsZone = &$_SESSION['thsZone'];
			if(!is_a($thsZone, 'zone'))
			{
				trigger_error("Please include zone_default.php before calling run()");
			}
		}
		else
		{
			if(!class_exists('zone_default'))
			{
				trigger_error("Please create zone_default");
			}
			$thsZone = &new zone_default();
			
		}
		$thsZone->handleRequest($PATH_ARRAY);
	}
}
?>