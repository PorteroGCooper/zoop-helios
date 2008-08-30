<?php
/**
 * component_config
 *
 * @uses component
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_config extends component
{
	function component_config()
	{
		$this->requireComponent('spyc');
	}

	function getIncludes()
	{
		return array(
				"config_yaml" =>  $this->getBasePath() . "/Yaml.php",
				"config_config" =>  $this->getBasePath() . "/Config.php"
		);
	}
}
