<?php
// include captcha class
require('php-captcha.inc.php');

// define fonts
$aFonts = array('VeraMoBd.ttf');

// create new image
$oPhpCaptcha = new PhpCaptcha($aFonts, 200, 60);
//$oPhpCaptcha->DisplayShadows(true);
$oPhpCaptcha->UseColour(true);
$oPhpCaptcha->Create();
?>