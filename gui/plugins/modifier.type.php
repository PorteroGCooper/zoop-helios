<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty type modifier plugin
 *
 * Type:     modifier<br>
 * Name:     type<br>
 * Purpose:  get the variable type
 * @param mixed
 * @return string
 */
function smarty_modifier_type($var)
{
    return GetType($var);
}

?>
