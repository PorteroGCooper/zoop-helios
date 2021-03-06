<?php
/**
* @package storage
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
* @package storage
*/
class FileStorage extends Storage
{
	function FileStorage($basePath = null)
	{
		if ($basePath === null) {
			$basePath = Config::get('zoop.storage.filestorage_root');
		}
		$this->basePath = $basePath;
	}
	
	function fileExists($path)
	{		
		return file_exists($this->basePath . $path);		
	}

	
	function saveFile($path, $data)
	{
		//$dir[] = basename($path);
		mkdir_r($this->basePath . $path);
		$file = fopen($this->basePath . $path, 'wb');
		fwrite($file, $data);
		fclose($file);
	}
	
	function saveUploadedFile($newPath, $tempFile)
	{
		if(is_uploaded_file($tempFile))
		{
			mkdir_r($this->basePath . $newPath);
			if(!move_uploaded_file($tempFile, $this->basePath . $newPath))
				trigger_error("Could not save uploaded file");
			return true;
		}
		
		return false;
	}	
	
	
	function getFile($path)
	{
		return file_get_contents($this->basePath . $path);
	}
	
	function listDir($path)
	{
		$answer = array();
		$path = $this->basePath . $path;
		if(is_dir($path))
		{
			$dir = opendir($path);
			while(($file = readdir($dir)) !== false)
			{
				if($file[0] != '.')
					$answer[] = $file;
			}
			return $answer;
		}
		else 
			return array();
	}
}

