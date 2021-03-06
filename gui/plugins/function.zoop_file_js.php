<?php
/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     zoop_image
 * Purpose:  write html to include a image from the zoop framework
 * -------------------------------------------------------------
 */
function smarty_function_zoop_file_js($params, &$smarty)
{
	$base_href = $smarty->_tpl_vars['SCRIPT_URL'];
    	extract($params);
	if(!isset($component))
		$component = 'gui';

 	foreach (explode(',', $files) as $file){
		$file = trim($file);
   		echo "<script type=\"text/javascript\" src=\"$base_href/zoopfile/$component/$file\"></script>\n";
  	}
}

/* vim: set expandtab: */

?>
