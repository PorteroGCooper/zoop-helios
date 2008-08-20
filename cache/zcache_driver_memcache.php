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

class zcache_driver_memcache extends zcache_driver {

	var $memcache;
	var $basename;
	var $basekeys = "base_keys";
	var $ttl = 0;


	function zcache_driver_memcache($options = array()) 
	{
		global $memcache_servers;
		$this->memcache = new Memcache();
		if (isset($memcache_servers)) 
		{
			foreach ($memcache_servers as $server) 
			{
				$this->memcache->addServer($server, 11211);
			}
		} 
		else 
		{
			// if the config isn't setup properly use localhost
			$this->memcache->addServer("localhost", 11211);
		}


		if (isset($options['base'])) 
		{
			$this->basename = $options['base'];
		} 
		else 
		{
			$this->basename = "defaultbase";
		}

		// sets the group class var in the base class
		if (isset($options['group'])) 
		{
			$this->group = $options['group'];
		} 
		else 
		{
			$this->group = "defaultgroup";
		}
	}


	function cache($id, $data, $options = array()) 
	{
		$ttl = $this->ttl;
		if (isset($options['lifeTime']))
		{
			$this->ttl = $options['lifeTime'];
		}

		$group = $this->_getGroup($options);
		// add base/group to list of keys
		$this->_addBaseKey($this->basename);
		$this->_addGroupKey($this->basename, $group); 
		$key = $group .':'. $id;
		$this->_addNodeKey($group, $key); // add the node to node keys for the group
		return $this->memcache->set($this->keyify($key), $data, 0, $this->ttl);
	}

	function keyify($key) {
		//return md5($key);
		return $key;
	}
	
	function get($id, $options = array()) 
	{
		$group = $this->_getGroup($options);
 		$key = $group .':'. $id;
		return $this->memcache->get($this->keyify($key));
	}

	
  	function clear($id, $options = array(), $append=true) 
	{
		$group = $this->_getGroup($options);
		if ($append) {
			$id = $group .':'. $id;
		}
		$this->_deleteNodeKey($group, $id);
		return $this->memcache->delete($id);
	}

	
	function clearGroup($group = "default", $options = array(), $mode = "normal", $append=true) 
	{
		$uniq_group = $group;
		if ($append)
			$uniq_group = $this->_createGroupName($group);
		 
		// find the base this group is in and delete it
		$group_base = $this->_getGroupBase($uniq_group);
		if (strlen($group_base)) 
		{
			$this->_deleteGroupKey($group_base, $uniq_group);
		}

		// get all the keys of the group, and delete them
		$node_keys = $this->_getNodeKeysArray($uniq_group);
		if (count($node_keys)) 
		{
			foreach ($node_keys as $node) 
			{
				$this->clear($node, array('group'=>$group), false);
			}
		}

		// then delete the group key holding the node keys
		$this->memcache->delete($uniq_group);
	}

	
	function clearBase($base) 
	{
		// clear all the group and nodes within
		$groupkey_arr =	$this->_getGroupKeysArray($base);
		if (count($groupkey_arr)) 
		{
			foreach($groupkey_arr as $group) 
			{
				$this->clearGroup($group, array(), "normal", false);
			}
		}

		// delete from the list of base keys
		$this->_deleteBaseKey($base);

		// delete the base key itself
		$this->memcache->delete($base);
	}

	
	function clearAllCache() 
	{
     	$basekey_arr = $this->_getBaseKeysArray(); 
		if (count($basekey_arr)) 
		{
			foreach($basekey_arr as $base) 
			{
				$this->clearBase($base);
			}
		}

		$this->memcache->delete($this->basekeys);
	}

	/////////////////////////////
	/////////////////////////////
	////////// private //////////
	/////////////////////////////
	/////////////////////////////

	// private
	// overloaded from base class
	function _getGroup($options)
	{
		$group = zcache_driver::_getGroup($options);
		return $this->_createGroupName($group);
	}

	function _createGroupName($group) 
	{
		return $this->basename .':'. $group; 
	}

	// private
	// gets the base keys
	function _getBaseKeys() 
	{
		return $this->memcache->get($this->basekeys);
	}

	// private
	function _getBaseKeysArray() 
	{
		$keys = $this->_getBaseKeys(); 
		return $this->_explodeKeys($keys);
	}

	// private
	function _addBaseKey($key) {
		$basekey_arr = $this->_getBaseKeysArray();
		$this->_addKeyToArray($key, $basekey_arr);
		$this->memcache->set($this->basekeys, implode(',', $basekey_arr));
	}
			
	// private
	function _deleteBaseKey($base)
	{
		$basekey_arr = $this->_getBaseKeysArray();
		array_remval($base, $basekey_arr);
		$this->memcache->set($this->basekeys, implode(',', $basekey_arr));
	}


	// private
	// gets the group keys for a given base
	function _getGroupKeys($base) 
	{
		return $this->memcache->get($base);
	}

	// private
	function _getGroupKeysArray($base) 
	{
		$keys = $this->_getGroupKeys($base); 
		return $this->_explodeKeys($keys);
	}

	// private
	function _addGroupKey($base, $key) 
	{
		$groupkey_arr = $this->_getGroupKeysArray($base);
		$this->_addKeyToArray($key, $groupkey_arr);
		$this->memcache->set($base, implode(',', $groupkey_arr));
	}

	// private
	function _deleteGroupKey($base, $group)
	{
		$groupkey_arr = $this->_getGroupKeysArray($base);
		array_remval($group, $groupkey_arr);
		$this->memcache->set($base, implode(',', $groupkey_arr));
	}

	// private
	function _getGroupBase($group) 
	{
		$basekey_arr = $this->_getBaseKeysArray();
		if (count($basekey_arr)) 
		{
			foreach($basekey_arr as $base) 
			{
				$groupkey_arr = $this->_getGroupKeysArray($base);
				if (in_array($group, $groupkey_arr)) 
				{
					return $base;
				}
			}
		}
		trigger_error("could not find group base for group: $group");
		return "";
	}

	// private
	// gets the node keys for a given group 
	function _getNodeKeys($group) 
	{
		return $this->memcache->get($group);
	}

	// private
	function _getNodeKeysArray($group) 
	{
		$keys = $this->_getNodeKeys($group); 
		return $this->_explodeKeys($keys);
	}

	// private
	function _addNodeKey($group, $key) 
	{
		$nodekey_arr = $this->_getNodeKeysArray($group);
		$this->_addKeyToArray($key, $nodekey_arr);
		$this->memcache->set($group, implode(',', $nodekey_arr));
	}

	function _deleteNodeKey($group, $key) 
	{
		$nodekey_arr = $this->_getNodeKeysArray($group);
		array_remval($key, $nodekey_arr);
		$this->memcache->set($group, implode(',', $nodekey_arr));
	}

	// private
	// explodes on commma, or return empty array if param is empty
	function _explodeKeys($keys) 
	{
		if  (strlen($keys))
			return explode(',', $keys);
		else
			return array();
	}

	// private
	// adds key to array if it doesnt exist in the array
	function _addKeyToArray($key, &$arr) 
	{
		if (!in_array($key, $arr)) 
		{
			$arr[] = $key;
		}
	}

	// testing only- dont use this, will clear entire memcache cache
	function flushMem() {
		$this->memcache->flush();
	}

} /*zcache_driver_memcache*/


?>
