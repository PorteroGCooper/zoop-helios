<?php
/**
* @package gui
* @subpackage guicontrol
*/
include_once(dirname(__file__) . "/select.php");
class multiple extends select
{
	function render()
	{
		$this->params['multiple'] = 1;
		if (!isset($this->params['size']))
			$this->params['size'] = 4;
		return parent::render();
	}
}
?>