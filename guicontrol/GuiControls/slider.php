<?php
/**
* @package gui
* @subpackage guicontrol
*/
// Copyright (c) 2006 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

class slider extends GuiControl
{
	function getPersistentParams()
	{
		return array('validate');
	}

	function view()
	{
		$value = $this->getValue();
		return $this->params['index'][$value];
	}

	function render()
	{
		global $gui;
/*
		if (!isset($this->params['index']))
			return 'you need to specify an index for this guiControl';*/

		$size = 150;
		if (!isset($this->params['axis']))
			$this->params['axis'] = "horizontal";
		$attrs = array();

		$this->params['sliderValue'] = $this->getValue();

		extract($this->params);

		foreach ($this->params as $parameter => $value)
		{
			switch ($parameter) {   // Here we setup specific parameters that will go into the js

				case 'increment':
				case 'step':
				case 'alignX':
				case 'alignY':
				case 'disabled':
				case 'maximum':
				case 'minimum':
				case 'sliderValue':
					if ($value != '')
						$attrs[] = "$parameter:$value";
					break;

				case 'axis':
				case 'handleImage':
					if ($value != '')
						$attrs[] = "$parameter:'$value'";
					break;
				case 'range':
					$attrs[] = "$parameter" . ':$R(' . $value . ')';
					break;
				case 'default':
					if (empty($this->params['sliderValue']))
						$attrs[] = "sliderValue:$value";
					break;
				case 'index':
					if (is_array($value))
						$attrs[] = "values:[" . implode(",", array_keys($value))  . "]";
					else
						$attrs[] = "values:[" . $value . "]";
					break;
			}
		}

		$attrs = implode(',', $attrs);
		$value = $this->getValue();
		$label = $this->getLabelName();

		$html = "<table cellpadding=2><tr><td>
		  <div id=\"{$label}_track\" style=\"width:{$size}px;background-color:#aaa;height:3px;\">
  	  		<div id=\"{$label}_handle\" style=\"width:5px;height:10px;background-color:#000;\"> </div>
  		  </div></td><td>
  		  <div id=\"{$label}_display\" style=\"padding-left:5px;float:right;\">{$value}</div>
  		  </td></tr></table>

		  <input type=\"hidden\" {$this->getNameIdString()} value={$value}>
  		  ";

		$html .= "
		<script type=\"text/javascript\" language=\"javascript\">
		// <![CDATA[
		new Control.Slider('" .$label ."_handle','" .$label .'_track\',{
			' . $attrs .  ',
			onSlide:function(v){$(\'' . $label .'_display\').innerHTML=v},
			onChange:function(v){$(\'' .$label. '\').value=v}});
		// ]]>
		</script> ';

		return $html;
	}
}
?>