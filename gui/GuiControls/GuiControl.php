<?
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
* read the post/session data for gui controls
*/

/**
 * initGuiControls
 *
 * @access public
 * @return void
 */
function initGuiControls()
{
	global $controlData;

	$GLOBALS['controlData'] = NULL;
	$GLOBALS['controls'] = NULL;
	if($_SERVER["REQUEST_METHOD"] == 'POST')
	{
		$post = getRawPost();

		if(isset($post['controls']))
		{
			$GLOBALS['controlData'] = $post["controls"];
			UnsetPost('controls');
		}
	}
	else
	{
		if(isset($_SESSION['controls']))
		{
			$GLOBALS['controlData'] = $_SESSION['controls'];
			unset($_SESSION['controls']);
		}
	}

	if(isset($controlData))
	{
		$validate = true;

		loadChildControls($controlData);

		$GLOBALS['controls'] = &parseControlData($controlData);

		foreach($GLOBALS['controls'] as $type => $controlSet)
		{
			foreach($controlSet as $name => $control)
			{
				if($_SERVER["REQUEST_METHOD"] == 'POST')
				{
					global $POSTCOPY;

					$valid = $GLOBALS['controls'][$type][$name]->validate();
					if($valid === true)
					{
						$POSTCOPY[$name] = $GLOBALS['controls'][$type][$name]->getValue();
					}
					else
					{
						$GLOBALS['controls'][$type][$name]->setParam('errorState', $valid);
						$validate = false;
					}
				}
			}
		}

		if($validate == false)
		{
			if(!isset($_SESSION['controls']))
				session_register('controls');
			else
			{
				unset($_SESSION['controls']);
				$_SESSION['controls'] = array();
			}

			foreach($GLOBALS['controls'] as $type => $controllist)
			{
				foreach($controllist as $name => $control)
				{
					$_SESSION['controls'][$type][$name]['viewState'] = base64_encode(gzcompress(serialize($control->getParams())));
					$_SESSION['controls'][$type][$name]['value'] = $control->getValue();
				}
			}

			redirect(VIRTUAL_URL);
		}
	}
}

/**
 * loadChildControls
 *
 * @param mixed $controlData
 * @access public
 * @return void
 */
function loadChildControls(&$controlData)
{
	foreach ($controlData as $type => $typeobj)
	{
		includeGuiControl($type);

		foreach ($typeobj as $name)
		{
			if (is_array($name))
			{
				foreach ($name as $paramname => $value)
				{
					if ($paramname == 'controls')
					{
						loadChildControls($value);
					}
				}
			}
		}
	}
}

/**
 * &parseControlData
 *
 * @param mixed $controlData
 * @access public
 * @return void
 */
function &parseControlData(&$controlData)
{
	foreach($controlData as $type => $controlset)
	{
		foreach($controlset as $name => $controlitems)
		{
			$controls[$type][$name] = &getGuiControl($type, $name, true);

			if(is_array($controlitems))
			{
				foreach($controlitems as $paramname => $value)
				{
					if($paramname == 'controls')
					{
						if (isset($childControls))
							unset($childControls);
						$childControls = &parseControlData($value);

						foreach($childControls as $childType => $childSet)
						{
							foreach($childSet as $child)
							{
								$controls[$type][$name]->setParam($child->name, $child->getValue());
							}
						}
					}
					else if($paramname != 'viewState')
					{
						$controls[$type][$name]->setParam($paramname,  $value);
					}
					else
					{
						$viewState = $controls[$type][$name]->decode($value);
						if (is_array($viewState))
						{
							foreach($viewState as $stateName => $stateValue)
							{
								$controls[$type][$name]->setParam($stateName,  $stateValue);
							}
						}
					}
				}
			}
			else
			{
				// I DON'T THINK THIS EVER OCCURS?? SPF 4/9/06
				// SHOULD PROBABLY BE REMOVED AFTER MORE TESTING OCCURS
				$controls[$type][$name]->setValue($controlitems);
			}

			// We have reached this point but still need to assign the newly POSTED controls into their proper location.
			if (isset($childControls))
			{
				$controls[$type][$name]->params['controls'] = $childControls;
			}
		}
	}
	return $controls;
}

/**
 * &getGuiControl
 *
 * @param mixed $type
 * @param mixed $name
 * @param mixed $useGlobal
 * @access public
 * @return void
 */
function &getGuiControl($type, $name, $useGlobal = true)
{
	if($useGlobal)
	{
		global $controls;
		if(isset($controls[$type][$name]))
		{
			return $controls[$type][$name];
		}
	}

	includeGuiControl($type);

	if($useGlobal)
	{
		$controls[$type][$name] = &new $type($name);
		return $controls[$type][$name];
	}
	else
	{
		$control = &new $type($name);
		return $control;
	}
}

/**
 * includeGuiControl
 *
 * @param mixed $type
 * @access public
 * @return void
 */
function includeGuiControl($type)
{
	$filename = strtolower($type).".php";

	if(file_exists(app_dir . "/GuiControls/$filename"))
		include_once(app_dir . "/GuiControls/$filename");
	else if(file_exists(zoop_dir . "/gui/GuiControls/$filename"))
		include_once(zoop_dir . "/gui/GuiControls/$filename");
	else
		trigger_error("Please Implement a $type Control and place it in " .
					app_dir . "/GuiControls/$filename" . " or " .
					zoop_dir . "/gui/GuiControls/$filename");
}

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
	 * @return void
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
			setValidationType('length');
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
	 * getValue
	 *
	 * @access public
	 * @return void
	 */
	function getValue()
	{
		if (isset($this->params['value']))
			return $this->params['value'];
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
