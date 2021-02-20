<?php
	/* ORDER OF OPERATIONS
	-Require object core
	-Require function core
	-Include error handler
	-Open session
	-Open read-write connection to logging database
	-Open read-only connection to system database
	-Get required settings
	-Get version information
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
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Visited about page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Obtained setting \"name\"");
	$copyinfo=get_setting($sysdb,"copyinfo");
	if(empty($copyinfo))
	{
		$copyinfo="<p>No copyright information pertaining to this particular MRS was provided by the BOFH. You should talk to them if you want to know more about its purpose.</p>";
	}
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Obtained setting \"copyinfo\"");
	switch(get_setting($sysdb,"syslog"))
	{
		case "y":
		$syslog="Yes";
		break;
		
		case "n":
		$syslog="No";
		break;
		
		default:
		$syslog="Indeterminate";
		break;
	}
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Obtained setting \"syslog\"");
	switch(get_setting($sysdb,"errlog"))
	{
		case "y":
		$errlog="Yes";
		break;
		
		case "n":
		$errlog="No";
		break;
		
		default:
		$errlog="Indeterminate";
		break;
	}
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Obtained setting \"errlog\"");
	switch(get_setting($sysdb,"inlog"))
	{
		case "y":
		$inlog="Yes";
		break;
		
		case "n":
		$inlog="No";
		break;
		
		default:
		$inlog="Indeterminate";
		break;
	}
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Obtained setting \"inlog\"");
	$banhammer=get_setting($sysdb,"autoban");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Obtained setting \"autoban\"");
	$superban=get_setting($sysdb,"superban");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Obtained setting \"superban\"");
	$version=get_current_version($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Obtained build information for currently installed build");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Closed read-only handle to system database");
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"about.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Music Request System: About This MRS</title>
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
	<h1 style="text-align:center; text-decoration:underline;">About The <?php echo $name; ?> Music Request System</h1>
	<h3>MRS Install Version</h3>
	<p><b>Version: <?php echo $version->getMajor() . "." . $version->getMinor() . $version->getTag(); ?><br>
	Revision: <?php echo $version->getRevision(); ?><br>
	Build code: <?php echo $version->getBuildCode(); ?><br>
	Released: <?php echo $version->getRelease(); ?></b><br>
	Installed: <?php echo $version->getInstalled(); ?></b></p>
	<p><u>System Logging Settings</u><br>
	Detailed operational messages: <?php echo $syslog; ?><br>
	System error messages: <?php echo $errlog; ?><br>
	Login attempts: <?php echo $inlog; ?></p>
	<?php
		if($banhammer == "y" && $superban == "y")
		{
			echo("<p>This MRS is equipped with the Super Banhammer&trade;. Ask the BOFH about it.</p>\r\n");
		}
		elseif($banhammer == "y" && $superban == "n")
		{
			echo("<p>This MRS is equipped with the Banhammer&trade;. Ask the BOFH about it.</p>\r\n");
		}
		elseif($banhammer == "n" && $superban == "y")
		{
			echo("<p>This MRS is equipped with enhanced banning capabilities. Ask the BOFH about them.</p>\r\n");
		}
	?>
	<hr>
	<h3>Music Request System Copyright Information</h3>
	<p>The Music Request System (MRS) is copyright &copy; 2015-2021 Brad Hunter/<a href="http://www.youtube.com/user/carnelprod666" target="_blank">CarnelProd666</a>. All code pertaining to the MRS itself (and ONLY the MRS!) is licensed under the <a href="license.txt" target="_blank">DBAD Public License</a>, version 1.1. Learn more about the MRS <a href="http://firealarms.mooo.com/mrs" target="_blank">here</a>. Comments should be directed to the system administrator/BOFH and/or <a href="http://github.com/sultansofsweat" target="_blank">the software vendor</a>.</p>
	<p>The MRS makes use of <a href="https://sqlite.org" target="_blank">SQLite</a>, <a href="http://jquery.com/" target="_blank">JQuery</a> and the <a href="https://mottie.github.io/tablesorter/docs/" target="_blank">TableSorter</a> plugin, each of which is copyright their respective owners.<br>
	For systems running on non-compliant PHP versions, the MRS makes use of <a href="https://github.com/ircmaxell/password_compat/" target="_blank">password_compat</a>, produced by <a href="https://github.com/ircmaxell/" target="_blank">ircmaxell</a> and licensed under the <a href="https://github.com/ircmaxell/password_compat/blob/master/LICENSE.md" target="_blank">MIT license</a>.</p>
	<hr>
	<h3><?php $name; ?> Copyright Information</h3>
	<?php echo $copyinfo; ?>
	<hr>
	<p><a href="index.php">Abscond</a></p>
  </body>
</html>