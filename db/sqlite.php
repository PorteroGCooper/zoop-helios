<?
/**
* @package db
* @subpackage sqlite
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

/**
* @package db
* @subpackage mysql
*/
class component_db extends component
{
	function init()
	{
		if(defined('db_Database'))
		{
			$GLOBALS['defaultdb'] = &new database(database::makeDSN(db_RDBMS, NULL, NULL, NULL, NULL, db_Database));
			sqlite_create_function($defaultdb->db->connection, 'age', 'sqlite_age', 1);
			sqlite_create_function($defaultdb->db->connection, 'date_part', 'sqlite_date_part', 2);
			sqlite_create_function($defaultdb->db->connection, 'substr', 'sqlite_substr', 2);
			sqlite_create_function($defaultdb->db->connection, 'rand', 'sqlite_rand', 0);
			if(sqlite_last_error($defaultdb->db->connection))
			{
				echo(sqlite_error_string(sqlite_error()));
			}
		}
	}
}


function sqlite_age($date)
{
	//return $date;
	if($date == NULL || $date == '')
		return '';
	$parts = explode("-", $date);
	$smon = $parts[1];
	$sday = $parts[2];
	$syear = $parts[0];

	$today = time();
	$emon = date("m", $today);
	$eday = date("d", $today);
	$eyear = date("Y", $today);
	$daysdiff = Date_Calc::dateDiff($sday,$smon,$syear, $eday,$emon,$eyear);
	$years = floor($daysdiff / 365.25);
	$months = floor(($daysdiff - (365.25 * $years)) / 30);
	$days = floor(($daysdiff - (365.25 * $years) - (30 * $months)));

	return "$years years $months months $days days";
}

function sqlite_date_part($format, $agestring)
{
	if($agestring == NULL || $agestring == '')
		return '';
	$ageparts = explode(" " , $agestring);
	//echo_r($ageparts);
	$age = array();
	for($i = 0; $i < count($ageparts); $i+=2)
	{
		$age[$ageparts[$i + 1]] = $ageparts[$i];
	}
	if(strtolower($format) == 'y' || strtolower($format) == 'year')
	{
		return $age["years"];
	}
	if(strtolower($format) == 'm' || strtolower($format) == 'month')
	{
		return $age["months"];
	}
}

function sqlite_substr($string, $pos)
{
	return substr($string, $pos - 1);
}

