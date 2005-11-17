<?php
/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     xslt
 * Purpose:  Parse XML with XSLT to produce output.
 * -------------------------------------------------------------
 */
function smarty_function_xslt($params, &$this)
{
	$xh = xslt_create();
	
	$arguments = array();
	if (isset($params["xml"]))
	{
		$arguments["/_xml"] = $params["xml"];
		$xmlfile = "arg:/_xml";
	}
	else
	{
		$xmlfile = $params["xmlfile"];
	}
	
	if (isset($params["xsl"]))
	{
		$arguments["/_xsl"] = $params["xsl"];
		$xslfile = "arg:/_xsl";
	}
	else
	{
		$xslfile = $params["xslfile"];
	}
	
	if (isset($params["base"]))
	{
		xslt_set_base ( $xh, $params["base"] )
	}
	
    echo xslt_process($xh, $xmlfile, $xslfile, NULL, $arguments);
	xslt_free($xh);
}

/* vim: set expandtab: */

?>
