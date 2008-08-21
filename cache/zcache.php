<?
/**
 * @group cache
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
 * An easy to use, high performance, and secure caching system.
 * zcache functions are intended to be called statically or from an instantiated object.
 * We currently use the pear library, cacheLite to handle our dirty work.
 *
 * @author Steve Francia <sfrancia@supernerd.com>
 * @version 1.1
 * @since 1.2
 * @package cache
 * @access public
 * @copyright Supernerd LLC and Contributors
 *
 */
class zcache
{

	var $driver_map;  // mapping of easy name to class name
	var $driver; // holds the driver object
	var $drivername; // holds the driver class name
	
	/**
	 * Constructor.
	 * Can be used to instantiate the object
	 *
	 * @param array $options
	 *   alter the options used from the default by passing new values in an array.
	 * @return the object
	 * @access public
	 */
	function &zcache($options = array())
	{
		// make sure to add to this map if you add drivers
		$this->driver_map = array('memcache'=>'zcache_driver_memcache',
								  'cachelite'=>'zcache_driver_cachelite');

		zcache::_setDriver($options);

		if (defined('default_cache_lifeTime'))
			$default_lifeTime = default_cache_lifeTime;
		else
			$default_lifeTime = NULL; // NULL = forever

		if (defined('cache_style') && cache_style == 'performance')
		{
			$DefaultOptions = array(
				'readControl' => false,
				'writeControl' => false,
				'fileNameProtection' => false,
				'automaticSerialization' => true,
				'cacheDir' => app_cache_dir,
				'group' => 'default',
				'lifeTime' => $default_lifeTime,
				//'hashedDirectoryLevel' => 1, // in large directories helps to have subdirectories for indexing
				'automaticCleaningFactor' => 200 // clean stale files 1x out of 200 writes
			);
		}
		else
		{
			$DefaultOptions = array(
				'automaticSerialization' => true,
				'cacheDir' => app_cache_dir,
				'group' => 'default',
				'lifeTime' => $default_lifeTime,
				//'hashedDirectoryLevel' => 1, // in large directories helps to have subdirectories for indexing
				'automaticCleaningFactor' => 200 // clean stale files 1x out of 200 writes
			);
		}

		$cacheoptions = array_merge($DefaultOptions, $options);

		// instantiate driver
		require_once("{$this->drivername}.php");
		$driver = new $this->drivername($cacheoptions); 	

	  	if (isset($this) && is_object($this)  && "zcache" == strtolower(get_class($this)))
	  	{
			return $this->driver =& $driver;
		}
		else
		{
			return $driver;
		}
	}

	// static
	function _setDriver($options) {
		$default_cache_driver = "";
		if (isset($options['cache_driver'])) {
			$default_cache_driver = $options['cache_driver'];
		} else if (defined('cache_driver')) {
			$default_cache_driver = cache_driver;  
		} else {
			$default_cache_driver = 'cachelite'; 
		}

 		if (array_key_exists($default_cache_driver, $this->driver_map)) {
			$this->drivername = $this->driver_map[$default_cache_driver];
		} else {
			// driver is invalid
			trigger_error("could not instantiate driver: {$this->drivername}");
		}
	}

	/**
	 * _getCacheObj
	 * Used internally so that each function can be called either statically or from within an object
	 *
	 * @param array $array
	 * @access private
	 * @return object
	 */
	function &_getCacheObj($array = array())
	{
		if (isset($this) && is_object($this) && "zcache" == strtolower(get_class($this)))
		{
			return $this->driver;
		}
		else
		{
			$tmp = zcache::zcache($array);
			return $tmp;
		}
	}


	/**
	 * cache
	 * cache or save the data passed.
	 * Used by cacheData and cacheString.
	 *
	 * @param string $id
	 * @param mixed $data
	 * @param array $options
	 * @access public
	 * @return boolean
	 */
  	function cache($id, $data, $options = array())
  	{
		$co = zcache::_getCacheObj($options);
		return $co->cache($id, $data, $options);
	}


	/**
	 * cacheData
	 * cache or save the data passed. Data can be in any format, It will be serialized before caching.
	 *
	 * @param string $id
	 * @param mixed $data
	 * @param array $options
	 * @access public
	 * @return boolean
	 */
	function cacheData($id, $data, $options = array())
	{
		$co = zcache::_getCacheObj($options);
		// serialize data
		$data = serialize($data);
		return zcache::cache($id, $data, $options);
	}

	/**
	 * cacheString
	 * cache or save the data passed. Data must be a string and will not be serialized before caching.
	 *
	 * @param string $id
	 * @param string $data
	 * @param array $options
	 * @access public
	 * @return boolean
	 */
	function cacheString($id, $data, $options = array())
	{
		$co = zcache::_getCacheObj($options);
		return $co->cacheData($id, $data, $options);
	}

	/**
	 * get
	 * get (retrive) the data from the passed id.
	 * Used by getData and getString.
	 *
	 * @param string $id
	 * @param array $options
	 * @access public
	 * @return mixed $data
	 */
	function get($id, $options = array())
  	{
		$co = zcache::_getCacheObj($options);
		return $co->get($id, $options);
	}

	/**
	 * getData
	 * get (retrive) the data from the passed id.
	 * This function expects to get cache stored by cacheData and will unserialize the file and then pass the mixed data.
	 *
	 * @param string $id
	 * @param array $options
	 * @access public
	 * @return mixed $data
	 */
	function getData($id, $options = array())
  	{
  		$data = zcache::get($id, $options);
		return unserialize($data);
	}

	/**
	 * getString
	 * get (retrive) the data from the passed id.
	 * This function expects to get cache stored by cacheString and will pass the string.
	 *
	 * @param string $id
	 * @param array $options
	 * @access public
	 * @return string $data
	 */
	function getString($id, $options = array())
  	{
		$options = array_merge($options, array('automaticSerialization' => false));
		return zcache::get($id, $options);
	}

	/**
	 * clear
	 * clear (or remove) the cache for the passed id.
	 *
	 * @param string $id
	 * @param array $options
	 * @access public
	 * @return boolean
	 */
  	function clear($id, $options = array())
  	{
	    $co = zcache::_getCacheObj($options);
	    return $co->clear($id, $options);
	}

	/**
	 * clearGroup
	 * clear (or remove) all the cache for the passed group.
	 *
	 * @param string $group
	 * @param string $mode
	 * @access public
	 * @return boolean
	 */
	function clearGroup($group = "default", $options = array(), $mode = "normal") // mode can be normal or recursive
	{
		if ($mode == "normal") // only mode implemented now
		{
			$options = array_merge($options, array('group' => $group)); // probably not necessary
			$co = zcache::_getCacheObj($options);
			return $co->clearGroup($group, $options);
		}
		else
		{
			// return rmrf(app_cache_dir . "$group/");
		}
	}

	/**
	 * clearBase
	 * clear (or remove) all the cache in the passed $base
	 *
	 * @param string $base
	 * @access public
	 * @return boolean
	 */
	function clearBase($base)
	{
	    $co = zcache::_getCacheObj(array());
		return $co->clearBase($base);
	}

	/**
	 * clearAllCache
	 * clear (or remove) all the cache.
	 *
	 * @access public
	 * @return boolean
	 */
	function clearAllCache()
	{
	    $co = zcache::_getCacheObj(array());
		$co->clearAllCache();
	}
}
