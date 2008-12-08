<?php
/**
* @category zoop
* @package convert
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
 * convert
 * A metaclass to wrap the conversion drivers. 
 * Convert takes data from a given format and converts it to and from a php associated array.
 *
 * @package
 * @version $id$
 * @copyright 1997-2008 Portero Inc.
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */

static class convert {

	/**
	* The following variables and methods should be duplicated in each class that extends this one
	*/

	private static $instance;

	/**
	 * The private construct prevents instantiating the class externally.  
	 * 
	 * @access private
	 * @return void
	 */
	private function __construct() { }

	/**
	 * Prevents external instantiation of copies of the Singleton class,
	 * 
	 * @access public
	 * @return void
	 */
	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

	/**
	 * Prevents external instantiation of copies of the Singleton class,
	 * 
	 * @access public
	 * @return void
	 */
	public function __wakeup() {
		trigger_error('Deserializing is not allowed.', E_USER_ERROR);
	}

	/**
	 * get Instance: a singleton method 
	 * 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function gi() {
		if (!self::$instance instanceof self) { 
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __call($method, $args) {
		if (substr($method, 0, 2) == 'to') {
			$param_name = lcfirst(substr($method, 2));
			array_unshift($args, $param_name);
			return call_user_func_array(array($this, '_to'), $args);
		} elseif (substr($method, 0, 4) == 'from') {
			$param_name = lcfirst(substr($method, 4));
			array_unshift($args, $param_name);
			return call_user_func_array(array($this, '_from'), $args);
		}

	}

	/**
	 * array holding the loaded drivers 
	 * 
	 * @var mixed
	 * @access public
	 */
	var $drivers;

	/**
	 * Get the backend driver and load it into the instance var. 
	 * 
	 * @access protected
	 * @return void
	 */
	function _loadDriver($backend) {
		global $zoop;
		$name = "convert_driver_" . $backend;
		$zoop->addInclude($name, ZOOP_DIR . "/convert/drivers/$backend.php");
		if (class_exists($name)) {
			$this->drivers[$backend] = new $name($this);
			return $this->drivers[$backend];
		} else {
			trigger_error("Invalid Driver: $name");
		}
	}

	/**
	 * get the backend driver 
	 * 
	 * @access public
	 * @return void
	 */
	function getDriver($backend) {
		if (!$this->drivers[$backend]) {
			return $this->_loadDriver($backend);
		}

		return $this->drivers[$backend];
	}

	/**
	 * Get the Driver and call test on it. 
	 * Driver->test() will connect to the store and return true; 
	 * 
	 * @access public
	 * @return void
	 */
	function testDriver($driver) {
		$drv = $this->getDriver($driver);
		return $drv->test();
	}

	public function _to ($name, $args ) {

	}

	public function _from ($name, $args ) {

	}
}
