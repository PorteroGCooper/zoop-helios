<?php
/**
* Error handling
* 
* This file includes the functions necessary for pretty error handling in zoop.
* 
* @package app
* @subpackage error
*/

function ignore_error($errno, $errstr)
{
	global $_error_ignored;
	if(!isset($_error_ignored[$errno]))
		$_error_ignored[$errno][] = $errstr;
}

function is_ignore_error($errno, $errstr)
{
	if(error_reporting() & $errno !== $errno)
		return true;
	global $_error_ignored;
	if(is_array($_error_ignored))
		foreach($_error_ignored[$errno] as $string)
		{
			if(strpos($errstr, $string) !== false)
				return true;
		}
	return false;
	//return isset($_error_ignored[$errstr]) && $_error_ignored[$errstr] == $errno;
}


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
* print verbose errors.
*/
function error_debug_handler($errno, $errstr, $errfile, $errline, $context, $backtrace = null)
{
	$type = GetErrorType($errno);
	
	if(is_ignore_error($errno, $errstr))
	{
		return;
	}
	
	if($type == 'Unknown')	
		return;
	if($type == 'Strict' && $errstr == 'Assigning the return value of new by reference is deprecated')
		return;
	
	$basedir = dirname(dirname(__file__));
	$errfile = str_replace($basedir, "", $errfile);
	
	if(php_sapi_name() != "cli")
	{
		echo "<table style='font-size: 15px;' cellpadding=\"0\" cellspacing='0' border='0'>";
		echo "<caption><span style='color: #FF1111;'>$type:</span>&nbsp; \"" . nl2br(htmlspecialchars($errstr)) . "\" in file $errfile (on line $errline)</caption>";
		echo "<tr><td>";
	}
	else
	{
		echo("$errstr\r\n");
	}
	
	if ($backtrace === null)
	{
		echo fetch_backtrace();
	}
	else
	{
		echo $backtrace;
	}
	
	if(php_sapi_name() != "cli")
	{
		echo "</td></tr></table>";
	}
}


function error_live_handler($errno, $errstr, $errfile, $errline, $context)
{
	/*
	global $silentErrors;
	if(!is_array($silentErrors))
		$silentErrors = array($silentErrors);
	*/
	$type = GetErrorType($errno);
	
	if($type == 'Unknown')
		return;
	//This error occurs a lot in PEAR classes.
	if($type == 'Strict' && $errstr == 'Assigning the return value of new by reference is deprecated')
		return;

	// SPF made changes to the error handling to provide a better live experience, not throwing errors unless necessary.
	
	// RJL Errors should _always_ be logged, and most errors should be displayed. The point is that most errors should be caught 
	// in live or test mode. Any error that occurs in live mode is an anomaly, behavior is undefined. We'll allow a global var, $silentErrors
	// to define error types that should be ignored...
	if(is_ignore_error($errno, $errstr))
	{
		return;
	}
	$num = LogError($errno, $errstr, $errfile, $errline, $context);
	/*
	if(in_array($type, $silentErrors))
	{
		return;
	}
	*/
	while (ob_get_level() > __zoop_error_ob_start )
	{
		ob_end_clean();
	}
	echo ' <br />
	 		<div align="center">
	 			<table style="width: 50%;  border: solid 1px #000000;" cellspacing="1" align="center">';
	echo  "			<tr align=\"center\">
						<td style=\"background-color: #ffffff; color: #000000; font-weight: bold;\">Application Error $num</td>
					</tr>
					<tr><td>&nbsp;</td></tr>";
	
	echo '			<tr align=\"center\">
						<td align=\"center\">';
							echo 'An error has occurred.  Please report the error code above.';
	echo '				</td>
					</tr>';
	echo '		</table>
			</div>';
	
	die();
}

	
function GetErrorType($errno)
{
	switch($errno)
	{
		case E_ERROR:
		case E_CORE_ERROR:
		case E_COMPILE_ERROR:
		case E_USER_ERROR:
			$errortype = "Fatal Error";
			break;

		case E_WARNING:
		case E_CORE_WARNING:
		case E_COMPILE_WARNING:
		case E_USER_WARNING:
			$errortype = "Warning";
			break;

		case E_NOTICE:
		case E_USER_NOTICE:
			$errortype = "Notice";
			break;
		case E_STRICT:
			$errortype = "Strict";
		default:
			$errortype = "Unknown";
	}
	
	return $errortype;
}
	
	
	

	
function AddMiscErrorInfo()
{
	$info = array();
	if(php_sapi_name() != "cli")
	{
		$info["_SERVER"][] = "REMOTE_ADDR";
		$info["_SERVER"][] = "HTTP_USER_AGENT";
		$info["_SERVER"][] = "HTTP_REFERER";
		$info["_SERVER"][] = "HTTP_COOKIE";
	}
	$info['sGlobals'] = 1;
	$info['sUrls'] = 1;
	
	return $info;
}
	
