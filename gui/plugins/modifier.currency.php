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
 * Name:     currency
 * Purpose:  format number as currency
 * -------------------------------------------------------------
 */
function smarty_modifier_currency($number)
{
	return sprintf( "%.2f", $number );
}

?>
