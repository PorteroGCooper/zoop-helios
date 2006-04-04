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
				}
			}
			redirect(VIRTUAL_URL);
		}
	}
}

function loadChildControls(&$controlData)
{
	foreach ($controlData as $type)
	{
		foreach ($type as $name)
		{
			if (is_array($name))
			{
				foreach ($name as $paramname => $value)
				{
					if ($paramname == 'controlsList')
					{
						$childControlList = unserialize(gzuncompress(base64_decode($value)));
						foreach ($childControlList as $ccontrol)
						{
							includeGuiControl($ccontrol);
						}
					}

					if ($paramname == 'controls')
					{
						loadChildControls($value);
					}
				}
			}
		}
	}
}

function &parseControlData(&$controlData)
{
	$controls = array();

	foreach($controlData as $type => $controlset)
	{
		foreach($controlset as $name => $controlitems)
		{
			$controls[$type][$name] = getGuiControl($type, $name, false);

			if(is_array($controlitems))
			{
				foreach($controlitems as $paramname => $value)
				{
					if($paramname == 'controls')
					{
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
						$controls[$type][$name]->setParam($paramname,  $value);
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
				$controls[$type][$name]->setValue($controlitems);
			}
		}
	}

	return $controls;
}

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
	var $params;
	var $persistent;
	var $parent;

	function GuiControl($name)
	{
		$this->name = $name;
		$this->persistent = $this->getPersistentParams();
	}

	function getPersistentParams()
	{
		trigger_error("Please implement a getPersistentParams that returns an array of the names of
				all parameters that must be persistent across requests.");
	}

	function setParam($name, $value)
	{
		$this->params[$name] = $value;
	}

	function setParams($valueArray)
	{
		foreach($valueArray as $name => $value)
		{
			$this->params[$name] = $value;
		}
	}

	function setParent($parent)
	{
		$this->parent = $parent;
	}

	function getParam($name)
	{
		if (isset($this->params[$name]))
			return $this->params[$name];
		else
			return;
	}

	function getParams()
	{
		return $this->params;
	}

	function getName()
	{
		$type = get_class($this);

		if (!isset($this->parent))
			return "controls[$type][{$this->name}]";
		else
			return "{$this->parent}[controls][$type][{$this->name}]";
	}

	function validate()
	{
		if(isset($this->params['validate']))
		{

			if (!isset($this->params['validate']['type'])) // make sure essential elements are set
			{
				if (isset($this->params['validate']['required']) && $this->params['validate']['required'] == true)
						$this->params['validate']['type'] = 'length';
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
	function setRequired($req = true)
	{
		$this->params['validate']['required'] = $req;

		if (!isset($this->params['validate']['type']) || !$this->params['validate']['type'])
			setValidationType('length');
	}

	function setValidationType($type)
	{
		$this->params['validate']['type'] = $type;
 	}

 	function setValidationParam($name, $value)
 	{
		$this->params['validate'][$name] = $value;
 	}

	function getValue()
	{
		if (isset($this->params['text']))
			return $this->params['text'];
		elseif (isset($this->params['value']))
			return $this->params['value'];
		else
			return;
	}

	function renderViewState()
	{
		$viewState = $this->encode($this->getViewState());
		$name = $this->getName();
		$html = "<input type=\"hidden\" name=\"{$name}[viewState]\" value=\"$viewState\">";
		return $html;
	}

	function encode($value)
	{
		return base64_encode(gzcompress(serialize($value)));
	}

	function decode($string)
	{
		return unserialize(gzuncompress(base64_decode($string)));
	}

	function render()
	{
		$html = "Please implement a Render function for " . get_class($this);
		return $html;
	}

	function view()
	{
		return $this->getValue();
	}

	function getLabelName()
	{
		$label = $this->getName();
		return $label;
	}

	function display()
	{
		echo ($this->render(true));
	}

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
