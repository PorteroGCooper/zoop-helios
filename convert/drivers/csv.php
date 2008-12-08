<?php

//include("XML/Serializer.php");

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
 * Takes a php array and converts it into csv or vice versa
 *
 * @package
 * @version $id$
 * @copyright 1997-2008 Portero Inc.
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class convert_driver_csv extends convert_driver_abstract {

	/**
	 * Take an array and convert it to an csv string
	 * @param $data array
	 * @return string
	 */
	public function to($data, $options = array()) {
		$defaultOptions = Config::get('zoop.convert.csv');
		$options = $options + $defaultOptions;
		$delimiter = $options['delimiter'];
		$enclosure = $options['enclosure'];
		
		$delimiter_esc = preg_quote($delimiter, '/');
		$enclosure_esc = preg_quote($enclosure, '/');
		
		if (count($data) < 1 ) { 
			return '';
		}

		$output = array();
		$first = true;

		foreach ($data as $record) {
			if ($first && $options['use_keys']) {
				$fieldNames = array_keys($record);
				$output[] = join($delimiter, $fieldNames);
			} else {
				$row = array();

				foreach ($record as $field) {
					$row[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
						$enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
					) : $field;
				}

				$output[] = join($delimiter, $row);
			}
			$first = false;
		}

		return join ("\n", $output);
	}

	/**
	 * Take a csv string and return an array
	 * @param $string string
	 * @return array
	 */
	public function from($data, $options = array()) {

	}

}
