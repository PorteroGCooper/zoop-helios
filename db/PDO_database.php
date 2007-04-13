<?
class database
{
	var $db = null;
	var $transaction = 0;
	function database($dsn)
	{
		global $globalTime;
		$this->dsn = $dsn;
		logprofile($globalTime, true);	
		try 
		{
			//echo("{$dsn['phptype']}: host={$dsn['hostspec']} port={$dsn['port']} dbname={$dsn['database']} user={$dsn['username']}" . (empty($dsn['password']) ? '' : " password={$dsn['password']}"));
			$this->db = new PDO("{$dsn['phptype']}: host={$dsn['hostspec']} port={$dsn['port']} dbname={$dsn['database']} user={$dsn['username']}" . (empty($dsn['password']) ? '' : " password={$dsn['password']}"));
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e)
		{
			$this->error($e);
		}
		logprofile($globalTime, "connect: {$dsn['phptype']}://{$dsn['hostspec']}:{$dsn['port']}/{$dsn['database']}");
		//log connection time
		//$this->db->setFetchMode(DB_FETCHMODE_ASSOC);
		//there are sometimes when this is a good thing, but mostly not.
		//makes it follow our order in explicit joins.
		//$this->db->query('set join_collapse_limit = 1');
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
		//echo($inQuery . "\n");
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
		if($this->transaction <= 0)
			$this->db->query("ROLLBACK");
	}
	
	function error($result = null)
	{
		while ($this->transaction)
		{
			sql_rollback_transaction();
		}
		//echo substr($inQueryString, 0, 1200) . "<br>" . 
		//echo_r($result);
		$error = $this->db->errorInfo();
		trigger_error($error[2]);
		die();
	}
	
	function trusted_query($inQueryString)
	{
		$result = $this->db->query($inQueryString);
		return $result;
	}

	function &query($inQueryString)
	{
		$this->verifyQuery($inQueryString);
		global $globalTime;
		logprofile($globalTime, true);
		try
		{
			$result = &$this->db->query($inQueryString);
		}
		catch(PDOException $e)
		{
			$this->error($e);
		}
		logprofile($globalTime, $inQueryString);
		return $result;
	}
	
	function &getOne($inQueryString)
	{
		$this->verifyQuery($inQueryString);
		global $globalTime;
		logprofile($globalTime, true);
		$result = &$this->db->getOne($inQueryString);
		logprofile($globalTime, $inQueryString);
		if(DB::isError($result))
		{
			$this->error($result);			
		}
		return $result;
	}
	
	function &getAll(&$inQueryString)
	{
		$this->verifyQuery($inQueryString);
		global $globalTime;
		logprofile($globalTime, true);
		try
		{
			$result = &$this->db->query($inQueryString);
		}
		catch(PDOException $e)
		{
			$this->error($e);
		}	
		logprofile($globalTime, $inQueryString);
		return $result;
	}
	
	function &getCol(&$query)
	{
		$this->verifyQuery($query);
		global $globalTime;
		logprofile($globalTime, true);
		$result = &$this->db->getCol($query);
		logprofile($globalTime, $query);
		if(DB::isError($result))
		{
			$this->error($result);			
		}
		return $result;
	}
	
