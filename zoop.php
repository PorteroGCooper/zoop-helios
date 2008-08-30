<?php
/**
 * @file
 *
 * Two classes are contained in this file: the Zoop class, and the Component base.
 *
 * @group zoop
 */

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
 * @mainpage Welcome to the Zoop Framework
 * 
 * Welcome to the Zoop Framework. Newcomers to Zoop should check out
 * {@link http://zoopframework.com/docs/from-a-to-zoop From A to Zoop},
 * a beginner's guide to Zoop.
 *
 * - {@link http://zoopframework.com/docs Zoop Documentation}
 *   - {@link http://zoopframework.com/docs/from-a-to-zoop From A to Zoop}
 * - {@link components Zoop Components}
 *   - {@link app App}
 *   - {@link auth Auth}
 *   - {@link cache Cache}
 *   - {@link chart Chart}
 *   - {@link db DB}
 *   - {@link forms Forms}
 *   - {@link fpdf FPDF}
 *   - {@link graphic Graphic}
 *   - {@link gui GUI}
 *   - {@link mail Mail}
 *   - {@link pdf PDF}
 *   - {@link sequence Sequence}
 *   - {@link session Session}
 *   - {@link spell Spell}
 *   - {@link storage Storage}
 *   - {@link userfiles Userfiles}
 *   - {@link validate Validate}
 *   - {@link xml XML}
 *   - {@link zone Zone}
 */

if(!defined('zoop_autoload') || zoop_autoload)
{
	function __autoload($name)
	{
		global $zoop;
		if($zoop->autoLoad($name))
			return true;
		else
			return false; //trigger_error("class $name not registered with the \$zoop object, try \$zoop->include(\"$name\",\"<fileName>\")");
	}
}

/**
 * The zoop class.
 *
 * The Zoop class is the glue that brings all the different components together.
 * It ties the config to the code and launches the controller.
 *
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
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
	 * The zoop constructor.
	 *
	 * The constructor adds in the "app" component, which required for all zoop apps.
	 *
	 * @param mixed $appPath
	 * @access public
	 * @return void
	 */
	function zoop($appPath = NULL) {
		$this->path = dirname(__file__);
		if ($appPath == NULL) {
			$this->appPath = $this->path;
		} else {
			$this->appPath = $appPath;
		}

		$this->addComponent('config');
		$this->addComponent('app'); //zoop always includes app_component
	}


	/**
	 * Add a Zoop component.
	 *
	 * Includes the component, the configuration for the component
	 * and all dependencies. addComponent will use autoload if available.
	 *
	 * @param string $name The name of the component to include.
	 * @access public
	 * @return void
	 */
	function addComponent($name) {
		if(!isset($this->components[$name]))
		{
			include($this->path . "/$name/{$name}_component.php");
			$class = "component_{$name}";
			$currComponent = &new $class();
			$this->includeConfig($name);
			$currComponent->defaultConstants();
			$components = &$currComponent->getRequiredComponents();
			foreach($components as $newname)
			{
				$this->addComponent($newname);
			}
			$this->addIncludes($currComponent->getIncludes());
			$this->components[$name] = &$currComponent;
		}
	}

	/**
	 * Add a zone to the application.
	 * 
	 * A zone is a section of the controller. Zones are analagous to a directory.
	 * addZone use autoload if available.
	 *
	 * @param string $name The name of the zone to add.
	 * @access public
	 * @return void
	 */
	function addZone($name) {
		$this->addComponent('zone');
		if (defined('legacy_app_layout') && !legacy_app_layout ) {
			$this->addInclude("zone_{$name}", $this->appPath . "/zones/{$name}.php");
		} else {
			$this->addInclude("zone_{$name}", $this->appPath . "/zone_{$name}.php");
		}
	}

	/**
	 * addObject
	 *
	 * @param string $name
	 * @param string $file Optionally, speficify the filename of the object to add.
	 * @access public
	 * @return void
	 */
	function addObject($name, $file = '') {
		if(!empty($file)) {
			$file = $this->appPath . "/objects/$file";
		} else {
			$file = $this->appPath . "/objects/$name.php";
		}
		$this->addInclude($name, $file);
	}

	/**
	 * addClass
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function addClass($name) {
		$file = $this->appPath . "/classes/$name.php";
		$this->addInclude($name, $file);
		//if (file_exists($file))
		//	include($file);
	}	
	
	/**
	 * Zoop includer.
	 *
	 * In a php 5+ environment with autoinclude it will maintain a hash
	 * of all includes and using autoinclude, will only include each file one time.
	 *
	 * In a php 4 environment, this is a wrapper for include_once()
	 *
	 * @param mixed $name
	 * @param mixed $file
	 * @access public
	 * @return void
	 */
	function addInclude($name, $file) {
		$this->includes[strtolower($name)] = $file;
		if(version_compare(PHP_VERSION, "5.0", "<"))
			include_once($file);
	}

	/**
	 * addIncludes
	 * 
	 * Simply a helper function to include many files with addInclude.
	 *
	 * @see zoop::addIncludes()
	 *
	 * @param array $classes
	 * @access public
	 * @return void
	 */
	function addIncludes($classes) {
		foreach($classes as $name => $file) {
			$this->addInclude($name, $file);
		}
	}

	/**
	 * autoLoad
	 *
	 * @param mixed $name
	 * @access public
	 * @return boolean
	 */
	function autoLoad($name) {
 		$name = strtolower($name);
		if( isset( $this->includes[$name])) {
			include_once($this->includes[$name]);
			return true;
		}
		return false;
	}

	/**
	 * init
	 * Initalizes the components.
	 * Calls the init method for each component.
	 *
	 * @access public
	 * @return void
	 */
	function init() {
		foreach($this->components as $name => $object) {
			if(!isset($this->init[$name]) || !$this->init[$name]) {
				$object->init();
				$this->init[$name] = true;
			}	
		}
		spl_autoload_register(array($this,'autoLoad'));
	}

	/**
	 * run
	 * runs the components.
	 * Calls the run method for each component.
	 *
	 * Most components will not have a run method.
	 * Special cases, would be the db and app
	 *
	 * @access public
	 * @return void
	 */
	function run() {
		foreach($this->components as $name => $object) {
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
 * The component class is a meta class of sorts. 
 * For each component a "component class" is required. 
 *
 * This is the base class for those "component classes"
 *
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
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
	 * Default constructor does nothing
	 *
	 * @access public
	 * @return void
	 */
	function component() { }

	/**
	 * getBasePath 
	 * Returns the base path for _this_ specific component.
	 * 
	 * @access public
	 * @return void
	 */
	function getBasePath() {
		return zoop_dir . "/" . $this->getName();
	}

	/**
	 * getName 
	 * Returns the name of the component
	 * 
	 * @access public
	 * @return void
	 */
	function getName() {
		$className = get_class($this);
		return $componentName = substr($className, 10);
	}

	/**
	 * defaultContstants
	 * Returns the default configuration constants.
	 * Included from the defaultConstants file bundled in the component.
	 *
	 * @access public
	 */
	function defaultConstants() {
		include($this->getBasePath() . "/defaultConstants.php");
	}

	/**
	 * requireComponent
	 *
	 * Setup a dependency of this component.
	 *
	 * @code
	 * $this->requireComponent('db');
	 * @endcode
	 *
	 * @param mixed $name
	 * @access public
	 * @return void
	 */
	function requireComponent($name) {
		$this->required[] = $name;
	}

	/**
	 * &getRequiredComponents
	 * Return components this component is dependant on. 
	 *
	 * @access public
	 * @return array
	 */
	function &getRequiredComponents() {
		return $this->required;
	}

	/**
	 * getIncludes
	 *
	 * To be overloaded. 
	 * Typically this method will establish all the files that this 
	 * component needs to run 
	 *
	 * It returns an array. An example of this is
	 * @code
	 * $file = $this->getBasePath();
	 * return array(
	 * 			"form2" => $file . "/forms2.php",
	 *			"form" => $file . "/forms.php",
	 *			"table" => $file . "/table.php",
	 *			"record" => $file . "/record.php",
	 *			"field" => $file . "/field.php",
	 *			"cell" => $file . "/cell.php",
	 *			"xml_serializer" => "XML/Serializer.php"
	 *	);
	 *	@endcode
	 *
	 * @access public
	 * @return array
	 *
	 */
	function getIncludes() {
		return array();
	}



	/**
	 * getConfigPath 
	 * 
	 * @access public
	 * @return void
	 */
	function getConfigPath()
	{
		return $this->getName();
	}

	/**
	 * loadConfig 
	 * 
	 * @access private
	 * @return void
	 */
	private function loadConfig()
	{
		Config::suggest(zoop_dir . '/' . $this->getName() . '/' . 'config.yaml', 'zoop.' . $this->getConfigPath());
	}

	/**
	 * Returns the configuration options using the Config class.
	 * Returns config options from "zoop.<modulename>.<path>"
	 * Path is optional and may be omitted.
	 *
	 * @param string $path
	 * @return array of configuration options
	 */
	function getConfig($path = '')
	{
		$config = Config::get('zoop.' . $this->getConfigPath() . $path);
		//echo_r($config);
		return $config;
	}

	/**
	 * init
	 *
	 * To be overloaded. 
	 * Some times a component may require some logic before the getIncludes. 
	 * The init method is a hook to be called before Including the component files
	 * with the getIncludes method.
	 *
	 * Will not be commonly used. Used in db.
	 * Run before getIncludes
	 *
	 * @access public
	 * @return void
	 */
	function init() {
		//default init does nothing
	}

	/**
	 * run
	 * The run method is a hook for the special case for when a component needs
	 * to run code upon inclusion. Most of the time it will be empty.
	 * Special cases are the db and app components.
	 *
	 * Run after getIncludes
	 *
	 * @access public
	 * @return void
	 */
	function run() {
		//really shouldn't do anything, unless its the app_component
	}
}

?>
