<?
/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     block.translate.php
 * Type:     block
 * Name:     translate
 * Purpose:  translate a block of text
 * -------------------------------------------------------------
 */
function smarty_block_perm($params, $content, &$smarty)
{
    if (isset($content)) {
	    extract($params);
        if( UserPerm( $perm ) )
        {
	        echo($content);
    	}
    }
}
?>