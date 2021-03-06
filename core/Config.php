<?php

/**
 * Configuration class
 * 
 * Provides methods for retrieving configuration options from a YAML config file.
 */
class Config {

	static $info = array();
	private static $file;
	private static $configCacheFile;
	private static $fileCache;
	private static $constCache = array();
	
	/**
	 * Helper function to load the config cache file.
	 * 
	 * @access private
	 * @see Config::loadFile
	 * @return void
	 */
	private static function loadFileCache() {
		if (defined('CONFIG_CACHE_DIR')) {
			self::$configCacheFile = CONFIG_CACHE_DIR . '/config.php';
			if (file_exists(self::$configCacheFile)) {
				 include(self::$configCacheFile);
			}
		} 
	}
	
	/**
	 * Get a config array from a file.
	 *
	 * Load and cache a yaml config file. Config options will be cached with constants replaced. Config
	 * keeps a list of constant values, and invalidates the cache if it contains 'dirty' data: i.e. changed
	 * constants in the app or Zoop will force a recache of the config data.
	 * 
	 * @access private
	 * @param mixed $name
	 * @return array
	 */
	private static function loadFile($name) {
		if (!file_exists($name)) return array();
		if (empty(self::$fileCache)) self::loadFileCache();
		
		$last_modified = filemtime($name);
		if (!isset(self::$fileCache[$name]) || $last_modified > self::$fileCache[$name]['modified']) {
			$info = Yaml::read($name);
			
			// Store replaced constants in config cache.
			$info = self::_replaceConstantsInArray($info);
			
			self::$fileCache[$name] = array('modified' => $last_modified, 'info' => $info);
			
			if (!FileUtils::isWritable(self::$configCacheFile)) {
				Component::envError('Unable to write to config cache file. Make sure ' . CONFIG_CACHE_DIR . ' exists and is writable.');
			} else {
				$contents = "<?php \n\n";
				$contents .= 'self::$fileCache = ' . var_export(self::$fileCache, true) . ";\n\n";
				// add the 'dirty cache' logic.
				if (!empty(self::$constCache)) {
					$contents .= 'if (' . implode("\n\t|| ", self::$constCache) . ') { self::$fileCache = array(); }';
				}
				file_put_contents(self::$configCacheFile, $contents);
			}
		}
		
		return self::$fileCache[$name]['info'];
	}

	/**
	 * suggest a value if one isn't already set
	 * takes a yaml file and places the values into $self::$info
	 * 
	 * @param mixed $file a yaml config file
	 * @param mixed $prefix  prefix to insert yaml file into (eg.. zoop.db)
	 * @static
	 * @access public
	 * @return void
	 */
	public static function suggest($file, $prefix = NULL) {
		if ($prefix) {
			$root = &self::getReference($prefix);
		} else {
			$root = &self::$info;
		}
		
		$config = self::loadFile($file);
		$root = self::merge($config, $root);
	}
	
	/**
	 * insist on a value, even if one is set
	 * takes a yaml file and places the values into $self::$info
	 * 
	 * @param mixed $file a yaml config file
	 * @param mixed $prefix  prefix to insert yaml file into (eg.. zoop.db)
	 * @static
	 * @access public
	 * @return void
	 */
	public static function insist($file, $prefix = NULL) {
		$root = $prefix ? self::getReference($prefix) : self::$info;
		
		$config = self::loadFile($file);
		self::$info = self::merge($root, $config);
	}
		
	/**
	 * Specify configuration file to use
	 *
	 * @param string $file Path and filename of the config file to use
	 * @static
	 * @access public
	 * @return void
	 */
	public static function setConfigFile($file) {
		self::$file = $file;
	}
	
	/**
	 * Loads the config file specified by the $file member variable (or CONFIG_FILE) 
	 *
	 * @static
	 * @access public
	 * @return void
	 */
	public static function load() {
		if(!self::$file) {
			self::setConfigFile(CONFIG_FILE);
		}
		
		self::insist(self::$file);

		
		if(defined('instance_config') && instance_config)
			self::insist(instance_config);

		global $zoop;
		$zoop->config = &self::$info;
	}

	/**
	 * Take a string and substitute %CONST% with a defined CONSTANT in keys and values
	 * 
	 * @param mixed $inString 
	 * @static
	 * @access private
	 * @return string 
	 */
	private static function _replaceConstantsInString($inString) {
		if (strpos($inString, '%') !== false) {
			preg_match_all("/\%([A-Z_-]+)\%/", $inString, $matches);
			if ($matches[1]) {
				foreach($matches[1] as $const) {
					if (defined($const)) {
						$inString = str_replace("%$const%", constant($const), $inString);
						
						// cache all the replaced constants, so we can watch for a dirty config cache.
						if (!isset(self::$constCache['def:'.$const])) {
							self::$constCache['def:'.$const] = '!defined("' . $const . '")';
							self::$constCache[$const] = 'constant("' . $const . '") != "' . constant($const) . '"';
						}
					}
				}
			}
		}
		return $inString;
	}

