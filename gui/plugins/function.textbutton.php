<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     textbutton
 * Purpose:  create an href with text, setting statusbar and onclick javascript
 * -------------------------------------------------------------
 */
function smarty_function_textbutton($params, &$this)
{
	$options = array();
	if (isset($params["tag"]))
	{
		$options[] = "onmouseover=\"window.status='" . addslashes($params["tag"]) . "'; return true;\" onmouseout=\"window.status = ''; return true;\"";
		$text = $params["tag"];
		unset($params["tag"]);
	}
	
	if (isset($params["action"]))
	{
		$options[] = "onclick=\"" . $params["action"] . " return false;\"";
		unset($params["action"]);
	}
	elseif (isset($params["localref"]))
	{
		$href = SCRIPT_URL . $params["localref"];
		unset($params["localref"]);
	}
	elseif (isset($params["href"]))
	{
		$href = $params["href"];
		unset($params["href"]);
	}
	else
	{
		$href="#";
	}
	
	foreach($params as $key => $value)
	{
		$options[] = "$key=\"$value\"";
	}
	
	$opts = implode(" ", $options);
	echo "<a href=\"$href\" $opts>$text</a>";
}

/* vim: set expandtab: */

?>
