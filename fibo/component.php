<?php
/**
 * component_db 
 * 
 * @uses component
 * @package 
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */

class component_fibo extends component
{
	function getIncludes() {
		return array( 'fibo' => $this->getBasePath() . '/fibo.php');
	}
}
?>