	function &getAssoc($query)
	{
		$this->verifyQuery($query);
		global $globalTime;
		logprofile($globalTime, true);
		$result = &$this->db->getAssoc($query);
		logprofile($globalTime, $query);
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
		
	//	this should be done differently!!!!!!!!!!!
	function insert($query, $sequence = NULL)
	{
		$result = $this->query($query);
		if($this->dsn['phptype'] == 'pgsql')
		{
			if($sequence !== NULL)
			{
				return $this->db->lastInsertId($sequence);
			}
			else
			{
				$id = $this->db->lastInsertId();
				return $id;
			}
		}
		else
		{
			return $this->db->lastInsertId();
		}
	}

	function fetch_sequence( $sequence )
	{
		return $this->getOne("select nextval('\"$sequence\"'::text)");
	}

	///////////////////////////////////////////////
	//	Query returns true if rows are returned  //
	///////////////////////////////////////////////

	function check($query)
	{
		$result = $this->query($query);
		
		if($result->fetch() !== false)
		{
			//$result->closeCursor();
			return 0;
		}
		else
		{
			//$result->closeCursor();
			return 1;
		}
	}

	function fetch_into_arrays($query)
	{
		$result = &$this->getAll($query, array(), DB_FETCHMODE_ASSOC | DB_FETCHMODE_FLIPPED);
		return $result;
	}

	function fetch_into_arrobjs($query)
	{
		bug("this function deprecated, please use a different one...");
		$result = &$this->getAll($query);
		return $result;
	}

	function new_fetch_into_array($query)
	{
		return $this->fetch_column($query);
	}
	
	function fetch_column($query)
	{
		$result = &$this->getCol($query);
		return $result;
	}

	function fetch_into_array($inTableName, $inFieldName, $inExtra = "")
	{
		bug("please change this to a query and use fetch_column");
		$result = &$this->getCol("SELECT $inFieldName FROM $inTableName $inExtra");
		return $result;;
	}


	function fetch_one($inQueryString)
	{
		$result = &$this->query($inQueryString);
		if($result === false)
		{
			$this->error();
		}
		$result->setFetchMode(PDO::FETCH_ASSOC);
		$value = $result->fetch();
		if($value === false)
		{
			return false;
		}
		if($result->fetch() !== false)
		{
			$numRows = 2;
			foreach($result as $row)
			{
				$numRows++;
			}
			trigger_error(substr($inQueryString, 0, 150) . "<br>Only one result was expected. " . $numRows . " were returned.<br>");
		}
		//$result->closeCursor();
		return $value;
	}

	function fetch_assoc($inQuery)
	{
		$result = &$this->getAssoc($inQuery);
		return $result;
	}

	function &fetch_rows($inQuery, $inReturnObjects = 0)
	{
		$rows = array();
		if($inReturnObjects)
		{
			$rows = &$this->getAll($inQuery, array(), DB_FETCHMODE_OBJECT);
		}
		else
		{
			$rows = &$this->getAll($inQuery);
		}
		return $rows;
	}

	function &fetch_map($inQuery, $inKeyField)
	{	
		$rows = $this->getAll($inQuery);
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
		$rows = $this->getAll($inQuery);
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
		$rows = $this->getAll($inQuery);
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
		$result = $this->query($inQueryString);
		if($result === false)
		{
			$this->error();
		}
		//$result->setFetchMode(PDO::FETCH_NUM);
		$value = $result->fetchColumn($inField);
		if($value === false)
		{
			return false;
		}
		if($result->fetchColumn($inField) !== false)
		{
			$numRows = 2;
			foreach($result as $row)
			{
				$numRows++;
			}
			trigger_error(substr($inQueryString, 0, 150) . "<br>Only one result was expected. " . $numRows . " were returned.<br>");
		}
		return $value;
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
	
	function &better_fetch_tree( $inQueryString, $rootNode, $idField = "id", $parentField = "parent", $depth = -1)
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
				$tree[$node]['children'] = $this->__sql_better_append_children($node, $objects, $idField, $parentField, $depth);
			}
		}
		else
		{
			$tree['children'] = $this->__sql_better_append_children($rootNode, $objects, $idField, $parentField, $depth);
		}
		
		return $tree;		
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
					if(isset($objects[$parentField][$object[$idField]]))
						$children[$object[$idField]]['children'] = $this->__sql_better_append_children($object[$idField], $objects, $idField, $parentField, $depth - 1);
				}
			}
		}
		return $children;
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
		//markprofile();
		if(is_array($inQueryString))
		{
			$objects = $inQueryString;
		}
		else
		{
			$objects = $this->prepare_tree_query($inQueryString, $idField);
		}
		//markprofile();
		//markprofile();
		if(is_array($rootNode))
		{
			foreach($rootNode as $node)
			{
				$children[$node] = $objects[$idField][$node];
			}			
		}
		else
		{
			//	get the id of the root node and and set it to the data for the root node
			//	in our result object (children)
			$children[$rootNode] = $objects[$idField][$rootNode];
		}
		foreach($children as $id => $node)
		{
			$this->_fetch_children($children, $objects, $id, $idField, $parentField, $depth);
		} 		
		//markprofile();
		
		return $children;
	}
	
	function _fetch_children(&$children, &$objects, $id, $idField, $parentField, $depth = -1)
	{
		if(isset($objects[$parentField][$id]) && ($depth != 0))
		{
			foreach($objects[$parentField][$id] as $index => $node)
			{
				$children[$node[$idField]] = $node;
				$this->_fetch_children($children, $objects, $node[$idField],$idField, $parentField, $depth - 1);
			}
		}
	}
	
	function &fetch_parents($inQueryString, $leafNode, $idField = "id", $parentField = "parent")
	{
		//	get the set of rows that we are dealing with.  It should contain all of the rows that could possibly
		//	end up in the parent chain
		if(!is_array($inQueryString))
			$objects = $this->fetch_map($inQueryString, $idField);
		else
			$objects = $inQueryString['id'];
		
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
	
	function escape_string($string)
	{
		return $this->db->quote($string);
	}
}
