<?php

/**
 * auth 
 * 
 * @auth 
 * @version $id$
 * @copyright 1997-2008 Portero Inc.
 * @author Steve Francia <steve.francia+zoop@gmail.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/license}
 */
class auth {

	/**
	* The following variables and methods should be duplicated in each class that extends this one
	*/

	private static $instance;
	private $roles = array();

	/**
	 * The private construct prevents instantiating the class externally.  
	 * 
	 * @access private
	 * @return void
	 */
	private function __construct() {
		$this->_loadACL();
	}

	/**
	 * Prevents external instantiation of copies of the Singleton class,
	 * 
	 * @access public
	 * @return void
	 */
	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

	/**
	 * Prevents external instantiation of copies of the Singleton class,
	 * 
	 * @access public
	 * @return void
	 */
	public function __wakeup() {
		trigger_error('Deserializing is not allowed.', E_USER_ERROR);
	}

	/**
	 * get Instance: a singleton method 
	 * 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function gi() {
		if (!self::$instance instanceof self) { 
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Path to the location in the config to find the configuration for (this instance of) auth. 
	 * 
	 * @var string
	 * @access public
	 */
	var $configBase = "zoop.auth";

	/**
	* End of necessary duplicated lines
	*/

	/**
	 * reference to the driver loaded 
	 * 
	 * @var mixed
	 * @access public
	 */
	var $driver;

	/**
	 * Get the backend driver and load it into the instance var. 
	 * 
	 * @access protected
	 * @return void
	 */
	function _loadDriver() {
		global $zoop;
		$backend = $this->getConfig('backend');
		$name = "auth_driver_" . $backend;
		$zoop->addInclude($name, ZOOP_DIR . "/auth/drivers/$backend.php");
		if (class_exists($name)) {
			$this->driver = new $name($this);
			return $this->driver;
		} else {
			trigger_error("Invalid Driver: $name");
		}
	}

	/**
	 * get the backend driver 
	 * 
	 * @access public
	 * @return void
	 */
	function getDriver() {
		if (!$this->driver) {
			$this->_loadDriver();
		}

		return $this->driver;
	}

	/**
	 * Get the Driver and call test on it. 
	 * Driver->test() will connect to the store and return true; 
	 * 
	 * @access public
	 * @return void
	 */
	function testDriver() {
		$drv = $this->getDriver();
		return $drv->test();
	}

	/**
	 * Pull the active user from the backend and place it into the session
	 *
	 * @param mixed $user_id
	 * @access public
	 * @return void
	 */
	function populateActiveUser($user_id) {
		return $this->getDriver()->populateActiveUser($user_id);
	}

	/**
	 * Return active user if user is logged in (NULL otherwise).
	 *
	 * @access public
	 * @return mixed
	 */
	function getActiveUser() {
		return $this->getDriver()->getActiveUser();
	}

	/**
	 * Return active user as an array if user is logged in (NULL otherwise).
	 *
	 * @access public
	 * @return mixed
	 */
	function getActiveUserArray() {
		return $this->getDriver()->getActiveUserArray();
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
		return $this->requireCondition($this->getActiveUser());
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
		return $this->_checkActiveUser((array)$user_id, $this->getConfig('fields.user.id'));
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
		return $this->_checkActiveUser((array)$user, $this->getConfig('fields.user.username'));
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
		$au = $this->getActiveUserArray();
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
		return $this->requireCondition($this->checkUser($user));
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
		return $this->requireCondition($this->checkUserId($user_id));
	}

	/**
	 * Return the groups for the given user, if none is given active user is used.
	 *
	 * @param mixed $user
	 * @access public
	 * @return mixed
	 */
	function getGroups($user = false) {
		return $this->getDriver()->getGroups($user);
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
		return $this->_foundInSet($group_id, $this->getGroups($user));
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
		return $this->checkGroupId($this->_groupNametoId($group), $user);
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
		return $this->requireGroupId($this->_groupNametoId($group));
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
		return $this->requireCondition($this->checkGroupId($group_id));
	}

	/**
	 * Pulls the group id from the db for a given group name.
	 * 
	 * @param mixed $name 
	 * @access protected
	 * @return void
	 */
	function _groupNametoId($name) {
		return $this->getDriver()->_groupNametoId($name);
	}

