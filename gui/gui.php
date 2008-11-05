<?php
// Copyright (c) 2008 Supernerd LLC and Contributors.
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
 * Gui is the Zoop framework View object.
 *
 * Gui wraps extends a Smarty object with Zoop Framework specific functionality and convenience
 * methods.
 *
 * @uses Smarty
 * @ingroup gui
 * @ingroup view
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @author Rick Gigger
 * @author John Lesueur
 * @author Richard Bateman
 * @author Justin Hileman {@link http://justinhileman.com}
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class gui extends Smarty {

	/**
	 * Framework-level css includes
	 *
	 * @var array
	 * @access private
	 * @see gui::add_css
	 */
	var $_zoopCss = array();
	
	/**
	 * Framework-level JavaScript includes
	 *
	 * @var array
	 * @access private
	 * @see gui::add_js
	 */
	var $_zoopJs = array();
	
	/**
	 * Application-level css includes
	 *
	 * @var array
	 * @access private
	 * @see gui::add_css
	 */
	var $_appCss = array();
	
	/**
	 * Application-level JavaScript includes
	 *
	 * @var array
	 * @access private
	 * @see gui::add_js
	 */
	var $_appJs = array();
	
	/**
	 * Gui regions definitions.
	 *
	 * @var array
	 * @access private
	 * @see gui::addRegion
	 * @see gui::removeRegion
	 * @see gui::sortRegions
	 */
	var $_regions = array();

	/**
	 * gui constructor
	 *
	 * @access public
	 * @return void
	 */
	function gui() {
		global $sGlobals;

		$this->Smarty();

		$guiConfig = Config::get('zoop.gui');
		$dirs = $guiConfig['directories'];
		//	set the default for the base template dir
		if(!defined("gui_base") )
			define("gui_base", $dirs['base']);

		$this->template_dir = array($dirs['template'], $dirs['base_template']);
		$this->setCompileDir($dirs['compile']);
		$this->setCacheDir($dirs['cache']);
		$this->caching = $guiConfig['caching'];

		if ($guiConfig['strip_html']) {
			$this->autoload_filters = array('pre' => array("strip_html"));
		} else {
			$this->autoload_filters = array();
		}

		//$this->plugins_dir = array(APP_DIR . "/guiplugins", dirname(__file__) . "/plugins");
		$this->plugins_dir = $dirs['plugins'];

		if($look = Config::get('app.gui.look')) {
			$this->config_dir = $this->template_dir . "/" . $look . "/configs";
			$this->debug_tpl = "file:" . $look . "/debug.tpl";
			$this->assign("template_root", $look);
			$this->assign("RES_ROOT", "public/resources/");
		} else {
			$this->config_dir = $this->template_dir . "/configs";
			$this->assign("template_root", $dirs['base']);
		}

		//	it should probably only do this if they are defined so you can use it
		//	without using the zone stuff
		if(defined("SCRIPT_URL") || defined("SCRIPT_REF") || defined("ORIG_PATH")) {
			$this->assign("VIRTUAL_URL", SCRIPT_URL . ORIG_PATH);
			$this->assign("VIRTUAL_PATH", ORIG_PATH);
			$this->assign("REQUESTED_URL", REQUESTED_URL);
			$this->assign("REQUESTED_PATH", REQUESTED_PATH);
			$this->assign("BASE_HREF", SCRIPT_REF);
			$this->assign("SCRIPT_URL", SCRIPT_URL);
			$this->assign("SCRIPT_BASE", SCRIPT_BASE);
		}

		$this->assign("APP_DIR", APP_DIR);
		$this->assign("app_dir", APP_DIR);
		
		// Initialize the gui object's default regions and template files.
		$this->init_regions();

		if ($title = Config::get('app.title')) {
			$this->assign("title", $title);
		}

		if ($public_web_path = Config::get('app.public_web_path')) {
			$this->assign("public_web_path", $public_web_path);
		}

		// Add YUI reset and base styles
		if (Config::get('zoop.gui.use_css_reset', false)) {
			$this->add_css('zoopfile/gui/css/yui-reset-min.css', 'zoop');
		}
		if (Config::get('zoop.gui.use_css_base', false)) {
			$this->add_css('zoopfile/gui/css/yui-base-min.css', 'zoop');
		}

		// Add some default (simple) stylesheet rules.
		$this->add_css('zoopfile/gui/css/defaults.css', 'zoop');

		$this->register_zcache();
		$this->init_registrations();
	}

    function register_zcache()
    {
		$this->load_filter( 'pre', 'zcache' );
		require_once $this->_get_plugin_filepath( 'function', 'include_zcache' );
		$this->register_function( 'include_zcache', 'smarty_function_include_zcache', false );
		$write_path = rtrim( $this->compile_dir, "/\\" ) . DIRECTORY_SEPARATOR . 'zcache' . DIRECTORY_SEPARATOR;
		$this->template_dir = (array)$this->template_dir;

		if ( !in_array( $write_path, $this->template_dir ) ) {
			$this->template_dir[] = $write_path;
		}
    }

    function unregister_zcache()
    {
		$this->template_dir = $this->template_dir[0];
		$this->unregister_prefilter( 'zcache' );
		$this->unregister_function( 'include_zcache' );
    }

    function absolute2relative( $path )
    {
		$template_dir = ( is_array( $this->template_dir ) )
			? $this->template_dir[0]
			: $this->template_dir;

		if ( !in_array( substr( $template_dir, 0, 1 ), array( '/', '\\' ) )) {
			// if template_dir path is already relative then we throw a fatal error
			// since we can not discover the base path with certainty
			$this->trigger_error( "The template_dir path '{$template_dir}' must be an absolute path. ", E_USER_ERROR );
		}

		if ( in_array( substr( $path, 0, 1 ), array( '/', '\\' ) )) {
			if ( substr( $path, 0, strlen( $template_dir )) == $template_dir ) {
				$result = substr( $path, strlen( $template_dir ) );
			} else {
				// we don't know this template path so we throw a fatal error
				$this->trigger_error( "The path for '{$path}' is not in the template path ('{$template_dir}'). ", E_USER_ERROR );
			}
		} else {
			// if recieved path is already relative, do nothing
			$result = $path;
		}

		return $result;
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

	function setCacheDir($inDir)
	{
		$this->cache_dir = $inDir;
	}

	/**
	 * fetch
	 *
	 * @param mixed $tpl_file
	 * @param mixed $cache_id
	 * @param mixed $compile_id
	 * @access public
	 * @return string
	 */
	function fetch($tpl_file, $cache_id = null, $compile_id = null) {

		if($look = Config::get('app.gui.look')) {
			$tpl_file = $look . "/" . $tpl_file;
		}
		
		return Smarty::fetch($tpl_file, $cache_id, $compile_id);
	}

	/**
	 * fetch ignoring guilook (works like smarty stock)
	 *
	 * @param mixed $tpl_file
	 * @param mixed $cache_id
	 * @param mixed $compile_id
	 * @access public
	 * @return string
	 */
	function rootFetch($tpl_file, $cache_id = null, $compile_id = null)
	{
		return Smarty::fetch($tpl_file, $cache_id, $compile_id);
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
 	function display($tpl_file, $cache_id = null, $compile_id = null)
 	{
 		echo $this->fetch($tpl_file, $cache_id, $compile_id);
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
		if($look = Config::get('app.gui.look')) {
			$params['smarty_include_tpl_file'] = $look . "/" . $params['smarty_include_tpl_file'];
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
		if ($tpl_var != '') {
			$this->_tpl_vars[$tpl_var] = $value;
		}
	}

	/**
	 * assign_array
	 * assigns an array of values to template variables
	 *
	 * @param mixed $tpl_var the template variable name(s)
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
 
	/**
	 * Render a page based on default or preconfigured templates.
	 *
	 * If a template file is passed, assign that template file to the primary region of the page
	 * before rendering the page.
	 *
	 * This file should be preferred over gui::display(), as this function displays the page content template
	 * in context (all other regions are rendered as well). The following two snippets will display the same
	 * message template, but the gui::generate() call will render any site specified header, sidebar, footer,
	 * css and JavaScript files, etc.
	 *
	 * @code
	 *   $gui->assign('message', 'Hello World.');
	 *   $gui->generate('message.tpl');
	 * @endcode
	 *
	 * @code
	 *   $gui->assign('message', 'Hello World.');
	 *   $gui->display('message.tpl');
	 * @endcode
	 * 
	 *
	 * @param string $primary_template (optional)
	 *   If no template file is specified, will default to default file for default region.
	 * @param array $params (optional) (does nothing for now.
	 * @access public
	 * @return void
	 */
	function generate($template_file = null, $params = array()) {
		if ($template_file != null) {
			$this->assignRegion(Config::get('zoop.gui.primary_region'), $template_file);
		}
		$this->display(Config::get('zoop.gui.templates.html'));
	}

	/**
	 * assignbrowser
	 *
	 * @access public
	 * @return void
	 * @deprecated 2.0
	 */
	function assignbrowser() {
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

	function is_cached($inTpl, $cache_id = null,  $compile_id = null)
	{

		if($look = Config::get('app.gui.look')) {
			$inTpl = $look . "/" . $inTpl;
		}
		return  parent::is_cached($inTpl, $cache_id, $compile_id);
	}

	function init_registrations()
	{
		$this->register_block('dynamic', array('gui', 'smarty_block_dynamic'), false);
	}

	function smarty_block_dynamic($param, $content, &$smarty) 
	{
		return $content;
	}
	
	/**
	 * Initialize regions. Called when the gui object is first created, sets up zoop or app
	 * Defaults and template files.
	 *
	 * @access public-ish
	 * @return void
	 */
	function init_regions() {
		$sort = Config::get('zoop.gui.regions');
		$templates = Config::get('zoop.gui.templates');
		foreach ($sort as $name) {
			if (isset($templates[$name])) {
				$this->addRegion($name, $templates[$name]);
			} else {
				trigger_error("No template file specified for region $name");
			}
		}
	}
	
	/**
	 * Add a new region.
	 *
	 * Will use a default template file for the region if no template is speficied.
	 *
	 * Note: This new region will be appended to the list of regions, so it will display
	 * below the footer if you don't do something about it. {@see gui::sortRegions}
	 *
	 * @param string $name Name of this region.
	 * @param string $template_var for this region. (optional)
	 * @return void
	 * @todo Verify that templates actually exist instead of blindly accepting the names?
	 */
	function addRegion($name, $template_var = null) {
		if (isset($this->_regions[$name])) {
			trigger_error("Region already defined: $name");
			return;
		}
		if ($template_var == null) {
			if (!$template_var = Config::get('zoop.gui.templates.' . $name)) {
				trigger_error("No template file defined for $name region");
				return;
			}
		}
		
		$this->_regions[$name] = $template_var;
	}
	
	/**
	 * Remove a region (don't display on this page/zone/etc).
	 *
	 * @param mixed $name Region name or array of names.
	 * @access public
	 */
	function removeRegion($name) {
		foreach ((array)$name as $region) {
			if (isset($this->_regions[$region])) {
				unset($this->_regions[$region]);
			} else {
				trigger_error("Unable to remove region, region $name not defined.");
			}
		}
	}
	
	/**
	 * Set region order.
	 *
	 * @param mixed $sort A string (comma separated) or array to reorder regions.
	 */
	function sortRegions($sort) {
		if (!is_array($sort)) {
			$sort = explode(',', $sort);
		}
		
		$old_regions = $this->_regions;
		$this->_regions = array();
		
		foreach ($sort as $name) {
			if (isset($old_regions[$name])) {
				$this->_regions[$name] = $old_regions[$name];
				unset($old_regions[$name]);
			}
			
			// trigger error? they passed a name that doesn't exist.
		}
		
		if (count($old_regions)) {
			trigger_error('Some regions unsorted (too few region names passed).');
		}
	}
	
	/**
	 * Assign a template file to a region.
	 *
	 * @param string $name Region name.
	 * @param string $template_file
	 * @access public
	 * @return void
	 */
	function assignRegion($name, $template_file) {
		if (!isset($this->_regions[$name])) {
			trigger_error("Unknown region: $name");
			return;
		}
		
		$this->_regions[$name] = $template_file;
	}
	
	/**
	 * Add (require) a CSS file to be linked by the gui object.
	 *
	 * @param string $path Path to CSS file
	 * @param string $scope Scope of CSS file include.
	 *   Determines include priority of this file (all zoop scope files will be included before app)
	 * @access public
	 * @return void
	 */
	function add_css($path, $scope = 'app') {
		// for backwards compatability... add the public dir if it's just a file name.
		if (strpos($path, '/') === false) {
			$path = Config::get('zoop.gui.directories.public') . '/' . $path;
		}
		
		switch ($scope) {
			case 'zoop':
				$this->_zoopCss[$path] = $path;
				break;
			case 'app':
				$this->_appCss[$path] = $path;
				break;
			}
	}
	
	/**
	 * Add (require) a JS file to be linked by the gui object.
	 *
	 * @param string $path Path to JS file
	 * @param string $scope Scope of JS file include.
	 *   Determines include priority of this file (all zoop scope files will be included before app)
	 * @access public
	 * @return void
	 */
	function add_js($path, $scope = 'app') {
		switch ($scope) {
			case 'zoop':
				$this->_zoopJs[$path] = $path;
				break;
			case 'app':
				$this->_appJs[$path] = $path;
				break;
		}
	}
	
	/**
	 * __call magic method.
	 *
	 * This method assigns template files and content to regions.
	 *
	 * @access private
	 */
	function __call($method, $args) {
		if (substr($method, 0, 6) == 'assign') {
			$param_name = substr($method, 6);

			//lowercasify the first letter...
			$param_name[0] = strtolower($param_name[0]);
			
			if (substr($param_name, -8) == 'Template') {
				$param_name = substr($param_name, 0, -8);
				$call_function = 'assignRegion';
			} else {
				$call_function = 'assign';
			}
			
			array_unshift($args, $param_name);
			return call_user_func_array(array($this, $call_function), $args);
		}
		else {
			trigger_error($method . " method undefined on Gui object.");
		}
	}


}