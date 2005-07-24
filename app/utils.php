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



	function logwrite($content, $filename = '/tmp/phplog')
	{

		// In our example we're opening $filename in append mode.
		// The file pointer is at the bottom of the file hence
		// that's where $somecontent will go when we fwrite() it.
		if (!$handle = fopen($filename, 'a')) {
			echo "Cannot open file ($filename)";
			exit;
		}

		// Write $somecontent to our opened file.
		if (fwrite($handle, $content) === FALSE) {
			echo "Cannot write to file ($filename)";
			exit;
		}

		//echo "Success, wrote ($somecontent) to file ($filename)";

		fclose($handle);

	}

///////////////////////////////////////////////////////
//
//	Function: Redirect( $URL )
//
//		Terminates program execution and redirects the
//	client's browser to specified URL.  WHERE:
//
//		$URL -	URL to redirect the client to.
//
///////////////////////////////////////////////////////



	function Redirect( $URL )
	{
		header("location: $URL");
		exit();
	}

///////////////////////////////////////////////////////
//
//	Function: RedirectBoS( )
//
//		Terminates program execution and redirects the
//	client's browser to the base URL of the script.
//
///////////////////////////////////////////////////////

	function RedirectBoS()
	{
		Redirect(SCRIPT_REF);
	}

///////////////////////////////////////////////////////
//
//	Function: BaseRedirect( $URL )
//
//		Terminates program execution and redirects the
//	client's browser to the URL relative to the script.
//
///////////////////////////////////////////////////////

	function BaseRedirect( $URL )
	{
		Redirect(SCRIPT_URL . $URL);
	}

	function RedirectRef()
	{
		Redirect($_SERVER["HTTP_REFERER"]);
	}

	function ZoneRedirect( $url, $depth = 0 )
	{
		BaseRedirect( zone::getZoneUrl($depth) . $url);
	}


