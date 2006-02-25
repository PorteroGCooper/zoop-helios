<?
/**
* @category zoop
* @package forms
*/
// Copyright (c) 2006 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

class field
{
	var $name;
	var $dbconnname;
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

	function field($fieldarray, $dbconnname)
	{
		global $$dbconnname;
		$this->dbconnname = $dbconnname;
		$this->name = $fieldarray["name"];
		$this->type = $fieldarray["type"];

		if (strstr($fieldarray["flags"], "auto_increment") || (strstr($fieldarray["flags"], "seq")))
				$this->autoincrement = 1;
		if (strstr($fieldarray["flags"], "primary_key"))
				$this->key  = 1;
	}
/*
	function setDb(&$db)
	{
		$$dbconnname = &$db;
	}*/

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
		$dbconnname = $this->dbconnname;
		global $$dbconnname;

		$restriction ? $where = " where $restriction" : $where = " ";
		$this->index = $$dbconnname->fetch_simple_map("select $key, $val from $table $where", $key,$val);
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
?>