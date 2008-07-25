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
class Storage
{
	function Storage()
	{
		
	}
	
	function saveFile($path, $data)
	{
		trigger_error('abstract method saveFile called');
	}
	
	
	function getFile($inPath)
	{
		trigger_error('abstract method getFile called');
	}
	
	function fileExists($inPath)
	{
		trigger_error('abstract method getFile called');
	}
	
	function saveUploadedFile($newPath, $tempFile)
	{
		if(is_uploaded_file($tempFile))
		{
			$this->importFromFile($newPath, $tempFile);
			
			return true;
		}
		
		return false;
	}	
	
	function importFromFile($path, $filename)
	{
		$data = file_get_contents($filename);
		
		$this->saveFile($path, $data);
	}
	
	function exportToFile($inPath, $filename)
	{
		mkdir_r($filename);
		/*
		
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
		*/
		$data = $this->getFile($inPath);
		
		$handle = fopen($filename, 'w+');

		fwrite($handle, $data);

		fclose($handle);
	}

}

