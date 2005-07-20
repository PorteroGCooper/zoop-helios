<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     date
 * Purpose:  format timestamps via date
 * -------------------------------------------------------------
 */
function smarty_modifier_date($string, $format="d-m-Y")
{
    return date($format, $string);
}

/* vim: set expandtab: */

?>
