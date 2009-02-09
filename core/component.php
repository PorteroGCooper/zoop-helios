<?php

/**
 * The Zoop core component.
 * 
 * This component includes a Config library and a set of Zoop core utils, required
 * by the Zoop framework to run.
 * 
 * @extends Component
 */
class Component_Core extends Component {
	function __construct() {
		// YAML parsing library is required for Config library.
		$this->requireComponent('spyc');
		
		$base = $this->getBasePath();
		//include($base . "/error.php");
		//include($base . "/utils.php");
	}
	
	function getIncludes() {
		$base = $this->getBasePath();
		
		return array(
			"Config"    => $base . '/Config.php',
			"FileUtils" => $base . '/FileUtils.php',
		);
	}
	
	function checkEnvironment() {
		if (!FileUtils::isWritable(CONFIG_CACHE_DIR)) {
			$this->envError('Unable to write to config cache file. Make sure ' . CONFIG_CACHE_DIR . ' exists and is writable.', false);
			return false;
		}
		return true;
	}
}