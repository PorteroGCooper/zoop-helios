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
* @package gui
* @subpackage guicontrol
*/
class GuiControl
{
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
	 * GuiControl
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function GuiControl($name)
	{
		$this->name = $name;
		$this->persistent = $this->getPersistentParams();
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
			return "controls[$type][{$this->name}]";
		else
			return "{$this->parent}[controls][$type][{$this->name}]";
	}

	/**
	 * validate
	 *
	 * @access public
	 * @return void
	 */
	function validate()
	{
		if(isset($this->params['validate']))
		{
			if (!isset($this->params['validate']['type'])) // make sure essential elements are set
			{
				if (isset($this->params['validate']['required']) && $this->params['validate']['required'] == true)
				{
						$this->params['validate']['type'] = 'length';
						$this->params['validate']['min'] = 1;
						$this->params['validate']['required'] = true;
				}
				else
					return true;
			}
			$validate = Validator::validate($this->getValue(), $this->params['validate']);
			//die();
			if($validate['result'] !== true)
			{
				if (isset($validate['message']))
					$errorState['text'] = $validate['message'];
				else
					$errorState['text'] = "Invalid, value must be {$this->params['validate']['type']} :";

				$errorState['value'] = $this->getValue();
				return $errorState;
			}
		}
		return true;
	}

	/**
	 * getErrorStatus
	 *
	 * @access public
	 * @return void
	 */
	function getErrorStatus()
	{
		if (isset($this->params['errorState']))
			return false;
		else
			return true;
	}

	/**
	 * getValidationAttr
	 *
	 * @param mixed $validate
	 * @access public
	 * @return string
	 */
	function getValidationAttr($validate)
	{
		if (isset($validate['required']) && $validate['required'] == true && !isset($validate['type']))
			$validate['type'] = "length";

		if(isset($validate['type']) && $validate['type'] != 'none')
		{
			return Validator::getAttr($validate);
		}
		else
		{
			return '';
		}
	}

	/**
	 * getValidationAttr
	 *
	 * @param mixed $validate
	 * @access public
	 * @return string
	 */
	function getValidationDivs($validate = null)
	{
		if (empty($validate))
		{
			if (!isset($this->params['validate']) || empty ($this->params['validate']))
				return "";
			else
				$validate = $this->params['validate'];
		}

		$html = "";

		$style = "style=\"display:none\"";

		$id = $this->getLabelName();

		if (isset($validate['max']) && !empty($validate['max']))
			$html .= "<div id=\"max-$id\" $style>{$validate['max']}</div>";

		if (isset($validate['min']) && !empty($validate['min']))
			$html .= "<div id=\"min-$id\" $style>{$validate['min']}</div>";

		if (isset($validate['regExp']) && !empty($validate['regExp']))
			$html .= "<div id=\"params-$id\" $style>{$validate['regExp']}</div>";

		if (isset($validate['regExp']) && !empty($validate['regExp']))
			$html .= "<div id=\"params-$id\" $style>{$validate['regExp']}</div>";

		if (isset($validate['field']) && !empty($validate['field']))
			$html .= "<div id=\"params-$id\" $style>{$validate['field']}</div>";

		return $html;
	}

	/**
	 * getValidationClasses
	 * Used to get the css class names so the javascript validator can be properly run
	 *
	 * @param mixed $validate
	 * @access public
	 * @return string
	 */
	function getValidationClasses($validate = null)
	{
		if (empty($validate))
		{
			if (!isset($this->params['validate']) || empty ($this->params['validate']))
				return "";
			else
				$validate = $this->params['validate'];
		}

		return Validator::getJSClassNames($validate);
	}

	/**
	 * getNameIdString
	 *
	 * @access public
	 * @return string
	 */
	function getNameIdString()
	{
		return "name=\"{$this->getLabelName()}\" id=\"{$this->getLabelName()}\"";
	}

	/**
	 * setRequired
	 *
	 * @param mixed $req
	 * @access public
	 * @return void
	 */
	function setRequired($req = true)
	{
		$this->params['validate']['required'] = $req;

		if (!isset($this->params['validate']['type']) || !$this->params['validate']['type'])
			$this->setValidationType('length');
	}

	/**
	 * setValidationType
	 *
	 * @param mixed $type
	 * @access public
	 * @return void
	 */
	function setValidationType($type)
	{
		$this->params['validate']['type'] = $type;
 	}

 	/**
 	 * setValidationParam
 	 *
 	 * @param mixed $name
 	 * @param mixed $value
 	 * @access public
 	 * @return void
 	 */
 	function setValidationParam($name, $value)
 	{
		$this->params['validate'][$name] = $value;
 	}

	/**
	 * setDefaultValue
	 * Sets the value for the control if no value is provided
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
	 * renderViewState
	 *
	 * @access public
	 * @return void
	 */
	function renderViewState()
	{
		$viewState = $this->encode($this->getViewState());
		$name = $this->getName();
		$html = "<input type=\"hidden\" name=\"{$name}[viewState]\" value=\"$viewState\">";
		return $html;
	}


	function renderErrorMessage()
	{
		if(isset($this->params['errorState']))
		{
			$errorState = $this->params['errorState'];
			$label = $this->getLabelName();
			$html =" <br><div class='s-message s-error' id='advice-$label'>";

			if (!empty($errorState['value']))
				$html .= "\"{$errorState['value']}\" {$errorState['text']}";
			else
				$html .= "{$errorState['text']}";

			$html .= "</div>";
			return $html;
		}
		return "";
	}

	/**
	 * encode
	 *
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function encode($value)
	{
		return base64_encode(gzcompress(serialize($value)));
	}

	/**
	 * decode
	 *
	 * @param mixed $string
	 * @access public
	 * @return void
	 */
	function decode($string)
	{
		return unserialize(gzuncompress(base64_decode($string)));
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
	 * view
	 *
	 * @access public
	 * @return void
	 */
	function view()
	{
		return $this->getValue();
	}

	/**
	 * getLabelName
	 *
	 * @access public
	 * @return void
	 */
	function getLabelName()
	{
		$label = $this->getName() . "[value]";
		return $label;
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

	function renderControl($echo = false)
	{
		$html =  $this->renderViewState();
		$html .= $this->getValidationDivs();
		$html .= $this->render();
		$html .= $this->renderErrorMessage();

		if ($echo)
			echo($html);
		else
			return $html;
	}

	/**
	 * getViewState
	 *
	 * @access public
	 * @return void
	 */
	function getViewState()
	{
		$viewState = array();
		foreach($this->persistent as $param)
		{
			if(isset($this->params[$param]))
				$viewState[$param] = $this->params[$param];
		}
		return $viewState;
	}
}
?>
