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

/**
* Integration between a database and html.
*
* Forms2 accomplishes the same thing as forms, only better. It creates an easy to use link between a table and a html list,
* or a record and an html form. It supports full validation of types and requirements.
*
* @author Steve Francia <sfrancia@supernerd.com>
* @version 2.0
* @since 1.2
* @package forms
* @access public
* @copyright Supernerd LLC and Contributors
*
*/
class form2
{
/**
* Name of current table in the database
*
* @var string
* @access public
*/
	var $tablename;
/**
* Reference to the table object
*
* @var object
* @access public
*/
	var $table;
/**
* Reference to the form object
*
* @var object
* @access public
*/
	var $form;
/**
* Reference to the record object
*
* @var object
* @access public
*/
	var $record;
/**
* Id of the current record
*
* @var mixed
* @access public
*/
	var $id = false;

/**
* Constructor.
*
* Can be used to instantiate the object, or if passed a type, handle the retrival of information from the database
*
* @param string $tablename  table name in the database
* @param string $type  OPTIONAL, can be 'list' or 'record'
* @param mixed  $int  OPTIONAL, if $type = 'list' than an int that represents the limit, if $type = 'record' then required and is the id of the record
* @return NULL
* @access public
*/
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

	/**
	 * setParam
	 *
	 * Set a table based parameter
	 *
	 * @param mixed $name
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setParam($name, $value)
	{
		$this->table->$name = $value;
	}

	/**
	 * setFieldParam
	 *
	 * Set a field specific parameter
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param string $name
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
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

	/**
	 * setFieldIndexTable
	 *
	 * Setup an index table for a specific field, especially useful for things like select boxes and the like.
	 * Used to grab a list of possible values and labels from another table in the database
	 *
	 * @param string $fieldname
	 * @param string $tablename
	 * @param string $id fieldname in the indexed table that has the values in it
	 * @param string $label fieldname in the indexed table that has the labels in it
	 * @param string $restriction something like "date = $date"
	 * @access public
	 * @return void
	 */
	function setFieldIndexTable($fieldname, $tablename, $id, $label, $restriction = null)
	{
		$this->table->fields[$fieldname]->setIndexTable($tablename, $id, $label, $restriction);
	}

	/**
	 * setFieldIndex
	 *
	 * Setup an index for a specific field, especially useful for things like select boxes and the like.
	 * here an array is passed in and key = value, value = label
	 *
	 * @param mixed $fieldname
	 * @param mixed $index
	 * @access public
	 * @return void
	 */
	function setFieldIndex($fieldname, $index)
	{
		$this->table->fields[$fieldname]->setIndex($index);
	}

	/**
	 * setAllFieldsParam
	 *
	 * Sets all existing fields to have a specific value for a specific property.
	 * Useful when you have a table with like 30 fields, but only want to show two in a list. setAllFieldsParam('listshow', false); then set the two necessary ones to true
	 *
	 * @param mixed $name
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setAllFieldsParam($name, $value)
	{
 		foreach ($this->table->fields as $field)
		{
			$array[] = $field->name;
		}

		$this->setFieldParam($array, $name, $value);
	}

	/**
	 * setAllFieldsInnerParam
	 *
	 * Sets all existing fields to have a specific value for a specific property's parameter.
	 * Some field parameters like html and validate have their own parameters, this function is used to change one of their parameters without changing the entire html or validate parameter.
	 *
	 * @param string $name
	 * @param string $innername
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setAllFieldsInnerParam($name, $innername, $value)
	{
		foreach ($this->table->fields as $field)
		{
			$array[] = $field->name;
		}

		$this->setFieldInnerParam($array, $name, $innername, $value);
	}

	/**
	 * setFieldInnerParam
	 *
	 * Some field parameters like html and validate have their own parameters, this function is used to change one of their parameters without changing the entire html or validate parameter.
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param string $name
	 * @param string $innername
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
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

	/**
	 * setHTMLoptions
	 * Setup the html display options used when rendering the form for this field.
	 * Should be something like $value = array("type" => "text");
	 * Values of the array other than type are the parameters required/supported by the type of guicontrol.
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param array $value
	 * @access public
	 * @return void
	 * @see guicontrol
	 */
	function setHTMLoptions($fieldname, $value)
	{
		$this->setFieldParam($fieldname, "html", $value);
	}

