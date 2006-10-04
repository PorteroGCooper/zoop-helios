<?php
/**
* @package gui
* @subpackage guicontrol
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
 * @uses GuiControl
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class text extends GuiControl
{
	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return void
	 */
	function getPersistentParams()
	{
		return array('validate');
	}

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
						if ($value != '')
							$attrs[] = "$parameter=\"$value\"";
						break;
					case 'readonly':
					case 'disabled':
						if ($value)
							$attrs[] = "readonly=\"true\"";
						break;
// 					case 'validate':
// 						$attrs[] = $this->getValidationAttr($this->params['validate']);
// 						break;
					case 'width':
					case 'height':
						if ($value != '')
							$Sattrs[] = "$parameter:$value;";
						break;
				}
			}

		$thistype = get_class($this);
		$attrs[] = "style=\"" . implode(' ', $Sattrs) . "\"";
		$attrs = implode(' ', $attrs);

		$vc = $this->getValidationClasses();
		$ni = $this->getNameIdString();
		$v = $this->getValue();

		$html = "<input class=\"$vc\" $ni $attrs value=\"$v\" type=\"$thistype\">";

		return $html;
	}
}
?>
