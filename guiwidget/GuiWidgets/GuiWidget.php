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
* @package gui
* @subpackage guiwidget
*/
class GuiWidget
{
	var $instanceCount = 1;
	/**
	 * params
	 *
	 * @var mixed
	 * @access public
	 */
	var $params;
	/**
	 * persistent
	 *
	 * @var mixed
	 * @access public
	 */
	var $persistent;
	/**
	 * parent
	 *
	 * @var mixed
	 * @access public
	 */
	var $parent;

	/**
	 * GuiWidget
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function GuiWidget($name = null)
	{
		if (is_null($name))
		{
			$name = "GuiWidget" . $this->instanceCount++;
		}
		$this->name = $name;
	}

	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return void
	 */
	function getPersistentParams()
	{
		trigger_error("Please implement a getPersistentParams that returns an array of the names of
				all parameters that must be persistent across requests.");
	}

	/**
	 * setParam
	 *
	 * @param mixed $name
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setParam($name, $value)
	{
		$this->params[$name] = $value;
	}

	/**
	 * setParams
	 *
	 * @param mixed $valueArray
	 * @access public
	 * @return void
	 */
	function setParams($valueArray)
	{
		foreach($valueArray as $name => $value)
		{
			$this->params[$name] = $value;
		}
	}

	/**
	 * setParent
	 *
	 * @param mixed $parent
	 * @access public
	 * @return void
	 */
	function setParent($parent)
	{
		$this->parent = $parent;
	}

	/**
	 * getParam
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function getParam($name)
	{
		if (isset($this->params[$name]))
			return $this->params[$name];
		else
			return;
	}

	/**
	 * getParams
	 *
	 * @access public
	 * @return void
	 */
	function getParams()
	{
		return $this->params;
	}

	/**
	 * getName
	 *
	 * @access public
	 * @return void
	 */
	function getName()
	{
		$type = get_class($this);

		if (!isset($this->parent))
			return "widgets_{$type}_{$this->name}";
		else
			return "{$this->parent}_widgets_{$type}_{$this->name}";
	}

	/**
	 * getNameIdString
	 *
	 * @access public
	 * @return string
	 */
	function getIdString()
	{
		$name = $this->getName();
          return "id=\"$name\"";
	}

	/**
	 * setDefaultValue
	 * Sets the value for the widget if no value is provided
	 *
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setDefaultValue($value)
	{
		$this->setParam("default", $value);
	}

	/**
	 * getValue
	 *
	 * @access public
	 * @return void
	 */
	function getValue()
	{
		if (isset($this->params['value']))
			return $this->params['value'];
		elseif (isset($this->params['default']))
			return $this->params['default'];
		else
			return;
	}

	/**
	 * setValue
	 *
	 * @param mixed $value
	 * @param mixed $force
	 * @access public
	 * @return void
	 */
	function setValue($value, $force = false)
	{
		if (!$force)
		{
			if (!isset($this->params['value']) || !$this->getErrorStatus())
				$this->setParam('value', $value);
		}
		else
			$this->setParam('value', $value);
	}

	/**
	 * render
	 *
	 * @access public
	 * @return void
	 */
	function render()
	{
		$html = "Please implement a Render function for " . get_class($this);
		return $html;
	}

	/**
	 * display
	 *
	 * @access public
	 * @return void
	 */
	function display()
	{
		echo ($this->render(true));
	}

	function renderWidget($echo = false)
	{
		$html = $this->render();

		if ($echo)
			echo($html);
		else
			return $html;
	}
}
?>
