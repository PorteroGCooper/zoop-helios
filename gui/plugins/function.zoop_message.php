<?php
include_once(ZOOP_DIR . "/gui/plugins/function.zoop_image.php");

/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     zoop_image
 * Purpose:  write html to include a image from the zoop framework
 * -------------------------------------------------------------
 */
function smarty_function_zoop_message($params, &$smarty)
{
    	$id = "";
    	$class = "";
	$type = "warning"; // can also be path
	$size = "large"; // can also be small
	$text = "";

    	extract($params);

	switch ($type)
	{
		case "warning":
	  	case "important":
	    	case "error":
		case "information":
	  		if ($size == "large")
	  			$Itype = $type;
			else
				$Itype = "$type-s";
			break;
   		default:
			$Itype = "warning";
	}

	$img = smarty_function_zoop_image(array('image' => "$Itype.png", 'group' => "messages", 'type' => "path"), $smarty);

	!empty($id) ? $idstring = " id='$id'" : $idstring = "";
	!empty($class) ? $class = " $class" : $class = $class;

	$size == "large" ? $mclass = "message" : $mclass = "s-message";

	$opentag = "<div class=\"$mclass m-$type$class\" style=\"background-image: url($img);\"$idstring>";

	$closetag = "</div>";

	return $opentag . $text . $closetag;

}

/* vim: set expandtab: */

?>
