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
* zcache functions are intended to be called statically or instantiated.
* We currently use the pear library, cacheLite to handle our dirty work.
*
* @author Steve Francia <sfrancia@supernerd.com>
* @version 1.0
* @since 1.2
* @package cache
* @access public
* @copyright Supernerd LLC and Contributors
*
*/

class zcache
{
	var $cacheLite;

	function &zcache($options = array())
	{
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
				'lifeTime' => $default_lifeTime,
				//'hashedDirectoryLevel' => 1, // in large directories helps to have subdirectories for indexing 
				'automaticCleaningFactor' => 200 // clean stale files 1x out of 200 writes
			);
		}
		
		$cacheoptions = array_merge($DefaultOptions, $options);
		
		if (isset($options['base']))
			$cacheoptions['cacheDir'] .= $options['base'];
					
		mkdirr($cacheoptions['cacheDir']);
			
		$cacheLite = new Cache_Lite($cacheoptions);	  
		
	  	if (isset($this) && is_object($this)  && "zcache" == strtolower(get_class($this)))
	  	{
		 	$this->cacheLite =& $cacheLite;
			return $this->cacheLite;
		}
		else
		{	  
			return $cacheLite;
		}
	}  
	
	function &_getCacheObj($array = array())
	{
		if (isset($this) && is_object($this) && "zcache" == strtolower(get_class($this)))
		{
			return $this->cacheLite;
		}
		else
		{
			$tmp = zcache::zcache($array);
			return $tmp;
		}
	}	
  
  	function cache($id, $data, $options = array())
  	{
		$co = zcache::_getCacheObj($options);
		
		if (isset($options['lifeTime']))
			$co->setLifeTime($options['lifeTime']);
		
		return $co->save($data, $id);
	}
	
	function cacheData($id, $data, $options = array())
	{		
		$options = array_merge($options, array('automaticSerialization' => true));
		return zcache::cache($id, $data, $options);
	}
	
	function cacheString($id, $data, $options = array())
	{
		$options = array_merge($options, array('automaticSerialization' => false));
		return zcache::cache($id, $data, $options);
	}
	
	function get($id, $options = array())
  	{
		$co = zcache::_getCacheObj($options);
		return $co->get($id);
	}
	
	function getData($id, $options = array())
  	{
  		$options = array_merge($options, array('automaticSerialization' => true));
  		return zcache::get($id, $options);
	}
	
	function getString($id, $options = array())
  	{
		$options = array_merge($options, array('automaticSerialization' => false));
		return zcache::get($id, $options);
	}
  
  	function clear($id, $options = array())
  	{
	    $co = zcache::_getCacheObj($options);
	    return $co->remove($id);
	}
	
	function clearGroup($group = "default", $mode = "normal") // mode can be normal or recursive
	{
		if ($mode == "normal") // only mode implemented now
		{
			$options = array_merge($options, array('group' => $group));
			$co = zcache::_getCacheObj($options);
			return $co->clean();	
		}
		else
		{
			// return rmrf(app_cache_dir . "$group/");
		}
	}
	
	function clearBase($base)
	{
		return rmrf(app_cache_dir . "$base");
	}
	
	function clearAllCache()
	{
		rmrf(app_cache_dir . "/");
		mkdirr(app_cache_dir);
	}
}
?>