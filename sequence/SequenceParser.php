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

//The parser for the xml file. parses the file into php data structures...
//this is the place to look if you want file format information, but good luck
//figuring it out. I wrote it in a day, alright?

/**
* @package sequence
*/
class SequenceParser
{
	function sequenceParser($filename = 'sequence.xml')
	{
		$xmldom = &new BMXmlDom();
		$this->xmlnode = $xmldom->parseFile($filename);
	}
	
	function &getSequenceObj()
	{
		if(PEAR::isError($this->xmlnode->nodeData))
		{
			trigger_error("error parsing file");
			echo_r($this->xmlnode);
		}
		$children = &$this->xmlnode->getChildren();
		
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			switch($thisChild->getName())
			{
				case 'zone':
					$this->handleZone($thisChild);	
					break;
				case 'zonesequence':
					$this->handleZoneSequence($thisChild);
					break;
			}
		}
		$obj = &new SequenceData($this->zonelist, $this->sequencelist);
		return $obj;
		//echo_r($this->zonelist);
		//echo_r($this->sequencelist);
	}
	
	function handleZone(&$zoneNode)
	{
		$this->zonelist[$zoneNode->getAttribute('name')]['name'] = $zoneNode->getAttribute('name');
		
		$children = &$zoneNode->getChildren();
		
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			switch($thisChild->getName())
			{
				case 'param':
					$param = $this->handleParam($thisChild);
					$this->zonelist[$zoneNode->getAttribute('name')]['params'][$param['name']][] = $param;
					break;
				case 'pagesequence':
					$sequence = &$this->handlePageSequence($thisChild);
					$this->zonelist[$zoneNode->getAttribute('name')]['sequences'][$sequence['name']] = &$sequence;
					break;
				case 'url':
					$links = &$thisChild->getChildren();
					$urls = array();
					for($link = $links->current(); $links->valid(); $link = $links->next())
					{
						$urls[] = $link->getAttribute('name');
					}
					$this->zonelist[$zoneNode->getAttribute('name')]['url'] = $urls;
				
			}
		}
	}
	
	function &handleParam($paramNode)
	{
		
		$param = array();
		$param['name'] = $paramNode->getAttribute('name');
		if($paramNode->hasAttribute('value'))
		{
			$param['value'] = $paramNode->getAttribute('value');
		}
		if($paramNode->hasAttribute('steps'))
		{
			$param['steps'] = explode(',', $paramNode->getAttribute('steps'));
		}
		if($paramNode->hasAttribute('mapto'))
		{
			$param['mapto'] = $paramNode->getAttribute('mapto');
		}
		else
		{
			$param['mapto'] = $param['name'];
		}
		if($paramNode->hasAttribute('shared'))
		{
			$param['shared'] = $paramNode->getAttribute('shared');
		}
		return $param;
	}
		
	
	
	function handleZoneSequence(&$sequenceNode)
	{
		$this->sequencelist[$sequenceNode->getAttribute('name')]['name'] = $sequenceNode->getAttribute('name');
		
		$children = &$sequenceNode->getChildren();
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			switch($thisChild->getName())
			{
				case 'param':
					$param = $this->handleParam($thisChild); 
					$this->sequencelist[$sequenceNode->getAttribute('name')]['params'][$param['name']][] = $param;							
					break;
				case 'step':
					$step = &$this->handleStep($thisChild);
					$this->sequencelist[$sequenceNode->getAttribute('name')]['steps'][$step['name']] = 
							$step;
					break;
			}
		}
				
		
	}
	
	function &handleStep(&$stepNode)
	{
		$step = array();
		if($stepNode->hasAttribute('zone'))
		{
			$step['zone'] = $stepNode->getAttribute('zone');
		}
		if($stepNode->hasAttribute('page'))
		{
			$step['page'] = $stepNode->getAttribute('page');
		}
		if($stepNode->hasAttribute('name'))
		{
			$step['name'] = $stepNode->getAttribute('name');
		}
		else if(isset($step['zone']))
		{
			$step['name'] = $step['zone'];
		}
		else
		{
			$step['name'] = $step['page'];
		}
		
		if($stepNode->hasAttribute('label'))
		{
			$step['label'] = $stepNode->getAttribute('label');
		}
		
		if($stepNode->hasAttribute('pagesequence'))
		{
			$step['pagesequence'] = $stepNode->getAttribute('pagesequence');
		}
		
		$children = &$stepNode->getChildren();
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			switch($thisChild->getName())
			{
				case 'param':
					$param = $this->handleParam($thisChild);
					$step['params'][$param['name']] = $param;
					break;
				case 'action':
					$action = $this->handleAction($thisChild);
					$step['actions'][$action['name']] = $action;
					break;
			}
		}
		return $step;
	}		
	
	function &handleAction($actionNode)
	{
		$action = array();
		$action['name'] = $actionNode->getAttribute('name');
		if($actionNode->hasAttribute('type'))
		{
			$action['type'] = $actionNode->getAttribute('type');
		}
		else
		{
			$action['type'] = 'page';
		}
		if($actionNode->hasAttribute('page'))
		{
			$action['page'] = $actionNode->getAttribute('page');
		}
		if($actionNode->hasAttribute('step'))
		{
			$action['step'] = $actionNode->getAttribute('step');
		}
		return $action;
	}
	
	function &handlePageSequence(&$sequenceNode)
	{
		$sequence = array();
		$sequence['name'] = $sequenceNode->getAttribute('name');
		$children = &$sequenceNode->getChildren();
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			switch($thisChild->getName())
			{
				case 'param':
					$param = $this->handleParam($thisChild); 
					$sequence['params'][$param['name']][] = &$param;							
					break;
				case 'step':
					$step = &$this->handleStep($thisChild);
					$sequence['steps'][$step['page']] = &$step;
					break;
				case 'freepage':
					$page = &$this->handleStep($thisChild);
					$sequence['allowedpages'][$page['page']] = &$page;
					
					break;
			}
		}
		return $sequence;
	}	
}
?>