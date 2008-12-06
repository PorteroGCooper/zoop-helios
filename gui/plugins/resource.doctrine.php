<?php
/**
 * Zoop Smarty Doctrine Template Resource
 *
 * @group gui
 * @group plugins
 *
 * @author Ryan Martinsen {@link http://ryanware.com}
 */

function _smarty_resource_doctrine_get_config($tpl_name) {
	// Example $tpl_name: TableName/TemplateName
	$parts = explode('/', $tpl_name);
	
	// Get the table name.
	$table = array_shift($parts);
	
	// Put back the rest of the pieces.
	$tpl_name = implode('/', $parts);
	
	$config_path = 'zoop.gui.template_resources.driver_config.doctrine.' . $table;
	
	$config[] = $tpl_name;
	$config[] = $table;
	$config[] = Config::get($config_path . '.name');
	$config[] = Config::get($config_path . '.source');
	
	return $config;
}

function smarty_resource_doctrine_get_template($tpl_name, &$tpl_source, &$smarty_obj) {
	list($tpl_name, $table, $name, $source) = _smarty_resource_doctrine_get_config($tpl_name);
	
	$template = Doctrine_Query::create()
				->select("t.$source")
				->from("$table t")
				->where("t.$name = ?", $tpl_name)
				->fetchArray();

    if (isset($template[0])) {
        $tpl_source = $template[0][$source];
        return true;
    } else {
        return false;
    }
}

function smarty_resource_doctrine_get_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj) {
	list($tpl_name, $table, $name) = _smarty_resource_doctrine_get_config($tpl_name);

	// Get timestamp column from doctrine
	$option = Doctrine::getTable($table)
			 ->getTemplate('Doctrine_Template_Timestampable')
			 ->getOption('updated');
	$timestamp = $option['name'];

	$template = Doctrine_Query::create()
				->select("t.$timestamp")
				->from("$table t")
				->where("t.$name = ?", $tpl_name)
				->fetchArray();

    if (isset($template[0])) {
        $tpl_timestamp = strtotime($template[0][$timestamp]);
        return true;
    } else {
        return false;
    }
}

function smarty_resource_doctrine_get_secure($tpl_name, &$smarty_obj) {
    // assume all templates are secure
    return true;
}

function smarty_resource_doctrine_get_trusted($tpl_name, &$smarty_obj) {
    // not used for templates
}
