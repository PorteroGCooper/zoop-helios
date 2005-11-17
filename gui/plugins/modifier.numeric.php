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
 * Name:     numeric
 * Purpose:  strip out all non-numeric characters from string
 * -------------------------------------------------------------
 */
function smarty_modifier_numeric($number)
{
    $tmp = $number;
    $tmp2 = "";
    
    while ($tmp2 != $tmp)
    {
        $tmp2 = $tmp;
        $tmp = ereg_replace("[^0-9.]+", "", $tmp2);
    }
    
    return($tmp);
}

?>
