<?php
/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     formatccn
 * Purpose:  convert string to lowercase
 * -------------------------------------------------------------
 */
function smarty_modifier_formatccn($ccn)
{
    $output = substr($ccn, 0, 4) . "-" . substr($ccn, 4, 4) . "-" . substr($ccn, 8, 4) . "-" . substr($ccn, 12, 4);
    
    return $output;
}

?>
