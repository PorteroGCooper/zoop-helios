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
 * Base GuiControl class.
 *
 * GuiControl parameters:
 * - displayTitle   - Will be shown as the GuiControl's 'label' (if {guicontrol_label} is output)
 * - label          - Same as displayTitle
 * - caption        - Caption shown under GuiControl: could be instructions for use, etc.
 *
 * @ingroup gui
 * @ingroup guicontrol
 */
class GuiControl {

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
	 * Sets name and calls getPersistentParams();
	 *
	 * Don't call this function directly, instead use GuiControl::get() method.
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 * @see GuiControl::get()
	 */
	function __construct($name) {
		global $gui;
		$gui->add_css('/zoopfile/guicontrol/css/guicontrols.css', 'zoop');
	
		$this->name = $name;
		$this->persistent = $this->getPersistentParams();
		$this->initControl();
	}
	
	function initControl() { }

	/**
	 * In each guiControl this method must be implemented and return an array of all params
	 * then need to persist across requests.
	 * Ex: return array('validate');
	 *
	 * @access public
	 * @return void
	 */
	function getPersistentParams() {
		trigger_error("Please implement a getPersistentParams that returns an array of the names of all parameters that must be persistent across requests.");
	}

	/**
	 * Set a param by name. 
	 * Will replace the param's value if it is already set
	 *
	 * @param mixed $name
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setParam($name, $value) {
		$this->params[$name] = $value;
		return $this;
	}

	/**
	 * Set all parameters. Will replace any existing Params
	 *
	 * @param mixed $valueArray
	 * @access public
	 * @return void
	 */
	function setParams($valueArray) {
		foreach($valueArray as $name => $value) {
			$this->params[$name] = $value;
		}
		return $this;
	}

	/**
	 * When embeding controls, the child control needs parent set. 
	 * $child->setParent($this->getName());
	 *
	 * @param mixed $parent
	 * @access public
	 * @return void
	 */
	function setParent($parent) {
		$this->parent = $parent;
		return $this;
	}

	/**
	 * Return param by name
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function getParam($name) {
		if (isset($this->params[$name])) {
			return $this->params[$name];
		} else {
			return;
		}
	}

	/**
	 * Return all params
	 *
	 * @access public
	 * @return void
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
		$type = $this->getType();
		if (!isset($this->parent)) {
			return "controls[$type][{$this->name}]";
		} else {
			return "{$this->parent}[controls][$type][{$this->name}]";
		}
	}
	
	/**
	 * Get a valid (X)HTML element ID for this GuiControl. Use this function instead of
	 * GuiControl::getLabelName whenever referring to this control in JavaScript or CSS.
	 * 
	 * @access public
	 * @return string GuiControl id
	 */
	function getId() {
		return str_replace(array('[', ']'), array('-', ''), $this->getLabelName());
	}
	
	/**
	 * Get the type for this GuiControl.
	 *
	 * @access public
	 * @return string GuiControl type ('text', 'hidden', etc)
	 */
	function getType() {
		if (!isset($this->type)) {
			$type = get_class($this);
			if (substr($type, -7) == 'Control') {
				$type = substr($type, 0, -7);
			}
			$this->type = lcfirst($type);
		}
		return $this->type;
	}
	
	/**
	 * getDisplayName
	 *
	 * @deprecated
	 * @see GuiControl::getLabel
	 * @access public
	 * @return string GuiControl label
	 */
	function getDisplayName() {
		return $this->getLabel();
	}
	
	/**
	 * Get a label for this GuiControl.
	 *
	 * If no label is specified in the GuiControl params (as either 'label' or 'displayName')
	 * returns a formatted version of this GuiControl's name.
	 * 
	 * @access public
	 * @return string GuiControl label
	 */
	function getLabel() {
		if (isset($this->params['label'])) {
			return $this->params['label'];
		} else if (isset($this->params['displayName'])) {
			return $this->params['displayName'];
		} else {
			return format_label($this->name);
		}
	}