/*********************************************************************\
	function: toNumeric

	purpose: removes all non-numeric characters
\*********************************************************************/

	function toNumeric( $number )
	{
		$tmp = $number;
		$tmp2 = "";

		while ($tmp2 != $tmp)
		{
			$tmp2 = $tmp;
			$tmp = ereg_replace("[^0-9.]+", "", $tmp2);
		}

		return($tmp);
	}


	/**
	 *
	 * @access public
	 * @return void
	 **/
	function valid( $mixed )
	{
		if (!isset($mixed) || strlen($mixed) == 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}


/*********************************************************************\
	function: formatCurrency

	purpose: accepts a numeric input, returns number formated to 2 decimals
\*********************************************************************/

	function formatCurrency( $number )
	{
		return sprintf( "%.2f", $number );
	}


/*********************************************************************\
	function: formatMemory

	purpose: accepts a numeric input, returns 23b, 1.42Kb, or 1.44Mb
\*********************************************************************/

	function formatMemory( $number )
	{
		$sp = "b";

		if ($number > 1024)
		{
			$number /= 1024;

			if ($number > 1024)
			{
				$number /= 1024;

				if ($number > 1024)
				{
					$number /= 1024;
					$sp = "Gb";
				}
				else
				{
					$sp ="Mb";
				}
			}
			else
			{
				$sp ="Kb";
			}
		}

		return sprintf( "%.2f", $number ) . " $sp";
	}

	function checkValidDate( $datestring )
	{
		$dt = $datestring;
		$dt = ereg_replace('([0-9]*)-([0-9]*)-([0-9]*)','\1/\2/\3', $dt);
		$dt = ereg_replace('([0-9]*)\.([0-9]*)\.([0-9]*)','\2/\1/\3', $dt);

		$tdt = strtotime($dt);

		if ($tdt === -1)
		{
			return false;
		}
		else
		{
			return $tdt;
		}
	}

/**************
find the current timezone.....
**************/
$tz = date('T');
$dst = date('Z');
if($dst)
{
	$tz = str_replace('D', 'S', $tz);
}

	function FormatPostgresDate( $inPostgresDate, $inFormatString, $inTimeZone = null)
	{
		if(strstr($inFormatString, "%") === false)
		{
			//bug("We need to make sure that $inFormatString string uses %'s");
			trigger_error("The Formating string that has been passed into the FormatPostgresDate() function is formated incorrectly.
			It must follow the formating convention from the Date.php class. For Example: D M j, Y becomes %a %b %e, %Y ");
		}
		//	this should actually parse in the hours, minutes and seconds too
		//		but I don't need them right now.
		$date = &new Date();
		if($inPostgresDate != 0)
		{
			global $tz;
			$timeparts = split("-|:| |\\.", $inPostgresDate);

			$year = $timeparts[0];
			$month = $timeparts[1];
			$day = $timeparts[2];
			$date->setYear($year);
			$date->setMonth($month);
			$date->setDay($day);

			if(isset($timeparts[3]))
			{
				$hours = $timeparts[3];
				$minutes = $timeparts[4];
				$seconds = $timeparts[5];
				$date->setHour($hours);
				$date->setMinute($minutes);
				$date->setSecond($seconds);
			}

			$date->setTZ(new Date_TimeZone($tz));
		}
		if($inTimeZone != NULL)
		{
			$date->convertTZ(new Date_TimeZone($inTimeZone));
		}

		$timeString = $date->format($inFormatString);

		/*
		$timestamp = mktime ( 0, 0, 0,  $month, $day, $year);
		$timeString = date($inFormatString, $timestamp);
		*/

		return $timeString;
	}

/*********************************************************************\
	function: formatMemoryHTML

	purpose: accepts a numeric input, returns 23b, 1.42Kb, or 1.44Mb
			Puts each in a <span class="{b/kb/mb/gb}"> tag
\*********************************************************************/

	function formatMemoryHTML( $number )
	{
		$sp = "b";

		if ($number > 1024)
		{
			$number /= 1024;

			if ($number > 1024)
			{
				$number /= 1024;

				if ($number > 1024)
				{
					$number /= 1024;
					$sp = "Gb";
				}
				else
				{
					$sp ="Mb";
				}
			}
			else
			{
				$sp ="Kb";
			}
		}

		$spt = strtolower($sp);

		return "<span class=\"$spt\">" . sprintf( "%.2f", $number ) . " $sp</span>";
	}


/*********************************************************************\
	function: formatCCN

	purpose: prints 4885666547985421 as 4885-6665-4798-5421
\*********************************************************************/

	function formatCCN( $ccn )
	{
		$output = substr($ccn, 0, 4) . "-" . substr($ccn, 4, 4) . "-" . substr($ccn, 8, 4) . "-" . substr($ccn, 12, 4);

		return $output;
	}


/*********************************************************************\
	function: processArray

	purpose: 	accepts an array and a function, then processes all
				values recursively with the function.
\*********************************************************************/

	function processArray( $arr, $function )
	{
		if (gettype($arr) != "array")
		{
			return $function($arr);
		}
		else
		{
			$buff = array();
			foreach ($arr as $key => $value)
			{
				$buff[$key] = processArray( $value, $function );
			}
			return $buff;
		}
	}

/*********************************************************************\
	function: xorEncrypt

	purpose: 	accepts a message and an 8 bit binary key
				returns the message encrypted.
\*********************************************************************/

function xorEncrypt($message, $key)
{
    $enc = "";

    for($i = 0; $i < strlen($message); $i++)
    {
	    $enc = chr(ord($message[$i]) ^ ($key - ($i * (strlen($message) / 2)))) . $enc;
    }

	return $enc;
}

/*********************************************************************\
	function: xorDecrypt

	purpose: 	accepts a message and an 8 bit binary key
				returns the message decrypted.
\*********************************************************************/

function xorDecrypt($message, $key)
{
    $enc = "";

    for($i = 0; $i < strlen($message); $i++)
    {
	    $enc = chr(ord($message[$i]) ^ ($key - ((strlen($message)-$i-1) * (strlen($message) / 2)))) . $enc;
    }

	return $enc;
}

function echo_r($mixed)
{
	if(app_status == "live")
		return;

	echo "<pre>";
	print_r($mixed);
	echo "</pre>";
}

	/**
	 *
	 * @access public
	 * @return void
	 **/
	function fetch_r($mixed)
	{
		ob_start();

		print_r($mixed);

		$tmp = ob_get_contents();

		ob_end_clean();
		return $tmp;
	}


	function &MapArray(&$transformee, &$transformer)
	{
		$result = array();
		foreach($transformee as $key => $val)
		{
			if(isset($transformer[ $val ]))
				$result[ $key ] = $transformer[ $val ];
			else
				$result[ $key ] = NULL;
		}

		return $result;
	}

function validEmailAddress ($email)
{
	if (eregi("[_\.0-9a-z-]+@[0-9a-z][-0-9a-z\.]+", $email, $check))
	{
//		if (getmxrr($check[1] . "." . $check[2], $temp))
//		{
			return true;
//		}
//		else
//		{
//			return false;
//		}
	}
	else
	{
		return false;
	}
}

function getmicrotime()
{
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

function markprofile($running_total = 0)
{
	if(app_status == 'dev')
	{
		global $_profile_time;
		$backtrace = debug_backtrace();
		$function = '';
		if(isset($backtrace[1]['class']))
		{
			$function = $backtrace[1]['class'];
		}
		if(isset($backtrace[1]['type']))
			$function .= $backtrace[1]['type'];
		if(isset($backtrace[1]['function']))
			$function .= $backtrace[1]['function'];
		$count = count($backtrace);
		if(!isset($_profile_time[$function][$count]))
		{
			$_profile_time[$function][$count] = getmicrotime();
		}
		else
		{
			$duration = (getmicrotime() - $_profile_time[$function][$count]) + $running_total;
			echo_r($function . ' @ line ' . $backtrace[0]['line'] . ' in ' . $backtrace[0]['file'] . ': ' . ($duration) . "<br>\n");
			unset($_profile_time[$function][$count]);
			return $duration;
		}
	}
}


function fetch_backtrace($full = false)
{
	if (function_exists("debug_backtrace"))
	{
		$trace = debug_backtrace();

		$basedir = dirname(dirname(__file__));
		$backtrace = sprintf("%30s%7s\t%-50s\r\n", "FILE:", "LINE:", "FUNCTION:");
		if(php_sapi_name() != "cli")
		{
			$backtrace = "
			<table cellpadding='1' cellspacing='0' border='1' style='margin-left: 5%; background-color: #DDDDDD;'>
			  <tr>
				<th colspan='3' style='text-align: center; color: #333399; padding-right: 5px;'>PHP Backtrace</th>
			  </tr>
			  <tr>
				<th style='text-align: left; padding-right: 5px;'>File:</th>
				<th style='text-align: left; padding-right: 5px;'>Line:</th>
				<th style='text-align: left; padding-right: 5px;'>Function:</th>
			  </tr>";
		}
		foreach ($trace as $line)
		{
			if (isset($line["file"]))
			{
				$file = str_replace($basedir, "", $line["file"]);
			}
			else
			{
				continue;
			}

			if (isset($line["class"]))
			{
				$func = $line["class"] . $line["type"] . $line["function"];
			}
			else
			{
				$func = $line["function"];
			}

			$arglist = array();
			if (!isset($line["args"]))
			{
				$line["args"] = array();
			}
			foreach ($line["args"] as $arg)
			{
				if (is_object($arg))
				{
					if ($full)
					{
						$arglist[] = "<pre>" . fetch_r($arg) . "</pre>";
						//$arglist[] = var_export($arg, true);
					}
					else
					{
						$arglist[] = "&lt;object&gt;";
					}
				}
				elseif (is_array($arg))
				{
					if ($full)
					{
						//$arglist[] = "<pre>" . fetch_r($arg) . "</pre>";
						$arglist[] = var_export($arg, true);
					}
					else
					{
						$arglist[] = "&lt;array&gt;";
					}
				}
				elseif (is_numeric($arg))
				{
					$arglist[] = $arg;
				}
				else
				{
					$arglist[] = "\"$arg\"";
				}
			}

			$funcargs = "(" . implode(", ", $arglist) . ")";;
			if(php_sapi_name() != "cli")
			{
				$backtrace .= "
				  <tr>
					<td>$file</td>
					<td>$line[line]</td>
					<td>$func $funcargs</td>
				  </tr>";
			 }
			 else
			 {
				 $backtrace .= sprintf("%30s%7d\t%-50s\r\n",$file,$line["line"],"$func $funcargs");
			 }
		}
		if(php_sapi_name() != "cli")
		{
			$backtrace .= "</table>";
		}
	}
	else
	{
		$backtrace = "Backtrace not supported in this version of PHP.  You need 4.3.x or better";
	}

	return $backtrace;
}

function array_sortonkeys($inArray, $forward = 1)
{
	$array = array_flip($inArray);

	if($forward)
		asort($array);
	else
		arsort($array);

	return array_flip($array);
}

function urlEncodeArray($array, $keyname = '') {
	$str = '';
	foreach ($array as $key => $val) {
		if (is_array($val)) {
			$str .= urlEncodeArray($val, $key);
		} else {
			if ($keyname) {
				$str .= urlencode($keyname) ."[$key]=". urlencode($val) . "&";
			} else {
				$str .= urlencode($key) .'='. urlencode($val) . '&';
			}
		}
	}
	return $str;
}


function BUG($desc = "")
{
	if(app_status == "dev")
	{
		$functioninfo = debug_backtrace();
		$string = 	"bug in <b>" .
					$functioninfo[0]["file"] .
					"</b> on line <b>" .
					$functioninfo[0]["line"] .
					"</b> in function <b>" .
					$functioninfo[1]["function"] .
					"</b><br>Description:<b>$desc</b><br>";
		echo($string);
	}
}

function echo_backtrace($full = false)
{
	echo fetch_backtrace($full);
}



function GetFreeBlocks($inTop, $inBottom, &$inPlaced, $inSlotHeight)
{
	$openSpace->top = $inTop;
	$openSpace->bottom = $inBottom;
	$openSpaces[] = $openSpace;

	if( isset($inPlaced) )
	{
		foreach($inPlaced as $placedKey => $placedItem)
		{
			$subTop = $placedItem->top;
			$subBottom = $placedItem->bottom;

			$newOpens = array();

			foreach($openSpaces as $key => $thisSpace)
			{
				//	it ends above the top
				if($subBottom > $thisSpace->top)
				{
					$openSpace->top = $thisSpace->top;
					$openSpace->bottom = $thisSpace->bottom;
					$newOpens[] = $openSpace;

//						echo "1<br>";
				}
				//	it starts below the bottom
				else if($subTop < $thisSpace->bottom)
				{
					$openSpace->top = $thisSpace->top;
					$openSpace->bottom = $thisSpace->bottom;
					$newOpens[] = $openSpace;

					// do nothing
//						echo "2<br>";
				}
				//	it starts below the top
				else if($subTop < $thisSpace->top)
				{
					$openSpace->top = $thisSpace->top;
					$openSpace->bottom = $subTop;
					$newOpens[] = $openSpace;

//						echo "3<br>";

					//	it's entirely in the space
					if($subBottom > $thisSpace->bottom)
					{
						$openSpace->top = $subBottom;;
						$openSpace->bottom = $thisSpace->bottom;
						$newOpens[] = $openSpace;

//							echo "3.5<br>";
					}


				}
				//	it starts above the top but ends below the top
				else
				{
					if($subBottom > $thisSpace->bottom)
					{
						$openSpace->top = $subBottom;;
						$openSpace->bottom = $thisSpace->bottom;
						$newOpens[] = $openSpace;
					}
					else
					{
					}
				}
			}
			$openSpaces = $newOpens;
		}
	}

	$slots = array();

	$slotHeight = $inSlotHeight;

	foreach($openSpaces as $key => $openSpace)
	{
		//	calculate the number of slots in this open space

		$nSlots = floor( ($openSpace->top - $openSpace->bottom) / ($slotHeight) );

		//	calculate the extra space were going to have at the bottom

		$extra = ($openSpace->top - $openSpace->bottom) % ($slotHeight);


		for($i = 0; $i < $nSlots; $i++)
		{
			//$slots[] = $openSpace->top - ($i * $slotHeight) - ($slotHeight / 2) - ($extra / 2);
			$slots[] = $openSpace->top - ($i * $slotHeight);
		}
	}

	return $slots;
}

//this function is dangerous, and shouldn't be used generally
function getRawPost()
{
	global $POSTCOPY;
	return $POSTCOPY;
}

function GetPostIsset($inName)
{
	global $POSTCOPY;
	return isset($POSTCOPY[$inName]);
}
//	This returns 0 or 1
function GetPostCheckbox($inName)
{
	global $POSTCOPY;
	return isset( $POSTCOPY[$inName] ) ? 1 : 0;
}

//	This returns the raw unfiltered contents of a post variable
function GetPostString($inName)
{
	global $POSTCOPY;
	if( isset($POSTCOPY["$inName"]) )
		return $POSTCOPY["$inName"];
	else
		return false;
}

//this should eventually check the html for javascript....
function GetPostHTML($inName)
{
	//reduce the HTML we get to acceptable HTML
	global $POSTCOPY;

	if(!defined('filter_input') || filter_input)
	{
		$html = $POSTCOPY[$inName];
		return __verifyHTMLTree($html);
	}
	else
	{
		$answer = $POSTCOPY[$inName];
	}
	return $answer;
}

function getPostHTMLArray($inName)
{
	global $POSTCOPY;
	$answer = array();
	$post = $POSTCOPY;
	if(is_array($inName))
	{
		foreach($inName as $key)
		{
			if(isset($post[$key]))
				$post = $post[$key];
			else
				return false;
		}
	}
	else
	{
		if(isset($post[$inName]))
			$post = $post[$inName];
		else
			return false;
	}
	if(isset($post))
	{
		foreach($post as $key => $text)
		{
			$answer[$key] = verifyText($text);
		}
	}
	return $answer;
}

$allowed_tags = array(
	"p",
	"root",
	"table",
	"tr",
	"td",
	"span",
	"ul",
	"ol",
	"li",
	"a",
	"br",
	"nobr",
);

$allowed_attributes = array(
	"class",
	"align",
	"valign",
	"href",
	"src",
	"target",
);

function __VerifyHTMLTree($html)
{
	require_once('XML/Tree.php');
	$tree = &new XML_Tree();
	//$html = $POSTCOPY[$inName];
	//print_r(get_class_methods("XML_Tree"));
	$html = stripslashes("<root><root>$html</root></root>");
	$htmltree = $tree->getTreeFromString("$html");
	__verifyHTMLTree_ex($htmltree);
	$answer = $htmltree->get();
	return substr($answer, 15, strlen($answer) - 31);
}

function __VerifyHTMLTree_ex(&$htmltree)
{
	global $allowed_tags, $allowed_attributes;
	foreach($htmltree->children as $key => $childtree)
	{
		if(in_array($htmltree->children[$key]->name, $allowed_tags))
			__VerifyHTMLTree($htmltree->children[$key]);
		else
			unset($htmltree->children[$key]);
	}
	foreach($htmltree->attributes as $key => $value)
	{
		if(!in_array($key, $allowed_attributes))
		{
			unset($htmltree->attributes[$key]);
		}
		else
		{
			if(stristr("javascript", $value))
			{
				unset($htmltree->attributes[$key]);
			}
		}
	}
}

function VerifyText($inText)
{
	$inText = br2nl($inText);
	if(!defined('filter_input') || filter_input)
		return strip_tags($inText);
	else
		return $inText;
}


function br2nl($text) {
   $text = str_replace("<br />\n", "\r\n", $text);
   $text = str_replace("<br>\n", "\r\n", $text);
   return $text;
}

//	This strips all html from the variable then returns it
function GetPostText($inName)
{
	global $POSTCOPY;
	if( isset($POSTCOPY[$inName]) )
		return VerifyTextOrArray($POSTCOPY["$inName"]);
	else
		return false;
}

function getPostTextArray($inName)
{
	global $POSTCOPY;
	$answer = array();
	$post = $POSTCOPY;
	if(is_array($inName))
	{
		foreach($inName as $key)
		{
			if(isset($post[$key]))
				$post = $post[$key];
			else
				return false;
		}
	}
	else
	{
		if(isset($post[$inName]))
			$post = $post[$inName];
		else
			return false;
	}
	if(isset($post))
	{
		foreach($post as $key => $text)
		{
			if (is_array($text))
			{
				$answer[$key] = VerifyTextOrArray($text);
			}
			else
			{
				$answer[$key] = verifyText($text);
			}
		}
	}

	return $answer;
}

function VerifyTextOrArray($array)
{
	if (!is_array($array))
	{
		return verifyText($array);
	}

	$return = array();

	foreach($array as $key => $value)
	{
		if (is_array($value))
		{
		    $return[$key] = VerifyTextArray($value);
		}
		else
		{
			$return[$key] = verifyText($value);
		}
	}
	return $return;
}

function VerifyInt($inNumber)
{
	if(defined('filter_input') && !filter_input)
		return $inNumber;
	if($inNumber === '')
		return '';
	assert( is_numeric($inNumber));
	return (integer)$inNumber;
}

//	This makes sure that it is an integer and casts it as such
function GetPostInt($inName)
{
	global $POSTCOPY;
	if( isset($POSTCOPY["$inName"]) )
	{
		return verifyInt($POSTCOPY["$inName"]);
	}
	else
		return false;
}

function GetPostIntArray($inName)
{
	global $POSTCOPY;
	$answer = array();
	$post = $POSTCOPY;
	if(is_array($inName))
	{
		foreach($inName as $key)
		{
			if(isset($post[$key]))
				$post = $post[$key];
			else
				return false;
		}
	}
	else
	{
		if(isset($post[$inName]))
			$post = $post[$inName];
		else
			return false;
	}
	if(isset($post))
	{
		foreach($post as $key => $text)
		{
			$answer[$key] = verifyInt($text);
		}
	}
	return $answer;
}

function GetPostIntTree($inName)
{
	global $POSTCOPY;
	$answer = array();
	$post = $POSTCOPY;
	if(is_array($inName))
	{
		foreach($inName as $key)
		{
			if(isset($post[$key]))
				$post = $post[$key];
			else
				return false;
		}
	}
	else
	{
		if(isset($post[$inName]))
			$post = $post[$inName];
		else
			return false;
	}
	return __getPostIntTree($post);
}

function __GetPostIntTree($post)
{
	$answer = array();
	if(is_array($post))
	{
		foreach($post as $key => $val)
		{
			$answer[$key] = __getPostIntTree($val);
		}
		return $answer;
	}
	else
	{
		return verifyInt($post);
	}
}

function GetPostTextTree($inName)
	{
		global $POSTCOPY;
		$answer = array();
		$post = $POSTCOPY;
		if(is_array($inName))
		{
			foreach($inName as $key)
			{
				if(isset($post[$key]))
					$post = $post[$key];
				else
					return false;
			}
		}
		else
		{
			if(isset($post[$inName]))
				$post = $post[$inName];
			else
				return false;
		}
		return __getPostTextTree($post);
	}

function __GetPostTextTree($post)
{
	$answer = array();
	if(is_array($post))
	{
		foreach($post as $key => $val)
		{
			$answer[$key] = __getPostTextTree($val);
		}
		return $answer;
	}
	else
	{
		return verifyText($post);
	}
}

function unsetPost($inName)
{
	global $POSTCOPY;
	unset($POSTCOPY[$inName]);
}

function getPostKeys()
{
	global $POSTCOPY;
	return array_keys($POSTCOPY);
}

function get_shared_key()
{
	$file = fopen(shared_key_path, "r");
	$key = fgets($file);
	fclose($file);
	return $key;
}

function get_key($type)
{
	$string = "{$type}_key_path";
	if(defined($string))
	{
		$file = fopen(constant($string), "r");
		$key = trim(fgets($file));
		fclose($file);
		return $key;
	}
	else
	{
		trigger_error("$string is not defined");
	}
}


function RequireCondition($bool)
{
	if(!$bool)
	{
		if(defined("app_login_page"))
		{
			if(app_status != 'dev')
			{
				trigger_error("Condition Failed");
				die();
			}
			global $eHandlerObject;

			if(app_status != 'dev')
				$eHandlerObject->logError("undefined", "Failed Require", "see backtrace", "see backtrace", "");

			if(app_status == 'live')
			{
				session_destroy();
			}

			redirect(app_login_page);
		}
		else
		{
			trigger_error("Condition Failed");
		}
	}
}

function remoteObjectCall($url, $object, $constparams, $method, $methodparams)
{
	$key = get_shared_key();
	$key .= $object;
	$key .= implode("", $constparams);
	$key .= $method;
	$key .= implode("", $methodparams);
	$hash = md5($key);

	$url .= "/" . $hash;
	$url .= "/" . $object;
	$url .= "/" . implode("/", $constparams);
	$url .= "/" . $method;
	$url .= "/" . implode("/", $methodparams);
	$file = fopen($url, "r");

	$output = fgets($file);
	if(!empty($output) && $output != "<br />\n")
		$answer = unserialize($output);
	else
	{
		$answer = false;
		trigger_error("Unable to get remote object...");
	}
	/*if(perform_status == "dev")
	{
		echo("<pre>\r\nRemote Output:\r\n");
		while($output)
		{
			echo($output . "<br>");
			$output = fgets($file);
		}
		echo("</pre>");
	}*/
	return $answer;
}


function file_set_contents($inFilename, $inContents)
{
   if(!$handle = fopen($inFilename, 'w'))
   {
		 trigger_error("Cannot open file ($filename)");
   }

   // Write $somecontent to our opened file.
   if (fwrite($handle, $inContents) === FALSE)
   {
	   trigger_error("Cannot write to file ($filename)");
   }

   fclose($handle);
}


function Encrypt($key, $input)
{
	$td = mcrypt_module_open (MCRYPT_TripleDES, "", MCRYPT_MODE_ECB, "");
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size ($td), MCRYPT_RAND);
	mcrypt_generic_init($td, $key, $iv);
	$encrypted_data = mcrypt_generic($td, $input);
	mcrypt_generic_deinit($td);

	return $encrypted_data;
}


function Decrypt($key, $input)
{
	$td = mcrypt_module_open(MCRYPT_TripleDES, "", MCRYPT_MODE_ECB, "");
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size ($td), MCRYPT_RAND);
	mcrypt_generic_init($td, $key, $iv);
	$decrypted_data = mdecrypt_generic($td, $input);
	mcrypt_generic_deinit($td);

	return $decrypted_data;
}

//
//	cli stuff
//

function RunCommand($inCommand)
{
	$command = $inCommand;

	echo $command;
	passthru($command);
}


function SetCompletionStatus($statusItemName, $start = NULL, $end = NULL, $goodEnd = NULL)
{
	$oldStatus = GetCompletionStatus($statusItemName);

	if($start == NULL)
		$start = $oldStatus['start'];

	if($end == NULL)
		$end = $oldStatus['end'];

	if($goodEnd == NULL)
		$goodEnd = $oldStatus['goodEnd'];

	file_set_contents(app_status_dir . "/" . $statusItemName, "$start $end $goodEnd");
}


function &GetCompletionStatus($statusItemName)
{
	$data = file_get_contents(app_status_dir . "/" . $statusItemName);

	$parts = explode(" ", $data);

	if( isset($parts[0]) )
		$status['start'] = (integer)trim($parts[0]);
	else
		$status['start'] = 0;

	if( isset($parts[1]) )
		$status['end'] = (integer)trim($parts[1]);
	else
		$status['end'] = 0;

	if( isset($parts[2]) )
		$status['goodEnd'] = (integer)trim($parts[2]);
	else
		$status['goodEnd'] = 0;

	return $status;
}

function sqlMap($field, $mapfield)
{
	$sql = "case $field ";
	foreach ($mapfield as $key => $value)
	{
		$sql .= "when $key then ". sql_escape_string($value) . " ";
	}
	$sql .= "else '' end";
	return $sql;
}


function mkdir_r($filename)
{
	str_replace("\\",'/', $filename);
	$dir = explode("/", $filename);
	array_pop($dir);
	$path = "";
	for($i=0; $i < count($dir); $i++)
	{
		$path .= $dir[$i] . "/";
		if(!is_dir($path))
		{
			mkdir($path,0770);
			chmod($path,0770);
		}
	}

	$dirName = implode("/", $dir);

	assert(is_dir($dirName));
}


// INPUT :
// $pHexColor : ie #339933
// OUTPUT :
// return rgb array
// Source : Rini Setiadarma, http://www.oodie.com/

function HexToRgb($pHexColor)
{

	$l_returnarray = array ();
	if (!(strpos ($pHexColor, "#") === FALSE))
	{
		$pHexColor = str_replace ("#", "", $pHexColor);
		for ($l_counter=0; $l_counter<3; $l_counter++)
		{
			$l_temp = substr($pHexColor, 2*$l_counter, 2);
			$l_returnarray[$l_counter] = 16 * hexdec(substr($l_temp, 0, 1)) + hexdec(substr($l_temp, 1, 1));
		}
	}
	return $l_returnarray;
}


//	give this funtion a 2 dimentional array and it will stream out a csv file to the browser

function StreamCSV($inData, $inFilename, $inColumns = NULL)
{
	$lines = array();

	if($inColumns === NULL)
	{
		//	if they didn't give us any info then this is simple...
		foreach($inData as $thisRow)
		{
			$lines[] = '"' . implode('","', $thisRow) . '"';
		}
	}
	else
	{
		//
		//	if they gave us column info...
		//

		//	first add a line for the column headings
		$thisLine = array();
		foreach($inColumns as $displayName => $dbName)
		{
			$thisLine[] = $displayName;
		}
		$lines[] = '"' . implode('","', $thisLine) . '"';

		//	now add in all the data
		foreach($inData as $thisRow)
		{
			$thisLine = array();
			foreach($inColumns as $displayName => $dbName)
			{
				$thisLine[] = $thisRow[$dbName];
			}
			$lines[] = '"' . implode('","', $thisLine) . '"';
		}
	}

	header("Content-Type: text/csv; extension=\".csv\"");
	header("Content-Disposition: attachment; filename=\"$inFilename\"");
	echo implode("\r\n", $lines);
}


function HTML2Txt($inHTML)
{

	$txt = br2nl($inHTML);

	return strip_tags($inText);

}



?>
