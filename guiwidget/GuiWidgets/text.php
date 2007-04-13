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
class guiwidget_text extends GuiWidget
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
					case 'maxlength':
					case 'size':
					case 'type':
					case 'class':
						if ($value != '')
							$attrs[] = "$parameter=\"$value\"";
						break;

					case 'width':
					case 'height':
						if ($value != '')
							$Sattrs[] = "$parameter:$value;";
						break;
				}
			}

		$attrs[] = "style=\"" . implode(' ', $Sattrs) . "\"";
		$attrs = implode(' ', $attrs);

		$idStr = $this->getIdString();
		$value = $this->getValue();

		$html = "<span $idStr $attrs >$value</span>";

		return $html;
	}
}
?>
