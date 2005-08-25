<?
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

////////////////////////////////////////////
//	Zone.php
//		Zone class.
//
//	Instantiate this class any time you need
//	a new section on your site.	 File names
//	should be "zone_{zonename}.php" and
//	should be in the same directory as index.php
//
//	This class is instantiated and the
//	handlerequest function called from the
//	index.php file.	 It will check path
//	and if a class member function of each
//	name exists (/path = path) information
//	and will automatically execute 			rmb 7-20-2001
////////////////////////////////////////////

	$GLOBALS['gUrlVars'] = array();
	$GLOBALS['gPathParts'] = array();
	$GLOBALS['gZoneUrls'] = array();

	class Zone
	{
        //////////////////////////////////////////////////////////////
        //  These are variables each instance uses to track itself  //
        //////////////////////////////////////////////////////////////

		var $error = "";		// The last error message recorded.
		var $errornum = 0;

		var $zonename = "";		/* This will contain the pathname of this zone.
								 * It will also contain the variable if urlvars
								 * is enabled for this class.
								 */
		var $zonetype = "";		// this will always contain the name of this zone

		var $urlvar = array();		// this is a legacy variable that should be left alone or set to false
		var $urlzone = array();		// this is a legacy variable that should be left alone

		var $subZone = false;	// true if this instance was created from an URLVAR zone

		var $parent = null;		// Reference to the parent of this zone

		var $_zone = array();	// array of subclasses for this zone

		var $allowed_children = array();	// These are the zone names valid in this zone   -- DON'T INCLUDE THE 'zone_' PART
		var $allowed_parents = array();	// These are the zones this zone can be a child of -- DON'T INCLUDE THE 'zone_' PART

		var $zoneParamNames = array();

		var $returnPaths = array();

		
		/* ADDED BY SPF - MAY 05 -
								   * extra security, require by default the post must be recieved by
								   * the page of the same name, this is for exceptions
								   */
		/* replaced with a open by default, restrict as requested
			rjl 8/25/2005 */
		//var $allowed_remote_post = array();  
		var $restricted_remote_post = array();
								   
		
		var $origins = array();

		var $url = "";
        //////////////////////////////////////////////////////////////
        //  These are settings that will be overridden by subclass  //
        //////////////////////////////////////////////////////////////

		/**
		*
		* @access public
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
		**/
		var $wildcards = false;

		/**
		*
		* @access public
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
		**/

        	var $urlvars = false;
		var $urlzones = false;

		/*
		* Set $this->Alias["alias"] = "Aliastarget"
		*/
		var $Alias = array();

		function Zone()		// Constructor
		{
			// nothing to put here at the moment
		}


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

			if (!$this->zonetype || !$this->subZone)
			{
			    $this->zonetype = ($this->zonename != "@ROOT") ? $this->zonename : "Default";  // SET $this->zonetype TO THE ZONENAME OR TO DEFAULT IF $this->zonename == @ROOT
			}

			if ($this->subZone)  // SHOULD ALWAYS BE FALSE
			{
			    array_push($this->urlzone, $this->zonename);
			}
			else  // THIS SHOULD ALWAYS RUN.
			{
				$this->urlzone = array();
				$this->urlzone[] = $this->zonename;
			}

// 			echo_r($this->zonename);
			//////////////////////////////////////////////////////////////
			//  $this->urlzones handling.  Allows variables in query    //
			//  path to be seperate zones in session memory.            //
			//////////////////////////////////////////////////////////////

// 			if ( $this->urlzones != false && $this->urlzones > 0)
// 			{
// 				//	we should NOT be using this
//  				assert(false);
//
// 				$nextpath = $this->zonetype;
//
// 			    $urlvar = $inPath[0];
// 				$zname = "zone_" . $nextpath;
//
// 				if (!isset($this->_zone["_URLZONE_"][$urlvar]) || !is_a($this->_zone["_URLZONE_"][$urlvar], $zname))
// 				{
//     					$this->_zone["_URLZONE_"][$urlvar] = new $zname;
// 					$this->_zone["_URLZONE_"][$urlvar]->parent =& $this->parent;
// 				}
// 				$this->_zone["_URLZONE_"][$urlvar]->urlvar = $this->urlvar;
// 				$this->_zone["_URLZONE_"][$urlvar]->urlzone = $this->urlzone;
//
// 				$this->_zone["_URLZONE_"][$urlvar]->urlvars = $this->urlvars;
// 				$this->_zone["_URLZONE_"][$urlvar]->urlzones = $this->urlzones - 1;
// 				$this->_zone["_URLZONE_"][$urlvar]->subZone = true;
//
// 				$this->_zone["_URLZONE_"][$urlvar]->zonetype = $this->zonetype;
//
// 				return $this->_zone["_URLZONE_"][$urlvar]->handleRequest($inPath);
// 			}

			//////////////////////////////////////////////////////////////
			//  $this->urlvars handling.  Allows variables in query     //
			//  path.  These variables stored in $this->urlvar array	//
			//////////////////////////////////////////////////////////////
// 			elseif ( $this->urlvars != false && $this->urlvars > 0)
// 			{
// 				//	we should NOT be using this
// 				assert(false);
//
// 				$this->urlvar = array();
//
// 				for ($i = 0; $i < $this->urlvars; $i++)
// 				{
// 					$uvar = array_shift($inPath);
// 					$gPathParts[] = $uvar;
// 					array_push($this->urlvar, array_shift($inPath));
// 				}
// 			}

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

					if (REQUEST_TYPE == "JSRS")
					{
						$this->_jsrsReturnError("Invalid Function Name, $path2!");
						return true;
					}
					elseif (REQUEST_TYPE == "XMLRPC")
					{
						$GLOBALS["zoopXMLRPCServer"]->returnFault(1, "Invalid XMLRPC function, $path2");
						return true;
					}

					array_unshift($inPath, 'default');
					return( $this->_checkFuncs("Default", $inPath) );
				}
			}
		}

		function _checkFuncs($curPath, $inPath)
		{

			if (REQUEST_TYPE == "JSRS")
			{
				return $this->_jsrsDispatch($curPath, $inPath);
			}
			elseif (REQUEST_TYPE == "XMLRPC")
			{
				return $this->_xmlrpcDispatch($curPath, $inPath);
			}
			else
			{
//  	            		echo_r($curPath);
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

			$cur_url = $prefix . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

			if (isset($_SERVER['HTTP_REFERER'])  && $_SERVER['HTTP_REFERER'] == $cur_url)
				return true;
			else
				return false;
		}

		function _checkZone($zoneName, $inPath)
		{
			$var2 = "zone_" . $zoneName;


			//	if the class exists and this zone has no allowed children or this is one of the allowed children
			//		not the easiest thing to follow but I guess it needs to be done this way since the object
			//		hasn't been instantiated yet.  Maybe a static method would be better here for getting the allowed
			//		parents

			if ( class_exists($var2) && (count($this->allowed_children) < 1 || in_array($zoneName, $this->allowed_children) ))
			{
				//	create the new zone object if it does not exist

				if ( !isset( $this->_zone[$zoneName] ) )
				{
					$this->_zone[$zoneName] = new $var2();
					$this->_zone[$zoneName]->parent =& $this;
				}

				// check to see if this is an allowed parent for the class we just created

				if (count($this->_zone[$zoneName]->allowed_parents) > 0 && !in_array($this->zonetype, $this->_zone[$zoneName]->allowed_parents))
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

				//$GLOBALS["zoopXMLRPCServer"]->returnFault(1, "Invalid XMLRPC function");
				return false;
			}
		}

		function _jsrsDispatch($curPath, $inPath)
		{
			if (method_exists($this, "jsrs" . $curPath))
			{
				$funcname = "jsrs" . $curPath;

				$funcparms[] = $inPath;

				$i = 0;

				while(isset($_REQUEST["jsrsP$i"]))
				{
					$funcparms[] = urldecode(substr($_REQUEST["jsrsP$i"], 1, strlen($_REQUEST["jsrsP$i"]) - 2));
					$i++;
				} // while

				ob_start();
				$ret = call_user_func_array(array(&$this, $funcname), $funcparms);
				$debug = ob_get_contents();
				ob_end_clean();

				$this->_jsrsReturn($ret, $debug);
				return true;
			}
			else
			{
				//$this->_jsrsReturnError("No such JSRS function");
				return false;
			}
		}

		function _jsrsReturn($payload, $debug)
		{
			$C = (isset($_REQUEST['jsrsContext']) ? $_REQUEST['jsrsContext'] : "");

			$payloadstring = $this->_jsrsStringIze($payload);

			echo "
				<html>
				<head>
				</head>
				<body onload=\"p=document.layers?parentLayer:window.parent; p.jsrsLoaded('" . $C . "');\">
				jsrsPayload:<br>
				<form name=\"jsrs_Form\">
					<textarea name=\"jsrs_Payload\">" . htmlspecialchars($payloadstring) . "</textarea>
					<textarea name=\"jrsr_Debug\" cols=\"50\" rows=\"5\">" . htmlspecialchars($debug) . "</textarea>
				</form>
				</body>
				</html>
			";

		    return true;
		}

		function _jsrsStringIze($arr, $index = 1)
		{
			$retarr = array();

			if (gettype($arr) != "array")
			{
				return($arr);
			}
			else
			{
				foreach ($arr as $key => $value)
				{
					$retarr[] = $this->_jsrsStringIze( $value, $index + 1 );
				}

				return(implode("~$index", $retarr));
			}
		}

		function _jsrsReturnError($str)
		{
			$C = (isset($_REQUEST['jsrsContext']) ? $_REQUEST['jsrsContext'] : "");

			// escape quotes
			$cleanStr = addslashes($str);

			// !!!! --- Warning -- !!!
			$cleanStr = "jsrsError: " . ereg_replace("\"", "\\\"", $cleanStr);
			echo "
				<html>
				<head>
				</head>
				<body onload=\"p=document.layers?parentLayer:window.parent; p.jsrsError('" . $C . "','" . urlencode($str) . "');\">
				$cleanStr
				</body>
				</html>
			";
		}

		function pageDefault($inPath)
		{
			// This function should be overridden to be the default function (in case there is either A: no path info, or B: no matching function or class for the path";

			die("You haven't overridden pagedefault!");
		}

		function initZone($inPath)
		{
			// This function should be overridden in each zone if you would like code to execute each time it hits the zone's handleRequest function.
		}

		function closeZone($inPath)
		{
			// This function should be overridden in each zone if you would like code to execute each time before it leaves the zone's handleRequest function.
		}

		function initPages($inPath)
		{
			// This function is run before any page or post function in the zone.
		}

		function closePages($inPath)
		{
			// This function is run after any page or post function in the zone.
		}

		function closePosts($inPath)
		{
			// This function is run after any page or post function in the zone.
		}

		function getZoneParamNames()
		{
			return $this->zoneParamNames;
		}
		
		//deprecated...
		function getUrlVarNames()
		{
			bug("called deprecated function: getUrlVarNames(), use getZoneParamNames()");
			return $this->zoneParamNames;
		}

		function setZoneParams($inParamNames)
		{
			return $this->zoneParamNames = $inParamNames;
		}
		
		//deprecated
		function setUrlVarNames($inUrlVarNames)
		{
			bug("called deprecated function: setUrlVarNames(), use setZoneParamNames()");
			return $this->zoneParamNames = $inUrlVarNames;
		}

		function getZoneParams()
		{
			global $gUrlVars;
			return $gUrlVars;
		}
		
		//deprecated
		function getUrlVars()
		{
			bug("called deprecated function: getUrlVars(), use getZoneParams()");
			global $gUrlVars;
			return $gUrlVars;
		}

		/**
		 * Return an array of parent(s) for this zone.
		 */
		function getMyParents() {
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

		function initParents() {

		}

		//should return an url to this zone
		function getZoneUrl($depth = 0)//this function should return a complete url, not a path
		{
			global $gZoneUrls;
			return SCRIPT_URL . $gZoneUrls[$depth];
		}

		//should return an app path to this zone
		function getZonePath($depth = 0)//use this function from now on, until we fix the function above
		{
			global $gZoneUrls;
			return $gZoneUrls[$depth];
		}

		//should redirect us in the zone to the page $inUrl
		function zoneRedirect( $inUrl = '')
		{
			BaseRedirect( $this->url . "/" . $inUrl);
		}
		
		//gets all the paths that were passed as get variables to this zone(macro zone sequencing function)
		function getReturnPaths()
		{
			foreach ($_GET as $key => $val)
			{
				if(substr($key, 0, 8) == "_return_")
					$this->returnPaths[substr($key,8)] = $val;
				if(substr($key, 0, 7) == "_origin")
				{
					$this->origins = array_flip(explode("/",$val));
				}
			}
		}

		//given an action, leaves the zone using that action(macro zone sequencing function)
		function returnRedirect( $inAction)
		{
			$url = $this->getReturnUrl($inAction);
			Redirect( $url);
		}

		//given an action, returns an url to leave the zone(macro zone sequencing function)
		function getReturnUrl($inAction)
		{

			if(!isset($this->origins[$inAction]))
			{
				if (!isset($this->returnPaths[$inAction]))
				{
					$url = '';
				} else {
					$url = $this->getCallUrl($this->returnPaths[$inAction], $this->returnPaths, $this->origins);
				}
			}
			else
			{
				$url = $this->returnPaths[$inAction];
			}

			return $url;
		}

		//given an url to go to, a set of actions, and a set of origins,
		//returns the link that is necessary to keep all sequencing intact(macro zone sequencing function)
		function getCallUrl($url, $inActions, $inOrigins)
		{
			/*
			foreach($inActions as $key => $nothing)
			{
				if(substr($key, 0, 4) != "back" && substr($key, 0, 4) != "next")
					bug("getCallUrl got $key");
			}
			*/

			$returns = array();

			foreach($inActions as $key => $val)
			{
				$returns[] = "_return_$key=" . $inActions[$key];
			}

			$returns[] = "_origin=" . implode( "/", array_flip($inOrigins) );

			$url = $url . "?" . implode("&", $returns);
			return $url;
		}

		function getSimpleCallUrl($url, $returnUrl, $identifier = "")
		{
			/*
			$fixed = array("objectives","evaluations", "meeting","individual","pmdevplan","manager","profile", "devplan");
			if(!in_array($identifier,$fixed))
				bug("Make sure that zone_$identifier expects back_$identifier");
			*/
			$origins["back_$identifier"] = 1;
			$origins["next_$identifier"] = 2;
			$actionurls["back_$identifier"] = $returnUrl;
			$actionurls["next_$identifier"] = $returnUrl;
			return $this->getCallUrl($url, $actionurls, $origins);
		}

		function getNavBar()
		{
			$urlVars = $this->getUrlVars();
			$sequenceID = $urlVars["sequenceId"];
			return $this->_navbar[$sequenceID]->_display_text;
		}

		function getPageOrderNo($pageName)
		{
			$urlVars = $this->getUrlVars();
			$sequenceID = $urlVars["sequenceId"];
			if(isset($this->_navbar[$sequenceID]->$pageName))
				return $this->_navbar[$sequenceID]->$pageName;
			else
				return -1;
		}

		//sets up a step(sequence/navigation) in the internal zone sequence(intra-zone sequencing)

		//declaration should be
		/*********  for better defaults
		function addStep($orderno, $pageName, $actions = array(), $display_text = $pageName, $link = $pageName, $sequenceId = "default", $url = 0)
		**********/
		function addStep($orderno, $sequenceId, $pageName, $display_text, $link, $actions, $url = 0)
		{
			$this->_sequence[$sequenceId]->$pageName = $actions;
			if($orderno > 0)
			{
				$this->_navbar[$sequenceId]->_display_text[$orderno] = $display_text;
				$this->_navbar[$sequenceId]->$pageName = $orderno;
				if(!$url)
					$this->_navbar[$sequenceId]->_link[$orderno] = SCRIPT_URL . $this->getZonePath() . "/" . $link;
				else
					$this->_navbar[$sequenceId]->_link[$orderno] = $link;

				$this->_sequence[$sequenceId]->lastAdded["url"] = $this->_navbar[$sequenceId]->_link[$orderno];
				$this->_sequence[$sequenceId]->lastAdded["pageName"] = $pageName;
			}
			else
			{
				if(!$url)
					$this->_sequence[$sequenceId]->lastAdded["url"] = SCRIPT_URL . $this->getZonePath() . "/" . $link;
				else
					$this->_sequence[$sequenceId]->lastAdded["url"] = $link;

				$this->_sequence[$sequenceId]->lastAdded["pageName"] = $pageName;
			}
		}

		function addSimpleStep($sequenceId, $display_text, $pageName)
		{
			$this->_sequence[$sequenceId]->$pageName = array();
			// set up our back, and lastaddeds next.  Don't overwrite lastadded's next....
			if(isset($this->_sequence[$sequenceId]->lastAdded))
			{
				$this->_sequence[$sequenceId]->$pageName =
					array("back" => $this->_sequence[$sequenceId]->lastAdded["url"]);
				// I would like to not overwrite anything with my stuff here....
				$this->_sequence[$sequenceId]->{$this->_sequence[$sequenceId]->lastAdded["pageName"]} =
					array("next" => SCRIPT_URL . $this->getZonePath() . "/" .$pageName)
					+ $this->_sequence[$sequenceId]->{$this->_sequence[$sequenceId]->lastAdded["pageName"]};
			}

			//find the spot where this page should put it's info...
			if(isset($this->_navbar[$sequenceId]->_display_text) && !isset($this->_navbar[$sequenceId]->$pageName))
			{
				$orderno = count($this->_navbar[$sequenceId]->_display_text) + 1;
			}
			else if(!isset($this->_navbar[$sequenceId]->$pageName))
			{
				$orderno = 1;
			}
			else
			{
				$orderno = $this->_navbar[$sequenceId]->$pageName;
			}
			//set up the navbar
			$this->_navbar[$sequenceId]->_display_text[$orderno] = $display_text;
			$this->_navbar[$sequenceId]->$pageName = $orderno;
			$this->_navbar[$sequenceId]->_link[$orderno] = SCRIPT_URL . $this->getZonePath() . "/" .$pageName;

			//we're now lastadded...
			$this->_sequence[$sequenceId]->lastAdded["url"] = SCRIPT_URL . $this->getZonePath() . "/" .$pageName;
			$this->_sequence[$sequenceId]->lastAdded["pageName"] = $pageName;
		}

		function setStepAction($sequenceId, $pageName, $action, $url)
		{
			//overwrite whatever may be there...
			$this->_sequence[$sequenceId]->$pageName = array_merge($this->_sequence[$sequenceId]->$pageName, array($action => $url));
		}

		//sets up a jumppoint in the internal zone sequence(intra-zone sequencing(also helps with macro zone sequencing))
		function setJumpPoint($sequenceId, $jumpName, $pageName)
		{
			$jumpName = "_".$jumpName;
			$this->_sequence[$sequenceId]->$jumpName = $pageName;
		}

		function resetSequences()
		{
			$this->_sequence = array();
			$this->_navbar = array();
		}

		//redirects to the correct page in the sequence given a current position and an action
		//falls through if action is not designated(intra-zone sequencing)
		function sequenceRedirect($action, $inPath)
		{
			$sequenceID = '';
			$urlVars = $this->getUrlVars();

			if (isset($urlVars["sequenceId"]))
				$sequenceID = $urlVars["sequenceId"];

			$stuff = array();

			if (isset($this->_sequence[$sequenceID]))
				$stuff = get_object_vars($this->_sequence[$sequenceID]);

			if(isset($stuff[$inPath]))
				$actions = $this->_sequence[$sequenceID]->$inPath;

			if(isset($actions) && isset($actions[$action]))
			{
				redirect($actions[$action]);
			}

			//	actually I think we should probably throw an error here
			//trigger_error("sequence $sequenceID has no entry for page $inPath and action $action", E_USER_NOTICE);

			// If no sequence exists, default to a zone redirect
			//$this->zoneRedirect();

		}

		//redirects to the $orderno_th page in the sequence
		//falls through if no such page(intra-zone sequencing)
		function navRedirect($orderno)
		{
			$urlVars = $this->getUrlVars();
			$sequenceID = $urlVars["sequenceId"];
			if(isset($this->_navbar[$sequenceID]->_link[$orderno]))
			{
				redirect($this->_navbar[$sequenceID]->_link[$orderno]);
			}
			trigger_error('zone.navRedirect failed; possible missing sequence: ' . $sequenceID, E_USER_NOTICE);
		}

		//redirects to the specified jumppoint(intra-zone sequencing)
		function jumpRedirect($jumpPoint)
		{
			$urlVars = $this->getUrlVars();
			$sequenceID = $urlVars["sequenceId"];
			$jumpPoint = "_" . $jumpPoint;

			if(isset($this->_sequence[$sequenceID]->$jumpPoint))
			{
				$page = $this->_sequence[$sequenceID]->$jumpPoint;

				$this->zoneRedirect($page);
			}

			if( !isset($this->_sequence[$sequenceID]) )
			{
				trigger_error('missing sequence: ' . $sequenceID, E_USER_NOTICE);
			}

			trigger_error("sequence $sequenceID missing jumpPoint: $jumpPoint", E_USER_NOTICE);
		}

		function isInSequence($sequenceId, $pageName)
		{
			global $currentSequence, $currentSequenceStep;
			if(isset($currentSequence))
			{
				if($currentSequence->pageInSequence($currentSequenceStep, $pageName))
				{
					return true;
				}
			}
			return isset($this->_sequence[$sequenceId]->$pageName);
		}

		function isCurrentSequence($allowedSequences)
		{
			global $currentSequence;
			if(is_array($allowedSequences))
			{
				return in_array($currentSequence->getCurrentPageSequence(), $allowedSequences);
			}
			else
			{
				return $currentSequence->getName() == $allowedSequences;
			}
		}

		function sequenceAllow($sequenceId, $pageName)
		{
			$this->_sequence[$sequenceId]->$pageName = "";
		}


		function hideNext()
		{
			global $gui;
			$gui->showNext = 0;
		}

		function guiAssign($templateVarName, $varValue)
		{
			global $gui;

			$gui->assign($templateVarName, $varValue);
		}


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
