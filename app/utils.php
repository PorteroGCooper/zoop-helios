<?php

/**
 * Utilities file
 *
 * @group app
 * @group utils
 * @todo split utils.php into logical subgroup files.
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
 * write to a log
 *
 * Append $content to $filename.
 *
 * @param string $content message to be written
 * @param string $filename filename to write to
 */
function logwrite($content, $filename = '/tmp/phplog') {
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
 * Redirect to an URL
 *
 * Redirect to $URL using method $redirectType and terminate the program.
 * Optionally return an HTTP response code:
 * - 301 Moved Permanently (probably should be default...)
 * - 302 Found (default value, don't need to specify)
 * - 303 See Other
 * - 307 Temporary Redirect
 *
 * @param string $URL Full url, http://example.com
 * @param integer $redirectType possible values are {@link HEADER_REDIRECT} and {@link JS_REDIRECT}
 * @param integer $response_code Optional response HTTP code to return. 
 */
function Redirect( $URL, $redirectType = HEADER_REDIRECT, $response_code = null) {
	global $globalTime;
	switch ($redirectType) {
		case HEADER_REDIRECT:
			if ($response_code) {
				header("location: $URL", TRUE, $response_code);
			}
			else {
				header("location: $URL");
			}
			break;
		case JS_REDIRECT:
			echo("<script language=\"JavaScript\" type=\"text/javascript\">top.location.href = \"$URL\";</script>");
			break;
		default:
			trigger_error("unknown redirect type");
			break;
	}
	logprofile($globalTime);
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
function RedirectBoS($redirectType = HEADER_REDIRECT) {
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
function BaseRedirect( $URL , $redirectType = HEADER_REDIRECT) {
	Redirect(SCRIPT_URL . $URL, $redirectType);
}

/**
* redirect to the referring page
*
* Redirect to HTTP_REFERER
*
* @uses Redirect
*/
function RedirectRef() {
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
function ZoneRedirect( $url, $depth = 0 ) {
	Redirect( zone::getZoneUrl($depth) . $url);
}


/**
 * checkValidDate
 *
 * @param mixed $datestring
 * @access public
 * @return void
 */
function checkValidDate( $datestring ) {
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

/**
 * FormatPostgresDate
 *
 * @param mixed $inPostgresDate
 * @param mixed $inFormatString
 * @param mixed $inTimeZone
 * @access public
 * @return void
 */
function FormatPostgresDate( $inPostgresDate, $inFormatString, $inTimeZone = null) {
	return sql_format_date($inPostgresDate, $inFormatString, $inTimeZone);
}


/**
* prints 48856665XXXX5421 as 4885-6665-XXXX-5421
*
* @param string $ccn Credit Card Number to Format
* @return string Formatted Credit Card Number
*/
function formatCCN( $ccn ) {
	$output = substr($ccn, 0, 4) . "-" . substr($ccn, 4, 4) . "-" . substr($ccn, 8, 4) . "-" . substr($ccn, 12, 4);

	return $output;
}

/**
* accepts an array and a function, then processes all values recursively with the function.
*
* @param array $arr
* @param function $function
*/
function processArray( $arr, $function ) {
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
function xorEncrypt($message, $key) {
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
function xorDecrypt($message, $key) {
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
function echo_r($mixed) {
// 	if(APP_STATUS == "live")
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
function die_r($mixed) {
	echo_r($mixed);
	die();
}
/**
* simply puts "<pre>" tags around the var_dump call so the formatting looks good in a browser.
*
* @param mixed $mixed
*/
function dump_r($mixed) {
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
function show_r($mixed, $color = "blue") {
	echo "<div align=\"left\" style=\"border: 1px solid $color;\">";
	echo_r($mixed);
	echo "</div>";
}
/**
*
* @access public
* @return void
**/
function fetch_r($mixed) {
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
function &MapArray(&$transformee, &$transformer) {
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
 * Return the last (or nth) element in an array.
 * 
 * @access public
 * @param array $stack 
 * @param int $where (optional, defaults to last index)
 * @return void
 */
function array_peek($stack, $where = null) {
	$cnt = count($stack);
	if ($cnt==0) return null;
	
	if ($where === null) $where = $cnt - 1;
	
	if ($where >= $cnt || $where < 0) $where = $cnt - 1;
	
	return $stack[$where];
}

/**
 * validEmailAddress
 *
 * @param mixed $email
 * @access public
 * @return void
 */
function validEmailAddress($email) {
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
function getmicrotime() {
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
function markprofile($running_total = 0) {
	if(APP_STATUS == 'dev')
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
function fetch_backtrace($full = false) {
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
function array_sortonkeys($inArray, $forward = 1) {
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
function BUG($desc = "") {
	if (show_warnings == false)
		return;

	if(APP_STATUS == "dev")
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
 * Alert developers about a deprecated call.
 *
 * An application with APP_STATUS set to live, or show warnings config set to false
 * will suppress deprecated() messages.
 *
 * @param string $desc
 * @access public
 * @return void
 */
function deprecated($desc = null) {
	if(Config::get('zoop.app.security.show_warnings', true) == false) return;
	
	if(APP_STATUS == 'dev') {
		$functioninfo = debug_backtrace();
		print "<p>Deprecated functionality in <strong>" . $functioninfo[0]["file"] . "</strong>";
		print " on line <strong>" . $functioninfo[0]["line"] . "</strong>";
		print " in function <strong>" . $functioninfo[1]["function"] . "</strong></p>";
		if ($desc) print "<p>Description: <strong>$desc</strong></p>";
	}
}

/**
 * echo_backtrace
 *
 * @param mixed $full
 * @access public
 * @return void
 */
function echo_backtrace($full = false) {
	echo fetch_backtrace($full);
}

/**
* A better stripslashes function. This one checks to see if magic_quotes_gpc is on
* and stripsslashes only when it is on.
* Also works on Arrays
*
* @author ferik100 at flexis dot com dot br posted to php.net (public domain)
* @param string $str
* @return string
*/
function strip_gpc_slashes ($input) {
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
function __VerifyHTMLTree($html) {
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
function __VerifyHTMLTree_ex(&$htmltree) {
	$allowed_tags = Config::Get('zoop.app.security.allowed_tags');
	$allowed_attributes = Config::Get('zoop.app.security.allowed_attributes');

	foreach($htmltree->children as $key => $childtree) {
		if(in_array($htmltree->children[$key]->name, $allowed_tags)) {
			__VerifyHTMLTree_ex($htmltree->children[$key]);
		} else {
			unset($htmltree->children[$key]);
		}
	}

	foreach($htmltree->attributes as $key => $value) {
		if(!in_array($key, $allowed_attributes)) {
			unset($htmltree->attributes[$key]);
		} else {
			if(stristr("javascript", $value)) {
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
function VerifyText($inText) {
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
   $text = str_replace("<br />", "\r\n", $text);
   $text = str_replace("<br>", "\r\n", $text);
   return $text;
}


/**
 * VerifyTextOrArray
 *
 * @param mixed $array
 * @access public
 * @return void
 */
function VerifyTextOrArray($array) {
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
 * get_shared_key
 *
 * reads a key(for authentication between apps) from the shared key file.
 * @access public
 * @return void
 */
function get_shared_key() {
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
function get_key($type) {
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
function RequireCondition($bool) {
	if(!$bool)
	{
		if(defined("app_login_page"))
		{
			//should dev be redirected?
			if(APP_STATUS != 'dev')
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
function remoteObjectCall($url, $object, $constparams, $method, $methodparams) {
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
function file_set_contents($inFilename, $inContents, $mode = 'w') {
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
function append_to_file($inFilename, $inContents) {
	file_set_contents($inFilename, $inContents, 'a');
}


/**
 * Opens / Creates a file ($inFilename) and dumps the value of an array, variable or object into it.
 * Useful for debugging purposes when doing operations that cannot write to the screen (like handling ajax posts).
 *
 * @param       string   $inFilename    	The absolute location of the file
 * @param       mixed    $inValue    	The variable to dump into to the file
 * @param       string   $mode    		Append mode, defaults to 'a' (open and append to the bottom)
 */
function dump_to_file($inFilename, $inValue, $mode = 'a') {
	$string = var_dump_ret($inValue);
	file_set_contents($inFilename, $string, $mode);
}

/**
 * Returns the output from var_dump to a string instead of to the screen.
 *
 * @param mixed $inValue The variable to dump.
 */
function var_dump_ret($mixed) {
	ob_start();
	var_dump($mixed);
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}


/**
 * Opens / Creates a file ($inFilename) and all necessary directories to it, then places $inContents into it
 *
 * @param       string   $inFilename    	The absolute location of the file
 * @param       string   $inContents    	The contents to put into file
 * @param       string   $mode    		Write mode, defaults to 'w' (open and write at top)
 */
function file_write($inFilename, $inContents, $mode = 'w') {
	if (mkdir_r($inFilename))
		file_set_contents($inFilename, $inContents, $mode);
}

/**
 * Deletes all files from a directory last modified longer than $seconds ago.
 *
 * @param string $path The directory
 * @param string $seconds number of seconds old
 */
function CleanDirectory($path, $seconds) {
	if($handle = opendir($path))
	{
		$ts = time();
		while($file = readdir($handle))
		{
			if($file != "." && $file != ".." && filemtime($path ."/$file") < ($ts - $seconds))
			{
 				unlink($path.$file);
			}
		}
		closedir($handle);
		return true;
	}
	else
		return false;
}

/**
 * Apply an Encryption on Input
 * To be used with Decrypt
 *
 * @param string $key The key to encrypt $input with
 * @param string $input The value to encrypt
 * @return string The encrypted data
 */
function Encrypt($key, $input) {
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
function Decrypt($key, $input) {
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
function RunCommand($inCommand) {
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
function SetCompletionStatus($statusItemName, $start = NULL, $end = NULL, $goodEnd = NULL) {
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
function &GetCompletionStatus($statusItemName) {
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
 * Create directories required for $filename recursively
 * using mkdirr.
 *
 * @param       string   $pathname    The filename you want to create a directory for.
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function mkdir_r($filename, $mode = 0770) {
	str_replace("\\",'/', $filename);
	$dir = explode(DIRECTORY_SEPARATOR, $filename);
	array_pop($dir);
	$path = implode(DIRECTORY_SEPARATOR, $dir);

	return mkdirr($path, $mode);
}

/**
 * Create a directory structure recursively
 *
 * @author      Aidan Lister <aidan@php.net>
 * @author      Steve Francia --- added support for if is already a symlink
 * @version     1.1
 * @link        http://aidanlister.com/repos/v/function.mkdirr.php
 * @param       string   $pathname    The directory structure to create
 * @return      bool     Returns TRUE on success, FALSE on failure
 */
function mkdirr($pathname, $mode = 0770) {

	// eliminate the trailing slash
	if (substr($pathname, -1) == "/")
		$pathname = substr($pathname, 0, -1);

	// Check if directory or symlink already exists
	if (is_dir($pathname) || empty($pathname) ) {
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
function HexToRgb($pHexColor) {

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
 * AccentTranscribe
 * Transcribes accents and umlauts, but also ligatures and runes known to ISO-8859-1
 *
 * @author sven schwyn
 * @param  string
 * @access public
 * @return string
 */
function accentTranscribe ($string) {
   $string = strtr($string,
       "\xA1\xAA\xBA\xBF\xC0\xC1\xC2\xC3\xC5\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD8\xD9\xDA\xDB\xDD\xE0\xE1\xE2\xE3\xE5\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF8\xF9\xFA\xFB\xFD\xFF",
       "!ao?AAAAACEEEEIIIIDNOOOOOUUUYaaaaaceeeeiiiidnooooouuuyy");
   $string = strtr($string, array("\xC4"=>"Ae", "\xC6"=>"AE", "\xD6"=>"Oe", "\xDC"=>"Ue", "\xDE"=>"TH", "\xDF"=>"ss", "\xE4"=>"ae", "\xE6"=>"ae", "\xF6"=>"oe", "\xFC"=>"ue", "\xFE"=>"th"));
   return($string);
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
function StreamCSV($inData, $inFilename, $inColumns = NULL) {
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
function randElement($inArray) {
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
function randElements($inArray, $num) {
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
function mrand($l,$h,$t,$len=false) {

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
* unserialize with some empty handling
*
* @param string $inString
*/
function unserializer($inString) {
	if (empty($inString) || is_null($inString))
		return array();
	else
		return unserialize($inString);
}

/**
* convert html to fairly readable text
*
* @param string $inHTML
*/
function HTML2Txt($inHTML) {
	$txt = br2nl($inHTML);

	return strip_tags($txt);
}


/**
 * seconds_to_time
 *
 * @param mixed $seconds
 * @param string $return
 * @access public
 * @return void
 */
function seconds_to_time($seconds, $return = "array") {
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
function fuzzy_seconds_to_time($seconds) {
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
 * rmrf  essentially works like rm -rf, recursively deletes all files and directories.
 *
 * @param mixed $seconds
 * @access public
 * @return void
 */
function rmrf($dir) {
	$d = dir($dir);
	$dir .= "/";
	while($f = $d->read() ){
		if($f != "." && $f != "..")
		{
			if(is_dir($dir.$f))
			{
				rmrf($dir.$f."/");
				rmdir($dir.$f);
			}
			if(is_file($dir.$f))
				unlink($dir.$f);
		}
	}
	$d->close();
}

//	give it the center, width and height to define an ellipse
//	then give it an angle and it will give you the x and y coordinates
//	the angle that it uses is not the actual angle that that will be drawn
//	The angle used is the same as would be drawn by the imagearc and image 
//		filled arc functions given the same angle.  The idea is that you 
//		take the angle given and map it to a circle whose diameter is the
//		same length the major axis of the ellipse.  Then you rotate the 
//		resulting circle along the horizontal axis.  Looking straight onto
//		the circle it becomes an ellipse as it is rotated.  Wherever the 
//		x and y from the original angle now appear to be on the 2D plane is where 
//		the new x and y are on the ellipse.  That x and y are what this function returns
//	Use this function to figure out the coordinates for pie slices generated with the 
//		imagearc and imagefilledarc functions as the actual angle it draws is NOT the actual
//		angle of the lines drawn on the screen

function EllipseCirclePos($cx, $cy, $w, $h, $theta, &$newx, &$newy) {
	if($w > $h)
	{
		$a = $w / 2;
		$b = $h / 2;
	}
	else
	{
		$b = $w / 2;
		$a = $h / 2;
	}

	//	$normalTheta = ($theta + (floor((abs($theta) + 360) / 360) * 360)) % 360;

	$ct = cos(deg2rad($theta));
	$st = sin(deg2rad($theta));

	//	From the parametric definition of the ellipse
	$newx = $a * $ct;
	$newy = $b * $st;
	
	//	This is if you think of it as a rotated circle and uses the definition of the ellipse
	//		as (($x*$x)/($a*$a)) + (($y*$y)/($b*$b)) = 1
	//	It gives the same results as the parametric equations above
	//$newx = $a * $ct;
	//$newy = sqrt( ( 1 - (($newx*$newx)/($a * $a)) ) * $b * $b);
	
	//
	//	These are the x and y that would be returned if we wanted the actual angle given to
	//		coorespond to the ellipse that is drawn and not to an imaginary circle that has
	//		a diamater matching the ellipses' major axis.
	//
	//$newx = sqrt(($b*$b*$ct*$ct)/(1 - $ct*$ct + (($b*$b*$ct*$ct)/($a*$a))));
	//$newy = sqrt(($a*$a*$st*$st)/(1 - $st*$st + (($a*$a*$st*$st)/($b*$b))));
	
	
	//if($normalTheta > 90 && $normalTheta < 270)
	//	$newx *= -1;
	
	//if($normalTheta > 180)
	//	$newy *= -1;

	$newx = round($newx);
	$newy = round($newy);

	$newx += $cx;
	$newy += $cy;
}

function NormalizeAngle($theta) {
	return ($theta + (floor((abs($theta) + 360) / 360) * 360)) % 360;
}

function NormalizeAngle2($theta) {
	if($theta >= 0 && $theta <= 360)
		return $theta;
	
	if($theta < 0)
	{
		$offCycles = floor((abs($theta) + 360) / 360);
		$theta += $offCycles * 360;
	}
	else
	{
		$offCycles = floor($theta / 360);
		$theta -= $offCycles * 360;
	}
	
	return $theta;
}

function logprofile(&$timestruct, $sql = false) {
	if(!defined('logprofile') || logprofile == false)
		return;
	if(!is_array($timestruct))
	{
		$timestruct['starttime'] = getmicrotime();
		$timestruct['sqltotal'] = 0;
		$timestruct['longestquerytime'] = 0;
		$timestruct['longestquery'] = '';
		$timestruct['longestquerycaller'] = '';
		$timestruct['sqlcount'] = 0;
	}
	else if($sql)
	{
		if(isset($timestruct['sqlstart']))
		{
			$currtime = getmicrotime();
			$length = $currtime - $timestruct['sqlstart'];
			unset($timestruct['sqlstart']);
			$timestruct['sqltotal'] += $length;
			$timestruct['sqlcount']++;
			$bt = debug_backtrace();
			foreach($bt as $step)
			{
				if($step['function'] != 'logprofile' && (!isset($step['class']) || $step['class'] != 'database'))
				{
					if(isset($step['file']))
						$caller = $step['file'] . ' @ line ' . $step['line'];
					else
						$caller = $step['class'] . '->' . $step['function'] . " file unknown(php doesn't know)";
					break;
				}
			}
			if($timestruct['longestquerytime'] < $length)
			{
				$timestruct['longestquerytime'] = $length;
				$timestruct['longestquery'] = $sql;
				$timestruct['longestquerycaller'] = $caller;
			}
			if(defined('logallqueries') && logallqueries != false)
			{
				$query['time'] = $length;
				$query['sql'] = $sql;
				$query['caller'] = $caller;
				$timestruct['queries'][] = $query;
			}
		}
		else
		{
			$timestruct['sqlstart'] = getmicrotime();
		}
	}
	else
	{
		$currtime = getmicrotime();
		$timestruct['totaltime'] = $currtime - $timestruct['starttime'];
		//log it to the file...
		if(logprofile == 'print')
		{
			if($_SERVER['REQUEST_METHOD'] != 'POST')
			{
				echo_r(implode('/', $GLOBALS['logpath']));
				echo_r($timestruct);
			}
		}
		else
		{
			$queryId = uniqid('query_');
			//add the .0 just in case the starttime fell on an even second...
			$time = explode('.', $timestruct['starttime'] . '.0');
			$ms = $time[1];
			$time = $time[0];
			$time = gmstrftime('%D %T.', $time) . $ms;
			$line[] = $time;
			$line[] = implode('/', $GLOBALS['logpath']);
			$line[] = $timestruct['sqlcount'];
			$line[] = $timestruct['totaltime'];
			$line[] = $timestruct['sqltotal'];
			$line[] = $queryId;
			$line[] = $timestruct['longestquerycaller'];
			$line[] = $timestruct['longestquerytime'];
			$file = fopen(logprofile . '/profile.log', 'a+');
			fwrite($file, '"' . implode("\",\t\"", $line) . '"' . "\n");
			fclose($file);
			$file = fopen(logprofile . '/longest.log', 'a+');
			fwrite($file, '"' . $queryId . '",	"' . $timestruct['longestquery'] . '"' . "\n");
			fclose($file);
			if(defined('logallqueries') && logallqueries != false)
			{
				$file = fopen(logprofile . '/queries.log', 'a+');
				foreach($timestruct['queries'] as $query)
				{
					$line = array();
					$line[] = $time;
					$line[] = $query['time'];
					$line[] = $query['sql'];
					$line[] = $query['caller'];
					fwrite($file, '"' . implode("\",\t\"", $line) . '"' . "\n");
				}
				fclose($file);
			}	
		}
	}
}

/**
 * take an array of directories and join them 
 * to form a valid path -- allows for trailing slashes
 *
 * @param array $dirs
 * @access public
 * @return string the full path with trailing slash
 */
function join_dirs($dirs) {
	foreach($dirs as $k=>$dir)
	{ // strip off the slashes except for the first (root) slash
		$dirs[$k] = ereg_replace ( ($k==0)?"\/$":"(^\/|\/$)", "" , $dir );
	}
	return implode(DIRECTORY_SEPARATOR, $dirs).DIRECTORY_SEPARATOR;
}

/**
 * Take zone path, zone params, page name and page params and construct a full path
 *
 * @param string $zone the zone path
 * @param array $z the array of zone parameters (optional)
 * @param string $page the page name (optional)
 * @param array $p the array of page parameters (optional)
 * @access public
 * @return string the full path
 */
function makePath( $zone, $z = array() , $page = '', $p = array() ) {
	$answer = "/$zone";
	foreach ($z as $key => $value) {
		$answer .= "/$key:$value";
	}
	$answer .= "/$page";
	foreach ($p as $key => $value) {
		$answer .= "/$key:$value";
	}
	return $answer;
}

if (!function_exists('json_encode')) {
	function json_encode($a=false) {
		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';
		if (is_scalar($a))
		{
			if (is_float($a))
			{
				// Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}

			if (is_string($a))
			{
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			}
			else
				return $a;
		}
		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a))
		{
			if (key($a) !== $i)
			{
				$isList = false;
				break;
			}
		}
		$result = array();
		if ($isList)
		{
			foreach ($a as $v) $result[] = json_encode($v);
			return '[' . join(',', $result) . ']';
		}
		else
		{
			foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
			return '{' . join(',', $result) . '}';
		}
	}
}


if (!function_exists('com_create_guid')) {
	function com_create_guid() {
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);// "}"
        return $uuid;
    }
}



/**
 * URL canonicalization function.
 *
 * Every url should be passed through this or the Smarty {$my_path|url} display filter.
 *
 * Optionally, specify whether to use an absolute url (if left blank, will use the app default).
 * 
 * @author Justin Hileman {@link http://justinhileman.com}
 * @access public
 * @param string $url
 * @param bool $use_absolute Use an absolute (fully qualified) url?
 * @return string Canonicalized url.
 */
function url($url, $use_absolute = null) {
	if ($use_absolute === null) {
		$use_absolute = Config::get('zoop.app.use_absolute_urls');
	}

	// If we're not rewriting urls, or if it's already an absolute url, just return it.
	if (!Config::get('zoop.app.canonicalize_urls') || strpos($url, '://') !== false) return $url;

	$base = base_href();
	if (strpos($url, $base) === 0) {
		// make sure each url is only canonicalized once...
		$url = substr($url, strlen($base));
	} else if (empty($url)) {
		$url = zone::getZoneBasePath() . '/' . $url;
	} else {
		switch ($url[0]) {
			case '?':
				// should prepend the zone path + page path + page params here...
				if ($use_absolute) {
					trigger_error('problems with url queries and absolute urls... fix this soon.');
					return base_href(true) . getPath() . $url;
				} else {
					return $url;
				}
				break;
			case '#':
				// this is just a fragment... return either the fragment or the base href plus fragment.
				if ($use_absolute) {
					trigger_error('problems with url fragments and absolute urls... fix this soon.');
					return base_href(true) . getPath() . $url;
				} else {
					return $url;
				}
				break;
			case '/':
				break;
			default:
				// tack on a zone path if this isn't a query, a fragment, or absolute from base.
				// THIS SHOULD BE a path relative to the zone base path, not the zone path with params.
				$url = zone::getZoneBasePath() . '/' . $url;
				break;
		}
	} 

	if ($use_absolute) {
		return base_href(true) . $url;
	} else {
		return $base . $url;
	}
}

/**
 * Base href helper function for URL canonicalization.
 *
 * @todo What's the difference between SCRIPT_REF and BASE_HREF ?
 *
 * @author Justin Hileman {@link http://justinhileman.com}
 * @param bool $absolute Do you want a fully qualified base href (http://domain and all)?
 * @return string Base href. Use it wisely.
 */
function base_href($absolute = false) {
	// return the whole thing for an absolute url.
	if ($absolute) {
		return BASE_HREF;
	} else {
		// the non-'mod_rewrite' case should be handled here?
		$parts = parse_url(BASE_HREF);
		return $parts['path'];
	}
}

/**
 * Convert a DB column key (or just about anything else) into a decent label.
 *
 * @author Justin Hileman {@link http://justinhileman.com}
 * @see nv_title_case()
 * @param string $str Label to convert
 * @return string Formatted form label
 */
function format_label ($str) {
	$str = str_replace(array('_', '-'), array(' ', ' '), $str);
	$str = preg_replace('#(?<=[a-z])([A-Z])#', ' $1', $str);
	return nv_title_case($str);
}

/**
 * Convert to title case.
 *
 * Attempts to properly capitalize post titles. Based on
 * {@link http://daringfireball.net/2008/05/title_case code by John Gruber}.
 *
 * @version 1.1
 * @author Adam Nolley {@link http://nanovivid.com/}
 *
 * @param string $str String to titlecaseify
 * @return string Input string converted to title case
 */
function nv_title_case($str) {
	
	// Edit this list to change what words should be lowercase
	$small_words = "a an and as at but by en for if in of on or the to v[.]? via vs[.]?";
	$small_re = str_replace(" ", "|", $small_words);
	
	// Replace HTML entities for spaces and record their old positions
	$htmlspaces = "/&nbsp;|&#160;|&#32;/";
	$oldspaces = array();
	preg_match_all($htmlspaces, $str, $oldspaces, PREG_OFFSET_CAPTURE);
	
	// Remove HTML space entities
	$words = preg_replace($htmlspaces, " ", $str);
	
	// Split around sentance divider-ish stuff
	$words = preg_split('/( [:.;?!][ ] | (?:[ ]|^)["–])/x', $words, -1, PREG_SPLIT_DELIM_CAPTURE);
	
	for ($i = 0; $i < count($words); $i++) {
		
		// Skip words with dots in them like del.icio.us
		$words[$i] = preg_replace_callback('/\b([[:alpha:]][[:lower:].\'ê(&\#8217;)]*)\b/x', 'nv_title_skip_dotted', $words[$i]);
		
		// Lowercase our list of small words
		$words[$i] = preg_replace("/\b($small_re)\b/ei", "strtolower(\"$1\")", $words[$i]);
		
		// If the first word in the title is a small word, capitalize it
		$words[$i] = preg_replace("/\A([[:punct:]]*)($small_re)\b/e", "\"$1\" . ucfirst(\"$2\")", $words[$i]);
		
		// If the last word in the title is a small word, capitalize it
		$words[$i] = preg_replace("/\b($small_re)([[:punct:]]*)\Z/e", "ucfirst(\"$1\") . \"$2\"", $words[$i]);
	}
	
	$words = join($words);
	
	// Oddities
	$words = preg_replace("/ V(s?)\. /i", " v$1. ", $words);						// v, vs, v., and vs.
	$words = preg_replace("/(['ê]|&#8217;)S\b/i", "$1s", $words);					// 's
	$words = preg_replace("/\b(AT&T|Q&A)\b/ie", "strtoupper(\"$1\")", $words);		// AT&T and Q&A
	$words = preg_replace("/-ing\b/i", "-ing", $words);								// -ing
	$words = preg_replace("/(&[[:alpha:]]+;)/Ue", "strtolower(\"$1\")", $words);	// html entities
	
	// Put HTML space entities back
	$offset = 0;
	for ($i = 0; $i < count($oldspaces[0]); $i++) {
		$offset = $oldspaces[0][$i][1];
		$words = substr($words, 0, $offset) . $oldspaces[0][$i][0] . substr($words, $offset + 1);
		$offset += strlen($oldspaces[0][$i][0]);
	}
	
	return $words;
}
function nv_title_skip_dotted($matches) {
	return preg_match('/[[:alpha:]] [.] [[:alpha:]]/x', $matches[0]) ? $matches[0] : ucfirst($matches[0]);
}


/**
 *  Addin lcfirst in builds where it is not yet present
 */
if ( false === function_exists('lcfirst') ) {
	function lcfirst( $str ) { 
		return (string)(strtolower(substr($str,0,1)).substr($str,1));
	}
}

/**
 * @todo If we're removing php 4 support, the following should be removed and utils5 should be combined with this file.
 */
if(version_compare(PHP_VERSION, '5.0', '<')) {
	include_once(dirname(__FILE__) . '/utils4.php');
} else {
	include_once(dirname(__FILE__) . '/utils5.php');
}
