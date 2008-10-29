<?php
/**
 * Zoop Smarty plugin
 * @group gui
 * @group plugins
 * @group Formz
 *
 * @author Justin Hileman
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

// include_once(dirname(__file__) . "/function.guicontrol.php");

/**
 * smarty_function_formz_form
 *
 * @param array $params
 * @param Smarty $smarty
 * @access public
 * @return string rendered HTML output from the form passed in $params
 */
function smarty_function_formz_list($params, &$smarty) {
	if (!isset($params['form'])) return;
	
	$lotsa_classes = Config::get('zoop.formz.lotsa_classes');

	$html = '';
	$html .= "\n\n<table>";
	$form = $params['form'];
	
	$fields = $form->getFields();
	
	// build the table header
	$row = array();
	foreach ($fields as $key => $field) {
		if (isset($fields[$key]['listshow']) && $fields[$key]['listshow'] == false) {
			unset($fields[$key]);
		} else {
			$label = (isset($fields[$key]['display']['label'])) ? $fields[$key]['display']['label'] : Formz::format_label($key);
			$row[] = '<th>' . Formz::format_label($label) . '</th>';
		}
	}
	$html .= "\n\t<thead>\n\t\t<tr>\n\t\t\t";
	$html .= implode("\n\t\t\t", $row);
	$html .= "\n\t\t</tr>\n\t</thead>\n";

	// grab the field names we care about
	$field_names = array_keys($fields);
	$id_field = $form->getIdField();
	
	// build the table...
	$html .= "\t<tbody>\n\t\t";
	
	$rows = array();
	$data = $form->getRecords();
	
	foreach ($data as $record) {
		$row = array();

	
		$id = $record[$id_field];
		
		foreach ($field_names as $field) {
			if (isset($record[$field])) {
				$value = $record[$field];
			} else if (isset($fields[$field]['display']['default'])) {
				$value = $fields[$field]['display']['default'];
			} else {
				$value = '';
			}
			
			$value = (isset($fields[$field]['display']['override'])) ? $fields[$field]['display']['override'] : $value;

			// @todo take care of relations...

			// create a listlink
			if (isset($fields[$field]['listlink'])) {
				$link = $fields[$field]['listlink'];
				$matches = array();
				preg_match('#%([a-zA-Z_]+?)%#', $link, $matches);
				if (count($matches)) {
					// replace with this table's id field, if applicable.
					if ($matches[1] == 'id') $matches[1] = $id_field;
					$link = str_replace($matches[0], urlencode($record[$matches[1]]), $link);
				} else {
					// automatically tack on the id if there's no wildcard to replace
					if (substr($link, -1) != '/') $link .= '/';
					$link .= $record[$id_field];
				}
				$value = '<a href="' . $link . '">' . $value . '</a>';
			} else if (isset($fields[$field]['listlinkCallback'])) {
				// deal with the callback...
				$value = '<a href="' . call_user_func($fields[$field]['listlinkCallback'], $id) . '">' . $value . '</a>';
			}
			
			$row[] = '<td>' . $value . '</td>';
			
			
		}
		$rows[] = "<tr>\n\t\t\t" . implode("\n\t\t\t", $row) . "\n\t\t</tr>\n";
	}
	

	$html .= implode("\n\t\t", $rows);
	$html .= "\t</tbody>\n</table>\n\n";

	return $html;
}
