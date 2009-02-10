<?php

/**
 * HTTP request utilities collection.
 *
 * Handles sanitizing of all GET and POST request variables, tons of options here!
 *
 * @group request_utils
 * @endgroup
 * 
 * @ingroup app
 * @ingroup get_utils
 * @author Justin Hileman {@link http://justinhileman.com}
 * @author John Lesueur, Steve Francia
 */

include_once(dirname(__file__) . "/class.inputfilter.php");

// Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

abstract class RequestUtils {
	/**
	 * Get an HTTP request variable or array of variables.
	 *
	 * Input will be filtered (or not) based on the 'filter_input' global setting.
	 *
	 * @see getGet
	 * @see getPost
	 *
	 * I don't like the existence of this function. I think there should never be such a thing as getPost,
	 * because we don't know how to filter all the elements, unless the programmer tells us how they should
	 * be filtered. - rjl
	 *
	 * @param string $get_or_post Request variable type ('get' or 'post').
	 * @param string $var_name Name of the HTTP request request variable (optional)
	 * @return mixed HTTP request value or array of values.
	 */
	protected function getRequest($get_or_post, $var_name = false) {
		if(defined('filter_input') && filter_input) {
			return self::getRequestHTML($get_or_post, $var_name);
		} else {
			return self::findRequestItem($get_or_post, $var_name);
		}
	}
	
	/**
	 * Warning : This function is dangerous, and shouldn't be used generally
	 * Zoop Automatically copies $_POST and provides functions to access it safely.
	 * This function returns the value of $_POST without any cleanup or filtering.
	 * @param mixed $var_name a string or array of strings that indicates the post index.
	 * @return mixed
	 */
	protected function getRawRequest($get_or_post, $var_name = false) {
		return self::findRequestItem($get_or_post, $var_name);
	}
	
	/**
	 * Returns a single HTTP request variable, or an array of HTTP request variables if $var_name is unset.
	 *
	 * Use the getPost() and getGet() functions instead, as they will (depending on your preferences)
	 * return either filtered or unfiltered input. If you filter by default but really want to live
	 * on the wild side, use getRawPost() and getRawGet() to use the unfiltered goodness.
	 *
	 * @access private
	 * @see GET::get
	 * @see POST::get
	 *
	 * @param string $get_or_post Request variable type ('get' or 'post').
	 * @param mixed $var_name request variable index (or array of indices). (optional)
	 * @return mixed request variable or array of variables.
	 */
	protected function findRequestItem($get_or_post, $var_name = false) {
		if ($get_or_post == 'get') {
			global $_GET_UNFILTERED;
			$request_vars = $_GET_UNFILTERED;
		} else if ($get_or_post == 'post') {
			global $_POST_UNFILTERED;
			$request_vars = $_POST_UNFILTERED;
		} else {
			trigger_error('Undefined request type: ' . $get_or_post);
			return;
		}
		if (!$var_name) {
			return $request_vars;
		} else if(is_array($var_name)) {
			foreach($var_name as $key) {
				if(isset($request_vars[$key])) {
					$request_vars = $request_vars[$key];
				} else {
					return false;
				}
			}
		} else {
			if(isset($request_vars[$var_name])) {
				$request_vars = $request_vars[$var_name];
			}
			else {
				return false;
			}
		}
		return $request_vars;
	}
	
	
	/**
	 * Checks to see if a variable was in the HTTP server request (GET or POST).
	 *
	 * @param string $get_or_post Request variable type ('get' or 'post').
	 * @param string $var_name Name of the variable.
	 * @return boolean
	 */
	private function getRequestVarIsset($get_or_post, $var_name = false) {
		$item = self::findRequestItem($get_or_post, $var_name);
		if(!$item) {
			return false;
		}
		return true;
	}
	
