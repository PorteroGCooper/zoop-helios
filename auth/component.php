<?php

// Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/** 
 * component_auth
 * Currently this component is database driven and dependant on sessions. It is configurable, but will use the default database connection
 *
 * @ingroup zoop
 * @ingroup auth
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class Component_Auth extends Component {

	public function __construct() {
		$this->requireComponent('db');
		$this->requireComponent('session');
		$this->requireComponent('app');
		$this->requireComponent('spyc');
	}
	
	public function getIncludes() {
		return array(
			'auth' => $this->getBasePath() . '/auth.php'
		);
	}
}