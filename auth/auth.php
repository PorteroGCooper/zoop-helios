<?php

class auth
{
	var $configBase = "zoop.auth";

	/**
	 * Pull the active user from the database and place it into the session.
	 *
	 * @param mixed $user_id
	 * @access public
	 * @return void
	 */
	function populateActiveUser($user_id) {
		$user = sql_fetch_map("
			SELECT u.*
			FROM " . $this->getConfig('tables.user') . " u
			WHERE " . $this->getConfig('fields.user.id') . " = $user_id
			", $this->getConfig('fields.user.id'));
		// DON'T KNOW WHY THE NEXT BLOCK DOESN'T COMPILE
		$groups = array();
		if ( $this->getConfig('use_groups') ) {
			$groups = sql_fetch_map("
				SELECT g." . $this->getConfig('fields.group.id') . ", g." . $this->getConfig('fields.group.name') .  "
				FROM " . $this->getConfig('tables.group') . " g
				 , " . $this->getConfig('tables.user_group') . " ug
				WHERE g." . $this->getConfig('fields.group.id') . " = ug." . $this->getConfig('fields.user_group.fk_group_id') . "
				 AND ug." . $this->getConfig('fields.user_group.fk_user_id') . " = $user_id
				", $this->getConfig('fields.group.id'));
		}

		$roles = array();
		if ( $this->getConfig('use_roles') ) {
			$roles = sql_fetch_map("
				SELECT r." . $this->getConfig('fields.role.id') . ", r." . $this->getConfig('fields.role.name') .
				"FROM " . $this->getConfig('tables.role') . " r
				 , " . $this->getConfig('tables.user_role') . " ur
				WHERE r." . $this->getConfig('fields.role.id') ." = ur." . $this->getConfig('fields.user_role.fk_role_id') . "
				 AND ur." . $this->getConfig('fields.user_role.fk_user_id') . " = $user_id
				", $this->getConfig('fields.role.id'));

		}

		$_SESSION['auth'][$this->getConfig('session_user')] = array('user' => $user, 'groups' => $groups, 'roles' => $roles);
	}


	/**
	 * Return active user if use is logged in (false otherwise).
	 *
	 * @access public
	 * @return mixed
	 */
	function getActiveUser() {
		if (isset($_SESSION['auth'][$this->getConfig('session_user')]) && !empty($_SESSION['auth'][$this->getConfig('session_user')]) ) {
			return $_SESSION['auth'][$this->getConfig('session_user')];
		} else {
			return false;
		}
	}

	/**
	 * Require if session user is 'logged in'.
	 *
	 * @see auth::getActiveUser
	 * @see auth::requireCondition
	 * @param mixed $user
	 * @return bool
	 */
	function requireLoggedIn() {
		return self::requireCondition(self::getActiveUser());
	}

	/**
	 * Check if session user matches provided user_id  
	 * Takes a user_id or an array of user_ids.
	 *
	 * @see auth::getActiveUser
	 * @param mixed $user
	 * @access public
	 * @return bool
	 */
	function checkUserId($user_id) {
		return self::_checkActiveUser(self::_arrayize($user_id), $this->getConfig('fields.user.id'));
	}

	/**
	 * Check if session username matches provided username.
	 * Takes a username or an array of usernames.
	 *
	 * @see auth::getActiveUser
	 * @param mixed $user
	 * @access public
	 * @return bool
	 */
	function checkUser($user) {
		return self::_checkActiveUser($self::_arrayize($user), $this->getConfig('fields.user.username'));
	}
	
	/**
	 * Check an array of values to see if one of the values is found in the active user.
	 * 
	 * @param mixed $array the array containing the potential values.
	 * @param mixed $field the field in the user array to compare against.
	 * @access protected
	 * @return boolean, $field
	 */
	function _checkActiveUser($array, $field) {
		$au = self::getActiveUser();
		return (isset($au[$field]) && !empty($au[$field]) && in_array($au[$field], $array));
	}

	/**
	 * Require one of the provided user(s) to match the session user
	 * Takes either a username, or an array of usernames
	 *
	 * @see auth::requireCondition
	 * @param mixed $user
	 * @return bool
	 */
	function requireUser($user) {
		return self::requireCondition(self::checkUser($user));
	}

	/**
	 * Require one of the provided user_id(s) to match the session user_id
	 * Takes either a user_id, or an array of user_ids
	 *
	 * @see auth::requireCondition
	 * @param mixed $user_id
	 * @return bool
	 */
	function requireUserId($user_id) {
		return self::requireCondition(self::checkUserId($user_id));
	}

	/**
	 * Return the groups for the given user, if none is given active user is used.
	 *
	 * @param mixed $user
	 * @access public
	 * @return mixed
	 */
	function getGroups($user = false) {
		if (!$user) { $user = self::getActiveUser(); }

		if ( isset($user['groups']) && !empty($user['groups']) ) {
			return $user['groups'];
		} else {
		    	return false;
		}
	}

	/**
	 * Check to see if the provided group_id is found in the give users group_ids.
	 * If no user is provided the active user is used.
	 *
	 * @see auth::getGroups
	 * @see auth::_foundInSet
	 * @param mixed $group_id
	 * @access public
	 * @return boolean
	 */
	function checkGroupId($group_id, $user = false) {
		return self::_foundInSet($group_id, self::getGroups($user));
	}

	/**
	 * Takes a Group name or array of names and fetches the id from the
	 * db and runs checkGroupId on it.
	 *  
	 * @see auth::_groupNametoId
	 * @see auth::checkGroupId
	 * @param mixed $group 
	 * @param mixed $user 
	 * @access public
	 * @return boolean
	 */
	function checkGroup($group, $user = false ) {
		return self::checkGroupId(self::_groupNametoId($group), $user);
	}

	/**
	 * Require the provided group to be found in session user's groups.
	 * Takes either a group, or an array of groups
	 *
	 * @see auth::requireCondition
	 * @param mixed $group
	 * @return bool
	 */
	function requireGroup($group) {
		return self::requireGroupId(self::_groupNametoId($group));
	}

	/**
	 * Require the provided group_id to be found in session user's groups.
	 * Takes either a group_id, or an array of group_ids
	 *
	 * @see auth::requireCondition
	 * @param mixed $group_id
	 * @return bool
	 */
	function requireGroupId($group_id) {
		return self::requireCondition(self::checkGroupId($group_id));
	}

	/**
	 * Pulls the group id from the db for a given group name.
	 * 
	 * @param mixed $name 
	 * @access protected
	 * @return void
	 */
	function _groupNametoId($name) {
		$name = self::_arrayize($name);

		return sql_fetch_rows("
				SELECT r." . $this->getConfig('fields.group.id') .  
				"FROM " . $this->getConfig('tables.group') . " r
				WHERE r." . $this->getConfig('fields.group.name') ."= '$name'");
	}


	/**
	 * Return the roles for the given user, if none is given active user is used.
	 *
	 * @param mixed $user
	 * @access public
	 * @return mixed
	 */
	function getRoles($user = false) {
		if (!$user) { $user = self::getActiveUser(); }

		if ( isset($user['roles']) && !empty($user['roles']) ) {
			return $user['roles'];
		} else {
		    	return false;
		}
	}

	/**
	 * Check to see if the provided role_id is found in the give users role_ids.
	 * If no user is provided the active user is used.
	 *
	 * @see auth::getRoles
	 * @see auth::_foundInSet
	 * @param mixed $role_id
	 * @access public
	 * @return boolean
	 */
	function checkRoleId($role_id, $user = false) {
		return self::_foundInSet($role_id, self::getRoles($user));
	}

	/**
	 * Takes a Role name or array of names and fetches the id from the
	 * db and runs checkRoleId on it.
	 *  
	 * @see auth::_roleNametoId
	 * @see auth::checkRoleId
	 * @param mixed $role 
	 * @param mixed $user 
	 * @access public
	 * @return boolean
	 */
	function checkRole($role, $user = false ) {
		return self::checkRoleId(self::_roleNametoId($role), $user);
	}

	/**
	 * Require the provided role to be found in session user's roles.
	 * Takes either a role, or an array of roles
	 *
	 * @see auth::requireCondition
	 * @param mixed $role
	 * @return bool
	 */
	function requireRole($role) {
		return self::requireRoleId(self::_roleNametoId($role));
	}

	/**
	 * Require the provided role_id to be found in session user's roles.
	 * Takes either a role_id, or an array of role_ids
	 *
	 * @see auth::requireCondition
	 * @param mixed $role_id
	 * @return bool
	 */
	function requireRoleId($role_id) {
		return self::requireCondition(self::checkRoleId($role_id));
	}

	/**
	 * Pulls the role id from the db for a given role name. 
	 * 
	 * @param mixed $name 
	 * @access protected
	 * @return array
	 */
	function _roleNametoId($name) {
		$name = self::_arrayize($name);

		return sql_fetch_rows("
				SELECT r." . $this->getConfig('fields.role.id') .  
				"FROM " . $this->getConfig('tables.role') . " r
				WHERE r." . $this->getConfig('fields.role.name') ."= '$name'");
	}


	/**
	 * Wrapper for requireRole, returns true if one of the provided roles match one of the active user's roles.
	 *
	 * @see auth::requireRole
	 * @param array $roles
	 * @access public
	 * @return mixed
	 */
/*
 *    function requireInRoles($roles) {
 *        $return = false;
 *
 *        foreach ($roles as $role) {
 *            if (self::checkRole($role) == true) {
 *                $return = true;
 *            }
 *        }
 *
 *        return self::requireCondition($return);
 *    }
 */


	/**
	 * If the provided value is true, return true, otherwise call self::failed.
	 *
	 * @see auth::failed
	 * @param mixed $var
	 * @access public
	 * @return void
	 */
	function requireCondition($var) {
		if ($var) {
			return true;
		} else {
			self::failed();
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
		$encryptFunction = $this->getConfig('password_encryption');
		$pw = $encryptFunction($password);
		return sql_fetch_one("SELECT " . $this->getConfig('fields.user.id') . " FROM " . $this->getConfig('tables.user') . " u WHERE " . $this->getConfig('fields.user.username') . " = $username AND " . $this->getConfig('fields.user.password') . " = $pw");
	}

	/**
	 * Validate the user against the database. Populate the user into the session.
	 *
	 * @see auth::populateActiveUser
	 * @see auth::_checkPassword
	 * @param mixed $username
	 * @param mixed $password
	 * @access public
	 * @return void
	 */
	function logIn($username, $password) {
		if ($user_id = self::_checkPassword($username, $password))	{
			self::populateActiveUser($user_id);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Log out user and send to the post_logout locations.
	 *
	 * @see auth::_logout
	 * @access public
	 * @return void
	 */
	function logOut() {
	    	self::_logout();
		BaseRedirect($this->getConfig('locations.post_logout'));
	}

	/**
	 * Called whenever a require fails.
	 * By default it will logout the user and redirect them to the configured denied location.
	 *
	 * @see auth::_logout
	 * @access public
	 * @return void
	 */
	function failed() {
	    	self::_logout();
		BaseRedirect( $this->getConfig('locations.denied') );
		return false;
	}

	/**
	 * Remove the user from the session.
	 *
	 * @access protected
	 * @return void
	 */
	function _logout() {
		unset( $_SESSION['auth'][$this->getConfig('session_user')] );
	}

	/**
	 * Converting a given parameter to an array if it isn't already
	 *
	 * @param mixed $in
	 * @access protected
	 * @return void
	 */
	function _arrayize($in) {
		if (is_array($in)) {
			return $in;
		} else {
			return array($in);
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
		$needles = self::_arrayize($needle);
		foreach ($needles as $needle) {
			if ( array_key_exists($needle, $hay) || in_array($needle, $hay) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * returns the config value for the auth component.
	 *
	 * @param string $path
	 * @return mixed
	 */
	function getConfig($path = false) {
		if ($path) {
			$path = "." . $path;
		}

		return Config::get($configBase . $path );
	}
}
