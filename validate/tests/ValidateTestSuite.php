<?php

class ValidateTestSuite extends ZoopTestSuite {

	var $requiredComponents = array('validate');

	function testAlphaNumeric() {
		$this->assertTrue  (Validator::boolvalidate('funny dog', array('type' => 'alphanumeric'))); 
		$this->assertFalse (Validator::boolvalidate('funny.dog', array('type' => 'alphanumeric'))); 
		$this->assertFalse (Validator::boolvalidate('funny()dog', array('type' => 'alphanumeric'))); 
		$this->assertFalse (Validator::boolvalidate('funny#dog', array('type' => 'alphanumeric'))); 
		$this->assertFalse (Validator::boolvalidate('funny13.42dog', array('type' => 'alphanumeric'))); 
		$this->assertFalse (Validator::boolvalidate('funny? dog', array('type' => 'alphanumeric'))); 
	}

	function testNumeric() {
		$this->assertFalse (Validator::boolvalidate('funny dog', array('type' => 'numeric'))) ;
		$this->assertTrue  (Validator::boolvalidate('1924', array('type' => 'numeric'))) ;
		$this->assertTrue  (Validator::boolvalidate('1924.13', array('type' => 'numeric'))); 
		$this->assertTrue  (Validator::boolvalidate('-1924', array('type' => 'numeric'))); 
		$this->assertFalse (Validator::boolvalidate('1924-13', array('type' => 'numeric'))); 
		$this->assertFalse (Validator::boolvalidate('19is a great number', array('type' => 'numeric'))); 
		$this->assertFalse (Validator::boolvalidate('three', array('type' => 'numeric'))); 
	}

	function testPhone() {
		$this->assertFalse (Validator::boolvalidate('funny dog', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('555.555.5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('555-555-5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('555 555 5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('5555555555', array('type' => 'phone'))) ;
		$this->assertFalse (Validator::boolvalidate('1.555.555.5555', array('type' => 'phone'))) ;
		$this->assertFalse (Validator::boolvalidate('1015555555555', array('type' => 'phone'))) ;
		$this->assertFalse (Validator::boolvalidate('10155555555555555555', array('type' => 'phone'))) ;
		$this->assertFalse (Validator::boolvalidate('10.555.555.5555', array('type' => 'phone'))) ;
	}

	function testLength() {
		$this->assertTrue  (Validator::boolvalidate('this is right', array('min' => 12, 'max' => 14, 'type' => 'length')));
		$this->assertFalse (Validator::boolvalidate('this is wrong', array('min' => 14, 'max' => 16, 'type' => 'length')));
		$this->assertFalse (Validator::boolvalidate('this is wrong', array('min' => 10, 'max' => 11, 'type' => 'length')));
		$this->assertTrue  (Validator::boolvalidate('this is right', array('min' => 13, 'max' => 14, 'type' => 'length')));
		$this->assertTrue  (Validator::boolvalidate('this is right', array('min' => 13, 'max' => 13, 'type' => 'length')));
	}

	function testCount() {
		$this->assertTrue  (Validator::boolvalidate(array(1,2,3), array('min' => 2, 'max' => 4, 'type' => 'quantity')));
		$this->assertFalse (Validator::boolvalidate(array(1,2,3), array('min' => 4, 'max' => 6, 'type' => 'quantity')));
		$this->assertFalse (Validator::boolvalidate(array(1,2,3), array('min' => 0, 'max' => 1, 'type' => 'quantity')));
		$this->assertTrue  (Validator::boolvalidate(array(1,2,3), array('min' => 3, 'max' => 4, 'type' => 'quantity')));
		$this->assertTrue  (Validator::boolvalidate(array(1,2,3), array('min' => 3, 'max' => 3, 'type' => 'quantity')));
	}

	function testCreditCard() {

	}

	function testEqualTo () {
	
	}

	function testInt () {
		$this->assertFalse (Validator::boolvalidate('funny dog', array('type' => 'int'))) ;
		$this->assertTrue  (Validator::boolvalidate('1924', array('type' => 'int'))) ;
		$this->assertTrue  (Validator::boolvalidate(1924, array('type' => 'int'))) ;
		$this->assertTrue  (Validator::boolvalidate(1924-13, array('type' => 'int'))) ;
		$this->assertFalse (Validator::boolvalidate('1924.13', array('type' => 'int'))); 
		$this->assertTrue  (Validator::boolvalidate('-1924', array('type' => 'int'))); 
		$this->assertFalse (Validator::boolvalidate('1924-13', array('type' => 'int'))); 
		$this->assertFalse (Validator::boolvalidate('19is a great number', array('type' => 'int'))); 
		$this->assertFalse (Validator::boolvalidate('three', array('type' => 'int'))); 
	}

	function testFloat () {
		$this->assertFalse (Validator::boolvalidate('funny dog', array('type' => 'float'))) ;
		$this->assertTrue  (Validator::boolvalidate('1924', array('type' => 'float'))) ;
		$this->assertTrue  (Validator::boolvalidate(1924, array('type' => 'float'))) ;
		$this->assertTrue  (Validator::boolvalidate(1924-13, array('type' => 'float'))) ;
		$this->assertTrue  (Validator::boolvalidate('1924.13', array('type' => 'float'))); 
		$this->assertTrue  (Validator::boolvalidate('-1924', array('type' => 'float'))); 
		$this->assertFalse (Validator::boolvalidate('1924-13', array('type' => 'float'))); 
		$this->assertFalse (Validator::boolvalidate('19is a great number', array('type' => 'float'))); 
		$this->assertFalse (Validator::boolvalidate('three', array('type' => 'float'))); 
	}

	function testMoney () {
	
	}

	function testZip () {
	
	}

	function testEmail () {
	
	}

	function testDomain () {
	
	}

	function testUrl () {
	
	}

	function testIp () {
	
	}

	function testPassword () {
	
	}

	function testSSN () {
	
	}

	function testDate () {
	
	}

	function testDBUnique () {
	
	}

}
