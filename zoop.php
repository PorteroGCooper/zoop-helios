<?
/**
* @category zoop
* @package zoop
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

class zoop
{
	function zoop($appPath = NULL)
	{
		$this->path = dirname(__file__);
		if($appPath == NULL)
			$this->appPath = $this->path;
		else
			$this->appPath = $appPath;

		$this->addComponent('app');//zoop always includes app_component
	}

	function addComponent($name)
	{
		if(!isset($this->components[$name]))
		{
			include_once($this->path . "/$name/{$name}_component.php");
			$class = "component_{$name}";
			$currComponent = &new $class();
			$components = &$currComponent->getRequiredComponents();
			foreach($components as $newname)
			{
				$this->addComponent($newname);
			}
			$this->components[$name] = &$currComponent;
		}
	}

	function addZone($name)
	{
		$this->addComponent('zone');
		include($this->appPath . "/zone_{$name}.php");
	}

	function addObject($name)
	{
		$file = $this->appPath . "/objects/$name.php";
		if (file_exists($file))
		include_once($file);
	}

	function init()
	{
		foreach($this->components as $name => $object)
		{
			$object->init();
		}
	}

	function run()
	{
		foreach($this->components as $name => $object)
		{
			$object->run();
		}
	}
}

class component
{
	var $required = array();

	function component()
	{
		//default constructor does nothing
	}

	function requireComponent($name)
	{
		$this->required[] = $name;
	}

	function &getRequiredComponents()
	{
		return $this->required;
	}

	function init()
	{
		//default init does nothing
	}

	function run()
	{
		//really shouldn't do anything, unless its the app_component
	}
}

?>