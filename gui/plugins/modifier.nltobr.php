<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     nltobr
 * Purpose:  makes newline chars <br> tags
 * -------------------------------------------------------------
 */
function smarty_modifier_nl2br($string)
{
    return nl2br($string);
}

/* vim: set expandtab: */

?>
