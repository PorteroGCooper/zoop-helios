<?php

//Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/**
 * Main component file for component_simpletest
 *
 * Class for unit testing in Zoop
 * @ingroup zoop
 * @ingroup simpletest
 * @ingroup component_simpletest
 */
class component_simpletest extends component {

	/**
	 * This is overloading the getIncludes function, but we're actually adding them ourselves,
	 * because we want to use addInclude to add a bunch of $name for the same $file at once.
	 * We're adding them in the getIncludes function because we want the includes to be added
	 * at the normal time.
	 * 
	 * @access public
	 * @return void
	 */
	function getIncludes() {
		global $zoop;
		$base = $this->getBasePath();

		$zoop->addInclude(
			array(
				'HtmlReporter',
				'TextReporter',
				'SelectiveReporter',
				'NoSkipsReporter'
			),
			$base . '/simpletest/reporter.php'
		);
		
		$zoop->addInclude(
			array(
				'FieldExpectation',
				'HttpHeaderExpectation',
				'NoHttpHeaderExpectation',
				'TextExpectation',
				'NoTextExpectation',
				'WebTestCase'
			),
			$base . '/simpletest/web_tester.php'
		);
		
		return array(
			'UnitTestCase' => $base . '/simpletest/unit_tester.php'
		);
	}
}
