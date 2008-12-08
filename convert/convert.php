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
 * Convert takes data from a given format and converts it to and from a php data type (associated array).
 *
 * @package
 * @version $id$
 * @copyright 1997-2008 Portero Inc.
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */

include_once(dirname(__file__) . "/drivers/abstract.php");

class convert {

	/**
	 * array holding the loaded drivers
	 *
	 * @var mixed
	 * @access public
	 */
	static $drivers;

	/**
	 * Get the backend driver and load it into the instance var.
	 *
	 * @access protected
	 * @return void
	 */
	private function _loadDriver($backend) {
		global $zoop;
		$name = "convert_driver_" . $backend;
		$zoop->addInclude($name, ZOOP_DIR . "/convert/drivers/$backend.php");
		if (class_exists($name)) {
			self::$drivers[$backend] = new $name($this);
			return self::$drivers[$backend];
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
		if (!self::$drivers[$backend]) {
			return self::_loadDriver($backend);
		}

		return self::$drivers[$backend];
	}

	/**
	 * Get the Driver and call test on it.
	 * Driver->test() will connect to the store and return true;
	 *
	 * @access public
	 * @return void
	 */
	function testDriver($driver) {
		$drv = self::getDriver($driver);
		return $drv->test();
	}

	/**
	 * Called by the each to* method, wraps driver to call.
	 * @param $name
	 * @param $args
	 * @return unknown_type
	 */
	private function _to ($name, $data, $args = array()) {
		$driver = self::_loadDriver($name);
		return $driver->to($data, $args);
	}

	/**
	 * Called by the each from* method, wraps driver from call.
	 * @param $name
	 * @param $args
	 * @return unknown_type
	 */
	private function _from ($name, $data, $args = array() ) {
		$driver = self::loadDriver($name);
		return $driver->from($data, $args);
	}

	function toJSON($data, $args = array()) { 
		return self::_to('json', $data, $args);
	}

	function fromJSON($data, $args = array()) { 
		return self::_from('json', $data, $args);
	}

	function toSerialized($data, $args = array()) { 
		return self::_to('serialized', $data, $args);
	}

	function fromSerialized($data, $args = array()) { 
		return self::_from('serialized', $data, $args);
	}

	function toXML($data, $args = array()) { 
		return self::_to('xml', $data, $args);
	}

	function fromXML($data, $args = array()) { 
		return self::_from('xml', $data, $args);
	}

	function toCSV($data, $args = array()) { 
		return self::_to('csv', $data, $args);
	}

	function fromCSV($data, $args = array()) { 
		return self::_from('csv', $data, $args);
	}

	function toYAML($data, $args = array()) { 
		return self::_to('yaml', $data, $args);
	}

	function fromYAML($data, $args = array()) { 
		return self::_from('yaml', $data, $args);
	}

	function toXLS($data, $args = array()) { 
		return self::_to('xls', $data, $args);
	}

	function fromXLS($data, $args = array()) { 
		return self::_from('xls', $data, $args);
	}

}
