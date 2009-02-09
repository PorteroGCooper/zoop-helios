<?php

class Component_Core extends Component {
	function __construct() {
		$this->requireComponent('spyc');
		$this->requireComponent('config');
		
		//include($base . '/Config.php');
		
		//include($base . "/error.php");
		//include($base . "/utils.php");
		//include($base . "/FileUtils.php");
	}
}