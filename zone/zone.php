<?
/**
* @package zone
*/

// Copyright (c) 2005 Supernerd LLC and Contributors.
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
* zone class.
*
* <p>Extend this class any time you need
* a new section on your site.	 File names
* should be "zone_{zonename}.php" and
* should be in the same directory as index.php</p>

* <p>This class is instantiated and the
* handlerequest function called from the
* index.php file.	 It will check path
* and if a class member function of each
* name exists (/path = path) information
* and will automatically execute</p>
*
* rmb 7-20-2001
* @package zone
*/

	/**
	 * zone 
	 * 
	 * @package 
	 * @version $id$
	 * @copyright 1997-2006 Supernerd LLC
	 * @author Richard Bateman
	 * @author John Lesueur
	 * @author Steve Francia <webmaster@supernerd.com> 
	 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
	 */
	class zone
	{
        //////////////////////////////////////////////////////////////
        //  These are variables each instance uses to track itself  //
        //////////////////////////////////////////////////////////////

		/**
		 * error 
		 * 
		 * @var string
		 * @access public
		 */
		var $error = "";		// The last error message recorded.
		/**
		 * errornum 
		 * 
		 * @var float
		 * @access public
		 */
		var $errornum = 0;

		/**
		 * zonename 
		 * 
		 * @var string
		 * @access public
		 */
		var $zonename = "";		/* This will contain the pathname of this zone.
								 * It will also contain the variable if urlvars
								 * is enabled for this class.
								 */

		/**
		 * zonetype 
		 * 
		 * @var string
		 * @access public
		 */
		var $zonetype = "";		// this will always contain the name of this zone

		/**
		 * urlvar 
		 * 
		 * @var array
		 * @access public
		 */
		var $urlvar = array();		// this is a legacy variable that should be left alone or set to false
		/**
		 * urlzone 
		 * 
		 * @var array
		 * @access public
		 */
		var $urlzone = array();		// this is a legacy variable that should be left alone

		/**
		 * parent 
		 * 
		 * @var mixed
		 * @access public
		 */
		var $parent = null;		// Reference to the parent of this zone

		/**
		 * _zone 
		 * 
		 * @var array
		 * @access protected
		 */
		var $_zone = array();	// array of subclasses for this zone

		/**
		 * allowed_children 
		 * 
		 * @var array
		 * @access public
		 */
		var $allowed_children = array();	// These are the zone names valid in this zone   -- DON'T INCLUDE THE 'zone_' PART
		/**
		 * allowed_parents 
		 * 
		 * @var array
		 * @access public
		 */
		var $allowed_parents = array();	// These are the zones this zone can be a child of -- DON'T INCLUDE THE 'zone_' PART

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


		/* ADDED BY SPF - MAY 05 -
								   * extra security, require by default the post must be recieved by
								   * the page of the same name, this is for exceptions
								   */
		/* replaced with a open by default, restrict as requested
			rjl 8/25/2005 */
		//var $allowed_remote_post = array();
		/**
		 * restricted_remote_post 
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
		* @access public
		**/

        	/**
        	 * urlvars 
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

		/*
		* Set $this->Alias["alias"] = "Aliastarget"
		*/
		var $Alias = array();

		function Zone()		// Constructor
		{
			// nothing to put here at the moment
		}


		/**
		 * handleRequest 
		 * 
		 * @param mixed $inPath 
		 * @access public
		 * @return void
		 */
		function handleRequest( $inPath )
		{
			global $gAlias;
			global $gUrlVars;
			global $gPathParts;
			global $gZoneUrls;

			$this->zonename = array_shift($inPath); // $inPath[0] IS NULL

			$GLOBALS['current_zone'] = $this->zonename; // SET TO NULL

			if (isset($gUrlVars['userType']))
				$GLOBALS['current_usertype'] = $gUrlVars['userType'];

			$gPathParts[] = $this->zonename;


			if (!$this->zonename)  // SINCE THIS IS NULL SET ZONENAME TO @ROOT
			{
				$this->zonename = "@ROOT";
			}

			if (!$this->zonetype)
			{
			    $this->zonetype = ($this->zonename != "@ROOT") ? $this->zonename : "Default";  // SET $this->zonetype TO THE ZONENAME OR TO DEFAULT IF $this->zonename == @ROOT
			}

			$this->urlzone = array();
			$this->urlzone[] = $this->zonename;

			// when wildcards are enabled, always execute the default function.
			if ($this->wildcards)
			{
				array_unshift($inPath, 'default');
				return( $this->_checkFuncs("Default", $inPath) );
			}

			// CHECK THE ZONE TO SEE IF ANY VARIABLES ARE IN THE PATH.
			if($urlVarNames = $this->getZoneParamNames())
			{
				foreach ($urlVarNames as $index => $varName)
				{
					if( count($inPath) > 0 )
					{
						$varValue = array_shift( $inPath );
						if(defined("strip_url_vars") && strip_url_vars)
							assert(strtok($varValue, " \t\r\n\0\x0B") === $varValue);
						$gUrlVars[ $varName ] = $varValue;
						$gPathParts[] = $varValue;
					}
					else
					{
						break;
					}
				}
			}

			// CHECK FOR SEQUENCES

			$tmp = $gPathParts;
			global $sequenceStack;

			if(isset($sequenceStack))
			{
				$temp = array_shift($tmp);
				array_unshift($tmp, implode(":", $sequenceStack));
				array_unshift($tmp, $temp);
			}
			$this->url = implode("/", $tmp);
			$this->initZone($inPath);

			array_unshift($gZoneUrls, $this->url);


			// THE SECOND STEP IS TO SEE IF THERE IS ANOTHER ZONE TO RUN.

			//	if there is something at all in the path

			if ( isset($inPath[0]) && $inPath[0] !== '' )
			{
				$path2 = $inPath[0]; //SET $path2 TO THE NEXT ZONENAME
			}
			else
			{
				$path2 = "Default";
			}

			if ( isset( $this->Alias[$path2]) && !(($retval = $this->_checkFuncs($this->Alias[$path2], $inPath)) === false) )
			{

				return $retval;
			}

			elseif ( !($retval = $this->_checkFuncs($path2, $inPath) === false) )
			{

				return $retval;

			}

			elseif ( isset( $gAlias[$path2]) && !(($retval = $this->_checkFuncs($gAlias[$path2], $inPath )) === false) )
			{
				return $retval;
			}

			else
			{
				// Try to execute the correct funtion
				if ( isset( $this->Alias[$path2]) && !(($retval = $this->_checkZone($this->Alias[$path2], $inPath)) === false) )
				{
					return( $retval );
				}

				else if ( !(($retval = $this->_checkZone($path2, $inPath)) === false) )
				{
					return( $retval );
				}

				else if ( isset( $gAlias[$path2]) && !(($retval = $this->_checkZone($gAlias[$path2], $inPath)) === false) )
				{
					return( $retval );
				}

				else
				{
					$this->error = "The name found in the path ($path2) was not a valid function or class.  Perhaps this class should have wildcards enabled?  Executing pageDefault function.";

					if (REQUEST_TYPE == "XMLRPC")
					{
						$GLOBALS["zoopXMLRPCServer"]->returnFault(1, "Invalid XMLRPC function, $path2");
						return true;
					}
					
					array_unshift($inPath, 'default');
					return( $this->_checkFuncs("Default", $inPath) );
				}
			}
		}

		/**
		 * _checkFuncs 
		 * 
		 * @param mixed $curPath 
		 * @param mixed $inPath 
		 * @access protected
		 * @return void
		 */
		function _checkFuncs($curPath, $inPath)
		{
			if (REQUEST_TYPE == "XMLRPC")
			{
				return $this->_xmlrpcDispatch($curPath, $inPath);
			}
			else
			{

				if ( $_SERVER["REQUEST_METHOD"] == "POST" && $this->_checkAllowedPost($curPath))
			    {


					if (method_exists($this, "post" . $curPath))
					{
						$funcName = "post" . $curPath;
						$GLOBALS['current_function'] = $funcName;
						$this->initPages($inPath);
						$tmp = $this->$funcName($inPath);
						$this->closePages($inPath);
						$this->closePosts($inPath);
						return $tmp;
					}
					else if(method_exists($this, "page" . $curPath))
					{
						//$funcName = "page" . $curPAth;
						$this->initPages($inPath);
						//$this->$funcName($inPath);
						$this->closePages($inPath);
						$this->closePosts($inPath);
						redirect($_SERVER["REQUEST_URI"]);
					}
				}
				if (method_exists($this, "page" . $curPath))
				{
	   				$funcName = "page" . $curPath;
					$GLOBALS['current_function'] = $funcName;

					$this->initPages($inPath);
					$tmp = $this->$funcName($inPath);

					$this->closePages($inPath);
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
		function _checkAllowedPost($curPath)
		{
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
		 * _checkZone 
		 * 
		 * @param mixed $zoneName 
		 * @param mixed $inPath 
		 * @access protected
		 * @return void
		 */
		function _checkZone($zoneName, $inPath)
		{
			$var2 = "zone_" . $zoneName;

// 			$this->include_zone($var2);

			//	if the class exists and this zone has no allowed children or this is one of the allowed children
			//		not the easiest thing to follow but I guess it needs to be done this way since the object
			//		hasn't been instantiated yet.  Maybe a static method would be better here for getting the allowed
			//		parents

			if ( 	class_exists($var2) && (
						count($this->allowed_children) < 1
						|| in_array($zoneName, $this->allowed_children) ))
			{
				//	create the new zone object if it does not exist

				if ( !isset( $this->_zone[$zoneName] ) )
				{
					$this->_zone[$zoneName] = new $var2();
					$this->_zone[$zoneName]->parent =& $this;
				}

				// check to see if this is an allowed parent for the class we just created

				if (	count($this->_zone[$zoneName]->allowed_parents) > 0
						&& !in_array($this->zonetype, $this->_zone[$zoneName]->allowed_parents))
				{
					return false;
				}
				$retval = $this->_zone[$zoneName]->handleRequest($inPath);
				if ($retval === false)
				{
				    $retval = "";
				}

				$this->closeZone($inPath);

				return( $retval );
			}
			else
			{
				return false;
			}

		}

		/**
		 * _xmlrpcDispatch 
		 * 
		 * @param mixed $curPath 
		 * @param mixed $inPath 
		 * @access protected
		 * @return void
		 */
		function _xmlrpcDispatch($curPath, $inPath)
		{
			if (method_exists($this, "xmlrpc" . $curPath))
			{
				$funcname = "xmlrpc" . $curPath;

				$params = $GLOBALS["zoopXMLRPCServer"]->getRequestVars();

				$methodname = $GLOBALS["zoopXMLRPCServer"]->methodname;

				ob_start();
				$retval = $this->$funcname($inPath, $params, $methodname);
				$debug = ob_get_contents();
				ob_end_clean();

				if (is_object($retval) && isset($retval->code))
				{
					$GLOBALS["zoopXMLRPCServer"]->returnFault($retval->code, $retval->string);
				}
				elseif (is_array($retval))
				{
					$GLOBALS["zoopXMLRPCServer"]->returnValues($retval);
				}
				elseif ($retval === true || $retval === false)
				{
					$val["value"] = $retval ? 1 : 0;
					$val["type"] = "boolean";
					$GLOBALS["zoopXMLRPCServer"]->returnValues($val);
				}
				elseif (is_numeric($retval) || strlen($retval) > 0)
				{
					$GLOBALS["zoopXMLRPCServer"]->returnValues($retval);
				}
				else
				{
					$GLOBALS["zoopXMLRPCServer"]->returnFault(2, "Function did not return a valid array");
				}
				return true;
			}
			else
			{
				return false;
			}
		}

		/**
		* If you don't store the zone in the session (defined in config.php),
		* Then the zone can be included dynamically.
		* This is useful if many requests would only use one or two zones.
		* If you use this do not include zones in includes.php other than default.
		* @since Version 1.2
		* @param string $name name of zone
		*/
// 		function include_zone($name)
// 		{
// 			if(!isset($this->included['zones'][$name]))
// 			{
// 				if (file_exists(app_dir . "/" . $name . ".php"))
// 					include(app_dir . "/" . $name . ".php");
// 				$this->included['zones'][$name] = 1;
// 			}
// 		}

		/**
		* Useful if one is not storing the zones in the session (defined in config.php),
		* for storing zone variables in the session without the overhead of the entire session.
		* also used for retriving that variable from the session.
		* @since Version 1.2
		* @param string $name name of variable
		* @param mixed $value value to store in variable
		* @return mixed
		*/
		function session($name, $value = NULL)
		{
			global $sGlobals;
			if($value === NULL)
			{
					return $sGlobals->zones[$this->zonename][$name];
			}
			else
			{
					$sGlobals->zones[$this->zonename][$name] = $value;
			}
		}

		/**
		 * pageDefault 
		 * 
		 * @param mixed $inPath 
		 * @access public
		 * @return void
		 */
		function pageDefault($inPath)
		{
			// This function should be overridden to be the default function (in case there is either A: no path info, or B: no matching function or class for the path";

			die("You haven't overridden pagedefault!");
		}

		/**
		 * initZone 
		 * 
		 * @param mixed $inPath 
		 * @access public
		 * @return void
		 */
		function initZone($inPath)
		{
			// This function should be overridden in each zone if you would like code to execute each time it hits the zone's handleRequest function.
		}

		/**
		 * closeZone 
		 * 
		 * @param mixed $inPath 
		 * @access public
		 * @return void
		 */
		function closeZone($inPath)
		{
			// This function should be overridden in each zone if you would like code to execute each time before it leaves the zone's handleRequest function.
		}

		/**
		 * initPages 
		 * 
		 * @param mixed $inPath 
		 * @access public
		 * @return void
		 */
		function initPages($inPath)
		{
			// This function is run before any page or post function in the zone.
		}

		/**
		 * closePages 
		 * 
		 * @param mixed $inPath 
		 * @access public
		 * @return void
		 */
		function closePages($inPath)
		{
			// This function is run after any page or post function in the zone.
		}

		/**
		 * closePosts 
		 * 
		 * @param mixed $inPath 
		 * @access public
		 * @return void
		 */
		function closePosts($inPath)
		{
			// This function is run after any page or post function in the zone.
		}

		/**
		 * getZoneParamNames 
		 * 
		 * @access public
		 * @return void
		 */
		function getZoneParamNames()
		{
			return $this->zoneParamNames;
		}

		//deprecated...
		/**
		 * getUrlVarNames 
		 * 
		 * @access public
		 * @return void
		 */
		function getUrlVarNames()
		{
			bug("called deprecated function: getUrlVarNames(), use getZoneParamNames()");
			return $this->zoneParamNames;
		}

		/**
		 * setZoneParams 
		 * 
		 * @param mixed $inParamNames 
		 * @access public
		 * @return void
		 */
		function setZoneParams($inParamNames)
		{
			return $this->zoneParamNames = $inParamNames;
		}

		//deprecated
		/**
		 * setUrlVarNames 
		 * 
		 * @param mixed $inUrlVarNames 
		 * @access public
		 * @return void
		 */
		function setUrlVarNames($inUrlVarNames)
		{
			bug("called deprecated function: setUrlVarNames(), use setZoneParamNames()");
			return $this->zoneParamNames = $inUrlVarNames;
		}

		/**
		 * getZoneParams 
		 * 
		 * @access public
		 * @return void
		 */
		function getZoneParams()
		{
			global $gUrlVars;
			return $gUrlVars;
		}

		//deprecated
		/**
		 * getUrlVars 
		 * 
		 * @access public
		 * @return void
		 */
		function getUrlVars()
		{
			bug("called deprecated function: getUrlVars(), use getZoneParams()");
			global $gUrlVars;
			return $gUrlVars;
		}

		/**
		 * Return an array of parent(s) for this zone.
		 */
		/**
		 * getMyParents 
		 * 
		 * @access public
		 * @return void
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
			if ($parent_zones) return ($parent_zones);
			foreach ($this->allowed_parents as $zone) {
				$parent_zone = 'zone_' . $zone;
				$x = new $parent_zone;
				$parent_zones = array_merge($parent_zones, $x->getMyParents());
				$parent_zones[] = $parent_zone;
			}

			return $parent_zones;
		}

		/**
		 * initParents 
		 * 
		 * @access public
		 * @return void
		 */
		function initParents() {

		}

		//should return an url to this zone
		/**
		 * getZoneUrl 
		 * 
		 * @param int $depth 
		 * @access public
		 * @return void
		 */
		function getZoneUrl($depth = 0)//this function should return a complete url, not a path
		{
			global $gZoneUrls;
			return SCRIPT_URL . $gZoneUrls[$depth];
		}

		//should return an app path to this zone
		/**
		 * getZonePath 
		 * 
		 * @param int $depth 
		 * @access public
		 * @return void
		 */
		function getZonePath($depth = 0)//use this function from now on, until we fix the function above
		{
			global $gZoneUrls;
			return $gZoneUrls[$depth];
		}

		//should redirect us in the zone to the page $inUrl
		/**
		 * zoneRedirect 
		 * 
		 * @param string $inUrl 
		 * @param mixed $redirectType 
		 * @access public
		 * @return void
		 */
		function zoneRedirect( $inUrl = '', $redirectType = HEADER_REDIRECT)
		{
			BaseRedirect( $this->url . "/" . $inUrl, $redirectType);
		}

		/**
		 * hideNext 
		 * 
		 * @access public
		 * @return void
		 */
		function hideNext()
		{
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
		function guiAssign($templateVarName, $varValue)
		{
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
		function guiDisplay($inTemplateName)
		{
			global $gui;

			//	get the name of the directory that the class should be in
			//	this logic could maybe be put in the contructor and reused???
			$className = get_class($this);
			$parts = explode('_', $className);
			array_shift($parts);
			$dirName = implode('_', $parts);

			$gui->display($dirName . '/'. $inTemplateName);
		}
	}
?>
