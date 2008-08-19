<?php
/**
* Zoop Guicontrol
* @package gui
* @subpackage guicontrol
*
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
 * date
 *
 * @uses GuiControl
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class dateControl extends GuiControl
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

		foreach ($this->params as $parameter => $value)
		{
			switch ($parameter) {   // Here we setup specific parameters that will go into the html
				case 'title':
				case 'maxlength':
				case 'width':
				case 'height':
				case 'size':
				case 'type':
					if ($value != '')
						$attrs[] = "$parameter=\"$value\"";
					break;
				case 'readonly':
					if ($value)
						$attrs[] = "readonly=\"true\"";
				case 'validate':
					$attrs[] = $this->getValidationAttr($this->params['validate']);
					break;
			}
		}

		$value = $this->getValue();
		$attrs = implode(' ', $attrs);
		$label = $this->getLabelName();
		$ni = $this->getNameIdString();


		$html = "<input $attrs value=\"$value\" $ni onfocus=\"show_Calendar(id);\">";
		$html .= "<img src=\"" . SCRIPT_URL . "/zoopfile/guicontrol/js/datechooser/cal2.gif\" onclick=\"toggle_Calendar('{$label}');\" style=\"cursor:pointer;\">";
		$html .= "<script src=\"" . SCRIPT_URL . "/zoopfile/guicontrol/js/datechooser/datechooser.js\"></script>";
		$html .= file_get_contents(zoop_dir . "/guicontrol/public/js/datechooser/cal_div.htm");
		if(!empty($value))
		{
			//make the cool calendar choosing thing start on the month that my value is.
			$date = explode('-', $value);
			$year = $date[0];
			$month = (int)$date[1] - 1;
			$html .= "<script>var calYear = document.getElementById('cal_Year'); calYear.innerHTML = '$year'; var calMonth = document.getElementById('cal_Month'); calMonth.title = '$month';</script>";
		}

		return $html;
	}
}
?>
