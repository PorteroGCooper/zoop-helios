<?php
/**
* @package gui
*/
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

/**
 * gui
 *
 * @uses Smarty
 * @package
 * @version $id$
 * @copyright 1997-2006 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */
class gui extends Smarty
{
	/**
	 * gui
	 *
	 * @access public
	 * @return void
	 */
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

		$this->plugins_dir = array(app_dir . "/guiplugins", dirname(__file__) . "/plugins");

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

		$this->assign("app_dir", app_dir);

		if (defined("app_default_title")) 	$this->assign("title", app_default_title);
		if (defined("public_web_path"))  $this->assign("public_web_path", public_web_path);

// 		$this->assignbrowser();

		//	what does this do
		$this->debugging = 1;
	}

	/**
	 * setCompileDir
	 *
	 * @param mixed $inDir
	 * @access public
	 * @return void
	 */
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

	/**
	 * fetch
	 *
	 * @param mixed $tpl_file
	 * @param mixed $cache_id
	 * @param mixed $compile_id
	 * @param mixed $display
	 * @access public
	 * @return string
	 */
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
	// 	Answer:
	// 	This function echos, instead of displaying.. It doesn't work properly without this
	/**
	 * display
	 *
	 * @param mixed $tpl_file
	 * @access public
	 * @return void
	 */
 	function display($tpl_file)
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

	/**
	 * _smarty_include
	 *
	 * @param mixed $params
	 * @access protected
	 * @return void
	 */
	function _smarty_include($params)
	{
		if (defined("gui_look") )
		{
			$params['smarty_include_tpl_file'] = gui_look . "/" . $params['smarty_include_tpl_file'];
		}

		Smarty::_smarty_include($params);
	}

	/**
	* assigns values to template variables
	*
	* @param string $tpl_var the template variable name(s)
	* @param mixed $value the value to assign
	*/
	function assign($tpl_var, $value = null)
	{
			if ($tpl_var != '')
				$this->_tpl_vars[$tpl_var] = $value;
	}

	/**
	* assigns an array of values to template variables
	*
	* @param array $tpl_var the template variable name(s)
	*
	*/

	/**
	 * assign_array
	 *
	 * @param mixed $tpl_var
	 * @access public
	 * @return void
	 */
	function assign_array($tpl_var)
	{
		foreach ($tpl_var as $key => $val)
		{
			if ($key != '') {
				$this->_tpl_vars[$key] = $val;
			}
		}
	}

	// A WRAPPER TO MAKE USING THIS STYLE OF TEMPLATES SIMPLIER
	/**
	 * generate
	 *
	 * @param mixed $inBodytpl
	 * @param mixed $inSidebartpl
	 * @param mixed $inMenutpl
	 * @param mixed $title
	 * @param string $inCss
	 * @access public
	 * @return void
	 */
	function generate($inBodytpl, $inSidebartpl, $inMenutpl, $title = app_default_title, $inCss = "styles.css")
	{

		$this->assign("title", $title);
		$this->assign("bodytpl", $inBodytpl);
		$this->assign("sidetpl", $inSidebartpl);
		$this->assign("menutpl", $inMenutpl);
		$this->assign("css", $inCss);

		$this->display("main.tpl");
	}

	/**
	 * assignbrowser
	 *
	 * @access public
	 * @return void
	 */
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
