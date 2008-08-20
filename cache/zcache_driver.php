<?
/**
* @package cache
*/

// Copyright (c) 2006 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/**
* An easy to use, high performance, and secure caching system.
* zcache functions are intended to be called statically or from an instantiated object.
*
* @author John Goulah
* @version 1.1
* @since 1.2
* @package cache
* @access public
* @copyright Supernerd LLC and Contributors
*
*/

class zcache_driver {

	var $group;

	/**
	 * getGroup
	 * get the group name (default = 'default').
	 * Used by cache and get.
	 *
	 * @param string $id
	 * @param mixed $data
	 * @param array $options
	 * @access public
	 * @return boolean
	 */
	function _getGroup($options = array())
	{
		if (isset($options['group']))
			return $options['group'];

		if (isset($this->group))
			return $this->group;

		return 'defaultgroup';
	}

	// pure virtual -- define in subclass
  	function cache($id, $data, $options = array()) {}
	
	// pure virtual -- define in subclass
	function get($id, $options = array()) {}
	
	// pure virtual -- define in subclass
  	function clear($id, $options = array()) {}
	
	// pure virtual -- define in subclass
	function clearGroup($group = "default", $options = array(), $mode = "normal") {}
	
	// pure virtual -- define in subclass
	function clearBase($base) {}
	
	// pure virtual -- define in subclass
	function clearAllCache() {}
}


?>
