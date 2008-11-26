<?php

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
 * @ingroup gui
 * @ingroup guiwidget
 */
class GuiWidget {
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
	function GuiWidget($name = null) {
		if (is_null($name)) {
			// will this actually work? doesn't it need to be a static class variable?
			$name = "GuiWidget" . $this->instanceCount++;
		}
		$this->name = $name;
		$this->initWidget();
	}
	
	/**
	 * Initialize guiWidget stuffs. Can be overridden by an extending class to hook things
	 * (like js or css includes) into the constructor.
	 * 
	 * @access public
	 * @return void
	 */
	function initWidget() { }

	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return void
	 */
	function getPersistentParams() {
		trigger_error("Please implement a getPersistentParams that returns an array of the names of
				all parameters that must be persistent across requests.");
	}

	/**
	 * setParam
	 *
	 * @param string $name
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setParam($name, $value) {
		$this->params[$name] = $value;
	}

	/**
	 * setParams
	 *
	 * @param array $valueArray
	 * @access public
	 * @return void
	 */
	function setParams($valueArray) {
		foreach($valueArray as $name => $value) {
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
	function setParent($parent) {
		$this->parent = $parent;
	}

	/**
	 * getParam
	 *
	 * @param string $name
	 * @access public
	 * @return mixed
	 */
	function getParam($name) {
		if (isset($this->params[$name])) {
			return $this->params[$name];
		} else {
			return;
		}
	}

	/**
	 * getParams
	 *
	 * @access public
	 * @return array
	 */
	function getParams() {
		return $this->params;
	}

	/**
	 * getName
	 *
	 * @access public
	 * @return void
	 */
	function getName() {
		$name = 'widgets_' . get_class($this) . '_' . $this->name;
		if (isset($this->parent)) {
			$name = $this->parent . '_' . $name;
		}
		return $name;
	}

	/**
	 * getNameIdString
	 *
	 * @access public
	 * @return string
	 */
	function getIdString() {
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
	function setDefaultValue($value) {
		$this->setParam("default", $value);
	}

	/**
	 * getValue
	 *
	 * @access public
	 * @return void
	 */
	function getValue() {
		if (isset($this->params['value'])) {
			return $this->params['value'];
		} else if (isset($this->params['default'])) {
			return $this->params['default'];
		} else {
			return;
		}
	}

	/**
	 * setValue
	 *
	 * @param mixed $value
	 * @param bool $force
	 * @access public
	 * @return void
	 */
	function setValue($value, $force = false) {
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
	 * @return string Rendered html
	 */
	function render() {
		trigger_error("Implement a render() function for " . get_class($this));
	}

	/**
	 * display
	 *
	 * @access public
	 * @return void
	 */
	function display() {
		echo ($this->render(true));
	}

	/**
	 * renderWidget 
	 * 
	 * @param bool $echo 
	 * @access public
	 * @return mixed
	 */
	function renderWidget($echo = false) {
		$html = $this->render();

		if ($echo) {
			echo($html);
		}
		else {
			return $html;
		}
	}
}