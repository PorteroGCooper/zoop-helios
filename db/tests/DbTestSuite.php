<?php

class DbTestSuite extends ZoopTestSuite {

//	var $requiredComponents = array();
	
	function testMakeDSN() {
		// function makeDSNFromString($dsn_string)
		$this->assertEqual(database::makeDSNFromString('mysql://user:pass@localhost/path'), array(
			'phptype' => 'mysql',
			'username' => 'user',
			'password' => 'pass',
			'hostspec' => 'localhost',
			'port' => '3306',
			'database' => 'path',
		));
		$this->assertEqual(database::makeDSNFromString('sqlite:/tmp/test.sqlite'), array(
			'phptype' => 'sqlite',
			'username' => null,
			'password' => null,
			'hostspec' => null,
			'port' => null,
			'database' => '/tmp/test.sqlite',
		));
		$this->assertEqual(database::makeDSNFromString('pgsql://me:sekrit@subdomain.example.com:1234/db_name'), array(
			'phptype' => 'pgsql',
			'username' => 'me',
			'password' => 'sekrit',
			'hostspec' => 'subdomain.example.com',
			'port' => '1234',
			'database' => 'db_name',
		));
		
		// function makeDSN($dbtype, $host, $port, $username, $password, $database)
		$this->assertEqual(database::makeDSN('mysql', 'localhost', 3306, 'user', 'pass', 'path'), array(
			'phptype' => 'mysql',
			'username' => 'user',
			'password' => 'pass',
			'hostspec' => 'localhost',
			'port' => '3306',
			'database' => 'path',
		));
		$this->assertEqual(database::makeDSN('pgsql', 'subdomain.example.com', 1234, 'me', 'sekrit', 'db_name'), array(
			'phptype' => 'pgsql',
			'username' => 'me',
			'password' => 'sekrit',
			'hostspec' => 'subdomain.example.com',
			'port' => '1234',
			'database' => 'db_name',
		));

	}
	
}
