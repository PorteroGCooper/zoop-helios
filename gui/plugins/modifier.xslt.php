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
 * Name:     xslt
 * Purpose:  Translate the variable using specified XSL file.
 * -------------------------------------------------------------
 */
function smarty_modifier_xslt($xml, $xslfile = '')
{
	$xh = xslt_create();
    $arguments = array(
         '/_xml' => $xml
    );
    return xslt_process($xh, 'arg:/_xml', $xslfile, NULL, $arguments);
	xslt_free($xh);
}

//die("fdsjklfasad");

/* vim: set expandtab: */

?>
