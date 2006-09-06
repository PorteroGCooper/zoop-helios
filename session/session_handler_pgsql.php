<?php
/**
 * PostgreSQL Session Handler for PHP
 *
 * Copyright 2000-2003 Jon Parise <jon@php.net>.  All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions
 *  are met:
 *  1. Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *  2. Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 *
 *  THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND
 *  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 *  IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 *  ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE
 *  FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 *  DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
 *  OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 *  HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 *  LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 *  OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 *  SUCH DAMAGE.
 *
 * Usage Notes
 * ~~~~~~~~~~~
 * - In php.ini, set session.save_handler to 'user'.
 * - In php.ini, set session.save_path to the name of the database table.
 * - Modify the $params string in pgsql_session_open() to match your setup.
 * - Create the table structure using the follow schema:
 *
 *      CREATE TABLE php_sessions (
 *          session_id  CHAR(40)    NOT NULL PRIMARY KEY,
 *          last_active INTEGER     NOT NULL,
 *          data        TEXT
 *      );
 *
 * @author  Jon Parise <jon@php.net>
 * @version 2.1, 02/10/2003
 * @package session
 * $Id: session_handler_pgsql.php,v 1.5 2004/09/23 05:00:52 rick Exp $
 */

/* Get the name of the session table.  Default to 'php_sessions'. */
$pgsql_session_table = 'php_sessions';

/* Global PostgreSQL database connection handle. */
$pgsql_session_handle = null;

/**
 * Opens a new session.
 *
 * @param   string  $save_path      The value of session.save_path.
 * @param   string  $session_name   The name of the session ('PHPSESSID').
 *
 * @return  boolean True on success, false on failure.
 */
function pgsql_session_open($save_path, $session_name)
{
    global $pgsql_session_handle;
    echo_r("hello");

    /* See: http://www.php.net/manual/function.pg-pconnect.php */
    if(defined(session_separate) && session_separate)
    	$params = 'host=' . session_server . ' port=' . session_port . ' dbname=' . session_database . ' user=' . session_username . ' password=' . session_password;
    else
    {
	    global $defaultdsn;
    	$params = 'host=' . $defaultdsn['hostspec'] . ' port=' . $defaultdsn['port'] . ' dbname=' . $defaultdsn['database'] . ' user=' . $defaultdsn['username'] . ' password=' . $defaultdsn['password'];
	}

    return ($pgsql_session_handle = pg_connect($params));
}

/**
 * Closes the current session.
 *
 * @return  boolean True on success, false on failure.
 */
function pgsql_session_close()
{
    global $pgsql_session_handle;


    if (isset($pgsql_session_handle)) {
        return pg_close($pgsql_session_handle);
    }

    return true;
}

/**
 * Reads the requested session data from the database.
 *
 * @param   string  $key    Unique session ID of the requested entry.
 *
 * @return  string  The requested session data.  A failure condition will
 *                  result in an empty string being returned.
 */
function pgsql_session_read($key)
{
    global $pgsql_session_handle, $pgsql_session_table;

    $key = addslashes($key);
    $now = time();

    /*
     * Attempt to retrieve a row of existing session data.
     *
     * We begin by starting a new transaction.  All of the session-related
     * operations with happen within this transcation.  The transaction will
     * be committed by either session_write() or session_destroy(), depending
     * on which is called.
     *
     * We mark this SELECT statement as FOR UPDATE because it is probable that
     * we will be updating this row later on in session_write(), and performing
     * an exclusive lock on this row for the lifetime of the transaction is
     * desirable.
     */
    $query = "begin; " .
             "select data from $pgsql_session_table " .
             "where session_id = '$key' for update; ";
	$result = pg_query($pgsql_session_handle, $query);

    /*
     * If we were unable to retrieve an existing row of session data, insert a
     * new row.  This ensures that the UPDATE operation in session_write() will
     * succeed.
     */
    if (($result === false) || (pg_num_rows($result) != 1)) {
        $query = "insert into $pgsql_session_table " .
                 "(session_id, last_active, data) " .
                 "values('$key', $now, ''); ";
        //I think this should be error-suppressed...  It might fail, according to later comments....
        $result = @pg_query($pgsql_session_handle, $query);

        /* If the insertion succeeds, return an empty string of data. */
        if (($result !== false) && (pg_affected_rows($result) == 1)) {
            pg_freeresult($result);
            return '';
        }

        /*
         * If the insertion fails, it may be due to a race condition that
         * exists between multiple instances of this session handler in the
         * case where a new session is created by multiple script instances
         * at the same time (as can occur when multiple session-aware frames
         * exist).
         *
         * In this case, we attempt another SELECT operation which will
         * hopefully retrieve the session data inserted by the competing
         * instance.
         */
        $query = "rollback; begin; " .
                 "select data from $pgsql_session_table " .
                 "where session_id = '$key' for update; ";
        $result = pg_query($pgsql_session_handle, $query);

        /* If this attempt also fails, give up and return an empty string. */
        if (($result === false) || (pg_num_rows($result) != 1)) {
            pg_freeresult($result);
            return '';
        }
    }

    /* Extract and return the 'data' value from the successful result. */
    $data = base64_decode(pg_fetch_result($result, 0, 'data'));
    pg_freeresult($result);
	//die("here");
    return $data;
}

/**
 * Writes the provided session data with the requested key to the database.
 *
 * @param   string  $key        Unique session ID of the current entry.
 * @param   string  $val        String containing the session data.
 *
 * @return  boolean True on success, false on failure.
 */
function pgsql_session_write($key, $val)
{
    global $pgsql_session_handle, $pgsql_session_table;

    $key = addslashes($key);
    $val = base64_encode($val);
    $now = time();

    /* Built and execute the update query. */
    $query = "update $pgsql_session_table set last_active=$now, data='$val' " .
             "where session_id='$key';" .
             "commit;";
    //echo_r($query);
    $result = pg_query($pgsql_session_handle, $query);

    $success = ($result !== false);
    pg_freeresult($result);

    return $success;
}

/**
 * Destroys the requested session.
 *
 * @param   string  $key        Unique session ID of the requested entry.
 *
 * @return  boolean True on success, false on failure.
 */
function pgsql_session_destroy($key)
{
    global $pgsql_session_handle, $pgsql_session_table;

    $key = addslashes($key);

    /* Built and execute the deletion query. */
    $query = "delete from $pgsql_session_table where session_id = '$key';" .
             "commit;";
    $result = pg_query($pgsql_session_handle, $query);

    /* A successful deletion query will affect a single row. */
    $success = (($result !== false) && (pg_affected_rows($result) == 1));
    pg_freeresult($result);

    return $success;
}

/**
 * Performs session garbage collection based on the provided lifetime.
 *
 * Sessions that have been inactive longer than $maxlifetime sessions will be
 * deleted.
 *
 * @param   int     $maxlifetime    Maximum lifetime of a session.
 *
 * @return  boolean True on success, false on failure.
 */
function pgsql_session_gc($maxlifetime)
{
    global $pgsql_session_handle, $pgsql_session_table;

    $expiry = time() - $maxlifetime;
    $query = "delete from $pgsql_session_table where last_active < $expiry; ";

    return (pg_query($pgsql_session_handle, $query) !== false);
}

/* Register the session handling functions with PHP. */
session_set_save_handler(
    'pgsql_session_open',
    'pgsql_session_close',
    'pgsql_session_read',
    'pgsql_session_write',
    'pgsql_session_destroy',
    'pgsql_session_gc'
);

?>
