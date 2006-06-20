<?

define("zoop_guicontrol_dir", zoop_dir . "/guicontrol/GuiControls/");
define("app_guicontrol_dir", app_dir . "/GuiControls/");

class component_guicontrol extends component
{
	function component_guicontrol()
	{

		$this->requireComponent('gui');
	}

	function getIncludes()
	{
		return array("guicontrol" => zoop_guicontrol_dir. 'GuiControl.php',
		"guicontainer" => zoop_guicontrol_dir. 'GuiContainer.php',
		"guimultivalue" => zoop_guicontrol_dir. 'GuiMultiValue.php');
	}

	/**
	 * init
	 *
	 * @access public
	 * @return void
	 */
	function init()
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

			component_guicontrol::loadChildControls($controlData);

			$GLOBALS['controls'] = &component_guicontrol::parseControlData($controlData);

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
						echo_r($_SESSION['controls']);
						$_SESSION['controls'][$type][$name]['viewState'] = base64_encode(gzcompress(serialize($control->getParams())));
						$_SESSION['controls'][$type][$name]['value'] = $control->getValue();
					}
				}

				if (isset($_SESSION['sUrls']) && count($_SESSION['sUrls']) > 1)
				{
					$key = count($_SESSION['sUrls']) - 2; // 2nd to last
					$previousPage = $_SESSION['sUrls'][$key];
					$url = substr($previousPage, strpos($previousPage, " ") + 1);
				}
				else
					$url = VIRTUAL_URL;

				redirect($url);
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
			component_guicontrol::includeGuiControl($type);

			foreach ($typeobj as $name)
			{
				if (is_array($name))
				{
					foreach ($name as $paramname => $value)
					{
						if ($paramname == 'controls')
						{
							component_guicontrol::loadChildControls($value);
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
	function &parseControlData(&$controlData, $useGlobals = true)
	{
		foreach($controlData as $type => $controlset)
		{
			foreach($controlset as $name => $controlitems)
			{
				$controls[$type][$name] = &getGuiControl($type, $name, $useGlobals);

				if(is_array($controlitems))
				{
					foreach($controlitems as $paramname => $value)
					{
						if($paramname == 'controls')
						{
							if (isset($childControls))
								unset($childControls);
							$childControls = &component_guicontrol::parseControlData($value, false);

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
					bug("error in parseControlData this shouldn't occur");
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
	 * includeGuiControl
	 *
	 * @param mixed $type
	 * @access public
	 * @return void
	 */
	function includeGuiControl($type)
	{
		$filename = strtolower($type).".php";

		if(file_exists(app_guicontrol_dir. "$filename"))
			include_once(app_guicontrol_dir. "$filename");
		else if(file_exists(zoop_guicontrol_dir. "$filename"))
			include_once(zoop_guicontrol_dir. "$filename");
		else
			trigger_error("Please Implement a $type Control and place it in " .
						app_guicontrol_dir. "$filename" . " or " .
						zoop_guicontrol_dir. "$filename");
	}
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

	component_guicontrol::includeGuiControl($type);

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
?>