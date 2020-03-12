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
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Visited banning page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Obtained setting \"security\"");
	$dateformat=get_setting($sysdb,"dateformat");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Obtained setting \"dateformat\"");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Closed read-only handle to system database");
	
	if(security_check($security) === false)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Not holding administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"login.php?redirect=admin-jail\"</script>");
	}
	
	if(!empty($_GET['lift']) && !empty(($id=preg_replace("/[^0-9]/","",$_GET['lift']))))
	{
		$bandb=open_db("db/bans.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Opened bans database in write mode");
		if(lift_uname_ban($bandb,$id) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Lifted ban with ID \"$id\"");
			trigger_error("Successfully lifted username ban.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Failed to lift ban with ID \"$id\"");
			trigger_error("Failed to lift username ban. Check yourself before continuing.");
		}
		close_db($bandb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Closed read-write handle to bans database");
	}
	
	$bandb=open_db("db/bans.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Opened bans database in read mode");
	$userbans=get_all_uname_bans($bandb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Obtained all username bans");
	$ipbans=get_all_ip_bans($bandb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Obtained all IP bans");
	close_db($bandb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Closed read-only handle to bans database");
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-jail.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Music Request System: The Jail</title>
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
		$('#ubans').tablesorter({
			widgets        : ['zebra','columns'],
			usNumberFormat : false,
			sortReset      : true,
			sortRestart    : true,
			sortList       : [[1,0]]
		});
		$('#ibans').tablesorter({
			widgets        : ['zebra','columns'],
			usNumberFormat : false,
			sortReset      : true,
			sortRestart    : true,
			sortList       : [[1,0]]
		});
	});
	</script>
    
    <!--[if IE]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Music Request System: Troll Rehab</h1>
	<h3>Troublemaker Users</h3>
	<table id="ubans" class="tablesorter-ice">
	<thead>
	<tr>
	<th class="sorter-false"><i>Lift Me</i></th>
	<th>Name</th>
	<th>Date</th>
	<th>Until</th>
	<th>Banned For</th>
	</tr>
	</thead>
	<tbody>
	<?php
		foreach($userbans as $ban)
		{
			echo("<tr><td><a href=\"admin-jail.php?lift=" . $ban->getID() . "\">Please!</a></td><td>" . $ban->getItem() . "</td><td>" . date($dateformat,$ban->getDate()) . "</td><td>");
			if($ban->getUntil() == 0)
			{
				echo("Permanent");
			}
			else
			{
				echo(date($dateformat,$ban->getUntil()));
			}
			echo("</td><td>" . $ban->getReason() . "</td></tr>");
		}
	?>
	</tbody>
	</table>
	<h3>Troublemaker IPs</h3>
	<table id="ibans" class="tablesorter-ice">
	<thead>
	<tr>
	<th class="sorter-false"><i>Lift Me</i></th>
	<th>IP</th>
	<th>Date</th>
	<th>Until</th>
	<th>Banned For</th>
	</tr>
	</thead>
	<tbody>
	<?php
		foreach($ipbans as $ban)
		{
			echo("<tr><td><a href=\"admin-jail.php?lift=" . $ban->getID() . "\">Please!</a></td><td>" . $ban->getItem() . "</td><td>" . date($dateformat,$ban->getDate()) . "</td><td>");
			if($ban->getUntil() == 0)
			{
				echo("Permanent");
			}
			else
			{
				echo(date($dateformat,$ban->getUntil()));
			}
			echo("</td><td>" . $ban->getReason() . "</td></tr>");
		}
	?>
	</tbody>
	</table>
	<p><a href="admin-index.php">Abscond</a>
  </body>
</html>