	/**
	 * Checks to see if a Checkbox was checked or not checked
	 *
	 * @todo Find out if we can return 'null' if unset.
	 * @todo Make this return true or false instead of 1 or 0.
	 *
	 * @param string $get_or_post Request variable type ('get' or 'post').
	 * @param string $var_name Name of the variable
	 * @return boolean
	 */
	protected function getRequestBool($get_or_post, $var_name = false) {
		$item = self::findRequestItem($get_or_post, $var_name);
		if(!$item) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Strip dangerous HTML and JavaScript from the HTTP request variables and return.
	 * Input will be filtered regardless of the global 'filter_input' setting.
	 * Can take an input to get that posted element or leave blank for all of post.
	 *
	 * @access private
	 * @see GET::getHTML
	 * @see POST::getHTML
	 *
	 * @param string $get_or_post Request variable type ('get' or 'post').
	 * @param string $var_name HTTP request variable name.
	 * @return mixed HTTP request variable value or array of values.
	 */
	protected function getRequestHTML($get_or_post, $var_name = false) {
		$item = self::findRequestItem($get_or_post, $var_name);
		if($item) {
			return self::filterHTML($item);
		} else {
			return $item;
		}
	}
	
	/**
	 * This strips all HTML from the variable then returns it.
	 * Input will be filtered regardless of the global 'filter_input' setting.
	 *
	 * @access private
	 * @see GET::getText
	 * @see POST::getText
	 *
	 * @param string $get_or_post Request variable type ('get' or 'post').
	 * @param string $var_name Name of the variable
	 * @return mixed HTTP request variable value or array of values.
	 */
	protected function getRequestText($get_or_post, $var_name = false) {
		$item = self::findRequestItem($get_or_post, $var_name);
		if(empty($item)) {
			return $item;
		} else {
			return self::stripHTML($item);
		}
	}
	
	/**
	 * Return the specified HTTP request variable as an integer.
	 *
	 * This method will cast the variable as an integer if it isn't already. This is preferred
	 * as it guarantees that your variable is either an integer or 'null'.
	 *
	 * @access private
	 * @see GET::getInt
	 * @see POST::getInt
	 *
	 * @param string $get_or_post Request variable type ('get' or 'post').
	 * @param string $var_name HTTP request variable name.
	 * @return mixed HTTP request value cast as an integer, or null.
	 */
	protected function getRequestInt($get_or_post, $var_name) {
		$item = self::findRequestItem($get_or_post, $var_name);
		
		// only return null/false/empty string if the item is STRICTLY EQUAL to those.
		// otherwise '0' will never come through.
		if ($item === null || $item === false || $item === '') {
			return null;
		} else {
			return self::verifyInt($item);
		}
	}
	
	/**
	 * Filters bad HTML from $in with default settings.
	 *
	 * @param mixed $in variable (array or string) to filter.
	 * @return boolean
	 */
	protected function filterHTML($in) {
		if (!isset(self::$html_filter)) self::$html_filter = new InputFilter(array(), array(), 1, 1);
		$out = self::$html_filter->process($in);
		return $out;
	}
	
	/**
	 * Strips all HTML from $in.
	 *
	 * @param mixed $in variable (array or string) to filter.
	 * @return boolean
	 */
	protected function stripHTML($in) {
		if (!isset(self::$strip_filter)) self::$strip_filter = new InputFilter();
		$out = self::$strip_filter->process($in);
		return $out;
	}
	
	/**
	 * verifyInt
	 *
	 * Take an item, (or array of items) and make sure that each item is an integer.
	 * Note that this function will return integer equivalents of strings (which is
	 * sometimes more than a bit funky) so be careful that you actually mean to ask
	 * for an integer before you use this.
	 *
	 * @param mixed $inNumber
	 * @access public
	 * @return mixed
	 */
	protected function verifyInt($in) {
		if (is_array($in)) {
			foreach($in as $key => $value) {
				$in[$key] = self::verifyInt($value);
			}
		} else if ($in === '') {
			// blank is ok, because we're not checking that it's set to a value,
			// just that if it has one, it is an integer.
			return '';
		} else {
			assert(is_numeric($in));
			return (integer)$in;
		}
		return $in;
	}
}

abstract class GET extends RequestUtils {
	/**
	 * Get a GET variable or array of variables.
	 *
	 * Input will be filtered (or not) based on the 'filter_input' global setting.
	 *
	 * @see GET::getText
	 * @see GET::getHTML
	 * @see GET::getInt
	 * @see GET::getCheckbox
	 *
	 * @param string $var_name Name of the GET request variable (optional)
	 * @return mixed POST value or array of values, if a single variable name is not requested.
	 */
	function get($var_name = false) {
		return self::getRequest('get', $var_name);
	}
	
	/**
	 * Warning : This function is dangerous, and shouldn't be used generally
	 * Zoop Automatically copies $_GET and provides functions to access it safely.
	 * 
	 * This function returns the value of $_GET without any cleanup or filtering.
	 * 
	 * @see GET::get
	 * @param mixed $var_name a string or array of strings that indicates the get index.
	 * @return mixed
	 */
	function getRaw($var_name = false) {
		return  self::getRawRequest('get', $var_name);
	}
	
	/**
	 * Checks to see if a variable was in the POST request.
	 *
	 * @param string $var_name Name of the variable.
	 * @return boolean
	 */
	function varIsset($var_name = false) {
		return self::getRequestVarIsset('get', $var_name);
	}
	
	/**
	 * Globally unset a GET request variable.
	 *
	 * @param mixed $var_name Variable to unset.
	 * @access public
	 * @return void
	 */
	function unsetVar($var_name) {
		global $_GET_UNFILTERED;
		unset($_GET_UNFILTERED[$var_name]);
	}
	
	/**
	 * Get all HTTP GET request keys.
	 *
	 * @access public
	 * @return array The array of GET request keys.
	 */
	function getKeys($var_name = false) {
		return array_keys(self::findRequestItem('get', $var_name));
	}

	/**
	 * Checks to see if a boolean value GET was checked or not checked
	 *
	 * @todo Find out if we can return 'null' if unset.
	 *
	 * @param string $var_name Name of the GET request variable
	 * @return boolean
	 */
	function getBool($var_name = false) {
		return self::getRequestBool('get', $var_name);
	}
	
