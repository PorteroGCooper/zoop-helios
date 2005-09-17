 <?php

 /*
  * Smarty plugin
  * -------------------------------------------------------------
  * Type:     date
  * Name:     date
  * Purpose:  return the date
  * -------------------------------------------------------------
  */
 function smarty_function_date($params, &$smarty)
 {
 	$format = "d-m-Y";
	extract($params);

	if (empty($date))
		return date($format);
	else
		return date($format, $date);
 }

 /* vim: set expandtab: */

 ?>
