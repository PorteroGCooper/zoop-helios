<?php
//define("APP_STATUS", 'dev');
//define("APP_DIR", dirname(__file__));
//define("APP_TEMP_DIR", APP_DIR . "/tmp");
define('ZOOP_DIR', dirname(__file__));
define("CONFIG_FILE", ZOOP_DIR . "/test/config.yaml");
$x = false; //change to true to debug;

include_once(ZOOP_DIR . "/zoop.php");
$zoop = &new zoop(dirname(__file__));
//Config::Load();

// PATH_SEPARATOR is : on Mac OS X? Not since HFS+ on OS 9, I think
//	require_once($zoop_dir . '/' . 'zoop.php');		
$zoop_dir = ZOOP_DIR;
require_once($zoop_dir . '/' . 'test/ZoopTestSet.php');

array_shift($argv); // get rid of the "script" argv
$flags = array();
$targets = array();

// Parse command line arguments and targets. This is a good candidate for
// factoring out into its own class.
foreach($argv as $v) {
	if ($x) echo "\n argument: $v ";
	if(strpos($v,'--') === 0) {
		if ($x) echo "is a flag";
		$v=substr($v,2);
		if($eqi = strpos($v,'=')) {
			$flagname = substr($v,0,$eqi);
			$flags[$flagname] = substr($v,$eqi);
		}
		else
			$flags[$v] = true;
	}
	else {
		if ($x) echo "is a target";
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
	if ($x) echo "\nTest target: $target";
	if(file_exists($target) && is_dir($target) && ($target != 'tests')) {
		chdir($target) || die("\nCan't change to target $target\n");
	}
	if ($x) echo " (in dir: ".getcwd().")";
	if(file_exists('tests') && is_dir('tests')) {
		if(!($testsdir = opendir('tests'))) {
			die("\nCan't open tests directory under $target\n");
		}
		else {
			if ($x) echo "\nin ".getcwd()."/tests";
			$matches = array();	
			while($testSetFileName = readdir($testsdir)) {
				if ($x) echo "\nfile $testSetFileName";
				if( preg_match('/(.*)(\\.class)?\\.php$/',$testSetFileName,$matches)
					&& include_once($path.'/'.$target.'/tests/'.$testSetFileName)) {
						if ($x) echo " has a php extension ";
						$className = $matches[1];
						if(class_exists($className)) {
							echo "\nTest Set $className\n";
							$testSet = new $className();
							$testSet->loadConfig();
							$testSet->runTests();
						}					
					}
				else {
					if ($x) echo " doesn't have a php extension";
				}
			}
		}
	}
}
echo "\n";
?>
