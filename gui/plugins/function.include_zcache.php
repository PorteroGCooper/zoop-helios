<?php

/**
* Zoop Smarty plugin
* @package Smarty
* @subpackage plugins
*/

/**
* Smarty {include_zcache} function plugin
*
* Includes a template using private caching parameters. Must be registered as non-caching.
*
* Adapted from clipcache written by boots
*
* @file        function.include_zcache.php
* @version     0.2.0 2007-Feb-09
* @since       2005-APR-08
*
* @author      Steve Francia {steve@takkle.com}
* @author      Andrew Hayward {andrew@takkle.com}
* @author      boots {jayboots ~ yahoo com}
* @copyright  takkle inc.,brainpower, boots, 2004-2007
* @license     LGPL 2.1
* @link        http://www.phpinsider.com/smarty-forum/viewtopic.php?p=19733#19733
* @link        http://www.zoopframework.com/

*
* @param array $params
* @param Smarty $smarty
*
* This function observes the following tag attributes (in $params):
*
* #param file required template file
* #param cache_id required specify cache build group
* #param cache_lifetime required time to live for template part/group
* #param ldelim optional specify the left delimiter to use for included content
* #param rdelim optional specify the right delimiter to use for included content
*/
function smarty_function_include_zcache($params, &$smarty)
{
// validation
foreach ( array( 'cache_id', 'file', 'cache_lifetime' ) as $required ) {

	if ( !array_key_exists( $required, $params ) ) {
		$smarty->trigger_error( "include_zcache: '$required' param missing. Aborted.", E_USER_WARNING );

		return;
	}
}

// handle optional delimiters
foreach ( array( 'rdelim'=>$smarty->right_delimiter, 'ldelim'=>$smarty->left_delimiter) as $optional=>$default ) {
	${"_{$optional}"} = $default;
	$$optional = ( array_key_exists( $optional, $params ) )
		? $params[$optional]
		: $default;
}

// save smarty environment as proposed by calling template
$_cache_lifetime = $smarty->cache_lifetime;
$smarty->cache_lifetime = $params['cache_lifetime'];
$_caching = $smarty->caching;
$smarty->caching = 2;
$smarty->left_delimiter = $ldelim;
$smarty->right_delimiter = $rdelim;

// run the requested zcache template
$tpl_file = $params['file'];

$content = $smarty->rootFetch( $tpl_file, $params['cache_id'] );

// restore smarty environment as proposed by calling template
$smarty->caching = $_caching;
$smarty->cache_lifetime = $_cache_lifetime;
$smarty->left_delimiter = $_ldelim;
$smarty->right_delimiter = $_rdelim;

return $content;
}
?>