<?php

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
 * Serve Zoop resource files.
 *
 * @ingroup zone
 */
class zone_zoopfile extends zone {

	function initZone() {
		/**
		 * Disable all output formats other than html.
		 * Leaving .png output enabled, for example, will prevent Zoopfile from
		 * serving any .png files.
		 */
		$this->setAllowableOutput(array('html'));
	}

	protected function getheaders() {
		if (function_exists("getallheaders")) {
			return getallheaders();
		} else {
			foreach($_SERVER as $name => $value) {
				if(substr($name, 0, 5) == 'HTTP_') {
					$headers[substr($name, 5)] = $value;
				}
			}
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
	function pageDefault($inPath) {
	
		array_shift($inPath);
		$module = array_shift($inPath);

		$filename = ZOOP_DIR . '/' . $module . '/public/' . implode('/', $inPath);

		if(file_exists($filename)) {
			$mtime = filemtime($filename);
		} else {
			return $this->responsePage(404);
		}

		$headers = $this->getheaders();
		$mdate = date('l, d M Y H:i:s T', $mtime);

		// guess which header to return...
		header('Content-Type: ' . $this->guessMIMEType($filename));
		
		//header('Cache-Control: max-age=86400');
		header('Cache-Control: ');
		header('Pragma: ');
		header('Expires: ');
		header('Last-Modified: ' . $mdate);

		if(isset($headers['If-Modified-Since'])) {
			if(strtotime($headers['If-Modified-Since']) == $mtime) {
				//send 304, not modified
				header('Pragma: ', true, 304);
				die();
			}
		}

		header('Content-length: ' . filesize($filename));
		
		$this->outputFile($filename);
	}

	function pageCaptchaImage($inPath) {
		$file = APP_DIR . '/tmp/captcha/' . $inPath[1];
		$mtime = filemtime($file);
 		header("Content-Type: image/jpeg");

		$this->outputFile($file);
	}

	/**
	 *
	 */
	function pageImage($inPath) {
		array_shift($inPath);

		$filename = ZOOP_DIR . '/gui/public/images/' . implode('/', $inPath);

		if(file_exists($file)) {
			$mtime = filemtime($filename);
		} else {
			return $this->responsePage(404);
		}

		// guess which content type header to return.
		header('Content-Type: ' . $this->guessMIMEType($filename));

		$this->outputFile($filename);
	}

	/**
	 * Output the given file. Just a wrapper for file_get_contents()
	 *
	 * @param string $file
	 * @return void
	 */
	protected function outputFile($file) {
		print file_get_contents($file);
		exit();
	}
	
	/**
	 * Guess what MIME type to use for the given filename.
	 *
	 * This function first tries the PECL Finfo extension, then tries the old
	 * (deprecated) mime_content_type function. Last, it makes a best guess effort
	 * based on the extension of the filename.
	 *
	 * @param string $filename
	 * @return string Best guess at file's MIME type.
	 * @author Justin Hileman {@link: http://justinhileman.com}
	 */
	protected function guessMIMEType($filename) {
		
		// look for the PECL extension first.
		if (Config::get('zoop.zone.zoopfile.use_finfo_extension') && function_exists('finfo_file')) {
			return finfo_file(finfo_open(FILEINFO_MIME), $filename);
		}
		
		// then try the old mime type function.
		if (function_exists('mime_content_type')) {
			return mime_content_type($filename);
		}
	
		// then make an outright guess based on the file extension.
		$fileSuffix = array_pop(explode('.', $filename));
		switch (strtolower($fileSuffix)) {
			case "js":
				return "application/x-javascript";
				break;
	
			case "json":
				return "application/json";
				break;
	
			case "jpg":
			case "jpeg":
			case "jpe":
				return "image/jpg";
				break;
	
			case "png":
			case "gif":
			case "bmp":
			case "tiff":
				return "image/" . strtolower($fileSuffix);
				break;
	
			case "css":
				return "text/css";
				break;
	
			case "xml":
				return "application/xml";
				break;
	
			case "doc":
			case "docx":
				return "application/msword";
				break;
	
			case "xls":
			case "xlt":
			case "xlm":
			case "xld":
			case "xla":
			case "xlc":
			case "xlw":
			case "xll":
				return "application/vnd.ms-excel";
				break;
	
			case "ppt":
			case "pps":
				return "application/vnd.ms-powerpoint";
				break;
	
			case "rtf":
				return "application/rtf";
				break;
	
			case "pdf":
				return "application/pdf";
				break;
	
			case "html":
			case "htm":
			case "php":
				return "text/html";
				break;
	
			case "txt":
				return "text/plain";
				break;
	
			case "mpeg":
			case "mpg":
			case "mpe":
				return "video/mpeg";
				break;
	
			case "mp3":
				return "audio/mpeg3";
				break;
	
			case "wav":
				return "audio/wav";
				break;
	
			case "aiff":
			case "aif":
				return "audio/aiff";
				break;
	
			case "avi":
				return "video/msvideo";
				break;
	
			case "wmv":
				return "video/x-ms-wmv";
				break;
	
			case "mov":
				return "video/quicktime";
				break;
	
			case "zip":
				return "application/zip";
				break;
	
			case "tar":
				return "application/x-tar";
				break;
	
			case "swf":
				return "application/x-shockwave-flash";
				break;
				
			default:
				return "unknown/" . trim($fileSuffix);
				break;
		}
	}
	
}