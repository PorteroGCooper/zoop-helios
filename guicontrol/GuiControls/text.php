<?php
/**
* @package gui
* @subpackage guicontrol
*/
// Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.


/**
 * textControl
 *
 * @uses GuiControl
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class textControl extends GuiControl
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

		$html = array();
		
		$html[] = 'input';


		if (isset($this->params) && !empty($this->params)) {
			foreach ($this->params as $parameter => $value) {
				switch ($parameter) {   // Here we setup specific parameters that will go into the html
					case 'title':
					case 'maxlength':
					case 'size':
					case 'type':
						if ($value != '')
							$html[] = "$parameter=\"$value\"";
						break;
					case 'readonly':
					case 'disabled':
						if ($value)
							$html[] = "readonly=\"true\"";
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
		}
			
		if (isset($this->params['type'])) {
			$thistype = $this->params['type'];
		} else {
			$thistype = $this->getType();
		}
		
/* 		die_r($this->params); */

		if (count($Sattrs)) {
			$html[] = "style=\"" . implode(' ', $Sattrs) . "\"";
		}

		$vc = $this->getValidationClasses();
		if (isset($this->params['class']))
			$vc .= " " . $this->params['class'];
		$v = $this->getValue();
		
		$html[] = $this->getNameIdString();
		
		if ($v) {
			$html[] = 'value="' . $v . '"';
		}
		if ($vc != '') {
			$html[] = 'class="' . $vc . '"';
		}
		$html[] = 'type="' . $thistype . '"';
	
		$html[] = '/';
		$html = "<" . implode(' ', $html) . ">"; // "<input $class $ni $attrs value=\"$v\" $type/>";

		return $html;
	}
}
