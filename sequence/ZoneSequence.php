<?php
/**
* @package sequence
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

//This is the workhorse of sequencing.
//It is a class that takes user supplied parameters, xml defined sequences, and 
//then creates urls. This is the object that gets stored in the session whenever
//you create a sequence, whether the user goes there or not.
//there needs to be some kind of garbage collection, but at the moment, we don't have any.
//important functions for users to know:

//	ZoneSequence($name)
//		constructor, sets it up
//	setParam($name, $value)
//		use this to set parameters that will be used in building urls
//	getUrl([$startstep [, $startpage]])
//		use this to get the starting url for a sequence, and only to get the starting url

//everything else should be handled(well) by zone_sequence
//look there for examples

/**
* @package sequence
*/
class ZoneSequence
{
	function ZoneSequence($sequenceName)
	{
		global $sequenceData;
		global $sGlobals;
		if(!isset($sGlobals->lastId))
		{
			$sGlobals->lastId = 0;
		}
		$this->id = $sGlobals->lastId++;
		
		$sGlobals->sequences[$this->id]['obj'] = &$this;
		$this->startUrl = VIRTUAL_URL;
		$this->name = $sequenceName;
		if(!$sequenceData->sequenceExists($this->name))
		{
			trigger_error("{$this->name} is not a valid zonesequence, please add it to the xml");
			echo_r($sequenceData);
		}
	}
	
	function getName()
	{
		return $this->name;
	}
	
	function setParam($paramName, $value)
	{
		$this->params[$paramName] = $value;
	}
	
	function getParam($paramName)
	{
		return $this->params[$paramName];
	}
	
	function pageInSequence($stepName, $pageName)
	{
		global $sequenceData;
		$step = $sequenceData->getZoneSequenceStep($this->name, $stepName);
		$pagesequence = &$sequenceData->getPageSequence($step['zone'], $step['pagesequence']);
		return isset($pagesequence['steps'][$pageName]) || isset($pagesequence['allowedpages'][$pageName]);
	}
	
	function &verifyParams()
	{
		global $sequenceData;
		$sequenceParams = &$sequenceData->getSequenceParams($this->name);
		//echo_r($sequenceParams);
		foreach($sequenceParams as $paramName => $paramstuff)
		{
			foreach($paramstuff as $paramtag)
			{
				$paramMap[$paramtag['mapto']][] = $paramtag;
				$map = end($paramMap[$paramtag['mapto']]);
				if(!isset($this->params[$paramName]) && !isset($map['value']))
				{
					trigger_error("please set '$paramName' before requesting an url");
				}
			}			
		}
		foreach($sequenceData->getZoneSequenceSteps($this->name) as $step)
		{
			if(isset($step['param']))
			{
				foreach($step['param'] as $param)
				{
					if(!isset($this->params[$step['zone'] . '.' . $param['name']]))
					{
						trigger_error("please set '$paramName' before requesting an url");
					}
				}
			}
		}
		return $paramMap;
	}
	
	function getStepParams($stepName)
	{
		//$params = $this->params;
		$paramMap = &$this->getParamMap();
		foreach($paramMap as $paramName => $paramtag)
		{
			$found = false;
			
			foreach($paramtag as $stepparam)
			{
				if(isset($stepparam['steps']) && in_array($stepName, $stepparam['steps']))
				{
					if(isset($this->params[$stepparam['name']]))
					{
						$params[$paramName] = $this->params[$stepparam['name']];
					}
					else
					{
						$params[$paramName] = $stepparam['value'];
					}
				}
				else if(!isset($stepparam['steps']))
				{
					if(isset($this->params[$stepparam['name']]))
					{
						$params[$paramName] = $this->params[$stepparam['name']];
					}
					else
					{
						$params[$paramName] = $stepparam['value'];
					}
				}
			}
		}
		return $params;
	}
	
	function getZoneParams($zoneName, $params)
	{
		global $sequenceData;
		$url = '';
		
		
		if($zoneName != 'default');
		$url .= "/$zoneName";
		//foreach parameter to the zone...
		foreach($sequenceData->getZoneParams($zoneName) as $paramName => $param)
		{
			if(isset($this->params[$zoneName . '.' . $paramName]))
			{
				$url .= '/' . $this->params[$zoneName . '.' . $paramName];
			}
			else
			{
				$url .= '/' . $params[$paramName];
			}
		}
			
		return $url;
	}
	
