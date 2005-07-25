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

include("XML/Serializer.php");

class form
{
	var $tables;
	var $db;
	var $dbconnname;

	function initTable($table, $dbconnname = 'defaultdb')
	{
		global $$dbconnname;

		$this->db = &$$dbconnname;
		$this->dbconnname = $dbconnname;

		if (!isset($this->tables->$table))
			$this->tables->$table = new table($table, &$this->db);
		else
			$this->tables->$table->setDb(&$this->db);
	}

	function passIdfield($table)
	{
		if (!isset($this->tables->$table))
			initTable($table, $this->dbconnname);

	return $this->tables->$table->idfield;
	}

	function grabRecord($table,  $id)
	{
		if (isset($this->tables->$table->records->$id))
			return;

		$this->initTable($table, $this->dbconnname);


		$idfield = $this->passIdfield($table);

		$this->tables->$table->records[$id] = new record ($table, $id, $idfield, &$this->db);
	}

	function passRecord($table, $id)
	{
		if (!isset($this->tables->$table->records[$id]))
				$this->grabRecord($table, $id);
		$this->tables->$table->records[$id]->order = &$this->tables->$table->order;

	return $this->tables->$table->records[$id];
	}


	function deleteRecord ($table, $id, $type = 0)
	{
		$idfield = $this->passIdfield($table);

		if (isset($this->tables->$table->deletedfield))
			$deletedfield = $this->tables->$table->deletedfield;

		if ($type == 0) # This means actually Delete the record
			$query = "DELETE FROM $table WHERE $idfield = $id";

		if ($type == 1) # this means set deleted flag to 1
			$query = "UPDATE $table set $deletedfield = 1 where $idfield = $id";

		$this->db->query($query);
	}

	function DescIntoFields($table, $id)
	{
		if (isset($this->tables->$table->records[$id]->values) && is_array($this->tables->$table->records[$id]->values))
		{
			while(list ($key,$cell) = each($this->tables->$table->records[$id]->values))
			{
				$fieldname = $cell->name;
				$this->tables->$table->records[$id]->values[$fieldname]->description =& $this->tables->$table->fields[$fieldname];
			}
		}
		else
		{
			echo_r("this record does not exist"); die();
		}
	}

	function saveRecord($post)
	{
		$this->setValuesFromPost($post);
		return $this->storeRecord($post["recordtable"], $post["recordid"]);
	}

	function setValuesFromPost($post)
	{
		$table = $post["recordtable"];
		$id = $post["recordid"];

		foreach($post as $field => $value)
		{
			if (isset($this->tables->$table->records[$id]->values[$field]))
			{
				if (is_array($value))
				{

					if ($this->tables->$table->fields[$field]->datatype == "numeric")
					{
						$setval = 0;
						foreach ($value as $val)
						{
							$setval = $setval | $val;
						}
						$this->tables->$table->records[$id]->values[$field]->value = $setval;
					}
					else
						$this->tables->$table->records[$id]->values[$field]->value = implode(",", $value);
				}
				else
					$this->tables->$table->records[$id]->values[$field]->value = $value;
			}
		}

	}

	function storeRecord($table, $id)
	{

		$record =& $this->tables->$table->records[$id];

		$idfield = $this->tables->$table->idfield;

		if ($id == "new" && $this->tables->$table->fields[$idfield]->autoincrement)
			unset($record->values[$idfield]); // only unset this if the table is set to autoincrement

		if ($record->db->db->phptype == "mysql")
			$colquote = '`';
		else
			$colquote = '"';

		$record->error = false;

		if ($id == "new")
		{
			$columnstring = "";
			$valuestring = "";
			foreach ($record->values as $field)
			{
				if ($field->value)
				{
						$columnstring .= $colquote . $field->name . $colquote . ",";
 					$value = stripslashes($this->db->escape_string($field->value));
					$valuestring .= "$value,";
				}
			}
			$columnstring = substr($columnstring, 0, -1);
			$valuestring = substr($valuestring, 0, -1);
			$query = "INSERT INTO $table ($columnstring) VALUES ($valuestring)";

			if (isset($this->tables->$table->sequence) && $this->tables->$table->sequence)
			{
				$this->db->insert($query);
				return $this->db->fetch_one_cell("SELECT currval('\"{$this->tables->$table->sequence}\"'::text)");
			}
			else
			{
				$this->db->insert($query);
				return $this->db->fetch_one_cell('select last_insert_id()');
			}
		}
		else
		{
			$setpart = "";
			foreach($record->values as $field){
				if ($field->value == null)
					$setpart .= $colquote . $field->name . $colquote  . "= null,";
				else
					$setpart .= $colquote . $field->name . $colquote  . "='" . $field->value ."',";

			}

			$setpart = substr($setpart, 0, -1);
			$returnid = $id;
			$id = $this->db->db->quoteSmart($id);

			$query = "UPDATE $table set $setpart where $idfield = $id";
			$this->db->query($query);
			return $returnid;
		}



	}
 }

