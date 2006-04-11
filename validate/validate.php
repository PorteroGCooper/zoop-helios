<?
/**
* @package validate
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
* @package validate
*
* @author  Steve Francia webmaster@supernerd.com
* @static
*/
class Validator
{
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

	function validate($value, $validate) // FOR PHP VALIDATION
	{
		if (!isset($validate['type']))  // if no validation type is set, validate as true
 			  $result['result'] = true;

		$function = strtolower("validate{$validate['type']}");
		//echo_r(get_class_methods('validate'));

		// handle required right here.
		if (isset($validate['required']) && $validate['required'] && strlen($value) < 1)
			$result['result'] = false;

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

	function validatePhone($value, $validate) // FOR PHP VALIDATION
	{
		$result = array('message' => "Must be a properly formatted (US) phone number with areacode, eg: 800-555-5555");
		if (preg_match('/\\(?[0-9]{3}\\)?[-. ]?[0-9]{3}[-. ]?[0-9]{4}/', $value))
			$result['result'] = true;
		else
			$result['result'] = false;

		return $result;
	}

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
		if (strlen($value) >= $validate['min'] && strlen($value) <= $validate['max'])
			$result['result'] = true;
		else
			$result['result'] = false;

		return $result;
	}

	function getEqualToAttr($validate) // FOR JS VALIDATION
	{
		if (!isset($validate['equal_id']))
			return ;

		$answer = "validate=\"equalto|{$validate['equal_id']}";

		if(!isset($validate['required']) || $validate['required'] == 0)
		{
			$answer .="|bok";
		}
		$answer .="\"";
		return $answer;
	}
	function validateEqualTo($value, $validate)
	{
		// THIS ONE ONLY PERFORMED IN JAVASCRIPT
  		$result['result'] = true;
  		return $result;
	}

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

	function getDomainAttr($validate)
	{
		return "";
	}

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

	function getIPAttr($validate)
	{
		return "";
	}

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

	function getPasswordAttr($validate)
	{
		return "";
		//$validate['regExp'] = "/\\\\A(?=[-_a-zA-Z0-9]*?[A-Z])(?=[-_a-zA-Z0-9]*?[a-z])(?=[-_a-zA-Z0-9]*?[0-9])\\\\S{6,}\\\\z/";
		//return Validator::getRegExpAttr($validate);
	}

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

	function getSSNAttr($validate)
	{
		return "";
	}

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

	function validateRegExp($value, $validate)
	{
		if (preg_match($validate['regExp'], $value))
			return array('result' => true);
		else
			return array('result' => false);
	}

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