	function getPageParams($thisStep)
	{
		//not used yet, but should be in the future.
		$paramMap = &$this->getParamMap();
		
		$url = "/{$thisStep['page']}";
		if(isset($thisStep['params']))
		{
			foreach($thisStep['params'] as $paramName => $param)
			{
				if(isset($this->params[$paramName]))
				{
					$url .= "/{$this->params[$paramName]}";
					continue;
				}
				else if(isset($param['value']))
				{
					$url .= "/{$param['value']}";
					continue;
				}
				else
				{
					trigger_error("unhandled sequencing issues here");
				}
			}
		}
		return $url;
	}
	
	//gets a map of true parameter names to passed in names, so that when a user specifies 
	//parameter 'b2', it can be mapped to zone b's parameter called 'second'
	function getParamMap()
	{
		global $sequenceData;
		if(APP_STATUS != 'live')
		{
			$paramMap = &$this->verifyParams();
		}
		else
		{
			$sequenceParams = &$sequenceData->getSequenceParams($this->name);
			foreach($sequenceParams as $paramName => $paramstuff)
			{
				foreach($paramstuff as $paramtag)
				{
					$paramMap[$paramtag['mapto']][] = $paramtag;
				}
			}
		}
		return $paramMap;
	}
	
	//This function should only be called when not in the sequence already.  
	//It adds the sequence to the sequencestack on the url, and gets the rest of the url
	function getUrl($startZone = null, $startPage = null)
	{
		global $sequenceData;
		global $sequenceStack;
		
		//Start with the zonesequencestep.
		$step = $sequenceData->getZoneSequenceStep($this->name, $startZone);
		$stepName = $step['name'];
				
		//set up the sequencestack
		$stacktemp = $sequenceStack;//in PHP 5 we should use clone here....
		$stacktemp[] = "{$this->id},$stepName";
		//url starts with the whole stack, plus this one.
		$url = SCRIPT_URL . '/' . implode(':', $stacktemp);		
		
		/*
		//don't put default into the url
		if($step['zone'] != 'default')
			$url .= '/' . $step['zone'];
		*/
		//what zones are needed to make an url to this zone(e.g. zone_base)
		$urlparts = $sequenceData->getZoneUrlParts($step['zone']);
		$params = &$this->getStepParams($stepName);
		//foreach base zone		
		foreach($urlparts as $urlzone)
		{
			//fill in the url with the zone/parameters
			//echo_r($urlzone);
			
			$url .= $this->getZoneParams($urlzone, $params);
		}
		
		$pagesequence = $step['pagesequence'];
		//get the page parts of the url from the pagesequence
		$pagesequence = &$sequenceData->getPageSequence($step['zone'], $pagesequence);
		//echo_r($pagesequence);
		if($startPage)
		{
			if(isset($pagesequence['steps'][$startPage]))
			{
				$thisStep = $pagesequence['steps'][$startPage];
			}
			else if(isset($pagesequence['allowedpages'][$startPage]))
			{
				$thisStep = $pagesequence['allowedpages'][$startPage];
			}
			else
			{
				trigger_error("please define page $startPage in zone {$step['zone']} for sequence {$pagesequence['name']}");
			}
		}
		else
		{
			$thisStep = reset($pagesequence['steps']);
		}
		//fill in the page/params
		$url .= $this->getPageParams($thisStep);
		//echo($url);
		return $url;
	}
	
	function pageStepDirection($stepName, $pageName, $direction = 'back')
	{
		global $sequenceStack, $sequenceData;
		$step = &$sequenceData->getZoneSequenceStep($this->name, $stepName);
		
		$zone = $step['zone'];
		$pageSequence = $step['pagesequence'];
		
		$pageSequence = &$sequenceData->getPageSequence($zone, $pageSequence);
		//wind through the steps and find the current page
		$key = '';
		for(reset($pageSequence['steps']); key($pageSequence['steps']) != $pageName; next($pageSequence['steps']))
		{
		}
		if($direction == 'back')
			$newStep = prev($pageSequence['steps']);
		else
			$newStep = next($pageSequence['steps']);
		if(!$newStep)
		{
			return false;
		}
		else
		{
			$url = SCRIPT_URL . '/' . 
				implode(':', $sequenceStack);
			$params = $this->getStepParams($stepName);
			$urlparts = $sequenceData->getZoneUrlParts($zone);
			//foreach base zone
			foreach($urlparts as $urlzone)
			{
				//fill in the url with the zone/parameters
				//echo_r($urlzone);
				$url .= $this->getZoneParams($urlzone, $params);
			}
			
			return $url . $this->getPageParams($newStep);
		}
	}
	
