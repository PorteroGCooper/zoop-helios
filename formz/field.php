<?php
/**
* @category zoop
* @package formz
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
 * field
 *
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class field
{
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
	 * type
	 *
	 * @var mixed
	 * @access public
	 */
	var $type;
	/**
	 * length
	 *
	 * @var mixed
	 * @access public
	 */
	var $length;
	/**
	 * datatype
	 *
	 * @var mixed
	 * @access public
	 */
	var $datatype;
	/**
	 * default
	 *
	 * @var mixed
	 * @access public
	 */
	var $default;
	/**
	 * label
	 *
	 * @var mixed
	 * @access public
	 */
	var $label;
	/**
	 * title
	 *
	 * @var mixed
	 * @access public
	 */
	var $title = false;
	/**
	 * html
	 *
	 * @var string
	 * @access public
	 */
	var $html = array('type' => 'text', 'length' => '', 'height' => '');
	/**
	 * formshow
	 *
	 * @var mixed
	 * @access public
	 */
	var $formshow = true;
	/**
	 * listshow
	 *
	 * @var mixed
	 * @access public
	 */
	var $listshow = true;
	/**
	 * listrequirement
	 *
	 * @var mixed
	 * @access public
	 */
	var $listrequirement = null;
	/**
	 * showIndex
	 *
	 * @var mixed
	 * @access public
	 */
	var $showIndex = false;
	/**
	 * clickable
	 *
	 * @var mixed
	 * @access public
	 */
	var $clickable = false;
	/**
	 * index
	 *
	 * @var mixed
	 * @access public
	 */
	var $index;
	/**
	 * autoincrement
	 *
	 * @var mixed
	 * @access public
	 */
	var $autoincrement;
	/**
	 * description
	 *
	 * @var mixed
	 * @access public
	 */
	var $description;
	/**
	 * validation
	 *
	 * @var array
	 * @access public
	 */
	var $validation = array(); //'type' => '', 'required' => false);
	/**
	 * key
	 *
	 * @var mixed
	 * @access public
	 */
	var $key;
	/**
	 * search
	 *
	 * @var string
	 * @access public
	 */
	var $search = array('searchable' => true, 'type' => 'exact', 'value' => null, 'value_max' => null, 'value_min' => null);
			// type can be "exact" or "contains" or "range" or "index" . We will guess this for you depending on type

	/**
	 * field
	 *
	 * @param mixed $fieldarray
	 * @param mixed $dbconnname
	 * @access public
	 * @return void
	 */
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

	/**
	 * setDb
	 *
	 * @param mixed $db
	 * @access public
	 * @return void
	 */
	function setDb(&$db)
	{
		$$dbconnname = &$db;
	}

	/**
	 * setIndex
	 *
	 * @param mixed $index
	 * @access public
	 * @return void
	 */
	function setIndex($index)
	{
		$this->index = $index;
		$this->showIndex = 1;
		(isset($this->html['type']) && $this->html['type'] == "multiple") ? $this->html['type'] : $this->html['type'] = "select";
		if ($this->search["type"] != 'multiple')
			$this->search["type"] = "index";
	}

	/**
	 * setIndexTable
	 *
	 * @param mixed $table
	 * @param mixed $key
	 * @param mixed $val
	 * @param mixed $restriction
	 * @access public
	 * @return void
	 */
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

	/**
	 * required
	 *
	 * @param mixed $var
	 * @access public
	 * @return void
	 */
	function required($var = true)
	{
		$this->validation['required'] = $var;
	}
}
?>
