<?php

/**
 * ZoopTestSuite
 * 
 * @package 
 * @version $id$
 * @copyright 2008 Portero Inc.
 * @author Weston Cann
 * @author Steve Francia
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */

class ZoopTestSuite extends UnitTestCase {

	var $suiteName = null;
	var $msgs = array();
	var $logger;
	var $requiredComponents = array();

	/**
	 * ZoopTestSuite
	 * 
	 * @param string $suiteName 
	 * @param object $logger 
	 * @access public
	 * @return void
	 */

	function ZoopTestSuite($suiteName=null,$logger=null) {
		$this->suiteName = $suiteName ? $suiteName : get_class($this);
		$this->logger = $logger;
	}

	/**
	 * loadConfig
	 * Load a yaml config file for the tests
	 * currently unimplemented
	 * 
	 * @access public
	 * @return void
	 */
	function loadConfig() {
		// to be written when needed
	}

	/**
	 * Include a Zoop Component for testing. 
	 * This will automatically handle dependencies .
	 * 
	 * @param mixed $component 
	 * @access public
	 * @return void
	 */
	function addComponent($component = false) {
		global $zoop;
		if ($component) {
			$zoop->addComponent($component);
		}
	}

	/**
	 * Initialize the test.
	 * Load the necessary components. Load the config file.
	 * 
	 * @access public
	 * @return void
	 */
	function initialize() {
		foreach ($this->requiredComponents as $component) {
			$this->addComponent($component);
		}

		global $zoop;
		$zoop->init();

		$this->loadConfig();

		$this->init();
	}

	/**
	 * Hook to be run at the end of initialize.
	 * To be overloaded by the extending classes 
	 * 
	 * @access public
	 * @return void
	 */
	function init() {

	}

	/**
	 * runTests 
	 * automatically invoked by zoop_test. Examines object methods,
	 * executes all those beginning with the word "test".
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
	 * Overload run with a bit easier calling convention, defaulted to text output 
	 * 
	 * @param string $type 
	 * @access public
	 * @return void
	 */
	function run($type = 'text') {
		if ( $type == 'html' ) {
			return $this->runHtml();
		} else {
			return $this->runText();
		} 
	}

	/**
	 * Wrapper for run with Text output
	 * 
	 * @access public
	 * @return void
	 */
	function runText() {
		return parent::run( new TextReporter() );
	}

	/**
	 * Wrapper for run with HTML output
	 * 
	 * @access public
	 * @return void
	 */
	function runHtml() {
		return parent::run( new HtmlReporter() );
	}
	
	/**
	 * clearMsgs 
	 * clears messages placed on the test suite message queue during test method execution
	 * 
	 * @access public
	 * @return void
	 */
	function clearMsgs() {
		$this->msgs = array();
	}

	/**
	 * msg 
	 * adds a message string to test suitemessage queue. Meant to be used inside of test methods
	 * 
	 * @param string $m 
	 * @access public
	 * @return void
	 */
	function msg($m) {
		$this->msgs[] = $m;
	}

	/**
	 * getMsgs 
	 * retreives message strings on the test message queue, joined by the argument to getMsgs, or, by default, "\n"
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
	 * echoes and potentially logs (if a logger is set) the name of a test, its result value, and any messages that may accompany the result.
	 * 
	 * @param string $testName 
	 * @param mixed $testResult 
	 * @param string $msg 
	 * @access public
	 * @return void
	 */

	function result($testName,$testResult,$msg) {
		echo "\n$testName: $testResult - $msg\n";
		if(is_object($this->logger) && method_exists($this->logger,'logTest')) {
			$this->logger->logTest($this->suiteName,$testName,$testResult,$msg);
		}
	}
}

?>
