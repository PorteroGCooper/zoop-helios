<?php

class FormzTestSuite extends ZoopTestSuite {

	var $requiredComponents = array('formz', 'doctrine', 'db');
	
	function init() {
		// set up test config
		$this->model_dir = ZOOP_DIR . "/formz/tests/Doctrine_Records/";
		$this->db_file = ZOOP_DIR . "/formz/tests/formz.sqlite";
		$this->dsn = 'sqlite:' . $this->db_file;

		// set up the db (dump it and start over)
		if (is_file($this->dsn)) unlink($this->db_file);
		$this->db = new SQLiteDatabase($this->db_file);
		
		// set up doctrine
		Doctrine_Manager::connection($this->dsn);
 		Doctrine::loadModels($this->model_dir);
		Doctrine::createTablesFromModels();
	}
	
	function testDBCreated() {
		// make sure there is a db
		$this->assertTrue(file_exists($this->db_file));
		$this->assertTrue(is_object($this->db));
		
		// make sure it's empty
		$this->assertEqual(count(Doctrine::getTable('FormzTest')->findAll()->toArray()), 0);
	}

	function testFormzComponentLoaded() {
		$this->assertTrue(class_exists('Formz'));
		$this->assertTrue(class_exists('component_doctrine'));
		$this->assertTrue(class_exists('Doctrine'));
		$this->assertTrue(class_exists('FormzTest'));
	}
	
	function testCreateNewDoctrineRecords() {
		$foo = new FormzTest();
		$this->assertTrue(is_a($foo, 'FormzTest'));
		
		$this->assertEqual($foo->toArray(), array(
			'id' => null,
			'short_string' => null,
			'long_string' => null,
			'some_int' => null,
		));
		
		$foo->short_string = 'ham';
		$foo->long_string = 'bacon';
		$foo->some_int = 12321;
		
		$this->assertEqual($foo->toArray(), array(
			'id' => null,
			'short_string' => 'ham',
			'long_string' => 'bacon',
			'some_int' => 12321,
		));
		
		// save it and make sure there's nothing else in there.
		$foo->save();
		$foo_id = $foo->get('id');

		$this->assertEqual(Doctrine::getTable('FormzTest')->find($foo_id)->toArray(), $foo->toArray());
	}

	function testFormatLabel() {
		$this->assertEqual(Formz::format_label('test'), 'Test');
		$this->assertEqual(Formz::format_label('test_label'), 'Test Label');
		$this->assertEqual(Formz::format_label('this-is-a-test'), 'This Is a Test');
		$this->assertEqual(Formz::format_label('this_is_another_test'), 'This Is Another Test');
	}
	
	function testTimestampable() {
		
	}
	
	function __destruct() {
		// if the test db exists destroy it.
		if (is_file($this->db_file)) unlink($this->db_file);
	}

}
