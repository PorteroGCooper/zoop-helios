<?php
/**
* @package db
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

//
//	class: complex update
//
//	the best thing I think to do here is to just give an example of how to use this class
//	
//	if we had a table items with fields id and data
//		and we wanted to update the data fields for two items (id = 1, 2; data = 'one', 'two')
//		and we wanted to do it all in one query we could do it like this
//
//	$updater = &new ComplexUpdate('table_name', 'where a = 6');
//	$updater->addField("data", "id");
//	$updater->addCase("data", 1, 'one');
//	$updater->addCase("data", 2, 'two');
//	$updater->go();

class ComplexUpdate {

	var $tableName;
	var $whereClause;
	var $fields;
	
	function ComplexUpdate($inTableName, $inWhereClause = '') {
		$this->tableName = $inTableName;
		$this->whereClause = $inWhereClause;
	}
	
	function addField($inSetField, $inCaseField) {
		$this->fields[$inSetField]->caseField = $inCaseField;
		$this->fields[$inSetField]->cases = array();
	}
	
	function addCase($inField, $inWhen, $inThen) {
		$this->fields[$inField]->cases[$inWhen] = $inThen;
	}
	
	function go($inExecute = 1) {
		if(count($this->fields) == 0) return 0;
		
		$executeIt = 0;
		
		//	initialze the main query string
		$queryString = 'UPDATE ' . $this->tableName . ' SET ';
		
		//	reset the array just in case
		reset($this->fields);
		
		//	take care of the first field (no comma)
		list($fieldName, $fieldData) = each($this->fields);
		
		if(count($fieldData->cases) > 0) {
			$caseString =  $fieldName . ' = CASE ' . $fieldData->caseField . ' ';
			
			reset($fieldData->cases);
			while(list($caseKey, $caseValue) = each($fieldData->cases)) {
				$executeIt = 1;
				
				if($caseValue === null) {
					$caseString .= 'WHEN ' . $caseKey . ' THEN NULL ';
				} else {
					$caseString .= 'WHEN ' . $caseKey . " THEN '" . $caseValue . "' ";
				}
			}
			
			$caseString .=  'ELSE ' . $fieldName . ' END ';
			
			$queryString .= $caseString;
			$usedFirstField = 1;
		} else {
			$usedFirstField = 0;
		}
		
		//	take care of the rest of them (add in the comma this time)
		while(list($fieldName, $fieldData) = each($this->fields)) {
			if(count($fieldData->cases) > 0) {
				if($usedFirstField) {
					$caseString = ' , ';
				} else {
					$caseString = '';
				}
				
				//	strictly speaking this flag is now named improperly.  We really just want to set
				//	it to one because we need the comma in there from here on out
				$usedFirstField = 1;
				
				$caseString .= $fieldName . ' = CASE ' . $fieldData->caseField . ' ';
				
				reset($fieldData->cases);
				while(list($caseKey, $caseValue) = each($fieldData->cases)) {
					$executeIt = 1;
					
					if($caseValue === null) {
						$caseString .= 'WHEN ' . $caseKey . ' THEN NULL ';
					} else {
						$caseString .= 'WHEN ' . $caseKey . " THEN '" . $caseValue . "' ";
					}
				}
				
				$caseString .=  'ELSE ' . $fieldName . ' END ';
				
				$queryString .= $caseString;
			}
		}
		
		if(strlen($this->whereClause) > 0) $queryString .= 'WHERE ' . $this->whereClause;
		
		if($executeIt) {
			if($inExecute) {
				sql_query($queryString);
			} else {
				die($queryString);
			}
		}
	}
}