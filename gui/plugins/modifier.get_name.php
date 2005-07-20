<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     get_name
 * Purpose:  Return name field from specified table or other field based on $fieldname
 * -------------------------------------------------------------
 */
function smarty_modifier_get_name($row_id, $tablename, $fieldname = 'name')
{
	// For performance reasons, store retrieved user info in a static array and only retrieve from database as needed.
	if (empty($tablename)) trigger_error("You must pass a table name to get_name");
	if (!(int)($row_id)) return $row_id;

	// For the special case of population, return a strings file entry
	if ($tablename == 'population') {
		global $strings;
		return $strings['groups'][$row_id];
	}

	// Get the requested field and return
	$sql = "select $fieldname from $tablename where id = $row_id";
	return sql_fetch_one_cell($sql);
}

/* vim: set expandtab: */

?>
