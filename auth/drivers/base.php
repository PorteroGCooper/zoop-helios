<?php

class auth_driver_base {
	var $auth;

	function __construct($auth) {
		$this->auth = $auth;
	}

	function test() {
		return true;
	}

	/**
	 * returns the config value for the auth component.
	 *
	 * @param string $path
	 * @return mixed
	 */
	function getConfig($path = false) {
		return $this->auth->getConfig($path);
	}

	/**
	 * Prepare password for comparison by encrypting if enabled 
	 * 
	 * @param mixed $password 
	 * @access protected
	 * @return void
	 */
	function _preparePassword($password) {
		if ( $this->getConfig('password_encryption') && $this->getConfig('encryption') ) {
			$encryptFunction = $this->getConfig('encryption');
			return $encryptFunction($password);
		} else { 
			return $password;
		}
	}

}

