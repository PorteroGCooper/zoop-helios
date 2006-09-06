<?php
/**
* @package sequence
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

//This class merely holds the xml data and gives some convenience functions used by ZoneSequence
//to get at the xml data.
/**
* @package sequence
*/
class SequenceData
{
	function SequenceData(&$zonelist, &$sequencelist)
	{
		$this->zonelist = &$zonelist;
		$this->sequencelist = &$sequencelist;
	}
	
	function sequenceExists($name, $zone = null)
	{
		if(!$zone)
		{
			return isset($this->sequencelist[$name]);
		}
		else
		{
			return isset($this->zonelist[$zone]['sequences'][$name]);
		}
	}
	
	function &getSequenceParams($name, $zone = null)
	{
		if(!$zone)
		{
			return $this->sequencelist[$name]['params'];
		}
		else
		{
			return $this->zonelist[$zone]['sequences'][$name]['params'];
		}
	}
	
	function &getZoneSequenceSteps($name)
	{
		return $this->sequencelist[$name]['steps'];
	}
	
	function &getZoneSequenceStep($name, $zone = null)
	{
		if(!$zone)
		{
			return reset($this->sequencelist[$name]['steps']);
		}
		else
		{
			return $this->sequencelist[$name]['steps'][$zone];
		}
	}
	
	function &getZoneParams($zone)
	{
		if(!isset($this->zonelist[$zone]))
		{
			trigger_error("please define zone '$zone' in the xml file");
		}
		if(isset($this->zonelist[$zone]['params']))
			return $this->zonelist[$zone]['params'];
		else
			return array();
	}
	
	function &getZoneUrlParts($zone)
	{
		if(!isset($this->zonelist[$zone]))
		{
			trigger_error("please define zone '$zone' in the xml file");
		}
		if(isset($this->zonelist[$zone]['url']))
			return $this->zonelist[$zone]['url'];
		else
			return array();
	}
	
	function getPageSequence($zone, $sequenceName)
	{
		if(!isset($this->zonelist[$zone]))
		{
			trigger_error("please define zone '$zone' in the xml file");
		}
		return $this->zonelist[$zone]['sequences'][$sequenceName];
	}
}
?>