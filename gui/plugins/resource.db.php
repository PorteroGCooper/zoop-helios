<?php
/**
 * Zoop Smarty Database Template Resource
 *
 * @group gui
 * @group plugins
 *
 * @author Ryan Martinsen {@link http://ryanware.com}
 */

function _smarty_resource_db_get_config($tpl_name) {
	// Example $tpl_name: TableName/TemplateName
	$parts = explode('/', $tpl_name);
	
	// Get the table name.
	$table = array_shift($parts);
	
	// Put back the rest of the pieces.
	$tpl_name = implode('/', $parts);
	
	$config_path = 'zoop.gui.template_resources.driver_config.db.' . $table;
	
	$config[] = $tpl_name;
	$config[] = $table;
	$config[] = Config::get($config_path . '.name');
	$config[] = Config::get($config_path . '.source');
	$config[] = Config::get($config_path . '.timestamp');
	
	return $config;
}

function smarty_resource_db_get_template($tpl_name, &$tpl_source, &$smarty_obj) {
	list($tpl_name, $table, $name, $source) = _smarty_resource_db_get_config($tpl_name);

	$template = sql_fetch_one("SELECT $source FROM $table WHERE $name = '". mysql_escape_string($tpl_name) . "'");

    if (isset($template[$source])) {
        $tpl_source = $template[$source];
        return true;
    } else {
        return false;
    }
}

function smarty_resource_db_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj) {
	list($tpl_name, $table, $name, $source, $timestamp) = _smarty_resource_db_get_config($tpl_name);

	$template = sql_fetch_one("SELECT $timestamp FROM $table WHERE $name = '". mysql_escape_string($tpl_name) . "'");
	
    if (isset($template[$timestamp])) {
        $tpl_timestamp = strtotime($template[$timestamp]);
        return true;
    } else {
        return false;
    }
}

function smarty_resource_db_get_secure($tpl_name, &$smarty_obj) {
    // assume all templates are secure
    return true;
}

function smarty_resource_db_get_trusted($tpl_name, &$smarty_obj) {
    // not used for templates
}
