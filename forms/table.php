<?php
/**
* @category zoop
* @package forms
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
 * table
 *
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class table
{
	/**
	 * idfield
	 *
	 * @var mixed
	 * @access public
	 */
	var $idfield;
	/**
	 * name
	 *
	 * @var mixed
	 * @access public
	 */
	var $name;
	/**
	 * dbconnname
	 *
	 * @var mixed
	 * @access public
	 */
	var $dbconnname;
	/**
	 * sequence
	 *
	 * @var mixed
	 * @access public
	 */
	var $sequence;
	/**
	 * listlink
	 *
	 * @var string
	 * @access public
	 */
	var $listlink = "edit";
	/**
	 * zone
	 *
	 * @var string
	 * @access public
	 */
	var $zone = "default";
	/**
	 * id_location
	 *
	 * @var string
	 * @access public
	 */
	var $id_location = "page"; // can be page or zone
	/**
	 * fields
	 *
	 * @var mixed
	 * @access public
	 */
	var $fields;
	/**
	 * records
	 *
	 * @var mixed
	 * @access public
	 */
	var $records;
	/**
	 * limit
	 *
	 * @var float
	 * @access public
	 */
	var $limit = 25; # LIMIT OF -1 GIVES YOU ALL THE RECORDS
	/**
	 * cur
	 *
	 * @var float
	 * @access public
	 */
	var $cur = 0;
	/**
	 * total
	 *
	 * @var mixed
	 * @access public
	 */
	var $total;
	/**
	 * pages
	 *
	 * @var mixed
	 * @access public
	 */
	var $pages;
	/**
	 * sort
	 *
	 * @var mixed
	 * @access public
	 */
	var $sort;
	/**
	 * direction
	 *
	 * @var string
	 * @access public
	 */
	var $direction = "ASC";
	/**
	 * deleteColumn
	 *
	 * @var float
	 * @access public
	 */
	var $deleteColumn = 0;
	/**
	 * deletelink
	 *
	 * @var string
	 * @access public
	 */
	var $deletelink = "delete";
	/**
	 * deletefield
	 *
	 * @var string
	 * @access public
	 */
	var $deletefield = "deleted";
	/**
	 * wherestr
	 *
	 * @var string
	 * @access public
	 */
	var $wherestr = "";
	/**
	 * order
	 *
	 * @var array
	 * @access public
	 */
	var $order = array();
	/**
	 * search
	 *
	 * @var string
	 * @access public
	 */
	var $search = array("type" => "all", "value" => null, "field" => null, "wherestr" => null);
	/**
	 * sql
	 *
	 * @var string
	 * @access public
	 */
	var $sql = "";
		// type can be individual or all or advanced

	/**
	 * table
	 *
	 * @param mixed $table
	 * @param mixed $dbconnname
	 * @access public
	 * @return void
	 */
	function table($table, $dbconnname)
	{
		$this->name = $table;
		$this->dbconnname = $dbconnname;

		global $$dbconnname;

		$dbname = $$dbconnname->dsn['database'];

		if (APP_STATUS == 'live' && $tablecache = zcache::getData($table, array('base'=> 'forms/processed_table_info/', 'group' => $dbname))) 
		{
			$this->fields = $tablecache->fields;
			$this->idfield = $tablecache->idfield;
			$this->sequence = $tablecache->sequence;
			$this->order = $tablecache->order;
		}
		else
		{
			$result = $$dbconnname->get_table_info($table);
			foreach($result as $field)
			{
				$fieldname = $field["name"];
				$this->fields[$fieldname] = new field($field, $dbconnname);

				if (strstr($field["flags"], "primary_key"))
				{
					$this->idfield = $field["name"];
					$this->fields[$fieldname]->formshow = 0;
					$this->fields[$fieldname]->listshow = 1;
								$this->fields[$fieldname]->clickable = 1;
				}
				//the first is for more modern postgres. the second for older postgres...
				if (preg_match('/.*?default_nextval%28(.*?)%29\\s.*/i', $field["flags"], $regs) || 
					preg_match('/.*?default_nextval.*?public.(.*?)%29\\s.*/i', $field["flags"], $regs))
				{
					$this->sequence = $regs[1];
				}

				if (strstr($field["flags"], "not_null"))
					$this->fields[$fieldname]->required = true;

				$this->fields[$fieldname]->length = $field["len"];
				$this->fields[$fieldname]->label = ucwords(strtolower(strtr($this->fields[$fieldname]->name,"_", " ")));

				switch(true)
				{
					case strstr($field["type"], "int"):
						$this->fields[$fieldname]->datatype = "numeric";
						$this->fields[$fieldname]->html['type'] = "text";
						$this->fields[$fieldname]->validation['type'] = "numeric";
						$this->fields[$fieldname]->search["type"] = "exact";
						break;
					case strstr($field["type"], "string"):
						$this->fields[$fieldname]->datatype = "text";
						$this->fields[$fieldname]->html['type'] = "text";
						$this->fields[$fieldname]->search["type"] = "contains";
						break;
					case strstr($field["type"], "blob"):
						$this->fields[$fieldname]->datatype = "text";
						$this->fields[$fieldname]->html['type'] = "text";
						$this->fields[$fieldname]->search["type"] = "contains";
						break;
					case strstr($field["type"], "date"):
						$this->fields[$fieldname]->datatype = "date";
						$this->fields[$fieldname]->html['type'] = "date";
						$this->fields[$fieldname]->search["type"] = "range";
						break;
				}

				$this->order[] = $fieldname;
			}

			$tmpobj->fields = &$this->fields;
			$tmpobj->idfield = &$this->idfield;
			$tmpobj->sequence = &$this->sequence;
			$tmpobj->order = &$this->order;

			zcache::cacheData($table, $tmpobj, array('base'=> 'forms/processed_table_info/', 'group' => $dbname)); 
		}
	}

	/**
	 * searchClear
	 *
	 * @access public
	 * @return void
	 */
	function searchClear()
	{
		unset($this->search["value"]);
		unset($this->search["field"]);
		unset($this->search["wherestr"]);

		$this->setAll("search['value']", null);
		$this->setAll("search['value_min']", null);
		$this->setAll("search['value_max']", null);
	}

	/**
	 * setDbconnname
	 *
	 * @param mixed $dbconnname
	 * @access public
	 * @return void
	 */
	function setDbconnname($dbconnname)
	{
		$this->dbconnname = $dbconnname;
	}

	/**
	 * setAll
	 *
	 * @param mixed $param
	 * @param mixed $val
	 * @access public
	 * @return void
	 */
	function setAll($param, $val)
	{
		if (isset($this->fields))
		{
			foreach ($this->fields as $field)
			{
			if (isset($this->fields[$field->name]->$param))
				$this->fields[$field->name]->$param = $val;
			}
		}
	}

	/**
	 * getTotal
	 *
	 * @access public
	 * @return void
	 */
	function getTotal()
	{
		$dbconnname = $this->dbconnname;
		global $$dbconnname;

		if (!$this->wherestr)
			$this->setupRequirements();
		$this->total = $$dbconnname->fetch_one_cell("SELECT COUNT(*) FROM $this->name $this->wherestr");
		if (is_numeric($this->limit))
			$this->pages = ceil($this->total / $this->limit);
		else
			$this->pages = 0;
	}

	/**
	 * setupEnv
	 *
	 * @param mixed $get
	 * @access public
	 * @return void
	 */
	function setupEnv($get)
	{
		if (isset($get["sort"]))
		{
			$this->sort = $get["sort"];
			if (!isset($this->fields[$this->sort]))
				$this->sort = $this->idfield;
		}
		else
		{
			if (!isset($this->fields[$this->sort]))
				$this->sort = $this->idfield;
		}

		if (isset($get["dir"]))
		{
			if ($get["dir"] == "ASC" || $get["dir"] == "DESC")
				$this->direction = $get["dir"];
			else
				$this->direction = "ASC";
		}
		if (isset($get["start"]))
		{
			if (!isset($this->total))
				$this->getTotal();

			if ($get["start"] > -1)
			{
				if ($this->total > $get["start"])
					$this->cur = $get["start"];
			}
		}
	}

	/**
	 * setupRequirements
	 *
	 * @access public
	 * @return void
	 */
	function setupRequirements()
	{
		if (isset($_POST))
			$post = $_POST;


		$where = array();
		$wherestr = "where 1 = 1";
		$value = "";

		// SETUP INDIVIDUAL AND ALL SEARCH TYPES
		if (isset($post) && $post && ($this->search["type"] == "individual" || $this->search["type"] == "all"))
		{
			if (isset($post["search"]))
			{
				$value = $post["search"];
				$this->search["value"] = $post["search"];
			}
			if (isset($post["search['field']"]))
				$this->search["field"] = $post["searchfield"];
		}
		
		foreach ($this->fields as $field)
		{
			
			if (isset($field->name))
			{
				$fieldname = $field->name;
				// SETUP LIST REQUIREMENTS
				
					
				if (isset($field->listrequirement) && $field->listrequirement)
				{
					if (is_array($field->listrequirement))
					{
						$orwhere = array();
						foreach($field->listrequirement as $listrequirement)
						{
							$orwhere[] = "$field->name = '$listrequirement'";
						}

						$where[] = "(" . implode(" OR ", $orwhere) . ")";
					}
					else
						$where[] = "$field->name = '$field->listrequirement'";
				}
				if (isset($field->where) && $field->where)
				{
					if (is_array($field->where))
					{
						$fieldwhere = '';
						foreach($field->where as $thiswhere)
						{
							if(empty($fieldwhere))
								$fieldwhere .= "$field->name {$thiswhere['condition']}";
							else
								$fieldwhere .= " {$thiswhere['junction']} $field->name {$thiswhere['condition']}";
						}
						$where[] = "($fieldwhere)";
					}
					else
						$where[] = "$field->name $field->where";
				}
				// SETUP ADVANCED SEARCH
				if (isset($post) && $post) // PROCESS SEARCH QUERY AS WELL AS NORMAL QUERY
				{
					$this->cur = 0;  // RESET TO PAGE 0 of RECORD SET

					if (isset($field->search["searchable"]) && $field->search["searchable"])
					{
						if ($this->search["type"] == "advanced")
						{
							if ($field->search["type"] != "range" && isset($post[$fieldname]))
							{
								$value = $post[$field->name];
							}
							else
								$value = true;
							$this->fields[$field->name]->search["value"] = $value;
						}
						else
						{
							if ($field->search["type"] == "range")
								$value = 0;
						}
						if ($field->search["type"] == "range")
						{
							$minval = $post[$field->name . "_min"];
							$maxval = $post[$field->name . "_max"];
							$this->fields[$field->name]->search["value_min"] = $minval;
							$this->fields[$field->name]->search["value_max"] = $maxval;
						}

						if (($value && ($this->search["type"] == "advanced" || $this->search["type"] == "all")) || ($this->search["type"] == "individual" && isset($post["searchfield"]) && $field->name == $post["searchfield"]));
						{
							switch ($field->search["type"])
							{

								case "exact":
									if (strlen($value) > 0)
										$swhere[] = "$field->name = '$value'";
									break;
								case "index":
									if ($value != "any" && (strlen($value) > 0))
										$swhere[] = "$field->name = '$value'";
									break;
								case "contains":
									if (strlen($value) > 0)
										$swhere[] = "$field->name LIKE '%$value%'";
									break;
								case "range":
									if (strlen($minval) > 0)
										$swhere[] = "$field->name >= '$minval'";
									if (strlen($maxval) > 0)
										$swhere[] = "$field->name <= '$maxval'";
									break;
								case "multiple":
									if ($value !== 1 && is_array($value))
									{
										foreach ($value as $val)
										{
											$mvalue[] = "$field->name = '$val'";
										}
										if (isset($mvalue))
										{
											$tmpstr = implode(" OR ", $mvalue);
											$swhere[] = " ($tmpstr) ";
										}
									}
									break;
							}
						}
					}
				}
			}
			else
			{
				echo("you have modified a field that doesn't exist. Here is an echo of that field");
				echo_r($field);
				echo_backtrace();
			}
		}
		if (isset($swhere[0]))
		{
			switch ($this->search["type"])
			{
				case "all":
					$search["wherestr"] = " AND (" . implode(" OR ", $swhere)  . ")";
					break;
				case "individual":
					$search["wherestr"] = " AND (" . implode(" AND ", $swhere)  . ")";
					break;
				case "advanced":
					$search["wherestr"] = " AND (" . implode(" AND ", $swhere)  . ")";
					break;
			}
			$this->search["wherestr"] = $search["wherestr"];
		}
		if (isset($post) && $post && !isset($swhere)) // IF POSTING A SEARCH WITHOUT VALUES THEN CLEAR OUT THE SWHERE STRING
		{
			$this->search["wherestr"] = null;
		}

		if (isset($where[0]))
			$wherestr .= " AND " . implode(" AND ", $where);

		if (isset($this->search["wherestr"]))
			$wherestr .= $this->search["wherestr"];

//  		echo_r($this->search["wherestr"]);
//   		echo_r($wherestr);
		$this->wherestr = $wherestr;
	}

	function getRecords() # LIMIT OF -1 GIVES YOU ALL THE RECORDS
	{
		$dbconnname = $this->dbconnname;
		global $$dbconnname;

		$this->setupRequirements();

		$this->records = NULL; # FIRST UNSET ALL THE CURRENT RECORDS

		$this->getTotal();

		if ($this->cur < 1)
				$this->cur = 0;

		$query = "SELECT * FROM $this->name $this->wherestr";

		if (isset($this->sort))
				$query .=" ORDER BY $this->sort";

		if (isset($this->sort) && isset($this->direction) && ($this->direction == "ASC" || $this->direction == "DESC"))
				$query .= " $this->direction";

		if ($this->limit != -1 && is_numeric($this->limit))
				$query .= " LIMIT $this->limit OFFSET $this->cur";

		$this->sql = $query;
		$results = $$dbconnname->fetch_rows($query);
		foreach ($results as $array)
		{
				$idfield = $this->idfield;
				$id = $array[$idfield];

				$this->records[$id] = new record($this->name, $id, $idfield, $dbconnname, $array);
		}
	}

	/**
	 * xmlExport
	 *
	 * @access public
	 * @return void
	 */
	function xmlExport()
	{
		$root = $this->name;
		$XML = "<$this->name>\r";
		if (count($this->records))
		{
			foreach ($this->records as $record)
			{
				$XML .= $record->xmlExport() . "\r";
			}
		}
		$XML .= "</$this->name>\r";


		return $XML;
	}

	/**
	 * reorder
	 *
	 * @param mixed $array
	 * @access public
	 * @return void
	 */
	function reorder($array = false)
	{
		if ($array)
		{
			if (is_array($array))
				$this->order = $array;
		}
		$array = $this->order;

		$a_diff = array_diff(array_keys($this->fields), $array);

		$this->order = array_merge($array, $a_diff);
	}

}
?>
