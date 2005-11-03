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
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 *
 * @author   Steve Francia
 * @version  1.0
 *
 * @param array
 * @param Smarty
 * @return string
 */

function smarty_function_forms_search($params, &$smarty)
{
	$title = "Search";
	$ajax = true;
	$class = "";
	$sdivid = "search" . rand();
	foreach ($params as $_key=>$_value) {
		switch ($_key) {
		case 'form':
		case 'class':
		case 'table':
		case 'title':
		case 'ajax':
			$$_key = $_value;
			break;
		}
	}

	$output = "";

	$display = "none";


	$cur_table = &$form->tables->$table;

	if ($class == "")
		$class = "forms";

	if ($cur_table->search["type"] == "all" || $cur_table->search["type"] == "individual")
	{
		if (strlen($cur_table->search['value']) != 0)
			$display = "block";
		$output .= "<input type=\"text\" value=\"$cur_table->search['value']\"  name=\"search\">";
	}

	if ($cur_table->search["type"] != "all")
	{

		if ($cur_table->search["type"] == "individual")
		{
			$output .="<br><SELECT name=\"searchfield\">";
		}

		if ($cur_table->search["type"] == "advanced")
		{
 			$output .= "<table class='$class'>\r";
			if (!$ajax)
				$output .= "<tr><th colspan='2'>$title</th></tr>\r";
		}

		foreach ($cur_table->order as $fieldname)
		{
			$field = &$cur_table->fields[$fieldname];
			if (is_array($field->search["value"]))
			{
				if (count($field->search["value"]) != 0)
					$display = "block";
			}
			else
			{
				if (strlen($field->search['value']) != 0 && $field->search['value'] != "any")
					$display = "block";
			}

			if (isset($field->search["searchable"]) && $field->search["searchable"])
			{
				if ($cur_table->search["type"] == "individual")
				{
					$cur_table->search["field"] == $field->name ? $selected = " selected " : $selected = "";
					$output .= "<OPTION value=\"$field->name\" $selected title=\"\">$field->name</OPTION>";
				}
				else // CREATE ADVANCED SEARCH -- A BOX FOR EACH SEARCHABLE FIELD
				{
					if ($field->search["type"] != "range")
					{
						switch ($field->search["type"])
						{
							case "exact":
							case "contains":
								$input = "<input type=\"text\" value=\"{$field->search['value']}\" name=\"$field->name\" id=\"$field->name\">";
								break;
							case "index":
								$input = "<select name=\"$field->name\">";
								$opput = "";
								$sel = false;
								foreach ($field->index as $val => $index)
								{
									if ($field->search["value"] == $val && $field->search["value"] != "any")
									{
										$selected = " selected ";
										$sel = true;
									}
									else
										$selected = "";
									$opput .= "<option value=\"$val\" $selected>$index</option>\r";
								}
									$sel === true ? $selected = "" : $selected = " selected ";
									$opput = "<option value=\"any\" $selected>Any</option>\r" . $opput;
									$input .= $opput;
								$input .= "</select>";
								break;
							case "multiple":
								$input = "<select name=\"{$field->name}[]\" multiple>";
								$opput = "";
								foreach ($field->index as $val => $index)
								{
									$selected = "";
									if (isset($field->search["value"]))
									{
										foreach ($field->search["value"] as $passedInValue)
										{
											if ($passedInValue == $val)
											{
												$selected = " selected ";
											}
										}
									}
									$opput .= "<option value=\"$val\" $selected>$index</option>\r";
								}
								$input .= $opput;
								$input .= "</select>";
								break;
						}
						$output .= "<tr><td>$field->label:</td><td>$input</td></tr>\r";
					}
					else
					{
						$output .= "<tr><td>$field->label min:</td><td><input type=\"text\" value=\"$field->search['value_min']\" name=\"$field->name" . "_min\" id=\"$field->name" . "_min\"></td></tr>\r";
						$output .= "<tr><td>$field->label max:</td><td><input type=\"text\" value=\"$field->search['value_max']\" name=\"$field->name" . "_max\" id=\"$field->name" . "_max\"></td></tr>\r";
					}
				}
			}
		}

		if ($cur_table->search["type"] == "individual")
			$output .= "</SELECT>";

		if ($cur_table->search["type"] == "advanced")
			$output .= "</table>\r";

	}

	$output .= "<br><br><input type=\"submit\" name=\"Submit\" id=\"submit\" value=\"Search\"><br><br>"; //onClick='document.main_form.submit();
	if ($ajax)
	{
		$output = "<a style=\"cursor:pointer;\" onClick=\"toggleVisible('$sdivid');\">Search</a><div id=\"$sdivid\" style=\"display:$display;\">" . $output;
		$output .= "</div>";
	}
return $output;
}


/* vim: set expandtab: */

?>