	/**
	 * setHTMLoption
	 * Setup a specific html display options to be used when rendering the form for this field.
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param string $innername option like type
	 * @param mixed $value
	 * @access public
	 * @return void
	 * @see guicontrol
	 */
	function setHTMLoption($fieldname, $innername, $value)
	{
		$this->setFieldInnerParam($fieldname, "html", $innername, $value);
	}

	/**
	 * setValidationOptions
	 * Define the validation for this specific field, or these sepecific fields.
	 * Values of the array other than type are the parameters required/supported by the type of validation.
	 * type needs to be one supported by the validate class
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param array $value array needs 'type' set.
	 * @access public
	 * @return void
	 * @see validate
	 */
	function setValidationOptions($fieldname, $value)
	{
		$this->setFieldParam($fieldname, "validation", $value);
	}

	/**
	 * setValidationOption
	 * Define an individaul validation parameter for this specific field, or these sepecific fields.
	 * could be something like setValidationOption('name', 'type', 'alphanumeric');
	 * type needs to be one supported by the validate class
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param string $innername
	 * @param string $value
	 * @access public
	 * @return void
	 * @see validate
	 */
	function setValidationOption($fieldname, $innername, $value)
	{
		$this->setFieldInnerParam($fieldname, "validation", $innername, $value);
	}

	/**
	 * required
	 * set a field or fields to be required as part of the validation.
	 *
	 * @param mixed $fieldname can be the name of a field, or an array of fieldnames.
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function required($fieldname, $value = true)
	{
		$this->setValidationOption($fieldname, 'required',  $value);
	}

	/**
	 * getValue
	 * gets the value from the record object and returns it.
	 *
	 * @param string $fieldname
	 * @access public
	 * @return mixed $value
	 */
	function getValue($fieldname)
	{
		if ($this->id == 'new')
			return "";

		if (isset($this->record->values[$fieldname]->value))
			return $this->record->values[$fieldname]->value;
		else
			trigger_error("No value is set for the field: $fieldname");
	}

	/**
	 * getRecords
	 * Requests the necessary records from the database (as would be used in a listing).
	 *
	 * @param mixed $limit
	 * @access public
	 * @return void
	 */
	function getRecords($limit = false)
	{
		if ($limit !== false)
			$this->setParam("limit", $limit);

		$this->table->getRecords();
	}

	/**
	 * getRecord
	 * Requests the requested record from the database (as would be used in a record).
	 *
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function getRecord($id = false)
	{
		if ($id === false)
			$id = $this->id;

		$this->record = &$this->form->passRecord($this->tablename, $id);
		$this->form->DescIntoFields($this->tablename, $id);

		return $this->record;
	}

	/**
	 * saveRecord
	 * Takes the current record and writes its content to the database.
	 * If the record is new it will insert it, if not it will update it.
	 *
	 * @param mixed $POST
	 * @access public
	 * @return void
	 */
	function saveRecord($POST = false)
	{
		if (!isset($this->id))
			trigger_error("Forms2 does not have a current record to save");
		if ($POST === false)
			$POST = getRawPost();

		$this->form->setvaluesfrompost($POST);
		return $this->form->storeRecord($this->tablename, $this->id);
	}

	/**
	 * setOrder
	 * Pass in an array of the fieldnames in the order you would like them to appear in for either the listing or form.
	 *
	 * @param array $array
	 * @access public
	 * @return void
	 */
	function setOrder($array)
	{
		$this->table->order = $array;
	}

	/**
	 * deleteRecord
	 * Removes a record from the database
	 *
	 * @param mixed $id
	 * @access public
	 * @return void
	 */
	function deleteRecord($id)
	{
		$this->form->deleteRecord($this->tablename, $id);
	}

	/**
	 * setTitle
	 * Sets the title for the html display of a listing or record.
	 *
	 * @param mixed $title
	 * @access public
	 * @return void
	 */
	function setTitle($title)
	{
		$this->record->title = $title;
	}

	/**
	 * guiAssign
	 * Used to assign a portion of the form into the gui object so that the smarty functions can use it to draw the form or listing.
	 * This step is necessary if you want to actually view anything.
	 *
	 * @param string $name a name to assign the value to
	 * @param mixed $switch can be form, tablename, table, or record
	 * @access public
	 * @return void
	 */
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

	/**
	 * sort
	 * tells the getRecords function a sorting to get the records in from the database.
	 *
	 * @param string $fieldname fieldname to sort on
	 * @param string $direction either ASC or DESC
	 * @access public
	 * @return void
	 */
	function sort($fieldname, $direction = "ASC")
	{
		$this->table->sort = $fieldname;
		$this->table->direction = $direction;
	}


}
?>
