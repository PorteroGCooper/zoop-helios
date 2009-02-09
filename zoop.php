<?php

/**
 * @file
 *
 * Two classes are contained in this file: the Zoop class, and the Component base.
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
 * @mainpage Welcome to the Zoop Framework 'Lunar' branch
 * 
 * Welcome to the Zoop Framework 'Lunar' branch. Lunar is currently a development branch
 * for testing new features and ideas for Zoop Framework 2.0. So if you use this branch,
 * please expect things to break :)
 *
 * Newcomers to Zoop should check out
 * {@link http://zoopframework.com/docs/from-a-to-zoop From A to Zoop},
 * a beginner's guide to Zoop.
 *
 * - {@link http://zoopframework.com/docs Zoop Documentation}
 *   - {@link http://zoopframework.com/docs/from-a-to-zoop From A to Zoop}
 *   - {@link http://zoopframework.com/docs/user-manual The Zoop Users Manual}
 *   - {@link http://zoopframework.com/docs/cookbook The Zoop Cookbook}
 * - {@link http://zoopframework.com/docs/user-manual/components Zoop Components}
 *   - App
 *   - Auth
 *   - Cache
 *   - Chart
 *   - DB
 *   - Forms
 *   - FPDF
 *   - Graphic
 *   - GUI
 *   - Mail
 *   - PDF
 *   - Sequence
 *   - Session
 *   - Spell
 *   - Storage
 *   - Userfiles
 *   - Validate
 *   - XML
 *   - Zone
 */


if(!defined('zoop_autoload') || zoop_autoload) {
	function __autoload($name) {
		global $zoop;

		if($zoop->autoLoad($name)) {
			return true;
		} else {
			return false; 
			//trigger_error("class $name not registered with the \$zoop object, try \$zoop->include(\"$name\",\"<fileName>\")");
		}
	}
}