function sqlite_rand()
{
	return rand();
}



	function sql_begin_transaction( $Db = -1 )
	{
		global $defaultdb;
		return $defaultdb->begin_transaction();
	}

	function sql_commit_transaction( $Db = -1 )
	{
		global $defaultdb;
		return $defaultdb->commit_transaction();
	}

	function sql_rollback_transaction( $Db = -1 )
	{
		global $defaultdb;
		return $defaultdb->rollback_transaction();
	}

	function sql_query( $inQueryString, $Db = -1 )
	{
		global $defaultdb;
		return $defaultdb->query($inQueryString);
	}

	function sql_get_fields($table)
	{
		global $defaultdb;
		return $defaultdb->get_fields($table);
	}

	function sql_insert($query)
	{
		global $defaultdb;
		$defaultdb->insert($query);
		return sqlite_last_insert_rowid($defaultdb->db->connection);
	}


	function sql_fetch_sequence( $sequence )
	{
		global $defaultdb;
		return $defaultdb->fetch_sequence($sequence);
	}

	///////////////////////////////////////////////
	//	Query returns true if rows are returned  //
	///////////////////////////////////////////////

	function sql_check($query)
	{
		global $defaultdb;
		return $defaultdb->check($query);
	}

	function sql_fetch_into_arrays($query)
	{
		global $defaultdb;
		return $defaultdb->fetch_into_arrays($query);

	}

	function sql_fetch_into_arrobjs($query)
	{
		global $defaultdb;
		return $defaultdb->fetch_into_arrobjs($query);
	}

	function sql_new_fetch_into_array($query)
	{
		global $defaultdb;
		return $defaultdb->new_fetch_into_array($query);
	}

	function sql_fetch_into_array($inTableName, $inFieldName, $inExtra = "")
	{
		global $defaultdb;
		return $defaultdb->fetch_into_array($inTableName, $inFieldName, $inExtra);
	}


	function sql_fetch_one($inQueryString)
	{
		global $defaultdb;
		return $defaultdb->fetch_one($inQueryString);
	}

	function sql_fetch_assoc($inQuery)
	{
		global $defaultdb;
		return $defaultdb->fetch_assoc($inQuery);
	}

	function sql_fetch_rows($inQuery, $inReturnObjects = 0)
	{
		global $defaultdb;
		return $defaultdb->fetch_rows($inQuery, $inReturnObjects);
	}

	function sql_fetch_map($inQuery, $inKeyField)
	{
		global $defaultdb;
		return $defaultdb->fetch_map($inQuery,$inKeyField);
	}


	function sql_fetch_simple_map($inQuery, $inKeyField, $inValueField)
	{
		global $defaultdb;
		return $defaultdb->fetch_simple_map($inQuery, $inKeyField, $inValueField);
	}


	function sql_fetch_complex_map($inQuery, $inKeyField)
	{
		global $defaultdb;
		return $defaultdb->fetch_complex_map($inQuery, $inKeyField);
	}


	function sql_fetch_one_cell($inQueryString, $inField = 0)
	{
		global $defaultdb;
		return $defaultdb->fetch_one_cell($inQueryString, $inField);
	}

	function &sql_fetch_tree( $inQueryString, $rootNode, $idField = "id", $parentField = "parent")
	{
		global $defaultdb;
		return $defaultdb->fetch_tree($inQueryString,$rootNode,$idField,$parentField);
	}

	//	inQuerystring can be a map (php array/hashtable), and then it will use the map instead of querying the database....
	//	This helps in making multiple calls when you need separate arrays for each parent node's children.
	//	Might be too much of a secret hack though - at least the var name should probably be changed

	function &sql_fetch_children( $inQueryString, $rootNode, $idField = "id", $parentField = "parent")
	{
		global $defaultdb;
		return $defaultdb->fetch_children($inQueryString,$rootNode,$idField,$parentField);
	}

	function &sql_fetch_parents($inQueryString, $leafNode, $idField = "id", $parentField = "parent")
	{
		global $defaultdb;
		return $defaultdb->fetch_parents($inQueryString,$leafNode,$idField,$parentField);
	}

	function &sql_prepare_tree_query($inQueryString, $idField = "id", $parentField = "parent")
	{
		global $defaultdb;
		return $defaultdb->prepare_tree_query($inQueryString, $idField, $parentField);
	}

	function &sql_better_fetch_tree( $inQueryString, $rootNode, $idField = "id", $parentField = "parent", $depth = -1)
	{
		global $defaultdb;
		return $defaultdb->better_fetch_tree($inQueryString,$rootNode,$idField,$parentField, $depth);
	}

	function &sql_better_fetch_children( $inQueryString, $rootNode, $idField = "id", $parentField = "parent")
	{
		global $defaultdb;
		return $defaultdb->better_fetch_children($inQueryString,$rootNode,$idField,$parentField);
	}

	function &sql_better_fetch_parents($inQueryString, $leafNode, $idField = "id", $parentField = "parent")
	{
		global $defaultdb;
		return $defaultdb->better_fetch_parents($inQueryString,$leafNode,$idField,$parentField);
	}

	function ticks($inString)
	{
		if($inString == NULL)
			return "NULL";
		else if($inString == "NULL")
			return $inString;
		else
		{
			return "'$inString'";
		}
	}

	function makeDate($Year, $Month = 1, $Day = 1)
	{
		if($Year == "")
		{
			$Date = "NULL";
		}
		else
		{
			if($Month == 0)
				$Month = 1;

			$Month = str_pad($Month, 2, "0", STR_PAD_LEFT);

			if($Day == 0)
				$Day = 1;

			$Day = str_pad($Day, 2, "0", STR_PAD_LEFT);

			$Date = "$Year-$Month-$Day";
		}

		return $Date;
	}

?>