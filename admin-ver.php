<?php
	include_once("backend/errorhandler.php");
	require_once("backend/objects.php");
	require_once("backend/functions.php");
	
	if(alt_sess_store() !== false)
	{
		session_save_path(alt_sess_store());
	}
	session_start();
	
	$logdb=open_db("db/logs.sqlite",SQLITE3_OPEN_READWRITE);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Visited deprecated settings page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Obtained setting \"security\"");
	$dateformat=get_setting($sysdb,"dateformat");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Obtained setting \"dateformat\"");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Closed read-only handle to system database");
	
	if(security_check($security) === false)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Not holding administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"login.php?redirect=admin-ver\"</script>");
	}
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Opened system database in read mode");
	$versions=get_version_history($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Obtained all version information entries");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Closed read-only handle to system database");
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-ver.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Request Machine: Version History</title>
	<script type="text/javascript" src="backend/jquery.js"></script>
	<script type="text/javascript" src="backend/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="backend/jquery.tablesorter.widgets.js"></script>
	<link rel="stylesheet" href="backend/theme.ice.css">
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
	<script>
	$(function(){
		$('#stats').tablesorter({
			widgets        : ['zebra','columns'],
			usNumberFormat : false,
			sortReset      : true,
			sortRestart    : true,
			sortList       : [[0,1]]
		});
	});
	</script>
    
    <!--[if IE]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Request Machine: Version History</h1>
	<table id="stats" class="tablesorter-ice">
	<thead>
	<tr>
	<th>Build Code</th>
	<th>Version</th>
	<th>Revision</th>
	<th>Released</th>
	<th>Installed</th>
	</tr>
	</thead>
	<tbody>
	<?php
		foreach($versions as $version)
		{
			echo("<tr><td>" . $version->getBuildCode() . "</td><td>" . $version->getMajor() . "." . $version->getMinor() . $version->getTag() . "</td><td>" . $version->getRevision() . "</td><td>" . $version->getRelease() . "</td><td>" . date($dateformat,strtotime($version->getInstalled())) . "</td></tr>");
		}
	?>
	</tbody>
	</table>
	<p><a href="admin-index.php">Abscond</a>
  </body>
</html>