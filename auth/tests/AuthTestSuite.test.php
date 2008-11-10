<?php

include_once(dirname(__FILE__) . "/sub_auth.php"); 

class AuthTestSuite extends ZoopTestSuite {

	var $requiredComponents = array('auth', 'doctrine');
	//var $requiredComponents = array('auth', 'db');

	function overloadConfig() {
		Config::set('zoop.db.dsn', 'sqlite:'. dirname(__FILE__) . "/test.db");
		Config::set('zoop.db.use_pdo', 1 );
		Config::set('zoop.doctrine.dsn', 'sqlite:'. dirname(__FILE__) . "/test.db");
		Config::set('zoop.doctrine.models_dir', dirname(__FILE__) . "/doctrineModels");
	}

	function testDriver() {
		$this->assertTrue  (auth::gi()->testDriver()); 
	}

	//function testYamlBackend() {
		//$a = sub_auth::gi();
		//$a->_setConfigBase("test.zoop.auth.yaml");
		//$this->assertEqual (Config::get('test.zoop.auth.yaml.backend'), 'yaml');
		//$this->assertEqual ($a->getConfig('backend'), 'yaml');
		//$this->assertTrue  ($a->testDriver()); 
		//$a->populateActiveUser('bob');
		//$user = $a->getActiveUser();
		//$this->assertTrue ( $user['user']['password'] == $a->getDriver()->_preparePassword('test'));
		//$this->backendTest ($a);
	//}

	function testDoctrineBackend() {
		global $zoop;
		$zoop->component('doctrine')->run();
		//Doctrine::generateModelsFromDb(dirname(__FILE__) . "/doctrineModels");

		$a = sub_auth::gi();
		$a->_setConfigBase("test.zoop.auth.doctrine");

		$this->assertEqual (Config::get('test.zoop.auth.doctrine.backend'), 'doctrine');
		$this->assertEqual ($a->getConfig('backend'), 'doctrine');
		$a->_loadDriver();
		$this->assertTrue  ($a->testDriver()); 
		$a->populateActiveUser(1);
		$user = $a->getActiveUser();
		$this->assertTrue ( $user['password'] == $a->getDriver()->_preparePassword('test'));
		$this->backendTest ($a);
	}

	//function testDBBackend() {
		//$a = sub_auth::gi();
		//$a->_setConfigBase("test.zoop.auth.db");
		//$this->assertEqual (Config::get('test.zoop.auth.db.backend'), 'db');
		//$this->assertEqual ($a->getConfig('backend'), 'db');
		//$a->_loadDriver();
		//$this->assertTrue  ($a->testDriver()); 
		//$a->populateActiveUser(1);
		//$user = $a->getActiveUser();
		//$this->assertTrue ( $user['user'][1]['password'] == $a->getDriver()->_preparePassword('test'));
		//$this->backendTest ($a);
	//}
	
	function backendTest($a) {
		$this->assertTrue ( $a->_checkPassword('bob','test')) ;
		$this->assertFalse ( $a->_checkPassword('bobby','test')) ;
		$this->assertFalse ( $a->_checkPassword('bob','test$')) ;
		$this->assertTrue ( $a->_checkPassword('steve','testing')) ;
		$this->assertFalse ( $a->_checkPassword('ryan','tester')) ;
		$this->assertTrue ( $a->checkGroup('one') );
		$this->assertFalse ( $a->checkGroup('1') );
		$this->assertTrue ( $a->checkGroup('two') );
		$this->assertFalse ( $a->checkGroup('four') );
		$this->assertFalse ( $a->checkGroup('doesntexist') );
		$this->assertTrue ( $a->checkRole('police') );
		$this->assertFalse ( $a->checkRole('1') );
		$this->assertFalse ( $a->checkRole('root') );
		$this->assertFalse ( $a->checkRole('doesntexist') );
	}
}