class table
{
	var $idfield;
	var $name;
	var $db;
	var $sequence;
	var $listlink = "edit";
	var $zone = "default";
	var $id_location = "page"; // can be page or zone
	var $fields;
	var $records;
	var $limit = 25; # LIMIT OF -1 GIVES YOU ALL THE RECORDS
	var $cur = 0;
	var $total;
	var $pages;
	var $sort;
	var $direction = "ASC";
	var $deleteColumn = 0;
	var $deletelink = "delete";
	var $deletefield = "deleted";
	var $wherestr = "";
	var $order = array();
	var $search = array("type" => "all", "value" => null, "field" => null, "wherestr" => null);
	var $sql = "";
		// type can be individual or all or advanced

	function table($table, $db)
	{

		$this->name = $table;
		$this->db = &$db;

		$result = $this->db->get_table_info($table);

		foreach($result as $field)
		{
			$fieldname = $field["name"];
			$this->fields[$fieldname] = new field($field, &$this->db);

			if (strstr($field["flags"], "primary_key"))
			{
				$this->idfield = $field["name"];
				$this->fields[$fieldname]->formshow = 0;
				$this->fields[$fieldname]->listshow = 1;
							$this->fields[$fieldname]->clickable = 1;
			}

			if (preg_match('/.*?default_nextval.*?public.(.*?)%29\\s.*/i', $field["flags"], $regs))
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
	}

	function searchClear()
	{
		unset($this->search["value"]);
		unset($this->search["field"]);
		unset($this->search["wherestr"]);

		$this->setAll("search['value']", null);
		$this->setAll("search['value_min']", null);
		$this->setAll("search['value_max']", null);
	}

	function setDb(&$db)
	{
		$this->db = &$db;
	}

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

	function getTotal()
	{
		if (!$this->wherestr)
			$this->setupRequirements();
		$this->total = $this->db->fetch_one_cell("SELECT COUNT(*) FROM $this->name $this->wherestr");
		if (is_numeric($this->limit))
			$this->pages = ceil($this->total / $this->limit);
		else
			$this->pages = 0;
	}

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

	function setupRequirements()
	{
		if (isset($_POST))
			$post = $_POST;


		$where = array();
		$wherestr = "where 1 = 1";
		$value = "";

		// SETUP INDIVIDUAL AND ALL SEARCH TYPES
		if ($post && ($this->search["type"] == "individual" || $this->search["type"] == "all"))
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
					if (isset($field->name))
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
				}
				// SETUP ADVANCED SEARCH
				if ($post) // PROCESS SEARCH QUERY AS WELL AS NORMAL QUERY
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
									if ($value !== 1)
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
				echo("you have modified a field that doesn't exist. Here is an echo..");
				echo_r($this->fields);
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
		if ($post && !isset($swhere)) // IF POSTING A SEARCH WITHOUT VALUES THEN CLEAR OUT THE SWHERE STRING
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
		$this->setupRequirements();

		$this->records = NULL; # FIRST UNSET ALL THE CURRENT RECORDS

		$this->getTotal();

		if ($this->cur < 1)
				$this->cur = 0;

		$query = "SELECT * FROM $this->name $this->wherestr";

		if (isset($this->sort))
				$query .=" ORDER BY $this->sort";

		if (isset($this->direction) && ($this->direction == "ASC" || $this->direction == "DESC"))
				$query .= " $this->direction";

		if ($this->limit != -1 && is_numeric($this->limit))
				$query .= " LIMIT $this->limit OFFSET $this->cur";

		$this->sql = $query;
		$results = $this->db->fetch_rows($query);
		foreach ($results as $array)
		{
				$idfield = $this->idfield;
				$id = $array[$idfield];

				$this->records[$id] = new record($this->name, $id, $idfield, &$this->db, $array);

		}
	}

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

class record
{
	var $id;
	var $db;
	var $table;
	var $idfield;
	var $tableref;
	var $values;
	var $order;
	var $error = false;
	var $submit = "Update";


