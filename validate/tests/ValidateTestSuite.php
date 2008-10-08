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
		// all the regular ones
		$this->assertTrue  (Validator::boolvalidate('555.555.5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('(555)555-5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('(555) 555-5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('555-555-5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('555 555 5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('5555555555', array('type' => 'phone'))) ;
		
		// with a 1 in front
		$this->assertTrue  (Validator::boolvalidate('1.555.555.5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('1(555)555-5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('1 (555) 555-5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('1-555-555-5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('1 555 555 5555', array('type' => 'phone'))) ;
		$this->assertTrue  (Validator::boolvalidate('15555555555', array('type' => 'phone'))) ;
		
		// almost right, but should be false
		$this->assertFalse  (Validator::boolvalidate('2.555.555.5555', array('type' => 'phone'))) ;
		$this->assertFalse  (Validator::boolvalidate('3(555)555-5555', array('type' => 'phone'))) ;
		$this->assertFalse  (Validator::boolvalidate('4 (555) 555-5555', array('type' => 'phone'))) ;
		$this->assertFalse  (Validator::boolvalidate('5-555-555-5555', array('type' => 'phone'))) ;
		$this->assertFalse  (Validator::boolvalidate('6 555 555 5555', array('type' => 'phone'))) ;
		$this->assertFalse  (Validator::boolvalidate('75555555555', array('type' => 'phone'))) ;

		// should be false as well
		$this->assertFalse (Validator::boolvalidate('funny dog', array('type' => 'phone'))) ;
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
		// simple cases
		$this->assertTrue	(Validator::boolvalidate('test@example.com', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test@mail.example.com', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test@example.org', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test@example.info', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test@example.co.uk', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test@lots.of.subdomains.example.com', array('type' => 'email'))) ;
		
		// email addresses with dots
		$this->assertTrue	(Validator::boolvalidate('test.two@example.com', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test.two@mail.example.com', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test.two@example.org', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test.two@example.info', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test.two@example.co.uk', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test.two@lots.of.subdomains.example.com', array('type' => 'email'))) ;
		
		// email addresses with pluses
		$this->assertTrue	(Validator::boolvalidate('test.two+three@example.com', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test.two+three@mail.example.com', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test.two+three@example.org', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test.two+three@example.info', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test.two+three@example.co.uk', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test.two+three@lots.of.subdomains.example.com', array('type' => 'email'))) ;

		// common characters (should all be allowed)
		$this->assertTrue	(Validator::boolvalidate('test_one@example.com', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test-two@example.com', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test+three@example.com', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test.four@example.com', array('type' => 'email'))) ;
		$this->assertTrue	(Validator::boolvalidate('test~five@example.com', array('type' => 'email'))) ;

		// and a beastly combination of all of the above
		$this->assertTrue	(Validator::boolvalidate('test-hyphen_underscore.dot+plus@example.com', array('type' => 'email'))) ;

/*		
		// ugly uncommon but valid characters
		$this->assertTrue	(Validator::boolvalidate("!#$%&'*+-/=?^_`{|}~@example.com", array('type' => 'email'))) ;
		// escaped literal ip address
		$this->assertTrue	(Validator::boolvalidate('test@[127.0.0.1]', array('type' => 'email'))) ;		
*/

		// not actually email addresses

		// invalid domain
		$this->assertFalse	(Validator::boolvalidate('test@example', array('type' => 'email'))) ;
		$this->assertFalse	(Validator::boolvalidate('test@exa mple.com', array('type' => 'email'))) ;

		// invalid characters
//		$this->assertFalse	(Validator::boolvalidate('test one@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test,two@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test>three@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test<four@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test"five@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test@six@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test()seven@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test[]eight@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test;nine@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test:ten@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test\eleven@example.com', array('type' => 'email'))) ;
		
		// dots in the wrong place
//		$this->assertFalse	(Validator::boolvalidate('.test@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test.@example.com', array('type' => 'email'))) ;
//		$this->assertFalse	(Validator::boolvalidate('test..fails@example.com', array('type' => 'email'))) ;

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
