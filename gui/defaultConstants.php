<?php
define_once('app_temp_dir', app_dir . '/tmp');
define_once('gui_base', app_dir . '/templates');
define_once("gui_look", "");	// under that directory, this scheme is in gui_base (make it easy to change schemes
define_once("strip_html", "0");	//security feature, if on means that all smarty tags of the format
				//{$text} will have all html stripped. { $text } will be left alone.
?>