	function getPageLabel($stepName, $page)
	{
		global $sequenceStack,$sequenceData;
		$step = $sequenceData->getZoneSequenceStep($this->name, $stepName);
		$zone = $step['zone'];
		$pageSequence = $step['pagesequence'];
		$pageSequence = &$sequenceData->getPageSequence($zone, $pageSequence);
		if(isset($pageSequence['steps'][$page]))
		{
			if(isset($pageSequence['steps'][$page]['label']))
				return $pageSequence['steps'][$page]['label'];
			else
				return '';
		}
		else if(isset($pageSequence['allowedpages'][$page]))
		{
			if(isset($pageSequence['steps'][$page]['label']))
				return $pageSequence['allowedpages'][$page]['label'];
			else
				return '';
		}
		else
		{
			return '';
		}
	}
	
	function getNavBar($stepName)
	{
		//markprofile();
		global $sequenceStack,$sequenceData;
		$step = $sequenceData->getZoneSequenceStep($this->name, $stepName);
		$zone = $step['zone'];
		$pageSequence = $step['pagesequence'];
		$pageSequence = &$sequenceData->getPageSequence($zone, $pageSequence);
		$links = array();
		foreach($pageSequence['steps'] as $key=>$step)
		{
			if(isset($step['label']))
			{
				$urlparts = $sequenceData->getZoneUrlParts($zone);
				$params = $this->getStepParams($stepName);
				
				//foreach base zone
				$url = SCRIPT_URL . '/' . implode(':', $sequenceStack);
				foreach($urlparts as $urlzone)
				{
					//fill in the url with the zone/parameters
					$url .= $this->getZoneParams($urlzone, $params);
				}
				$url .= $this->getPageParams($step);			
				$links[$step['label']] = $url;
			}
		}
		//markprofile();
		//echo_r($links);
		return $links;
	}
	
	function stepDirection($stepName, $direction = 'back')
	{
		global $sequenceStack,$sequenceData;
		$steps = &$sequenceData->getZoneSequenceSteps($this->name);
		reset($steps);
		$key = '';
		while(list($key, $value) = each($steps) && $key != $stepName)
		{
			//do nothing
		}
		if($direction == 'back')
			$newStep = prev($steps);
		else
			$newStep = next($steps);
		
		if(!$newStep)
		{
			return $this->startUrl;
		}
		else
		{
			$zone = $newStep['zone'];
			$pageSequence = $newStep['pagesequence'];
			$pageSequence = $sequenceData->getPageSequence($zone, $pageSequence);
			if($direction == 'back')
			{
				$step = end($pageSequence['steps']);
			}
			else
			{
				$step = reset($pageSequence['steps']);
			}
			$currentStep = array_pop($sequenceStack);
			$currentStep = explode(",", $currentStep);
			$currentStep[1] = $newStep['name'];
			$currentStep = implode(",", $currentStep);
			array_push($sequenceStack, $currentStep);
			$url = SCRIPT_URL . '/' . 
				implode(':', $sequenceStack);
			$params = $this->getStepParams($currentStep);	
			$urlparts = $sequenceData->getZoneUrlParts($zone);
			//foreach base zone
			foreach($urlparts as $urlzone)
			{
				//fill in the url with the zone/parameters
				//echo_r($urlzone);
				$url .= $this->getZoneParams($urlzone, $params);
			}
			
			return $url . $this->getPageParams($step);
		}
	}
	
	function getCurrentPageSequence()
	{
		global $currentSequenceStep, $sequenceData;
		$step = &$sequenceData->getZoneSequenceStep($this->name, $currentSequenceStep);
		
		$zone = $step['zone'];
		$pageSequence = $step['pagesequence'];
		
		$pageSequence = &$sequenceData->getPageSequence($zone, $pageSequence);
		return $pageSequence['name'];
		
	}
	
