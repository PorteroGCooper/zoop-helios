<?php

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
 * @ingroup doctrine
 * @ingroup components
 * @ingroup DB
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
		
		$active_connection = Config::get('zoop.doctrine.active_connection');
		
		if (Config::get('zoop.doctrine.dsn')) {
			// The default active_connection is set to '_dsn_connection' in zoop/doctrine/config.yaml
			// Unless overridden in APP_DIR/config.yaml this connection will be the active connection.
			$connections['_dsn_connection']['dsn'] = Config::get('zoop.doctrine.dsn');
		}
		
		$manager = Doctrine_Manager::getInstance();
		
		// Defaults. Any connection without its own collate and charset settings will use these.
		if (Config::get('zoop.doctrine.collate')) $manager->setCollate(Config::get('zoop.doctrine.collate'));
		if (Config::get('zoop.doctrine.charset')) $manager->setCharset(Config::get('zoop.doctrine.charset'));
		
		foreach ($connections as $conn_name => $conn_options) {
			if ( ! isset($conn_options['dsn'])) continue;
			
			// Setup the connection.
			$connection = $manager->connection($conn_options['dsn'], $conn_name);
			
			// Even though the defaults have been set on the manager, Doctrine still chokes
			// on some character sets unless the collate and charset settings are put on
			// every connection (for Firebird, MySql and PostgreSQL).
			$collate = isset($conn_options['collate']) ? $conn_options['collate'] : Config::get('zoop.doctrine.collate');
			$charset = isset($conn_options['charset']) ? $conn_options['charset'] : Config::get('zoop.doctrine.charset');
			
			if ($collate) $connection->setCollate($collate);
			if ($charset) $connection->setCharset($charset);
		}
		// No reason to keep the last connection object around.
		unset($connection);
		
		$manager->setCurrentConnection($active_connection);
		
		if (Config::get('zoop.doctrine.validation')) {
			$manager->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);
		}
		
		if (strtolower(Config::get('zoop.doctrine.model_loading')) == 'aggressive') {
			$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_AGGRESSIVE);
		} else {
			$manager->setAttribute(Doctrine::ATTR_MODEL_LOADING, Doctrine::MODEL_LOADING_CONSERVATIVE);
		}
		
		// DQL Callbacks enable functionality needed for, among other things, SoftDelete.
		$manager->setAttribute(Doctrine::ATTR_USE_DQL_CALLBACKS, (bool) Config::get('zoop.doctrine.use_dql_callbacks'));
		
		Doctrine::loadModels(Config::get('zoop.doctrine.models_dir')); // This call will not require the found .php files
		
		if (Config::get('zoop.doctrine.profiler')) {
			global $doctrine_profiler;
			$doctrine_profiler = new Doctrine_Connection_Profiler();
			$manager->getCurrentConnection()->addListener($doctrine_profiler);
		}
		
		if ($behaviors = Config::get('zoop.doctrine.behaviors')) {
			foreach ($behaviors as $behavior) {
				include_once(Config::get('zoop.doctrine.behaviors_dir') . '/' . $behavior . '.php');
			}
		}
		
		// Attach listeners to the manager, current connection, or another connection.
		if ($listeners = Config::get('zoop.doctrine.listeners')) {
			foreach ($listeners as $type => $listener_classes) {
				foreach ((array)$listener_classes as $listener) {
					if (!isset($listener['class']) || !$listener['class']) continue;
					if ((!isset($listener['level']) || !$listener['level']) && $type != 'Include_Only') continue;
					
					include_once(Config::get('zoop.doctrine.listeners_dir') . '/' . $listener['class'] . '.php');
					
					$method = '';
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
					
					if (empty($method)) continue;
					
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
}
