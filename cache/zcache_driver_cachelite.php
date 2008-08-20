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

require_once('Cache/Lite.php');

class zcache_driver_cachelite extends zcache_driver {

/**
* Reference to the cacheLite object
*
* @var object
* @access public
*/
	var $cacheLite;

	function zcache_driver_cachelite($options = array()) 
	{

		if (isset($options['base']))
			$options['cacheDir'] = join_dirs(array($options['cacheDir'], $options['base']));

		mkdirr($options['cacheDir']);

		$cacheLite = new Cache_Lite($options);

		$this->cacheLite =& $cacheLite;
		$this->group = $options['group'];
	}


  	function cache($id, $data, $options = array()) 
	{
		if (isset($options['lifeTime']))
			$this->cacheLite->setLifeTime($options['lifeTime']);
			
//			echo "group: ".  $this->_getGroup($options);
		return $this->cacheLite->save($data, $id, $this->_getGroup($options));
	}
	
	
	function get($id, $options = array()) 
	{
		return $this->cacheLite->get($id, $this->_getGroup($options));
	}
	
  	function clear($id, $options = array()) 
	{
	    return $this->cacheLite->remove($id, $this->_getGroup($options));
	}

	
	function clearGroup($group = "default", $options = array(), $mode = "normal") 
	{
		$this->cacheLite->setOption('group', $group);	
		return $this->cacheLite->clean($group);
	}

	
	function clearBase($base) 
	{
		return rmrf(join_dirs(array(app_cache_dir, "$base")));
	}

	
	function clearAllCache() 
	{
		rmrf(app_cache_dir . "/");
		mkdirr(app_cache_dir);
	}
}
