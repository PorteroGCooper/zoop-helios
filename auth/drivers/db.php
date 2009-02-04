<?php

include_once(dirname(__file__) . '/base.php');

/**
 * A PDO DB backed Auth driver
 * 
 * @extends AuthDriver_Base
 */
class AuthDriver_DB extends AuthDriver_Base {

	/**
	 * Pull the active user from the database and place it into the session.
	 *
	 * @param integer $user_id
	 * @access public
	 * @return void
	 */
	public function populateActiveUser($user_id) {
		if (!is_numeric($user_id) || empty($user_id)) return;

		$AND = '';
		if ($this->getConfig('use_active') ) {
			$AND = ' AND ' . $this->getConfig('fields.user.active') . " = '" . $this->escape_string($this->getConfig('active_value')) . "'";
		}

		$sql = "
			SELECT u.* FROM " . $this->getConfig('tables.user') . " u 
			WHERE u." . $this->getConfig('fields.user.id') . ' = ' . $user_id . $AND;
		$user = sql_fetch_map($sql, $this->getConfig('fields.user.id'));

		$groups = array();
		if ($this->getConfig('use_groups')) {
			$sql = "
				SELECT g." . $this->getConfig('fields.group.id') . ", g." . $this->getConfig('fields.group.name') .  "
				FROM " . $this->getConfig('tables.group') . " g, " . $this->getConfig('tables.user_group') . " ug
				WHERE g." . $this->getConfig('fields.group.id') . " = ug." . $this->getConfig('fields.user_group.fk_group_id') . "
				AND ug." . $this->getConfig('fields.user_group.fk_user_id') . ' = ' . $user_id;

			//print_r($sql . "\n\r");
			$groups = sql_fetch_map($sql, $this->getConfig('fields.group.id'));
		}

		$roles = array();
		if ($this->getConfig('use_roles')) {
			$sql = "
				SELECT r." . $this->getConfig('fields.role.id') . ", r." . $this->getConfig('fields.role.name') . " 
				FROM " . $this->getConfig('tables.role') . " r, " . $this->getConfig('tables.user_role') . " ur
				WHERE r." . $this->getConfig('fields.role.id') ." = ur." . $this->getConfig('fields.user_role.fk_role_id') . "
				AND ur." . $this->getConfig('fields.user_role.fk_user_id') . ' = ' . $user_id;

			$roles = sql_fetch_map($sql, $this->getConfig('fields.role.id'));
		}

		$_SESSION['auth'][$this->getConfig('session_user')] = array('user' => $user, 'groups' => $groups, 'roles' => $roles);
	}

	/**
	 * Pulls the group id from the db for a given group name.
	 * 
	 * @param mixed $name 
	 * @access protected
	 * @return void
	 */
	function _groupNametoId($name) {
		$sql = "
			SELECT g." . $this->getConfig('fields.group.id') . "
			FROM " . $this->getConfig('tables.group') . " g 
			WHERE g." . $this->getConfig('fields.group.name') . " = '". $this->escape_string($name) . "'";
		$result = sql_fetch_one($sql);
		return $result[$this->getConfig('fields.group.id')];
	}

	/**
	 * Pulls the role id from the db for a given role name. 
	 * 
	 * @param mixed $name 
	 * @access protected
	 * @return array
	 */
	function _roleNametoId($name) {
		$sql = "
			SELECT r." . $this->getConfig('fields.role.id') .  "
			FROM " . $this->getConfig('tables.role') . " r 
			WHERE r." . $this->getConfig('fields.role.name') . " = '" . $this->escape_string($name) . "'";
		$result = sql_fetch_one($sql);
		return $result[$this->getConfig('fields.role.id')];
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
		$AND = '';
		if ($this->getConfig('use_active') ) {
			$AND = ' AND ' . $this->getConfig('fields.user.active') . " = '" . $this->escape_string($this->getConfig('active_value')) . "'";
		}

		$pw = $this->_preparePassword($password);

		$sql =
			"SELECT " . $this->getConfig('fields.user.id')
			. " FROM " . $this->getConfig('tables.user') . " u"
			. " WHERE " . $this->getConfig('fields.user.username') . " = '" . $this->escape_string($username)
				. "' AND " . $this->getConfig('fields.user.password') . " = '" . $this->escape_string($pw) . "'"
				. $AND;
		//print_r($sql . "\n\r");
		return sql_fetch_one($sql);
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
		foreach ((array)$needles as $needle) {
			if ( array_key_exists($needle, $hay) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Escapes a string using the default database's function for escaping.
	 *
	 * @param string  string to be escaped
	 * @access protected
	 * @return string
	 */
	protected function escape_string($string) {
		if (!isset($GLOBALS['defaultdb']) || !($GLOBALS['defaultdb'] instanceof database)) {
			trigger_error('db has not been instantiated?');
			return false;
		}
		return $GLOBALS['defaultdb']->escape_string($string);
	}

}
