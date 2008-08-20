<?
////////////////////////////////////////////////////
//  Define Cache constants here				  //
////////////////////////////////////////////////////

	define("zone_cache", true);		// instantiate zone caching

	define("default_cache_lifeTime", 3600);	// in seconds, NULL = forever
	
	define("cache_style", "secure");	// can be "secure" or "performance"
	// performance avoids checks and security features to gain some speed.
	// only use 'performance' if you know what you are doing.

	define("cache_driver", "cachelite"); // can be "cachelite" or "memcache"

	$memcache_servers = array();
	if (defined(app_status)&&app_status=="live") {
		$memcache_servers[] = "web2";
	} else {
		$memcache_servers[] = "localhost";
	}
?>