	function record($table, $id, $idfield, &$db, $array = "")
	{
		$this->id = $id;
		$this->table = $table;
		$this->idfield = $idfield;
		$this->db = &$db;

		if ($this->id == "new")
		{

			$rows = $this->db->get_table_info($table);
			foreach ($rows as $row)
			{
				$name = $row["name"];
				$cell = new cell($name, NULL);
				$this->values[$name] = $cell;
			}
		$this->submit = "Add";
		}
		else
		{
			if (!$array)
			{
				// STILL NEED TO WRITE DATABASE INDEPENDENT
				$Query = "SELECT * FROM $table WHERE $idfield = '$id'";

				$row = $this->db->fetch_one($Query);

				foreach( $row as $key => $value)
				{
					$cell = new cell($key, $value);
					$this->values[$key] = $cell;
				}
			}
			else
			{
				foreach( $array as $key => $value)
				{
					$cell = new cell($key, $value);
					$this->values[$key] = $cell;
				}
			}
		}
	}

	function setDb(&$db)
	{
		$this->db = &$db;
	}

	function xmlExport()
	{
		if (substr($this->idfield, -3) == "_id")
			$root = substr($this->idfield, 0, -3);
		elseif (substr($this->table, -1) == "s")
			$root = substr($this->table, 0, -1);
		else
			$root = $this->table;

		$valarray = array();

		foreach ($this->values as $value)
		{
// 				if (!is_array($value->value) && strpos($value->value,">") > 0)
// 					$valarray[$value->name] = "<![CDATA[$value->value]]>";
// 				else
					$valarray[$value->name] = $value->value;
		}

		if (!isset($serializer))
			$serializer = new XML_Serializer();

		// perform serialization
		$serializer->setOption("indent", "     ");
		$serializer->setOption("rootName", $root);
//		$serializer->setOption("replaceEntities",XML_UTIL_ENTITIES_NONE);
		$serializer->setOption("replaceEntities",XML_UTIL_CDATA_SECTION);
		$result = $serializer->serialize($valarray);

		// check result code and display XML if success

		if($result === true)
			$XML = $serializer->getSerializedData();

	return $XML;
	}
}

class field
{
	var $name;
	var $db;
	var $type;
	var $length;
	var $datatype;
	var $default;
	var $label;
	var $title = false;
	var $html = array('type' => 'text', 'length' => '', 'height' => '');
	var $formshow = true;
	var $listshow = true;
	var $listrequirement = null;
	var $showIndex = false;
	var $clickable = false;
	var $index;
	var $autoincrement;
	var $description;
	var $validation = array(); //'type' => '', 'required' => false);
	var $key;
	var $search = array('searchable' => true, 'type' => 'exact', 'value' => null, 'value_max' => null, 'value_min' => null);
			// type can be "exact" or "contains" or "range" or "index" . We will guess this for you depending on type

	function field($fieldarray, $db)
	{
		$this->db = &$db;
		$this->name = $fieldarray["name"];
		$this->type = $fieldarray["type"];

		if (strstr($fieldarray["flags"], "auto_increment") || (strstr($fieldarray["flags"], "seq")))
				$this->autoincrement = 1;
		if (strstr($fieldarray["flags"], "primary_key"))
				$this->key  = 1;

	}

	function setDb(&$db)
	{
		$this->db = &$db;
	}

	function setIndex($index)
	{
		$this->index = $index;
		$this->showIndex = 1;
		(isset($this->html['type']) && $this->html['type'] == "multiple") ? $this->html['type'] : $this->html['type'] = "select";
		if ($this->search["type"] != 'multiple')
			$this->search["type"] = "index";
	}

	function setIndexTable($table, $key, $val, $restriction = null)
	{
		$restriction ? $where = " where $restriction" : $where = " ";
		$this->index = $this->db->fetch_simple_map("select $key, $val from $table $where", $key,$val);
		$this->showIndex = 1;
		(isset($this->html['type']) && $this->html['type'] == "multiple") ? $this->html['type'] : $this->html['type'] = "select";
		if ($this->search["type"] != 'multiple')
			$this->search["type"] = "index";
	}

	function required($var = true)
	{
		$this->validation['required'] = $var;
	}
}

class cell
{
	var $name;
	var $value;
	var $description;

	function cell($name, $value)
	{
		$this->name = $name;
		$this->value = $value;
	}
}

?>
