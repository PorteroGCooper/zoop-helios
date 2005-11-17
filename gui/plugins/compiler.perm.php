<?php
/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     compiler.perm.php
 * Type:     compiler
 * Name:     perm
 * Purpose:  Output header enabling perm checks in templates.
 * -------------------------------------------------------------
 */
 
function smarty_compiler_perm($tag_arg, &$smarty)
{
    //return 'if( isset($strings[\'perm\'][\'' . $tag_arg . '\']) && UserPerm($strings[\'perm\'][\'' . $tag_arg . '\']) ) {';
	
	return 'if( UserPerm($strings[\'perm\'][\'' . $tag_arg . '\']) ) {';
}
?>