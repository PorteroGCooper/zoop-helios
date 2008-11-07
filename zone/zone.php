<?php
/**
 * @package zone
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

$GLOBALS['gUrlVars'] = array();
$GLOBALS['gPathParts'] = array();
$GLOBALS['gZoneUrls'] = array();

/**
 * zone
 * Extend this class any time you need
 * a new section on your site.	 File names
 * should be "zone_{zonename}.php" and
 * should be in the same directory as index.php
 *
 * This class is instantiated and the
 * handlerequest function called from the
 * index.php file.	 It will check path
 * and if a class member function of each
 * name exists (/path = path) information
 * and will automatically execute
 *
 * rmb 7-20-2001
 * @package zone
 *
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Richard Bateman
 * @author John Lesueur
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @author Andrew Hayward <andrew@gratuitousPawn.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class zone
{
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
	 * allowed_children
	 * These are the zone names valid in this zone   -- DON'T INCLUDE THE 'zone_' PART
	 *
	 * @var array
	 * @access public
	 */
	var $allowed_children = array();

	/**
	 * allowed_parents
	 * These are the zones this zone can be a child of -- DON'T INCLUDE THE 'zone_' PART
	 *
	 * @var array
	 * @access public
	 */
	var $allowed_parents = array();	

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
	 * checkPathForSequences 
	 * 
	 * @access public
	 * @return void
	 */
	function checkPathForSequences () {
		global $gPathParts;//an array of all the parts of the path so far
		global $gZoneUrls;
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
		$gui->assign('ZONE_PATH', $this->getZonePath());
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

					if (REQUEST_TYPE == "XMLRPC") {
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
	function setPageVars() {
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
	function _urlStringToArray($inString) {
		$tmp = explode(":", $inString);

		if (count($tmp) == 1) {
			$new_array[] = $inString;
		} elseif (count($tmp)  == 2 ) {
			$new_array[$tmp[0]] = $tmp[1];
		} else {
			$new_key = array_shift($tmp);
			$new_array[$new_key] = $tmp;
		}

		return $new_array;
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
	 * * Determine if next token (part of the url) is a page or a zone and execute
	 *
	 * @see zone::addAlias
	 * @see zone::executeNextFunction
	 * @param array $inPath
	 * @access public
	 * @return void
	 */
	function handleRequest( $inPath ) {
		$this->_inPath = $inPath;
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

	/**
	 * For a given url Token, check to see if it matches a page or post function
	 * for this zone
	 *
	 * @param string $curPath
	 * @param array $inPath
	 * @access protected
	 * @return void
	 */
	function _checkFuncs($curPath, $inPath) {
		$this->setPageVars();

		if (REQUEST_TYPE == "XMLRPC") {
			return $this->_xmlrpcDispatch($curPath, $this->_inPath);
		} else {

			if ( $_SERVER["REQUEST_METHOD"] == "POST" && $this->_checkAllowedPost($curPath)) {
				if (method_exists($this, "post" . $curPath)) {
					$funcName = "post" . $curPath;
					$GLOBALS['current_function'] = $funcName;
					global $logpath;
					$logpath[] = "$curPath/post";
					$this->initPages($this->_inPath, $GLOBALS['gUrlVars']);
					$tmp = $this->$funcName($this->_inPath, $GLOBALS['gUrlVars']);
					$this->closePages($this->_inPath, $GLOBALS['gUrlVars']);
					$this->closePosts($this->_inPath, $GLOBALS['gUrlVars']);
					return $tmp;
				} else if(method_exists($this, "page" . $curPath)) {
					// I DON'T KNOW IF THIS IS EVEN USED, DOESN'T APPEAR TO BE
					global $logpath;
					$logpath[] = "$curPath/post";
					//$funcName = "page" . $curPAth;
					$this->initPages($this->_inPath, $GLOBALS['gUrlVars']);
					//$this->$funcName($this->_inPath);
					$this->closePages($this->_inPath, $GLOBALS['gUrlVars']);
					$this->closePosts($this->_inPath, $GLOBALS['gUrlVars']);
					redirect($_SERVER["REQUEST_URI"]);
				}
			} if (method_exists($this, "page" . $curPath)) {
				global $logpath;
				$logpath[] = "$curPath/get";
				$funcName = "page" . $curPath;
				$GLOBALS['current_function'] = $funcName;
				$this->initPages($this->_inPath, $GLOBALS['gUrlVars']);
				$tmp = $this->$funcName($this->_inPath);

				$this->closePages($this->_inPath, $GLOBALS['gUrlVars']);
				return( $tmp );
			}
			return false;
		}
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
			count($this->allowed_children) < 1
			|| in_array($zoneName, $this->allowed_children) )) {
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
		if (isset ( $this->parent)) {
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
		if ( count($this->_zone[$zoneName]->allowed_parents) > 0
			&& !in_array($this->zonetype, $this->_zone[$zoneName]->allowed_parents)) {
				return false;
		} elseif ($this->getName() != "@ROOT") {
			$this->_zone[$zoneName]->allowed_parents = array($this->_getClassNamePath());
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
	function session($name, $value = NULL) {
		global $sGlobals;
		if($value === NULL)
		{
			if(isset($sGlobals->zones) && isset($sGlobals->zones[$this->zonename]) && isset($sGlobals->zones[$this->zonename][$name]))
				return $sGlobals->zones[$this->zonename][$name];
			else
				return NULL;
		}
		else
		{
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
		$this->responsePage404();
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
	 * @since 2.0
	 * @access public
	 * @return void
	 */
	function responsePage404() {
		header ('HTTP/1.1 404 Not Found');
		echo("<h1>404 Page not found</h1>");
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
		return $this->zoneParamNames = $inParamNames;
	}

	/**
	 * setZoneParamsNames
	 *
	 * @param mixed $inParamNames
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
		if ( isset( $this->_zoneParams[$inName] ) )
			return $this->_zoneParams[$inName];
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
		if ( isset( $this->pageVars[$inName] ) )
			return $this->pageVars[$inName];
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

		if (!$this->allowed_parents) {
			return array();
		}

		foreach ($this->allowed_parents as $zone) {
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
		if (isset($this->parent) && !empty($this->parent))
			return $this->ancestors = array_merge((array)$this->parent->getName(), $this->parent->getAncestors());
		else
			return array();
	}

	/**
	 * Check if ancestor of current zone
	 * 
	 * @param string $str zone name 
	 * @access public
	 * @return bool True if zone passed in is an ancestor of current zone
	 */
	function isAncestor($str) {
		$strs = (array) $str;
		$ancestors = $this->getAncestors();
		foreach ( $strs as $str )
			if (in_array($str, $ancestors))
				return true;
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
		$strs = (array) $strs;
		foreach ( $strs as $str )
			if ( !$this->isAncestor($str) )
				return false;
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
		global $gZoneUrls;
		return SCRIPT_URL . $gZoneUrls[$depth];
	}

	/**
	 * Returns an app path to this zone
	 *
	 * @param int $depth
	 * @access public
	 * @return string
	 */
	function getZonePath($depth = 0) { //use this function from now on, until we fix the function above {
		global $gZoneUrls;
		return $gZoneUrls[$depth];
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
		if (empty($inUrl))
			$url = $this->url;
		else
			$url = $this->url . "/" . $inUrl;
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
	 * guiDisplay
	 *
	 * @param mixed $inTemplateName
	 * @access public
	 * @return void
	 */
	function guiDisplay($inTemplateName) {
		global $gui;
		$gui->display( $this->canonicalizeTemplate($inTemplateName) );
	}

	/**
	 * guiCaching
	 * enable caching of file to display and set lifetime.
	 *
	 * @param int $ttl
	 * @access public
	 * @return void
	 */
	function guiCaching($ttl = null)
	{
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
	function canonicalizeTemplate ( $tplName )
	{
		if ( substr ( $tplName, 0, 1 ) == "/" )
			return substr ( $tplName, 1 );

		if ( !isset ( $this->templateBase ) ) {
			$class = strstr ( get_class ( $this ), '_' ) ; // Class with zone_ stripped off.
			$dir = str_replace('_', '/', $class);
			$this->templateBase = "zones/" . substr ($dir, 1 );
		}
		return $this->templateBase . '/' . $tplName;
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
	function makePath($z = '', $page = '', $p = '' ) {
		$zone = $this->_getRawZonePath();
		$this->_verifyRequiredParams($z);
		return makePath($zone, $z, $page, $p);
	}

	/**
	 * A convenience method for returning the current base zone path
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
		foreach($params as $name)
		{
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
	
	
	
	
}
