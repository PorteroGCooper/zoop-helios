<?php
/**
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
	 * zone_sequence 
	 * 
	 * @uses zone
	 * @package 
	 * @version $id$
	 * @copyright 1997-2008 Supernerd LLC
	 * @author Steve Francia <steve.francia+zoop@gmail.com> 
	 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
	 */
	class zone_sequence extends zone
	{
		/**
		 * inSequence 
		 * 
		 * @access public
		 * @return void
		 */
		function inSequence()
		{
			global $sequenceStack;
			return isset($sequenceStack);
		}
		
		/**
		 * isInSequence 
		 * 
		 * @param mixed $sequenceId 
		 * @param mixed $pageName 
		 * @access public
		 * @return void
		 */
		function isInSequence($sequenceId, $pageName)
		{
			global $currentSequence, $currentSequenceStep;
			if(isset($currentSequence))
			{
				if($currentSequence->pageInSequence($currentSequenceStep, $pageName))
				{
					return true;
				}
			}
			return false;
		}
		
		/**
		 * isCurrentSequence 
		 * 
		 * @param mixed $allowedSequences 
		 * @access public
		 * @return void
		 */
		function isCurrentSequence($allowedSequences)
		{
			global $currentSequence;
			if(is_array($allowedSequences))
			{
				return in_array($currentSequence->getCurrentPageSequence(), $allowedSequences);
			}
			else
			{
				return $currentSequence->getName() == $allowedSequences;
			}
		}
		
		/**
		 * &getCurrentSequence 
		 * 
		 * @access public
		 * @return void
		 */
		function &getCurrentSequence()
		{
			global $currentSequence;
			return $currentSequence;
		}
		
		/**
		 * &getCurrentStep 
		 * 
		 * @access public
		 * @return void
		 */
		function &getCurrentStep()
		{
			global $currentSequenceStep;
			return $currentSequenceStep;
		}
				
		/**
		 * sequenceRedirect 
		 * 
		 * @param mixed $page 
		 * @param mixed $action 
		 * @access public
		 * @return void
		 */
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
		
		/**
		 * getNavBar 
		 * 
		 * @access public
		 * @return void
		 */
		function getNavBar()
		{
			$currentSequence = &$this->getCurrentSequence();
			$currentStep = &$this->getCurrentStep();
			return $currentSequence->getNavBar($currentStep);
		}
		
		/**
		 * getCurrentPage 
		 * 
		 * @param mixed $page 
		 * @access public
		 * @return void
		 */
		function getCurrentPage($page)
		{
			$currentSequence = &$this->getCurrentSequence();
			$currentStep = &$this->getCurrentStep();
			return $currentSequence->getPageLabel($currentStep, $page);
		}
		
		/**
		 * navBarRedirect 
		 * 
		 * @param mixed $action 
		 * @access public
		 * @return void
		 */
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
		
		/**
		 * closePosts 
		 * 
		 * @param mixed $inPath 
		 * @access public
		 * @return void
		 */
		function closePosts($inPath)
		{
			$action = POST::getText("actionField");
			
			if(substr($action,0,3) == "nav")
			{					
				$this->navBarRedirect($action);				
			}
			$this->sequenceRedirect($inPath[0], $action);			
		}
				
	}
?>
