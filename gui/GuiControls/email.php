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

/**
 * email 
 * 
 * @uses GuiControl
 * @package 
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class email extends GuiControl
{
	/**
	 * validate 
	 * 
	 * @access public
	 * @return void
	 */
	function validate()
	{
		return true;
	}

	/**
	 * getValue 
	 * 
	 * @access public
	 * @return void
	 */
	function getValue()
	{

		if (isset($this->params['email']))
			$email = trim($this->params['email']) . "@" . $this->params['domain'];
		else
			$email = "";
		return $email;

	}

	/**
	 * getLabelName 
	 * 
	 * @access public
	 * @return void
	 */
	function getLabelName()
	{
		$label = $this->getName() . "[controls][text][email][text]";
		return $label;
	}

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
