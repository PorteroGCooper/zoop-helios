<?php

/**
 * Display all (or a single) gui region.
 *
 * @code
 *   {regions}
 *   {regions name='header'}
 * @endcode
 *
 * @ingroup gui
 * @ingroup smarty
 */
function smarty_function_regions($params, &$smarty) {
	$regions = (isset($smarty->_regions)) ? $smarty->_regions : array();

	$html = '';
	
	if (isset($params['name'])) {
		if (isset($regions[$params['name']])) {
			$regions = array($params['name'] => $regions[$params['name']]);
			unset($smarty->_regions[$params['name']]);
		} else {
			$regions = array();
		}
	}
	
	$add_region_divs = Config::get('zoop.gui.add_region_divs');
	
	foreach ($regions as $name => $template_file) {
		if ($add_region_divs) $html .= "\n<div id=\"$name\">\n";
		if (isset($smarty->_renderedRegions[$name])) {
			$html .= $smarty->_renderedRegions[$name];
		} else {
			$html .= $smarty->fetch($template_file);
		}
		if ($add_region_divs) $html .= "\n</div>\n\n";
	}
	
	return $html;
}