	/**
	 * Loads permissions from permissions_file, if exists
	 *
	 * @access protected
	 * @return void
	 */
	protected function _loadACL() {
		if ($acl_file = $this->getConfig('permissions_file')) {
			$cache_file = APP_DIR . '/' . $this->getConfig('permissions_file_cache');
			
			if ($cache_file && file_exists($cache_file) && filemtime($cache_file) > filemtime(APP_DIR . '/' . $acl_file)) {
				include($cache_file);
			}
			
			if (empty($this->permissions)) {
				$this->permissions = Yaml::read($this->getConfig('permissions_file'));
				$this->_hydrateACL($this->permissions);
				
				if ($cache_file) {
					if (!file_exists($cache_file)) {
						mkdir_r($cache_file);
					} else if (!is_writable($cache_file)) {
						trigger_error("Unable to write to ACL cache file: $cache_file. Make sure file exists and is writable.");
						return;
					}
					
					file_put_contents($cache_file, "<?php \n\n" . '$this->permissions = ' . var_export($this->permissions, true) . ";\n\n");
				}
			}
		} else {
			$this->permissions = array();
		}
	}

	/**
	 * Takes an array of permissions, fills array children with the values of ALL.
	 * foo.ALL.example appends 'example' to the end of everything else in foo.
	 *
	 * @param array $permissions
	 * @param array $all
	 * @access protected
	 * @return void
	 */
	protected function _hydrateACL(&$permissions, $all = array()) {
		if (isset($permissions['ALL'])) {
			$all = array_merge($permissions['ALL'], $all);
			unset($permissions['ALL']);
		}

		foreach ($permissions as $key => $value) {
			if (is_array($value) && ! empty($value)) {
				$this->_hydrateACL($permissions[$key], $all);
			} elseif ((is_array($value) && empty($value)) || '[]' == $value) {
				$permissions[$key] = $all;
			} else {
				// Assume that each $permissions item is the same and only do this once.
				// Doing this on each $permissions item ends up looping and killing php.
				// This is because $permissions is a referenced array.
				foreach ($all as $v) {
					if (!in_array($v, $permissions)) {
						$permissions[] = $v;
					}
				}
				break;
			}
		}
	}

	/**
	 * Returns ACL permissions based on a permission string (eg. crud.create.users)
	 *
	 * @param string $permission_str String from which to fetch ACL permissions
	 * @access public
	 * @return array of configuration values
	 */
	public function getACL($permission_str) {
		$parts = explode('.', $permission_str);
		$cur = $this->permissions;

		foreach($parts as $thisPart) {
			if(isset($cur[$thisPart])) {
				$cur = $cur[$thisPart];
			} else {
				return array();
			}
		}
		return $cur;
	}

	/**
	 * ACL. Checks that the user has a required role for the current action.
	 * Takes a yaml path string (eg. crud.read.administrators)
	 *
	 * @see auth::getACL
	 * @see auth::checkRole
	 * @param string $permission_str String for which to fetch ACL permissions
	 * @access public
	 * @return boolean
	 */
	public function checkPermission($permission_str) {
		$roles = $this->getACL($permission_str);
		
		foreach ($roles as $role) {
			if ($this->checkRole($role)) return true;
		}
		
		return false;
	}

	/**
	 * ACL. Requires that the user has a required role for the current action.
	 * Takes a yaml path string (eg. crud.read.administrators)
	 *
	 * @see auth::requireCondition
	 * @see auth::checkPermission
	 * @param string $permission_str String for which to fetch ACL permissions
	 * @access public
	 * @return boolean
	 */
	public function requirePermission($permission_str) {
		return $this->requireCondition($this->checkPermission($permission_str));
	}

	/**
	 * Return the roles for the given user, if none is given active user is used.
	 *
	 * @param mixed $user
	 * @access public
	 * @return mixed
	 */
	function getRoles($user = false) {
		return $this->getDriver()->getRoles($user);
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
		return $this->_foundInSet($role_id, $this->getRoles($user));
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
		return $this->checkRoleId($this->_roleNametoId($role), $user);
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
		return $this->requireRoleId($this->_roleNametoId($role));
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
		return $this->requireCondition($this->checkRoleId($role_id));
	}

