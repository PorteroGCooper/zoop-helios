<?php
/**
 * Configuration class
 * 
 * Provides methods for retrieving configuration options from a YAML config file.
 *
 */
class Config
{
	private static $info = array();
	private static $file;
	
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
		if($prefix)
			$root = &self::getReference($prefix);
		else
			$root = &self::$info;
		$root = array_merge(self::_replaceConstantsInArray(Yaml::read($file)), $root);
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
		self::$info = array_merge($root, self::_replaceConstantsInArray(Yaml::read($file)));
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
	 * Loads the config file specified by the $file member variable (or APP_DIR/config.yaml) 
	 *
	 * @static
	 * @access public
	 * @return void
	 */
	public static function load() {
		if(!self::$file) {
			self::setConfigFile(APP_DIR . '/config.yaml');
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
		if ( strstr($inString, '%') ) {
			preg_match_all("/\%([A-Z_-]+)\%/", $inString, $matches);
			if ($matches[1]) {
				foreach($matches[1] as $const) {
					if ( defined($const) ) {
						$inString = str_replace("%$const%", constant($const), $inString);
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
		self::$info = array_merge_recursive(self::$info, $array);
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
		$total = count($parts);
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
}
