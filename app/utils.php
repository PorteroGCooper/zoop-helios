<?
/**
* Utilities file
*
* @package app
* @subpackage utils
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

/**
* write to a log
*
* Append $content to $filename.
*
* @param string $content message to be written
* @param string $filename filename to write to
*/
function logwrite($content, $filename = '/tmp/phplog')
{
	append_to_file($filename, $content);
}

///////////////////////////////////////////////////////
//
//	Function: Redirect( $URL , $redirectType)
//
//		Terminates program execution and redirects the
//	client's browser to specified URL.  WHERE:
//
//		$URL -	URL to redirect the client to.
//		$redirectType - method of redirection
//
///////////////////////////////////////////////////////

define("HEADER_REDIRECT", 1);
define("JS_REDIRECT", 2);

/**
* redirect to an URL
*
* redirect to $URL using method $redirectType and terminate the program.
*
* @param string $URL Full url, http://example.com
* @param integer $redirectType possible values are {@link HEADER_REDIRECT} and {@link JS_REDIRECT}
*/
function Redirect( $URL, $redirectType = HEADER_REDIRECT)
{
	switch($redirectType)
	{
		case HEADER_REDIRECT:
			header("location: $URL");
			break;
		case JS_REDIRECT:
			echo("<script language=\"JavaScript\" type=\"text/javascript\"><!-- top.location.href = \"$URL\"; --></script>");
			break;
		default:
			trigger_error("unknown redirect type");
			break;
	}
	exit();
}


/**
* redirect to the base of the application
*
* redirect using method $redirectType
*
* @param integer $redirectType
* @uses Redirect
*/
function RedirectBoS($redirectType = HEADER_REDIRECT)
{
	Redirect(SCRIPT_REF, $redirectType);
}

/**
* redirect within the application
*
* Redirect to SCRIPT_URL . $URL, for example:
*
* BaseRedirect("/login") redirects to http://example.com/index.php/login
*
* @param string $URL path info to an url within the application. Starts with "/"
* @param integer $redirectType
* @uses Redirect
*/
function BaseRedirect( $URL , $redirectType = HEADER_REDIRECT)
{
	Redirect(SCRIPT_URL . $URL, $redirectType);
}

/**
* redirect to the referring page
*
* Redirect to HTTP_REFERER
*
* @uses Redirect
*/
function RedirectRef()
{
	Redirect($_SERVER["HTTP_REFERER"]);
}

/**
* redirect to a zone path
*
* Redirect to a path($url) based on the current zone
*
* @deprecated use zone::zoneRedirect() instead
* @uses Redirect
*/
function ZoneRedirect( $url, $depth = 0 )
{
	Redirect( zone::getZoneUrl($depth) . $url);
}


/**
 * checkValidDate 
 * 
 * @param mixed $datestring 
 * @access public
 * @return void
 */
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
/*
JOHN WHAT IS THIS, IT WAS JUST SITTING OUTSIDE OF ANY FUNCTION
*/
$tz = date('T');
$dst = date('Z');
if($dst)
{
	$tz = str_replace('D', 'S', $tz);
}

/**
 * FormatPostgresDate 
 * 
 * @param mixed $inPostgresDate 
 * @param mixed $inFormatString 
 * @param mixed $inTimeZone 
 * @access public
 * @return void
 */
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


/**
* prints 48856665XXXX5421 as 4885-6665-XXXX-5421
*
* @param string $ccn Credit Card Number to Format
* @return string Formatted Credit Card Number
*/
	function formatCCN( $ccn )
	{
		$output = substr($ccn, 0, 4) . "-" . substr($ccn, 4, 4) . "-" . substr($ccn, 8, 4) . "-" . substr($ccn, 12, 4);

		return $output;
	}

/**
* accepts an array and a function, then processes all values recursively with the function.
*
* @param array $arr
* @param function $function
*/
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

/**
 * xorEncrypt 
 *
 * accepts a message and an 8 bit binary key and returns the message encrypted.
 * @param mixed $message 
 * @param mixed $key 
 * @access public
 * @return void
 */
function xorEncrypt($message, $key)
{
    $enc = "";

    for($i = 0; $i < strlen($message); $i++)
    {
	    $enc = chr(ord($message[$i]) ^ ($key - ($i * (strlen($message) / 2)))) . $enc;
    }

	return $enc;
}