	function getActionUrl($stepName, $pageName, $action)
	{
		global $sequenceData;
		global $sequenceStack;
		$step = $sequenceData->getZoneSequenceStep($this->name, $stepName);
		
		$zone = $step['zone'];
		$pageSequence = $step['pagesequence'];
		
		$pageSequence = &$sequenceData->getPageSequence($zone, $pageSequence);
		$url = SCRIPT_URL . '/' . implode(':', $sequenceStack);
		if(isset($pageSequence['allowedpages'][$pageName]))
		{
			$newStep = $pageSequence['allowedpages'][$pageName]['actions'][$action];
		}
		if(isset($pageSequence['steps'][$pageName]['actions'][$action]))
		{
			
			$newStep = $pageSequence['steps'][$pageName]['actions'][$action];
		}
		if(isset($newStep))
		{
			switch($newStep['type'])
			{
				case 'nextPage':
					//do a next...
					$url = $this->pageStepDirection($stepName, $pageName, 'forward');
					if(!$url)
					{
						$url = $this->stepDirection($stepName, $pageName, 'forward');
					}
					break;
				case 'backPage':
					//do a back...
					$url = $this->pageStepDirection($stepName,$pageName, 'back');
					if(!$url)
					{
						$url = $this->stepDirection($stepName, 'back');
					}
					break;
				case 'nextZone':
					//go to the next zone
					$url = $this->stepDirection($stepName, $pageName, 'forward');
					break;
				case 'backZone':
					//go to the prev zone
					$url = $this->stepDirection($stepName, $pageName, 'back');
					break;
				case 'namedStep':
					//change current step....
					$newZoneStep = $newStep['step'];
					
					$step = $sequenceData->getZoneSequenceStep($this->name, $newZoneStep);		
					$zone = $step['zone'];
					$pageSequence = $step['pagesequence'];					
					$pageSequence = &$sequenceData->getPageSequence($zone, $pageSequence);
					$curstep = explode(",", array_pop($sequenceStack));
					$curstep[1] = $newStep['step'];
					$curstep = implode(",", $curstep);
					array_push($sequenceStack, $curstep);
					$url = SCRIPT_URL . '/' . implode(':', $sequenceStack);
					
					$steps = $sequenceData->getZoneSequenceSteps($this->name);
					$newZone = $steps[$newZoneStep]['zone'];
					$urlparts = $sequenceData->getZoneUrlParts($newZone);
					$params = $this->getStepParams($newZoneStep);
					foreach($urlparts as $urlzone)
					{
						//fill in the url with the zone/parameters
						//echo_r($urlzone);
						$url .= $this->getZoneParams($urlzone, $params);
					}
					if(isset($newStep['page']))
					{
						$newPageStep = $newStep['page'];
					}
					else
					{
						$newPageStep = reset($pageSequence['steps']);
						$newPageStep = $newPageStep['page'];
					}
					if(isset($pageSequence['steps'][$newPageStep]))
					{
						$url .= $this->getPageParams($pageSequence['steps'][$newPageStep]);
					}
					else
					{
						$url .= $this->getPageParams($pageSequence['allowedpages'][$newPageStep]);
					}
					break;
				case 'exitSequence':
					//exit this sequence....
					$url = $this->startUrl;
					break;
				case 'page':
					$params = $this->getStepParams($stepName);
					$urlparts = $sequenceData->getZoneUrlParts($zone);
					//foreach base zone
					foreach($urlparts as $urlzone)
					{
						//fill in the url with the zone/parameters
						//echo_r($urlzone);
						$url .= $this->getZoneParams($urlzone, $params);
					}
					if(isset($pageSequence['steps'][$newStep['page']]))
					{
						$url .= $this->getPageParams($pageSequence['steps'][$newStep['page']]);
					}
					else
					{
						$url .= $this->getPageParams($pageSequence['allowedpages'][$newStep['page']]);
					}
					break;
				default:
					trigger_error("Action type {$newStep['type']} not yet implemented");
					break;
			}
		}
		else if(isset($pageSequence['steps'][$pageName]))
		{
			if($action == 'back')
			{
				$url = $this->pageStepDirection($stepName,$pageName, 'back');
				if(!$url)
				{
					$url = $this->stepDirection($stepName, 'back');
				}
			}
			else
			{
				$url = $this->pageStepDirection($stepName, $pageName, 'forward');
				if(!$url)
				{
					$url = $this->stepDirection($stepName, $pageName, 'forward');
				}
			}
		}
		else
		{
			trigger_error("sequence doesn't know where we are?");
		}
		return $url;
	}
}
?>