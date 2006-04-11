<?php
/**
* POST Utilities file
*
* @package app
* @subpackage post_utils
* @author John Lesueur
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
* Warning : This function is dangerous, and shouldn't be used generally
* Zoop Automatically copies $_POST and provides functions to access it safely.
* This function returns the value of $_POST without any cleanup or filtering.
*
* @return array
*/
function getRawPost()
{
	global $POSTCOPY;
	return $POSTCOPY;
}

/**
* Checks to see if a variable was in the POST
*
* @param string $inName Name of the variable
* @return boolean
*/
function GetPostIsset($inName)
{
	global $POSTCOPY;
	return isset($POSTCOPY[$inName]);
}

/**
* Checks to see if a Checkbox POST was checked or not checked
*
* @param string $inName Name of the variable
* @return boolean This returns 0 or 1
*/
function GetPostCheckbox($inName)
{
	global $POSTCOPY;
	return isset( $POSTCOPY[$inName] ) ? 1 : 0;
}

/**
* This returns the raw unfiltered contents of a post variable
*
* @param string $inName Name of the variable
* @return mixed
*/
function GetPostString($inName)
{
	global $POSTCOPY;
	if( isset($POSTCOPY["$inName"]) )
		return $POSTCOPY["$inName"];
	else
		return false;
}

/**
* This strips javascript and any tags not in $allowed_tags from HTML
* see __verifyHTMLTree below
*
* @param string $inName Name of the variable
* @return mixed
*/
function GetPostHTML($inName)
{
	//reduce the HTML we get to acceptable HTML
	global $POSTCOPY;

	if(!defined('filter_input') || filter_input)
	{
		$html = $POSTCOPY[$inName];
		return __verifyHTMLTree($html);
	}
	else
	{
		$answer = $POSTCOPY[$inName];
	}
	return $answer;
}

/**
 * getPostHTMLArray 
 * 
 * @param mixed $inName 
 * @access public
 * @return void
 */
function getPostHTMLArray($inName)
{
	global $POSTCOPY;
	$answer = array();
	$post = $POSTCOPY;
	if(is_array($inName))
	{
		foreach($inName as $key)
		{
			if(isset($post[$key]))
				$post = $post[$key];
			else
				return false;
		}
	}
	else
	{
		if(isset($post[$inName]))
			$post = $post[$inName];
		else
			return false;
	}
	if(isset($post))
	{
		foreach($post as $key => $text)
		{
			$answer[$key] = verifyText($text);
		}
	}
	return $answer;
}

/**
* This strips all html from the variable then returns it
*
* @param string $inName Name of the variable
* @return mixed
*/
function GetPostText($inName)
{
	global $POSTCOPY;
	if( isset($POSTCOPY[$inName]) )
		return VerifyTextOrArray($POSTCOPY["$inName"]);
	else
		return false;
}

/**
 * getPostTextArray 
 * 
 * @param mixed $inName 
 * @access public
 * @return void
 */
function getPostTextArray($inName)
{
	global $POSTCOPY;
	$answer = array();
	$post = $POSTCOPY;
	if(is_array($inName))
	{
		foreach($inName as $key)
		{
			if(isset($post[$key]))
				$post = $post[$key];
			else
				return false;
		}
	}
	else
	{
		if(isset($post[$inName]))
			$post = $post[$inName];
		else
			return false;
	}
	if(isset($post))
	{
		foreach($post as $key => $text)
		{
			if (is_array($text))
			{
				$answer[$key] = VerifyTextOrArray($text);
			}
			else
			{
				$answer[$key] = verifyText($text);
			}
		}
	}

	return $answer;
}

/**
* This makes sure that the requested post var is an integer and casts it as such
*
* @param string $inName Name of the variable
* @return mixed Either the int or false
*/
function GetPostInt($inName)
{
	global $POSTCOPY;
	if( isset($POSTCOPY["$inName"]) )
	{
		return verifyInt($POSTCOPY["$inName"]);
	}
	else
		return false;
}

/**
 * GetPostIntArray 
 * 
 * @param mixed $inName 
 * @access public
 * @return void
 */
function GetPostIntArray($inName)
{
	global $POSTCOPY;
	$answer = array();
	$post = $POSTCOPY;
	if(is_array($inName))
	{
		foreach($inName as $key)
		{
			if(isset($post[$key]))
				$post = $post[$key];
			else
				return false;
		}
	}
	else
	{
		if(isset($post[$inName]))
			$post = $post[$inName];
		else
			return false;
	}
	if(isset($post))
	{
		foreach($post as $key => $text)
		{
			$answer[$key] = verifyInt($text);
		}
	}
	return $answer;
}

/**
 * GetPostIntTree 
 * 
 * @param mixed $inName 
 * @access public
 * @return void
 */
function GetPostIntTree($inName)
{
	global $POSTCOPY;
	$answer = array();
	$post = $POSTCOPY;
	if(is_array($inName))
	{
		foreach($inName as $key)
		{
			if(isset($post[$key]))
				$post = $post[$key];
			else
				return false;
		}
	}
	else
	{
		if(isset($post[$inName]))
			$post = $post[$inName];
		else
			return false;
	}
	return __getPostIntTree($post);
}

/**
 * __GetPostIntTree 
 * 
 * @param mixed $post 
 * @access protected
 * @return void
 */
function __GetPostIntTree($post)
{
	$answer = array();
	if(is_array($post))
	{
		foreach($post as $key => $val)
		{
			$answer[$key] = __getPostIntTree($val);
		}
		return $answer;
	}
	else
	{
		return verifyInt($post);
	}
}

/**
 * GetPostTextTree 
 * 
 * @param mixed $inName 
 * @access public
 * @return void
 */
function GetPostTextTree($inName)
	{
		global $POSTCOPY;
		$answer = array();
		$post = $POSTCOPY;
		if(is_array($inName))
		{
			foreach($inName as $key)
			{
				if(isset($post[$key]))
					$post = $post[$key];
				else
					return false;
			}
		}
		else
		{
			if(isset($post[$inName]))
				$post = $post[$inName];
			else
				return false;
		}
		return __getPostTextTree($post);
	}

/**
 * __GetPostTextTree 
 * 
 * @param mixed $post 
 * @access protected
 * @return void
 */
function __GetPostTextTree($post)
{
	$answer = array();
	if(is_array($post))
	{
		foreach($post as $key => $val)
		{
			$answer[$key] = __getPostTextTree($val);
		}
		return $answer;
	}
	else
	{
		return verifyText($post);
	}
}

/**
 * unsetPost 
 * 
 * @param mixed $inName 
 * @access public
 * @return void
 */
function unsetPost($inName)
{
	global $POSTCOPY;
	unset($POSTCOPY[$inName]);
}

/**
 * getPostKeys 
 * 
 * @access public
 * @return void
 */
function getPostKeys()
{
	global $POSTCOPY;
	return array_keys($POSTCOPY);
}
?>
