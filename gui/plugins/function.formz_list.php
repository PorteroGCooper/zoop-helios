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

/**
 * Smarty plugin for rendering Formz lists
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
function smarty_function_formz_list($params, &$smarty) {
	if (!isset($params['form'])) return;
	$form = $params['form'];

	$use_softdelete = $form->isSoftDeletable();
	$lotsa_classes = Config::get('zoop.formz.lotsa_classes');
	$tablename = strtolower($form->tablename);
	$zone_path = $smarty->get_template_vars('ZONE_PATH');
	$sortable = $form->isSortable();
	$searchable = $form->isSearchable();
		
	$html = "\n\n";

	// TODO Make this pretty.
	if ($searchable && $form->getSearchForms() !== null) {
		$html .= '<form class="formz-search-form">' . '<input id="search_text" name="q" type="text">&nbsp;' . 
				'<input id="search_button" name="search_button" type="button" value="Search">' . '</form>';
	}

	// figure out class names for this form.
	$form_classes = (isset($form->display['class'])) ? $form->display['class'] : array();
	if (!is_array($form_classes)) $form_classes = explode(' ', $form_classes);
	$form_classes[] = 'formz';
	$form_classes[] = 'formz-list';
	if ($sortable) $form_classes[] = 'sortable';
	
	if ($lotsa_classes) {
		$form_classes[] = 'formz-' . strtolower($form->tablename);
	}	
	if ($form->editable) {
		$form_action = (isset($form->callback) && $form->callback != '') ? ' action="' . $form->callback .'"' : '';
		$form_classes[] = 'formz-editable';
		$form_classes[] = 'formz-list-editable';
		$html .= '<form'. $form_action .' method="post" class="'. implode(' ', $form_classes) .'" id="formz_'. $tablename . '_list">';
	}
	else {
		$html .= '<div class="formz formz-list '. implode(' ', $form_classes) .'" id="formz_'. $tablename . '_list">';
	}
	$html .= "\n<table>";
	
	$fields = $form->getFields(false);

	$current_sort = $form->getSortField();
	$current_order = $form->getSortOrder();
	// build the table header
	$row = array();
	foreach ($fields as $key => $field) {
		if (isset($fields[$key]['listshow']) && $fields[$key]['listshow'] == false) {
			unset($fields[$key]);
		} else {
			$label = (isset($fields[$key]['display']['label'])) ? $fields[$key]['display']['label'] : format_label($key);
			
			// If the field is a foreign field, check for a label and if one doesn't exist then offer a sane default
			if (!isset($fields[$key]['display']['label']) && strchr($key, '.') !== false) {
				$label = format_label(str_replace('.', ' ', $key));
			}
			
			// handle all the sorting magick.
			if ($sortable) {

				// if this column is not sortable, skip it (and apply js metadata to tell sorter to skip it)
				if (isset($fields[$key]['sortable']) && !$fields[$key]['sortable']) {
					$row[] = '<th class="{sorter: false}">' . format_label($label) . '</th>';
				} else {
					$href = $zone_path . '?sort=' . $key;
				
					// If this is the current sort field, add a sort direction and class for styling.
					if ($key == $current_sort) {
						if ($current_order == 'ASC') {
							$href .= '&order=desc';
							$th_class = 'headerSortDown';
						} else {
							$th_class = 'headerSortUp';
						}
					} else {
						$th_class = 'header';
					}
					$row[] = '<th class="'. $th_class .'"><a href="'. url($href) .'">' . format_label($label) . '</a></th>';
				}
			} else {
				$row[] = '<th>' . format_label($label) . '</th>';
			}
		}
	}
	
	$rowActionColumnThreshold = Config::get('zoop.formz.rowaction.column_threshold');
	$rowActions = $form->getRowActions();
	
	if (count($rowActions) > $rowActionColumnThreshold) {
		$row[] = '<th>Actions</th>';
	} else {
		foreach ($rowActions as $key => $rowAction) {
			$row[] = '<th>' . $rowAction['label'] . '</th>';
		}
	}
	
	$html .= "\n\t<thead>\n\t\t<tr>\n\t\t\t";
	$html .= implode("\n\t\t\t", $row);
	$html .= "\n\t\t</tr>\n\t</thead>\n";

	// grab the field names we care about
	$field_names = array_keys($fields);
	$id_field = $form->getIdField();
	// and the slug field, just for fun.
	if ($sluggable = $form->isSluggable()) {
		$slug_field = $form->getSlugField();
	}
	
	// build the table...
	$html .= "\t<tbody>\n\t\t";
	
	$rows = array();
	$data = $form->getRecords();
	
	$rowIndex = 0;
	foreach ($data as $record) {
		$row = array();
		$row_classes = array();
		$id = $record[$id_field];
		
		if ($use_softdelete && isset($record['deleted']) && $record['deleted']) $row_classes[] = 'deleted';
		
		foreach ($field_names as $field) {
			if (strchr($field, '.') !== false) {
				$value = $form->getValue($field, $id);
				if (is_array($value)) {
					// if (isset($fields[$field]['listexpand']) && $fields[$field]['listexpand']) {
					// 	$tmp = '<table>';
					// 	foreach ($value as $entry) {
					// 		$tmp .= '<tr><td>' . $entry . '</td></tr>';
					// 	}
					// 	$tmp .= '</table>';
					// 	$value = $tmp;
					// } else {
						$value = implode(', ', $value);
					// }
				}
			} else if (isset($record[$field])) {
				$value = $record[$field];
			} else if (isset($fields[$field]['display']['default'])) {
				$value = $fields[$field]['display']['default'];
			} else {
				$value = '';	
			}
			
			$field_type = null;
			if (isset($fields[$field]['type'])) {
				$field_type = $fields[$field]['type'];
			}
			
			switch ($field_type) {				
				case 'boolean' :
					$value = $value ? 'true' : 'false';
					$value = '<span class="bool-' . $value . '">' . $value . '</span>';
					break;
			}
			
			$value = (isset($fields[$field]['display']['override'])) ? $fields[$field]['display']['override'] : $value;

			// @todo take care of relations...

			$link_title = (isset($fields[$field]['display']['title'])) ? 'title="' . $fields[$field]['display']['title'] . '" ' : '';

			// create a listlink
			if (isset($fields[$field]['listlink'])) {
				$link = $fields[$field]['listlink'];
				$matches = array();
				preg_match('#%([a-zA-Z_]+?)%#', $link, $matches);
				if (count($matches)) {
					// replace with this table's id field, if applicable.
					if ($matches[1] == 'id') $matches[1] = $id_field;
					if ($sluggable && $matches[1] == 'slug') $matches[1] = $slug_field;
					$link = str_replace($matches[0], urlencode($record[$matches[1]]), $link);
				} else {
					// automatically tack on the id if there's no wildcard to replace
					if (substr($link, -1) != '/') $link .= '/';
					$link .= $record[$id_field];
				}
				$value = '<a ' . $link_title . 'class="listlink ' . $field . '-link" href="' . url($link) . '"><span>' . $value . '</span></a>';
			} else if (isset($fields[$field]['listlinkCallback'])) {
				// deal with the callback...
				$value = '<a ' . $link_title . 'href="' . url(call_user_func($fields[$field]['listlinkCallback'], $id)) . '">' . $value . '</a>';
			}
/*
			// make this bad boy editable...
			else if ($form->editable) {
				die($value);
			}
*/

			$row[] = '<td>' . $value . '</td>';
			
			$rowActions = $form->getRowActions();
			if (count($rowActions)) {
				$id_field = $form->getIdField();
				$value = '';
				if (count($rowActions)>$rowActionColumnThreshold) {
					$value .= '<td>';
					foreach ($rowActions as $key => $rowAction) {
						$value .= ' ';
						if ($rowAction['type'] == 'link') {
							$link = $id . $rowAction['link'];
							$value .= '<a class="'.$rowAction['class'].'" href="' . url($zone_path . $link) . '" title="'.$rowAction['title'].'"><span>' . $rowAction['label'] . '</span></a>';
						} else {
							$control = GuiControl::get('button', $key);
							$control->setParams($rowAction);
							$value .= '<span>' . $control->render() . '</span>';
						}
					}
					$value .= '</td>';
				} else {
					$value = array();
					foreach ($rowActions as $key => $rowAction) {
						if ($rowAction['type'] == 'link') {
							$link = $id . $rowAction['link'];
							$value[] = '<td><a class="'.$rowAction['class'].'" href="' . url($zone_path . $link) . '" title="'.$rowAction['title'].'"><span>' . $rowAction['label'] . '</span></a></td>';
						} else {
							$control = GuiControl::get('button', $key);
							$control->setParams($rowAction);
							$value[] = '<td><span>' . $control->render() . '</span></td>';
						}
					}
					$value = implode("\n\t\t\t", $value);
				}
			}
		}
		
		if (!empty($value) && !empty($rowActions)) {
			$row[] = $value;
		}
		
		if ($lotsa_classes) $row_classes[] = ($rowIndex % 2 == 0) ? 'even' : 'odd';
		$class = (count($row_classes)) ? ' class="' . implode(' ', $row_classes) . '"' : '';
		$rows[] = "<tr" . $class . "\">\n\t\t\t" . implode("\n\t\t\t", $row) . "\n\t\t</tr>\n";
		$rowIndex++;
	}
	
	
	// now add the form list actions
	$actions = $form->getListActions();
	if (count($actions)) {
		$id_field = $form->getIdField();
		
		// add all list actions to an array (we'll implode them later and put 'em in a row...)
		$list_actions = array();
		foreach ($actions as $key => $action) {
			if ($action['type'] == 'link') {		
				$link = $action['link'];
				$matches = array();
				preg_match('#%([a-zA-Z_]+?)%#', $link, $matches);
				if (count($matches)) {
					// replace with this table's id field or slug field, if applicable.
					if ($matches[1] == 'id') $matches[1] = $id_field;
					if ($sluggable && $matches[1] == 'slug') $matches[1] = $slug_field;
					if (!isset($data[$matches[1]])) $data[$matches[1]] = '0';
					$link = str_replace($matches[0], urlencode($data[$matches[1]]), $link);
				} else {
					// automatically tack on the id if there's no wildcard to replace.
					// i don't like this one.
					if (substr($link, -1) != '/') $link .= '/';
					$action['link'] .= $data[$id_field];
				}
				$list_actions[] = '<a href="' . url($link) . '">' . $action['label'] . '</a>';
			} else if ($action['type']=='paginate') {
			

			/*
				// get the pagination format:
				$format = Config::get('zoop.formz.paginate.format');
				$delimiter = Config::get('zoop.formz.paginate.format_delimiter');
				
				$chunks = explode($delimiter, $format);
				
				die_r($chunks);
				$page_links = array();
				
				// grab first, prev

				while ($chunk = array_shift($chunks)) {
					if (is_integer($chunk)) {
						
					} else if {
						strpos()
					}
				}
			*/
			
			
				$page_links = array();
				$page_count = $form->getPageCount();
				
				if ($page_count == 1) continue;
				
				$format = Config::get('zoop.formz.paginate.format');
				
				if ($format['first']) {
					if ($action['page'] > 1) {
						$page_links[] = '<a class="page-first" href="' . url($zone_path) . '" title="First page">' . $format['first'] .'</a>';
					} else {
						$page_links[] = '<span class="page-first">' . $format['first'] .'</span>';
					}
				}
				if ($format['prev']) {
					if ($action['page'] > 1) {
						$page_links[] = '<a class="page-prev" href="' . url($zone_path) . '?page=' . ($action['page'] - 1) . '" title="Previous page">' . $format['prev'] . '</a>';
					} else {
						$page_links[] = '<span class="page-prev">' . $format['prev'] . '</span>';
					}
				}
				
			/*
				// deal with the page links in the middle of the pagination format array.
				if (is_array($format['mid']) && count($format['mid'])) {
					if (in_array('...', $format['mid'])) {
						
					}
				}
			*/
				
				if ($format['next']) {
					if ($page_count > $action['page']) {
						$page_links[] = '<a class="page-next" href="' . url($zone_path) . '?page=' . ($action['page'] + 1) . '" title="Next page">'. $format['next'] .'</a>';
					} else {
						$page_links[] = '<span class="page-next">'. $format['next'] .'</span>';
					}
				}
				if ($format['last']) {
					if ($action['page'] < $page_count) {
						$page_links[] = '<a class="page-last" href="' . url($zone_path) . '?page=' . $page_count . '" title="Last page">'. $format['last'] .'</a>';
					} else {
						$page_links[] = '<span class="page-last">'. $format['last'] .'</span>';
					}
				}
				if (count($page_links)) {
					$list_actions[] = '<span class="formz-paginate">' . implode(Config::get('zoop.formz.paginate.format_delimiter'), $page_links) . '</span>';
				}
			} else {
				$control = GuiControl::get('button', $key);
				$control->setParams($action);			
				$list_actions[] = $control->render();
			}
		}
		
		if (count($list_actions)) {
			$action_html = implode(Config::get('zoop.formz.list_actions.separator'), $list_actions);
			
			if (count($rowActions) > $rowActionColumnThreshold) {
				$totalRows = count($fields) + 1;
			} else { 
				$totalRows = count($fields) + count($rowActions);
			}

			$actionRow = "<tr class=\"action-row\">\n\t\t\t<td colspan=\"" . $totalRows . "\">" . $action_html . "</td>\n\t\t</tr>\n";
			switch(Config::get('zoop.formz.list_actions.position')) {
				case 'top':
					array_unshift($rows, $actionRow);
					break;
				case 'bottom':
					$rows[] = $actionRow;
					break;
				case 'both':
				default:
					array_unshift($rows, $actionRow);
					$rows[] = $actionRow;
					break;
			}
		}
	}
	$html .= implode("\n\t\t", $rows);
	$html .= "\t</tbody>\n</table>\n";
	$html .= ($form->editable) ? "</form>\n\n" : "</div>\n\n";
	return $html;
}
