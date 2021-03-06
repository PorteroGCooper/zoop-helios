<?php

// Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

$GLOBALS['gUrlVars'] = array();
$GLOBALS['gPathParts'] = array();
$GLOBALS['gZoneUrls'] = array();
$GLOBALS['gZoneBasePath'] = null;

/**
 * zone
 * Extend this class any time you need
 * a new section on your site.
 * Files should be "{zonename}.php" and
 * placed in %APP_DIR%/zones/
 *
 * This class is instantiated and the
 * handlerequest function called from the
 * index.php file.	 It will check path
 * and if a class member function of each
 * name exists (/path = path) information
 * and will automatically execute
 *
 * rmb 7-20-2001
 * 
 * @group zone
 * @endgroup
 *
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Richard Bateman
 * @author John Lesueur
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @author Andrew Hayward <andrew@gratuitousPawn.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class zone {
	//////////////////////////////////////////////////////////////
	//  These are variables each instance uses to track itself  //
	//////////////////////////////////////////////////////////////

	/**
	 * error
	 * The last error message recorded.
	 *
	 * @var string
	 * @access public
	 */
	var $error = "";
	/**
	 * errornum
	 *
	 * @var int
	 * @access public
	 */
	var $errornum = 0;

	/**
	 * zonename
	 * Contains the pathname of this zone.
	 * It will also contain the variable if urlvars
	 * is enabled for this class.
	 *
	 * @var string
	 * @access public
	 */
	var $zonename = "";

	/**
	 * zonetype
	 * Contains the name of this zone.
	 *
	 * @var string
	 * @access public
	 */
	var $zonetype = "";

	/**
	 * parent
	 * Reference to the parent of this zone
	 *
	 * @var mixed
	 * @access public
	 */
	var $parent = null;

	/**
	 * _zone
	 * Array of subclasses for this zone
	 *
	 * @var array
	 * @access protected
	 */
	var $_zone = array();	//

	/**
	 * _inPath
	 * Variable to store $inPath for entire zone
	 *
	 * @var mixed
	 * @access protected
	 */
	var $_inPath;

	/**
	 * The type of the request. Options are JSRS, XMLRPC, post, get
	 * @var string
	 */
	protected $requestType = null;

	/**
	 * Set of Data used for Exporting and Rendering. Defined by a page Request
	 * @var array
	 */
	protected $dataSet = array();

	/**
	 * The extension specificied in the path
	 *
	 * @var mixed
	 * @access public
	 */
	var $requestedExt;

	/**
	 * The extension that the zone will allow and render for this request
	 *
	 * @var mixed
	 * @access public
	 */
	var $ext;
	
	/**
	 * an array of the given request's (allowed) output.
	 * @var String
	 */
	var $outputType;

	/**
	 * allowedChildren
	 * These are the zone names valid in this zone   -- DON'T INCLUDE THE 'zone_' PART
	 *
	 * @var array
	 * @access public
	 */
	var $allowedChildren = array();

	/**
	 * allowedParents
	 * These are the zones this zone can be a child of -- DON'T INCLUDE THE 'zone_' PART
	 *
	 * @var array
	 * @access public
	 */
	var $allowedParents = array();

	/**
	 * zoneParamNames
	 *
	 * @var array
	 * @access public
	 */
	var $zoneParamNames = array();

	/**
	 * returnPaths
	 *
	 * @var array
	 * @access public
	 */
	var $returnPaths = array();

	/**
	 * ancestors
	 *
	 * @var array
	 * @access public
	 */
	var $ancestors = array();

	/**
	 * pageVars
	 *
	 * @var array
	 * @since Version 2.0
	 * @access public
	 */
	var $pageVars = array();

	/**
	 * _zoneParams
	 * Place to store the zone parameter array with values
	 *
	 * @var array
	 * @since Version 2.0
	 * @access private
	 */
	var $_zoneParams = array();

	/**
	 * restricted_remote_post
	 * Extra security, require by default the post must be recieved by
	 * the page of the same name, this is for exceptions
	 * ADDED BY SPF - MAY 05
	 *
	 * Replaced with a open by default, restrict as requested
	 * rjl 8/25/2005
	 *
	 * @var array
	 * @access public
	 */
	var $restricted_remote_post = array();


	/**
	 * origins
	 *
	 * @var array
	 * @access public
	 */
	var $origins = array();

	/**
	 * url
	 *
	 * @var string
	 * @access public
	 */
	var $url = "";

	//////////////////////////////////////////////////////////////
	//  These are settings that will be overridden by subclass  //
	//////////////////////////////////////////////////////////////

	/**
	 * zone is wildcard zone
	 *
	 * If $wildcards == true then all path info
	 * past this class will be sent to pageDefault,
	 * or postDefault if the form was posted.
	 *
	 * http://localhost/index.php/class/these/are/parameters
	 *
	 * if $wildcards == true then the path /these/are/parameters
	 * will be sent to (page/post)Default w/out looking for a
	 * pagethese function.
	 *
	 * @access public
	 *
	 **/
	var $wildcards = false;

	/**
	 * zone has urlvars
	 *
	 * if $urlvars == <num of levels> then the fw will look for variables
	 * in the path immediately after the name of this class up to <num> levels
	 * and snag those variables and store them in the $this->urlvar array.
	 *
	 * http://localhost/index.php/user/12/add
	 *
	 * goes to pageadd() in class user, but stores a ["12"] in the
	 * $this->urlvar array.
	 *
	 * $urlzones is the same except it stores individual instances.
	 * The name is stored in $this->zonename.  The reason for individual
	 * instances is that each class instance exists in sessions.
	 * So $this->Var1 in /user/12 and $this->Var1 in /user/3
	 * are different.
	 *
	 * @var mixed
	 * @access public
	 */
	var $urlvars = false;

	/**
	 * urlzones
	 *
	 * @var mixed
	 * @access public
	 */
	var $urlzones = false;

	/**
	 * zcache
	 *
	 * @var object
	 * @access public
	 */
	var $zcache;

	/**
	 * Alias
	 * An array of all the page aliases for this zone.
	 * This is not the ideal way to do aliases anymore.
	 *
	 * @code
	 * Set $this->Alias["alias"] = "Aliastarget"
	 * @endcode
	 *
	 * @see zone::addAlias
	 * @deprecated since 2.0
	 * @var array
	 * @access public
	 */
	var $Alias = array();

	/**
	 * Private array of path aliases, used by addAlias() and getAlias()
	 *
	 * @see zone::addAlias
	 * @see zone::getAlias
	 * @var array
	 * @access private
	 */
	var $_pathAliases = array();

	/**
	 * Private array of reverse path aliases, used by Global Redirect.
	 *
	 * @see zone::addAlias
	 * @see zone::getAlias
	 * @var array
	 * @access private
	 */
	var $_redirectAliases = array();

	/**
	 * A default template to be used for HTML rendering.
	 *
	 * @see zone::outputAJAX
	 * @see zone::outputHTML
	 * @access private
	 */
	protected $_template = null;

	/**
	 * Return the Zone Name
	 * In a nested zone, it only returns the final part.
	 *
	 * @access public
	 * @return void
	 */
	function getZoneName() {
		return $this->zonename;
	}

	/**
	 * Zone Constructor
	 *
	 * @access public
	 * @return void
	 */
	function __construct()	{
		if (defined("zone_cache") && zone_cache)
			$this->initZoneCache();
		if (isset($this->Aliases) && count($this->Aliases)) {
			$this->addAliases($this->Aliases);
			$this->Aliases = array();
		}
	}

	/**
	 * findZoneName
	 *
	 * @access public
	 * @return void
	 */
	function findZoneName() {
		global $gPathParts; //an array of all the parts of the path so far

		$this->zonename = array_shift($this->_inPath); // $this->_inPath[0] IS NULL
		// pull zone name from inPath

		$GLOBALS['current_zone'] = $this->zonename; // SET TO NULL

		$gPathParts[] = $this->zonename;

		if (!$this->zonename) {
			// SINCE THIS IS NULL SET ZONENAME TO @ROOT
			$this->zonename = "@ROOT";
		}

		if (!$this->zonetype) {
			// SET $this->zonetype TO THE ZONENAME OR TO DEFAULT IF $this->zonename == @ROOT
			$this->zonetype = ($this->zonename != "@ROOT") ? $this->zonename : "Default";
		}

		$this->url = implode('/', $gPathParts);
	}

	/**
	 * findZoneParams
	 *
	 * @access public
	 * @return void
	 */
	function findZoneParams() {
		global $gUrlVars;
		global $gPathParts; //an array of all the parts of the path so far

		// CHECK THE ZONE TO SEE IF ANY VARIABLES ARE IN THE PATH.
		if($urlVarNames = $this->getZoneParamNames()) {
			// loop once for each name
			foreach ($urlVarNames as $index => $varName) {
				// we need to handle special names
				$origVarName = $varName;
				if ($varName == "*") $varName = "{0,..}";
				if ($varName == "+") $varName = "{1,..}";
				if ($varName == "?") $varName = "{1,1}";

				if (preg_match('/\{(\s?[\d]*\s?),(\s?[\d]*\s?|\s?\.\.\s?)\}/', $varName,$range)) {
					$min = $range[1];
					$max = $range[2];
					if ($max == "..") $max = PHP_INT_MAX; // Translate .. to a number

					$count = 0;
					while ($count < $max) {
						$tmpVar = array_shift($this->_inPath);
						if ( strpos ( $tmpVar, ":") ) {
							$this->_zoneParams = array_merge($this->_zoneParams, $this->_urlStringToArray($tmpVar));
							$count++;
						} else {
							if ($count < $min ) {
								trigger_error("A parameter is missing for '$origVarName' for this zone");
								$count = $max + 1;
								break;
							} else {
								array_unshift($this->_inPath, $tmpVar);
								$count = $max + 1;
								break;
							}
						}
					}
				} else {
					$varValue = array_shift( $this->_inPath );
					if(Config::get('zoop.security.strip_uri_vars')) {
						if (strtok($varValue, " \t\r\n\0\x0B") !== $varValue) {
							$varValue = $this->missingParameter($varName);
							if (is_null($varValue))
								trigger_error("The parameter '$varName' must be supplied for this zone");
						}
					}

					$this->_zoneParams[ $varName ] = $varValue;
					$gUrlVars[ $varName ] = $varValue;
					$gPathParts[] = $varValue;
				}
			}
		}
	}

	/**
	 * Parse and initialize sequences from the path.
	 *
	 * This function should be renamed, as it actually does a lot more than just setting up
	 * sequences now.
	 *
	 * Add the current url to the zone urls array. Set the current zone base path in globals.
	 * Assign the current zone path and zone base path to the $gui object.
	 *
	 * NOTE: there's a huge problem with the zone base path right now. Parent
	 * zones should have zone params included in the base path, but the current
	 * zone should not. As it stands, all zone params are stripped out of the
	 * base path. THIS IS A PROBLEM.
	 *
	 * @todo Make sure gZoneBasePath contains parent zone params.
	 *
	 * @access public
	 * @return void
	 */
	function checkPathForSequences() {
		global $gPathParts;//an array of all the parts of the path so far
		global $gZoneUrls;
		global $gZoneBasePath;
		global $gui;

		// CHECK FOR SEQUENCES
		$tmp = $gPathParts;

		global $sequenceStack;
		if(isset($sequenceStack)) {
			$temp = array_shift($tmp);//the first thing in path_info must be a null?
			array_unshift($tmp, implode(":", $sequenceStack));//reinject the sequence stack into the path_info
			array_unshift($tmp, $temp);
		}

		global $logpath;
		if(!isset($logpath))
			$logpath = array();
		$logpath[] = $this->zonename;

		$this->url = implode('/', $tmp);

		array_unshift($gZoneUrls, $this->url);
		$gZoneBasePath = '/' . $this->_getRawZonePath();
		$gui->assign('ZONE_PATH', $this->getZonePath());
		$gui->assign('ZONE_BASE_PATH', $gZoneBasePath);
	}

	/**
	 * This function will either run the page/post function, or will execute the child zone,
	 * depending on what is found in the token of the url passed
	 *
	 * How the method runs:
	 * Establish Index as the "/" and Default as the fall back method names
	 * If there is an alias, lets use it's value, rather than the url
	 * First we check the current zone for a matching page, if none exists we look for
	 * a zone to match.
	 * Lastly, if nothing matches, run default.
	 *
	 *
	 * @access public
	 * @return void
	 */
	function executeNextFunction () {
		if ( isset($this->_inPath[0]) && $this->_inPath[0] !== '' ) {
			$pathToken = $this->_inPath[0]; //SET $pathToken TO THE NEXT ZONENAME
		} else {
			$pathToken = "Index";
		}

		// Check Aliases first
		if ( isset ( $this->Alias[$pathToken] ) ) { $pathToken = $this->Alias[$pathToken]; }

			// if none are found Check functions in this zone next
			if ( ($retval = $this->_checkFuncs($pathToken, $this->_inPath) === false) ) {
				// if none are found Check other child zones next
				if ( (($retval = $this->_checkZone($pathToken, $this->_inPath)) === false) ) {
					$this->error = "The name found in the path ($pathToken) was not a valid function or class.
						Perhaps this class should have wildcards enabled?  Executing pageDefault function.";

					if ($this->getRequestType() == "XMLRPC") {
						$GLOBALS["zoopXMLRPCServer"]->returnFault(1, "Invalid XMLRPC function, $pathToken");
						$retval = true;
					}

					array_unshift($this->_inPath, 'default');
					$retval = $this->_checkFuncs("Default", $this->_inPath) ;
				}
			}

		return $retval;
	}

	/**
	 * Support the new key:value or key:value:value url page parameter structure.
	 * Populate the $this->pageVars variable
	 *
	 * @access public
	 * @return void
	 */
	function initPageVars() {
		$inPath = $this->_inPath;
		$pageName = array_shift($inPath);

		$path_array = $inPath;

		$new_path_array = array();
		foreach ($path_array as $key => $value ) {
			$new_path_array = array_merge($new_path_array, $this->_urlStringToArray($value));
		}

		$this->pageVars = $new_path_array;
	}

	/**
	 * Used to take a string in one of the formats
	 * value
	 * key:value
	 * key:value:value2:value3:...
	 * and return an array with the proper structure.
	 *
	 * @param mixed $inString
	 * @access protected
	 * @return array
	 */
	protected function _urlStringToArray($inString) {
		$tmp = explode(":", $inString);

		if (count($tmp) == 1) {
			$new_array[] = $inString;
		} elseif (count($tmp) == 2) {
			$new_array[$tmp[0]] = $tmp[1];
		} else {
			$new_key = array_shift($tmp);
			$new_array[$new_key] = $tmp;
		}

		return $new_array;
	}


	/**
	 * Set the dataset for a single request.
	 * Typically set in an init method.
	 *
	 * @param $data
	 */
	function setData($data) {
		$this->dataSet = $data;
	}

	/**
	 * Return the dataset for a single request.
	 * Called by the output* methods.
	 *
	 * @return array
	 */
	function getData() {
		return $this->dataSet;
	}

	/**
	 * Where most of the magic of the controller happens
	 * Take an array which is the various parts of the remainder of the url
	 * and determine which methods to call and (child) zones to run.
	 *
	 * Basic logic is as follows..
	 * * Convert the current path using path aliases for the current zone
	 * * If wildcards are enabled, run Default functions (page or post Default)
	 * * Capture Parameters for this Zone.
	 * * Check for Sequences.
	 * * Capture remainder of url tokens as Page Variables
	 * * Initialize this zone.
	 * * Determine if next token (part of the url) is a method in this zone,  or a child zone and execute
	 *
	 * @see zone::addAlias
	 * @see zone::executeNextFunction
	 * @param array $inPath
	 * @access public
	 * @return void
	 */
	function handleRequest( $inPath ) {
		$this->_inPath = $inPath;
		$this->setExtFromPath();
		$this->findZoneName();
		$this->checkAlias();
		// when wildcards are enabled, always execute the default function.
		if ($this->wildcards) {
			array_unshift($this->_inPath, 'default');
			return ( $this->_checkFuncs("Default", $this->_inPath) );
		}

		$this->findZoneParams();
		$this->checkPathForSequences();
		$this->initZone($this->_inPath);
		$retval = $this->executeNextFunction();

		if($this->zonename == '@ROOT') {
			global $globalTime;
			logprofile($globalTime);
		}
		return $retval;
	}

	function setExtFromPath() {
		$last = array_peek($this->_inPath);

		$this->requestedExt = substr(strrchr($last,'.'),1);
		return $this->requestedExt;
	}

	function handlePageRequest($curPath, $prefix = null) {
		$this->initPages($this->_inPath, $GLOBALS['gUrlVars']);
		$this->logPageRequest($curPath);

		$initFunc = "init" . $curPath;
		if (method_exists($this, $initFunc)) {
			$this->$initFunc();
		}

		// REMOVE THE EXTENSION FROM THE PAGE PARAMS
		$last = array_pop($this->pageVars);
		$ext = $this->getExtension();

		if (substr($last, -1 - strlen($ext) ) == "." . $ext) {
			$last = substr($last, 0, -1 - strlen($ext));
		}

		array_push($this->pageVars, $last);
		$return = $this->callRequestFunction($curPath, $prefix);

		$this->closePages($this->_inPath, $GLOBALS['gUrlVars']);
		if ($this->getRequestType() == 'POST') {
			$this->closePosts($this->_inPath, $GLOBALS['gUrlVars']);
		}
		return $return;
	}

	function callRequestFunction ($curPath, $outputType = null)  {

		if ($outputType === null) {
			$outputType = $this->getRequestType();
		}

		// If the curPath has the allowed ext, strip it.
		$ext = $this->getExtension();

		if (substr($curPath, -1 - strlen($ext) ) == "." . $ext) {
			$curPath = substr($curPath, 0, -1 - strlen($ext));
		}

		$funcName = strtolower($outputType) . ucfirst($curPath);
		$outputFuncName = "output" . strtoupper($outputType);
		$pageFunc =  'page' . ucfirst($curPath) ;

		if (method_exists($this, $funcName)) {
			return $this->$funcName($this->_inPath, $GLOBALS['gUrlVars']);
		} elseif ($outputType == 'HTML' && method_exists($this, $pageFunc)) {
			return $this->$pageFunc($this->_inPath, $GLOBALS['gUrlVars']);
		} elseif (method_exists($this, $outputFuncName )) {
			return $this->$outputFuncName($this->_inPath, $GLOBALS['gUrlVars']);
		} else {
			return false;
		}
	}

	/**
	 * Set the allowable outputs for this request path.
	 *
	 * @param $array
	 * @return $array
	 */
	function setAllowableOutput($array = null) {
		if ($array === null) {
			$this->allowableOutput = Config::Get('zoop.zone.allowable_output');
		} else {
			$this->allowableOutput = $array;
		}
		return $this->allowableOutput;
	}

	/**
	 * Returns an array of all allowable output formats.
	 * If nothing is set.. Return html.
	 *
	 * @return unknown_type
	 */
	function getAllowableOutput() {
		if (isset($this->allowableOutput)) {
			return $this->allowableOutput;
		} else {
			$this->setAllowableOutput();
			if ( isset($this->allowableOutput) ) {
				return $this->allowableOutput;
			} else {
				return array('html');
			}
		}
	}

	function logPageRequest($curPath) {
		global $logpath;

		$logpath[] = "$curPath/" . $this->getRequestType();
		$funcName = $this->getRequestType() . $curPath;
		$GLOBALS['current_function'] = $funcName;
	}


	/**
	 * Figure out and return the extension that is being explicitly requested.
	 *
	 * @access public
	 * @return void
	 */
	function getExtension() {
		$ext = $this->getRequestedExtension();

		foreach ($this->getAllowableOutput() as $o) {
			if (strtolower($ext) == strtolower($o)) {
				$this->ext = strtolower($o);
				return $this->ext;
			}
		}

		return false;
	}

	/**
	 * Returns the requested Extension
	 * This is set during the handleRequest method.
	 *
	 * @access public
	 * @return string
	 */
	function getRequestedExtension() {
		return $this->requestedExt;
	}

	/**
	 * This method determines what the requested output type is and returns it.
	 * Defaults to html
	 * First check the requested extension.
	 * Then check if $GET['output'] is set.
	 * Finally fallback to the header requested.
	 *
	 * @return string
	 */
	protected function getRequestedOutputType() {
		if ($this->getExtension() ) {
			return $this->getExtension();
		} elseif ($get = GET::getText('output')) {
			return $get;
		}

		return "html";
	}

	protected function getOutputType() {
		if (isset($this->outputType)) {
			return $this->outputType;
		}

		$output = $this->getRequestedOutputType();

		foreach ($this->getAllowableOutput() as $o) {
			if (strtolower($output) == strtolower($o)) {
				$this->outputType = strtolower($o);
				return $this->outputType;
			}
		}

		$this->responsePage(404);
	}

	/**
	 * Figure out which requestType has been requested and return it.
	 * There are three request types, XMLRPC, GET, POST
	 *
	 *
	 * @return string requestType
	 */
	function getRequestType() {
		if (isset($this->requestType)) {
			return $this->requestType;
		}

		if (isset($_REQUEST['jsrsContext']) && substr($_REQUEST['jsrsContext'], 0, 7) == "phpjsrs") {
			$this->requestType = "JSRS";
		} elseif (xmlrpc_server::isRequest()) {
				$this->requestType = "XMLRPC";
			$GLOBALS["zoopXMLRPCServer"] = new xmlrpc_server();
			$GLOBALS["zoopXMLRPCServer"]->startServer();
		} elseif ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
			$this->requestType = 'POST';
		} else {
			$this->requestType = strtoupper($this->getOutputType());
		}

		return $this->requestType;
	}

	/**
	 * For a given url Token, find the function to execute in this zone
	 *
	 *
	 * @param string $curPath
	 * @param array $inPath
	 * @access protected
	 * @return void
	 */
	function _checkFuncs($curPath, $inPath) {
		$this->initPageVars();

		switch (strtoupper($this->getRequestType())) {

			case 'XMLRPC':
				return $this->_xmlrpcDispatch($curPath, $this->_inPath);
				break;
			case 'POST':
				if (method_exists($this, "post" . $curPath)) {
					return $this->handlePageRequest($curPath);
				} else if((method_exists($this, "page" . $curPath)) || (method_exists($this, "init" . $curPath))) {
					redirect($_SERVER["REQUEST_URI"]);
				}
				break;
			default:
				if((method_exists($this, "page" . $curPath)) || (method_exists($this, "init" . $curPath))) {
					return $this->handlePageRequest($curPath);
					break;
				}
		}

		return false;
	}

	/**
	 * _checkAllowedPost
	 *
	 * @param mixed $curPath
	 * @access protected
	 * @return void
	 */
	function _checkAllowedPost($curPath) {
		//  THIS FUNCTION CHECKS TO MAKE SURE THAT THE CURRENT PAGE IS THE SAME AS THE REFERRING PAGE
		//  OR THAT THE CURRENT PAGE IS PERMITTED TO HAVE REMOTE POSTING
		//  SPF - MAY 05

		//  Restricting should not be the default behavior, as this breaks compatibility, and is not
		//	expected. rjl 8/25/2005

		if (!in_array($curPath, $this->restricted_remote_post))
			return true;

		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
			$prefix = "https://";
		else
			$prefix = "http://";

		$cur_url = $prefix . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		if (isset($_SERVER['HTTP_REFERER'])  && $_SERVER['HTTP_REFERER'] == $cur_url)
			return true;
		else
			return false;
	}

	/**
	 * Check and Execute iff the passed in url Token is a zone.
	 * First check if an explicit child zone exists, that will take precedence.
	 * Second check if a zone matches.. if one does. Check it to ensure it
	 * has permission to be adopted.
	 *
	 * @see zone::_checkExplicitChildZone
	 * @see zone::_executeChildZone
	 * @param string $zoneName part of url to test
	 * @param array $inPath
	 * @access protected
	 * @return boolean
	 */
	function _checkZone($zoneName, $inPath) {
		if ($this->_checkExplicitChildZone($zoneName, $inPath) !== false) {
			return true;
		}
			//	if the class exists and this zone has no allowed children or this is one of the allowed children
			//		not the easiest thing to follow but I guess it needs to be done this way since the object
			//		hasn't been instantiated yet.  Maybe a static method would be better here for getting the allowed
			//		parents
		$childZoneName = "zone_" . $zoneName;
		if ( class_exists($childZoneName) && (
			count($this->allowedChildren) < 1
			|| in_array($zoneName, $this->allowedChildren) )) {
				//	create the new zone object if it does not exist
				$this->_executeChildZone($zoneName, $inPath);
		} else {
			return false;
		}

	}

	/**
	 * Returns the parent zone if set.
	 *
	 * @access public
	 * @return mixed
	 */
	function getParent() {
		if (isset($this->parent)) {
			return $this->parent;
		} else {
			return false;
		}
	}

	/**
	 * Method to get full zone name when working
	 * with explicit children zones.
	 *
	 * @access protected
	 * @return string
	 */
	function _getClassNamePath() {
		$parent = $this->getParent();
		if ( $parent && $parent->getName() != "@ROOT") {
			return $this->parent->_getClassNamePath() ."_" . $this->getName();
		} else {
			return $this->getName();
		}
	}

	/**
	 * Instantiate and validate child zone.
	 * Setup zone relationships.
	 * Ensures allowed to be related.
	 * Run handleRequest on Child Zone
	 *
	 * @param mixed $zoneName
	 * @param mixed $inPath
	 * @access protected
	 * @return void
	 */
	function _executeChildZone($zoneName, $inPath) {
		$childZoneName = "zone_" . $zoneName;

		if ( !isset( $this->_zone[$zoneName] ) ) {
			$this->_zone[$zoneName] = new $childZoneName();
			$this->_zone[$zoneName]->parent =& $this;
		}

		// check to see if this is an allowed parent for the class we just created
		if ( count($this->_zone[$zoneName]->allowedParents) > 0
			&& !in_array($this->zonetype, $this->_zone[$zoneName]->allowedParents)) {
				return false;
		} elseif ($this->getName() != "@ROOT") {
			$this->_zone[$zoneName]->allowedParents = array($this->_getClassNamePath());
		}

		$retval = $this->_zone[$zoneName]->handleRequest($inPath);

		if ($retval === false) {
			$retval = "";
		}

		$this->closeZone($inPath);

		return( $retval );
	}

	/**
	 * Check to see if $zoneName is an explicit child zone.
	 * An Explicit Child Zone is one that has been placed in a
	 * subdirectory with the same name as the parent zone and has
	 * the class name zone_parentZoneName_ZoneName.
	 * Explicit Children Zones can only have one parent.
	 *
	 * @param string $zoneName
	 * @param array $inPath
	 * @access protected
	 * @return boolean
	 */
	function _checkExplicitChildZone($zoneName, $inPath) {
		$explicitChildName = $this->_getClassNamePath() . "_$zoneName";
		$explicitChildZoneName = "zone_" . $explicitChildName;
		if ( class_exists($explicitChildZoneName)) {
			$this->_executeChildZone($explicitChildName, $inPath);
			return true;
		}
		return false;
	}

	/**
	 * _xmlrpcDispatch
	 *
	 * @param mixed $curPath
	 * @param mixed $inPath
	 * @access protected
	 * @return void
	 */
	function _xmlrpcDispatch($curPath, $inPath) {
		if (method_exists($this, "xmlrpc" . $curPath)) {
			$funcname = "xmlrpc" . $curPath;

			$params = $GLOBALS["zoopXMLRPCServer"]->getRequestVars();

			$methodname = $GLOBALS["zoopXMLRPCServer"]->methodname;

			ob_start();
			$retval = $this->$funcname($inPath, $params, $methodname);
			$debug = ob_get_contents();
			ob_end_clean();

			if (is_object($retval) && isset($retval->code)) {
				$GLOBALS["zoopXMLRPCServer"]->returnFault($retval->code, $retval->string);
			} elseif (is_array($retval)) {
				$GLOBALS["zoopXMLRPCServer"]->returnValues($retval);
			} elseif ($retval === true || $retval === false) {
				$val["value"] = $retval ? 1 : 0;
				$val["type"] = "boolean";
				$GLOBALS["zoopXMLRPCServer"]->returnValues($val);
			} elseif (is_numeric($retval) || strlen($retval) > 0) {
				$GLOBALS["zoopXMLRPCServer"]->returnValues($retval);
			} else {
				$GLOBALS["zoopXMLRPCServer"]->returnFault(2, "Function did not return a valid array");
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Useful if one is not storing the zones in the session (defined in config.php),
	 * for storing zone variables in the session without the overhead of the entire session.
	 * also used for retriving that variable from the session.
	 * @since Version 1.2
	 * @param string $name name of variable
	 * @param mixed $value value to store in variable
	 * @return mixed
	 */
	function session($name, $value = null) {
		global $sGlobals;
		if($value === null) {
			if(isset($sGlobals->zones)
				&& isset($sGlobals->zones[$this->zonename])
				&& isset($sGlobals->zones[$this->zonename][$name])) {
				return $sGlobals->zones[$this->zonename][$name];
			} else {
				return null;
			}
		} else {
			$sGlobals->zones[$this->zonename][$name] = $value;
		}
	}

	/**
	 * Catchall for any unhanled request to the zone.
	 * Should be overridden to be the default function
	 * (in case there is either A: no path info, or B: no matching function or class for the path";
	 * If zone has wildcards on it will handle all requests.
	 * If the url doesn't match any zone pages or childzones then this method is called. By default it is a 404 page.
	 *
	 * @since 1.0
	 * @param mixed $inPath
	 * @access public
	 * @return void
	 */
	function pageDefault($inPath) {
		$this->responsePage(404);
	}

	/**
	 * pageIndex
	 * This is the method that gets called when a request is made to host/zone/
	 * By default it will call $this->pageDefault which will in turn result in a 404 by default.
	 * Please overload this method.
	 *
	 * @since 2.0
	 * @param mixed $inPath
	 * @access public
	 * @return void
	 */
	function pageIndex($inPath) {
		$this->pageDefault($inPath);
	}

	/**
	 * responsePage404
	 * a default 404 page to be called throughout the application whenever a page is not found.
	 * This is a placeholder.. Please modify it to be a better function.
	 * Perhaps a subclass with all (many) of the response headers would be in order.
	 * Would need to be able to be extended so apps could stylize their own.
	 *
	 * @access public
	 * @return void
	 */
	function responsePage404() {
		deprecated('Please use zone::responsePage(404); instead of zone::responsePage404();');
		$this->responsePage(404);
	}

	/**
	 * Return the given response page.
	 *
	 * Returns HTTP header and response code. Displays a default response error message.
	 * This response page can be overridden by defining response templates in your app config:
	 *
	 * @code
	 * gui:
	 *     templates:
	 *         response:
	 *             404: response/not_found.tpl
	 *             403: response/access_denied.tpl
	 * @code
	 *
	 * @access public
	 * @param int $code. (default: 404)
	 * @return void
	 */
	function responsePage($code = 404, $message = null) {
		$codes = array(
			'100' => "100 Continue",
			'101' => "101 Switching Protocols",
			'200' => "200 OK",
			'201' => "201 Created",
			'202' => "202 Accepted",
			'203' => "203 Non-Authoritative Information",
			'204' => "204 No Content",
			'205' => "205 Reset Content",
			'206' => "206 Partial Content",
			'300' => "300 Multiple Choices",
			'301' => "301 Moved Permanently",
			'302' => "302 Found",
			'303' => "303 See Other",
			'304' => "304 Not Modified",
			'305' => "305 Use Proxy",
			'307' => "307 Temporary Redirect",
			'400' => "400 Bad Request",
			'401' => "401 Unauthorized",
			'402' => "402 Payment Required",
			'403' => "403 Forbidden",
			'404' => "404 Not Found",
			'405' => "405 Method Not Allowed",
			'406' => "406 Not Acceptable",
			'407' => "407 Proxy Authentication Required",
			'408' => "408 Request Time-out",
			'409' => "409 Conflict",
			'410' => "410 Gone",
			'411' => "411 Length Required",
			'412' => "412 Precondition Failed",
			'413' => "413 Request Entity Too Large",
			'414' => "414 Request-URI Too Large",
			'415' => "415 Unsupported Media Type",
			'416' => "416 Requested range not satisfiable",
			'417' => "417 Expectation Failed",
			'500' => "500 Internal Server Error",
			'501' => "501 Not Implemented",
			'502' => "502 Bad Gateway",
			'503' => "503 Service Unavailable",
			'504' => "504 Gateway Time-out",
			'505' => "505 HTTP Version not supported"
		);

		if (!isset($codes[$code])) {
			trigger_error('Unknown response code: ' . $code);
			return;
		}

		if ($message === null) {
			$message = $codes[$code];
		}

		header('Status: ' . $codes[$code], true, $code);
		global $gui;

		$gui->assign('title', $message);
		if($template = Config::get('zoop.gui.templates.response.' . $code)) {
			$gui->assign('response_message', $message);
			$gui->generate($template);
		} else {
			$gui->assignContent('<h2>'.$message.'</h2>');
			$gui->generate();
		}
		exit();
	}

	/**
	 * Initialize the zone.
	 * Should be overridden in each zone if you would like code
	 * to execute each time it hits the zone's handleRequest function.
	 *
	 * Other than the constructor, this is the only method that is called in a
	 * parent zone.
	 *
	 *
	 * @param mixed $inPath
	 * @access public
	 * @return void
	 */
	function initZone($inPath) { }

	/**
	 * Close the zone.
	 * Should be overridden in each zone if you would like code
	 * to execute each time before it leaves the zone's
	 * handleRequest function.
	 *
	 * @param mixed $inPath
	 * @access public
	 * @return void
	 */
	function closeZone($inPath) { }

	/**
	 * initPages
	 * Executed before any page or post function in the zone.
	 * Only called in the zone you are in.
	 * This method will not be called in parent zones.
	 *
	 * @param mixed $inPath
	 * @access public
	 * @return void
	 */
	function initPages($inPath) { }

	/**
	 * closePages
	 * Executed after any page or post function in the zone.
	 *
	 * @param mixed $inPath
	 * @access public
	 * @return void
	 */
	function closePages($inPath) { }

	/**
	 * closePosts
	 * Executed after any post function in the zone.
	 *
	 * @param mixed $inPath
	 * @access public
	 * @return void
	 */
	function closePosts($inPath) { }

	/**
	 * getName
	 *
	 * @access public
	 * @return string The name of the current Zone
	 */
	function getName() {
		return $this->zonename;
	}

	/**
	 * setZoneParams
	 * alias for setZoneParamsNames
	 *
	 * @param mixed $inParamNames
	 * @access public
	 * @return bool
	 */
	function setZoneParams($inParamNames) {
		return $this->setZoneParamsNames($inParamNames);
	}

	/**
	 * setZoneParamsNames
	 *
	 * @param array $inParamNames
	 * @access public
	 * @return void
	 */
	function setZoneParamsNames($inParamNames) {
		return $this->zoneParamNames = $inParamNames;
	}

	/**
	 * getZoneParamNames
	 *
	 * @access public
	 * @return void
	 */
	function getZoneParamNames() {
		return $this->zoneParamNames;
	}

	/**
	 * getZoneParams
	 *
	 * @access public
	 * @return void
	 */
	function getZoneParams() {
		return $this->_zoneParams;
	}

	/**
	 * getZoneParam
	 *
	 * @param mixed $inName
	 * @access public
	 * @return void
	 */
	function getZoneParam($inName) {
		if (isset($this->_zoneParams[$inName])) {
			return $this->_zoneParams[$inName];
		}
	}

	/**
	 * getPageParams
	 *
	 * @access public
	 * @return void
	 */
	function getPageParams() {
		return $this->pageVars;
	}

	/**
	 * getPageParam
	 *
	 * @param mixed $inName
	 * @access public
	 * @return void
	 */
	function getPageParam($inName) {
		if (isset($this->pageVars[$inName])) {
			return $this->pageVars[$inName];
		}
	}

	/**
	 * Return an array of all possible parent(s) for this zone recursively.
	 * This method will loop through all allowed parents recursively
	 * Returning all allowable parents zone object names.
	 *
	 * @access public
	 * @return array $parent_zones zone object names
	 */
	function getMyParents() {
		/**
		 * parent_zones
		 *
		 * @static
		 * @var mixed
		 * @access public
		 */
		static $parent_zones;

		if ($parent_zones) {
			return ($parent_zones);
		} else {
			$parent_zones = array();
		}

		if (!$this->allowedParents) {
			return array();
		}

		foreach ($this->allowedParents as $zone) {
			$parent_zone = 'zone_' . $zone;
			$x = new $parent_zone;
			$parent_zones = array_merge($parent_zones, $x->getMyParents());
			$parent_zones[] = $parent_zone;
		}

		return $parent_zones;
	}

	/**
	 * Returns the names of all the zones who are ancestors of this zone.
	 *
	 * @access public
	 * @return array the names of the zones that are ancestors of this zone (parent, grandparent,...)
	 */
	function getAncestors() {
		if (isset($this->parent) && !empty($this->parent)) {
			return $this->ancestors = array_merge((array)$this->parent->getName(), $this->parent->getAncestors());
		}
		else {
			return array();
		}
	}

	/**
	 * Check if ancestor of current zone
	 *
	 * @param string $str zone name
	 * @access public
	 * @return bool True if zone passed in is an ancestor of current zone
	 */
	function isAncestor($str) {
		$strs = (array)$str;
		$ancestors = $this->getAncestors();
		foreach ($strs as $str) {
			if (in_array($str, $ancestors)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Helper method, wraps isAncestor, accepts multiple input
	 *
	 * @see isAncestor
	 * @param array $strs
	 * @access public
	 * @return bool True if any passed are ancestors
	 */
	function areAncestors($strs) {
		$strs = (array)$strs;
		foreach ( $strs as $str ) {
			if (!$this->isAncestor($str)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * initParents
	 *
	 * @access public
	 * @return void
	 */
	function initParents() { }

	/**
	 * Returns a complete url to this zone, not a path
	 *
	 *
	 * @param int $depth
	 * @access public
	 * @return string A complete url to this zone, not a path
	 */
	function getZoneUrl($depth = 0) {
		return SCRIPT_URL . zone::getZonePath($depth);
	}

	/**
	 * Returns an app path to the rendered zone (depth = 0)
	 * Pass in the $depth paramenter to get all previous (parent) zones
	 * Returns app path for each of the parent zones (depth 1 .. x)
	 * This method is globally executed, so will have the same result if run in $this-> or $this->parent
	 * use this function from now on, until we fix the function above
	 *
	 * @param int $depth
	 * @access public
	 * @return string
	 */
	function getZonePath($depth = 0) {
		global $gZoneUrls;
		return $gZoneUrls[$depth];
	}

	/**
	 * Return the current zone base path (without zone params).
	 *
	 * @access public
	 * @return string
	 */
	static function getZoneBasePath() {
		global $current_zone;
		return zone::getZonePath(1) . '/' . $current_zone;
	}

	/**
	 * zoneRedirect
	 * Redirect to the page and url in the current zone
	 *
	 * @param string $inUrl
	 * @param mixed $redirectType
	 * @access public
	 * @return void
	 */
	function zoneRedirect( $inUrl = '', $redirectType = HEADER_REDIRECT) {
		if (empty($inUrl)) {
			$url = $this->url;
		} else {
			$url = $this->url . "/" . $inUrl;
		}
		BaseRedirect( $url, $redirectType);
	}

	/**
	 * hideNext
	 *
	 * @access public
	 * @return void
	 */
	function hideNext() {
		global $gui;
		$gui->showNext = 0;
	}

	/**
	 * guiAssign
	 *
	 * @param mixed $templateVarName
	 * @param mixed $varValue
	 * @access public
	 * @return void
	 */
	function guiAssign($templateVarName, $varValue) {
		global $gui;
		$gui->assign($templateVarName, $varValue);
	}

	/**
	 * Display the given (or default) template.
	 *
	 * @see gui::display()
	 * @param string $template
	 * @access public
	 * @return void
	 */
	function guiDisplay($template = null) {
		global $gui;
		if (empty($template)) $template = $this->getTemplate();
		
		$gui->display($template);
	}

	/**
	 * Generate a page with the given (or default) template.
	 *
	 * @see gui::generate()
	 * @param string $template
	 * @access public
	 * @return void
	 */
	function guiGenerate($template = null) {
		global $gui;
		if (empty($template)) $template = $this->getTemplate();
		
		$gui->generate($template);
	}

	/**
	 * guiCaching
	 * enable caching of file to display and set lifetime.
	 *
	 * @param int $ttl
	 * @access public
	 * @return void
	 */
	function guiCaching($ttl = null) {
		global $gui;
		if (!defined("gui_caching") || gui_caching == 0)
			return;
		else
		{
			$gui->caching = gui_caching;
			if (is_null($ttl) && defined(gui_cache_lifetime))
				$gui->cache_lifetime = gui_cache_lifetime;
			else
				$gui->cache_lifetime = $ttl;
		}
	}

	/**
	 * guiIsCached
	 *
	 * @param mixed $tplFile
	 * @param mixed $cache_id
	 * @access public
	 * @return bool
	 */
	function guiIsCached($tplFile, $cache_id) {
		global $gui;
		return $gui->is_cached($tplFile, $cache_id);
	}

	/**
	 * Called when a required zone parameter is missing.
	 * To be overloaded to change the default functionality.
	 *
	 * @access public
	 * @return void
	 */
	function missingParameter() { }

	/**
	 * Sets up an instance of zcache in $this->zcache.
	 *
	 * @access public
	 * @return void
	 */
	function initZoneCache() {
		$dirName = substr ( strstr ( get_class ( $this ), '_' ), 1 );
		$this->cacheBase = "zones/$dirName/";
		$this->zcache = new zcache(array('base'=> $this->cacheBase));
	}

	/**
	 * Provide a path to a template based on a zoneName (and location)
	 *
	 * @param mixed $tplName
	 * @access public
	 * @return string
	 */
	function canonicalizeTemplate ($tplName) {
		if (substr($tplName, 0, 1) == "/") {
			return substr($tplName, 1);
		}

		if (!isset($this->templateBase)) {
			$class = strstr(get_class($this), '_') ; // Class with zone_ stripped off.
			$dir = str_replace('_', '/', $class);
			$this->templateBase = "zones/" . substr($dir, 1);
		}
		return $this->templateBase . '/' . $tplName;
	}
	
	/**
	 * Set the template file which will be used to render the requested page.
	 *
	 * NOTE: this currently only applies to ajax and html request types.
	 * 
	 * @param string $template 
	 * @access protected
	 * @return void
	 */
	protected function setTemplate($template) {
		$this->_template = $template;
	}

	/**
	 * Return the template file which will be used to render the requested page.
	 * 
	 * @see Zone::setTemplate
	 * @access protected
	 * @return string Template file
	 */
	protected function getTemplate() {
		return $this->_template;
	}


	/**
	 * Return the base path of the current zone
	 *
	 * @access public
	 * @return string path
	 */
	function makeBasePath() {
		$zone = $this->_getRawZonePath();
		return makePath($zone);
	}

	/**
	 * Given zone, page and $page params return a path
	 *
	 * @param mixed $z zone params
	 * @param mixed $page page name
	 * @param mixed $p page params
	 * @access public
	 * @return string path
	 */
	function makePath($z = array(), $page = '', $p = array() ) {
		$zone = $this->_getRawZonePath();
		$this->_verifyRequiredParams($z);
		return makePath($zone, $z, $page, $p);
	}

	/**
	 * Special convenience function to return the path to the Index Page for this zone
	 *
	 * @access public
	 * @return void
	 */
	function makeIndexPath() {
		$zone = $this->_getRawZonePath();
		return makePath($zone);
	}

	/**
	 * A convenience method for returning the current base zone path
	 * Doesn't include zone Parameters
	 *
	 * @access private
	 * @return string path
	 */
	function _getRawZonePath() {
		$zone = substr ( strstr ( get_class ( $this ), '_' ), 1 );
		$zone = str_replace( '_', '/', $zone );
		return $zone;
	}

	/**
	 * Given zone, page and $page params return a url
	 *
	 * @param mixed $z
	 * @param mixed $page
	 * @param mixed $p
	 * @access public
	 * @return string url
	 */
	function makeUrl($z, $page, $p) {
		$zone = substr ( strstr ( get_class ( $this ), '_' ), 1 );
		$this->_verifyRequiredParams($z);
		return SCRIPT_URL . makePath($zone, $z, $page, $p);
	}

	/**
	 * Ensure that required params are passed when using makepath
	 *
	 * @param mixed $z
	 * @access private
	 * @return void
	 */
	private function _verifyRequiredParams($z) {
		$zone = substr ( strstr ( get_class ( $this ), '_' ), 1 );
		$params = $this->getZoneParamNames();
		foreach($params as $name) {
			if(!isset($z[$name]))
				trigger_error("missing param $name for makepath of zone $zone");
		}
	}

	/**
	 * Set Zone level path aliases. These aliases have way more magic than the old aliases.
	 *
	 * Aliases can be static (like 'create' -> 'new/update') or contain variables
	 * ('%id%/edit' -> '%id%/update'). Variables are denoted by wrapping the variable name
	 * in '%'. A fairly common name is '%id%'. Variables can be named just about anything, as long
	 * as the name contains only letters and numbers. Variable position and order need not
	 * stay the same. For example, '%first%/foo/%second%' might alias to '%second%/bar/%first%'.
	 *
	 * Aliases must be added in the zone constructor so that they are available early enough
	 * to do some good.
	 *
	 * @code
	 * // zone constructor
	 * function zone_myZone() {
	 *   $this->addAlias('%id%/edit', '%id%/update');
	 *   $this->addAlias('create', 'new/update');
	 *   $this->addAlias('%first%/swap/%second%', '%second%/swapped/%first%');
	 * }
	 * @endcode
	 *
	 * Zone::addAlias() and Zone::checkAlias() can be overloaded by extending classes to provide
	 * even more awesome. One might use database lookup driven aliases, for cool paths like
	 * 'blog/2008/10/my-post-title' ...
	 *
	 * @see Zone::checkAlias
	 * @param string $alias_from The URL in the address bar of the browser.
	 * @param string $alias_to The zoop zone/page/param path (or path chunk) which will be called.
	 * @access public
	 **/
	function addAlias($from_alias, $to_alias) {
		$matches = array();
		preg_match_all('#%[a-zA-Z0-9]*%#', $from_alias, $matches);

		$from_re = $redirect_to = $from_alias;
		$to_re = $redirect_from = $to_alias;
		$callback = 0;

		// build a regex. this could get ugly.
		if (count($matches)) {
			foreach($matches[0] as $match) {
				if (strpos($to_re, $match) !== false) {
					$callback++;

					// aliases
					$from_re = str_replace($match, '([^/]+)', $from_re);
					$to_re = str_replace($match, "\\" . $callback, $to_re);
				}
			}

			if (Config::get('zoop.zone.aliases.global_redirect')) {
				foreach($matches[0] as $match) {
					if (strpos($redirect_to, $match) !== false) {
						// backwards regexes for redirects
						$redirect_to = str_replace($match, "\\" . $callback, $redirect_to);
						$redirect_from = str_replace($match, '([^/]+)', $redirect_from);
						$callback--;
					}
				}
			}
		}

		$from_re = '#^' . $from_re . '#';
		if (isset($this->_pathAliases[$from_re])) {
			trigger_error("An alias for `$from_alias` is already set, unable to add another alias.");
			return false;
		}
		$this->_pathAliases[$from_re] = $to_re;

		if (Config::get('zoop.zone.aliases.global_redirect')) {
			$redirect_from = '#^' . $redirect_from . '#';
			$this->_redirectAliases[$redirect_from] = $redirect_to;
		}

		return true;
	}

	/**
	 * Add a bunch of aliases at once.
	 *
	 * @code
	 *   $this->addAliases(array(
	 *     '%id%/edit' => '%id%/update',
	 *     'create' => 'new/update',
	 *     '%first%/swap/%second%' => '%second%/swapped/%first%',
	 *   ));
	 * @endcode
	 *
	 * @see Zone::addAlias
	 * @param array $aliases
	 * @access public
	 **/
	function addAliases($aliases) {
		foreach ($aliases as $from => $to) {
			$this->addAlias($from, $to);
		}
	}

	/**
	 * Check the current path against zone alias rules, converting if necessary.
	 *
	 * If no $path parameter is passed, this function will check the zone's $inPath chunk against
	 * the alias rules. If a match is found, it will set the $inPath to the new, aliasified
	 * path, and return true.
	 *
	 * If a $path parameter is specified, checkAlias() will check it against this zone's alias
	 * rules, converting it if necessary, and returning it in the form in which it was received
	 * (array or string).
	 *
	 * If global redirects are enabled (zoop.zone.aliases.global_redirect) and the checked path
	 * has a canonical alias, this function will do an external redirect to the canonical url.
	 * This will change the url in the user's browser to the correct (canonical) url. This will
	 * aid in search engine optimization efforts by removing 'duplicate content', or pages
	 * accessible from multiple urls.
	 *
	 * CAVEAT: checkAlias will currently only resolve one level of aliases. For example, assume
	 * `x` is aliased to `y`, and `y` is aliased to `z`. If a user visits `x`, he will be shown
	 * content for url `y` (or lack of content). Consider the action of checkAlias to be undefined
	 * in this instance, and program accordingly. This will probably change at a later date. To avoid
	 * problems, please choose your aliases wisely. For example, alias `x` to `z` and `y` to `z`.
	 *
	 * @see Zone::addAlias
	 * @param mixed $path An (optional) array or string path to check against this zone's aliases.
	 * @access public
	 * @return mixed A new, easier to use, thunked path, as an array or string.
	 **/
	function checkAlias($path = null) {
		$path_alias = $path;

		if ($path_alias === null) {
			$path_alias = $this->_inPath;
		}

		if (is_array($path_alias)) $path_alias = implode('/', $path_alias);

		// External redirect to the canonical alias, if applicable.
		if (Config::get('zoop.zone.aliases.global_redirect')) {
			foreach($this->_redirectAliases as $redirect_from => $redirect_to) {
				if (preg_match($redirect_from, $path_alias)) {
					$this->zoneRedirect(preg_replace($redirect_from, $redirect_to, $path_alias));
				}
			}
		}

		$success = false;
		foreach($this->_pathAliases as $from_re => $to_re) {
			$path_alias = preg_replace($from_re, $to_re, $path_alias, -1, $success);
			if ($success) continue;
		}

		// return the resulting path in whatever form it came to us.
		if ($path === null) {
			$this->_inPath = explode('/', $path_alias);
			return true;
		} else if (is_array($path)) {
			return explode('/', $path_alias);
		} else {
			return $path_alias;
		}
	}

	/**
	 * The default method for outputting (echoing) partial HTML content, such as for Ajax
	 *
	 * @access public
	 * @return void
	 */
	function outputAJAX() {
		header("Content-Type: text/html");
		$this->guiDisplay();
	}

	/**
	 * The default method for rendering HTML.
	 *
	 * @access public
	 * @return void
	 */
	function outputHTML() {
		global $gui;
		$this->guiGenerate();
	}

	/**
	 * The default method for rendering PDF.
	 *
	 * @access public
	 * @return void
	 */
	function outputPDF() {

	}

	/**
	 * The default method for rendering PDF.
	 *
	 * @access public
	 * @return void
	 */
	function outputPNG() {

	}

	/**
	 * The default method for outputing (echoing) JSON data.
	 *
	 * @access public
	 * @return void
	 */
	function outputJSON() {
		header("Content-Type: application/text-json");
		$data = convert::toJSON($this->getData());
		echo $data;
	}

	/**
	 * The default method for outputing (echoing) CSV data.
	 *
	 * @access public
	 * @return void
	 */
	function outputCSV() {
		header("Content-Type: application/text-csv");
		$data = convert::toCSV($this->getData());
		echo $data;
	}

	/**
	 * The default method for outputing (echoing) XLS data.
	 *
	 * @access public
	 * @return void
	 */
	function outputXLS() {
		header("Content-Type: application/vnd.ms-excel");
		$data = convert::toXLS($this->getData());
		echo $data;
	}

	/**
	 * The default method for outputing (echoing) YAML data.
	 *
	 * @access public
	 * @return void
	 */
	function outputYAML() {
		header("Content-Type: application/text-yaml");
		$data = convert::toYAML($this->getData());
		echo $data;
	}

	/**
	 * The default method for outputing (echoing) XML data.
	 *
	 * @access public
	 * @return void
	 */
	function outputXML() {
		header('Content-Type: text/xml'); 
		$options = array('rootName' => $this->getName(), 'defaultTagName' => $this->form->tablename );
		$data = convert::toXML($this->getData(), $options);
		echo $data;
	}

	/**
	 * The default method for outputing (echoing) Serialized (PHP) data.
	 *
	 * @access public
	 * @return void
	 */
	function outputSerialized() {
		header("Content-Type: application/text");
		$data = convert::toSerialized($this->getData());
		echo $data;
	}

}