	/**
	 * validate
	 *
	 * @access public
	 * @return void
	 */
	function validate() {
		if(isset($this->params['validate'])) {
			// make sure essential elements are set
			if (!isset($this->params['validate']['type'])) {
				if (isset($this->params['validate']['required']) && $this->params['validate']['required'] == true) {
						$this->params['validate']['type'] = 'length';
						$this->params['validate']['min'] = 1;
						$this->params['validate']['required'] = true;
				} else {
					return true;
				}
			}
			$validate = Validator::validate($this->getValue(), $this->params['validate']);
		
			if($validate['result'] !== true) {
				if (isset($validate['message'])) {
					$errorState['text'] = $validate['message'];
				} else {
					$errorState['text'] = "Invalid, value must be {$this->params['validate']['type']} :";
				}

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
	function getErrorStatus() {
		if (isset($this->params['errorState'])) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * getValidationAttr
	 *
	 * @param mixed $validate
	 * @access public
	 * @return string
	 */
	function getValidationAttr($validate) {
		if (isset($validate['required']) && $validate['required'] == true && !isset($validate['type'])) {
			$validate['type'] = "length";
		}

		if(isset($validate['type']) && $validate['type'] != 'none') {
			return Validator::getAttr($validate);
		} else {
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
	function getValidationDivs($validate = null) {
		if (empty($validate)) {
			if (!isset($this->params['validate']) || empty ($this->params['validate'])) {
				return "";
			} else {
				$validate = $this->params['validate'];
			}
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
	function getValidationClasses($validate = null) {
		if (empty($validate)) {
			if (!isset($this->params['validate']) || empty ($this->params['validate'])) {
				return "";
			}
			else {
				$validate = $this->params['validate'];
			}
		}

		return Validator::getJSClassNames($validate);
	}

	/**
	 * getNameIdString
	 *
	 * @access public
	 * @return string
	 */
	function getNameIdString() {
		$name = $this->getLabelName();
		$id = $this->getId();
		
		return "name=\"$name\" id=\"$id\"";
	}

	/**
	 * Flag the guiControl as Required
	 *
	 * @param mixed $req
	 * @access public
	 * @return void
	 */
	function setRequired($req = true) {
		$this->params['validate']['required'] = $req;
		
		return $this;
	}

	/**
	 * Set the validation type.
	 *
	 * Type must match that of the Zoop Validation Class
	 *
	 * @param mixed $type
	 * @access public
	 * @return void
	 */
	function setValidationType($type) {
		$this->params['validate']['type'] = $type;
		
		return $this;
 	}

 	/**
 	 * Set a validation parameter.
 	 *
 	 * @param mixed $name
 	 * @param mixed $value
 	 * @access public
 	 * @return void
 	 */
 	function setValidationParam($name, $value) {
		$this->params['validate'][$name] = $value;
		
		return $this;
 	}

	/**
	 * Set the value for the control if no value is provided.
	 *
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function setDefaultValue($value) {
		return $this->setParam("default", $value);
	}

	/**
	 * Return the value for this control.
	 *
	 * Typically called after the user has posted data, returns the value.
	 *
	 * @access public
	 * @return mixed
	 */
	function getValue() {
		if (isset($this->params['value'])) {
			return $this->params['value'];
		} elseif (isset($this->params['default'])) {
			return $this->params['default'];
		} else {
			return;
		}
	}

	/**
	 * Set the value for the control 
	 * (usually called before rendering)
	 *
	 * @param mixed $value
	 * @param mixed $force
	 * @access public
	 * @return void
	 */
	function setValue($value, $force = false) {
		if (!$force) {
			if (!isset($this->params['value']) || !$this->getErrorStatus()) {
				$this->setParam('value', $value);
			}
		} else {
			$this->setParam('value', $value);
		}
		
		return $this;
	}

	/**
	 * ViewState places a bunch of information into the form about this control as hidden fields.
	 * This permits guiControls to work without sessions.
	 *
	 * This method renders the ViewState.
	 *
	 * @access public
	 * @return void
	 */
	function renderViewState() {
		$viewState = $this->encode($this->getViewState());
		$name = $this->getName();
		$html = "<input type=\"hidden\" name=\"{$name}[viewState]\" value=\"$viewState\">";
		return $html;
	}

	function renderErrorMessage() {
		if(isset($this->params['errorState'])) {
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
	
	function renderCaption() {
		if ($caption = $this->getParam('caption')) {
			return '<p class="caption">' . $caption . '</p>';
		}
	}

	/**
	 * encode
	 *
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	function encode($value) {
		return base64_encode(gzcompress(serialize($value)));
	}

	/**
	 * decode
	 *
	 * @param mixed $string
	 * @access public
	 * @return void
	 */
	function decode($string) {
		return unserialize(gzuncompress(base64_decode($string)));
	}

	/**
	 * Render is the method defined in each control, that renders the control. 
	 * Render simply renders the control, but doesn't include all viewState, validation, etc..
	 * Don't call this method directly as you will be missing critical pieces of data, but rather call renderControl
	 *
	 * @see GuiControl::renderControl
	 * @access protected
	 * @return void
	 */
	protected function render() {
		trigger_error('Please implement a GuiControl render function for ' . get_class($this));
	}

	/**
	 * Render the guiControl in a non-editable state.
	 * Used to view the value. By default, just returns the value.
	 *
	 * @see self::getValue
	 * @access public
	 * @return void
	 */
	function view() {
		return $this->getValue();
	}

	/**
	 * getLabelName
	 *
	 * @access public
	 * @return void
	 */
	function getLabelName() {
		$label = $this->getName() . "[value]";
		return $label;
	}
	
	/**
	 * Assign this GuiControl to the Smarty param passed as $name
	 *
	 * @access public
	 * @param string $name (Optional, defaults to name passed to constructor)
	 * @see gui::assign()
	 */
	function guiAssign($name = null) {
		global $gui;
		if (empty($name)) $name = $this->name;
		$gui->assign($name, $this);
		return $this;
	}

	/**
	 * Changing this method to a final method (to weed out the last of the 'display' functions
	 * on subclasses [justin]
	 * 
	 * @depricated
	 * @access public
	 * @return void
	 */
	final function display() {
		deprecated('The use of guicontrol::display() is deprecated. Please use print $mycontrol->render()');
		print $this->render();
	}

	/**
	 * Echo or return the guiControl.
	 *
	 * Call this method (instead of render()) to use the GuiControl.
	 * 
	 * @param mixed $echo 
	 * @access public
	 * @return void
	 */
	function renderControl($echo = false) {
		if ($echo) {
			echo (string)$this;
		} else {
			return (string)$this;
		}
	}

	/**
	 * Convert this GuiControl to an HTML string.
	 *
	 * Render the control along with it's view state, validation messages, etc..
	 *
	 * Use of string conversion is preferred over renderControl() call,
	 * because GuiControls can be rendered simply by calling 'print $control'.
	 * 
	 * @access public
	 * @return string HTML rendered GuiControl.
	 */
	function __toString() {
		$html =  $this->renderViewState();
		$html .= $this->getValidationDivs();
		$html .= $this->render();
		$html .= $this->renderCaption();
		$html .= $this->renderErrorMessage();
		return $html;
	}

	/**
	 * ViewState places a bunch of information into the form about this control as hidden fields.
	 * This permits guiControls to work without sessions.
	 *
	 * This method returns the ViewState.
	 *
	 * @access public
	 * @return void
	 */
	function getViewState() {
		$viewState = array();
		foreach($this->persistent as $param) {
			if(isset($this->params[$param])) {
				$viewState[$param] = $this->params[$param];
			}
		}
		return $viewState;
	}
	
	/**
	 * Get an instance of a GuiControl. Use this function instead of the global getGuiControl().
	 *
	 * @code
	 *   $myGuiControl = GuiControl::get('button', 'submit_button');
	 * @code
	 *
	 * This call can be chained together with all sorts of other GuiControl initialization function
	 * calls, allowing code like the following:
	 * 
	 * @code
	 *   GuiControl::get('button', 'submit_button')
	 *       ->setParams(array('type' => 'submit', 'value' = 'Save'))
	 *       ->guiAssign();
	 * @code
	 *
	 * @param string $type
	 * @param string $name
	 * @param bool $useGlobal (default: true)
	 * @return GuiControl
	 */
	static function &get($type, $name, $useGlobal = true) {
	
		if($useGlobal) {
			global $controls;
			if(isset($controls[$type][$name])) {
				return $controls[$type][$name];
			}
		}
	
		component_guicontrol::includeGuiControl($type);
		$className = component_guicontrol::getGuiControlClassName($type);

		if($useGlobal) {
			$controls[$type][$name] = &new $className($name);
			return $controls[$type][$name];
		} else {
			$control = &new $className($name);
			return $control;
		}

	}
}
