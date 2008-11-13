<?php
/**
* @package gui
* @subpackage guicontrol
*/
include_once(dirname(__file__) . "/text.php");
class passwordControl extends textControl {
	function __construct($var) {
		parent::__construct($var);

		$this->type = 'password';
	}


}
?>
