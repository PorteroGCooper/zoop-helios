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
function smarty_function_js_zoop_file($params, &$smarty)
{
	$base_href = $smarty->_tpl_vars['BASE_HREF'];
    	extract($params);

 	foreach (explode(',', $files) as $file){
		$file = trim($file);
   		echo "<script type=\"text/javascript\" src=\"$base_href/zoopfile/gui/$file\"></script>\n";
  	}
}

/* vim: set expandtab: */

?>
