<?php

/**
 * Configuration component
 * 
 * @group config
 * @endgroup
 * 
 * @ingroup components
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class Component_Config extends Component {
	function __construct() {
		$this->requireComponent('spyc');
	}
	
	function checkEnvironment() {
		if (!FileUtils::isWritable(CONFIG_CACHE_DIR)) {
			$this->envError('Unable to write to config cache file. Make sure ' . CONFIG_CACHE_DIR . ' exists and is writable.', false);
			return false;
		}
		return true;
	}
	
	function getIncludes() {
		return array(
			"config" => $this->getBasePath() . "/Config.php"
		);
	}
}