<?php
/**
 * component_spyc
 *
 * @uses component
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class component_spyc extends component
{
	function getIncludes()
	{
		return array(
			"spyc" =>  $this->getBasePath() . "/spyc.php5",
		);
	}
}
