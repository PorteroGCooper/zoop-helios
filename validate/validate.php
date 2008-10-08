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
 * This validation is used by the guicontrols, forms and numerous other things to provide consistent validation.
 * Validator provides validation functions for both php based validation, and generating validation instructions to a javascript validation library.
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
class Validator
{
	/**
	 * getAttr
	 *
	 * validate wrapper for the functions in this class.
	 * the validate array must have 'type' => $type set for this to work.
	 *
  	 * @param array $validate
	 * @access public
  	 * @return string
	 */
	function getAttr($validate) // FOR JS VALIDATION
	{
		$function = strtolower("get{$validate['type']}Attr");

		$validatorArray = get_class_methods('Validator');
		foreach ($validatorArray as $key => $validatorElement)
			$validatorArray[$key] = strtolower($validatorElement);
		if (in_array($function, $validatorArray))
			return Validator::$function($validate);
		else
			return ""; // Not every Validation function has a javascript counterpart.
	}

	/**
	 * validate
	 * validate wrapper for the functions in this class.
	 *
	 * the validate array must have 'type' => $type set for this to work.
	 *
	 * @param mixed $value The value to be validated.
	 * @param array $validate An array passing parameters to the validation functions (accepts type, and various other things like max & min depending on the validation routine)
	 * @access public
  	 * @return array
	 */
	function validate($value, $validate) // FOR PHP VALIDATION
	{
		if (!isset($validate['type']))  // if no validation type is set, validate as true
 			  $result['result'] = true;

		$function = strtolower("validate{$validate['type']}");

		// handle required right here.
		if (isset($validate['required']) && $validate['required'] && strlen($value) < 1)
		{
			$result['message'] = "This field is required";
			$result['result'] = false;
			return $result;
		}

		if ((!isset($validate['required']) || $validate['required'] == false) && strlen($value) < 1)
		{
			$result['result'] = true;
			return $result;
		}

		$validatorArray = get_class_methods('Validator');
		foreach ($validatorArray as $key => $validatorElement)
			$validatorArray[$key] = strtolower($validatorElement);
		if (in_array($function, $validatorArray))
			return Validator::$function($value, $validate);
		else
			trigger_error("No known validation for {$validate['type']}");
	}

	/**
	 * boolValidate
	 * a boolean validate wrapper for the functions in this class.
	 * This function will return a boolean true / false for validation, rather than the array returned by the other functions.
	 * the validate array must have 'type' => $type set for this to work.
	 *
	 * @param mixed $value The value to be validated.
	 * @param array $validate An array passing parameters to the validation functions (accepts type, and various other things like max & min depending on the validation routine)
	 * @access public
  	 * @return array
	 */
	function boolvalidate($value, $validate)
	{
		$result = Validator::validate($value, $validate);

		if($result['result'] == true)
			return true;
		else
			return false;
	}

	/**
	 * validateStack
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
	 * type2ClassNames
	 * This function will convert the validation types to the appropriate string of class names to use with the javascript validator
	 *
	 * @param array $validate An array passing parameters to the validation functions (accepts type, and various other things like max & min depending on the validation routine)
	 * @access public
  	 * @return string
	 */
	function type2ClassNames($validate)
	{
		if (!isset($validate['type']))
			return "";

		switch (strtolower($validate['type']))
		{
			case "merge" :
			case "stack" :
				$names = " ";
				foreach ($validate['validators'] as $validator)
				{
					$names .= Validator::JSClassNames($validator) . " ";
				}
				return trim($names);
				break;
			default:
				return Validator::NameToJS($validate['type']);
				break;
		}
	}

	/**
	 * type2JSParamClassNames
	 * This function will convert the validation types and properties to the appropriate additional class names for the javascript validator. To be used with type2ClassNames
	 *
	 * @param array $validate An array passing parameters to the validation functions (accepts type, and various other things like max & min depending on the validation routine)
	 * @access public
  	 * @return string
	 */
	function type2JSParamClassNames($validate)
	{
		$classes = array();
		if (isset($validate['required']) && $validate['required'] == true)
			$classes[] = "required";

		if (isset($validate['max']) && $validate['type'] != 'length')
			$classes[] = "LessThan";

		if (isset($validate['min']) && $validate['type'] != 'length')
			$classes[] = "GreaterThan";

		return implode(" ", $classes);
	}

	/**
	 * getJSClassNames
	 * This function will convert the validation array to the appropriate string of class names to use with the javascript validator. Combines output of type2ClassNames and type2JSParamClassNames
	 *
	 * @param array $validate An array passing parameters to the validation functions (accepts type, and various other things like max & min depending on the validation routine)
	 * @access public
  	 * @return string
	 */
	function getJSClassNames($validate)
	{
		$cn = array();
		$cn[] = Validator::type2ClassNames($validate);
		$cn[] = Validator::type2JSParamClassNames($validate);

		return implode(" ", $cn);
	}

