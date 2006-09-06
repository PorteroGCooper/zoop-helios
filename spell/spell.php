<?php
/**
* @package spell
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
* @package spell
*/
class spell extends spellBase
{
	function spell($text)
	{
		$this->spellBase($text);
	}

	function initRequest()
	{
		parent::initRequest();
		$this->initWords();
	}

	function addWord($word)
	{
		global $spelldb;
		
		//	ereg_replace("[^[:alpha:]]", "", $word);	//	this is such a tricky problem
		$word = ereg_replace("[(){}[_+=*&^ %$#@!;:0123456789'\"<>,./?\\~`]", "", $word);
		
		if (isset($this) && is_a($this, "spell"))
			spellBase::addWord($word);

		if (!$spelldb->check("select word from wordlist where word = '$word'"))
		{
			$spelldb->query("insert into wordlist (word) values ('$word')");
		}
	}

	function deleteWord($word)
	{
		global $spelldb;
		$spelldb->query("delete from wordlist where word = '$word'");
	}

	function initWords()
	{
		global $spelldb;
		$words = $spelldb->new_fetch_into_array("select word from wordlist");
		foreach($words as $word)
		{
			spellBase::addWord($word);
		}
		return $words;
	}

	function getWords()
	{
		global $spelldb;
		$words = $spelldb->new_fetch_into_array("select word from wordlist order by word");
		return $words;
	}

}
?>