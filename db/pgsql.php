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

/*****************************************************************\
	pgsql.php

	Purpose:	Implement pgsql versions of the sql_whatever functions
\*****************************************************************/
//$defaultdsn = database::makeDSN(fwDB_RDBMS, fwDB_Server, fwDB_Port, fwDB_Username, fwDB_Password, fwDB_Database);
//$defaultdb = &new database($defaultdsn);
class framework_db extends framework
{
	function init()
	{
		$GLOBALS['defaultdb'] = &new database(database::makeDSN(db_RDBMS, db_Server, db_Port, db_Username, db_Password, db_Database));
	}
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

	function sql_insert($query, $sequence)
	{
		global $defaultdb;
		$result = $defaultdb->insert($query);
		return $defaultdb->fetch_one_cell("SELECT currval('\"$sequence\"'::text)");
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


	//	this is a stupid function name.  Please use sql_fetch_column below
	function sql_new_fetch_into_array($query)
	{
		return sql_fetch_column($query);
	}

	function sql_fetch_column($query)
	{
		global $defaultdb;
		//	the database clas should also create fetch_column and depricate
		//	new_fetch_into_array
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


	function &sql_fetch_complex_map($inQuery, $inKeyField)
	{
		global $defaultdb;
		return $defaultdb->fetch_complex_map($inQuery, $inKeyField);
	}


	function sql_fetch_one_cell($inQueryString, $inField = 0)
	{
		global $defaultdb;
		return $defaultdb->fetch_one_cell($inQueryString, $inField);
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

	function &sql_better_fetch_children( $inQueryString, $rootNode, $idField = "id", $parentField = "parent", $depth = -1)
	{
		global $defaultdb;
		return $defaultdb->better_fetch_children($inQueryString,$rootNode,$idField,$parentField);
	}

	function &sql_fetch_parents($inQueryString, $leafNode, $idField = "id", $parentField = "parent")
	{
		global $defaultdb;
		return $defaultdb->fetch_parents($inQueryString,$leafNode,$idField,$parentField);
	}

	function sql_get_table_info($Table)
	{
		global $defaultdb;
		return $defaultdb->get_table_info($Table);
	}

	function ticks($inString)
	{
		if($inString === NULL)
			return "NULL";
		else if($inString == "NULL")
			return $inString;
		else
		{
			return "'$inString'";
		}
	}

	function sql_escape_string($inString)
	{
		global $defaultdb;
		return $defaultdb->escape_string($inString);
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
