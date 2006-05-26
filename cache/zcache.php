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
* An easy to use, effective and secure caching system. 
* zcache functions are intended to be called statically,  but can also be used as an object 
* (you just need to know what parameters to pass).
* We use the pear library, cacheLite to handle our dirty work, but we don't use groups in the same way as they do.
* Our groups are subdirectories where cacheLite places all files in the same directory and appends different keys.
* Our method provides one benefit, less files in a directory for faster indexing.
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
		$DefaultOptions = array(
			'readControl' => false,
			'automaticSerialization' => true,
			'cacheDir' => app_cache_dir,
			'lifeTime' => NULL
		);
		
		$cacheoptions = array_merge($DefaultOptions, $options);
		
		$cacheLite = new Cache_Lite($cacheoptions);	  
	  
	  	if (isset($this) && is_object($this))
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
	  if (isset($this) && is_object($this))
	  {
	  	return $this->cacheLite;
	  }
	  else
	  {
	  		$tmp = zcache::zcache($array);
	  		return $tmp;
	  }
	}	
  
  	function cache($id, $data, $group = "default", $duration = 3600) // 1 hour
  	{
		$co = zcache::_getCacheObj(array('cacheDir' => app_cache_dir . "$group/", 'lifeTime' => $duration));
		return $co->save($data, $id);
	}
	
	function get($id, $group = "default")
  	{
		$co = zcache::_getCacheObj(array('cacheDir' => app_cache_dir . "$group/"));
		return $co->get($id);
	}
  
  	function remove($id, $group = "default")
  	{
	    $co = zcache::_getCacheObj(array('cacheDir' => app_cache_dir . "$group/"));
	    return $co->remove($id);
	}
	
	function clean($group = "default")
	{
		$co = zcache::_getCacheObj(array('cacheDir' => app_cache_dir . "$group/"));
		return $co->clean();
	}	
	
	function cleanAllCache()
	{
		
	}
}
?>