	/**
	 * Pulls the role id from the backend for a given role name. 
	 * 
	 * @param mixed $name 
	 * @access protected
	 * @return array
	 */
	function _roleNametoId($name) {
		if (!isset($this->roles[$name])) {
			$this->roles[$name] = $this->getDriver()->_roleNametoId($name);
		}
		return $this->roles[$name];
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
	 *            if ($this->checkRole($role) == true) {
	 *                $return = true;
	 *            }
	 *        }
	 *
	 *        return $this->requireCondition($return);
	 *    }
	 */


	/**
	 * If the provided value is true, return true, otherwise call $this->failed.
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
			$this->failed();
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
		return $this->getDriver()->_checkPassword($username, $password);
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
		if ($user_id = $this->_checkPassword($username, $password))	{
			$this->populateActiveUser($user_id);
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
		$this->_logout();
		BaseRedirect($this->getConfig('locations.post_logout'));
	}

	/**
	 * Called whenever a 'require' fails.
	 *
	 * This checks whether there's a user logged in (and if the app wants to show a login form
	 * if there's no current user). If no user, the visitor will be redirected to a login form.
	 * If the user is logged in, they will be shown an Access Denied message.
	 *
	 * @see auth::_showLogin
	 * @see auth::_accessDenied
	 * @access public
	 * @return void
	 */
	function failed() {
		// if there is no user logged in, give them a chance to log in.
		if ($this->getConfig('denied.show_login_if_logged_out') && $this->getActiveUser() === null) {
			return $this->_showLogin();
		} else {
			return $this->_accessDenied();
		}
	}

	/**
	 * Show a login form.
	 *
	 * Redirect user to the login page. If a redirect parameter has been set
	 * ('zoop.auth.login_redirect_param') pass the current url in that param.
	 * This will be handled by the post handler for the login form using something like:
	 *
	 * @code
	 * if ($redirect_to = getGet('redirect')) {
	 *     BaseRedirect($redirect_to);
	 * }
	 * @endcode
	 * 
	 * @access protected
	 * @return void
	 */
	protected function _showLogin() {
		$redirect = $this->getConfig('locations.login');
		if ($redirect_param = $this->getConfig('login_redirect_param', false)) {
			
			// grab the redirect path
			$path = $GLOBALS['PATH_INFO'];
			if ($_SERVER['QUERY_STRING']) $path .= '?' . $_SERVER['QUERY_STRING'];
			
			$redirect .= '/?' . $redirect_param . '=' . urlencode($path);
		}
		BaseRedirect($redirect);
	}
	
	/**
	 * Handle 'access denied' error.
	 *
	 * If preferred method for handling access denied errors is with a redirect, redirect
	 * to that location. Otherwise, display a 401 message or template.
	 * 
	 * @access protected
	 * @return void
	 */
	protected function _accessDenied() {
		if ($this->getConfig('denied.handling') == 'redirect') { 
			BaseRedirect( $this->getConfig('locations.denied') );
		} else {
			$response = '401 Access Denied';
			header('Status: ' . $response, true, 401);
			
			global $gui;
			$gui->assign("title", $response);
			
			if($template = $this->getConfig('denied.template')) {
				$gui->generate($template);
			} else {
				$gui->assignContent('<h2>'.$response.'</h2>');
				$gui->generate();
			}
		}
		die();
	}

	/**
	 * Remove the user from the session.
	 *
	 * @access protected
	 * @return void
	 */
	function _logout() {
		unset($_SESSION['auth'][$this->getConfig('session_user')]);
	}
	
	/**
	 * Redirect user to a login form (will optionally include a redirect back to here).
	 * 
	 * @access protected
	 * @return void
	 */
	protected function _loginRedirect($include_redirect = true) {
		
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
		return $this->getDriver()->_foundInSet($needles, $hay);
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

		return Config::get($this->configBase . $path);
	}

	/**
	 * To overwrite/set the configBase var
	 * 
	 * @param mixed $path 
	 * @access protected
	 * @return void
	 */
	function _setConfigBase($path) {
		$this->configBase = $path;
	}
}
