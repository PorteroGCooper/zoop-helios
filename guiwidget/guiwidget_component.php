<?php

define("zoop_guiwidget_dir", zoop_dir . "/guiwidget/GuiWidgets/");
define("app_guiwidget_dir", app_dir . "/GuiWidgets/");

class component_guiwidget extends component
{
	function component_guiwidget()
	{
		$this->requireComponent('gui');
	}

	function getIncludes()
	{
		return array("GuiWidget" => zoop_guiwidget_dir. 'GuiWidget.php',
// 		"GuiContainer" => zoop_guiwidget_dir. 'GuiContainer.php',
		"WidgetGui" => zoop_dir . "/guiwidget/widgetgui.php");
	}

	/**
	 * includeguiwidget
	 *
	 * @param mixed $type
	 * @access public
	 * @return void
	 */
	function includeGuiWidget($type)
	{
 		$filename = strtolower($type).".php";

		if(file_exists(app_guiwidget_dir. "$filename"))
			include_once(app_guiwidget_dir. "$filename");
		else if(file_exists(zoop_guiwidget_dir. "$filename"))
			include_once(zoop_guiwidget_dir. "$filename");
		else
			trigger_error("Please Implement a $type widget and place it in " .
						app_guiwidget_dir. "$filename" . " or " .
						zoop_guiwidget_dir. "$filename");
	}
}

/**
 * &getguiwidget
 *
 * @param mixed $type
 * @param mixed $name
 * @param mixed $useGlobal
 * @access public
 * @return void
 */
function &getGuiWidget($type, $name, $useGlobal = false)
{
	if($useGlobal)
	{
		global $guiwidgets;
		if(isset($guiwidgets[$type][$name]))
		{
			return $guiwidgets[$type][$name];
		}
	}

	component_guiwidget::includeGuiWidget($type);

	$className = "guiwidget_{$type}";

	if($useGlobal)
	{
		$guiwidgets[$type][$name] = &new $className($name);
		return $guiwidgets[$type][$name];
	}
	else
	{
		$control = &new $className($name);
		return $control;
	}
}
?>
