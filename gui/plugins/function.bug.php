 <?php
 
 /*
  * Smarty plugin
  * -------------------------------------------------------------
  * Type:     count
  * Name:     count
  * Purpose:  return the size of an array
  * -------------------------------------------------------------
  */
 function smarty_function_bug($params, &$smarty)
 {
	extract($params);
	
	if (empty($message))
	{
		$smarty->trigger_error("assign: missing 'message' parameter");
		return;
	}
	
	BUG($message);
 }
 
 /* vim: set expandtab: */
 
 ?>
