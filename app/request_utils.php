<?php
/**
 * HTTP request utilities collection.
 *
 * Handles sanitizing of all GET and POST request variables, tons of options here!
 *
 * @ingroup app
 * @ingroup post_utils
 * @ingroup get_utils
 * @author Justin Hileman {@link http://justinhileman.com}
 * @author John Lesueur, Steve Francia
 */

include_once( dirname(__file__) . "/class.inputfilter.php");

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
 * Get a POST variable or array of variables.
 *
 * Input will be filtered (or not) based on the 'filter_input' global setting.
 *
 * @see getPostText
 * @see getPostHTML
 * @see getPostInt
 * @see getPostCheckbox
 *
 * @param string $var_name Name of the POST request variable (optional)
 * @return mixed POST value or array of values, if a single variable name is not requested.
 */
function getPost($var_name = false) {
	return _getRequest('post', $var_name);
}

/**
 * Get a GET variable or array of variables.
 *
 * Input will be filtered (or not) based on the 'filter_input' global setting.
 *
 * @see getGetText
 * @see getGetHTML
 * @see getGetInt
 * @see getGetCheckbox
 *
 * @param string $var_name Name of the GET request variable (optional)
 * @return mixed POST value or array of values, if a single variable name is not requested.
 */
function getGet($var_name = false) {
	return _getRequest('get', $var_name);
}

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
function _getRequest($get_or_post, $var_name = false) {
	if(defined('filter_input') && filter_input) {
		return _getRequestHTML($get_or_post, $var_name);
	} else {
		return _findRequestItem($get_or_post, $var_name);
	}
}



/**
 * Returns a single POST request variable, or an array of POST variables if $var_name is unset.
 *
 * DANGER WILL ROBINSON! This is UNFILTERED, UNSANITIZED input. DON'T USE THIS!
 *
 * Use the getPost() function instead, as that will (depending on your preferences)
 * return either filtered or unfiltered input. If you filter by default but really
 * want to live on the wild side, use getRawPost() to use the unfiltered goodness.
 *
 * @see getPost()
 * @see getRawPost()
 * @param mixed $var_name POST request index (or array of indices). (optional)
 * @return mixed POST request variable or array of variables.
 */
function findPostItem($var_name = false) {
	deprecated('This function has been deprecated. Please access POST variables through getPost(), getPostText(), getPostInt(), getPostCheckbox() or--if you absolutely need it--through getRawPost().');
	return;
/* 	return _findRequestItem('post', $var_name); */
}

/**
 * Returns a single HTTP request variable, or an array of HTTP request variables if $var_name is unset.
 *
 * DANGER WILL ROBINSON! This is UNFILTERED, UNSANITIZED input. DON'T USE THIS!
 *
 * Use the getPost() and getGet() functions instead, as they will (depending on your preferences)
 * return either filtered or unfiltered input. If you filter by default but really want to live
 * on the wild side, use getRawPost() and getRawGet() to use the unfiltered goodness.
 *
 * @access private
 * @see getGet
 * @see getPost
 *
 * @param string $get_or_post Request variable type ('get' or 'post').
 * @param mixed $var_name request variable index (or array of indices). (optional)
 * @return mixed request variable or array of variables.
 */
