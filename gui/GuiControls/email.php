<?
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

class email extends GuiControl
{
	function validate()
	{
		return true;
	}

	function getValue()
	{

		if (isset($this->params['email']))
			$email = trim($this->params['email']) . "@" . $this->params['domain'];
		else
			$email = "";
		return $email;

	}

	function getLabelName()
	{
		$label = $this->getName() . "[controls][text][email][text]";
		return $label;
	}

	function getPersistentParams()
	{
		return array('validate');
	}

	function render()
	{
		$html = $this->renderViewState();
		$attrs = array();
		$value = $this->getValue();
		$name = $this->getName();


		$newvalue = explode('@', $value);
		if (isset($newvalue[1]))
		{
			$emailvalue = htmlspecialchars($newvalue[0]);
			$domainvalue = htmlspecialchars($newvalue[1]);
		}
		else
		{
			$emailvalue = "";
			$domainvalue = "";
		}

		$usercontrol = &getGuiControl('text', 'email');
		$usercontrol->setParam('text', $emailvalue);
		$usercontrol->setParent($name);
		$usercontrol->setParams($this->params);
		$html .= $usercontrol->render();

		$html .= " @ ";

		$domaincontrol = &getGuiControl('select', 'domain');
		$domaincontrol->setParam('value', $domainvalue);
		$domaincontrol->setParams($this->params);
		$domaincontrol->setParent($name);
		$html .= $domaincontrol->render();

		$this->controls = array(&$usercontrol, &$domaincontrol);


		if(isset($this->params['errorState']))
		{
			$errorState = $this->params['errorState'];
			$html .=" <span style=\"color: red;\">{$errorState['text']} {$errorState['value']}</span>";
		}

		return $html;
	}
}
?>