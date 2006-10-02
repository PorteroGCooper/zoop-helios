<?php
/**
* @category zoop
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

/**
* serve zoop javascript files
*
* @package zone
*/
class zone_zoopfile extends zone
{

	function getheaders()
	{
		if (function_exists("getallheaders"))
		{
			return getallheaders();
		}
		else
		{
		   foreach($_SERVER as $name => $value)
		       if(substr($name, 0, 5) == 'HTTP_')
		           $headers[substr($name, 5)] = $value;
		   return $headers;
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
		array_shift($inPath);
		$module = array_shift($inPath);

		$jsfile = zoop_dir . '/' . $module . '/public/' . implode('/', $inPath);

		if(file_exists($jsfile))
			$mtime = filemtime($jsfile);
		else
			return $this->page404($inPath);

		$headers = $this->getheaders();
		$mdate = date('l, d M Y H:i:s T', $mtime);

		switch (strtolower(substr(strrchr($jsfile, "."), 1)))
		{
			case 'js':
				header('Content-Type: x-application/javascript');
				break;
			case 'html' :
				header('Content-Type: text/html');
				break;
			case 'css' :
				header('Content-Type: text/css');
				break;
			default:
				if(function_exists('mime_content_type'))
					header('Content-Type: ' . mime_content_type($jsfile));
		}

		//header('Cache-Control: max-age=86400');
		header('Cache-Control: ');
		header('Pragma: ');
		header('Expires: ');
		header('Last-Modified: ' . $mdate);

		if(isset($headers['If-Modified-Since']))
		{
			if(strtotime($headers['If-Modified-Since']) == $mtime)
			{
				//send 304, not modified
				header('Pragma: ', true, 304);
				die();
				//echo_r($headers);
			}
		}

		header('Content-length: ' . filesize($jsfile));
		$this->outputFile($jsfile);
// 		include($jsfile);
// 		die();
	}

	function pageCaptchaImage($inPath)
	{
		$file = app_dir . '/tmp/captcha/' . $inPath[1];
		$mtime = filemtime($file);
 		header("Content-Type: image/jpeg");

		$this->outputFile($file);
// 		include($file);
// 		die();
	}

	function pageImage($inPath)
	{
		array_shift($inPath);

		$file = zoop_dir . '/gui/public/images/' . implode('/', $inPath);

		if(file_exists($file))
			$mtime = filemtime($file);
		else
			die();

		switch (substr(strrchr(strtolower($file), "."), 1))
		{
			case "gif" :
 				header("Content-Type: image/gif");
				break;
			case "jpg" :
			case "jpeg":
 				header("Content-Type: image/jpeg");
				break;
			case "png":
  				header("Content-Type: image/png");
				break;
		}

		$this->outputFile($file);
	}

	function page404($inPath)
	{
		die();
	}

	function outputFile($file)
	{
		$hfile = fopen($file, 'rb');
		while(!feof($hfile)){
			print fread($hfile, 1024 * 8);
		} // while
		die();
	}
}
?>