	/**
	 * nameToJS
	 * This function will take one validation type and return the javascript class name equivalent
	 *
	 * @param array $validate An array passing parameters to the validation functions (accepts type, and various other things like max & min depending on the validation routine)
	 * @access public
  	 * @return string
	 */
	function nameToJS($name)
	{
		switch(strtolower($name))
		{
			case "phone" :
			case "date" :
			case "email" :
			case "domain" :
			case "url" :
			case "ssn" :
			case "password" :
			case "ip" :
			case "regexp" :
				return "validate-$name";
				break;
			case "money" :
				return "validate-currency-dollar";
				break;
			case "creditcard" :
				return "validate-cc";
				break;
			case "float" :
			case "numeric" :
				return "validate-number";
    				break;
			case "alphanumeric" :
	   			return "validate-alphanum";
				break;
			case "equalto" :
	   			return "EqualTo";
				break;
			case "length" :
	   			return "Length";
				break;
			case "int" :
	   			return "validate-digits";
				break;
		}
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
  	 * @param mixed $value The value to be validated.
  	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validatePhone($value, $validate) // FOR PHP VALIDATION
	{
		$result = array('message' => "Must be a properly formatted (US) phone number with areacode, eg: 800-555-5555");
		if (preg_match('/\\(?[0-9]{3}\\)?[-. ]?[0-9]{3}[-. ]?[0-9]{4}/', $value))
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
	function getLengthAttr($validate) // FOR JS VALIDATION
	{
		$answer = "validate=\"length";

 		if (isset($validate['max']) && is_numeric($validate['max']) && (!isset($validate['min']) || !is_numeric($validate['min'])))
 			$validate['min'] = 1;

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
	function validateInt($value, $validate = array())
	{
		$result = array('message' => "Must be an integer");

		if (is_numeric($value) && preg_match('/\\b\\d+\\b/', $value))
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
	function getEmailAttr($validate) // FOR JS VALIDATION
	{
		$answer = "validate=\"email";

		if (isset($validate['strict']))
		{
			if($validate['strict'])
				$validate['format'] = 3;
			else
				$validate['format'] = 2;

			$answer .= "|{$validate['strict']}";
		}

		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .= "|bok";
		}
		$answer .="\"";
		return $answer;
	}

	/**
	 * validateEmail
	 *
  	 * @param mixed $value The value to be validated.
	 * @param array $validate
	 * @access public
  	 * @return array
	 */
	function validateEmail($value, $validate = array())
	{
		$result = array('message' => "Must be a properly formatted valid email address, eg: user@domain.com");

		if (preg_match('/\\b[A-Z0-9._%-]+@[A-Z0-9._%-]+\\.[A-Z]{2,4}\\b/i', $value))
		{
   			$result['result'] = true;
		}
		else
			$result['result'] = false;

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
	function getUrlAttr($validate)
	{
		$answer = "validate=\"url";

		if (isset($validate['hosts']))
		{
			$answer .= "|{$validate['hosts']}";
		}
		else
			$answer .= "|http,https";

		if (isset($validate['hostOptional']) && $validate['hostOptional'])
			$answer .= "|1";
		else
			$answer .= "|0";

		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .= "|bok";
		}
		$answer .="\"";
		return $answer;
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
	function getDateAttr($validate)
	{
		return ""; // THE Fvalidate routine doesn't quite work properly

		$answer = "validate=\"date";

		if (!isset($validate['format']))
			$validate['format'] = "us";

		if ($validate['format'] == "writeout") // let PHP handle this
			return "";

		switch ($validate['format'])
		{
			case "us":
				$answer .= "|mm/dd/yyyy";
				break;
			case "european":
				$answer .= "|dd/mm/yyyy";
				break;
			case "universal":
				$answer .= "|yyyy/mm/dd";
				break;
			default:
				$answer .= "|mm/dd/yyyy";
				break;
		}

		if (!isset($validate['delim']))
			$validate['delim'] = "-";

		switch ($validate['delim'])
		{
			case "/":
				$answer .= "|/";
				break;
			case "-/":
				$answer .= "|-/";
				break;
			default:
				$answer .= "|-/";
				break;
		}

		if (isset($validate['relation']) && isset($validate['date']))
		{
			switch ($validate['relation'])
			{
				case "none":
					$answer .= "|0";
					break;
				case "before":
					$answer .= "|1";
					break;
				case "beforeOn":
					$answer .= "|2";
					break;
				case "after":
					$answer .= "|3";
					break;
				case "afterOn":
					$answer .= "|4";
					break;
				default:
					$answer .= "|0";
					break;
			}

			$answer .= "|{$validate['relation']}";
		}


		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .= "|bok";
		}
		$answer .="\"";
		return $answer;
	}
}
?>
