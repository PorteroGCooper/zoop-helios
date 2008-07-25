<?php
/**
* @package gui
* @subpackage guicontrol
*/
// Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

include_once(zoop_dir . "/gui/plugins/function.html_options.php");

/**
 * select
 *
 * @uses GuiControl
 * @package
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <steve.francia+zoop@gmail.com>
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class richselect extends guicontrol
{

	function view()
	{
		$value = $this->getValue();
		$width = "150";
		if (isset($this->params['width']))
			$width = $this->params['width'];

		if (isset($this->params['index'][$value]))
		{
			$html = $this->params['index'][$value];
			$html = "<div style=\"position:relative; width:{$width}px;\"> $html </div>";
			return $html;
		}
		else
			return "";
	}

	/**
	 * getPersistentParams
	 *
	 * @access public
	 * @return void
	 */
	function getPersistentParams()
	{
		return array('validate');
	}

	/**
	 * render
	 *
	 * @access public
	 * @return void
	 */
	function render()
	{
		global $gui;
		if (!isset($this->params['index']))
			return 'you need to specify an index for this guiControl';

		$width = "180";
		$height = 22;
 		$onclick = "";

		$value = $this->getValue();
		$label = $this->getLabelName();

		if (isset($this->params['width']))
			$width = $this->params['width'];
		$newwidth = $width + 25;

		if (isset($this->params['height']))
			$height = $this->params['height'];

 		if (isset($this->params['onclick']))
 			$onclick = $this->params['onclick'];

		$html = "
			<div style=\"position:relative; width: {$width}px;\" class=\"b\">
				<div  onclick=\"new Effect.toggle('" .$label. "_dd','BLIND', {duration: 0.20}); return false;\" style=\"background : white; border : #D0D0D0 inset 2px; width : $newwidth"."px; height: {$height}px; padding:2px; cursor:default;\" class=\"c\">
					<div id=\"$label"."_holder\" style=\"float: left;\" class=\"d\">";
					isset($this->params['index'][$value]) ? $html .= $this->params['index'][$value] : $html.= "<br>";
					$html .= "
					</div>
					<div class=\"e\" style=\"border: 1px solid #868686;float:right;background: #C0C0C0; color: #5B5B5B; padding: 0px 3px 0px 3px;font: 10pt arial black;\">v</div>
				</div>

			</div>";
			$html .= "<INPUT type=\"hidden\" value=\"$value\" id=\"$label\" name=\"$label\">";
			$html .= "<div id=\"$label"."_dd\" class=\"dd\" style=\"background : white; border : #D0D0D0 inset 2px; padding:2px; position:absolute; height:230px; display:none; z-index:1000; \">
				<div style=\"position:relative; overflow:auto; height:230px;\">";

				foreach ($this->params['index'] as $optval => $optlabel)
				{
					$html .= "<div onclick=\"$('$label').value = '$optval'; $('$label"."_holder').innerHTML = this.innerHTML; new Effect.toggle('$label"."_dd','BLIND', {duration: 0.20}); ".$onclick." return false;\" style=\"cursor:pointer\" class=\"ddi\">$optlabel</div>";
				}
				$html .= "
				</div>
			</div>
		";

		return $html;
	}
}
?>