function _findRequestItem($get_or_post, $var_name = false) {
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
 * Checks to see if a variable was in the POST request.
 *
 * @param string $var_name Name of the variable.
 * @return boolean
 */
function getGetIsset($var_name = false) {
	return _getRequestIsset('get', $var_name);
}

/**
 * Checks to see if a variable was in the POST request.
 *
 * @param string $var_name Name of the variable.
 * @return boolean
 */
function getPostIsset($var_name = false) {
	return _getRequestIsset('post', $var_name);
}

/**
 * Checks to see if a variable was in the HTTP server request (GET or POST).
 *
 * @param string $get_or_post Request variable type ('get' or 'post').
 * @param string $var_name Name of the variable.
 * @return boolean
 */
function _getRequestIsset($get_or_post, $var_name = false) {
	$item = _findRequestItem($get_or_post, $var_name);
	if(!$item) {
		return false;
	}
	return true;
}

/**
 * Checks to see if a boolean value GET was checked or not checked
 *
 * @todo Find out if we can return 'null' if unset.
 *
 * @param string $var_name Name of the GET request variable
 * @return boolean This returns 0 or 1 (for some unknown reason).
 */
function getGetBool($var_name = false) {
	return _findRequestItem('get', $var_name);
}

/**
 * Checks to see if a boolean value POST was checked or not checked
 *
 * @todo Find out if we can return 'null' if unset.
 *
 * @param string $var_name Name of the POST request variable to check
 * @return boolean This returns 0 or 1 (for some unknown reason).
 */
function getPostBool($var_name = false) {
	return _findRequestItem('post', $var_name);
}

/**
 * @deprecated
 * @param string $var_name
 * @return bool Checkbox value
 */
function getPostCheckbox($var_name = false) {
	deprecated('Use getPostBool instead of getPostCheckbox');
	return getGetBool($var_name);
}


/**
 * Checks to see if a Checkbox GET was checked or not checked
 *
 * @todo Find out if we can return 'null' if unset.
 * @todo Make this return true or false instead of 1 or 0.
 *
 * @param string $get_or_post Request variable type ('get' or 'post').
 * @param string $var_name Name of the variable
 * @return boolean This returns 0 or 1
 */
function _getRequestBool($get_or_post, $var_name = false) {
	$item = _findRequestItem($get_or_post, $var_name);
	if(!$item) {
		return 0;
	} else {
		return 1;
	}
}



/**
 * Globally unset a POST request variable.
 *
 * @param mixed $var_name Variable to unset.
 * @access public
 * @return void
 */
function unsetPost($var_name) {
	global $_POST_UNFILTERED;
	unset($_POST_UNFILTERED[$var_name]);
}

/**
 * Globally unset a GET request variable.
 *
 * @param mixed $var_name Variable to unset.
 * @access public
 * @return void
 */
function unsetGet($var_name) {
	global $_GET_UNFILTERED;
	unset($_GET_UNFILTERED[$var_name]);
}



/**
 * Get all HTTP GET request keys.
 *
 * @access public
 * @return array The array of POST request keys.
 */
function getGetKeys($var_name = false) {
	return array_keys(_findRequestItem('get', $var_name));
}

/**
 * Get all HTTP POST request keys.
 *
 * @access public
 * @return array The array of POST request keys.
 */
function getPostKeys($var_name = false) {
	return array_keys(_findRequestItem('post', $var_name));
}



/**
 * Warning : This function is dangerous, and shouldn't be used generally
 * Zoop Automatically copies $_POST and provides functions to access it safely.
 * This function returns the value of $_POST without any cleanup or filtering.
 * @param mixed $var_name a string or array of strings that indicates the post index.
 * @return mixed
 */
function getRawPost($var_name = false) {
	return  _getRawRequest('post', $var_name);
}

/**
 * Warning : This function is dangerous, and shouldn't be used generally
 * Zoop Automatically copies $_POST and provides functions to access it safely.
 * This function returns the value of $_POST without any cleanup or filtering.
 * @param mixed $var_name a string or array of strings that indicates the post index.
 * @return mixed
 */
function getRawGet($var_name = false) {
	return _getRawRequest('get', $var_name);
}

/**
 * Warning : This function is dangerous, and shouldn't be used generally
 * Zoop Automatically copies $_POST and provides functions to access it safely.
 * This function returns the value of $_POST without any cleanup or filtering.
 * @param mixed $var_name a string or array of strings that indicates the post index.
 * @return mixed
 */
function _getRawRequest($get_or_post, $var_name = false) {
	return _findRequestItem($get_or_post, $var_name);
}




/**
 * Strip dangerous HTML and JavaScript from the HTTP request variables and return.
 * Input will be filtered regardless of the global 'filter_input' setting.
 * Can take an input to get that posted element or leave blank for all of post.
 *
 * @param string $var_name Name of the variable
 * @return mixed HTTP request variable value or array of values.
 */
function getPostHTML($var_name = false) {
	return _getRequestHTML('post', $var_name);
}

/**
 * Strip dangerous HTML and JavaScript from the HTTP request variables and return.
 * Input will be filtered regardless of the global 'filter_input' setting.
 * Can take an input to get that posted element or leave blank for all of post.
 *
 * @param string $var_name Name of the variable
 * @return mixed HTTP request variable value or array of values.
 */
function getGetHTML($var_name = false) {
	return _getRequestHTML('get', $var_name);
}

/**
 * Strip dangerous HTML and JavaScript from the HTTP request variables and return.
 * Input will be filtered regardless of the global 'filter_input' setting.
 * Can take an input to get that posted element or leave blank for all of post.
 *
 * @access private
 * @see getGetHTML
 * @see getPostHTML
 *
 * @param string $get_or_post Request variable type ('get' or 'post').
 * @param string $var_name HTTP request variable name.
 * @return mixed HTTP request variable value or array of values.
 */
function _getRequestHTML($get_or_post, $var_name = false) {
	$item = _findRequestItem($get_or_post, $var_name);
	if($item) {
		return HTMLFilterElement($item);
	} else {
		return $item;
	}
}



/**
 * This strips all HTML from the GET variable then returns it.
 * Input will be filtered regardless of the global 'filter_input' setting.
 *
 * @param string $var_name Name of the variable
 * @return mixed GET request variable value or array of values.
 */
function getGetText($var_name = false) {
	return _getRequestText('get', $var_name);
}

/**
 * This strips all HTML from the POST variable then returns it.
 * Input will be filtered regardless of the global 'filter_input' setting.
 *
 * @param string $var_name Name of the variable
 * @return mixed POST request variable value or array of values.
 */
function getPostText($var_name = false) {
	return _getRequestText('post', $var_name);
}

/**
 * This strips all HTML from the variable then returns it.
 * Input will be filtered regardless of the global 'filter_input' setting.
 *
 * @access private
 * @see getGetText
 * @see getPostText
 *
 * @param string $get_or_post Request variable type ('get' or 'post').
 * @param string $var_name Name of the variable
 * @return mixed HTTP request variable value or array of values.
 */
function _getRequestText($get_or_post, $var_name = false) {
	$item = _findRequestItem($get_or_post, $var_name);
	if(!$item) {
		return $item;
	} else {
		return  StripHTMLElement($item);
	}
}



/**
 * Return the specified GET request variable as an integer.
 *
 * This method will cast the variable as an integer if it isn't already. This is preferred
 * over getGet, as it guarantees that your variable is either an integer or 'null'.
 *
 * If you need to distinguish between 0 and unspecified value, be sure to use '==='.
 *
 * @code
 *   if (getGetInt('foo') === null) {
 *      echo 'GET variable not set';
 *   }
 * @endcode
 *
 * @param string $var_name HTTP GET request variable name.
 * @return mixed HTTP GET request value cast as an integer, or null.
 */
function getGetInt($var_name) {
	return _getRequestInt('get', $var_name);
}

/**
 * Return the specified GET request variable as an integer.
 *
 * This method will cast the variable as an integer if it isn't already. This is preferred
 * over getGet, as it guarantees that your variable is either an integer or 'null'.
 *
 * If you need to distinguish between 0 and unspecified value, be sure to use '==='.
 *
 * @code
 *   if (getPostInt('foo') === null) {
 *      echo 'POST variable not set';
 *   }
 * @endcode
 *
 * @param string $var_name HTTP GET request variable name.
 * @return mixed HTTP GET request value cast as an integer, or null.
 */
function getPostInt($var_name) {
	return _getRequestInt('post', $var_name);
}

/**
 * Return the specified HTTP request variable as an integer.
 *
 * This method will cast the variable as an integer if it isn't already. This is preferred
 * as it guarantees that your variable is either an integer or 'null'.
 *
 * @access private
 * @see getGetInt
 * @see getPostInt
 *
 * @param string $get_or_post Request variable type ('get' or 'post').
 * @param string $var_name HTTP GET request variable name.
 * @return mixed HTTP request value cast as an integer, or null.
 */
function _getRequestInt($get_or_post, $var_name) {
	$item = _findRequestItem($get_or_post, $var_name);
	
	// only return null/false/empty string if the item is STRICTLY EQUAL to those.
	// otherwise '0' will never come through.
	if ($item === null || $item === false || $item === '') {
		return null;
	} else {
		return verifyInt($item);
	}
}




/**
 * Filters bad HTML from $in with default settings.
 *
 * @param mixed $in variable (array or string) to filter.
 * @return boolean
 */
function HTMLFilterElement($in) {
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
function StripHTMLElement($in) {
	$filter = new InputFilter();
	$out = $filter->process($in);
	return $out;
}

/**
 * VerifyInt - take an item, an array or arraytree and make sure that each item is an integer.
 *
 * @param mixed $inNumber
 * @access public
 * @return void
 */
function VerifyInt($inNumber) {
	if (is_array($inNumber)) {
		foreach($inNumber as $key => $value) {
			$inNumber[$key] = VerifyInt($value);
		}
	} else if ($inNumber === '') {
		//blank is ok, because we're not checking that it's set to a value, just that if it has one, it is an integer.
		return '';
	} else {
		assert(is_numeric($inNumber));
		return (integer)$inNumber;
	}
	return $inNumber;
}