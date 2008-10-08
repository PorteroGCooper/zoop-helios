<?php

/**
 * ZoopTestSet 
 * 
 * @package 
 * @version $id$
 * @copyright 2008 Portero Inc.
 * @author Weston Cann
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class ZoopTestSet {

	var $setName = null;
	var $dsn = null;
	var $msgs = array();
	var $logger;

	/**
	 * ZoopTestSet 
	 * 
	 * @param mixed $setName 
	 * @param mixed $logger 
	 * @access public
	 * @return void
	 */
	function ZoopTestSet($setName=null,$logger=null) {
		$this->setName = $setName ? $setName : get_class($this);
		$this->logger = $logger;
	}

	/**
	 * Load a yaml config file for the tests
	 *
	 * 
	 * @access public
	 * @return void
	 */
	function loadConfig() {
		// to be written when needed

	}

	/**
	 * runTests 
	 * 
	 * @access public
	 * @return void
	 */
	function runTests() {
		$methods = get_class_methods($this);
		foreach($methods as $method) {
			if(strncmp($method,'test',4) === 0) {
				$testName = substr($method,4);
				$this->clearMsgs();
				$rv = $this->$method();
				$this->result($testName,$rv,$this->getMsgs());
			}
		}	
	}
	
	/**
	 * clearMsgs 
	 * 
	 * @access public
	 * @return void
	 */
	function clearMsgs() {
		$this->msgs = array();
	}

	/**
	 * msg 
	 * 
	 * @param mixed $m 
	 * @access public
	 * @return void
	 */
	function msg($m) {
		$this->msgs[] = $m;
	}

	/**
	 * getMsgs 
	 * 
	 * @param string $sep 
	 * @access public
	 * @return void
	 */
	function getMsgs($sep = "\n") {
		return join($sep,$this->msgs);
	}

	/**
	 * result 
	 * 
	 * @param mixed $testName 
	 * @param mixed $testResult 
	 * @param mixed $msg 
	 * @access public
	 * @return void
	 */
	function result($testName,$testResult,$msg) {
		echo "\n$testName: $testResult - $msg\n";
		if(is_object($this->logger) && method_exists($this->logger,'logTest')) {
			$this->logger->logTest($this->setName,$testName,$testResult,$msg);
		}
	}
}

?>
