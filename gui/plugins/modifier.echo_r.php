<?php
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.
/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     echo_r
 * Purpose:  return the result of print_r on $string
 * -------------------------------------------------------------
 */
function smarty_modifier_echo_r($string)
{
	echo "<pre>";
	print_r($string);
	echo "</pre>";

}

/* vim: set expandtab: */

?>
