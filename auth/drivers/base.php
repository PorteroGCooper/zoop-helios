<?php

abstract class AuthDriver_Base {
	var $auth;

	public function __construct($auth) {
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
	public function getConfig($path = false) {
		return $this->auth->getConfig($path);
	}

	/**
	 * Return the groups for the given user, if none is given active user is used.
	 *
	 * @param mixed $user
	 * @access public
	 * @return mixed
	 */
	public function getGroups($user = false) {
		if (!$user) $user = $this->auth->getActiveUser();

		if (isset($user['groups']) && !empty($user['groups'])) {
			return $user['groups'];
		} else {
			return array();
		}
	}

	/**
	 * Return the roles for the given user, if none is given active user is used.
	 *
	 * @param mixed $user
	 * @access public
	 * @return mixed
	 */
	public function getRoles($user = false) {
		if (!$user) $user = $this->auth->getActiveUser();

		if (isset($user['roles']) && !empty($user['roles'])) {
			return $user['roles'];
		} else {
			return array();
		}
	}

	/**
	 * Return active user if use is logged in (null otherwise).
	 *
	 * @access public
	 * @return mixed
	 */
	public function getActiveUser() {
		if (isset($_SESSION['auth'][$this->getConfig('session_user')]) && !empty($_SESSION['auth'][$this->getConfig('session_user')])) {
			return $_SESSION['auth'][$this->getConfig('session_user')];
		} else {
			return null;
		}
	}

	public function getActiveUserArray() {
		// don't call this... cast result of getActiveUser() as an array instead.
		deprecated("WTF?");
		return (array)$this->getActiveUser();
	}

	/**
	 * Prepare password for comparison by encrypting if enabled 
	 * 
	 * @param string $password 
	 * @access protected
	 * @return string
	 */
	protected function _preparePassword($password) {
		if ($this->getConfig('password_encryption') && $this->getConfig('encryption')) {
			$encryptFunction = $this->getConfig('encryption');
			return $encryptFunction($password);
		} else { 
			return $password;
		}
	}
}