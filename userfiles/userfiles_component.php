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
 * @ingroup storage
 */
class component_userfiles extends component {
	function component_userfiles() {
		$this->requireComponent('db');
	}
	
	function getIncludes() {
		$base = $this->getBasePath();
		
		include_once("VFS.php");
		include_once("VFS/sql.php");
		
		return array(
//			"VFS" => "VFS.php",
//			"VFS_sql" => "VFS/sql.php",
			'userfiledb' => $base . "/userfiledb.php"
		);
	}
}
