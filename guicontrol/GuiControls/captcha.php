<?php
/**
* @package gui
* @subpackage guicontrol
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

// include captcha class
require(ZOOP_DIR . "/guicontrol/GuiControls/libs/captcha/php-captcha.inc.php");

/**
 * captcha
 * Produces and validates a captcha image.
 *
 * @uses GuiControl
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class captcha extends GuiControl
{
	function validate()
	{
		$value = $this->getValue();

		$validate = PhpCaptcha::Validate($value);
		$errorState['text'] = "did not match the image.";
		$errorState['value'] = $this->getValue();
		$errorState['result'] = $validate;

		if ($validate)
			return true;
		else
			return $errorState;
	}
	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return void
	 */
	function getPersistentParams()
	{
		return array('validate');
	}

	/**
	 * render
	 *
	 * @access public
	 * @return void
	 */
	function render()
	{
		// define fonts
		$aFonts = array(dirname(__file__) . '/libs/captcha/VeraMoBd.ttf');

		$_SESSION['captchaTS'] = base64_encode(microtime());

		$filename = $_SESSION['captchaTS'] . ".jpg";
		$path = APP_DIR . '/tmp/captcha/';
		$file = $path . $filename;

		if (file_exists($path))
			CleanDirectory($path, 30);
		mkdirr($path);
		// create new image
		$oPhpCaptcha = new PhpCaptcha($aFonts, rand(150,250), rand(40,60));
// 		$oPhpCaptcha->DisplayShadow(true);
		$oPhpCaptcha->SetNumChars(rand(4,7));
		$oPhpCaptcha->SetMinFontSize(14);
		$oPhpCaptcha->SetMaxFontSize(20);
		$oPhpCaptcha->UseColour(rand(0,1));
		$oPhpCaptcha->Create($file);

		$ni = $this->getNameIdString();
		$v = $this->getValue();

		$html = "Please enter the letters you see in the image.<br>";
		$html .= "<img alt=\"security image\" src=\"". SCRIPT_REF ."/zoopfile/CaptchaImage/$filename\"><br>";
 		$html .= "<input $ni value=\"$v\">(case insensitive)";

		return $html;
	}
}


?>