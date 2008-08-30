<?
////////////////////////////////////////////////////
//  Define Template constants here				  //
////////////////////////////////////////////////////

	define("gui_base", APP_DIR . "/templates");		// root of templates directory.  defaults to templates/

	define("gui_look", "default");	// under that directory, this scheme is in gui_base (make it easy to change schemes

	define("strip_html", "1");	//security feature, if on means that all smarty tags of the format
						//{$text} will have all html stripped. { $text } will be left alone.
	define("gui_caching", 2); // 0: off     1: every file same lifetime    2: filebased lifetimes
 	define("gui_cache_lifetime", 3600);

?>