	/**
	 * Take an array and substitute %CONST% with a defined CONSTANT in keys and values
	 * 
	 * @param mixed $inArray 
	 * @static
	 * @access private
	 * @return array
	 */
	private static function _replaceConstantsInArray($inArray) {
		$newArray = array();
		foreach($inArray as $key => $value) {
			$key = self::_replaceConstantsInString($key);
			if (is_array($value)) {
				$value = self::_replaceConstantsInArray($value);
			} else {
				$value = self::_replaceConstantsInString($value);
			}

			$newArray[$key] = $value;
		}

		return $newArray;
	}

	/**
	 * Returns configuration options based on a path (i.e. zoop.db or zoop.application.info)
	 *
	 * @param string $path Path for which to fetch options
	 * @param string $default A value to return if the $path is unset
	 * @return array of configuration values
	 */
	public static function get($path, $default = null) {
		$parts = explode('.', $path);
		$cur = self::$info;
		
		foreach($parts as $thisPart)
			if(isset($cur[$thisPart]))
				$cur = $cur[$thisPart];
			else
				return $default;
		
		return $cur;
	}	
	
	/**
	 * &getReference 
	 * Operates like get, but instead passes a reference 
	 * to the variable, rather than a copy
	 * 
	 * @param string $path Path for which to fetch options
	 * @static
	 * @access public
	 * @return void
	 */
	public static function &getReference($path) {
		$parts = explode('.', $path);
		$cur = &self::$info;
		
		foreach($parts as $thisPart)
		{
			if(isset($cur[$thisPart]))
				$cur = &$cur[$thisPart];
			else
			{
				$cur[$thisPart] = array();
				$cur = &$cur[$thisPart];
			}
		}
		
		return $cur;
	}

   /**
    * Sets a config parameter.
    *
    * If a config parameter with the name already exists the value will be overridden.
    *
	* @param string $path Path for which to set options
    * @param mixed  $value A config parameter value
    */
	public static function set($path, $value) {
		$array = self::_path2array($path, $value);
		self::better_array_merge_recursive(self::$info, $array);
	}

	/**
	 * The php array_merge_recursive doesn't quite work the way we need it to. 
	 * This one enables the set method to work properly
	 * 
	 * @param mixed $cur 
	 * @param mixed $array 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function better_array_merge_recursive(&$cur, $array) {
		if (!is_array($array)) {
			return $cur = $array;
		}

		foreach ($array as $key => $value ) {
			if (isset($cur[$key])) {
				self::better_array_merge_recursive($cur[$key], $value);
			} else {
				$cur[$key] = $value;
			}
		}
	}

	/**
	 * _path2array 
	 * take a path string and create an array with that structure to set value to
	 * 
	 * @static
	 * @access private
	 * @param string $path Path is a string with "." as the delimiter
	 * @param mixed $value 
	 * @return array
	 */
	private static function _path2array($path, $value) {
		$parts = explode('.', $path);
		$array = array();
		$cur =& $array;
		$count = 1;
		$total = count($parts) ;
		foreach ($parts as $part) {
			if ($count < $total) {
				$cur[$part] = array();
			} else {
				$cur[$part] = $value;
			}

			$cur =& $cur[$part];
			$count++;
		}

		return $array;
	}

	/**
	 * Inserts an array into the config.
	 * Sets an array of config parameters.
	 *
	 * If an existing config parameter name matches any of the keys in the supplied
	 * array, the associated value will be overridden.
	 *
	 * @static
	 * @access public
	 * @param array $values An structured array to insert into the config
	 */
	public static function insert($values = array(), $path = false) {
		$array = self::_path2array($path, $values);
		self::$info = array_merge_recursive(self::$info, $array);
	}

	/**
	 * Returns all configuration parameters.
	 *
	 * @static
	 * @access public
	 * @return array An associative array of configuration parameters.
	 */
	public static function getAll() {
		return self::$info;
	}

	/**
	 * Returns a reference to all configuration parameters. 
	 * 
	 * @static
	 * @access public
	 * @return array An associative array of configuration parameters.
	 * @return reference 
	 */
	public static function &getAllReference() {
		return self::$info;
	}
	
	
	/**
	 * Recursively merge arrays, ensure identical keys are replaced, not converted to arrays.
	 *
	 * @author Paha <paha at paha dot hu>
	 * @link http://us.php.net/manual/en/function.array-merge-recursive.php#73843
	 */
	public static function merge($array0, $array1) {
		$arrays = func_get_args();
		$remains = $arrays;
	
		// We walk through each arrays and put value in the results (without
		// considering previous value).
		$result = array();
	
		// loop available array
		foreach($arrays as $array) {
	
			// The first remaining array is $array. We are processing it. So
			// we remove it from remaing arrays.
			array_shift($remains);
	
			// We don't care non array param, like array_merge since PHP 5.0.
			if(is_array($array)) {
				// Loop values
				foreach($array as $key => $value) {
					if(is_array($value)) {
						// we gather all remaining arrays that have such key available
						$args = array();
						foreach($remains as $remain) {
							if(array_key_exists($key, $remain)) {
								array_push($args, $remain[$key]);
							}
						}
	
						if(count($args) > 2) {
							// put the recursion
							$result[$key] = call_user_func_array(__FUNCTION__, $args);
						} else {
							foreach($value as $vkey => $vval) {
								$result[$key][$vkey] = $vval;
							}
						}
					} else {
						// simply put the value
						$result[$key] = $value;
					}
				}
			}
		}
		return $result;
	}
}








