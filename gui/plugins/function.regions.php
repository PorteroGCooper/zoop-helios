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
		if ($add_region_divs) echo "\n<div id=\"$name\">\n";
		echo $smarty->fetch($template_file);
		if ($add_region_divs) echo "\n</div>\n\n";
	}
}