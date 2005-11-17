<?
/**
* @package storage
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
* @package storage
*/
	class userfiledb extends VFS_sql
	{
		function userfiledb()
		{
			global $defaultdb;
			if(!userfile_separate)
			{
				$options = $defaultdb->getDSN();
			}
			else
			{
				$options["phptype"] = userfile_type;
				$options["hostspec"] = userfile_server;
				$options["protocol"] = "tcp";
				$options["username"] = userfile_username;
				$options["password"] = userfile_password;
				$options["database"] = userfile_database;
			}
			$options["table"] = "file";
			
			VFS::VFS($options);
			
		}
		
		
		function exists($inDir, $inFilename)
		{
			
			
			$conn = $this->_connect();
	        
			if (is_a($conn, 'PEAR_Error'))
			{
	            return $conn;
	        }
			
			$exists = $this->_db->getOne("SELECT vfs_id FROM file WHERE vfs_path = '$inDir' AND vfs_name = '$inFilename'");
			
			return $exists;
		}
		
	}
	
	
	function db_move_uploaded_file($inTempFile, $inDir, $inFilename)
	{
		if(is_uploaded_file($inTempFile))
		{
			$userfile = &new userfiledb();
			
			$userfile->write($inDir, $inFilename, $inTempFile, true);
			
			return true;
		}
		
		return false;
	}
	
	
	function ImportImage($inImageFile, $inDir, $inFilename)
	{
		$userfile = &new userfiledb();
		
		$userfile->write($inDir, $inFilename, $inImageFile, true);
	}
	
	//	I should probably use better namespace conventions here
	
	function StreamImage($inDir, $inFilename)
	{
		$userfile = &new userfiledb();
		
		$data = $userfile->read($inDir, $inFilename);
		
		echo($data);
	}
	
	
	function ImageExists($inDir, $inFilename)
	{
		$userfile = &new userfiledb();
		
		return $userfile->exists($inDir, $inFilename);
	}
	
	function ImageToTemp($inTempFile, $inDir, $inFilename)
	{
	    $dir = explode("/", $inTempFile);
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
		
		$userfile = &new userfiledb();
		
		$data = $userfile->read($inDir, $inFilename);
		
		$handle = fopen($inTempFile, 'w+');

		fwrite($handle, $data);
		
	    fclose($handle);
	    
	}
?>