<?php
	/* ORDER OF OPERATIONS
	-Require function core
	-Include error handler
	-Open session
	-Open read-write connection to logging database
	-Open read-only connection to system database
	-Get required settings
	-Close system database
	-If signed out, redirect out
	-If submission sent, perform log out operations
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
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"logout.php","Visited logout page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"logout.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"logout.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"logout.php","Obtained setting \"security\"");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"logout.php","Closed read-only handle to system database");
	
	if(security_check($security) === false)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"logout.php","User does not have existing administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"logout.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"index.php\"</script>");
	}
	
	if(!empty($_POST['submit']))
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"logout.php","Dropping administrative privileges");
		$_SESSION['mrsadmin']="n";
		$_SESSION['mrsip']="";
		$_SESSION['mrsua']="";
		$_SESSION['mrsid']="";
		unset($_SESSION['mrsadmin'],$_SESSION['mrsip'],$_SESSION['mrsua'],$_SESSION['mrsid']);
		session_destroy();
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"logout.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"index.php?out=yes\"</script>");
	}
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"logout.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Request Machine: Log out</title>
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
		if(!empty($failed))
		{
			trigger_error("The specified password was incorrect.",E_USER_WARNING);
		}
	?>
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Request Machine: Log Out</h1>
	<form action="logout.php" method="post">
	Are you sure you want to log out?<br>
	<input type="hidden" name="submit" value="yes">
	<input type="submit" value="Yes"><input type="button" value="No" onclick="window.location.href='index.php'">
	</form>
  </body>
</html>