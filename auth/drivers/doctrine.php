<?php
include_once(dirname(__file__) . "/base.php");

class auth_driver_doctrine extends auth_driver_base {
	var $userObj;

	function populateActiveUser($user_id) {
		$userTable = Doctrine::getTable($this->getConfig('models.user'));
		$user = $userTable->find($user_id);

		//$findBy = array(
				//'active'   => 1,
				//'username' => 'bob',
			//);

		//$user = $userTable->customFindByMultiple($findBy);
		//print_r($user->password);
		//exit;
		//

		// var_dump( $user->Association->contains(4));

		if ($this->getConfig('use_active')) {
			$activeProp = $this->getConfig('fields.user.active');
			if ( $user->$activeProp != $this->getConfig('active_value')) {
				return array();
			}
		}

		$_SESSION['auth'][$this->getConfig('session_user')] = $user->toArray();
	}

	/**
	 * Return the groups for the given user, if none is given active user is used.
	 *
	 * @param mixed $user
	 * @access public
	 * @return mixed
	 */
	function getGroups($user = false) {
		if (!$user) { $user = $this->auth->getActiveUser(); }

		$relName = $this->getConfig('models.group');
		return $user->$relName;
	}

	/**
	 * Return the roles for the given user, if none is given active user is used.
	 *
	 * @param mixed $user
	 * @access public
	 * @return mixed
	 */
	function getRoles($user = false) {
		if (!$user) { $user = $this->auth->getActiveUser(); }

		$relName = $this->getConfig('models.role');
		return $user->$relName;
	}

	/**
	 * Return active user if user is logged in (NULL otherwise).
	 *
	 * @access public
	 * @return mixed
	 */
	function getActiveUser() {
		if (isset($_SESSION['auth'][$this->getConfig('session_user')]) && !empty($_SESSION['auth'][$this->getConfig('session_user')]) ) {
			if (!isset($this->userObj)) {
				$userTable = Doctrine::getTable($this->getConfig('models.user'));
				$user_id = $_SESSION['auth'][$this->getConfig('session_user')][$this->getConfig('fields.user.id')];
				$this->userObj = &$userTable->find($user_id);
			}
			return $this->userObj;
		} else {
			return NULL;
		}
	}

	/**
	 * Pulls the group id from doctrine for a given group name.
	 * 
	 * @param mixed $name 
	 * @access protected
	 * @return void
	 */
	function _groupNametoId($name) {
		$call = "findOneBy" . $this->getConfig('fields.group.name');
		$groupTable = Doctrine::getTable($this->getConfig('models.group'));
		$id = $this->getConfig('fields.group.id');
		$group = $groupTable->$call($name);
		if ($group) {
			return $group->$id;
		} else {
			return NULL;
		}
	}

	/**
	 * Pulls the role id from doctrine for a given role name. 
	 * 
	 * @param mixed $name 
	 * @access protected
	 * @return array
	 */
	function _roleNametoId($name) {
		$call = "findOneBy" . $this->getConfig('fields.role.name');
		$roleTable = Doctrine::getTable($this->getConfig('models.role'));
		$id = $this->getConfig('fields.role.id');
		$role = $roleTable->$call($name);
		if ($role) {
			return $role->$id;
		} else {
			return NULL;
		}
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
		$call = "findOneBy" . $this->getConfig('fields.user.username');
		$userTable = Doctrine::getTable($this->getConfig('models.user'));
		$user = $userTable->$call($username);

		if (!$user) {
			return NULL;
		}

		if ($this->getConfig('use_active') ) {
			$activeField = $this->getConfig('fields.user.active');
			if ($user->$activeField != $this->getConfig('active_value') ) {
				return NULL;
			}
		}

		$pw = $this->_preparePassword($password);

		if ($user->password == $pw ) {
			return $user->id;
		} else {
			return NULL;
		}
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
		$newhay = $hay->getPrimaryKeys();
		$needles = $this->auth->_arrayize($needles);
		foreach ($needles as $needle) {
			if ( in_array($needle, $newhay) ) {
				return true;
			}
		}

		return false;
	}

}

