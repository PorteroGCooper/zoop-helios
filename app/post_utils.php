<?php
include_once( dirname(__file__) . "/class.inputfilter.php");

/**
* POST Utilities file
*
* @package app
* @subpackage post_utils
* @author John Lesueur, Steve Francia
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
* Filters bad HTML from $in with default settings.
*
* @param mixed $in variable (array or string) to filter.
* @return boolean
*/
function HTMLFilterElement($in)
{
	$filter = new InputFilter(array(), array(), 1, 1);
	$out = $filter->process($in);
	return $out;
}

/**
* Strips all HTML from $in.
*
* @param mixed $in variable (array or string) to filter.
* @return boolean
*/
function StripHTMLElement($in)
{
	$filter = new InputFilter();
	$out = $filter->process($in);
	return $out;
}

/**
* Returns the POST either Raw or Filtered for bad HTML
* Depends on define "filter_input"
* Can take an input to get that posted element or leave blank for all of post.
*
* @param string $inName Name of the variable
* @return boolean
*/
function getPost($inName = false)
{
	return getPostHTML($inName);
}

/**
* This returns the raw unfiltered contents of a post variable
*
* @param string $inName Name of the variable
* @return mixed
*/
function GetPostString($inName = false)
{
	if (!$inName)
		return getRawPost();

	global $POSTCOPY;
	if( isset($POSTCOPY["$inName"]) )
		return $POSTCOPY["$inName"];
	else
		return false;
}

/**
* This strips dangerous html and javascript from the post
* Depends on define "filter_input"
* Can take an input to get that posted element or leave blank for all of post.
*
* @param string $inName Name of the variable
* @return mixed
*/
function GetPostHTML($inName = false)
{
	if(!defined('filter_input') || filter_input)
	{
		global $POSTCOPY;

		if (!$inName)
			return  HTMLFilterElement($POSTCOPY);

		if( isset($POSTCOPY["$inName"]) )
			return HTMLFilterElement($POSTCOPY["$inName"]);
		else
			return false;
	}
	else
		return getPostString($inName);
}

/**
* This strips all html from the variable then returns it
* Depends on define "filter_input"
* Can take an input to get that posted element or leave blank for all of post.
*
* @param string $inName Name of the variable
* @return mixed
*/
function GetPostText($inName)
{
	if(!defined('filter_input') || filter_input)
	{
		global $POSTCOPY;

		if (!$inName)
			return  StripHTMLElement($POSTCOPY);

		if( isset($POSTCOPY["$inName"]) )
			return StripHTMLElement($POSTCOPY["$inName"]);
		else
			return false;
	}
	else
		return getPostString($inName);
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
