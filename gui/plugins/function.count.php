<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     count
 * Name:     count
 * Purpose:  return the size of an array
 * -------------------------------------------------------------
 */
function smarty_function_count($params, &$smarty)
{
    extract($params);
	
    if (empty($var)) {
        $smarty->trigger_error("assign: missing 'var' parameter");
        return;
    }
	
	if (empty($array)) {
        $smarty->trigger_error("assign: missing 'array' parameter");
        return;
    }
	
	$smarty->assign($params["var"], count($array));
}

/* vim: set expandtab: */

?>
