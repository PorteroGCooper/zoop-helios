<?php
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

require_once(dirname(__file__) . "/Smarty.class.php");

class gui extends Smarty
{
	function gui()
	{
		global $sGlobals;

		$this->Smarty();

   		//	set the default for the base template dir
   		if(!defined("gui_base") )
   			define("gui_base", app_dir . "/templates");

   		$this->template_dir = gui_base;
		$this->setCompileDir(app_temp_dir . "/gui");

		if (defined("strip_html") && strip_html)
		{
			$this->autoload_filters = array('pre' => array("strip_html"));
		}
		else
		{
			$this->autoload_filters = array();
		}

		$this->plugins_dir = array(dirname(__file__) . "/plugins", app_dir . "/guiplugins");

		if(defined("gui_look") )
		{
			//	what exactly does the config directory do???
			$this->config_dir = $this->template_dir . "/" . gui_look . "/configs";
			$this->debug_tpl = "file:" . gui_look . "/debug.tpl";
			$this->assign("template_root", gui_look);
			$this->assign("RES_ROOT", "public/resources/");
		}
		else
		{
			//	what exactly does the config directory do???
			$this->config_dir = $this->template_dir . "/configs";
			$this->assign("template_root", gui_base);
		}

		//	it should probably only do this if they are defined so you can use it
		//	without using the zone stuff
		if(defined("SCRIPT_URL") || defined("SCRIPT_REF") || defined("ORIG_PATH"))
		{
			$this->assign("VIRTUAL_URL", SCRIPT_URL . ORIG_PATH);
			$this->assign("BASE_HREF", SCRIPT_REF);
			$this->assign("SCRIPT_URL", SCRIPT_URL);
			$this->assign("SCRIPT_BASE", SCRIPT_BASE);
		}

		if (defined("app_default_title")) 	$this->assign("title", app_default_title);
		if (defined("public_web_path"))  $this->assign("public_web_path", public_web_path);

// 		$this->assignbrowser();

		//	what does this do
		$this->debugging = 1;
	}

	function setCompileDir($inDir)
	{
		$this->compile_dir = $inDir;
	}


	/*======================================================================*\
		Function:   fetch()
		Purpose:    executes & returns or displays the template results
		Notes:		Do NOT call this funciton using fw_gui_look or a /
					before the template file!

					This function is used by display.
	\*======================================================================*/
	function fetch($tpl_file, $cache_id = null, $compile_id = null, $display = false)
	{
		if (defined("gui_look") )
		{
			$tpl_file = gui_look . "/" . $tpl_file;
		}

		return Smarty::fetch($tpl_file, $cache_id, $compile_id, $display);
	}

	//	what is the point of this function. It isn't adding anything to the base class function
	//	and the second paramater isn't even being used.
	function display($tpl_file, $base_template = 'template.tpl')
	{
		echo $this->fetch($tpl_file);
	}

    /*======================================================================*\
        Function:   _smarty_include()
        Purpose:    called for included templates
		Notes:		This method is here because of the fw_gui_look constant.
					If that constant exists then included files need to be
					looked for in the fw_gui_look sub dir.
    \*======================================================================*/
    function _smarty_include($params)
	{
		if (defined("gui_look") )
		{
			$params['smarty_include_tpl_file'] = gui_look . "/" . $params['smarty_include_tpl_file'];
		}

		Smarty::_smarty_include($params);
	}

	// A WRAPPER TO MAKE USING THIS STYLE OF TEMPLATES SIMPLIER
	function generate($inBodytpl, $inSidebartpl, $inMenutpl, $title = app_default_title, $inCss = "styles.css")
	{

		$this->assign("title", $title);
		$this->assign("bodytpl", $inBodytpl);
		$this->assign("sidetpl", $inSidebartpl);
		$this->assign("menutpl", $inMenutpl);
		$this->assign("css", $inCss);

		$this->display("main.tpl");
	}

	function assignbrowser()
    	{
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$ie6 = 'MSIE 6.0';
		$ie55 = 'MSIE 5.5';
		$win = 'Window';

		$pos1 = strpos($browser, $win);

		if ($pos1 == true)
			{
				$pos2 = strpos($browser, $ie6);
				$pos3 = strpos($browser, $ie55);
				if ($pos2 == true)
					$this->assign("browser", "ie 6");
				else if ($pos3 == true)
					$this->assign("browser", "ie 5.5");
				else
					$this->assign("browser", "other");
			}
		else
			$this->assign("browser", "other");
	}

}
?>