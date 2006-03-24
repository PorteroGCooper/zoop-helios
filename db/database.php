<?
/**
* @package db
* @subpackage database
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

class database
{
	var $db = null;
	var $transaction = 0;
	function database($dsn)
	{

		$options = array(
	    	'debug'       => 2
		);

		if (defined('db_persistent'))
			$options['persistent'] = db_persistent;

		$this->dsn = &$dsn;
		$this->db = DB::connect($dsn, $options);
		if(DB::isError($this->db))
		{
			$this->error($this->db);
		}
		$this->db->setFetchMode(DB_FETCHMODE_ASSOC);
	}

	function getDSN()
	{
		return $this->dsn;
	}

	function verifyQuery($inQuery)
	{
		if(defined("verify_queries") && verify_queries)
		{
			$inQuote = 0;
			for($i = 0 ; $i < strlen($inQuery); $i++)
			{
				if(!$inQuote && $inQuery[$i] == ';')
					trigger_error("this query had a ;, and is not safe...");
				else if($inQuery[$i] == '\'')
				{
					if($inQuote)
					{
						$inQuote = 0;
					}
					else
						$inQuote = 1;
				}
				else if($inQuery[$i] == '\\')
				{
					$i++;
				}
			}
		}
	}

	function makeDSN($dbtype, $host, $port, $username, $password, $database)
	{
		return array(
		    'phptype'  => $dbtype,
		    //'dbsyntax' => false,
		    'username' => $username,
		    'password' => $password,
		    //'protocol' => false,
		    'hostspec' => $host,
		    'port'     => $port,
		    //'socket'   => false,
		    'database' => $database,

	   );
	}

	function begin_transaction( )
	{
		if($this->transaction == 0)
			$this->db->query("BEGIN");
		$this->transaction++;
	}

	function commit_transaction( )
	{
		$this->transaction--;
		if($this->transaction == 0)
			$this->db->query("END");
	}

	function rollback_transaction( )
	{
		$this->transaction--;
		if($this->transaction == 0)
			$this->db->query("ROLLBACK");
	}

	function error($result)
	{
		while ($this->transaction)
		{
			sql_rollback_transaction();
		}
		//echo substr($inQueryString, 0, 1200) . "<br>" .
		//echo_r($result);
		trigger_error("PearDB returned an error. The error was " . $result->getMessage());
		die();
	}

	function query( $inQueryString, $Db = -1 )
	{
		$this->verifyQuery($inQueryString);
		$result = $this->db->query($inQueryString);
		if(DB::isError($result))
		{
			$this->error($result);
		}
		return $result;
	}

	function get_fields($table)
	{
		return $this->db->tableInfo($table);
	}

	function insert($query)
	{
		return $this->db->query($query);
	}

/**
* Take a Associated Array and Table name and insert the array's values into the database.
* Array should be in the format $arrayname['fieldname'] => value
*
* This function will escape <b>Everything</b> so please don't escape before hand.
*
* @param array $inArray the array to be inserted
* @param string $tablename the name of the table to insert the array into
*/
	function insert_array($inArray, $tablename)
	{
		foreach ($inArray as $field => $value)
		{
			$fields[] = $this->escape_identifier($field);
			$values[] = $this->escape_string($value);
		}

		$fieldstr = implode(",", $fields);
		$valuestr = implode(",", $values);
		$tablename = $this->escape_identifier($tablename);

		$query = "INSERT INTO $tablename ($fieldstr) VALUES ($valuestr)";

		return $this->insert($query);
	}

/**
* Take a Associated Array and Table name, and Primary Key name and Id and update the database with the values in the array.
* Array should be in the format $arrayname['fieldname'] => value
*
* This function will escape <b>Everything</b> so please don't escape before hand.
*
* @param array $inArray the array to be inserted
* @param string $tablename the name of the table to insert the array into
* @param string $primarykey the name of the primary key field
* @param string $id of the primary key
*/
	function update_array($inArray, $tablename, $primarykey, $primarykeyvalue)
	{
		foreach ($inArray as $field => $value)
		{
			$updateStr = "";
			$updateStr .= $this->escape_identifier($field);
			$updateStr .= "=";
			$updateStr .= $this->escape_string($value);

			$updateArray[] = $updateStr;
		}

		$newupdateStr = implode(",", $updateArray);
		$tablename = $this->escape_identifier($tablename);
		$primarykey = $this->escape_identifier($primarykey);
		$primarykeyvalue = $this->escape_string($primarykeyvalue);

		$query = "UPDATE $tablename SET $newupdateStr WHERE $primarykey = $primarykeyvalue";

 		return $this->query($query);
	}

	function fetch_sequence( $sequence )
	{
		return $this->db->getOne("select nextval('\"$sequence\"'::text)");
	}

