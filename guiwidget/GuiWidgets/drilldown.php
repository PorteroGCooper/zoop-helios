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

/**
 * Drilldown Menu guiWidget
 *
 * An iPod style drilldown menu based heavily upon
 * {@link http://www.filamentgroup.com/lab/jquery_ipod_style_drilldown_menu this post}
 *
 * It doesn't actually work yet. Don't use it.
 *
 * @ingroup gui
 * @ingroup guiwidget
 * @ingroup jquery
 * @author Justin Hileman {@link http://justinhileman.com}
 */
class guiwidget_drilldown extends GuiWidget {
	function initWidget() {
		global $gui;
		
		$gui->add_js('/zoopfile/gui/js/jquery.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/jquery.drilldown.utilities.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/jquery.position.js', 'zoop');
		$gui->add_js('/zoopfile/gui/js/jquery.drilldown.js', 'zoop');
		
		$gui->add_css('/zoopfile/gui/css/drilldown.css', 'zoop');
	}

	/**
	 * Render a drilldown guiWidget.
	 *
	 * @access public
	 * @return string Rendered HTML
	 */
	function render() {
		global $gui;
		
		$attrs = array();
		$Sattrs = array();

		$html = "";
		
		if (!isset($this->params['url'])) {
			trigger_error('Specify a "url" parameter for Drilldown guiWidget ' . $this->name);
			return;
		}
		
		$link_text = format_label($this->name);
		if (isset($this->params['label'])) $link_text = $this->params['label'];
		
		$link_id = $this->getName();
		
		$html = '<a class="menuBtn menuAction menuNavigator" id="'. $link_id .'" href="'. url($this->params['url']) .'">'. $link_text .'</a>';
		
		$gui->add_js('jQuery(function($){
			var menuContent_' . $link_id . ' = $.get($("#' . $link_id . '").attr("href"), function(data){menuContent_' . $link_id . ' = data;});
			$("#' . $link_id . '").click(function(){			
				var menu'. $link_id .' = new Menu(this, {
					content: menuContent_' . $link_id . ',
					width: 216,
					maxHeight: 300,
					positionOpts: {offsetY: -1},
					callerOnState: "foo", 
					itemHover: "hover", 
					selectCategories: false,
					topLinkText: "All Categories",
					altClasses: "drilldown"
				}).create();
				return false;
			});			
		});
		
		function foo() {
			alert("this");
		}
		', 'inline');
		
		return $html;
	}
}