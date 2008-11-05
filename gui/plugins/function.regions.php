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
		} else {
			$regions = array();
		}
	}
	
	foreach ($regions as $name => $template_file) {
		echo "\n<div id=\"$name\">\n";
		echo $smarty->fetch($template_file);
		echo "\n</div>\n\n";
	}
}