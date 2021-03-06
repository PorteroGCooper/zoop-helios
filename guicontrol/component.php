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
 * component_guicontrol
 *
 * @ingroup guicontrol
 * @ingroup components
 * 
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @author John LeSueur <john@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_guicontrol extends component {

	function __construct() {
		$this->requireComponent('gui');
	}

	function getIncludes() {
		$base = Config::get('zoop.guicontrol.directories.zoop');
		return array(
			"GuiControl"    => $base . "/GuiControl.php",
			"GuiContainer"  => $base . "/GuiContainer.php",
			"GuiMultiValue" => $base . "/GuiMultiValue.php"
		);
	}

	function run() {
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

			if (!isset($_SESSION['guiControlUrl']))
				session_register('guiControlUrl');

			if (!stristr(VIRTUAL_URL, $GLOBALS['Sname'] . "/zoopfile"))
				$_SESSION['guiControlUrl'] = VIRTUAL_URL;
		}

		if(isset($controlData))
		{
			$validate = true;
			
			component_guicontrol::loadChildControls($controlData);

			$GLOBALS['controls'] = &component_guicontrol::parseControlData($controlData);

			// TODO: fix posted checkboxes. they currently dont' show up unless they're checked...
			// echo_r($GLOBALS['controls']);

			foreach($GLOBALS['controls'] as $type => $controlSet)
			{
				foreach($controlSet as $name => $control)
				{
					if($_SERVER["REQUEST_METHOD"] == 'POST')
					{
						global $_POST_UNFILTERED;

						$valid = $GLOBALS['controls'][$type][$name]->validate();
						if($valid === true)
						{
							$_POST_UNFILTERED[$name] = $GLOBALS['controls'][$type][$name]->getValue();
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
						$_SESSION['controls'][$type][$name]['value'] = strip_gpc_slashes($control->getValue());
					}
				}

				if (isset($_SESSION['guiControlUrl']))
					$url = $_SESSION['guiControlUrl'];
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
				$controls[$type][$name] = GuiControl::get($type, $name, $useGlobals);

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
					// This happens if only the value is posted for a guicontrol(there is no viewstate)
					// Most useful when it's an ajax post.
					// This is maybe a little dangerous, but I'm disabling the bug for now.
					//bug("error in parseControlData this shouldn't occur");
					$controls[$type][$name]->setValue($controlitems);
					//var_dump($controlData);
					//die();
				}

				// We have reached this point but still need to assign the newly POSTED controls into their proper location.
				if (isset($childControls)) {
					$controls[$type][$name]->setParam('controls', $childControls);
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
	function includeGuiControl($type) {
		$filename = strtolower($type).".php";

		$app_dir  = Config::get('zoop.guicontrol.directories.zoop');
		$zoop_dir = Config::get('zoop.guicontrol.directories.app');

		if(file_exists("$app_dir/$filename")) {
			include_once("$app_dir/$filename");
		} else if(file_exists("$zoop_dir/$filename")) {
			include_once( "$zoop_dir/$filename");
		} else {
			trigger_error("Please Implement a $type Control and place it in " .
				"$app_dir/$filename" . " or " .
				"$zoop_dir/$filename");
		}
	}

	/**
	 * Provide the class name for a GuiControl based on it's type
	 *
	 * @param string $type
	 * @return string $classname
	 */
	function getGuiControlClassName($type) {
		return $type . "Control";
	}
}

/**
 * &getGuiControl
 *
 * @deprecated
 * @param string $type
 * @param string $name
 * @param bool $useGlobal
 * @access public
 * @return void
 */
function &getGuiControl($type, $name, $useGlobal = true) {
	deprecated('The getGuiControl() global function has been deprecated in favor of GuiControl::get()');
	return GuiControl::get($type, $name, $useGlobal);
}