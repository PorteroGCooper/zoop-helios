<?php

include("XML/Serializer.php");

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
 * Takes a php array and converts it into xml or vice versa
 *
 * @package
 * @version $id$
 * @copyright 1997-2008 Portero Inc.
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class convert_driver_xml extends convert_driver_abstract {

	/**
	 * Take an array and convert it to an xml string
	 * @param $data array
	 * @param $options array
	 * @return string
	 */
	public function to($data, $options = array()) {

		$serializer_options = Config::get('zoop.convert.xml.serializer');
		$serializer_options['linkbreak'] = "\n";
		$serializer_options = array_merge($serializer_options, $options );

		if ($serializer_options['use_cdata']) {
			$serializer_options['replaceEntities'] = XML_UTIL_CDATA_SECTION;
		}

		$serializer = new XML_Serializer($serializer_options);
		$result = $serializer->serialize($data);

		// check result code and display XML if success
		if($result === true) {
			$XML = $serializer->getSerializedData();
		}

		return $XML;
	}

	/**
	 * Take a XML string and return a Simple XML object or array, default SimpleXML
	 * @param $string string
	 * @param $options array
	 * @return array
	 */
	public function from($data, $options = array()) {


		if (Config::get('zoop.convert.xml.return_simpleXML')) {
			return $simpleXMLObj;
		} else {
			return convert_driver_xml::SimpleXmlElementObjectIntoArray($simpleXMLObj);
		}
	}



	/**
	 * Method to take a Simple XML object and convert it to array
	 *
	 * Taken from :
	 * @url http://www-128.ibm.com/developerworks/xml/library/x-xml2jsonphp/
	 * @author Steve Francia <steve.francia+zoop@gmail.com>
	 * @author Senthil Nathan (sen@us.ibm.com), Senior Software Engineer, IBM
	 * @author Edward J Pring (pring@us.ibm.com), Senior Software Engineer, IBM
	 * @author John Morar (morar@us.ibm.com), Senior Technical Staff Member, IBM
	 * @param $simpleXmlElementObject
	 * @param $recursionDepth
	 * @return unknown_type
	 */
	public static function SimpleXmlElementObjectIntoArray($simpleXmlElementObject, &$recursionDepth=0) {
		// Keep an eye on how deeply we are involved in recursion.
		if ($recursionDepth > Config::get('zoop.convert.xml.max_recursion_depth')) {
			// Fatal error. Exit now.
			return(null);
		}

		if ($recursionDepth == 0) {
			if (get_class($simpleXmlElementObject) != Config::get('zoop.convert.xml.simple_xml.php_class')) {
				// If the external caller doesn't call this function initially
				// with a SimpleXMLElement object, return now.
				return(null);
			} else {
				// Store the original SimpleXmlElementObject sent by the caller.
				// We will need it at the very end when we return from here for good.
				$callerProvidedSimpleXmlElementObject = $simpleXmlElementObject;
			}
		} // End of if ($recursionDepth == 0) {

		if (get_class($simpleXmlElementObject) == Config::get('zoop.convert.xml.simple_xml.php_class')) {
			// Get a copy of the simpleXmlElementObject
			$copyOfsimpleXmlElementObject = $simpleXmlElementObject;
			// Get the object variables in the SimpleXmlElement object for us to iterate.
			$simpleXmlElementObject = get_object_vars($simpleXmlElementObject);
		}

		// It needs to be an array of object variables.
		if (is_array($simpleXmlElementObject)) {
			// Initialize the result array.
			$resultArray = array();
			// Is the input array size 0? Then, we reached the rare CDATA text if any.
			if (count($simpleXmlElementObject) <= 0) {
				// Let us return the lonely CDATA. It could even be
				// an empty element or just filled with whitespaces.
				return (trim(strval($copyOfsimpleXmlElementObject)));
			}

			// Let us walk through the child elements now.
			foreach($simpleXmlElementObject as $key=>$value) {
				// When this block of code is commented, XML attributes will be
				// added to the result array.
				// Uncomment the following block of code if XML attributes are
				// NOT required to be returned as part of the result array.

				if (!Config::get('zoop.convert.xml.simple_xml.maintain_attributes')) {
					 if((is_string($key)) && ($key == Config::get('zoop.convert.xml.simple_xml.object_property_for_attributes'))) {
					 continue;
					 }
				}

				// Let us recursively process the current element we just visited.
				// Increase the recursion depth by one.
				$recursionDepth++;
				$resultArray[$key] =
				convert_driver_xml::SimpleXmlElementObjectIntoArray($value, $recursionDepth);

				// Decrease the recursion depth by one.
				$recursionDepth--;
			} // End of foreach($simpleXmlElementObject as $key=>$value) {

			if ($recursionDepth == 0) {
				// That is it. We are heading to the exit now.
				// Set the XML root element name as the root [top-level] key of
				// the associative array that we are going to return to the caller of this
				// recursive function.
				$tempArray = $resultArray;
				$resultArray = array();
				$resultArray[$callerProvidedSimpleXmlElementObject->getName()] = $tempArray;
			}

			return ($resultArray);
		} else {
			// We are now looking at either the XML attribute text or
			// the text between the XML tags.
			return (trim(strval($simpleXmlElementObject)));
		} // End of else
	} // End of function SimpleXmlElementObjectIntoArray.

}
