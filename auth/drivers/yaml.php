<?php
include_once(dirname(__file__) . "/base.php");

class auth_driver_yaml extends auth_driver_base {
	/**
	 * path and filename of the yaml file 
	 * 
	 * @var mixed
	 * @access public
	 */
	var $file;

	/**
	 * UserGroupRole hash  
	 * 
	 * @var mixed
	 * @access public
	 */
	var $ugrhash;

	/**
	 * Read the yaml file into the $this->ugrhash variable 
	 * 
	 * @access protected
	 * @return void
	 */
	function _readYaml() {
		$this->file = $this->getConfig('file');
		$this->ugrhash = Yaml::read($this->file);
         //print_r($this->ugrhash);
	}

	/**
	 * Connect to store and return true 
	 * 
	 * @access public
	 * @return void
	 */
	function test() {
		$this->_readYaml();
		return true;
	}

	/**
	 * Pull the active user from the database and place it into the session.
	 *
	 * @param mixed $user_id
	 * @access public
	 * @return void
	 */
	function populateActiveUser($user_id) {
		$this->_readYaml();
		$user = $this->ugrhash['users'][$user_id];

		if ($this->getConfig('use_active') && $user['active'] != $this->getConfig('active_value')) {
			return false;
		}

		$groups = array();
		if ( $this->getConfig('use_groups') ) {
			$groups = $user['groups'];
		}

		$roles = array();
		if ( $this->getConfig('use_roles') ) {
			$roles = $user['roles'];
		}

		$_SESSION['auth'][$this->getConfig('session_user')] = array('user' => $user, 'groups' => $groups, 'roles' => $roles);
	}

	/**
	 * Pulls the group id from the db for a given group name.
	 * Here for compatibility reasons as in the yaml backend there aren't ids.
	 * 
	 * @param mixed $name 
	 * @access protected
	 * @return void
	 */
	function _groupNametoId($name) {
		return $name;
	}

	/**
	 * Pulls the role id from the db for a given role name. 
	 * Here for compatibility reasons as in the yaml backend there aren't ids.
	 * 
	 * @param mixed $name 
	 * @access protected
	 * @return array
	 */
	function _roleNametoId($name) {
		return $name;
	}

	/**
	 * Check the credentials provided against the database.
	 *
	 * @param mixed $username
	 * @param mixed $password
	 * @access protected
	 * @return void
	 */
	function _checkPassword($username, $password) {
		$this->_readYaml();

		if (!isset($this->ugrhash['users'][$username]) ) {
			return false;
		}

		$user = $this->ugrhash['users'][$username];

		if ($this->getConfig('use_active') && $user['active'] != $this->getConfig('active_value')) {
			return false;
		}

		$pw = $this->_preparePassword($password);

		return ($user['password'] == $pw ); 
	}

	/**
	 * Return's true if the needle(s) are found as the keys or values of the $hay
	 *
	 * @param mixed $needles
	 * @param mixed $hay
	 * @access protected
	 * @return void
	 */
	function _foundInSet($needles, $hay) {
		if (!is_array($hay)) { 
			trigger_error('$hay must be an array'); 
			return false;
		}

		$needles = $this->auth->_arrayize($needles);
		foreach ($needles as $needle) {
			if ( in_array($needle, $hay) ) {
				return true;
			}
		}

		return false;
	}
}
