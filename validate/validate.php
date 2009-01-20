<?php

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
 * Validator
 *
 * Validator is intended to provide a standard means of validating types for the Zoop Framework.
 * This validation is used by the guicontrols, forms and numerous other things to provide
 * consistent validation. Validator provides validation functions for both php based validation,
 * and generating validation instructions to a javascript validation library.
 *
 * php functions are named validateType
 * js instruction functions are named getTypeAttr
 *
 * @package validate
 * @version $id$
 * @static
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class Validator {

	private static $validators = null;
	private static $jsValidators = null;
	private static $nativeValidators = null;
	private static $jsValidationRules = null;
	
	static function initValidators() {
		self::$validators = array();
		self::$jsValidators = array();
		self::$nativeValidators = array();
		
		// set up ours
		foreach (get_class_methods('Validator') as $_key => $_val) {
			if (substr($_val, 0, 10) == 'validateJS') {
				if ($type = strtolower(substr($_val, 10))) self::$jsValidators[$type] = $_val;
			} else if (substr($_val, 0, 8) == 'validate') {
				if ($type = strtolower(substr($_val, 8))) self::$validators[$type] = $_val;
			}
		}
		
		// add the native validators
		if (function_exists('filter_list')) {
			foreach (filter_list() as $filter) {
				switch ($filter) {
					case 'int':
						self::$nativeValidators['int'] = FILTER_VALIDATE_INT;
						break;
					case 'boolean':
						self::$nativeValidators['bool'] = FILTER_VALIDATE_BOOLEAN;
						break;
					case 'float':
						self::$nativeValidators['float'] = FILTER_VALIDATE_FLOAT;
						break;
					case 'validate_regexp':
						self::$nativeValidators['regex'] = FILTER_VALIDATE_REGEXP;
						break;
					case 'validate_url':
						self::$nativeValidators['url'] = FILTER_VALIDATE_URL;
						break;
					case 'validate_email':
						self::$nativeValidators['email'] = FILTER_VALIDATE_EMAIL;
						break;
					case 'validate_ip':
						self::$nativeValidators['ip'] = FILTER_VALIDATE_IP;
						break;
					default:
						break;
				}
			}
		}
		
		global $gui;
		
		$gui->add_jquery();
		$gui->add_js('/zoopfile/gui/js/jquery.validate.js');
		$gui->add_jquery('$("form").validate();');
	}
	
	static function validators($type = null) {
		if ($type !== null) {
			$type = strtolower($type);
			if (isset(self::$validators[$type])) return self::$validators[$type];
			else return false;
		}
		return self::$validators;
	}
	
	static function jsValidators($type = null) {
		if ($type !== null) {
			$type = strtolower($type);
			if (isset(self::$jsValidators[$type])) return self::$jsValidators[$type];
			else return false;
		}
		return self::$jsValidators;
	}
	
	static function nativeValidators($type = null) {
		if ($type !== null) {
			$type = strtolower($type);
			if (isset(self::$nativeValidators[$type])) return self::$nativeValidators[$type];
			else return false;
		}
		
		return self::$nativeValidators;
	}
	
	/**
	 * Validate the given value (based on validation options).
	 * 
	 * The $validate options requires a 'type' => $type index.
	 *
	 * @param mixed $value The value to be validated.
	 * @param array $validate Validation options.
	 *    An array of validation parameters, such as type, min/max, etc. as required by validation type.
	 * @access public
	 * @return array Validation result
	 */
	function validate($value, $validate) {
		// if no validation type is set, validate as true
		if (!isset($validate['type'])) $result['result'] = true;
		
		// If a value isn't set, handle 'required' then return.
		if (empty($value)) {
			// handle required right here.
			if (isset($validate['required']) && $validate['required']) {
				$result['message'] = "This field is required";
				$result['result'] = false;
				return $result;
			}
			if (!isset($validate['required']) || $validate['required'] == false) {
				$result['result'] = true;
				return $result;
			}
		}
		
		if ($function = self::validators($validate['type'])) {
			return self::$function($value, $validate);
		} else {
			trigger_error('No known validation for ' . $validate['type']);
		}
	}
	
	/**
	 * Handle JavaScript validation settings.
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string A set of classes to add to a form element for validation.
	 */
	function validateJS($validate) {
		if (isset($validate['type']) && $function = self::jsValidators($validate['type'])) {
			$classes = self::$function($validate);
		} else {
			$classes = array();
		}
		
		if (isset($validate['required']) && $validate['required']) $classes[] = 'required';
		return $classes;
	}

	/**
	 * Return a boolean (true/false) value for validation, rather than the array returned by other
	 * validation functions.
	 *
	 * @see Validator::validate()
	 * @param mixed $value The value to be validated.
	 * @param array $validate Validation options.
	 * @access public
  	 * @return bool Validation result
	 */
	function boolValidate($value, $validate) {
		$result = Validator::validate($value, $validate);
		
		return ($result['result'] == true) ? true : false;
	}

	/**
	 * validateSTack
	 * a function to stack validators together.
	 * This function will validate more than one validator, stacked in order one at a time. Permits validating things like Alpahnumeric & Length together for example.
	 * It will return as soon as one of the validators fails. This is particularly useful if one of the validators performs a sql check.
	 * the validate array must have 'validators' => array of individual "validate" arrays  set for this to work.
	 *
	 * @param mixed $value The value to be validated.
	 * @param array $validate An array passing parameters to the validation functions (accepts type, and various other things like max & min depending on the validation routine)
	 * @access public
  	 * @return array
	 */
	function validateStack($value, $validate)
	{
		if (!isset($validate['validators']) || empty($validate['validators']))
			trigger_error('you need to define some validators');

		foreach($validate['validators'] as $validator)
		{
			$result = Validator::validate($value, $validator);

			if($result['result'] != true)
				return $result;
		}

		return $result;
	}

	/**
	 * validateMerge
	 a function to combine validators together.
	 * This function will validate more than one validator, in order one at a time. Permits validating things like Alpahnumeric & Length together for example.
	 * It will perform all validations before it returns the combined results.
	 * the validate array must have 'validators' => array of individual "validate" arrays  set for this to work.
	 *
	 * @param mixed $value The value to be validated.
	 * @param array $validate An array passing parameters to the validation functions (accepts type, and various other things like max & min depending on the validation routine)
	 * @access public
  	 * @return array
	 */
	function validateMerge($value, $validate)
	{
		$result['result'] = true;
		$result['message'] = "";

		if (!isset($validate['validators']) || empty($validate['validators']))
			trigger_error('you need to define some validators');

		foreach($validate['validators'] as $validator)
		{
			$tmpresult = Validator::validate($value, $validator);

			if($tmpresult['result'] != true)
			{
				$result['result'] = false;
				$result['message'] .= $tmpresult['message'] . "<br>";
			}
		}

		return $result;
	}
	
	/**
	 * getPhoneAttr
	 *
	 * @param array $validate accepts format => strict and required => true
	 * @access public
  	 * @return string
	 */
	function getPhoneAttr($validate) // FOR JS VALIDATION
	{
		$answer = "validate=\"phone";
		if(isset($validate['format']))
		{
			if($validate['format'] == 'strict')
			{
				$validate['format'] = 1;
			}
			else
			{
				$validate['format'] = 0;
			}
			$answer .= "|{$validate['format']}";
		}
		if(!isset($validate['required']) || !$validate['required'])
		{
			$answer .= "|bok";
		}
		$answer .="\"";
		return $answer;
	}

	/**
	 * validatePhone
	 *
	 * @todo Add an international phone number validation method.
  	 * @param mixed $value The value to be validated.
  	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validatePhone($value, $validate) // FOR PHP VALIDATION
	{
		$result = array('message' => "Must be a properly formatted (US) phone number with areacode, eg: 800-555-5555");
		if (preg_match('/^(1[-. ]?)?\\(?[0-9]{3}\\)?[-. ]?[0-9]{3}[-. ]?[0-9]{4}$/', $value))
			$result['result'] = true;
		else
			$result['result'] = false;

		return $result;
	}

	/**
	 * getLengthAttr
	 *
	 * @param array $validate
	 * @access public
	 * @return string
	 */
	function validateJSLength($validate) {
		global $gui;
		$rules = array();

		if(isset($validate['min']) && is_numeric($validate['min'])) {
			$rules['minlength'] = $validate['min'];
		}
		
		if (isset($validate['max']) && is_numeric($validate['max'])) {
			$rules['maxlength'] = $validate['max'];
			
			// there's an implied min length here...
			if (!isset($validate['min']) || !is_numeric($validate['min'])) $rules['maxlength'] = $validate['min'];
		}
		
		if (count($rules)) {
			$custom_class = 'v' . count(self::$jsValidationRules);
			$rule = json_encode($rules);
			$md5 = hash('md5', $rule);
			
			if (isset(self::$jsValidationRules[$md5])) {
				$custom_class = self::$jsValidationRules[$md5];
			} else {
				$custom_class = 'v' . count(self::$jsValidationRules[$md5]);
				self::$jsValidationRules[$md5] = $custom_class;
				$gui->add_jquery('$.validator.addClassRules("' . $custom_class . '", ' . $rule . ');');
			}
			return array($custom_class);
		}
		
		return array();
	}

	/**
	 * validateLength
	 *
  	 * @param mixed $value The value to be validated.
  	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateLength($value, $validate) // FOR PHP VALIDATION
	{
		!isset($validate['min']) ? $validate['min'] = 0 : $validate['min'];
		!isset($validate['max']) ? $validate['max'] = false : $validate['max'];

		if ($validate['max'] == false)
			$result = array('message' => "Must be longer than {$validate['min']} characters.");
		elseif ($validate['min'] == 0)
			$result = array('message' => "Must be shorter than {$validate['max']}characters.");
		else
			$result = array('message' => "Must be between {$validate['min']} and {$validate['max']} characters long");

		if (strlen($value) >= $validate['min'] && (strlen($value) <= $validate['max'] || $validate['max'] === false))
		{
			$result['result'] = true;
		}
		else
			$result['result'] = false;

		return $result;
	}

	/**
	 * validateQuantity
	 * Validate the number of elements in an array
	 *
  	 * @param mixed $value The value to be validated.
  	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateQuantity($value, $validate)
	{
		!isset($validate['min']) ? $validate['min'] = 0 : $validate['min'];
		!isset($validate['max']) ? $validate['max'] = false : $validate['max'];

		if ($validate['max'] == false)
			$result = array('message' => "You must select at least {$validate['min']}.");
		elseif ($validate['min'] == 0)
			$result = array('message' => "You must select at most {$validate['max']}.");
		else
			$result = array('message' => "You must select between {$validate['min']} and {$validate['max']}, inclusive.");

		if (count($value) >= $validate['min'] && (count($value) <= $validate['max'] || $validate['max'] === false))
		{
			$result['result'] = true;
		}
		else
			$result['result'] = false;

		return $result;
	}

	/**
	 * validateCreditCard
	 * Validate a credit card input to see if format is valid.
	 * By no means a way of validating an acutal card, merely checks if the format complies
	 * with the standard formats
	 *
  	 * @param mixed $value The value to be validated.
  	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateCreditCard($value, $validate)
	{
		$result = array('message' => "You must provide a valid credit card.");

		if (preg_match('/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{14}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/', $value))
		{
			$result['result'] = true;
		}
		else
			$result['result'] = false;

		return $result;
	}

	/**
	 * getEqualToAttr
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function getEqualToAttr($validate) // FOR JS VALIDATION
	{
		if (!isset($validate['field']))
			return ;

		$answer = "validate=\"equalto|{$validate['field']}";

		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .="|bok";
		}
		$answer .="\"";
		return $answer;
	}
	/**
	 * validateEqualTo
	 *
  	 * @param mixed $value The value to be validated.
  	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateEqualTo($value, $validate)
	{
		$post = getRawPost();

		$field = $validate['field'];

		$result = array('message' => "Must match {$field}.");

		if (!isset($post[$field]) || $value != $post[$field])
			$result['result'] = false;
		else
			$result['result'] = true;

		return $result;
	}

	/**
	 * getIntAttr
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function getIntAttr($validate) // FOR JS VALIDATION
	{
		$answer = "validate=\"number|0";

		if (isset($validate['max']) && is_numeric($validate['max']) && (!isset($validate['min']) || !is_numeric($validate['min'])))
			$validate['min'] = 0;

		if(isset($validate['min']) && is_numeric($validate['min']))
			$answer .= "|{$validate['min']}";
		if (isset($validate['max']) && is_numeric($validate['max']))
			$answer .= "|{$validate['max']}";

		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .= "|bok";
		}
		$answer .="\"";
		return $answer;
	}

	/**
	 * validateInt
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateInt($value, $validate = array()) {
		$result = array('message' => "Must be an integer");

		if (is_numeric($value) && ((int)$value == (string)$value)) {
			if (isset($validate['min']) && $value < $validate['min']) {
				$result['result'] = false;
				$result['message'] .=  ", larger than {$validate['min']}";
			}
			elseif (isset($validate['max']) && $value > $validate['max']) {
				$result['result'] = false;
				$result['message'] .=  ", smaller than {$validate['max']}";
			}
			else {
				$result['result'] = true;
			}
		}
		else
			$result['result'] = false;

		return $result;
	}

	/**
	 * getFloatAttr
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function getFloatAttr($validate) // FOR JS VALIDATION
	{
		$answer = "validate=\"number|1";

		if (isset($validate['max']) && is_numeric($validate['max']) && (!isset($validate['min']) || !is_numeric($validate['min'])))
			$validate['min'] = 0;

		if(isset($validate['min']) && is_numeric($validate['min']))
			$answer .= "|{$validate['min']}";
		if (isset($validate['max']) && is_numeric($validate['max']))
			$answer .= "|{$validate['max']}";

		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .= "|bok";
		}
		$answer .="\"";
		return $answer;
	}

	/**
	 * validateFloat
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateFloat($value, $validate = array())
	{
		$result = array('message' => "Must be a numeric value");

		if (is_numeric($value) && preg_match('/[-+]?\\b(?:[0-9]*\\.)?[0-9]+\\b/', $value))
		{
			if (isset($validate['min']) && $value < $validate['min'])
			{
				$result['result'] = false;
				$result['message'] .=  ", larger than {$validate['min']}";
			}
			elseif (isset($validate['max']) && $value > $validate['max'])
			{
				$result['result'] = false;
				$result['message'] .=  ", smaller than {$validate['max']}";
			}
			else
				$result['result'] = true;
		}
		else
   			$result['result'] = false;

		return $result;
	}

	/**
	 * getNumericAttr
	 *
	 * @param array $validate
	 * @access public
	 * @return string
	 */
	function getNumericAttr($validate) // FOR JS VALIDATION
	{
		$answer = "validate=\"numeric";

		if(isset($validate['min']) && is_numeric($validate['min']))
			$answer .= "|{$validate['min']}";

		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .= "|bok";
		}
		$answer .="\"";
		return $answer;
	}

	/**
	 * validateNumeric
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
	 * @return array
	 */
	function validateNumeric($value, $validate = array())
	{
		$result = array('message' => "Must be a numeric value");

		if (is_numeric($value))
		{
			if (isset($validate['min']) && $value < $validate['min'])
			{
				$result['result'] = false;
				$result['message'] .=  ", larger than {$validate['min']}";
			}
			elseif (isset($validate['max']) && $value > $validate['max'])
			{
				$result['result'] = false;
				$result['message'] .=  ", smaller than {$validate['max']}";
			}
			else
				$result['result'] = true;
		}
		else
			$result['result'] = false;

		return $result;
	}

    /**
     * getAlphaNumericAttr
     *
     * @param array $validate
     * @access public
     * @return array
     */
    function getAlphaNumericAttr($validate) // FOR JS VALIDATION
	{
		$answer = "validate=\"alnum";

		if(isset($validate['min']) && is_numeric($validate['min']))
			$answer .= "|{$validate['min']}";
		else
			$answer .= "|1";

		if (isset($validate['case']))
		{
			switch ($validate['case'])
			{
				case 'any':
					$answer .= "|A";
					break;
				case 'upper':
				case 'uc':
					$answer .= "|U";
					break;
				case 'lower':
				case 'lc':
					$answer .= "|L";
					break;
				case 'capitol':
				case 'ic':
					$answer .= "|C";
					break;
				default:
					$answer .= "|A";
					break;
			}

		}
		else
			$answer .= "|A";

		if (isset($validate['numbers']))
		{
			if ($validate['numbers'])
				$answer = "|true";
			else
				$answer = "|false";
		}
		else
			$answer = "|true";

		if (isset($validate['spaces']))
		{
			if ($validate['spaces'])
				$answer = "|true";
			else
				$answer = "|false";
		}
		else
			$answer = "|true";

		if (isset($validate['puncs']))
		{
			switch ($validate['puncs'])
			{
				case 'any':
					$answer .= "|any";
					break;
				case 'none':
					$answer .= "|none";
					break;
				default:
					$answer .= "|{$validate['puncs']}";
					break;
			}

		}
		else
			$answer .= "|any";

		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .= "|bok";
		}
		$answer .="\"";
		return $answer;
	}

	/**
	 * validateAlphaNumeric
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
	 * @return array
	 */
	function validateAlphaNumeric($value, $validate = array())
	{
		$result = array('message' => "Must be an AlphaNumeric value (no symbols)");

		if (preg_match('/\\A[\\w\\s]+\\z/', $value)) {
			$result['result'] = true;
		} else {
			$result['result'] = false;
		}

   		return $result;
	}

	/**
	 * getMoneyAttr
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function getMoneyAttr($validate) // FOR JS VALIDATION
	{
		$answer = "validate=\"money";

		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .= "|bok";
		}
		$answer .="\"";
		return $answer;
	}

	/**
	* validateMoney
	*
	* Validates String to see if it is formatted as money
	* @param mixed $value The value to be validated.
	* @param array $validate
	* @access public
	* @return array
	*/
	function validateMoney($value, $validate = array())
	{
		$result = array('message' => "Must be a value of money (numeric)");
		if (preg_match('/\\b[0-9]{1,3}(?:,?[0-9]{3})*(?:\\.[0-9]{2})?\\b/', $value))
		{
			$result['result'] = true;
		}
		else
   			$result['result'] = false;

   		return $result;
	}

	/**
	 * getZipAttr
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function getZipAttr($validate) // FOR JS VALIDATION
	{
		$answer = "validate=\"zip";

		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .= "|bok";
		}
		$answer .="\"";
		return $answer;
	}

	/**
	 * validateZip
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateZip($value, $validate = array()) // VALIDATES US OR CANADA
	{
		$result = array('message' => "Must be a properly formatted US or Canadian zip code");

		if (preg_match('/\\b[0-9]{5}(?:-[0-9]{4})?\\b/', $value) || preg_match('/\\b[ABCEGHJKLMNPRSTVXY][0-9][A-Z] [0-9][A-Z][0-9]\\b/i' , $value))
		{
			$result['result'] = true;
		}
		else
			$result['result'] = false;

		return $result;
	}

	/**
	 * getEmailAttr
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function validateJSEmail($validate) {
		return array('email');
	}

	/**
	 * validateEmail
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateEmail($value, $validate = array()) {
		$result = array('message' => "Must be a properly formatted valid email address, eg: user@domain.com");

		// split it on the @sign, validate the first half as email, second half as domain...
		$chunks = explode('@', $value);
		
		// try the domain half, then the username half
		if (count($chunks) < 2) {
			$result['result'] = false;
		} else if (!self::boolvalidate($chunks[1], array('type' => 'domain'))) {
			$result['result'] = false;
		} else if (preg_match('/^[-+_A-Z0-9]+(\.[-+_A-Z0-9]+)*$/i', $chunks[0])) {
   			$result['result'] = true;
		} else {
			$result['result'] = false;
		}

		return $result;
	}

	/**
	 * validateDomain
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateDomain($value, $validate = array())
	{
		$result = array('message' => "Must be a properly formatted domain, eg: domain.com");

		if (preg_match('/\\b[A-Z0-9._%-]+\\.[A-Z]{2,4}\\b/i', $value))
		{
   			$result['result'] = true;
		}
		else
			$result['result'] = false;

		return $result;
	}

	/**
	 * getDomainAttr
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function getDomainAttr($validate)
	{
		return "";
	}

	/**
	 * validateUrl
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateUrl($value, $validate = array())
	{
		$result = array('message' => "Must be a properly formatted url, eg: http://domain.com/directory");

		if (preg_match('/https?:\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|]/i', $value))
		{
			$result['result'] = true;
		}
		else
			$result['result'] = false;

		return $result;
	}

	/**
	 * getUrlAttr
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function validateJSUrl($validate) {
		return array('url');
	}

	/**
	 * validateIP
	 *
	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
 	 * @return array
	 */
	function validateIP($value, $validate = array())
	{
		$result = array('message' => "Must be a ip address in the format XXX.XXX.XXX.XXX");

		if (preg_match('/\\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\b/', $value))
		{
			$result['result'] = true;
		}
		else
			$result['result'] = false;

		return $result;
	}

	/**
	 * getPasswordAttr
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function getPasswordAttr($validate)
	{
		return "";
		//$validate['regExp'] = "/\\\\A(?=[-_a-zA-Z0-9]*?[A-Z])(?=[-_a-zA-Z0-9]*?[a-z])(?=[-_a-zA-Z0-9]*?[0-9])\\\\S{6,}\\\\z/";
		//return Validator::getRegExpAttr($validate);
	}

	/**
	 * validatePassword
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validatePassword($value, $validate = array()) // AT LEAST 6 DIGITS, 1 UC, 2LC, 1 NUMERAL
	{
		$result = array('message' => "Must be a secure password consisting of 1 UC, 2 LC and 1 Num, no shorter than 6 characters");
		if (preg_match('/\\A(?=[-_a-zA-Z0-9]*?[A-Z])(?=[-_a-zA-Z0-9]*?[a-z])(?=[-_a-zA-Z0-9]*?[0-9])\\S{6,}\\z/', $value))
		{
			$result['result'] = true;
		}
		else
		{
			$result['result'] = false;
		}
		return $result;
	}

	/**
	 * getSSNAttr
	 *
  	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function getSSNAttr($validate)
	{
		return "";
	}

	/**
	 * validateSSN
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateSSN($value, $validate = array())
	{
		$result = array('message' => "Must be a valid US Social Security Number");
		if (preg_match('/\\b[0-9]{3}-[0-9]{2}-[0-9]{4}\\b/', $value))
		{
			$result['result'] = true;
		}
		else
			$result['result'] = false;

		return $result;
	}

	/**
	 * 	function getRegExpAttr($validate) // FOR JS VALIDATION
	 *
	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function getRegExpAttr($validate) // FOR JS VALIDATION // ONLY WORKS ON TEXT
	{
		$answer = "validate=\"custom";

		if(isset($validate['regExp']))
			$answer .= "|{$validate['regExp']}";
		else
			return;

		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .= "|bok";
		}

		$answer .="\"";
		return $answer;
	}

	/**
	 * validateRegExp
	 *
  	 * @param mixed $value The value to be validated.
  	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateRegExp($value, $validate)
	{
		if (preg_match($validate['regExp'], $value))
			return array('result' => true);
		else
			return array('result' => false);
	}

	/**
	 * validateDbUnique
	 * This function checks the database to see if a value already exists. Uses the default db.
	 *
  	 * @param mixed $value The value to be validated.
  	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateDbUnique($value, $validate)
	{
		$result['message'] = "is already in use, please choose another one";

		if (isset($validate['where']))
			$wherestr = " AND ({$validate['where']}) ";
		else
			$wherestr = "";

		if (sql_check("SELECT {$validate['field']} from {$validate['table']} where {$validate['field']}=\"$value\" $wherestr"))
			$result['result'] = false;
		else
			$result['result'] = true;

		return $result;
	}

	/**
	 * validateDate
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateDate($value, $validate = array())
	{
		if (!isset($validate['format']))
			$validate['format'] = "universal";

		switch ($validate['format'])
		{
			case "writeout":
				$result = array('message' => "Must be a properly formatted date, eg: DD MMM YYYY");
				$regex = '/(0[1-9]|[12][0-9]|3[01])[- \/.](jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[- \/.](19|20)[0-9]{2}/i';
				break;
			case "us":
				$result = array('message' => "Must be a properly formatted date, eg: mm/dd/yyyy");
				$regex = '/(0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[- \/.](19|20)[0-9]{2}/';
				break;
			case "european":
				$result = array('message' => "Must be a properly formatted date, eg: dd/mm/yyyy");
				$regex = '/(0[1-9]|[12][0-9]|3[01])[- \/.](0[1-9]|1[012])[- \/.](19|20)[0-9]{2}/';
				break;
			case "universal":
				$result = array('message' => "Must be a properly formatted date, eg: yyyy/mm/dd");
				$regex = '/(19|20)[0-9]{2}[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])/';
				break;
			default:
				$result = array('message' => "Must be a properly formatted date, eg: mm/dd/yyyy");
				$regex = '/(0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[- \/.](19|20)[0-9]{2}/';
				break;
		}

		if (preg_match($regex, $value))
		{
			$result['result'] = true;
		}
		else
			$result['result'] = false;

		return $result;
	}

	/**
	 * getDateAttr
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function validateJSDate($validate) {
		return array('date');
	}
}

Validator::initValidators();