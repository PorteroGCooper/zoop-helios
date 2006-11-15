<?php
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */


/**
 * @author   Steve Francia <sfrancia@supernerd.com>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_forms_list($params, &$smarty)
{
	$table_attr = 'border="1"';
	$tr_attr = '';
	$td_attr = '';
	$sort_type='js';
	$cols = 3;
	$show_error = "1";
	$rows = 3;
	$trailpad = '&nbsp;';
	$vdir = 'down';
	$hdir = 'right';
	$inner = 'cols';
	$class = "";
	$empty_error = false;

	foreach ($params as $_key=>$_value) {
		switch ($_key) {
		case 'form':
		case 'table':
		case 'class':
		case 'empty_error':
		case 'sort_type':
			$$_key = $_value;
			break;
		}
	}

$table_id = __randomstring();

$ctable =& $form->tables->$table;

if ($ctable->pages > 1)
	$sort_type = "forms";

$baselink = $smarty->_tpl_vars["SCRIPT_URL"] . "/" . $form->tables->$table->zone . "/";

$initpath = $smarty->_tpl_vars["VIRTUAL_URL"];

$lastchar = substr($initpath, -1);
if (($lastchar == "/") || is_int($lastchar))
	$initpath = $initpath;
else
	$initpath = $initpath . "/";
$apath = explode("/",$initpath);
$lastelement = array_pop($apath);
$path = implode("/", $apath) . "/";
if ($class == "" || $class == " ")
	$class = "list";

if (!isset($form->tables->$table))
	return "";

	$output = "<table cellpadding=0 cellspacing=0 border=0><tr><td colspan=\"3\">";
	$output .= "<table class='$class' cellpadding='1' cellspacing='1' border='0'><tr>";

	if (isset($form->tables->$table->title))
		$output .= "<th>" . $form->tables->$table->title . "</th></tr><tr>";

	if ($sort_type == 'js')
		$output .= "<TD><table  id='$table_id' class='sortable' cellpadding=1 cellspacing=1>";
	else
		$output .= "<TD><table  id='$table_id' cellpadding=1 cellspacing=1>";


	if ($sort_type == 'js')  // OUTPUT THE COLUMN DATATYPE FOR JS SORTING
	{
		/* This code doesn't do anything?
		foreach ($form->tables->$table->order as $fieldname)
		{

			$field = &$form->tables->$table->fields[$fieldname];

			if (isset($field->listshow))
			{
				if ($field->listshow)
				{
					$datatype = $field->datatype;
					if (!$datatype)
						$datatype = "alpha";
				}
			}
		}
		if ($form->tables->$table->deleteColumn)
		{

		}
		*/
		$output .= "<tr  style=\"cursor: hand; cursor: pointer;\">";
	}
	else
		$output .= "<tr>";


	$title = $form->tables->$table->name;
	$i = 0;

	foreach ($form->tables->$table->order as $fieldname)
	{
		$field = &$form->tables->$table->fields[$fieldname];
		if (isset($field->listshow) && $field->listshow)
		{
			if ($sort_type == "forms")
			{
				if ($field->name == $ctable->sort)
				{
					if ($ctable->direction == "ASC")
						$dir = "DESC";
					else
						$dir = "ASC";
				}
				else
					$dir = "ASC";
				$i++;
				$output .= "<th>";
				$output .= "<a href=\"";
				$output .= "$path?sort=$field->name&dir=$dir&start=0";
				$output .= "\">";
				$output .= $field->label;
				$output .= "</a>";
				$output .= "</th>";
			}
			else
			{
				$i++;
				$output .= "<th>";
				$output .= $field->label;
				$output .= "</th>";
			}
		}
	}

	if ($form->tables->$table->deleteColumn)
	{
	$i++;
	$output .= "<th><a>Del</a></th>";
	}

	$cols = $i;
