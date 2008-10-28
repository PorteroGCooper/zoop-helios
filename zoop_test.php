#!/usr/bin/php -q
<?php
/**
* @category zoop_test
* @package zoop_test
*/

// Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/**
 * This is a script meant to be called from the cli. 
 */

//define("APP_STATUS", 'dev');
//define("APP_DIR", dirname(__file__));
//define("APP_TEMP_DIR", APP_DIR . "/tmp");
define('ZOOP_DIR', dirname(__file__));
define("CONFIG_FILE", ZOOP_DIR . "/test/config.yaml");
define("DEBUG", false); //change to true to debug;

include_once(ZOOP_DIR . "/zoop.php");
$zoop = &new zoop(dirname(__file__));
$zoop->addComponent('simpletest');
$zoop->init();
$zoop->run();
//Config::Load();

// PATH_SEPARATOR is : on Mac OS X? Not since HFS+ on OS 9, I think
//	require_once($zoop_dir . '/' . 'zoop.php');		
$zoop_dir = ZOOP_DIR;
require_once($zoop_dir . '/' . 'test/ZoopTestSuite.php');

array_shift($argv); // get rid of the "script" argv
$flags = array();
$targets = array();

// Parse command line arguments and targets. This is a good candidate for
// factoring out into its own class.
foreach($argv as $v) {
	if (DEBUG) echo "\n argument: $v ";
	if(strpos($v,'--') === 0) {
		if (DEBUG) echo "is a flag";
		$v=substr($v,2);
		if($eqi = strpos($v,'=')) {
			$flagname = substr($v,0,$eqi);
			$flags[$flagname] = substr($v,$eqi);
		}
		else
			$flags[$v] = true;
	}
	else {
		if (DEBUG) echo "is a target";
		array_push($targets,$v);
	}
}

// Only flag at the moment, path to directory of operation
$path = isset($flags['path']) ? $flags['path'] : getcwd();
chdir($path) || die("Can't change to given path $path\n");

// examine each target directory. If it's called test, it's what we want. If it contains a directory called test, then that's what we want. Then include each php file, check it for matching test class names, instantiate these, invoking the runTests method on each

if(($tcount = count($targets)) < 1) die("\nNo test targets. Stopping.\n");
else echo("\n$tcount targets\n");

foreach($targets as $target) {
	chdir($path);
	if (DEBUG) echo "\nTest target: $target";
	if(file_exists($target) && is_dir($target) && ($target != 'tests')) {
		chdir($target) || die("\nCan't change to target $target\n");
		$zoop->addComponent($target);
	}
	if (DEBUG) echo " \n(in dir: ".getcwd().")";
	if(file_exists('tests') && is_dir('tests')) {
		if(!($testsdir = opendir('tests'))) {
			die("\nCan't open tests directory under $target\n");
		}
		else {
			if (DEBUG) echo "\nin ".getcwd()."/tests";
			$matches = array();	
			// Load Config files in Directory
			while($testConfigFileName = readdir($testsdir)) {
				if (DEBUG) echo "\nFILE: $testConfigFileName";
				if( preg_match('#(.*)\\.config\\.yaml$#',$testConfigFileName,$matches)) {
					if (DEBUG) { echo " is a CONFIG file" ; }
					$configPath = $matches[1];
					Config::suggest($path.'/'.$target.'/tests/'.$testConfigFileName, 'test.zoop.' . $configPath);
				}
			}

			rewinddir($testsdir);

			$matches = array();	
			// Load and Run Tests in Directory
			// Unit Test files need to end in .test.php
			while($testSetFileName = readdir($testsdir)) {
				if (DEBUG) echo "\nfile $testSetFileName";
				if( preg_match('#(.*)\\.test\\.php$#',$testSetFileName,$matches)
					&& include_once($path.'/'.$target.'/tests/'.$testSetFileName)) {
						if (DEBUG) { echo " is a TEST file  " ; }
						$className = $matches[1];
						if(class_exists($className)) {
							echo "\nTest Set $className\n";
							$testSet =& new $className();
							$testSet->initialize();
							$testSet->run();
							// $testSet->runTests();
						}					
				} else {
					#if (DEBUG) echo " isn't a php test file (.test.php extension)";
				}
			}
		}
	}
}
echo "\n";
