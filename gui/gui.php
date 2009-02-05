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
	 * Inline JavaScript includes
	 *
	 * @var array
	 * @access private
	 * @see gui::add_js
	 */
	var $_inlineJs = array();

	/**
	 * Inline css includes
	 *
	 * @var array
	 * @access private
	 * @see gui::add_css
	 */
	var $_inlineCss = array();

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
	var $_renderedRegions = array();
	
	var $header_written = false;

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

		if (in_array('db', (array)Config::get('zoop.gui.template_resources.drivers'))) {
			include_once($this->plugins_dir[1] . '/resource.db.php');
			$this->register_resource('db', array(
				'smarty_resource_db_get_template',
				'smarty_resource_db_get_timestamp',
				'smarty_resource_db_get_secure',
				'smarty_resource_db_get_trusted')
			);
		}
		if (in_array('doctrine', (array)Config::get('zoop.gui.template_resources.drivers'))) {
			include_once($this->plugins_dir[1] . '/resource.doctrine.php');
			$this->register_resource('doctrine', array(
				'smarty_resource_doctrine_get_template',
				'smarty_resource_doctrine_get_timestamp',
				'smarty_resource_doctrine_get_secure',
				'smarty_resource_doctrine_get_trusted')
			);
		}

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
		$this->initRegions();

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

	/**
	 * Render and echo a given template. Wraps a call to gui::fetch() and echos the result.
	 *
	 * @param string $tpl_file
	 * @param string $cache_id (default:null)
	 * @param string $compile_id (default:null)
	 * @access public
	 * @return void
	 */
 	function display($tpl_file = null, $cache_id = null, $compile_id = null) {
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
	function _smarty_include($params) {
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
		if (Config::get('zoop.gui.prerender_regions', true)) $this->renderRegions();
		$this->display(Config::get('zoop.gui.templates.html'));
	}
	
	function is_cached($inTpl, $cache_id = null,  $compile_id = null) {
		if($look = Config::get('app.gui.look')) {
			$inTpl = $look . "/" . $inTpl;
		}
		return  parent::is_cached($inTpl, $cache_id, $compile_id);
	}

	function init_registrations() {
		$this->register_block('dynamic', array('gui', 'smarty_block_dynamic'), false);
	}

	function smarty_block_dynamic($param, $content, &$smarty) {
		return $content;
	}
	
	/**
	 * Initialize regions. Called when the gui object is first created, sets up zoop or app
	 * defaults and template files.
	 *
	 * @access public-ish
	 * @return void
	 */
	function initRegions() {
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
	 * below the footer if you don't do something about it. Either add an $after param
	 * or {@see gui::sortRegions}
	 *
	 * @param string $name Name of this region.
	 * @param string $template_var for this region. (optional)
	 * @param string $after Insert the region after given region name. (optional)
	 * @return void
	 * @todo Verify that templates actually exist instead of blindly accepting the names?
	 */
	function addRegion($name, $template_var = null, $after = null) {
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
		
		if ($after !== null && array_key_exists($after, $this->_regions)) {
			$new = array();
			foreach($this->_regions as $_key => $_val) {
				$new[$_key] = $_val;
				if ($_key == $after) $new[$name] = $template_var;
			}
			$this->_regions = $new;
		} else {
			$this->_regions[$name] = $template_var;
		}
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
		}
		$this->_regions = $this->_regions + $old_regions;
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
			if ($name == 'content') {
				$name = Config::get('zoop.gui.primary_region');
			} else {
				trigger_error("Unknown region: $name");
				return;
			}
		}
		
		$this->_regions[$name] = $template_file;
	}
	
	/**
	 * Pre-Render regions to display in the main site template.
	 * 
	 * Pre-rendering regions allows Gui to render the <head> tag after all of the body. This way
	 * javascript and css requirements can be declared lazily (at any point while rendering the body)
	 * but will all still make it in the <head>. This is vital for compression, aggregation and caching
	 * of all site resources.
	 *
	 * If this is causing problems, disable it via the config option 'zoop.gui.prerender_regions'.
	 *
	 * Note: In the event that anything is rendered *after* the <head>, Gui is still smart enough to 
	 * take care of the resources. If they show up too late to be in <head>, they will be included inline.
	 * 
	 * @see Gui::generate()
	 * @access protected
	 * @return void
	 */
	protected function renderRegions() {
		foreach ($this->_regions as $name => $region) {
			$this->_renderedRegions[$name] = $this->fetch($region);
		}
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
		// handle inline stuff separately.
		if ($scope == 'inline') {
				$md5 = hash('md5', $path);
				
				// if the header's already been written, spit this out and hope for the best.
				if ($this->header_written && !isset($this->_inlineCss[$md5])) {
					echo '<style type="text/css">';
					echo "\n";
					echo $path;
					echo "\n</style>\n";
				}
				$this->_inlineCss[$md5] = $path;
				return;
		}
		
		// for backwards compatability... add the public dir if it's just a file name.
		if (strpos($path, '/') === false) {
			$path = Config::get('zoop.gui.directories.public') . '/' . $path;
		}
		
		// if this doesn't start with a slash, it should.
		if ($path[0] !== '/') {
			$path = '/' . $path;
		}
		
		// if we've already written the header, let's just dump this now and hope for the best.
		if ($this->header_written && !isset($this->_zoopCss[$path]) && !isset($this->_appCss[$path])) {
			echo '<link rel="stylesheet" type="text/css" href="' . url($path) . "\" />\n";
		}
		
		// now add the css file to the zoop and app resources.
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
	 * Inline JavaScript can be included by passing 'inline' as the scope argument. Inline js is put in
	 * the <head>, just after the rest of the JS includes. Inline js will be checked for uniqueness just like
	 * includes.
	 *
	 * @param string $path Path to JS file
	 * @param string $scope Scope of JS file include.
	 *   Determines include priority of this file (all zoop scope files will be included before app)
	 * @access public
	 * @return void
	 */
	function add_js($path, $scope = 'app') {
	
		// handle inline stuff separately.
		if ($scope == 'inline') {
				$md5 = hash('md5', $path);
				
				// if the header's already been written, spit this out and hope for the best.
				if ($this->header_written && !isset($this->_inlineJs[$md5])) {
					echo '<script type="text/javascript">';
					echo "\n//<![CDATA[\n\t";
					echo $path;
					echo "\n//]]>\n</script>\n";
				}
				$this->_inlineJs[$md5] = $path;
				return;
		}
		
		// if this doesn't start with a slash, it should.
		if ($path[0] !== '/') {
			$path = '/' . $path;
		}
		
		// if we've already written the header, let's just dump this now and hope for the best.
		if ($this->header_written && !isset($this->_zoopJs[$path]) && !isset($this->_appJs[$path])) {
			echo '<script type="text/javascript" src="' . url($path) . "\"></script>\n";
		}
		
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
	 * Add jQuery inline javascript. Hotter than gui::add_js().
	 * 
	 * @access public
	 * @return void
	 */
	function add_jquery($js = null) {
		if (empty($js)) {
			$this->add_js('/zoopfile/gui/js/jquery.js', 'zoop');
		} else {
			$js = 'jQuery(function($){' . $js . '});';
			$this->add_js($js, 'inline');
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
			
			// check if this is really a region.
			$regions = Config::get('zoop.gui.regions');
			if (!in_array($param_name, $regions)) {
				// if there's no region called 'content', rename this to the primary region.
				if ($param_name == 'content') {
					$param_name = Config::get('zoop.gui.primary_region');
				} else {
					trigger_error($method . " method undefined on Gui object.");
				}
			}
			
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
			trigger_error($method . " method undefined on Gui object.", E_USER_ERROR);
		}
	}

}