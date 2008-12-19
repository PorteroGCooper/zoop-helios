<?php
/**
* @category zoop
* @package doctrine
*/
// Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

 /**
 * @package doctrine
 * @uses component
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}/
 */
class component_doctrine extends component {

	function init() {
		$include_path = Config::get('zoop.doctrine.include_path');

		require_once(($include_path ? $include_path . '/' : '') . 'Doctrine.php'); 
		spl_autoload_register(array('Doctrine', 'autoload'));
	}

	/**
	 * Loads all database connections, sets attributes on the connections, and loads models.
	 * 
	 * Doctrine lazy-connects to databases so loading all databases in Doctrine is not a
	 * significant overhead.
	 *
	 * @access public
	 * @return void
	 */
	function run() {
		$connections = Config::get('zoop.doctrine.connections', array());
		
		$connection_name = Config::get('zoop.doctrine.active_connection');

		if (Config::get('zoop.doctrine.dsn')) {
			// The default active_connection is set to '_dsn_connection' in zoop/doctrine/config.yaml
			// Unless overridden in APP_DIR/config.yaml this connection will be the active connection.
			$connections['_dsn_connection']['dsn'] = Config::get('zoop.doctrine.dsn');
			
			/*
			 * From the Doctrine Documentation:
			 * It is worth noting that for certain databases (Firebird, MySql and PostgreSQL) setting the
			 * charset option [in model definitions] might not be enough for Doctrine to return data
			 * properly. For those databases, users are advised to also use the setCharset function of
			 * the database connection.
			 */
			$connections['_dsn_connection']['charset'] = Config::get('zoop.doctrine.charset');
		}
		
		$manager = Doctrine_Manager::getInstance();
		
		foreach ($connections as $conn_name => $connection) {
			if ( ! isset($connection['dsn'])) continue;
			
			// Use zoop.doctrine.charset for any connections without their own charset.
			$charset = isset($connection['charset']) ? $connection['charset'] : Config::get('zoop.doctrine.charset');

			if (empty($charset)) {
				$manager->connection($connection['dsn'], $conn_name);
			} else {
				$manager->connection($connection['dsn'], $conn_name)->setCharset($charset);
			}
		}
		
		$manager->setCurrentConnection($connection_name);
		$manager->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);
		$manager->setAttribute('model_loading', Config::get('zoop.doctrine.model_loading'));
		// DQL Callbacks enable functionality needed for, among other things, SoftDelete.
		if (Config::get('zoop.doctrine.use_dql_callbacks')) {
			$manager->setAttribute('use_dql_callbacks', true);
		} else {
			$manager->setAttribute('use_dql_callbacks', false);
		}
		Doctrine::loadModels(Config::get('zoop.doctrine.models_dir')); // This call will not require the found .php files

		// Attach listeners to the manager, current connection, or another connection.
		if ($listeners = Config::get('zoop.doctrine.listeners')) {
			foreach ($listeners as $type => $listener) {
				if (!$listener['class'] || ! $listener['level']) continue;

				include_once(Config::get('zoop.doctrine.listeners_dir') . '/' . $listener['class'] . '.php');

				switch ($type) {
					case 'Doctrine_EventListener':
					case 'Doctrine_EventListener_Interface':
					case 'Doctrine_Overloadable':
						$method = 'addListener';
						break;
					case 'Doctrine_Record_Listener':
						$method = 'addRecordListener';
						break;
				}

				if ($listener['level'] == 'manager') {
					$manager->$method(new $listener['class']());
				} elseif ($listener['level'] == 'active_connection') {
					$manager->getCurrentConnection()->$method(new $listener['class']());
				} else {
					$manager->getConnection($listener['level'])->$method(new $listener['class']());
				}
			}
		}
	}
}
