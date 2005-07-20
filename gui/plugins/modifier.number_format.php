<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     number_format
 * Purpose:  format strings via sprintf
 * -------------------------------------------------------------
 */
function smarty_modifier_number_format($string, $decimal_place=2, $decimal_place_string=".", $thousands_string=",")
{
    return number_format($string, $decimal_place, $decimal_place_string, $thousands_string);
}

/* vim: set expandtab: */

?>
