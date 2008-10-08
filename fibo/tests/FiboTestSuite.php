<?php

class FiboTestSuite extends ZoopTestSuite {

	function testComponentFiboLoads() {
		global $zoop;

		$rv = true;
		if(!$zoop->addComponent('fibo')) {
			$this->msg("apparent error loading component fibo");
			$rv = false;
		}
		if(!class_exists('fibo')) {
			$this->msg("class fibo not defined");
			$rv = false;
		}
		return $rv;
	}

	function testFiboCreation() {
		$rv = true;
		$fibo = new fibo(10);
		if(get_class($fibo) != 'fibo') {
			$this->msg("fibo ojbect not created");
			$rv = false;
		}
		return $rv;
	}

	function testFiboSequence() {
		$rv = true;
		$fibo = new fibo(10,1);
		$ad = array_diff($fibo->sequence(),array(1,1,2,3,5,8,13,21,34));
		if(count($ad) > 0) {
			$this->msg("difference between expected sequence and given sequence: [".join(',',$ad)."]");
			$rv = false;
		}
		return $rv;
	}
}

?>