function GetMiscErrorInfo()
{
	$info = AddMiscErrorInfo();

	$ret = "<table cellpadding='1' cellspacing='0' align='center' width='80%' border='1' style='background-color: #DDDDDD;'>";
	$ret .= "<tr><th>Variable</th><th>Value</th></tr>\n";
	
	foreach ($info as $var => $vals)
	{
		if (!isset($$var))
		{
			global $$var;
		}
		if(!isset($$var))
		{
			//skip it if it isn't yet defined...
			continue;
		}
		if (is_array($$var))
		{
			$src =& $$var;
			if(is_array($vals))
			{
				foreach ($vals as $keyname)
				{

					$ret .= "<tr><td>\n	" . $var . "[{$keyname}]</td><td>\n	" . (isset($src[$keyname]) ? $src[$keyname] : "unset") . "</td></tr>\n";
				}
			}
			else
			{
				foreach($src as $keyname => $val)
				{
					$ret .= "<tr><td>\n	" . $var . "[{$keyname}]</td><td>\n	" . $src[$keyname] . "</td></tr>\n";
				}
			}
		}
		elseif (is_object($$var))
		{
			$src =& $$var;
			$values = get_object_vars($$var);
			foreach ($values as $name => $value)
			{
				$ret .= "<tr><td>\n\t{$var}->{$name}</td><td>\n\t{$value}</td></tr>\n";
			}
		}
	}
	$ret .= "<tr><td>\n\tTime</td><td>\n\t" . date("r") . "</td></tr>\n";
	$ret .= "</table>";
	return $ret;
}
	
	
//	This needs several things fixed in it
//
//	1) It needs to handle the command line differently.  
//		a) Server variables should not be logged
//		b) It should output the error info in all modes. The output should be
//			formateed for the console not html.  Then it should die
//	2) Any variable that is not set throws an error here in our error handler.
//		it should log 'not set' when that happens.
//	3) It should log to a different file everyday, then a cron job should be
//		set up send me an email everyday with the days log file attached
	
function LogError($errno, $errstr, $errfile, $errline, $context)
{
	if(php_sapi_name() != "cli")
	{
		$host = $_SERVER["SERVER_ADDR"];
	}
	else
	{
		$host = '127.0.0.1';
		define('VIRTUAL_URL', $_SERVER["SCRIPT_NAME"]);
	}
	
	list($tmp, $tmp, $tmp, $num) = explode(".", $host);

	$type = GetErrorType($errno);

	$errNum = uniqid($num . ".");	// Get a unique error ID for this
	
	$message = "<strong>Error #$errNum:</strong>
				<p style='font-size: 15px;'>
					<span style='color: #FF1111;'>
						$type
					</span>
					&nbsp;
					\"" . htmlspecialchars($errstr) . "\" in file $errfile (on line $errline)<br />
					<span style='color: #FF1111;'>
						URL:
					</span>
					&nbsp;
					\"" . VIRTUAL_URL . "\"
				</p>
				<span><a href='' onclick='if(document.getElementById(\"$errNum\").style.display==\"\")document.getElementById(\"$errNum\").style.display=\"none\"; else document.getElementById(\"$errNum\").style.display=\"\"; return false;'>Show Details</a></span>
				<span id=\"$errNum\" style='display:none'>
				" . fetch_backtrace() . "<br />" . GetMiscErrorInfo() . "
				</span>
				<hr width='75%' />";
	
	$logFile = LOG_FILE;
	
	$fp = fopen($logFile, "a+");
	fwrite($fp, $message);
	fflush($fp);
	fclose($fp);

	return $errNum;
}

?>
