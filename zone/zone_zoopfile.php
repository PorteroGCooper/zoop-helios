<?php
/**
* serve zoop javascript files
*
* @package zone
*/
class zone_zoopfile extends zone
{

	function pageDefault($inPath)
	{
		array_shift($inPath);
		$module = array_shift($inPath);
		$jsfile = zoop_dir . '/' . $module . '/public/' . implode('/', $inPath);
		$mtime = filemtime($jsfile);
		$headers = getallheaders();
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
		header('Content-type: ' . mime_content_type($jsfile));
		header('Content-length: ' . filesize($jsfile));
		include($jsfile);
		die();
		$file = fopen($jsfile, 'rb');
		while(!feof($file)){
			print fread($file, 1024 * 8);
		} // while
	}
}

