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

include_once(zoop_guicontrol_dir. "select.php");

class select_update extends select
{

	function render()
	{
		global $gui;

		$label = $this->getLabelName();

		$method = 'post';

		extract($this->params);

		if (!isset($update_id))
		{
			$update_id = $label . rand(1,250000);
			$drawdiv = 1;
		}

		$url = $this->params['url'];

		$newoptions['Choose'] = '-- Please Choose';
		foreach ($this->params['index'] as $key => $option)
		{
			$newoptions[$key] = $option;
		}

		$this->params['index'] = $newoptions;

		$AJAX = 'new Ajax.Updater(\'' . $update_id . '\', \'' . $url .'\', {parameters:Form.serialize(this.form), method: \'' . $method . '\'});';

		$this->params['onChange'] = $AJAX;

		$html = parent::render();

		if (isset($drawdiv))
			$html .= "<div id='$update_id'></div>";

		return $html;
	}
}
?>