<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     assign_array
 * Purpose:  assign a value to a template variable
 * -------------------------------------------------------------
 */
function smarty_function_assign_array($params, &$smarty)
{
    extract($params);

    if (empty($var)) {
        $smarty->trigger_error("assign: missing 'var' parameter");
        return;
    }
    
    if (empty($key)) {
        $smarty->trigger_error("assign: missing 'key' parameter");
        return;
    }

    if (!in_array('value', array_keys($params))) {
        $smarty->trigger_error("assign: missing 'value' parameter");
        return;
    }
	
    $smarty->_tpl_vars[$var][$key] = $value;
}

/* vim: set expandtab: */

?>
