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
 * component_sequence class.
 *
 * @ingroup components
 * @ingroup sequence
 * @extends component
 */
class component_sequence extends component {

	function __construct() {
		$this->requireComponent('session');
		$this->requireComponent('xml');
	}
	
	function getIncludes() {
		$base = $this->getBasePath();
		return array(
			'sequencedata'   => $base . "/SequenceData.php",
			'sequenceparser' => $base . "/SequenceParser.php",
			'zonesequence'   => $base . "/zonesequence.php"
		);
	}

	function init() {
		if(defined('sequence_file')) {
			$sequenceFile = sequence_file;
			global $sequenceData, $sGlobals, $PATH_ARRAY;
			$sequences = &new SequenceParser($sequenceFile);
			$GLOBALS['sequenceData'] = $sequences->getSequenceObj();
			if(isset($PATH_ARRAY[1]) && is_numeric(substr($PATH_ARRAY[1],0,1)))
			{
				$temp = array_shift($PATH_ARRAY);
				$stack = array_shift($PATH_ARRAY);
				array_unshift($PATH_ARRAY, $temp);
				$GLOBALS['sequenceStack'] = explode(':', $stack);
				$seq = explode(',', end($GLOBALS['sequenceStack']));
				$seqId = $seq[0];
				if(isset($sGlobals->sequences))
				{
					$GLOBALS['currentSequence'] = &$sGlobals->sequences[$seqId]['obj'];
					$GLOBALS['currentSequenceStep'] = $seq[1];
				}
			}
		}
	}
}
