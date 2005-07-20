<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty strlen modifier plugin
 *
 * Type:     modifier<br>
 * Name:     strlen<br>
 * Purpose:  get string lenght of the variable
 * @param string
 * @return string
 */
function smarty_modifier_strlen($string)
{
    return strlen($string);
}

?>
