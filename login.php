<?php
	/* ORDER OF OPERATIONS
	-Require function core
	-Include error handler
	-Open session
	-Set up redirect information
	-If signed in, redirect out
	-Open read-write connection to logging database
	-Open read-only connection to system database
	-Get required settings
	-Close system database
	-Open read-only connection to bans database
	-Check for active bans against username or IP address
	-Close bans database
	-Close logging database
	-If banned, redirect out with a message that the user was banned
	-If password submitted, check it and perform login operations
	*/
	
	include_once("backend/errorhandler.php");
	require_once("backend/objects.php");
	require_once("backend/functions.php");
	
	if(alt_sess_store() !== false)
	{
		session_save_path(alt_sess_store());
	}
	session_start();
	
	if(!empty($_GET['redirect']) && file_exists($_GET['redirect'] . ".php"))
	{
		$redirect=$_GET['redirect'] . ".php";
	}
	else
	{
		$redirect="";
	}
	
	$logdb=open_db("db/logs.sqlite",SQLITE3_OPEN_READWRITE);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Visited login page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Obtained setting \"security\"");
	$passwd=get_setting($sysdb,"passwd");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Obtained setting \"passwd\"");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Closed read-only handle to system database");
	
	if(security_check($security) === true)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Already holding administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"index.php\"</script>");
	}
	
	$bans=array();
	$bandb=open_db("db/bans.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Opened bans database in read mode");
	if(!empty($_SESSION['username']))
	{
		$bans=array_merge(get_all_active_bans_for_ip($bandb,$_SERVER['REMOTE_ADDR']),get_all_active_bans_for_uname($bandb,$_SESSION['username']));
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Obtained active username and IP bans");
	}
	else
	{
		$bans=get_all_active_bans_for_ip($bandb,$_SERVER['REMOTE_ADDR']);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Obtained active IP bans");
	}
	close_db($bandb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Closed read-only handle to bans database");
	
	if(count($bans) > 0)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Login prevented by ongoing ban.");
		banhammer("attempted hijack of system",true);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"index.php?in=banned\"</script>");
	}
	
	if(!empty($_POST['password']))
	{
		if(!empty($_POST['redirect']) && file_exists($_POST['redirect']))
		{
			$redirect=$_POST['redirect'];
		}
		else
		{
			$redirect="index.php?in=yes";
		}
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Begin processing password");
		$success=password_verify($_POST['password'],$passwd);
		if($success === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Password successfully verified, escalating to admin privileges");
			$_SESSION['mrsadmin']="y";
			$_SESSION['mrsip']=$_SERVER['REMOTE_ADDR'];
			$_SESSION['mrsua']=$_SERVER['HTTP_USER_AGENT'];
			$_SESSION['mrsid']=base64_encode($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . getcwd());
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Closing read-write handle to logging database, last log message from this page");
			close_db($logdb);
			die("<script type=\"text/javascript\">window.location = \"$redirect\"</script>");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Password does not match");
			banhammer("repeated invalid logins",false,true);
			$failed=true;
		}
	}
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"login.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Music Request System: Log in</title>
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
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Music Request System: Log In</h1>
	<form action="login.php" method="post">
	<input type="hidden" name="redirect" value="<?php echo $redirect; ?>">
	Enter the administrative password: <input type="password" name="password"><br>
	<input type="submit" value="Log in"><input type="button" value="Cancel" onclick="window.location.href='index.php'">
	</form>
  </body>
</html>