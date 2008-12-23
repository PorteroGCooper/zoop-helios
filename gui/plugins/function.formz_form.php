<?php

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
 * Smarty plugin to render Formz objects as HTML forms (edit and view).
 *
 * @ingroup gui
 * @ingroup plugins
 * @ingroup Formz
 * @author Justin Hileman {@link http://justinhileman.com}
 *
 * @param array $params
 * @param Smarty $smarty
 * @access public
 * @return string rendered HTML output from the form passed in $params
 */
function smarty_function_formz_form($params, &$smarty) {
	if (!isset($params['form'])) return;

	$lotsa_classes = Config::get('zoop.formz.lotsa_classes');
	$zone_path = $smarty->get_template_vars('ZONE_PATH');
	$zone_base_path = $smarty->get_template_vars('ZONE_BASE_PATH');
	
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

	// add a form token first.
	// $form_items[] = GuiControl::get('FormToken', 'token')->renderControl();

	foreach ($fields as $key => $field) {
		$i++;
		// skip ones we don't want on the form...
		if (isset($field['formshow']) && $field['formshow'] == false) continue;
		if (isset($field['relation_alias']) && $form->getParentTablename() === $field['relation_alias']) continue;
		
		if (isset($field['embedFormz'])) {
			$label = (isset($field['display']['label'])) ? $field['display']['label'] : format_label($key);
			$form_item = '<div class="formz-field-'.strtolower($key).'-wrapper embedded-formz-wrapper form-item">';
		
			$formz_object = $form->getEmbeddedFormz($field['relation_alias']);
			
			if ($field['rel_type'] == Formz::ONE) {
				if (isset($data[$key])) {
					$formz_object->getRecord($data[$key]);
				}
			} else {
				$form_item .= '<h3>' . $label . '</h3>';
			}
			$formz_object->setEditable(false);
			$form_item .= '<div class="form-item-content">';
			$form_item .= smarty_function_formz(array('form' => $formz_object), $smarty);
			$form_item .= '</div></div>';
			$form_items[] = $form_item;
			continue;
		}
		
		/**
		 * figure out the 'required' logic
		 *
		 * if the field was set required (or not) using formz::setFieldRequired(), this will trump everything.
		 * otherwise, defer to the validation set for this field.
		 *
		 */
		if (!isset($field['required'])) {
			if (isset($field['display']['validate']) && isset($field['display']['validate']['required'])) {
				$field['required'] = $field['display']['validate']['required'];
			} else {
				$field['required'] = false;
			}
		}
		
		// now force the validation on the guicontrol.
		$field['display']['validate']['required'] = $field['required'];

		// guess what type of guicontrol to use on this bad boy
		if (isset($field['display']['type'])) {
			$type = $field['display']['type'];
		} else {
			// Autodetect password fields, make 'em display as password guiControls.
			if ($key == 'password' || $key == 'pass') {
				$type = 'password';
			} else {
				// these ones are text type
				if (isset($field['length']) && $field['length'] > 1024) {
					// 'long' strings need to be textareas, not text fields.
					$type = 'textarea';
				} else {			
					$type = 'text';
				}
			}
		}
		
		$label = (isset($field['display']['label'])) ? $field['display']['label'] : format_label($key);
		$value = isset($data[$key]) ? $data[$key] : '';
		
		$field_type = null;
		if (isset($field['type'])) {
			$field_type = $field['type'];
		}
		
		// do any 'field type' specific changes
		switch($field_type) {
			case 'boolean':
			case 'bool':
				if ($form->editable) {
					$value = $value ? true : false;
					$type = 'checkbox';
				} else {
					$value = $value ? 'true' : 'false';
					$value = '<span class="bool-' . $value . '">' . $value . '</span>';
				}
				break;
			case 'relation':
				if (!isset($field['display']['index'])) {
					$field['display']['index'] = $form->getTableRelationValues($key);
				}
				
				// decide whether this should be single or multiple select
				if ($field['rel_type'] == Formz::ONE) {
					if (!isset($fields[$key]['display']['type'])) {
						$type = 'select';
					}
					if ($form->editable && !$field['required']) {
						// TODO remove this field if something's already selected in this dropdown.
						$null_val = Config::get('zoop.formz.select_null_value');
						$null_val = str_replace('%field%', $label, $null_val);
						$field['display']['index'] = array('' => $null_val) + $field['display']['index'];
					}
				} else {
					if (!isset($fields[$key]['display']['type']) || $fields[$key]['display']['type'] === 'relation') {
						if (isset($field['display']['checkboxThreshold'])) {
							$threshold = $field['display']['checkboxThreshold'];
						} else {
							$threshold = Config::get('zoop.formz.checkboxes_threshold');
						}
						if (count($field['display']['index']) > $threshold) {
							$type = 'multiple';
						} else {
							$type = 'checkboxes';
						}
					}
				}
				break;
		}

		if ($key == $form->getIdField() && $form->record_id == 'new') $value = 'new';
		
		// get a new guiControl to deal with this
		$control = GuiControl::get($type, $key);
		if ($form->editable) {
			// grab the default value, if one isn't set.
			if (empty($value) && isset($field['default'])) {
				$value = $field['default'];
			}
		}
		
		if (isset($field['editable']) && !$field['editable']) {
			$field['display']['disabled'] = true;
		}
		
		$value = (isset($field['override'])) ? $field['override'] : $value;
		
		// figure out class names for this form item.
		$form_item_classes = (isset($field['display']['class'])) ? $field['display']['class'] : array();
		if (!is_array($form_item_classes)) $form_item_classes = explode(' ', $form_item_classes);
		if ($lotsa_classes) {
			$form_item_classes[] = 'formz-field-' . $key;
			$form_item_classes[] = 'formz-' . $type;
		}
		$field['display']['class'] = implode(' ', $form_item_classes);

		// TODO Fix this. we need to htmlspecialchars based on guicontrol type.
		/* if ($form->editable) $value = htmlspecialchars($value); */
		
		$control->setParam('value', $value);
		if (isset($field['display'])) $control->setParams($field['display']);
		
		$form_item = '';
		
		if (!isset($field['display']['type']) || $field['display']['type'] != 'hidden') {
			$required = ($field['required']) ? '<span class="required" title="Required">*</span>' : '';
		
			$titlestr = (isset($field['display']['title'])) ? ' title="'. $field['display']['title'] .'"' : '';
			$form_item .= '<label for="' . $control->getLabelName() .'"' .$titlestr. '>' . $label . $required . '</label>';
		}
		
		if (isset($field['display']['caption']))
			$form_item .= '<span class="caption">' . $field['display']['caption'] . '</span>';

		if ($form->editable) {
			$control_html = $control->renderControl();
		} else {
			$control_view = $control->view();
			if (isset($field['formlink']) && !empty($field['formlink'])) {
				$url = $field['formlink'];
				$control_view = '<a href="'. url($url) .'">'. $control_view .'</a>';
			}
			$control_html = '<div class="'. implode(' ', $form_item_classes) .'">'. $control_view .'</div>';
		}
		
		$form_item .= '<div class="form-item-content">' . $control_html . '</div>';
		
		$form_item_classes[] = 'form-item';
		$form_items[] = '<div class="' . implode('-wrapper ', $form_item_classes) . '">' . $form_item . '</div>';
	}

	// now add the form actions	
	if ($form->editable) {
		$id_field = $form->getIdField();
		if ($form->isSluggable()) $slug_field = $form->getSlugField();
		
		$actions = $form->getActions();
		foreach ($actions as $key => $action) {
			if ($action['type'] == 'link') {
				$link = $action['link'];
				$matches = array();
				preg_match('#%([a-zA-Z_]+?)%#', $link, $matches);
				if (count($matches)) {
					// replace with this table's id field, if applicable.
					if ($matches[1] == 'id') $matches[1] = $id_field;
					// replace with this table's slug field, if applicable.
					if ($form->isSluggable() && $matches[1] == 'slug') $matches[1] = $slug_field;
					$link = str_replace($matches[0], urlencode($data[$matches[1]]), $link);
				}
				$form_items[] = '<a href="' . url($link) . '">' . $action['label'] . '</a>';

			} else {
				$control = GuiControl::get('button', $key);
				$control->setParams($action);			
				$form_items[] = $control->renderControl();
			}
		}
	}
	
	$content .= implode("\n\t", $form_items);
	$content .= ($form->editable) ? "\n</form>\n\n" : "\n</div>\n\n";

	return $content;
}
