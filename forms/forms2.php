<?
/**
* @package forms

// Copyright (c) 2006 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.
*/


class form2
{
	var $tablename;
	var $table;
	var $form;
	var $record;
	var $id = false;


	function form2($tablename, $type = false, $int = false)
	{
		$this->tablename = $tablename;

		$this->form = new form;
		$this->form->initTable($tablename);
		$this->table = &$this->form->tables->$tablename;
		$this->table->setupenv($_GET);

		if ($type == 'list')
		{
			$this->getRecords($int);
		}
		elseif ($type == 'record')
		{
			if ($int === false)
				trigger_error("You need to specify an id for this type form");
			$this->getRecord($int);
			$this->id = $int;
		}
	}

	function setParam($name, $value)
	{
		$this->table->$name = $value;
	}

	function setFieldParam($fieldname, $name, $value)
	{
		if (is_array($fieldname))
			foreach ($fieldname as $fieldn)
			{
				if (isset($this->table->fields[$fieldn]))
					$this->table->fields[$fieldn]->$name = $value;
				else
					trigger_error("No field exists with the name: $fieldn");
			}
		else
		{
			if (isset($this->table->fields[$fieldname]))
				$this->table->fields[$fieldname]->$name = $value;
			else
				trigger_error("No field exists with the name: $fieldname");
		}
	}

	function setFieldIndexTable($fieldname, $tablename, $id, $label)
	{
		$this->table->fields[$fieldname]->setIndexTable($tablename, $id, $label);
	}

	function setFieldIndex($fieldname, $index)
	{
		$this->table->fields[$fieldname]->setIndex($index);
	}

	function setAllFieldsParam($name, $value)
	{
 		foreach ($this->table->fields as $field)
		{
			$array[] = $field->name;
		}

		$this->setFieldParam($array, $name, $value);
	}

	function setAllFieldsInnerParam($name, $innername, $value)
	{
		foreach ($this->table->fields as $field)
		{
			$array[] = $field->name;
		}

		$this->setFieldInnerParam($array, $name, $innername, $value);
	}

	function setFieldInnerParam($fieldname, $name, $innername, $value)
	{

		if (is_array($fieldname))
			foreach ($fieldname as $fieldn)
			{
				if (isset($this->table->fields[$fieldn]->$name))
					{
						$tmp = &$this->table->fields[$fieldn]->$name;
						$tmp[$innername] = $value;
					}
				else
					trigger_error("No field exists with the name: $fieldn OR No parameter is named $name");
			}
		else
		{
			if (isset($this->table->fields[$fieldname]->$name))
			{
				$tmp = &$this->table->fields[$fieldname]->$name;
				$tmp[$innername] = $value;
			}
			else
				trigger_error("No field exists with the name: $fieldname OR No parameter is named $name");
		}
	}

	function setHTMLoptions($fieldname, $value)
	{
		$this->setFieldParam($fieldname, "html", $value);
	}

	function setHTMLoption($fieldname, $innername, $value)
	{
		$this->setFieldInnerParam($fieldname, "html", $innername, $value);
	}

	function setValidationOptions($fieldname, $value)
	{
		$this->setFieldParam($fieldname, "validation", $value);
	}

	function setValidationOption($fieldname, $innername, $value)
	{
		$this->setFieldInnerParam($fieldname, "validation", $innername, $value);
	}

	function required($fieldname, $value = true)
	{
		$this->setValidationOption($fieldname, 'required',  $value);
	}

	function getValue($fieldname)
	{
		if ($this->id == 'new')
			return "";

		if (isset($this->record->values[$fieldname]->value))
			return $this->record->values[$fieldname]->value;
		else
			trigger_error("No value is set for the field: $fieldname");
	}

	function getRecords($limit = false)
	{
		if ($limit !== false)
			$this->setParam("limit", $limit);

		$this->table->getRecords();
	}

	function getRecord($id = false)
	{
		if ($id === false)
			$id = $this->id;

		$this->record = &$this->form->passRecord($this->tablename, $id);
		$this->form->DescIntoFields($this->tablename, $id);

		return $this->record;
	}

	function saveRecord($POST = false)
	{
		if (!isset($this->id))
			trigger_error("Forms2 does not have a current record to save");
		if ($POST === false)
			$POST = getRawPost();

		$this->form->setvaluesfrompost($POST);
		return $this->form->storeRecord($this->tablename, $this->id);
	}

	function setOrder($array)
	{
		$this->table->order = $array;
	}

	function deleteRecord($id)
	{
		$this->form->deleteRecord($this->tablename, $id);
	}

	function setTitle($title)
	{
		$this->record->title = $title;
	}

	function guiAssign($name, $switch)
	{
		global $gui;

		switch($switch)
		{
			case 'form':
				$gui->assign($name, $this->form);
				break;

			case 'tablename':
				$gui->assign($name, $this->tablename);
				break;

			case 'table':
				$gui->assign($name, $this->table);
				break;

			case 'record':
				$gui->assign($name, $this->record);
				break;
		}
	}

	function sort($fieldname, $direction = "ASC")
	{
		$this->table->sort = $fieldname;
		$this->table->direction = $direction;
	}


}
?>