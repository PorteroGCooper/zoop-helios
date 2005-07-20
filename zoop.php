<?
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

		$this->addFramework('app');//zoop always includes app_framework
	}

	function addFramework($name)
	{
		if(!isset($this->frameworks[$name]))
		{
			include_once($this->path . "/$name/{$name}_framework.php");
			$class = "framework_{$name}";
			$currFramework = &new $class();
			$frameworks = &$currFramework->getRequiredFrameworks();
			foreach($frameworks as $newname)
			{
				$this->addFramework($newname);
			}
			$this->frameworks[$name] = &$currFramework;
		}
	}

	function addZone($name)
	{
		$this->addFramework('zone');
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
		foreach($this->frameworks as $name => $object)
		{
			$object->init();
		}
	}

	function run()
	{
		foreach($this->frameworks as $name => $object)
		{
			$object->run();
		}
	}
}

class framework
{
	var $required = array();

	function framework()
	{
		//default constructor does nothing
	}

	function requireFramework($name)
	{
		$this->required[] = $name;
	}

	function &getRequiredFrameworks()
	{
		return $this->required;
	}

	function init()
	{
		//default init does nothing
	}

	function run()
	{
		//really shouldn't do anything, unless its the app_framework
	}
}

?>