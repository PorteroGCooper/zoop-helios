<?
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

include_once(dirname(__file__) . "/../plugins/function.loadeditor.php");

class editor extends GuiControl
{

	function setValue($value)
	{
		$this->params['text'] = $value;
	}

	function getPersistentParams()
	{
		return array('validate');
	}

	function render()
	{
		global $smarty;

		$html = $this->renderViewState();
		$this->params['name'] = $this->getName();
		$this->params['value'] = $this->getValue();

		$html .= smarty_function_loadeditor($this->params, &$smarty);

		if(isset($this->params['errorState']))
		{
			$errorState = $this->params['errorState'];
			$html .=" <span style=\"color: red;\">{$errorState['text']} {$errorState['value']}</span>";
		}

		return $html;
	}
}
?>