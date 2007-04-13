<?php
/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     date_format
 * Purpose:  format datestamps via strftime
 * -------------------------------------------------------------
 */
require_once SMARTY_DIR . 'plugins/shared.make_better_timestamp.php';
require_once 'Date.php';
function smarty_modifier_better_date_format($string, $format="%b %e, %Y", $nullfiller="", $timezone = "EST")
{
    if($string == NULL)
		return $nullfiller;
	else
	{
		//echo($string);
		///*
		$answer = formatPostgresDate($string, $format, $timezone);
		return $answer;
		$parts = explode("-", $string);
		$date = &new Date();
		$date->setYear($parts[0]);
		$date->setMonth($parts[1]);
		$date->setDay($parts[2]);
		return $date->format($format);
		//*/
		return strftime($format, smarty_make_better_timestamp($string));
	}
}

/* vim: set expandtab: */

?>
