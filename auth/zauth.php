<?php

class zauth
{
	function requireLoggedIn()
	{
		if (self::getActiveUser()) {
			return true;
		} else {
			self::failed();
		}	
	}

	function requireUser($user)
	{	
		$au = self::getActiveUser();
		$user = self::paramToArray($user);

		if (isset($au['user_id']) && !empty($au['user_id']) && in_array($au['user_id'], $user)) {
			return true;
		} else {
			self::failed();
		}	
	
	}

	function requireGroup($group)
	{
		$au = self::getActiveUser();
		if (isset($au['groups']) && !empty($au['groups']) && self::anyExistInArray($group, $au['groups']) ) {
			return true;
		} else {
			self::failed();
		}
	}

	function requireRole($role)
	{
		$au = self::getActiveUser();
		if (isset($au['roles']) && !emtpy($au['roles']) && self::anyExistInArray($role, $au['roles']) ) {
			return true;
		} else {
			self::failed();
		}
	}

	function requireCondition($var)
	{
		if  ($var) {
			return true;
		} else {
			self::failed();
		}
	}

	function populateActiveUser($user_id)
	{
		$user = sql_fetch_assoc("
			SELECT u.* 
			FROM " . AUTH_USER_TABLE . " u 
			WHERE user_id = $user_id
			");

		if (!empty(AUTH_GROUP_TABLE)) {
			$groups = sql_fetch_assoc("
				SELECT g.group_id, g.name 
				FROM " . AUTH_GROUP_TABLE . " g
				 , " . AUTH_USER_GROUP_TABLE . " ug 
				WHERE g.group_id = ug.group_id
				 AND user_id = $user_id
				");
		} else {
			$groups = array();
		}

		if (!empty(AUTH_ROLE_TABLE)) {
			$roles = sql_fetch_assoc("
				SELECT r.role_id, r.name
				FROM " . AUTH_ROLE_TABLE . " r
				 , " . AUTH_USER_ROLE_TABLE . " ur
				WHERE r.role_id = ur.role_id
				 AND user_id = $user_id
				");

		} else {
			$roles = array();
		}

		$_SESSION[AUTH_SESSION_USER] = array('user' => $user, 'groups' => $groups, 'roles' => $roles);
	}

	function checkPassword($username, $password)
	{
		$encryptFunction = AUTH_PASSWORD_ENCRYPTION;
		$pw = $encryptFunction($password);
		return sql_fetch_one("SELECT user_id FROM " . AUTH_USER_TABLE . " u WHERE username = $username AND $password = $pw");
	}

	function logIn($username, $password)
	{
		if ($user_id = self::checkPassword($username, $password)
		{
			self::populateActiveUser($user_id);
			return true;
		}
		else {
			return false;
		}
	}

	function logOut()
	{
		unset( $_SESSION[AUTH_SESSION_USER] );
		BaseRedirect(AUTH_POSTLOGOUT_LOCATION);
	}

	// Extend and replace this function to change the default behavior.
	function failed()
	{
		unset ( $_SESSION[AUTH_SESSION_USER] );
		BaseRedirect( AUTH_FAILED_LOCATION );
		return false;
	}

	function paramToArray($in)
	{
		if (is_array($in)) {
			return $in;
		} else {
			return array($in);
		}
	}

	function getActiveUser()
	{
		if (isset($_SESSION[AUTH_SESSION_USER]) && !empty($_SESSION[AUTH_SESSION_USER]) ) {
			return $_SESSION[AUTH_SESSION_USER];
		} else {
			return false;
		}
	}

	function anyExistInArray($needles, $hay)
	{
		$needles = self::paramToArray($needle);
		foreach ($needles as $needle)
		{
			if ( array_key_exists($needle, $hay) || in_array($needle, $hay) ) {
				return true;
			}	
		}

		return false;
	}
}
