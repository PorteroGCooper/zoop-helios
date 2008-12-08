<?php
/**
* @category zoop
* @package convert
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
 * Takes a php array and converts it into json or vice versa
 *
 * @package
 * @version $id$
 * @copyright 1997-2008 Portero Inc.
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */

class convert_driver_json extends convert_driver_abstract {

	/**
	 * Take an array and convert it to a serialized php string
	 * @param $data array
	 * @return string
	 */
	public function to($data, $options = array()) {
		return json_encode($data);
	}

	/**
     * Take a serialized string and return a php data structure (array)
     * @param $string string
     * @return array
     */
	public function from($data, $options = array()) {
		return json_decode($data, true);
	}


}
