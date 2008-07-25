<?php
include_once( dirname(__file__) . "/class.inputfilter.php");

/**
* POST Utilities file
*
* @package app
* @subpackage post_utils
* @author John Lesueur, Steve Francia
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

//find the item of the post that we should be using. 
/**
* Filters bad HTML from $in with default settings.
*
* @param mixed $inName a string or array of strings that indicates the post index.
* @return mixed
*/
function findPostItem($inName = false)
{
	global $POSTCOPY;
	$post = $POSTCOPY;
	if (!$inName)
		return $POSTCOPY;
	else if(is_array($inName))
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
	return $post;
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
//	I don't like the existence of this function. I think there should never be such a thing as getPost, because we don't 
//	know how to filter all the elements, unless the programmer tells us how they should be filtered. - rjl
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
function getPostString($inName = false)
{
	deprecated('This function has been deprecated in favor of getRawPost().');
	return getRawPost($inName);
}

/**
* Checks to see if a variable was in the POST
*
* @param string $inName Name of the variable
* @return boolean
*/
function getPostIsset($inName = false)
{
	$item = findPostItem($inName);
	if(!$item)
		return $item;
	return true;
}

/**
* Checks to see if a Checkbox POST was checked or not checked
*
* @param string $inName Name of the variable
* @return boolean This returns 0 or 1
*/
function getPostCheckbox($inName = false)
{
	$item = findPostItem($inName);
	if(!$item)
		return 0;
	return 1;
}

/**
 * getPostHTMLArray
 *
 * @param mixed $inName
 * @access public
 * @return void
 */
function getPostHTMLArray($inName = false)
{
	deprecated('This function has been deprecated in favor of getPostHTML().');
	return getPostHTML($inName);
}

/**
 * getPostTextArray
 *
 * @param mixed $inName
 * @access public
 * @return void
 */
function getPostTextArray($inName = false)
{
	deprecated('This function has been deprecated in favor of getPostText().');
	return getPostText($inName);
}

/**
 * VerifyInt - take an item, an array or arraytree and make sure that each item is an integer.
 *
 * @param mixed $inNumber
 * @access public
 * @return void
 */
function VerifyInt($inNumber)
{
	if(defined('filter_input') && !filter_input)
		return $inNumber;
	if(is_array($inNumber))
	{
		foreach($inNumber as $key => $value)
		{
			$inNumber[$key] = VerifyInt($inNumber);
		}
	}
	else if($inNumber === '')//blank is ok, because we're not checking that it's set to a value, just that if it has one, it is an integer.
		return '';
	else
	{
		assert( is_numeric($inNumber));
		return (integer)$inNumber;
	}
	return $inNumber;
}

/**
 * getPostIntArray
 *
 * @param mixed $inName
 * @access public
 * @return void
 */
function getPostIntArray($inName = false)
{
	deprecated('This function has been deprecated in favor of getPostInt().');
	return getPostInt($inName);
}

/**
 * getPostIntTree
 *
 * @param mixed $inName
 * @access public
 * @return void
 */
function getPostIntTree($inName)
{
	deprecated('This function has been deprecated in favor of getPostInt().');
	return getPostInt($inName);
}

/**
 * GetPostTextTree
 *
 * @param mixed $inName
 * @access public
 * @return void
 */
function getPostTextTree($inName = false)
{
	deprecated('This function has been deprecated in favor of getPostText().');
	return getPostText($inName);
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
function getPostKeys($inName = false)
{
	return array_keys(findPostItem($inName));
}

/**
* Warning : This function is dangerous, and shouldn't be used generally
* Zoop Automatically copies $_POST and provides functions to access it safely.
* This function returns the value of $_POST without any cleanup or filtering.
* @param mixed $inName a string or array of strings that indicates the post index.
* @return mixed
*/
function getRawPost($inName = false)
{
	return findPostItem($inName);
}

/**
* This strips dangerous html and javascript from the post
* Depends on define "filter_input"
* Can take an input to get that posted element or leave blank for all of post.
*
* @param string $inName Name of the variable
* @return mixed
*/
function getPostHTML($inName = false)
{
	if(!defined('filter_input') || filter_input)
	{
		$item = findPostItem($inName);
		if(!$item)
			return $item;
		return HTMLFilterElement(findPostItem($inName));
	}
	else
		return getRawPost($inName);
}

/**
* This strips all html from the variable then returns it
* Depends on define "filter_input"
* Can take an input to get that posted element or leave blank for all of post.
*
* @param string $inName Name of the variable
* @return mixed
*/
function getPostText($inName = false)
{
	if(!defined('filter_input') || filter_input)
	{
		$item = findPostItem($inName);
		if(!$item)
			return $item;
		return  StripHTMLElement($item);
	}
	else
		return getRawPost($inName);
}

/**
* This makes sure that the requested post var is an integer and casts it as such
*
* @param string $inName Name of the variable
* @return mixed Either the int or false
*/
function getPostInt($inName)
{
	$item = findPostItem($inName);
	if(!$item)
		return $item;
	return verifyInt($item);
}

?>
