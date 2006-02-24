<?
/**
* mssql sql_* functions
*
* This file is out of sync with the rest. Please copy from pgsql.php, and
* modify this file as necessary. Sorry, none of us use mssql to test it.
* @package db
* @subpackage mssql
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

/*****************************************************************\
	mssql.php

	Purpose:	Impliment mysql versions of the sql_whatever functions
\*****************************************************************/



if (isset($HTTP_POST_VARS))
{
    reset ($HTTP_POST_VARS);

    while (list($key, $value) = each ($HTTP_POST_VARS)) {
        $HTTP_POST_VARS[$key] = stripslashes( $HTTP_POST_VARS[$key] );

		$$key = stripslashes($$key);
    }


    ini_alter("magic_quotes_sybase",1);

	reset ($HTTP_POST_VARS);

    while (list($key, $value) = each ($HTTP_POST_VARS)) {
        $HTTP_POST_VARS[$key] = addslashes( $HTTP_POST_VARS[$key] );

		$$key = addslashes($$key);
    }
}


if (fwDB_Sessions == true) include("db/session_mssql.php");



$DbServer = fwDB_Server;
$DbUsername = fwDB_Username;
$DbPassword = fwDB_Password;
$DbDatabase = fwDB_Database;


$Db_Link = sql_pconnect( $DbServer, $DbUsername, $DbPassword );

if ( $tmp = sql_error() )
{
	LogNow("SQL Error on pconnect: $tmp");
	echo $tmp;
}

sql_select_db($DbDatabase, $Db_Link);

	function sql_query( $inQueryString, $onerr = -1, $Db = -1 )
	{
		global $Db_Link;
		if ($Db == -1) $Db = $Db_Link;

		if (DEBUG == true)
		{
			LogNow("SQL query: $inQueryString");
		}

		$result = mssql_query( $inQueryString, $Db );
		$err = sql_error($Db);

		if (DEBUG == true)
		{
			$tmpmsg = mssql_get_last_message();
			LogNow("SQL query return message: $tmpmsg");
		}

		if($err == "")
		{
			return $result;
		}
		else
		{
			if ($onerr != -1)
			{
				sql_query($onerr);
				return false;
			}
			else
			{
    			//die($err);
				LogNow("SQL query Error:  $err");
    			die(substr($inQueryString, 0, 150) . "<br>" . $err);
			}
		}
	}

	function sql_error( $Db = -1 )
	{
		global $Db_Link;
		if ($Db == -1) $Db = $Db_Link;

		return( mssql_error($Db) );
	}

	function sql_num_rows( $resultset )
	{
		global $Db_Link;

		return( mssql_num_rows($resultset) );
	}

	function sql_fetch_array( $resultset )
	{
		return( mssql_fetch_array( $resultset ) );
	}

	function sql_pconnect( $server, $username = "", $password = "" )
	{
		if ($username && $password)
		{
			return mssql_pconnect( $server, $username, $password );
		}
		elseif ($username)
		{
			return mssql_pconnect( $server, $username );
		}
		else
		{
			return mssql_pconnect( $server );
		}
	}

	function sql_insert_id( $link = "" )
	{
		if (!$link)
		{
			global $Db_Link;
			$link = $Db_Link;
		}

		return mssql_insert_id( $link );
	}

	function sql_select_db( $db, $link = "" )
	{
		if (!$link)
		{
			global $Db_Link;
			$link = $Db_Link;
		}

		mssql_select_db( $db, $link );
	}

	///////////////////////////////////////////////
	//	Query returns true if rows are returned  //
	///////////////////////////////////////////////

	function sql_check($query, $onerr = -1, $db = -1)
	{
		$result = sql_query($query, $onerr, $db);

		$err = sql_error();

		if ($err == "")
		{
			if (sql_num_rows($result) < 1)
			{
				return(false);
			}
			else
			{
				return(1);
			}
		}
		else
		{
			echo 3;
			$Error = 1;
			$ErrorMessage = $query . "<br>\n" . $err;
			echo $ErrorMessage;
			return(false);
		}
	}

	function sql_fetch_into_arrays($query)
	{
		global $db_fetch_array;
		global $db_num_rows;

		$rows = sql_query($query);

		$result = NULL;

		//	fill in the result arrays
		for($i = 0; $row = sql_fetch_array($rows); $i++)
		{
			reset($row);
			while(list($key, $val) = each($row))
			{
				$fieldName = trim($key);
				if (isset($row[$fieldName]))
				{
					$result[$fieldName][$i] = $row[$fieldName];
				}
				else
				{
					$result[$fieldName][$i] = false;
				}
			}
		}

		return $result;
	}

	function sql_fetch_into_array($query)
	{
		$rows = sql_query($query);

		$results = array();

		while($row = $db_fetch_array($rows))
		{
			$results[] = $row[0];
		}

		return $results;
	}


	function sql_fetch_one($inQueryString)
	{
		$result = sql_query($inQueryString);
		$err = sql_error();

		if($err == "")
		{
			$numRows = sql_num_rows($result);
			if($numRows > 1)
			{
				die(substr($inQueryString, 0, 150) . "<br>Only one result was expected. " . $numRows . " were returned.<br>");
			}

			if($numRows == 0)
			{
				return(false);
			}

			return sql_fetch_array($result);
		}
		else
			die(substr($inQueryString, 0, 150) . "<br>" . $err);
	}

	function sql_fetch_one_cell($inQueryString, $inField = 0)
	{
		$result = sql_query($inQueryString);
		$err = sql_error();

		if($err == "")
		{

			$numRows = sql_num_rows($result);


			if($numRows > 1)
			{
				die(substr($inQueryString, 0, 150) . "<br>Only one result was expected. " . $numRows . " were returned.<br>");
			}

			if($numRows == 0)
			{
				return(false);
			}

			$result = sql_fetch_array($result);

			if (!isset($result[$inField]))
			{
				$result[$inField] = "";
			}

			return $result[$inField];
		}
		else
			die(substr($inQueryString, 0, 150) . "<br>" . $err);
	}


	function sql_fetch_map($inQuery, $inKeyField, $inValueField)
	{

		$rows = sql_query($inQuery);

		$results = array();

		while($row = sql_fetch_array($rows))
		{
			$key = $row[ $inKeyField ];
			$results[ $key ] = $row[$inValueField];
		}

		return $results;
	}
?>
<?

	function mssql_error()
	{
		$rs = mssql_query("select @@ERROR as ErrorNo");
		//echo "error?: $rs<BR>";

		$rs2 = mssql_fetch_array( $rs );

		if (!isset($rs2["ErrorNo"]) || $rs2["ErrorNo"] > 0)
		{
			mssql_free_result( $rs );
			return mssql_get_last_message();
		}
		else
		{
			return(false);
		}
	}

	function mssql_insert_id( $link = "" )
	{
		assert(false);
		include("includes/sysenv.php");

		$rs = $db_query("select @@IDENTITY as ID");

		//echo "Insert id?: $rs<BR>";

		$rs2 = @$db_fetch_array( $rs );

		return( $rs2["ID"] );
	}
?>