/**
* returns true if rows are returned
*
* @param string $query the query for the database
* @return boolean
*/
	function check($query)
	{
		$result = $this->db->query($query);

		if(DB::isError($result))
		{
			$this->error($result);
		}

		if($result->numRows() < 1)
		{
			$result->free();
			return 0;
		}
		else
		{
			$result->free();
			return 1;
		}
	}

	function fetch_into_arrays($query)
	{
		$result = $this->db->getAll($query, array(), DB_FETCHMODE_ASSOC | DB_FETCHMODE_FLIPPED);
		if(DB::isError($result))
		{
			$this->error($result);
		}
		return $result;
	}

	function fetch_into_arrobjs($query)
	{
		$this->verifyQuery($query);
		bug("this function deprecated, please use a different one...");
		$result = $this->db->getAll($query);
		if(DB::isError($result))
		{
			$this->error($result);
		}

		return $result;
	}

	function new_fetch_into_array($query)
	{
		$this->verifyQuery($query);
		$result = $this->db->getCol($query);
		if(DB::isError($result))
		{
			$this->error($result);
		}
		return $result;
	}

	function fetch_into_array($inTableName, $inFieldName, $inExtra = "")
	{
		bug("please change this to a query and use new_fetch_into_array");
		$result = $this->db->getCol("SELECT $inFieldName FROM $inTableName $inExtra");
		if(DB::isError($result))
		{
			$this->error($result);
		}
		return $result;;
	}

/**
* Use this function to get a record from the database. It will be returned as an array with the key as the fieldname and the value as the value.
*
* @param string $query the query for the database
* @return associative array in the form [fieldname] => value;
*/
	function fetch_one($inQueryString)
	{
		$result = $this->db->query($inQueryString);
		if(DB::isError($result))
		{
			$this->error($result);
		}

		$numRows = $result->numRows();

		if($numRows > 1)
		{
			trigger_error ( "Only one result was expected. " . $numRows . " were returned");
		}
		else if($numRows == 0)
		{
			return(false);
		}

		$row = $result->fetchRow();
		$result->free();

		return $row;
	}
