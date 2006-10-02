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
 *
 * @author   Steve Francia
 * @version  1.0
 *
 * @param array
 * @param Smarty
 * @return string
 */

include_once(dirname(__file__) . "/function.guicontrol.php");

/**
 * smarty_function_forms_form
 *
 * @param mixed $params
 * @param mixed $smarty
 * @access public
 * @return void
 */
function smarty_function_forms_form($params, &$smarty)
{

	$cols = 1;
	$class = "";
	foreach ($params as $_key=>$_value) {
		switch ($_key) {
			case 'form':
			case 'class':
			case 'form_type':
			case 'cols':
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
	$output .= "</ul><table class='$class' cellpadding=2 cellspacing=2 border=0>";
	(isset($form->title) && $form->title) ? $output .= "<tr><th colspan=$cols>$form->title</th></tr>" : "";

	$output .= "<tr><td valign=\"top\"><table cellpadding=0 cellspacing=0 border=0 width='100%' style=\"border-collapse: collapse;\">";

	$totalshow = 0;

	if (!is_array($form->order))
		$form->order = implode(",", $form->order);

	foreach ($form->order as $fieldname)
	{
		if (isset($form->values[$fieldname]))
		{
			$field = &$form->values[$fieldname];
			if (isset($field->description->formshow) && $field->description->formshow)
				$totalshow++;
		}
	}


	if ($cols != 1)
		$break = intval($totalshow / $cols);
	else
		$break = 10000000;
	$editor = 1;
	$counter = 0;
	foreach ($form->order as $fieldname)
	{
		if (isset($form->values[$fieldname]))
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
						$control->setValue($value);
					else
						$control->setValue(htmlspecialchars($value));

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

					$control = &getGuiControl($type, $name);
					$control->setParam('value', $value);
					$control->setParam('index', $index);
					$control->setParams($field->description->html);
					$labelname = $control->getLabelName();
					$formpart = $control->view();
				}

				$output .= "<tr>";
				$titlestr = "";
				if ($field->description->title)
					$titlestr = "title=\"{$field->description->title}\"";
				$output .= "<td valign=\"top\" class=\"labelcell\">" . "<label for=\"$labelname\" $titlestr>\r";
				(isset($field->description->validation['required']) && $field->description->validation['required'] && $form_type == "form" ) ? $output .= "<span style=\"color:red;\">*</span>" : "";
				$output .= $field->description->label . ":</label></td>\r";
				$output .= "<td valign=\"top\" class=\"fieldcell\">" . $formpart . "</td>\r";
				$output .= "</tr>";


				if ($counter == $break)
				{
					$output .= "</table></td><td valign=\"top\"><table cellpadding=0 cellspacing=0 border=0 width='100%'>";
					$counter = -1;
				}
				$counter++;
			}
		}
        }
        $output .= "</table></td></tr></table>";

	  if ($form_type == "form")
	  {
		$output .= "<input type=\"hidden\" name=\"recordid\" value=\"$recordid\">";
		$output .= "<input type=\"hidden\" name=\"recordtable\" value=\"$recordtable\">";
		$output .= "<br><input type=\"submit\" name=\"SubmitButton\" class=\"submit\" id=\"SubmitButton\" value=\"$submitlabel\" onclick=\"return submitForm(form);\">";
	   }
    return $output;
}


/* vim: set expandtab: */
?>
