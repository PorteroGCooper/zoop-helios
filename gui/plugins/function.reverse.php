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
 * Name:     reverse
 * Purpose:  reverse sort an array on it's keys and assign it
 * -------------------------------------------------------------
 */
function smarty_function_reverse($params, &$smarty)
{
    extract($params);

    if (empty($array)) {
        $smarty->trigger_error("assign: missing 'array' parameter");
        return;
    }

    if (!in_array('new', array_keys($params))) {
        $smarty->trigger_error("assign: missing 'new' parameter");
        return;
    }
	
	$array = array_flip($array);
	
	arsort($array);
	
	$array = array_flip($array);
	
    $smarty->assign($new, $array);
}

/* vim: set expandtab: */

?>
