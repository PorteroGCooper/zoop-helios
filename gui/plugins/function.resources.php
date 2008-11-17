<?php

/**
 * Zoop resource link Smarty plugin.
 *
 * Resource will automatically link to css and js resources assigned to the $gui.
 * If a css or js file (or array of files) is passed, will link to those instead.
 *
 * @code
 *   {resources}
 *   {resources css=$foo js=$foo}
 * @endcode
 *
 * @see gui::add_js
 * @see gui::add_css
 * @ingroup gui
 * @ingroup smarty
 */
function smarty_function_resources($params, &$smarty) {

	// if either css or js resources are given, use those...
	if (isset($params['css']) || isset($params['js'])) {
		if (isset($params['css'])) {
			$css = $params['css'];
			if (!is_array($css)) {
				$css = array($css);
			}
		} else {
			$css = array();
		}
		if (isset($params['js'])) {
			$js = $params['js'];
			if (!is_array($js)) {
				$js = array($js);
			}
		} else {
			$js = array();
		}
	} else {
		// automatically grab all the assigned css and js files
		$zoopCss = (isset($smarty->_zoopCss)) ? $smarty->_zoopCss : array();
		$appCss = (isset($smarty->_appCss)) ? $smarty->_appCss : array();
		$css = array_merge($zoopCss, $appCss);
		
		$zoopJs = (isset($smarty->_zoopJs)) ? $smarty->_zoopJs : array();
		$appJs = (isset($smarty->_appJs)) ? $smarty->_appJs : array();
		$js = array_merge($zoopJs, $appJs);
		
		// add inline js stuff here...
		$inline_js = isset($smarty->_inlineJs) ? $smarty->_inlineJs : array();
	}

	// spit 'em out.
	foreach ($css as $file) {
		echo '<link rel="stylesheet" type="text/css" href="' . $file . "\" />\n";
	}
	foreach ($js as $file) {
		echo '<script type="text/javascript" src="' .$file . "\"></script>\n";
	}
	if (count($inline_js)) {
		echo '<script type="text/javascript">';
		echo "\n//<![CDATA[\n\t";
		echo implode("\n\t", $inline_js);
		echo "\n//]]>\n</script>\n";
	}
}