<?php
	/* ORDER OF OPERATIONS
	-Require core
	-Open session
	-Open read-write connection to logging database
	-If not signed in, redirect to login page
	-Open read-only connection to system database
	-Get required settings
	-Close system database
	-Close logging database
	*/
	
	include_once("backend/errorhandler.php");
	require_once("backend/objects.php");
	require_once("backend/functions.php");
	
	if(alt_sess_store() !== false)
	{
		session_save_path(alt_sess_store());
	}
	session_start();
	
	$logdb=open_db("db/logs.sqlite",SQLITE3_OPEN_READWRITE);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-index.php","Visited control panel main page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-index.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-index.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-index.php","Obtained setting \"security\"");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-index.php","Closed read-only handle to system database");
	
	if(security_check($security) === false)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-index.php","Not holding administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-index.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"login.php?redirect=admin-index\"</script>");
	}
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-index.php","Closing read-write handle to logging database, last log message from this page");
	close_db($logdb);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="generator" content="CoffeeCup HTML Editor (www.coffeecup.com)">
    <meta name="dcterms.created" content="Sun, 14 Jan 2018 04:33:28 GMT">
    <meta name="description" content="Microwave ovens.">
    <meta name="keywords" content="music, request, system, microwave, oven, russians, gpx, spoooooorts">
	<link rel="shortcut icon" href="favicon/active.ico">
    <title><?php echo $name; ?> Music Request System: Central Control</title>
	<style type="text/css">
    <!--
    body {
      color:#000000;
	  background-color:#FFFFFF;
      background-image:url('backgrounds/active.gif');
      background-repeat:repeat;
    }
    a  { color:#FFFFFF; background-color:#0000FF; }
    a:visited { color:#FFFFFF; background-color:#800080; }
    a:hover { color:#000000; background-color:#00FF00; }
    a:active { color:#000000; background-color:#FF0000; }
    -->
    </style>
    
    <!--[if IE]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
	<?php
		if(!empty($_GET['success']))
		{
			trigger_error("Successfully changed settings.");
		}
		if(!empty($_GET['failure']))
		{
			trigger_error("Failed to change settings.",E_USER_ERROR);
		}
	?>
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Music Request System: Control Center</h1>
	<p><a href="admin-dump.php">Show All System Settings</a><br>
	<a href="admin-resprev.php">Reset <b>All</b> To Previous Values</a><br>
	<a href="admin-resdef.php">Reset <b>All</b> To System Defaults</a><br>
	<a href="admin-optimize.php">Optimize System Databases</a></p>
	<p><a href="admin-passwd.php">System Security</a><br>
	<a href="admin-sys.php">System Information</a><br>
	<a href="admin-bkg.php">System Background</a><br>
	<a href="admin-ico.php">System Icon</a><br>
	<a href="admin-new.php">System Flag</a><br>
	<a href="admin-logsess.php">Logging and Sessions</a><br>
	<a href="admin-copyright.php">Copyright Information</a><br>
	<a href="admin-home.php">Homepage Options</a><br>
	<a href="admin-search.php">Searching Options</a><br>
	<a href="admin-songs.php">Song List Options</a><br>
	<a href="admin-req.php">Requesting Options</a><br>
	<a href="admin-ban.php">Banhammer&trade; Options</a><br>
	<a href="admin-api.php">System API Options</a><br>
	<a href="admin-upg.php">System Update Options</a><br>
	<a href="update-index.php">System Updater</a></p>
	<p><a href="admin-jail.php">The Jail</a><br>
	<a href="admin-reports.php">Troublemakers' Requests</a><br>
	<a href="admin-maps.php">User Request Statistics</a><br>
	<a href="admin-oldsongs.php">Deleted Song Entries</a><br>
	<a href="admin-oldreqs.php">Deleted Requests</a><br>
	<a href="admin-depset.php">Deprecated Settings</a><br>
	<a href="admin-syslog.php">System Log</a><br>
	<a href="admin-errlog.php">Error Log</a><br>
	<a href="admin-inlog.php">Login Attempts</a><br>
	<a href="admin-ver.php">System Version History</a><br>
	</p>
	<p><a href="index.php">Abscond</a>
  </body>
</html>