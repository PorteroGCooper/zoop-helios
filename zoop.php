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

if(!defined('zoop_autoload') || zoop_autoload)
{
	function __autoload($name)
	{
		global $zoop;
		if($zoop->autoLoad($name))
			return true;
		else
			return false;//trigger_error("class $name not registered with the \$zoop object, try \$zoop->include(\"$name\",\"<fileName>\")");
	}
}

/**
 * zoop
 *
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class zoop
{
	/**
	 * init
	 *
	 * @var array
	 * @access public
	 */
	var $init = array();

	/**
	 * zoop
	 *
	 * @param mixed $appPath
	 * @access public
	 * @return void
	 */
	function zoop($appPath = NULL)
	{
		$this->path = dirname(__file__);
		if($appPath == NULL)
			$this->appPath = $this->path;
		else
			$this->appPath = $appPath;

		$this->addComponent('app');//zoop always includes app_component
	}

	/**
	 * addComponent
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function addComponent($name)
	{
		if(!isset($this->components[$name]))
		{
			include($this->path . "/$name/{$name}_component.php");
			$class = "component_{$name}";
			$currComponent = &new $class();
			$components = &$currComponent->getRequiredComponents();
			$this->addIncludes($currComponent->getIncludes());
			foreach($components as $newname)
			{
				$this->addComponent($newname);
			}
			$this->components[$name] = &$currComponent;
		}
	}

	/**
	 * addZone
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function addZone($name)
	{
		$this->addComponent('zone');
		$this->addInclude("zone_{$name}", $this->appPath . "/zone_{$name}.php");
		//include($this->appPath . "/zone_{$name}.php");
	}

	/**
	 * addObject
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function addObject($name)
	{
		$file = $this->appPath . "/objects/$name.php";
		$this->addInclude($name, $file);
		//if (file_exists($file))
		//	include($file);
	}

	/**
	 * addInclude
	 *
	 * @param mixed $name
	 * @param mixed $file
	 * @access public
	 * @return void
	 */
	function addInclude($name, $file)
	{
		$this->includes[$name] = $file;
		if(version_compare(PHP_VERSION, "5.0", "<"))
			include_once($file);
	}

	/**
	 * addInclude
	 *
	 * @param array $classes
	 * @access public
	 * @return void
	 */
	function addIncludes($classes)
	{
		foreach($classes as $name => $file)
			$this->addInclude($name, $file);
	}

	/**
	 * autoLoad($name)
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function autoLoad($name)
	{
// 		$name = strtolower($name);
		if( isset( $this->includes[$name]))
		{
			include_once($this->includes[$name]);
			return true;
		}
		return false;
	}

	/**
	 * init
	 *
	 * @access public
	 * @return void
	 */
	function init()
	{
		foreach($this->components as $name => $object)
		{
			if(!isset($this->init[$name]) || !$this->init[$name])
			{
				$this->includeConfig($name);
				$object->init();
				$this->init[$name] = true;
			}
		}
	}

	/**
	 * run
	 *
	 * @access public
	 * @return void
	 */
	function run()
	{
		foreach($this->components as $name => $object)
		{
			$object->run();
		}
	}

	/**
	 * includeConfig
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function includeConfig($name)
	{
		$name = strtolower($name);
		if (file_exists($this->appPath . "/config/$name.php"))
			include($this->appPath . "/config/$name.php");
	}
}

/**
 * component
 *
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class component
{
	/**
	 * required
	 *
	 * @var array
	 * @access public
	 */
	var $required = array();

	/**
	 * component
	 *
	 * @access public
	 * @return void
	 */
	function component()
	{
		//default constructor does nothing
	}

	/**
	 * requireComponent
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function requireComponent($name)
	{
		$this->required[] = $name;
	}

	/**
	 * &getRequiredComponents
	 *
	 * @access public
	 * @return void
	 */
	function &getRequiredComponents()
	{
		return $this->required;
	}

	/**
	 * getIncludes
	 *
	 * @access public
	 * @return void
	 *
	 */
	function getIncludes()
	{
		return array();
	}

	/**
	 * init
	 *
	 * @access public
	 * @return void
	 */
	function init()
	{
		//default init does nothing
	}

	/**
	 * run
	 *
	 * @access public
	 * @return void
	 */
	function run()
	{
		//really shouldn't do anything, unless its the app_component
	}
}

?>
