<?php
/**
 * @package db
 * @subpackage database
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
 * Database
 *
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @author John Lesusur
 * @author Rick Gigger
 * @author Richard Bateman
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class Database {
	/**
	 * db
	 *
	 * @var mixed
	 * @access public
	 */
	var $db = null;
	/**
	 * transaction
	 *
	 * @var float
	 * @access public
	 */
	var $transaction = 0;

	/**
	 * database
	 *
	 * @param string $dsn
	 * @access public
	 * @return void
	 */
	public function __construct($dsn) {
		$options = array(
			'debug' => 2
		);
		if (defined('db_persistent')) $options['persistent'] = db_persistent;
			
		if (!is_array($dsn)) $dsn = database::makeDSNFromString($dsn);
		$this->dsn = &$dsn;
		
		global $globalTime;
		logprofile($globalTime, true);
		
		$this->db = DB::connect($dsn, $options);
		logprofile($globalTime, "connect: {$dsn['phptype']}://{$dsn['hostspec']}:{$dsn['port']}/{$dsn['database']}");

		if (DB::isError($this->db)) {
			$this->error($this->db);
		}
		
		$this->db->setFetchMode(DB_FETCHMODE_ASSOC);
	}

	/**
	 * getDSN
	 *
	 * @access public
	 * @return string
	 */
	public function getDSN() {
		return $this->dsn;
	}

	/**
	 * verifyQuery
	 *
	 * @param string $inQuery
	 * @access public
	 * @return void
	 */
	public function verifyQuery($inQuery) {
		if (defined("verify_queries") && verify_queries) {
			$inQuote = 0;
			for($i = 0 ; $i < strlen($inQuery); $i++) {
				if (!$inQuote && $inQuery[$i] == ';') {
					trigger_error("this query had a ;, and is not safe...");
				} elseif ($inQuery[$i] == '\'') {
					if ($inQuote) {
						$inQuote = 0;
					} else {
						$inQuote = 1;
					}
				} else if ($inQuery[$i] == '\\') {
					$i++;
				}
			}
		}
	}

	/**
	 * makeDSN
	 *
	 * @param string $dbtype
	 * @param string $host
	 * @param string $port
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @access public
	 * @return void
	 */
	public function makeDSN($dbtype, $host, $port, $username, $password, $database) {
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
	
	/**
	 * Make a DSN config array from a string.
	 *
	 * @param string $dsn_string a string of the format mysql://username:password@localhost/dbname
	 * @access public
	 * @return void
	 */
	public function makeDSNFromString($dsn_string) {
		$default_ports = array(
			'mysql' => 3306,
			'pgsql' => 5432
		);
		
		$dsn = parse_url($dsn_string);
		
		// add default ports if one isn't specified.
		if (!isset($dsn['port'])) {
			$dsn['port'] = (isset($default_ports[$dsn['scheme']])) ? $default_ports[$dsn['scheme']] : null;
		}
		
		// strip leading slash if this is a mysql or pg database name
		if ($dsn['scheme'] != 'sqlite' && $dsn['path'][0] == '/') $dsn['path'] = substr($dsn['path'],1);
		
		return array(
			'phptype' => $dsn['scheme'],
			//'dbsyntax' => false,
			'username' => isset($dsn['user']) ? $dsn['user'] : null,
			'password' => isset($dsn['pass']) ? $dsn['pass'] : null,
			//'protocol' => false,
			'hostspec' => isset($dsn['host']) ? $dsn['host'] : null,
			'port'     => $dsn['port'],
			//'socket'   => false,
			'database' => $dsn['path'],
		);
	}

	/**
	 * begin_transaction
	 *
	 * @access public
	 * @return void
	 */
	public function begin_transaction() {
		if ($this->transaction == 0) $this->db->query('BEGIN');
		$this->transaction++;
	}

	/**
	 * commit_transaction
	 *
	 * @access public
	 * @return void
	 */
	public function commit_transaction() {
		$this->transaction--;
		if ($this->transaction == 0) $this->db->query('COMMIT');
	}

	/**
	 * rollback_transaction
	 *
	 * @access public
	 * @return void
	 */
	public function rollback_transaction() {
		$this->transaction--;
		if ($this->transaction == 0) $this->db->query('ROLLBACK');
	}

	/**
	 * error
	 *
	 * @param object $result
	 * @access public
	 * @return void
	 */
	public function error($result) {
		while ($this->transaction) {
			sql_rollback_transaction();
		}
		//echo substr($inQueryString, 0, 1200) . "<br>" .
		//echo_r($result);
		trigger_error("PearDB returned an error. The error was " . $result->getMessage());
		die();
	}

	/**
	 * trusted_query
	 *
	 * @param string
	 * @access public
	 * @return object
	 */	
	public function trusted_query($inQueryString) {
		$result = $this->db->query($inQueryString);
		return $result;
	}
	
	/**
	 * query
	 *
	 * @param mixed $inQueryString
	 * @access public
	 * @return object
	 */
	public function &query($inQueryString) {
		$this->verifyQuery($inQueryString);
		global $globalTime;
		logprofile($globalTime, true);
		$result = &$this->db->query($inQueryString);
		logprofile($globalTime, $inQueryString);
		if (DB::isError($result)) {
			$this->error($result);			
		}
		return $result;
	}
	
	public function &getOne($inQueryString) {
		$this->verifyQuery($inQueryString);
		global $globalTime;
		logprofile($globalTime, true);
		$result = &$this->db->getOne($inQueryString);
		logprofile($globalTime, $inQueryString);
		if (DB::isError($result)) {
			$this->error($result);			
		}
		return $result;
	}
	
	public function &getAll(&$inQueryString, $params = array(), $mode = DB_FETCHMODE_DEFAULT) {
		$this->verifyQuery($inQueryString);
		global $globalTime;
		logprofile($globalTime, true);
		$result = &$this->db->getAll($inQueryString, $params, $mode);
		logprofile($globalTime, $inQueryString);
		if (DB::isError($result)) {
			$this->error($result);			
		}
		return $result;
	}
	
	public function &getCol(&$query) {
		$this->verifyQuery($query);
		global $globalTime;
		logprofile($globalTime, true);
		$result = &$this->db->getCol($query);
		logprofile($globalTime, $query);
		if (DB::isError($result)) {
			$this->error($result);			
		}
		return $result;
	}
	
	public function &getAssoc($query) {
		$this->verifyQuery($query);
		global $globalTime;
		logprofile($globalTime, true);
		$result = &$this->db->getAssoc($query);
		logprofile($globalTime, $query);
		if (DB::isError($result)) {
			$this->error($result);			
		}
		return $result;
	}

	/**
	 * get_fields
	 *
	 * @param string $table
	 * @access public
	 * @return object
	 */
	public function get_fields($table) {
		return $this->db->tableInfo($table);
	}

	/**
	 * insert
	 *
	 * @param string $query
	 * @access public
	 * @return object
	 */
	public function insert($query) {
		return $this->query($query);
	}

	/**
	 * fetch_sequence
	 *
	 * @param string $sequence
	 * @access public
	 * @return object
	 */
	public function fetch_sequence($sequence) {
		return $this->getOne("select nextval('\"$sequence\"'::text)");
	}

	/**
	 * returns true if rows are returned
	 *
	 * @param string $query the query for the database
	 * @access public
	 * @return boolean
	 */
	public function check($query) {
		$result = $this->query($query);

		if ($result->numRows() < 1) {
			$result->free();
			return 0;
		} else {
			$result->free();
			return 1;
		}
	}

	/**
	 * fetch_into_arrays
	 *
	 * @param string $query
	 * @access public
	 * @return array
	 */
	public function fetch_into_arrays($query) {
		$result = $this->getAll($query, array(), DB_FETCHMODE_ASSOC | DB_FETCHMODE_FLIPPED);
		return $result;
	}

	/**
	 * fetch_into_arrobjs
	 *
	 * @param string $query
	 * @access public
	 * @return array ?
	 */
	public function fetch_into_arrobjs($query) {
		bug("this function deprecated, please use a different one...");
		$result = $this->getAll($query);
		return $result;
	}

	/**
	 * new_fetch_into_array
	 *
	 * @param string $query
	 * @access public
	 * @return array
	 */
	public function new_fetch_into_array($query) {
		return $this->fetch_column($query);
	}
	
	public function fetch_column($query) {
		die('hi');
		$result = &$this->getCol($query);
		return $result;
	}

	/**
	 * fetch_into_array
	 *
	 * @param string $inTableName
	 * @param string $inFieldName
	 * @param string $inExtra
	 * @access public
	 * @return object ?
	 */
	public function fetch_into_array($inTableName, $inFieldName, $inExtra = "") {
		bug("please change this to a query and use fetch_column");
		$result = &$this->getCol("SELECT $inFieldName FROM $inTableName $inExtra");
		return $result;
	}

	/**
	 * Use this function to get a record from the database. It will be returned as an array with the key as the fieldname and the value as the value.
	 *
	 * @param string $query the query for the database
	 * @access public
	 * @return associative array in the form [fieldname] => value;
	 */
	public function fetch_one($inQueryString) {
		$result = &$this->query($inQueryString);
		$numRows = $result->numRows();

		if ($numRows > 1) {
			trigger_error ( "Only one result was expected. " . $numRows . " were returned");
		} elseif ($numRows == 0) {
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
	 * @access public
	 * @return associative array in the form [primarykeyvalue][fieldname] => value;
	 */
	public function fetch_assoc($inQuery) {
		$result = $this->getAssoc($inQuery);
		return $result;
	}

	/**
	 * fetch_rows
	 *
	 * @param string $inQuery
	 * @param int $inReturnObjects
	 * @access public
	 * @return array
	 */
	public function &fetch_rows($inQuery, $inReturnObjects = 0) {
		$rows = array();
		if ($inReturnObjects) {
			$rows = &$this->getAll($inQuery, array(), DB_FETCHMODE_OBJECT);
		} else {
			$rows = &$this->getAll($inQuery);
		}
		return $rows;
	}

	/**
	 * &fetch_map
	 *
	 * @param string $inQuery
	 * @param string $inKeyField
	 * @access public
	 * @return array
	 */
	public function &fetch_map($inQuery, $inKeyField) {
		$rows = $this->getAll($inQuery);
		$results = array();
		foreach ($rows as $row) {
			if (is_array($inKeyField)) {
				$cur = &$results;

				foreach ($inKeyField as $val) {
					$curKey = $row[$val];

					if (!isset($cur[$curKey])) {
						$cur[$curKey] = array();
					}

					$cur = &$cur[ $curKey ];
				}
				if (count($cur)) {
					echo_r($results);
					trigger_error("duplicate key $curKey, would silently destroy data");
				}

				$cur = $row;
			} else {
				$mapKey = $row[$inKeyField];

				foreach ($row as $key => $val) {
					$results[$mapKey][$key] = $val;
				}
			}
		}
		return $results;
	}

	/**
	 * fetch_simple_map
	 *
	 * @param string $inQuery
	 * @param string $inKeyField
	 * @param string $inValueField
	 * @access public
	 * @return array
	 */
	public function fetch_simple_map($inQuery, $inKeyField, $inValueField) {
		$rows = $this->getAll($inQuery);
		$results = array();
		
		foreach ($rows as $row) {
		//while($row = sql_fetch_array($rows)) {
			$cur = &$results;
			if (is_array($inKeyField)) {
				foreach ($inKeyField as $key) {
					$cur = &$cur[$row[$key]];
					$lastKey = $row[$key];
				}
			} else {
				$cur = &$cur[$row[$inKeyField]];
				$lastKey = $row[$inKeyField];
			}
			if (isset($cur) && !empty($lastKey)) {
				trigger_error("duplicate key in query: \n $inQuery \n");
			}
			$cur = $row[ $inValueField ];
		}

		return $results;
	}

	/**
	 * &fetch_complex_map
	 *
	 * @param string $inQuery
	 * @param string $inKeyField
	 * @access public
	 * @return array
	 */
	public function &fetch_complex_map($inQuery, $inKeyField) {
		$rows = $this->getAll($inQuery);
		$results = array();

		//	loop through each row in the result set

		foreach ($rows as $row) {
			if ( gettype($inKeyField) == "array") {
				$cur = &$results;

				foreach ( $inKeyField as $val ) {
					$curKey = $row[$val];

					if (!isset($cur[$curKey])) {
						$cur[$curKey] = array();
					}

					$cur = &$cur[$curKey];
				}

				$cur[] = $row;
			} else {
				//	get the key for the result map
				$mapKey = $row[$inKeyField];

				$results[$mapKey][] = $row;
			}
		}

		return $results;
	}

	/**
	 * fetch_one_cell
	 *
	 * @param mixed $inQueryString
	 * @param int $inField
	 * @access public
	 * @return string
	 */
	public function fetch_one_cell($inQueryString, $inField = 0) {
		$result = $this->query($inQueryString, array(), DB_FETCHMODE_ORDERED);
		
		$numRows = $result->numRows();
		if ($numRows > 1) {
			trigger_error(substr($inQueryString, 0, 150) . "<br>Only one result was expected. " . $numRows . " were returned.<br>");
		} elseif ($numRows == 0) {
			$result->free();
			return(false);
		}

		$row = $result->fetchRow(DB_FETCHMODE_ORDERED);
		$result->free();
		if (!isset($row[$inField])) {
			$row[$inField] = null;
		}

		return $row[$inField];
	}

	/**
	 * &prepare_tree_query
	 *
	 * @param mixed $inQueryString
	 * @param string $idField
	 * @param string $parentField
	 * @access public
	 * @return array
	 */
	public function &prepare_tree_query($inQueryString, $idField = "id", $parentField = "parent") {
		$map = &$this->fetch_map($inQueryString, $idField);
		$complex = array();
		foreach ($map as $id => $obj) {
			$complex[$obj[$parentField]][] = &$map[$id];
		}
		$answer[$idField] = &$map;
		$answer[$parentField] = &$complex;
		return $answer;
	}

	/**
	 * &better_fetch_tree
	 *
	 * @param mixed $inQueryString
	 * @param mixed $rootNode
	 * @param string $idField
	 * @param string $parentField
	 * @param int $depth
	 * @access public
	 * @return array
	 */
	public function &better_fetch_tree($inQueryString, $rootNode, $idField = "id", $parentField = "parent", $depth = -1) {
		if (!is_array($inQueryString)) {
			//do your own complex mapping...
			//find the root nodes as you go...
			$objects = &$this->prepare_tree_query($inQueryString, $idField, $parentField);
		} else {
			//php5 clone this
			$objects = &$inQueryString;
		}
		if (is_array($rootNode) && in_array($object[$idField], $rootNode)) {
			foreach ($rootNode as $node) {
				$tree[$node] = $objects[$idField][$node];
			}
		} else {
			$tree = $objects[$idField][$rootNode];
		}

		if (is_array($rootNode)) {
			foreach ($rootNode as $node) {
				$tree[$node]['children'] = $this->__sql_better_append_children($node, $objects, $idField, $parentField, $depth);
			}
		} else {
			$tree['children'] = $this->__sql_better_append_children($rootNode, $objects, $idField, $parentField, $depth);
		}
		return $tree;
	}

	/**
	 * &fetch_tree
	 *
	 * @param mixed $inQueryString
	 * @param mixed $rootNode
	 * @param string $idField
	 * @param string $parentField
	 * @access public
	 * @return array
	 */
	public function &fetch_tree( $inQueryString, $rootNode, $idField = "id", $parentField = "parent") {
		if (is_array($inQueryString)) {
			$objects = $inQueryString;
		} else {
			$objects = $this->fetch_map($inQueryString, $idField);
		}
		if (is_array($rootNode)) {
			foreach ($rootNode as $node) {
				//php 5 need clone here
				$node = $objects[$node];
				$tree[] = $this->__sql_append_children($node, $objects, $idField, $parentField);
			}
		} else {
			//php 5 need clone here
			$rootNode = $objects[$rootNode];
			$tree = $this->__sql_append_children($rootNode, $objects, $idField, $parentField);
		}

		return $tree;
	}

	/**
	 * &__sql_append_children
	 *
	 * @param mixed $rootObject
	 * @param mixed $objects
	 * @param mixed $idField
	 * @param mixed $parentField
	 * @access public
	 * @return array  array of objects
	 */
	function &__sql_append_children(&$rootObject, $objects, $idField, $parentField) {
		foreach ($objects as $object) {
			if (isset($object[$parentField]) && $object[$parentField] == $rootObject[$idField]) {
				$rootObject["children"][$object[$idField]] = $object;
				$this->__sql_append_children($rootObject["children"][$object[$idField]], $objects, $idField, $parentField);
			}
		}

		return $rootObject;
	}

	/**
	 * &__sql_better_append_children
	 *
	 * @param mixed $rootObjectId
	 * @param mixed $objects
	 * @param mixed $idField
	 * @param mixed $parentField
	 * @param mixed $depth
	 * @access public
	 * @return array  array of objects
	 */
	function &__sql_better_append_children(&$rootObjectId, &$objects, $idField, $parentField, $depth = -1) {
		if ($depth != 0) {
			$children = array();
			if (isset($objects[$parentField][$rootObjectId])) {
				foreach ($objects[$parentField][$rootObjectId] as $object) {
					$children[$object[$idField]] = $object;
					if (isset($objects[$parentField][$object[$idField]]))
						$children[$object[$idField]]['children'] = $this->__sql_better_append_children($object[$idField], $objects, $idField, $parentField, $depth - 1);
				}
			}
		}
		return $children;
	}


	//	inQuerystring can be a map (php array/hashtable), and then it will use the map instead of querying the database....
	//	This helps in making multiple calls when you need separate arrays for each parent node's children.
	//	Might be too much of a secret hack though - at least the var name should probably be changed

	/**
	 * &fetch_children
	 *
	 * @param mixed $inQueryString
	 * @param mixed $rootNode
	 * @param string $idField
	 * @param string $parentField
	 * @access public
	 * @return void
	 */
	public function &fetch_children( $inQueryString, $rootNode, $idField = "id", $parentField = "parent") {
		//	get the set of rows that we are dealing with.  It shoudld contain all of the rows that could possibly
		//	end up as nodes in the tree
		if (is_array($inQueryString)) {
			$objects = $inQueryString;
		} else {
			//markprofile();
			$objects = $this->fetch_map($inQueryString, $idField);
			//markprofile();
		}
		//markprofile();
		if (is_array($rootNode)) {
			foreach ($rootNode as $node) {
				$children[$objects[$node][$idField]] = $objects[$node];
			}
		} else {
			//	get the id of the root node and and set it to the data for the root node
			//	in our result object (children)
			$children[$objects[$rootNode][$idField]] = $objects[$rootNode];
		}

		//fixed point algorithm....
		$done = false;
		while(!$done) {
			$done = true;
			foreach ($objects as $object) {
				//	if the db row has a parent and is not already in the tree
				if (isset($children[$object[$parentField]]) && !isset($children[$object[$idField]])) {
					$done = false;

					$children[$object[$idField]] = $object;
					$keys = array_keys($children[$object[$parentField]]);
					//fill in inherited properties from parents....
					//is this a good idea?
					//*
					foreach ($keys as $key) {
						if (!isset($children[$object[$idField]][$key])) {
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

	/**
	 * &better_fetch_children
	 *
	 * @param mixed $inQueryString
	 * @param mixed $rootNode
	 * @param string $idField
	 * @param string $parentField
	 * @param mixed $depth
	 * @access public
	 * @return void
	 */
	public function &better_fetch_children( $inQueryString, $rootNode, $idField = "id", $parentField = "parent", $depth = -1) {
		//	get the set of rows that we are dealing with.  It shoudld contain all of the rows that could possibly
		//	end up as nodes in the tree
		if (is_array($inQueryString)) {
			$objects = $inQueryString;
		} else {
			$objects = $this->prepare_tree_query($inQueryString, $idField);
		}

		if (is_array($rootNode)) {
			foreach ($rootNode as $node) {
				$children[$node] = $objects[$idField][$node];
			}
		} else {
			//	get the id of the root node and and set it to the data for the root node
			//	in our result object (children)
			$children[$rootNode] = $objects[$idField][$rootNode];
		}
		foreach ($children as $id => $node) {
			$this->_fetch_children($children, $objects, $id, $idField, $parentField, $depth);
		}
		//markprofile();
		//echo_r($children);
		return $children;
	}

	/**
	 * _fetch_children
	 *
	 * @param mixed $children
	 * @param mixed $objects
	 * @param mixed $id
	 * @param mixed $depth
	 * @access protected
	 * @return void
	 */
	function _fetch_children(&$children, &$objects, $id, $idField, $parentField, $depth = -1) {
		if (isset($objects[$parentField][$id]) && ($depth != 0)) {
			foreach ($objects[$parentField][$id] as $index => $node) {
				$children[$node[$idField]] = $node;
				$this->_fetch_children($children, $objects, $node[$idField], $idField, $parentField, $depth - 1);
			}
		}
	}

	/**
	 * &fetch_parents
	 *
	 * @param mixed $inQueryString
	 * @param mixed $leafNode
	 * @param string $idField
	 * @param string $parentField
	 * @access public
	 * @return void
	 */
	public function &fetch_parents($inQueryString, $leafNode, $idField = "id", $parentField = "parent") {
		//	get the set of rows that we are dealing with.  It should contain all of the rows that could possibly
		//	end up in the parent chain
		if (!is_array($inQueryString)) {
			$objects = $this->fetch_map($inQueryString, $idField);
		} else {
			$objects = $inQueryString['id'];
		}

		//	set up the first node, we will go up from here
		$parents[$leafNode] = $objects[$leafNode];

		//	walk up the tree to the root
		$nextParent = $objects[$leafNode][$parentField];
		while(isset($objects[$nextParent]) && $objects[$nextParent] != NULL && !isset($parents[$nextParent])) {
			$parents[$objects[$nextParent][$idField]] = $objects[$nextParent];
			$nextParent = $objects[$nextParent][$parentField];
		}
		return $parents;
	}

	/**
	 * get_table_info
	 *
	 * @param mixed $inTable
	 * @access public
	 * @return void
	 */
	function get_table_info($inTable) {
		$result = $this->db->tableinfo($inTable);

		if (db::isError($result)) {
			$this->error($result);
		}
		return $result;
	}

	/**
	 * escape_string
	 *
	 * @param string $inString
	 * @access public
	 * @return string
	 */
	public function escape_string($inString) {
		return $this->db->quoteSmart($inString);
	}

	/**
	 * escape_identifier
	 *
	 * @param string $inString
	 * @access public
	 * @return string
	 */
	public function escape_identifier($inString) {
		return $this->db->quoteIdentifier($inString);
	}

	/**
	 * escape_tablename
	 *
	 * @param string $inString
	 * @access public
	 * @return string 
	 */
	public function escape_tablename($inString) {
		$name = explode(".", $inString);
		foreach ($name as $part) {
			$newname[] = $this->db->quoteIdentifier($part);
		}
		return implode('.', $newname);
	}
}
