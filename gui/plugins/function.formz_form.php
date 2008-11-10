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

include_once(dirname(__file__) . "/function.guicontrol.php");

/**
 * smarty_function_formz_form
 *
 * @param array $params
 * @param Smarty $smarty
 * @access public
 * @return string rendered HTML output from the form passed in $params
 */
function smarty_function_formz_form($params, &$smarty) {
	if (!isset($params['form'])) return;
	
	$lotsa_classes = Config::get('zoop.formz.lotsa_classes');
	
	$form = $params['form'];
	$form_items = array();
	
	$content = "\n\n";

	$tablename = strtolower($form->tablename);

	$record_id = $form->record_id;
	$fields = $form->getFields();
	$data = $form->getData();
//	$relations = $form->getRelations();
	
	// figure out class names for this form item.
	$form_classes = (isset($form->display['class'])) ? $form->display['class'] : array();
	if (!is_array($form_classes)) $form_classes = explode(' ', $form_classes);
	$form_classes[] = 'formz';
	if ($lotsa_classes) {
		$form_classes[] = 'formz-' . strtolower($form->tablename);
	}
	
	if ($form->editable) {
		$form_action = (isset($form->callback) && $form->callback != '') ? ' action="' . $form->callback .'"' : '';
		$form_classes[] = 'formz-editable';
		$form_items[] = '<form'. $form_action .' method="post" class="'. implode(' ', $form_classes) .'" id="formz_'. $tablename . '_' . $record_id .'">';
	} else {
		$form_items[] = '<div class="formz '. implode(' ', $form_classes) .'" id="formz_'. $tablename . '_' . $record_id .'">';
	}
	$i = -1;
	foreach ($fields as $key => $field) {
		$i++;
		// skip ones we don't want on the form...
		if (isset($field['formshow']) && $field['formshow'] == false) continue;
		
		if (isset($field['display']['type']))
			$type = $field['display']['type'];
		else if (isset($field['length']) && $field['length'] > 1024) {
			// 'long' strings need to be textareas, not text fields.
			$type = 'textarea';
		} else {			
			$type = 'text';
		}
		
		$value = isset($data[$key]) ? $data[$key] : '';

		// prob'ly a bit ghetto...
		if ($key == 'id' && $form->record_id == 'new') $value = 'new';
		
		// get a new guiControl to deal with this
		$control = &getGuiControl($type, $key);
		if ($form->editable) {
			// grab the default value, if one isn't set.
			if (strlen($value) == 0 && isset($field['default'])) {
				$value = $field['default'];
			}
		} else {
			
		}
		
		$value = (isset($field['display']['override'])) ? $field['display']['override'] : $value;
		
		// figure out class names for this form item.
		$form_item_classes = (isset($field['display']['class'])) ? $field['display']['class'] : array();
		if (!is_array($form_item_classes)) $form_item_classes = explode(' ', $form_item_classes);
		if ($lotsa_classes) {
			$form_item_classes[] = 'formz-field-' . $key;
			$form_item_classes[] = 'formz-' . $type;
		}
		$field['display']['class'] = implode(' ', $form_item_classes);

		if ($form->editable) $value = htmlspecialchars($value);
		$control->setParam('value', $value);
		if (isset($field['display'])) $control->setParams($field['display']);
		
		$form_item = '';
		
		if (!isset($field['display']['type']) || $field['display']['type'] != 'hidden') {
			$label = (isset($field['display']['label'])) ? $field['display']['label'] : Formz::format_label($key);

			$required = (isset($field['required']) && $field['required']) ? '<span class="required" title="Required">*</span>' : '';
		
			$titlestr = (isset($field['display']['title'])) ? ' title="'. $field['display']['title'] .'"' : '';
			$form_item .= '<label for="' . $control->getLabelName() .'"' .$titlestr. '>' . $label . $required . '</label>';
		}
		
		if (isset($field['display']['caption']))
			$form_item .= '<span class="caption">' . $field['display']['caption'] . '</span>';

		if ($form->editable) {
			$control_html = $control->render();
		} else {
			$control_html = '<div class="' . implode(' ', $form_item_classes) . '">' . $control->view() . '</div>';
		}
		
		$form_item .= '<div class="form-item-content">' . $control_html . '</div>';
		
		$form_item_classes[] = 'form-item';
		$form_items[] = '<div class="' . implode('-wrapper ', $form_item_classes) . '">' . $form_item . '</div>';
	}

	// now add the form actions	
	if ($form->editable) {
		$id_field = $form->getIdField();
		$actions = $form->getActions();
		foreach ($actions as $key => $action) {
			if ($action['type'] == 'link') {
				
				$link = $action['link'];
				$matches = array();
				preg_match('#%([a-zA-Z_]+?)%#', $link, $matches);
				if (count($matches)) {
					// replace with this table's id field, if applicable.
					if ($matches[1] == 'id') $matches[1] = $id_field;
					$link = str_replace($matches[0], urlencode($data[$matches[1]]), $link);
				} else {
					// automatically tack on the id if there's no wildcard to replace
					if (substr($link, -1) != '/') $link .= '/';
					$action['link'] .= $data[$id_field];
				}
				$value = '<a href="' . $link . '">' . $action['label'] . '</a>';

			} else {
				$control = &getGuiControl('button', $key);
				$control->setParams($action);			
				$form_items[] = $control->render();
			}
		}
	}
	
	$content .= implode("\n\t", $form_items);
	$content .= ($form->editable) ? "\n</form>\n\n" : "\n</div>\n\n";

	echo $content;
}