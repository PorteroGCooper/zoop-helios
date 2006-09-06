<?php
/**
* @package db
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

	class ComplexInsert
	{
		var $tableName;
		var $fields;
		var $rows;
		
		
		function ComplexInsert($inTableName)
		{
			$this->tableName = $inTableName;
			$this->rows = array();
		}
		
		
		function setFields($inFields)
		{
		    $numargs = func_num_args();
		    
		    $arg_list = func_get_args();
			
		    for($i = 0; $i < $numargs; $i++)
			{
		    	$this->fields[] = $arg_list[$i];
			}
	    }
		
		
		function addRow($inCells)
		{
			$numargs = func_num_args();
		    
			assert($numargs == count($this->fields));
			
		    $arg_list = func_get_args();
			
			$newRow = array();
		    for ($i = 0; $i < $numargs; $i++)
			{
		    	$newRow[] = $arg_list[$i];
			}
			
			$this->rows[] = $newRow;
		}
		
		
		function getNumRows()
		{
			return count($this->rows);
		}
		
		
		function go($inExecute = 1)
		{
			if(count($this->fields) == 0)
				return 0;
			
			if(count($this->rows) == 0)
				return 0;
			
			//	convert to a comma delimited list
			
			$theFields = array();
			foreach($this->fields as $thisField)
			{
				if( gettype($thisField) == "array" )
				{
					$theFields[] = $thisField[1];
				}
				else
				{
					$theFields[] = $thisField;
				}
			}
			
			$fieldList = implode(", ", $theFields);
			
			
			$insertTextArray = array();
			
			
			while(list($rowKey, $thisRow) = each($this->rows))
			{
				$textString = "SELECT";
				
				$first = 1;
				
				foreach($thisRow as $fieldKey => $thisField)
				{
					$fieldDesc = $this->fields[$fieldKey];
					
					if($first == 0)
					{
						$textString .= ",";
					}
					
					if($thisField === NULL)
					{
						if( (gettype($fieldDesc) == "array") && ($fieldDesc[0] == "date") )
							$textString .= " NULL::timestamp";
						else if(gettype($fieldDesc == "array") && ($fieldDesc[0] == "int") )
							$textString .= " NULL::integer";
						else
							$textString .= " NULL";
					}
					else
					{
						
						if( (gettype($fieldDesc) == "array") && ($fieldDesc[0] == "int") )
						{
							if( !is_numeric($thisField) )
								trigger_error("$fieldDesc[1] must be numeric, got $thisField");	// if they are saying that this is an int it better be an int
							$textString .= " $thisField";
						}
						else if( (gettype($fieldDesc) == "array") && ($fieldDesc[0] == "date") )
						{
							if( strlen($thisField) == 0)
								$textString .= " CAST(NULL AS timestamp)";
							else
								$textString .= " CAST('" . $thisField . "' AS timestamp)";
						}
						else
						{
							$textString .= " '" . $thisField . "'";
						}
						
					}
					
					$first = 0;
				}
				
				$insertTextArray[] = $textString;
			}
			
			$insertTextQuery = implode(" UNION ", $insertTextArray);
			
			$tableName = $this->tableName;
			
			$insertQuery = "INSERT INTO $tableName ($fieldList) " . $insertTextQuery;
			
			if($inExecute)
				sql_query($insertQuery);
			else
				die($insertQuery);
		}
	}
?>