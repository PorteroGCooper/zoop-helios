#!/usr/bin/php -q
<?php
/**
* @category zoop_create
* @package zoop_create
*/

// include_once( dirname( __FILE__ ) . "/app/utils.php" );
error_reporting(E_ALL);

// Copyright (c) 2007 Supernerd LLC and Contributors.
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
 * It is also useful as a class if wanting to include for web based creation.
 */

if (isset( $_SERVER['argc'] ) && $_SERVER['argc'] > 0 )
{
	$method = $_SERVER['argv'][1];

	$create = new zoop_create( getcwd() );

	switch ($method) 
	{
		case 'project':	
			$create->project( $_SERVER['argv'][2] );
			break;
		case 'zone':
			$create->zone( $_SERVER['argv'][2] );
			break;
		case 'config':
			$create->config( $_SERVER['argv'][2] );
			break;
		default:
			echo ("NOT A VALID COMMAND");			
			break;
	}
}


/**
 * zoop_create
 *
 * @package
 * @version $id$
 * @copyright 1997-2007 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class zoop_create
{
	/**
	 * init
	 *
	 * @var array
	 * @access public
	 */
	var $init = array();

	var $path = "";
	var $projectName = "";
	var $oTag = '<?php';
	var $cTag = '?>';
	var $useClose = true;

	/**
	 * zoop
	 *
	 * @param mixed $appPath
	 * @access public
	 * @return void
	 */
	function zoop_create($appPath = NULL)
	{
		$this->path = dirname(__file__);
		include_once( $this->path . "/app/utils.php" );

		if($appPath == NULL)
			$this->appPath = $this->path;
		else
			$this->appPath = $appPath;
	}

	function setProjectPath($path)
	{
		if ( !empty ($path ) ) {
			$this->projectPath = $path;
		} else {
			$this->projectPath = $this->appPath;
		}
	}

	function project($name)
	{
		$this->projectName = $name;
		$this->setProjectPath($this->appPath . DIRECTORY_SEPARATOR . $name);

		// MAKE ALL THE DIRECTORIES FIRST
		$this->skeletonDirs();
		// CREATE THE ESSENTIAL FILES
		$this->indexFile();
		$this->includesFile();
		$this->configFile();
		$this->zone("default");
		$this->zone("admin");
	}

	function skeletonDirs()
	{
		$dirstring = "config classes guiplugins GuiControls GuiWidgets mail objects public/resources templates/default/admin templates/default/layout tmp/gui zones";
		$dirstring = str_replace('/', DIRECTORY_SEPARATOR, $dirstring);
		$dirs = explode(" ", $dirstring);

		foreach ($dirs as $dir) {
			mkdirr( $this->projectPath . DIRECTORY_SEPARATOR . "$dir");
		}
	}

	function setFile( $name, $content )
	{
		$name = str_replace('/', DIRECTORY_SEPARATOR, $name);	
		file_set_contents($this->projectPath . DIRECTORY_SEPARATOR . $name, $content);
	}

	function indexFile()
	{
		$index = <<<INDEX
$this->oTag
	include_once("includes.php");
	\$zoop->run();
$this->cTag
INDEX;

		$this->setFile("index.php", $index);
	}

	function includesFile()
	{
		$includes = <<<INCLUDES
$this->oTag
include_once(dirname(__file__) . "/config.php");

include_once(zoop_dir . "/zoop.php");

\$zoop = &new zoop(dirname(__file__));

////////////////////////////////////////////////////
// COMPONENTS									  //
////////////////////////////////////////////////////

// \$zoop->addComponent('db');
\$zoop->addComponent('gui');
\$zoop->addComponent('guicontrol');
// \$zoop->addComponent('pdf');
// \$zoop->addComponent('spell');
// \$zoop->addComponent('userfiles');
// \$zoop->addComponent('sequence');
// \$zoop->addComponent('forms');
// \$zoop->addComponent('mail');
// \$zoop->addComponent('auth');
// \$zoop->addComponent('zcache');

////////////////////////////////////////////////////
//  ZONES (CONTROLLERS)							  //
//  Located in $this->projectPath/zones/		  //
////////////////////////////////////////////////////

\$zoop->addZone('default');
\$zoop->addZone('admin');

////////////////////////////////////////////////////
//  OBJECTS									      //
//	Located in $this->projectPath/objects/		  //	
////////////////////////////////////////////////////

// \$zoop->addObject('blocks');

////////////////////////////////////////////////////
//  CLASSES										  //
//	Located in $this->projectPath/classes/		  //
////////////////////////////////////////////////////

// \$zoop->addClass('math');

////////////////////////////////////////////////////
//  include other needed files here			      //
////////////////////////////////////////////////////

//	include_once(dirname(__file__) . "/misc.php");

////////////////////////////////////////////////////
//  include PEAR classes/libs here			  	  //
////////////////////////////////////////////////////

// include_once("XML/Unserializer.php");
//
\$zoop->init();
$this->cTag
INCLUDES;

		$this->setFile( "includes.php", $includes);
	}	
	function configFile()
	{
		$config = <<<CONFIG
$this->oTag;
/////////////////////////////////////////////////
//
//	File: config.php
//
//		Set Application options
//
/////////////////////////////////////////////////

	// DEFINE THE APPLICATION STATUS - affects error handling and useful for making deployment specific config options
	// use dev, test or live
	define("app_status", 'dev');

	// DEFINE YOUR APPLICATIONS DIRECTORY - use this when including files from your application
	define("app_dir", dirname(__file__));	// we should use this whenever including a file from our application

	//	DEFINE YOUR SYSTEMS zoop DIRECTORY
	if(app_status == 'dev')
	{
		//define('zoop_dir', '/home/steve/Projects/zoop1.0'); //can be absolute or
		define('zoop_dir', app_dir . "/../zoop"); // it can be relative
	}
	else
	{
		//when deploying to live or test servers, you may want to deploy with zoop in the current directory
		define('zoop_dir', dirname(__file__));
	}

	define("LOG_FILE", app_dir . "/../log/errors.log.html");
	define("app_temp_dir", dirname(__file__) . '/tmp');

//////////////////////////////////////////////////////
//					PEAR Stuff						//
//////////////////////////////////////////////////////
//  uncomment one line if using the zoop/libs instead of your systemwide pear libraries.

	ini_set('include_path',ini_get('include_path').':'. zoop_dir . '/lib/pear:'); // FOR UNIX
//	ini_set('include_path',ini_get('include_path').';'. zoop_dir . '/lib/pear:'); // FOR WINDOWS

//////////////////////////////////////////////////////
//				Template Stuff		   			    //
//////////////////////////////////////////////////////

	define("app_default_title", "My Zoop Application");

//////////////////////////////////////////////////////
//				Security Features	         	    //
//////////////////////////////////////////////////////

	define('strip_url_vars' ,1); //disallow spaces in url parameters, helps prevent sql injection.
	define("verify_queries", 1); //disallow multi statement queries, e.g. select * from person where id = '';delete from person;
	//helps prevent some forms of sql injection.
	define('filter_input', 1); //tells getPost family of functions to filter post variables based on type.
	define('hide_post', 1); //turns off access to post variables through anything but the getPost family of functions
	define('zone_saveinsession', 1); //determines whether zone objects are saved in sessions, allowing you to use zones to contain persistent variables
	//defaults to true, for Backwards Compatibility.
	define('show_warnings', 1); // determines whether the bug function is displayed or not.
$this->cTag;
CONFIG;
		$this->setFile("config.php", $config);
	}

	function config ($name)
	{
		$configFile = $this->appPath . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "$name.php";
		if (!file_exists($configFile ) ) {
			copy ( $this->path . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . "defaultConstants.php", $configFile);
		} else {
			echo " File already exists";
		}
	}

	function zone ($name, $functions = null)
	{
		$zone_name = "zone_{$name}";
		$default_functions[$zone_name] = "";
		$default_functions['makePath'] = "return \"/$name\";";
		$default_functions['initZone'] = "";
		$default_functions['initPages'] = "";
		$default_functions['pageDefault'] = "";		

		if (!empty ($functions ) ) {
			$functions = array_merge( $default_functions, $functions );
		} else {
			$functions = $default_functions;
		}

		$zoneContent = <<<Z
$this->oTag
class zone_$name extends zone
{
Z;
		foreach ($functions as $functionName => $functionContent )
		{
			$pre = strtolower ( substr ($functionName, 0, 4 ) );
			if ( $pre == "init" || $pre == "page" || $pre == "post" ) {
				$zoneContent .=  <<<Z

	function $functionName(\$inPath)
	{
		$functionContent
	}

Z;
			} else {
				$zoneContent .=  <<<Z

	function $functionName()
	{
		$functionContent
	}

Z;
			}
		}
			
		$zoneContent .= <<<Z
}
$this->cTag
Z;

		$this->setFile("zones/$name.php", $zoneContent);
		mkdirr($this->projectPath . "/templates/default/zones/$name");
	}

}
?>