/**
 * xorDecrypt 
 *
 * 	accepts a message and an 8 bit binary key and returns the message decrypted 
 * @param mixed $message 
 * @param mixed $key 
 * @access public
 * @return void
 */
function xorDecrypt($message, $key)
{
    $enc = "";

    for($i = 0; $i < strlen($message); $i++)
    {
	    $enc = chr(ord($message[$i]) ^ ($key - ((strlen($message)-$i-1) * (strlen($message) / 2)))) . $enc;
    }

	return $enc;
}
/**
* simply puts "<pre>" tags around the print_r call so the formatting looks good in a browser.
*
* @param mixed $mixed
*/
function echo_r($mixed)
{
// 	if(app_status == "live")
// 		return;

	echo "<pre>";
	print_r($mixed);
	echo "</pre>";
}

/**
* die() doesn't do a good job of dumping objects and arrays. this one does what die should...
*
* @param mixed $mixed
*/
function die_r($mixed)
{
	echo_r($mixed);
	die();
}
/**
* simply puts "<pre>" tags around the var_dump call so the formatting looks good in a browser.
*
* @param mixed $mixed
*/
function dump_r($mixed)
{
	echo("<pre>");
	var_dump($mixed);
	echo("</pre>");
}
/**
* draws a div with a colored border around an echo_r call. Very useful to keep track of multiple echo_r calls.
*
* @param mixed $mixed
* @param string $color any acceptable color string that works with css
*/
function show_r($mixed, $color = "#000000")
{
	echo "<div align=\"left\" style=\"border: 1px solid $color;\">";
	echo_r($mixed);
	echo "</div>";
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

/**
 * &MapArray 
 * 
 * @param mixed $transformee 
 * @param mixed $transformer 
 * @access public
 * @return void
 */
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

/**
 * validEmailAddress 
 * 
 * @param mixed $email 
 * @access public
 * @return void
 */
function validEmailAddress ($email)
{
	if (eregi("[_\.0-9a-z-]+@[0-9a-z][-0-9a-z\.]+", $email, $check))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * getmicrotime 
 * 
 * @access public
 * @return void
 */
function getmicrotime()
{
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

/**
 * markprofile 
 * 
 * @param int $running_total 
 * @access public
 * @return void
 */
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

/**
 * fetch_backtrace 
 * 
 * @param mixed $full 
 * @access public
 * @return void
 */
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

/**
 * array_sortonkeys 
 * 
 * @param mixed $inArray 
 * @param int $forward 
 * @deprecated use ksort or krsort instead
 * @access public
 * @return void
 */
function array_sortonkeys($inArray, $forward = 1)
{
	if($forward)
		ksort($inArray);
	else
		krsort($inArray);
}

/**
 * urlEncodeArray 
 * 
 * @param mixed $array 
 * @param string $keyname 
 * @access public
 * @return void
 */
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


/**
 * BUG 
 * 
 * @param string $desc 
 * @access public
 * @return void
 */
function BUG($desc = "")
{
	if (show_warnings == false)
		return;

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

/**
 * echo_backtrace 
 * 
 * @param mixed $full 
 * @access public
 * @return void
 */
function echo_backtrace($full = false)
{
	echo fetch_backtrace($full);
}

/**
* A better stripslashes function. This one checks to see if magic_quotes_gpc is on
* and stripsslashes only when it is on.
* Also works on Arrays
*
* @author ferik100 at flexis dot com dot br   posted to php.net (public domain)
* @param string $str
* @return string
*/
function strip_gpc_slashes ($input)
{
	if ( !get_magic_quotes_gpc() || ( !is_string($input) && !is_array($input) ) )
	{
		return $input;
	}

	if ( is_string($input) )
	{
		$output = stripslashes($input);
	}
	elseif ( is_array($input) )
	{
		$output = array();
		foreach ($input as $key => $val)
		{
		$new_key = stripslashes($key);
		$new_val = strip_gpc_slashes($val);
		$output[$new_key] = $new_val;
		}
	}

	return $output;
}

/**
 * __VerifyHTMLTree 
 * 
 * @param mixed $html 
 * @access protected
 * @return void
 */
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

/**
 * __VerifyHTMLTree_ex 
 * 
 * @param mixed $htmltree 
 * @access protected
 * @return void
 */
function __VerifyHTMLTree_ex(&$htmltree)
{
	global $allowed_tags, $allowed_attributes;

	foreach($htmltree->children as $key => $childtree)
	{
		if(in_array($htmltree->children[$key]->name, $allowed_tags))
			__VerifyHTMLTree_ex($htmltree->children[$key]);
		else
		{
			unset($htmltree->children[$key]);
		}
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

/**
 * VerifyText 
 * 
 * @param mixed $inText 
 * @access public
 * @return void
 */
function VerifyText($inText)
{
	$inText = br2nl($inText);
	if(!defined('filter_input') || filter_input)
		return strip_tags($inText);
	else
		return $inText;
}

/**
* Companion to nl2br, convert "<br>" tags to new line characters
*
* @param string $text
* @return string
*/
function br2nl($text) {
   $text = str_replace("<br />\n", "\r\n", $text);
   $text = str_replace("<br>\n", "\r\n", $text);
   return $text;
}


/**
 * VerifyTextOrArray 
 * 
 * @param mixed $array 
 * @access public
 * @return void
 */
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

/**
 * VerifyInt 
 * 
 * @param mixed $inNumber 
 * @access public
 * @return void
 */
function VerifyInt($inNumber)
{
	if(defined('filter_input') && !filter_input)
		return $inNumber;
	if($inNumber === '')
		return '';
	assert( is_numeric($inNumber));
	return (integer)$inNumber;
}

/**
 * get_shared_key 
 * 
 * reads a key(for authentication between apps) from the shared key file.
 * @access public
 * @return void
 */
function get_shared_key()
{
	$file = fopen(shared_key_path, "r");
	$key = fgets($file);
	fclose($file);
	return $key;
}

/**
 * get_key 
 * 
 * @param mixed $type 
 * @access public
 * @return void
 */
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
 /**
  * Useful when programming or debugging.
  * Require input to be true, error triggered if not true.
  *
  * @param       bool   $bool    The conditional value
  * @access public
  * @return void
  */
function RequireCondition($bool)
{
	if(!$bool)
	{
		if(defined("app_login_page"))
		{
			//should dev be redirected?
			if(app_status != 'dev')
			{
				trigger_error("Condition Failed");
				die();
			}
			redirect(app_login_page);
		}
		else
		{
			trigger_error("Condition Failed");
		}
	}
}

/**
 * remoteObjectCall 
 * 
 * @param mixed $url 
 * @param mixed $object 
 * @param mixed $constparams 
 * @param mixed $method 
 * @param mixed $methodparams 
 * @access public
 * @return void
 */
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

 /**
  * Opens / Creates a file ($inFilename) and places $inContents into it
  * If you are using PHP 5 you could alternatively use file_put_contents instead as it is a native function now.
  *
  * @param       string   $inFilename    	The absolute location of the file
  * @param       string   $inContents    	The contents to put into file
  * @param       string   $mode    		Write mode, defaults to 'w' (open and write at top)
  * @access public
  * @return void
  */
function file_set_contents($inFilename, $inContents, $mode = 'w')
{
	if(!$handle = fopen($inFilename, $mode))
	{
		trigger_error("Cannot open file ($filename)");
	}
	if (fwrite($handle, $inContents) === FALSE)
	{
		trigger_error("Cannot write to file ($filename)");
	}
	fclose($handle);
}

 /**
  * Opens / Creates a file ($inFilename) and appends $inContents to the end of it
  *
  * @param       string   $inFilename    	The absolute location of the file
  * @param       string   $inContents    	The contents to append to the file
  */
function append_to_file($inFilename, $inContents)
{
	file_set_contents($inFilename, $inContents, 'a');
}

 /**
  * Opens / Creates a file ($inFilename) and all necessary directories to it, then places $inContents into it
  *
  * @param       string   $inFilename    	The absolute location of the file
  * @param       string   $inContents    	The contents to put into file
  * @param       string   $mode    		Write mode, defaults to 'w' (open and write at top)
  */
function file_write($inFilename, $inContents, $mode = 'w')
{
	if (mkdir_r($inFilename))
		file_set_contents($inFilename, $inContents, $mode);
}

 /**
  * Apply an Encryption on Input
  * To be used with Decrypt
  *
  * @param       string   $key    The key to encrypt $input with
  * @param       string   $input    The value to encrypt
  * @return      string   The encrypted data
  */
function Encrypt($key, $input)
{
	$td = mcrypt_module_open (MCRYPT_TripleDES, "", MCRYPT_MODE_ECB, "");
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size ($td), MCRYPT_RAND);
	mcrypt_generic_init($td, $key, $iv);
	$encrypted_data = mcrypt_generic($td, $input);
	mcrypt_generic_deinit($td);

	return $encrypted_data;
}

 /**
  * Decrypt data for usage
  * To be used with Encrypt
  *
  * @param       string   $key     The key to decrypt $input with (the same as the key it was encrypted with)
  * @param       string   $input   The Encrypted value to decrypt
  * @return      string   The decrypted data
  */
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
/**
 * RunCommand 
 * 
 * @param mixed $inCommand 
 * @access public
 * @return void
 */
function RunCommand($inCommand)
{
	$command = $inCommand;

	echo $command;
	passthru($command);
}

/**
 * SetCompletionStatus 
 * 
 * @param mixed $statusItemName 
 * @param mixed $start 
 * @param mixed $end 
 * @param mixed $goodEnd 
 * @access public
 * @return void
 */
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

/**
 * &GetCompletionStatus 
 * 
 * @param mixed $statusItemName 
 * @access public
 * @return void
 */
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

 /**
  *  Create directories required for $filename recursively
  *  using mkdirr.
  *
  * @param       string   $pathname    The filename you want to create a directory for.
  * @return      bool     Returns TRUE on success, FALSE on failure
  */
function mkdir_r($filename)
{
	str_replace("\\",'/', $filename);
	$dir = explode(DIRECTORY_SEPARATOR, $filename);
	array_pop($dir);
	$path = implode(DIRECTORY_SEPARATOR, $dir);

	return mkdirr($path, 0770);
}

 /**
  * Create a directory structure recursively
  *
  * @author      Aidan Lister <aidan@php.net>
  * @version     1.0.0
  * @link        http://aidanlister.com/repos/v/function.mkdirr.php
  * @param       string   $pathname    The directory structure to create
  * @return      bool     Returns TRUE on success, FALSE on failure
  */
 function mkdirr($pathname, $mode = 0770)
 {
     // Check if directory already exists
     if (is_dir($pathname) || empty($pathname)) {
         return true;
     }

     // Ensure a file does not already exist with the same name
     if (is_file($pathname)) {
         trigger_error('mkdirr() File exists', E_USER_WARNING);
         return false;
     }

     // Crawl up the directory tree
     $next_pathname = substr($pathname, 0, strrpos($pathname, DIRECTORY_SEPARATOR));
     if (mkdirr($next_pathname, $mode)) {
         if (!file_exists($pathname)) {
             return mkdir($pathname, $mode);
         }
     }
     return false;
 }


/**
 * HexToRgb 
 * 
 * @author Rini Setiadarma
 * @link   http://www.oodie.com/
 * @param  string $pHexColor 
 * @access public
 * @return array rgb array
 */
function HexToRgb($pHexColor)
{

	$l_returnarray = array ();
	if (!(strpos ($pHexColor, "#") === FALSE))
	{
		$pHexColor = str_replace ("#", "", $pHexColor);
		for ($l_counter=0; $l_counter < 3; $l_counter++)
		{
			$l_temp = substr($pHexColor, 2*$l_counter, 2);
			$l_returnarray[$l_counter] = 16 * hexdec(substr($l_temp, 0, 1)) + hexdec(substr($l_temp, 1, 1));
		}
	}
	return $l_returnarray;
}


/**
 * StreamCSV 
 *
 * give this funtion a 2 dimentional array and it will stream out a csv file to the browser
 * @param mixed $inData 
 * @param mixed $inFilename 
 * @param mixed $inColumns 
 * @access public
 * @return void
 */
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

/**
* return a random element's value within an array
*
* @param array $inArray to return a random element
*/
function randElement($inArray)
{
	$tmp = mt_rand(0, count($inArray) - 1);

	$keys = array_keys($inArray);
	$key = $keys[$tmp];

	return $inArray[$key];
}
/**
* return a given number of random nonduplicate elements of an array
*
* @param array $inArray to return a random element
* @param int $number of random elements to return
*/
function randElements($inArray, $num)
{
	if (count($inArray) == 0)
		return $inArray;

	if ($num > count($inArray))
		$num = count($inArray);

	$tmpkeys = mrand(0, count($inArray) - 1, $num);

	$keys = array_keys($inArray);

	$returnarray = array();

	foreach($tmpkeys as $tkey)
	{
		$key = $keys[$tkey];
		$returnarray[] = $inArray[$key];
	}

	return $returnarray;
}

/**
* Multiple Unique Random Numbers
*
* @usage array mrand ( int min, int max, int count [, int strlen ] )
*/
function mrand($l,$h,$t,$len=false){

	if($l>$h){$a=$l;$b=$h;$h=$a;$l=$b;}
	if( (($h-$l)+1)<$t || $t<=0 )return false;

    	$n = array();

	if($len>0)
	{
		if(strlen($h)<$len && strlen($l)<$len)return false;
		if(strlen($h-1)<$len && strlen($l-1)<$len && $t>1)return false;

		while(count($n)<$t)
		{
			$x = mt_rand($l,$h);
			if(!in_array($x,$n) && strlen($x) == $len)
				$n[] = $x;
		}

	}
	else
	{
		while(count($n)<$t)
		{
			$x = mt_rand($l,$h);
			if(!in_array($x,$n))
				$n[] = $x;
		}
	}

	return $n;
}


/**
* convert html to fairly readable text
*
* @param string $inHTML
*/
function HTML2Txt($inHTML)
{
	$txt = br2nl($inHTML);

	return strip_tags($inText);
}


/**
 * seconds_to_time 
 * 
 * @param mixed $seconds 
 * @param string $return 
 * @access public
 * @return void
 */
function seconds_to_time($seconds, $return = "array")
{
	$months = 0;
	$days = 0;
	$hours = 0;
	$minutes = 0;

	while ($seconds >= 60) {
		if ($seconds >= 2629743.83) {
		$months = floor($seconds / 2629743.83);
		$seconds = $seconds - ($months * 2629743.83);
		} elseif ($seconds >= 86400) {
		// there is more than 1 day
		$days = floor($seconds / 86400);
		$seconds = $seconds - ($days * 86400);
		} elseif ($seconds >= 3600) {
		$hours = floor($seconds / 3600);
		$seconds = $seconds - ($hours * 3600);
		} elseif ($seconds >= 60) {
		$minutes = floor($seconds / 60);
		$seconds = $seconds - ($minutes * 60);
		}
	}

	if ($return == 'string')
	{
		$string = "";

		if ($months != 0)
			$string .= "$months months, ";
		if ($days != 0)
			$string .= "$days days, ";
		if ($hours != 0)
			$string .= "$hours hours, ";
		if ($minutes != 0)
			$string .= "$minutes minutes, ";
		if ($seconds != 0)
			$string .= "$seconds seconds";
		if ($string == "")
			$string = "0";

	return $string;
	}

	if ($return == "abbr")
	{
		if ($seconds < 10)
			$string = ":0$seconds";
		else
			$string = ":$seconds";

		$string = "$minutes" . $string;

		if ($hours != 0)
			$string = "$hours:" . $string;

		if ($days != 0)
			$string = "$days D " . $string;

		if ($months != 0)
			$string = "$months M " . $string;

		return $string;

	}

	return array('months' => $months, 'days' => $days, 'hours' => $hours, 'minutes' => $minutes, 'seconds' => $seconds);
}

/**
 * fuzzy_seconds_to_time 
 * 
 * @param mixed $seconds 
 * @access public
 * @return void
 */
function fuzzy_seconds_to_time($seconds)
{
	$timearray = seconds_to_time($seconds);

	if ($timearray['months'] != 0)
		return "~{$timearray['months']} months";

	if ($timearray['days'] != 0)
		return "~{$timearray['days']} days";

	if ($timearray['hours'] != 0)
		return "~{$timearray['hours']} hours";

	if ($timearray['minutes'] != 0)
		return "~{$timearray['minutes']} minutes";

	if ($timearray['seconds'] != 0)
		return "{$timearray['seconds']} seconds";
}

/**
 * define_once 
 *
 * use instead of define to insure that the the $name is not being redefined 
 * @param mixed $name 
 * @param mixed $value 
 * @access public
 * @return void
 */
function define_once($name, $value){
	if(!defined($name))
		define($name, $value);
}
?>
