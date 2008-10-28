<?php
include_once( dirname(__FILE__) . "/../auth.php");

class sub_auth extends auth {

	private static $instance;

	/**
	 * get Instance: a singleton method 
	 * 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function gi() {
		if (!self::$instance instanceof self) { 
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Path to the location in the config to find the configuration for (this instance of) auth. 
	 * 
	 * @var string
	 * @access public
	 */
	var $configBase = "zoop.auth";
}