// 	$output .= "</tr></thead><tbody>";
	$output .= "</tr>";
	$j = 1;
	if(!is_array($form->tables->$table->records))
		$form->tables->$table->getRecords();
	if (is_array($form->tables->$table->records))
	{
		foreach ($form->tables->$table->records as $record)
		{
			if(isset($form->tables->$table->rowclasses))
			{
				$classField = $form->tables->$table->rowclasses['field'];
				if(isset($form->tables->$table->rowclasses['classes'][$record->values[$classField]->value]))
					$rclass = $form->tables->$table->rowclasses['classes'][$record->values[$classField]->value];
			
			}
			if(!isset($rclass))
				if ($j%2 == 0)
					$rclass = "even";
				else
					$rclass = "odd";

			$output .= "<tr class=\"" . $rclass . "\">";
			if ($form->tables->$table->id_location == "page")
				$link = $baselink . $form->tables->$table->listlink . "/" . $record->id ;
			else
				$link = $baselink . $record->id . "/" .  $form->tables->$table->listlink;
			foreach ($form->tables->$table->order as $fieldname)
			{
				$field = &$form->tables->$table->fields[$fieldname];
				if (isset($field->listshow) && $field->listshow)
				{
					$name= $field->name;
					if (!empty($field->showIndex))
					{
						$tvalue = $record->values[$name]->value;
						if (isset($field->index[$tvalue]))
							$lvalue = $field->index[$tvalue];
						else
							$lvalue = "";
					}
					else
					{
						switch($field->type)
						{
							case "timestamptz":
							case "date":
								if(isset($field->format))
									$lvalue = sql_format_date($record->values[$name]->value, $field->format);
								else
									$lvalue = $record->values[$name]->value;
								break;
							default:
								$lvalue = $record->values[$name]->value;
						}
					}
					if ($field->clickable)
						$output .= "<td><a href=\"$link\">" . $lvalue . "</a></td>";
					else
						$output .= "<td>" . $lvalue . "</td>";
				}
			}
			
			if ($form->tables->$table->deleteColumn)
			{
					if (isset($form->tables->$table->deletelink))
					{
						$deletelink = $smarty->_tpl_vars["SCRIPT_URL"] . "/" . $form->tables->$table->zone . "/" .$form->tables->$table->deletelink . "/" . $record->id ;
						$output .= "<td class=\"" . $rclass . "\" align=\"right\" valign\"bottom\"><a href=\"$deletelink\" onclick=\"return confirm('Are you sure you want to delete this?');\" class=\"delete\">" . "X" . "</a></td>";
					}
					else
					{
						$output .= "<td class=\"" . $rclass . "\">" . "set deletelink var" . "</td>";
					}
			}

			$j++;
			$output .= "</tr>";
		}
	}
	else
	{
		if ($empty_error === false)
			$output = "<h2>No Records Found</h2>";
		else
			$output = "<h2>$empty_error</h2>";
		return $output;
	}

	//$output .= "</tbody></table></td></tr>";
	$output .= "</table></td></tr></table></td></tr>";

	if ($ctable->pages > 1)
	{



		$output .= "<tr><td align=\"left\" width=\"33%\">&nbsp;";
		if ($ctable->cur >= $ctable->limit)
		{
			$output .= "<a href=\"";
			$output .= $path;
			$output .= "?start=";
			$output .= ($ctable->cur - $ctable->limit);
			$output .="\" class=\"pages\">&#171; Previous</a>";
		}
		$output .= "</td>";
		$output .= "<td align=\"center\" width=\"33%\">&nbsp;";
		$output .= $ctable->cur +1;
		$output .= " - ";
		$output .= ($ctable->cur + $ctable->limit) > $ctable->total ? $ctable->total : $ctable->cur + $ctable->limit ;
		$output .= " of ";
		$output .= $ctable->total;
		$output .= "</td>";
		$output .="<td align=\"right\" width=\"33%\">&nbsp;";
		if ($ctable->cur < $ctable->total - $ctable->limit)
		{
			$output .= "<a href=\"";
			$output .= $path;
			$output .= "?start=";
			$output .= ($ctable->cur + $ctable->limit);
			$output .="\" class=\"pages\">Next &#187;</a>";
		}
		$output .= "</td></tr>";
	}

	$output .= "</table>";
return $output;
}


function __randomstring() {
	// RANDOM KEY PARAMETERS
	$keychars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$length = rand(8,40);

	// RANDOM KEY GENERATOR
	$randkey = "";
	for ($i=0;$i<$length;$i++)
	{
	$randkey .= substr($keychars, rand(1, strlen($keychars) ), 1);
	}
	return $randkey;
}

/* vim: set expandtab: */

?>
