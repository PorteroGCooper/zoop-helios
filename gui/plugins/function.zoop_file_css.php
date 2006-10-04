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
function smarty_function_zoop_file_css($params, &$smarty)
{
	$base_href = $smarty->_tpl_vars['BASE_HREF'];
    	extract($params);

 	foreach (explode(',', $files) as $file){
		$file = trim($file);
		echo "<link href=\"$base_href/zoopfile/gui/css/$file\" rel=\"stylesheet\" type=\"text/css\">\n";
  	}
}

/* vim: set expandtab: */

?>
