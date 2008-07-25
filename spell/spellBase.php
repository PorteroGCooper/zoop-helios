<?php
/**
* @package spell
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
* @package spell
*/
class spellBase
{
	var $pspell;
	var $ignore = array();
	var $index = 0;
	var $delim = " ()[]?&,.-;\r\n\t\"\\:";
	var $exceptions;
	
	function spellBase($text)
	{
		/*if(talent_status == desktop)
		{
			$pspell_config = pspell_config_create("en");
			pspell_config_personal($pspell_config, "/var/dictionaries/custom.pws");
			pspell_config_repl($pspell_config, "/var/dictionaries/custom.repl");
			$this->pspell = pspell_new_config($pspell_config);
		}
		else
		*/
		
		if(function_exists('GetLanguageCode'))
			$languageCode = GetLanguageCode();
		else
			$languageCode = 'en';
			
		$this->pspell = pspell_new ($languageCode, "", "", 'utf-8', PSPELL_NORMAL);
		$this->text = $text;
		$this->exceptions = array();
	}
	
	function initRequest()
	{
		if(function_exists('GetLanguageCode'))
			$languageCode = GetLanguageCode();
		else
			$languageCode = 'en';

		$this->pspell = pspell_new ($languageCode, "", "", 'utf-8', PSPELL_NORMAL);
	}
	
	function getSuggestions()
	{
		$starttext = substr($this->text, $this->index, strlen($this->text) - $this->index);
		$word = strtok($starttext, $this->delim);
		
		/*
		if (!pspell_check($this->pspell, $word))
		    $suggestions = pspell_suggest($this->pspell, $word);
		*/
		
		
		
		if( !$this->checkWord($word) )
		{
			$suggestions = $this->getWordSuggestions($word);
		}
		
		if(isset($suggestions))
			return $suggestions;
		
		return 0;
	}
	
	
	
	function checkText()
	{	
		$starttext = substr($this->text, $this->index, strlen($this->text) - $this->index);
		$word = strtok($starttext, $this->delim);
		if(!$word)
		{
			return -1;
		}
		if(!is_numeric($word) && !isset($this->ignore[$word]) && $this->getSuggestions($word) != 0)
		{
			return $this->index;
		}
		else
		{
			$this->advanceIndex();		
			return $this->checkText();
		}
		return -1;
	}
	
	function advanceIndex()
	{
		$starttext = substr($this->text, $this->index, strlen($this->text) - $this->index);+
		$word = strtok($starttext, $this->delim);
		$this->index += strlen($word) + 1;
		$starttext = substr($this->text, $this->index, strlen($this->text) - $this->index);+
		$word = strtok($starttext, $this->delim);
		if($this->index < strlen($this->text))
			$this->index = strpos($this->text, $word, $this->index);
		if($this->index == 0)
		{
			$this->index = strlen($this->text);
		}
	}
	
	function getNextWord()
	{
		$starttext = substr($this->text, $this->index, strlen($this->text) - $this->index);
		$word = strtok($starttext, $this->delim);
		return $word;
	}
	
	function skipWord()
	{
		$starttext = substr($this->text, $this->index, strlen($this->text) - $this->index);
		$this->advanceIndex();
	}
	
	function replace($replacement)
	{
		$starttext = substr($this->text, $this->index, strlen($this->text) - $this->index);
		$word = strtok($starttext, $this->delim);
		$this->text = substr_replace($this->text, $replacement, $this->index, strlen($word));				
	}
	
	function replaceAll($replacement)
	{
		$starttext = substr($this->text, $this->index, strlen($this->text) - $this->index);
		$word = strtok($starttext, $this->delim);
		$index = $this->index;
		$oldword = $word;
		while($word)
		{
			if($word == $oldword)
				$this->text = substr_replace($this->text, $replacement, $index, strlen($word));
			$starttext = substr($this->text, $this->index, strlen($this->text) - $this->index);
			$word = strtok($starttext, $this->delim);
			$index += strlen($word);
			$word = strtok($starttext, $this->delim);
			$index = strpos($this->text, $word, $index);
		}
	}
	
	function getHighlightedText($beforeWord, $afterWord)
	{
		$disptext = substr($this->text, 0, $this->index) . $beforeWord . substr($this->text, $this->index, strlen($this->getNextWord())) . $afterWord . substr($this->text, $this->index + strlen($this->getNextWord()));
		return $disptext;
	}
		
	function ignoreAll()
	{
		$word = $this->getNextWord();
		$this->ignore[$word] = 1;
	}
	
	function addWord($word)
	{
		if($word == '')
			return;
		
		//	until we can be confident that we aren't adding invalid words into the spell checker
		//	we need to just do this to make sure that we aren't going to throw an error
		//	If we could handle the error better maybe we could get around it but I think that
		//	this is the best solution for now.  I left the original code commented how below.
		$this->exceptions[$word] = 1;
		
		/*
		if( strpos($word, "&") )
			$this->exceptions[$word] = 1;
		else
			pspell_add_to_session($this->pspell, $word);
		*/
	}
	
	function checkWord($inWord)
	{
		if( isset($this->exceptions[$inWord]) )
			return 1;
		else
			return pspell_check($this->pspell, $inWord);
	}
	
	
	function getWordSuggestions($inWord)
	{
		if( isset($this->exceptions[$inWord]) )
			return array();
		else
			return pspell_suggest($this->pspell, $inWord);
	}
}
?>