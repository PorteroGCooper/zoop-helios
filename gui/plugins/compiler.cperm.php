<?php
/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.cperm.php
 * Type:     compiler
 * Name:     cperm
 * Purpose:  Output header closing perm checks in templates.
 * -------------------------------------------------------------
 */
function smarty_compiler_cperm($tag_arg, &$smarty)
{
	return "}";
}
?>