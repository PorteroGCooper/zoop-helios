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

include_once(dirname(__file__) . "/function.guicontrol.php");

function smarty_function_forms_form($params, &$smarty)
{


	$class = "";
	foreach ($params as $_key=>$_value) {
		switch ($_key) {
			case 'form':
			case 'class':
			case 'form_type':
			$$_key = $_value;
			break;
		}
	}

	$recordid = $form->id;
	$recordtable = $form->table;
	$submitlabel = $form->submit;

	if ($class == "")
		$class = "forms";

	if (!isset($form_type))
		$form_type = "form";  // EITHER FORM OR SHOW

	$output = "<ul align=\"center\" id=\"errorsbx\">";
	if ($form->error)
		$output .= "<li>". $form->error ."</li>";
	$output .= "</ul><table class='$class' cellpadding=1 cellspacing=1>";
	(isset($form->title) && $form->title) ? $output .= "<tr><th colspan='2'>$form->title</th></tr>" : "";

	$editor = 1;
	foreach ($form->order as $fieldname)
	{
		$field = &$form->values[$fieldname];

		if ($field->description->formshow)
		{
			$name = $field->name;
			$value = $field->value;
			$type = isset($field->description->html['type']) ? $field->description->html['type'] : 'text';
			$index = $field->description->index;

			if ($form_type == "form")
			{
				if ((strlen($field->value) == 0) && (isset($field->description->default) && strlen($field->description->default)))
				{
					$value = $field->description->default;
				}

				$control = &getGuiControl($type, $name);
				if ($type == 'textarea' || $type == 'editor' || $type == 'minieditor' || $type == 'fulleditor' || $type == 'blockeditor')		// No need for htmlspecialchars function to run.
					$control->setParam('value', $value);
				else
					$control->setParam('value', htmlspecialchars($value));

				if (isset($field->description->validation))
					$control->setParam('validate', $field->description->validation);
				$control->setParam('index', $index);
				$control->setParams($field->description->html);

				switch ($type) {   // for establishing type specific options
					case 'editor':
					case 'fulleditor':
					case 'minieditor':
						$control->setParam('editor', $editor);
						$editor++;
						break;
				}
				$labelname = $control->getLabelName();
				$formpart = $control->render();
				$formpart .= "\r";
			}
			else
			{
				switch ($type) {
					case 'text':
					case 'password':
					case 'textarea':
					case 'editor':
					case 'fulleditor':
					case 'minieditor':
					case 'blockeditor':
						$formpart = $value;
						break;
					case 'select':
						foreach ($index as $pval => $label)
						{
							$pval == $value ? $selected = " selected " : $selected = " ";
							if ($selected == " selected ") $formpart = "$label\r";

						}
						break;
					case 'multiple':
						$formpart = "<select width=\"$width\" name=\"$name" . "[]\" id=\"$name\" multiple>";
						foreach ($index as $pval => $label)
						{
							isset($value[$pval]) ? $selected = " selected " : $selected = " ";
							$formpart .= "<option value=\"$pval\" label=\"$label\" $selected>$label</option>";
						}
						$formpart .= "</select>";
						break;
					case 'checkbox':
						if ($value)
     							$formpart = 'On';
     						else
     							$formpart = 'Off';
					default:
						$formpart = $value;
				}
				$labelname = "";

			}

		$output .= "<tr>";
		$output .= "<td valign=\"top\">" . "<label for=\"$labelname\">\r";
		(isset($field->description->validation['required']) && $field->description->validation['required'] && $form_type == "form" ) ? $output .= "<span style=\"color:red;\">*</span>" : "";
		$output .= $field->description->label . ":</label></td>\r";
		$output .= "<td>" . $formpart . "</td>\r";
		$output .= "</tr>";
		}

        }
        $output .= "</table>";

	  if ($form_type == "form")
	  {
		$output .= "<input type=\"hidden\" name=\"recordid\" value=\"$recordid\">";
		$output .= "<input type=\"hidden\" name=\"recordtable\" value=\"$recordtable\">";
		$output .= "<br><input type=\"submit\" name=\"Submit\" class=\"submit\" id=\"submit\" value=\"$submitlabel\" onclick=\"return submitForm(form);\">";
	   }
    return $output;
}


/* vim: set expandtab: */

?>
