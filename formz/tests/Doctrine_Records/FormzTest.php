<?php

class FormzTest extends Doctrine_Record {

	public function setTableDefinition() {
		$this->hasColumn('short_string', 'string', 1024);
		$this->hasColumn('long_string', 'string', 2147483647, array('minlength' => 1));
		$this->hasColumn('some_int', 'integer', 10);
	}

	public function setUp() {
/*
		$this->actAs('Timestampable');
		$this->hasOne('Language as Lang', array('local' => 'lang_id', 'foreign' => 'id'));
*/
	}
}