<?php
/**
* @package gui
* @subpackage guiwidget
*/
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
 * text
 *
 * @uses GuiWidget
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class guiwidget_list extends GuiWidget
{
	/**
	 * render
	 *
	 * @access public
	 * @return void
	 */
	function render()
	{
		$attrs = array();
		$Sattrs = array();

		$html = "";

		if (isset($this->params) && !empty($this->params))
			foreach ($this->params as $parameter => $value)
			{
				switch ($parameter) {   // Here we setup specific parameters that will go into the html
					case 'title':
					case 'class':
					case 'id':
					case 'style':
						$value = trim ( $value );
						if ( !empty ( $value ) )
							$attrs[] = "$parameter=\"" . strip_tags ( addSlashes ( $value ) ) . "\"";
						break;
				}
			}

		//$attrs[] = "style=\"" . implode(' ', $Sattrs) . "\""; // DA - remove empty style="" thing
		$attrs = implode(' ', $attrs);
		
		$html = "<ul {$attrs}>\n";
		foreach ( $this->params['list'] as $listItem ) {
			$html .= "<li";
			if ( isset ( $listItem['attrs'] ) ) {
				foreach ( $listItem['attrs'] as $param => $val ) {
					$val = trim ( $val );
					if ( !empty ( $val ) )
						$html .= " {$param}=\"" . addSlashes ( $val ) . "\"";
				}
			}
			$html .= ">";
			$html .= ( empty ( $listItem['content'] ) ? '&nbsp;' : $listItem['content'] );
			$html .= "</li>\n";
		}
		$html .= "</ul>\n";
		
		return $html;
	}
}
?>
