<?php
	//This file contains the MRS error handler, which is used to replace the built-in that PHP uses
	
	require_once("backend/functions.php");
	
	function eh($errno, $errstr, $errfile, $errline)
	{
		$db=open_db("db/logs.sqlite",SQLITE3_OPEN_READWRITE);
		insert_error_log($db,$_SERVER['REMOTE_ADDR'],time(),basename($errfile) . ":$errline",$errno,$errstr);
		close_db($db);
		switch ($errno)
		{
			case E_ERROR:
			case E_COMPILE_ERROR:
			case E_CORE_ERROR:
			echo "<p><b><u>ERROR:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "<br>
			This is a fatal error, stopping execution. Threaten a thousand camels upon the server.</p>\n";
			exit(1);
			break;
			
			case E_USER_ERROR:
			echo "<p><b><u>ERROR:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "</p>\n";
			break;
			
			case E_WARNING:
			case E_USER_WARNING:
			echo "<p><b><u>WARNING:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "</p>\n";
			break;
			
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			echo "<p><b><u>SYSTEM WARNING:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "<br>\n
			This is probably a problem. Continuing anyways, expect severe breakage.</p>\n";
			break;
			
			case E_DEPRECATED:
			case E_USER_DEPRECATED:
			echo "<p><b><u>DEPRECATION NOTICE:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "</p>\n";
			break;

			case E_NOTICE:
			case E_USER_NOTICE:
			echo "<p><b><u>NOTICE:</u></b> " . $errstr . "<br>\n
			Located on line $errline of " . basename($errfile) . "</p>\n";
			break;

			default:
			echo "<p>Unidentified error <b><u>[$errno]</u></b>: $errstr<br>\n
			Located on line $errline of " . basename($errfile) . "</p>\n";
			break;
    	}

    	/* Don't execute PHP internal error handler */
    	return true;
	}
	
	//Shutdown function
	function sh()
	{
		$last_error = error_get_last();
		if(!empty($last_error['type']))
		{
			eh($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}
	
	//Set error handler to the custom one
	$oeh=set_error_handler("eh");
	register_shutdown_function("sh");
?>