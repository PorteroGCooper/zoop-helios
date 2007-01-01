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
function smarty_function_zoop_image($params, &$smarty)
{
    	static $id = "default";
    	static $class = "";
	static $type = "full"; // can also be path

	$base_href = $smarty->_tpl_vars['SCRIPT_URL'];
    	extract($params);

	$path = "$base_href/zoopfile/image/$group/$image";

	if ($type == "full")
    		return "<img src='$path' border='0'>";
	else
		return $path;

}

/* vim: set expandtab: */

?>
