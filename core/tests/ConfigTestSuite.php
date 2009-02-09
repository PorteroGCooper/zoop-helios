<?php

class ConfigTestSuite extends ZoopTestSuite {

	var $requiredComponents = array('config');

	function testYamlDriver() {
		$this->assertEqual (Config::get('test.zoop.config.test_value'), 'awesome');
		$test_array = Config::get('test.zoop.config.test_value');
		$this->assertEqual (count($test_array), 3);
		$this->assertEqual ($test_array[0], 'value1');
	}
}