/**
 * The Zoop class.
 * The Zoop class is the glue that brings all the different components together.
 * It ties the config to the code and launches the controller.
 * 
 * @group zoop
 * @endgroup
 * 
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class Zoop {

	/**
	 * @var array $init
	 * @access private
	 */
	private $init = array();
	private $environmentCache = array();
	
	/**
	 * @var array $components
	 * @access private
	 */
	private $components = array();

	/**
	 * The Zoop class constructor.
	 *
	 * The constructor adds in the "app" component, which required for all zoop apps.
	 *
	 * @param mixed $appPath
	 * @access public
	 * @return void
	 */
	function __construct($appPath = null) {
		$this->path = dirname(__file__);
		if ($appPath == null) {
			$this->appPath = $this->path;
		} else {
			$this->appPath = $appPath;
		}

		$this->getEnvironmentCache();
		$this->addComponent('config');
	}


	/**
	 * Add a Zoop component.
	 *
	 * Includes the component, the configuration for the component
	 * and all dependencies. addComponent will use autoload if available.
	 *
	 * Broken up to facilitate easier overloading required for the zoop_test script
	 *
	 * @param string $name The name of the component to include.
	 * @access public
	 * @return void
	 */
	function addComponent($name) {
		if(!isset($this->components[$name])) {
			$currComponent = $this->instantiateComponent($name);
			$this->addComponentConfig($name, $currComponent);
			$this->addRequiredComponents($currComponent);
			$this->addIncludes($currComponent->getIncludes());
			$this->components[$name] = &$currComponent;
			
			// if the rest of the components have been initialized, init and run this one too.
			if (isset($this->init['zoop']) && $this->init['zoop']) {
				if (!isset($this->init[$name]) || !$this->init[$name]) {
					$this->checkComponentEnvironment($name);
					$this->components[$name]->init();
					$this->components[$name]->run();
					$this->init[$name] = true;
				}
			}
		}
	}
	

	/**
	 * Find and instantiate the component 
	 * 
	 * @param mixed $name 
	 * @access public
	 * @return void
	 */
	function instantiateComponent($name) {
		include($this->path . "/$name/{$name}_component.php");
		$class = "component_{$name}";
		$currComponent = &new $class();
		return $currComponent;
	}

	/**
	 * Load the config for the component 
	 * 
	 * @param string $name 
	 * @param Component $currComponent 
	 * @access public
	 * @return void
	 */
	function addComponentConfig($name, &$currComponent) {
		if ($name != 'spyc' && $name != 'config') {
			$this->includeConfig($name);
			/* can't load config before config component is loaded */
			$currComponent->loadConfig();
		}
	}
	
	/**
	 * Include the given config file.
	 *
	 * @param string $name
	 * @access public
	 * @return bool Success
	 */
	private function includeConfig($name) {
		$name = strtolower($name);
		$file = Config::get('zoop.app.directories.config') . '/' . $name . '.yaml';
		if (file_exists($file)) {
			Config::suggest($file);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Find and add all required components 
	 * 
	 * @param Component $currComponent 
	 * @access private
	 * @return void
	 */
	private function addRequiredComponents(&$currComponent) {
		$components = $currComponent->getRequiredComponents();
		foreach($components as $newname) {
			$this->addComponent($newname);
		}
	}
	
	/**
	 * Check the environment for a given component.
	 * 
	 * Retrieves the cached state, if it exists. Otherwise checks and saves the component environment.
	 * 
	 * @access private
	 * @param string $name
	 * @return bool Success
	 */
	private function checkComponentEnvironment($name) {
		if (isset($this->environmentCache[$name]) && $this->environmentCache[$name]) {
			return true;
		} else {
			return $this->cacheEnvironment($name, $this->components[$name]->checkEnvironment());
		}
	}
	
	/**
	 * Cache component environment state. Used internally by Zoop::checkComponentEnvironment()
	 * 
	 * @access private
	 * @param string $name
	 * @param bool $state
	 * @return bool Environment state
	 */
	private function cacheEnvironment($name, $state) {
		$this->environmentCache[$name] = $state;
		
		$cache_file = CONFIG_CACHE_DIR . '/environment.php';
		if (!file_exists($cache_file)) {
			mkdir_r($cache_file);
		} else if (!is_writable($cache_file)) {
			trigger_error("Unable to write to environment cache file: $cache_file. Make sure file exists and is writable.");
			return;
		}
		file_put_contents($cache_file, "<?php \n\n" . '$this->environmentCache = ' . var_export($this->environmentCache, true) . ";\n\n");
		
		return $state;
	}
	
	/**
	 * Load the environment cache file.
	 * 
	 * @access private
	 * @return void
	 */
	private function getEnvironmentCache() {
		$cache_file = CONFIG_CACHE_DIR . '/environment.php';
		if (file_exists($cache_file)) {
			include($cache_file);
		}
	}
	
	/**
	 * Accessor for a component 
	 * Will load component if it has not been loaded.
	 * 
	 * @param mixed $name 
	 * @access public
	 * @return void
	 */
	function component($name) {
		$this->addComponent($name);
		if (isset($this->components[$name])) {
			return $this->components[$name];
		} else {
			trigger_error("Component $name does not exist");
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
		$zone_dir = Config::get('zoop.zone.directory');
		$zone_name = 'zone_' . str_replace('/', '_', $name);
		$this->addInclude($zone_name, $zone_dir . DIRECTORY_SEPARATOR . $name . '.php');
	}

	/**
	 * Add a decorator zone to the application.
	 * Decorator zones can attach themselves to many zones, controlled by $zone->allowedParents, $zone->allowedChildren
	 * 
	 * A zone is a section of the controller. Zones are analagous to a directory.
	 * addZone use autoload if available.
	 *
	 * @param string $name The name of the zone to add.
	 * @access public
	 * @return void
	 */
	function addDecoratorZone($name) {
		$this->addComponent('zone');
		$zone_dir = Config::get('zoop.zone.decorators_directory');
		$zone_name = "zone_" . str_replace( DIRECTORY_SEPARATOR, "_", $name);
		$this->addInclude($zone_name,  "$zone_dir" . DIRECTORY_SEPARATOR . "{$name}.php");
	}

	/**
	 * Loads a Model object
	 * A model is a model object representative of a database table, view, etc. 
	 * You may roll your own, use doctrine or other. 
	 *
	 * @see addModel
	 * @depreciated
	 * @param string $name
	 * @param string $file Optionally, speficify the filename of the object to add.
	 * @access public
	 * @return void
	 */
	function addObject($name, $file = '') {
		$dir = Config::get('zoop.app.directories.objects');
		if(!empty($file)) {
			$file = "$dir/$file";
		} else {
			$file = "$dir/$name.php";
		}
		$this->addInclude("$name", $file);
		$this->addModel($name, $file);
	}

	/**
	 * Loads a Model object or class.
	 * 
	 * ********************************************************************
	 * Not sure if this is useful. Leaving in here for now. 
	 * Added by SPF Aug 29th
	 * ********************************************************************
	 *
	 * A model is a model class or object representative of a database table, view, etc. 
	 * You may roll your own, use doctrine or other. 
	 *
	 * @param string $name
	 * @param string $file Optionally, speficify the filename of the object to add within the models directory
	 * @access public
	 * @return void
	 */
	function addModel($name, $file = '') {
		$dir = Config::get('zoop.app.directories.model');
		if(!empty($file)) {
			$file = "$dir/$file";
		} else {
			$file = "$dir/$name.php";
		}
		$this->addInclude("$name", $file);
	}

	/**
	 * addClass
	 * Set a class for inclusion. 
	 * Classes are found in APP_DIR/lib by default, the same as lib
	 *
	 * @param string $name
	 * @param string $file Optionally, speficify the filename of the object to add within the classes directory
	 * @access public
	 * @return void
	 */
	function addClass($name, $file = '') {
		$dir = Config::get('zoop.app.directories.classes');
		if(!empty($file)) {
			$file = "$dir/$file";
		} else {
			$file = "$dir/$name.php";
		}
		$this->addInclude("$name", $file);
	}	
	
	/**
	 * addLib
	 * Sets a lib (library file) for inclusion.
	 * Libs are found in APP_DIR/lib by default, the same as classes
	 *
	 * @param string $name
	 * @param string $file Optionally, speficify the filename of the object to add within the lib directory
	 * @access public
	 * @return void
	 */
	function addLib($name, $file = '') {
		$dir = Config::get('zoop.app.directories.lib');
		if(!empty($file)) {
			$file = "$dir/$file";
		} else {
			$file = "$dir/$name.php";
		}
		$this->addInclude("$name", $file);
	}	
	
	/**
	 * Zoop includer.
	 * 
	 * In a PHP 5+ environment with autoinclude it will maintain a hash
	 * of all includes and using autoinclude, will only include each file one time.
	 * 
	 * In a PHP 4 environment, this is a wrapper for include_once()
	 * 
	 * @param mixed $names
	 *    The class name (or a array of names) found in $file.
	 * @param string $file
	 * @access public
	 * @return void
	 */
	function addInclude($names, $file) {
		foreach ((array)$names as $name) {
			$this->includes[strtolower($name)] = $file;
		}
		if (version_compare(PHP_VERSION, "5.0", "<")) {
			include_once($file);
		}
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
	 * The Zoop autoloader.
	 *
	 * @param mixed $name
	 * @access public
	 * @return boolean
	 */
	function autoLoad($name) {
		$name = strtolower($name);

		if (isset($this->includes[$name])) {
			include_once($this->includes[$name]);
			return true;
		}
		return false;
	}

	/**
	 * Initalizes Zoop.
	 *
	 * Initializes all included components and registers Zoop::autoLoad() as an autoload handler.
	 *
	 * @access public
	 * @return void
	 */
	function init() {
		foreach($this->components as $name => $object) {
			if(!isset($this->init[$name]) || !$this->init[$name]) {
				$this->checkComponentEnvironment($name);
				$object->init();
				$this->init[$name] = true;
			}
		}
		$this->init['zoop'] = true;
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
abstract class Component {
	
	private $name;
	
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
	function __construct() { }

	/**
	 * getBasePath 
	 * Returns the base path for _this_ specific component.
	 * 
	 * @access public
	 * @return void
	 */
	function getBasePath() {
		return ZOOP_DIR . "/" . $this->getName();
	}

	/**
	 * Get the (lower case) name of this component.
	 * 
	 * @access public
	 * @return string
	 */
	function getName() {
		if (!isset($this->name)) {
			$this->name = strtolower(substr(get_class($this), 10));
		}
		
		return $this->name;
	}

	/**
	 * Add a dependency for this component.
	 *
	 * @code
	 *   $this->requireComponent('db');
	 * @endcode
	 *
	 * @param string $name
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
	 * Get all includes needed for this component to run.
	 * 
	 * This method should be overloaded by Component classes. It returns an array, like the following:
	 * 
	 * @code
	 *   $file = $this->getBasePath();
	 *   return array(
	 *     // 'class_name'  => 'file_name',
	 *     "form2"          => $file . "/forms2.php",
	 *     "form"           => $file . "/forms.php",
	 *     "table"          => $file . "/table.php",
	 *     "record"         => $file . "/record.php",
	 *     "field"          => $file . "/field.php",
	 *     "cell"           => $file . "/cell.php",
	 *     "xml_serializer" => "XML/Serializer.php"
	 *   );
	 * @endcode
	 *
	 * @access public
	 * @return array
	 */
	function getIncludes() {
		return array();
	}

	/**
	 * getConfigPath 
	 * The location in the config variable to find this component's config
	 * By default it is the name of the component.
	 *	
	 * @access public
	 * @return void
	 */
	function getConfigPath() {
		return $this->getName();
	}
	
	/**
	 * loadConfig 
	 * Loads the config.yaml file from the component directory.
	 * Sets these values as the defaults for the app to overwrite.
	 * 
	 * @see Config::suggest
	 * @access private
	 * @return void
	 */
	function loadConfig() {
		Config::suggest(ZOOP_DIR . '/' . $this->getName() . '/' . 'config.yaml', 'zoop.' . $this->getConfigPath());
	}

	/**
	 * Returns the configuration options using the Config class.
	 * Returns config options from "zoop.<modulename>.<path>"
	 * Path is optional and may be omitted.
	 *
	 * @param string $path
	 * @return array of configuration options
	 */
	function getConfig($path = '') {
		$config = Config::get('zoop.' . $this->getConfigPath() . $path);
		return $config;
	}
	
	/**
	 * Check environment requirements for this component.
	 * 
	 * To be overloaded by extending classes.
	 * 
	 * This is the correct place to check the existance and writability of cache folders,
	 * etc. Return true if everything checks out. Note that if this function returns true
	 * it will not be executed on future page loads (until the config cache is cleared).
	 * 
	 * @access public
	 * @return bool
	 */
	function checkEnvironment() {
		// return true if environment checks out
		return true;
	}

	/**
	 * Initialize this component.
	 * 
	 * To be overloaded.
	 * 
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
		// default init does nothing
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
		// really shouldn't do anything, unless its the app_component
	}
}
