<?php
/**
* @package db
* @subpackage mysql
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

function sql_connect() {
	if (!isset($GLOBALS['defaultdb']) || $GLOBALS['defaultdb'] == NULL)
		$GLOBALS['defaultdb'] = &new database( Config::get('zoop.db.dsn') );
}

function sql_begin_transaction($Db = -1) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->begin_transaction();
	return $return;
}

function sql_commit_transaction($Db = -1) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->commit_transaction();
	return $return;
}

function sql_rollback_transaction($Db = -1) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->rollback_transaction();
	return $return;
}

function sql_query( $inQueryString, $Db = -1 ) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->query($inQueryString);
	return $return;
}

function sql_get_fields($table) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->get_fields($table);
	return $return;
}

function sql_insert($query) {
	sql_connect();
	global $defaultdb;
	$defaultdb->insert($query);
	return sql_fetch_one_cell('select last_insert_id()');
}

function sql_fetch_sequence( $sequence ) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_sequence($sequence);
	return $return;
}

///////////////////////////////////////////////
//	Query returns true if rows are returned  //
///////////////////////////////////////////////

function sql_check($query) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->check($query);
	return $return;
}

function sql_fetch_into_arrays($query) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_into_arrays($query);
	return $return;

}

function sql_fetch_into_arrobjs($query) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_into_arrobjs($query);
	return $return;
}


//	this is a stupid function name.  Please use sql_fetch_column below
function sql_new_fetch_into_array($query) {
	sql_connect();
	return sql_fetch_column($query);
}

function sql_fetch_column($query) {
	sql_connect();
	global $defaultdb;
	//	the database clas should also create fetch_column and depricate
	//	new_fetch_into_array
	$return = $defaultdb->new_fetch_into_array($query);
	return $return;
}

function sql_fetch_into_array($inTableName, $inFieldName, $inExtra = "") {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_into_array($inTableName, $inFieldName, $inExtra);
	return $return;
}

function sql_fetch_one($inQueryString) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_one($inQueryString);
	return $return;
}

function sql_fetch_assoc($inQuery) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_assoc($inQuery);
	return $return;
}

function sql_fetch_rows($inQuery, $inReturnObjects = 0) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_rows($inQuery, $inReturnObjects);
	return $return;
}

function sql_fetch_map($inQuery, $inKeyField) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_map($inQuery,$inKeyField);
	return $return;
}


function sql_fetch_simple_map($inQuery, $inKeyField, $inValueField) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_simple_map($inQuery, $inKeyField, $inValueField);
	return $return;
}


function sql_fetch_complex_map($inQuery, $inKeyField) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_complex_map($inQuery, $inKeyField);
	return $return;
}


function sql_fetch_one_cell($inQueryString, $inField = 0) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_one_cell($inQueryString, $inField);
	return $return;
}

function &sql_prepare_tree_query($inQueryString, $idField = "id", $parentField = "parent") {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->prepare_tree_query($inQueryString, $idField, $parentField);
	return $return;
}

function &sql_better_fetch_tree( $inQueryString, $rootNode, $idField = "id", $parentField = "parent", $depth = -1) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->better_fetch_tree($inQueryString,$rootNode,$idField,$parentField, $depth);
	return $return;
}

function &sql_fetch_tree( $inQueryString, $rootNode, $idField = "id", $parentField = "parent") {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_tree($inQueryString,$rootNode,$idField,$parentField);
	return $return;
}

//	inQuerystring can be a map (php array/hashtable), and then it will use the map instead of querying the database....
//	This helps in making multiple calls when you need separate arrays for each parent node's children.
//	Might be too much of a secret hack though - at least the var name should probably be changed

function &sql_fetch_children( $inQueryString, $rootNode, $idField = "id", $parentField = "parent") {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_children($inQueryString,$rootNode,$idField,$parentField);
	return $return;
}

function &sql_better_fetch_children( $inQueryString, $rootNode, $idField = "id", $parentField = "parent", $depth = -1) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->better_fetch_children($inQueryString,$rootNode,$idField,$parentField);
	return $return;
}

function &sql_fetch_parents($inQueryString, $leafNode, $idField = "id", $parentField = "parent") {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->fetch_parents($inQueryString,$leafNode,$idField,$parentField);
	return $return;
}

function sql_get_table_info($Table) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->get_table_info($Table);
	return $return;
}

function ticks($inString) {
	if ($inString === NULL)
		return "NULL";
	elseif ($inString == "NULL")
		return $inString;
	else
	{
		return "'$inString'";
	}
}

function sql_escape_string($inString) {
	sql_connect();
	global $defaultdb;
	$return = $defaultdb->escape_string($inString); return $return;
}

function makeDate($Year, $Month = 1, $Day = 1) {
	if ($Year == "") {
		$Date = "NULL";
	} else {
		if ($Month == 0)
			$Month = 1;

		$Month = str_pad($Month, 2, "0", STR_PAD_LEFT);

		if ($Day == 0)
			$Day = 1;

		$Day = str_pad($Day, 2, "0", STR_PAD_LEFT);

		$Date = "$Year-$Month-$Day";
	}

	return $Date;
}