/**
* Use this function to get a record, or multiple records from the database.
* It will be returned as a two dimensional array. The first dimension will be an array with the key being the value of the primary key in each record.
* The second dimension would be identical to that returned from fetch_one but without the primary key.
*
* @param string $query the query for the database
* @return associative array in the form [primarykeyvalue][fieldname] => value;
*/
	function fetch_assoc($inQuery)
	{
		$this->verifyQuery($inQuery);
		$result = $this->db->getAssoc($inQuery);
		if(DB::isError($result))
		{
			$this->error($result);
		}

		return $result;
	}

	function fetch_rows($inQuery, $inReturnObjects = 0)
	{
		$this->verifyQuery($inQuery);
		$rows = array();
		if($inReturnObjects)
		{
			$rows = $this->db->getAll($inQuery, array(), DB_FETCHMODE_OBJECT);
		}
		else
		{
			$rows = $this->db->getAll($inQuery);
		}
		if(DB::isError($rows))
		{
			$this->error($rows);
		}
		return $rows;
	}

	function &fetch_map($inQuery, $inKeyField)
	{
		$this->verifyQuery($inQuery);
		$rows = $this->db->getAll($inQuery);
		if(DB::isError($rows))
		{
			$this->error($rows);
		}
		$results = array();

		foreach($rows as $row)
		{
			if( is_array($inKeyField))
			{
				$cur = &$results;

				foreach( $inKeyField as $val )
				{
					$curKey = $row[ $val ];

					if( !isset( $cur[ $curKey ] ) )
					{
						$cur[ $curKey ] = array();
					}

					$cur = &$cur[ $curKey ];
				}
				if(count($cur))
				{
					echo_r($results);
					trigger_error("duplicate key $curKey, would silently destroy data");
				}

				$cur = $row;
			}
			else
			{
				$mapKey = $row[ $inKeyField ];

				foreach($row as $key => $val)
				{
					$results[$mapKey][$key] = $val;
				}
			}
		}
		return $results;
	}


	function fetch_simple_map($inQuery, $inKeyField, $inValueField)
	{
		$this->verifyQuery($inQuery);
		$rows = $this->db->getAll($inQuery);
		if(DB::isError($rows))
		{
			$this->error($rows);
		}
		$results = array();

		foreach($rows as $row)
		//while($row = sql_fetch_array($rows))
		{
			$cur = &$results;
			if(is_array($inKeyField))
			{
				foreach($inKeyField as $key)
				{
					$cur = &$cur[$row[$key]];
					$lastKey = $row[$key];
				}
			}
			else
			{
				$cur = &$cur[$row[$inKeyField]];
				$lastKey = $row[$inKeyField];
			}
			if(isset($cur) && !empty($lastKey))
			{
				trigger_error("duplicate key in query: \n $inQuery \n");
			}
			$cur = $row[ $inValueField ];
		}

		return $results;
	}


	function &fetch_complex_map($inQuery, $inKeyField)
	{
		$this->verifyQuery($inQuery);
		$rows = $this->db->getAll($inQuery);
		if(DB::isError($rows))
		{
			$this->error($rows);
		}
		$results = array();

		//	loop through each row in the result set

		foreach($rows as $row)
		{
			if( gettype($inKeyField) == "array")
			{
				$cur = &$results;

				foreach( $inKeyField as $val )
				{
					$curKey = $row[ $val ];

					if( !isset( $cur[ $curKey ] ) )
					{
						$cur[ $curKey ] = array();
					}

					$cur = &$cur[ $curKey ];
				}

				$cur[] = $row;
			}
			else
			{
				//	get the key for the result map
				$mapKey = $row[ $inKeyField ];

				$results[$mapKey][] = $row;
			}
		}

		return $results;
	}


	function fetch_one_cell($inQueryString, $inField = 0)
	{
		$result = $this->db->query($inQueryString, array(), DB_FETCHMODE_ORDERED);
		if(DB::isError($result))
		{
			$this->error($result);
		}

		$numRows = $result->numRows();
		if($numRows > 1)
		{
			trigger_error(substr($inQueryString, 0, 150) . "<br>Only one result was expected. " . $numRows . " were returned.<br>");
		}
		else if($numRows == 0)
		{
			$result->free();
			return(false);
		}

		$row = $result->fetchRow(DB_FETCHMODE_ORDERED);
		$result->free();
		if (!isset($row[$inField]))
		{
			$row[$inField] = null;
		}

		return $row[$inField];
	}

	function &prepare_tree_query($inQueryString, $idField = "id", $parentField = "parent")
	{
		$map = &$this->fetch_map($inQueryString, $idField);
		$complex = array();
		foreach($map as $id => $obj)
		{
			$complex[$obj[$parentField]][] = &$map[$id];
		}
		$answer[$idField] = &$map;
		$answer[$parentField] = &$complex;
		return $answer;
	}

	function &better_fetch_tree( &$inQueryString, $rootNode, $idField = "id", $parentField = "parent")
	{
		if(!is_array($inQueryString))
		{
			//do your own complex mapping...
			//find the root nodes as you go...
			$objects = &$this->prepare_tree_query($inQueryString, $idField, $parentField);
		}
		else
		{
			//php5 clone this
			$objects = &$inQueryString;
		}
		if(is_array($rootNode) && in_array($object[$idField], $rootNode))
		{
			foreach($rootNode as $node)
			{
				$tree[$node] = $objects[$idField][$node];
			}
		}
		else
		{
			$tree = $objects[$idField][$rootNode];
		}

		if(is_array($rootNode))
		{
			foreach($rootNode as $node)
			{
				$tree[$node]['children'] = $this->__sql_better_append_children($node, $objects, $idField, $parentField);
			}
		}
		else
		{
			$tree['children'] = $this->__sql_better_append_children($rootNode, $objects, $idField, $parentField);
		}

		return $tree;
	}

	function &fetch_tree( $inQueryString, $rootNode, $idField = "id", $parentField = "parent")
	{
		if(is_array($inQueryString))
		{
			$objects = $inQueryString;
		}
		else
		{
			$objects = $this->fetch_map($inQueryString, $idField);
		}
		if(is_array($rootNode))
		{
			foreach($rootNode as $node)
			{
				//php 5 need clone here
				$node = $objects[$node];
				$tree[] = $this->__sql_append_children($node, $objects, $idField, $parentField);
			}
		}
		else
		{
			//php 5 need clone here
			$rootNode = $objects[$rootNode];
			$tree = $this->__sql_append_children($rootNode, $objects, $idField, $parentField);
		}

		return $tree;
	}

	function &__sql_append_children(&$rootObject, $objects, $idField, $parentField)
	{
		foreach($objects as $object)
		{
			if(isset($object[$parentField]) && $object[$parentField] == $rootObject[$idField])
			{
				$rootObject["children"][$object[$idField]] = $object;
				$this->__sql_append_children($rootObject["children"][$object[$idField]], $objects, $idField, $parentField);
			}

		}

		return $rootObject;
	}

	function &__sql_better_append_children(&$rootObjectId, &$objects, $idField, $parentField, $depth = -1)
	{
		if($depth != 0)
		{
			$children = array();
			if(isset($objects[$parentField][$rootObjectId]))
			{
				foreach($objects[$parentField][$rootObjectId] as $object)
				{
					$children[$object[$idField]] = $object;
					$children[$object[$idField]]['children'] = $this->__sql_better_append_children($object[$idField], $objects, $idField, $parentField, $depth - 1);
				}
			}
		}
		return $children;
	}


	//	inQuerystring can be a map (php array/hashtable), and then it will use the map instead of querying the database....
	//	This helps in making multiple calls when you need separate arrays for each parent node's children.
	//	Might be too much of a secret hack though - at least the var name should probably be changed

	function &fetch_children( $inQueryString, $rootNode, $idField = "id", $parentField = "parent")
	{
		//	get the set of rows that we are dealing with.  It shoudld contain all of the rows that could possibly
		//	end up as nodes in the tree
		if(is_array($inQueryString))
		{
			$objects = $inQueryString;
		}
		else
		{
			//markprofile();
			$objects = $this->fetch_map($inQueryString, $idField);
			//markprofile();
		}
		//markprofile();
		if(is_array($rootNode))
		{
			foreach($rootNode as $node)
			{
				$children[$objects[$node][$idField]] = $objects[$node];
			}
		}
		else
		{
			//	get the id of the root node and and set it to the data for the root node
			//	in our result object (children)
			$children[$objects[$rootNode][$idField]] = $objects[$rootNode];
		}

		//fixed point algorithm....
		$done = false;
		while(!$done)
		{
			$done = true;
			foreach($objects as $object)
			{
				//	if the db row has a parent and is not already in the tree
				if(isset($children[$object[$parentField]]) && !isset($children[$object[$idField]]))
				{
					$done = false;

					$children[$object[$idField]] = $object;
					$keys = array_keys($children[$object[$parentField]]);
					//fill in inherited properties from parents....
					//is this a good idea?
					//*
					foreach($keys as $key)
					{
						if(!isset($children[$object[$idField]][$key]))
						{
							$children[$object[$idField]][$key] = $children[$object[$parentField]][$key];
						}
					}
					//*/
				}
			}
		}
		//markprofile();
		return $children;
	}

	//	inQuerystring can be a map (php array/hashtable), and then it will use the map instead of querying the database....
	//	This helps in making multiple calls when you need separate arrays for each parent node's children.
	//	Might be too much of a secret hack though - at least the var name should probably be changed

	function &better_fetch_children( $inQueryString, $rootNode, $idField = "id", $parentField = "parent", $depth = -1)
	{
		//	get the set of rows that we are dealing with.  It shoudld contain all of the rows that could possibly
		//	end up as nodes in the tree
		if(is_array($inQueryString))
		{
			$objects = $inQueryString;
		}
		else
		{
			$objects = $this->prepare_tree_query($inQueryString, $idField);
		}

		if(is_array($rootNode))
		{
			foreach($rootNode as $node)
			{
				$children[$node] = $objects['id'][$node];
			}
		}
		else
		{
			//	get the id of the root node and and set it to the data for the root node
			//	in our result object (children)
			$children[$rootNode] = $objects['id'][$rootNode];
		}
		foreach($children as $id => $node)
		{
			$this->_fetch_children($children, $objects, $id, $depth);
		}
		//markprofile();
		//echo_r($children);
		return $children;
	}

	function _fetch_children(&$children, &$objects, $id, $depth = -1)
	{
		if(isset($objects['parent'][$id]) && ($depth != 0))
		{
			foreach($objects['parent'][$id] as $index => $node)
			{
				$children[$node['id']] = $node;
				$this->_fetch_children($children, $objects, $node['id'], $depth - 1);
			}
		}
	}

	function &fetch_parents($inQueryString, $leafNode, $idField = "id", $parentField = "parent")
	{
		//	get the set of rows that we are dealing with.  It should contain all of the rows that could possibly
		//	end up in the parent chain
		$objects = $this->fetch_map($inQueryString, $idField);

		//	set up the first node, we will go up from here
		$parents[$leafNode] = $objects[$leafNode];

		//	walk up the tree to the root
		$nextParent = $objects[$leafNode][$parentField];
		while(isset($objects[$nextParent]) && $objects[$nextParent] != NULL && !isset($parents[$nextParent]))
		{
			$parents[$objects[$nextParent][$idField]] = $objects[$nextParent];
			$nextParent = $objects[$nextParent][$parentField];
		}
		return $parents;
	}

	function get_table_info($inTable)
	{
		$result = $this->db->tableinfo($inTable);

		if(DB::isError($result))
		{
			$this->error($result);
		}
		return $result;
	}

	function escape_string($inString)
	{
		return $this->db->quoteSmart($inString);
	}

	function escape_identifier($inString)
	{
		return $this->db->quoteIdentifier($inString);
	}
}
?>