	/**
	 * Strip dangerous HTML and JavaScript from the HTTP request variables and return.
	 * Input will be filtered regardless of the global 'filter_input' setting.
	 * Can take an input to get that posted element or leave blank for all of post.
	 *
	 * @param string $var_name Name of the variable
	 * @return mixed HTTP request variable value or array of values.
	 */
	function getHTML($var_name = false) {
		return self::getRequestHTML('get', $var_name);
	}
	
	/**
	 * This strips all HTML from the GET variable then returns it.
	 * Input will be filtered regardless of the global 'filter_input' setting.
	 *
	 * @param string $var_name Name of the variable
	 * @return mixed GET request variable value or array of values.
	 */
	function getText($var_name = false) {
		return self::getRequestText('get', $var_name);
	}
	
	/**
	 * Return the specified GET request variable as an integer.
	 *
	 * This method will cast the variable as an integer if it isn't already. This is preferred
	 * over GET::get, as it guarantees that your variable is either an integer or 'null'.
	 *
	 * If you need to distinguish between 0 and unspecified value, be sure to use '==='.
	 *
	 * @code
	 *   if (GET::getInt('foo') === null) {
	 *      echo 'GET variable not set';
	 *   }
	 * @endcode
	 *
	 * @param string $var_name HTTP GET request variable name.
	 * @return mixed HTTP GET request value cast as an integer, or null.
	 */
	function getInt($var_name) {
		return self::getRequestInt('get', $var_name);
	}

}

abstract class POST extends RequestUtils {
	/**
	 * Get a POST variable or array of variables.
	 *
	 * Input will be filtered (or not) based on the 'filter_input' global setting.
	 *
	 * @see POST::getText
	 * @see POST::getHTML
	 * @see POST::getInt
	 * @see POST::getCheckbox
	 *
	 * @param string $var_name Name of the POST request variable (optional)
	 * @return mixed POST value or array of values, if a single variable name is not requested.
	 */
	static function get($var_name = false) {
		return self::getRequest('post', $var_name);
	}
	
	/**
	 * Warning : This function is dangerous, and shouldn't be used generally
	 * Zoop Automatically copies $_POST and provides functions to access it safely.
	 * 
	 * This function returns the value of $_POST without any cleanup or filtering.
	 * 
	 * @see POST::get
	 * @param mixed $var_name a string or array of strings that indicates the post index.
	 * @return mixed
	 */
	function getRaw($var_name = false) {
		return  self::getRawRequest('post', $var_name);
	}
	
	/**
	 * Checks to see if a variable was in the POST request.
	 *
	 * @param string $var_name Name of the variable.
	 * @return boolean
	 */
	function varIsset($var_name = false) {
		return self::getRequestVarIsset('post', $var_name);
	}
	
	/**
	 * Globally unset a POST request variable.
	 *
	 * @param mixed $var_name Variable to unset.
	 * @access public
	 * @return void
	 */
	function unsetVar($var_name) {
		global $_POST_UNFILTERED;
		unset($_POST_UNFILTERED[$var_name]);
	}
	
	
	/**
	 * Get all HTTP POST request keys.
	 *
	 * @access public
	 * @return array The array of POST request keys.
	 */
	function getKeys($var_name = false) {
		return array_keys(self::findRequestItem('post', $var_name));
	}

	
	/**
	 * Checks to see if a boolean value POST was checked or not checked
	 *
	 * @todo Find out if we can return 'null' if unset.
	 *
	 * @param string $var_name Name of the POST request variable to check
	 * @return boolean
	 */
	function getBool($var_name = false) {
		return self::getRequestBool('post', $var_name);
	}
	
	/**
	 * Strip dangerous HTML and JavaScript from the HTTP request variables and return.
	 * Input will be filtered regardless of the global 'filter_input' setting.
	 * Can take an input to get that posted element or leave blank for all of post.
	 *
	 * @param string $var_name Name of the variable
	 * @return mixed HTTP request variable value or array of values.
	 */
	function getHTML($var_name = false) {
		return self::getRequestHTML('post', $var_name);
	}
	
	/**
	 * This strips all HTML from the POST variable then returns it.
	 * Input will be filtered regardless of the global 'filter_input' setting.
	 *
	 * @param string $var_name Name of the variable
	 * @return mixed POST request variable value or array of values.
	 */
	function getText($var_name = false) {
		return self::getRequestText('post', $var_name);
	}
	
	/**
	 * Return the specified POST request variable as an integer.
	 *
	 * This method will cast the variable as an integer if it isn't already. This is preferred
	 * over POST::get, as it guarantees that your variable is either an integer or 'null'.
	 *
	 * If you need to distinguish between 0 and unspecified value, be sure to use '==='.
	 *
	 * @code
	 *   if (POST::getInt('foo') === null) {
	 *      echo 'POST variable not set';
	 *   }
	 * @endcode
	 *
	 * @param string $var_name HTTP POST request variable name.
	 * @return mixed HTTP POST request value cast as an integer, or null.
	 */
	function getInt($var_name) {
		return self::getRequestInt('post', $var_name);
	}
}
