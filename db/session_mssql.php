<?
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

/* ------------------------------------------------------------------------
 * session_mssql.php
 * ------------------------------------------------------------------------
 * PHP4 mssql Session Handler
 * Version 1.00
 * by Ying Zhang (ying@zippydesign.com)
 * Last Modified: May 21 2000
 *
 * ------------------------------------------------------------------------
 * TERMS OF USAGE:
 * ------------------------------------------------------------------------
 * You are free to use this library in any way you want, no warranties are
 * expressed or implied.  This works for me, but I don't guarantee that it
 * works for you, USE AT YOUR OWN RISK.
 *
 * While not required to do so, I would appreciate it if you would retain
 * this header information.  If you make any modifications or improvements,
 * please send them via email to Ying Zhang <ying@zippydesign.com>.
 *
 * ------------------------------------------------------------------------
 * DESCRIPTION:
 * ------------------------------------------------------------------------
 * This library tells the PHP4 session handler to write to a mssql database
 * instead of creating individual files for each session.
 *
 * Create a new database in mssql called "sessions" like so:
 *
 * CREATE TABLE sessions (
 *      sesskey char(32) not null,
 *      expiry int(11) unsigned not null,
 *      value text not null,
 *      PRIMARY KEY (sesskey)
 * );
 *
 * ------------------------------------------------------------------------
 * INSTALLATION:
 * ------------------------------------------------------------------------
 * Make sure you have mssql support compiled into PHP4.  Then copy this
 * script to a directory that is accessible by the rest of your PHP
 * scripts.
 *
 * ------------------------------------------------------------------------
 * USAGE:
 * ------------------------------------------------------------------------
 * Include this file in your scripts before you call session_start(), you
 * don't have to do anything special after that.
 */



$SESS_DBHOST = fwDB_Server;			/* database server hostname */
$SESS_DBNAME = fwDB_Database;			/* database name */
$SESS_DBUSER = fwDB_Username;		/* database user */
$SESS_DBPASS = fwDB_Password;		/* database password */

$SESS_DBH = "";
$SESS_LIFE = get_cfg_var("session.gc_maxlifetime");

function sess_open($save_path, $session_name) {
	global $SESS_DBHOST, $SESS_DBNAME, $SESS_DBUSER, $SESS_DBPASS, $SESS_DBH;

	if (! $SESS_DBH = mssql_pconnect($SESS_DBHOST, $SESS_DBUSER, $SESS_DBPASS)) {
		echo "<li>Can't connect to $SESS_DBHOST as $SESS_DBUSER";
		echo "<li>mssql Error: ", mssql_error();
		die;
	}
	
	//echo $SESS_DBH;

	if (! mssql_select_db($SESS_DBNAME, $SESS_DBH)) {
		echo "<li>Unable to select database $SESS_DBNAME";
		die;
	}

	return true;
}

function sess_close() {
	return true;
}

function sess_read($key) {
	global $SESS_DBH, $SESS_LIFE;

	//die($SESS_DBH);
	$qry = "SELECT value FROM sessions WHERE sesskey = '$key' AND expiry > " . time();
	$qid = mssql_query($qry, $SESS_DBH);

	//echo "id: $qid<BR>";
	
	if (list($value) = mssql_fetch_row($qid)) {
		return $value;
	}

	return false;
}

function sess_write($key, $val) {
	global $SESS_DBH, $SESS_LIFE;
	
	//	we doen't really want this thing to timeout
	$expiry = time() + $SESS_LIFE + (60 * 60 * 24);
	//$value = addslashes($val);
	$value = str_replace("'", "''", $val);


	$qid = mssql_query("session_save '$key', '$value', $expiry", $SESS_DBH);
/*	$row = mssql_fetch_row($qid);
	$count = $row[0];

	if($count == 0)
	{
		$qry = "INSERT INTO sessions VALUES ('$key', $expiry, '$value')";
		$qid = mssql_query($qry, $SESS_DBH);
	}
	else
	{
		$qry = "UPDATE sessions SET expiry = $expiry, value = '$value' WHERE sesskey = '$key' AND expiry > " . time();
		$qid = mssql_query($qry, $SESS_DBH);
	}*/

	return $qid;
}

function sess_destroy($key) {
	global $SESS_DBH;

	$qry = "DELETE FROM sessions WHERE sesskey = '$key'";
	$qid = mssql_query($qry, $SESS_DBH);

	return $qid;
}

function sess_gc($maxlifetime) {
	global $SESS_DBH;

	$qry = "DELETE FROM sessions WHERE expiry < " . time();
	//die($qry);
	$qid = mssql_query($qry, $SESS_DBH);

	return mssql_affected_rows($SESS_DBH);
}

session_set_save_handler(
	"sess_open",
	"sess_close",
	"sess_read",
	"sess_write",
	"sess_destroy",
	"sess_gc");
?>