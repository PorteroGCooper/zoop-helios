<?

////////////////////////////////////////////////////
//              Session Options               	  //
////////////////////////////////////////////////////

	if(app_status == "desktop") 				//or whatever
		define("session_type", "files");
	else
	{
		define("session_type", "files");		//	should be files or pgsql
		define("session_separate", 0);
		define("session_path", "server"); // can be server or app.
		/* THIS WILL SEPERATE THE SESSIONS BY APPLICATION OR SHARE ONE SESSION ACROSS THE SERVER. */

		/* Define these if you have your sessions in a separate database from your main data

		define("session_server", fwDB_Server);
		define("session_port", fwDB_Port);
		define("session_database", fwDB_Database);
		define("session_username", fwDB_Username);
		define("session_password", fwDB_Password);
		*/

		/* For table information see the sessions directory inside of zoop */
	}


?>
