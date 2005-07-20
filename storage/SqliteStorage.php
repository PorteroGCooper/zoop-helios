<?
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

class SqliteStorage extends Storage
{
	function SqliteStorage()
	{
	}
	
	function fileExists($path)
	{
		if(sql_fetch_one_cell("select id from file where path = '$path'") === false)
			return false;
		else
			return true;
	}

	
	function saveFile($path, $data)
	{		
		$data = base64_encode($data);
		
		$id = sql_fetch_one_cell("select id from file where path = '$path'");
		
		$curDate = time();
		
		if($id === false)
		{
			sql_query("insert into file (path, data, date_added, date_modified) values ('$path', '$data', $curDate, $curDate)");
		}
		else
		{
			sql_query("update file set data = '$data', date_modified = $curDate where path = '$path'");
		}
	}
	
	
	function getFile($path)
	{
		if(sql_fetch_one_cell("select id from file where path = '$path'") === false)
			trigger_error('file does not exist');
		
		return base64_decode(sql_fetch_one_cell("select data from file where path = '$path'"));
	}
}
?>
