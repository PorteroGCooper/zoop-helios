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

	class zone_sequence extends zone
	{
		function inSequence()
		{
			global $sequenceStack;
			return isset($sequenceStack);
		}
		
		function &getCurrentSequence()
		{
			global $currentSequence;
			return $currentSequence;
		}
		
		function &getCurrentStep()
		{
			global $currentSequenceStep;
			return $currentSequenceStep;
		}
				
		function sequenceRedirect($page, $action)
		{
			if($this->inSequence())
			{
				$currentSequence = &$this->getCurrentSequence();
				$currentStep = &$this->getCurrentStep();
				
				//echo_r($currentSequence);
				$url = $currentSequence->getActionUrl($currentStep, $page, $action);
				//echo_r($action);
				//echo_r($url);
				if($url)
					redirect($url);
			}	
		}
		
		function getNavBar()
		{
			$currentSequence = &$this->getCurrentSequence();
			$currentStep = &$this->getCurrentStep();
			return $currentSequence->getNavBar($currentStep);
		}
		
		function getCurrentPage($page)
		{
			$currentSequence = &$this->getCurrentSequence();
			$currentStep = &$this->getCurrentStep();
			return $currentSequence->getPageLabel($currentStep, $page);
		}
		
		function navBarRedirect($action)
		{
			if($this->inSequence())
			{
				$currentSequence = &$this->getCurrentSequence();
				$currentStep = &$this->getCurrentStep();
				$orderid = substr($action,4);
				$navbar = $currentSequence->getNavBar($currentStep);
				$labels = array_keys($navbar);
				redirect($navbar[$labels[$orderid]]);
			}
		}
		
		function closePosts($inPath)
		{
			$action = getPostText("actionField");
			
			if(substr($action,0,3) == "nav")
			{					
				$this->navBarRedirect($action);				
			}
			$this->sequenceRedirect($inPath[0], $action);			
		}
				
	}
?>