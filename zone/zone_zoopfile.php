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
		//header('Cache-Control: max-age=86400');
		header('Cache-Control: ');
		header('Pragma: ');
		header('Expires: ');
		header('Last-Modified: ' . $mdate);

		if(isset($headers['If-Modified-Since']))
		{
			if(strtotime($headers['If-Modified-Since']) == $mtime)
			{
				//send 304
				header('Pragma: ', true, 304);
				die();
				//echo_r($headers);
			}
		}
		if(substr(strrchr($jsfile, "."), 1) == 'js')
			header('Content-type: x-application/javascript');// . mime_content_type($jsfile));
		else if(substr(strrchr($jsfile, "."), 1) == 'html')
			header('Content-type: text/html');// . mime_content_type($jsfile));
		header('Content-length: ' . filesize($jsfile));
		include($jsfile);
		die();
		$file = fopen($jsfile, 'rb');
		while(!feof($file)){
			print fread($file, 1024 * 8);
		} // while
	}

	function pageCaptchaImage($inPath)
	{
		$file = app_dir . '/tmp/captcha/' . $inPath[1];
		$mtime = filemtime($file);
 		header("Content-type: image/jpeg");
		include($file);
		die();
	}
	
	function page404($inPath)
	{
		die();
	}
}
?>