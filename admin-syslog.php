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
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Visited system log viewing page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Obtained setting \"security\"");
	$dateformat=get_setting($sysdb,"dateformat");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Obtained setting \"dateformat\"");
	$syslog=get_setting($sysdb,"syslog");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Obtained setting \"syslog\"");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Closed read-only handle to system database");
	
	if(security_check($security) === false)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Not holding administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"login.php?redirect=admin-syslog\"</script>");
	}
	
	if(!empty($_GET['clear']) && $_GET['clear'] == "y")
	{
		if(clear_system_log($logdb) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Cleared system log");
			trigger_error("Successfully erased system logs.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Failed to clear system log");
			trigger_error("Failed to erase system logs. Check yourself before continuing.");
		}
	}
	if(!empty($_GET['read']) && $_GET['read'] == "y")
	{
		if(mark_system_log_as_read($logdb) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Marked system log as read");
			trigger_error("Successfully read system logs.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Failed to mark system log as read");
			trigger_error("Failed to read system logs. Check yourself before continuing.");
		}
	}
	
	$logs=get_system_logs($logdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Obtained all log entries");
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-syslog.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Request Machine: System Log</title>
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
			sortList	   : [[4,0]]
		});
	});
	</script>
    
    <!--[if IE]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Request Machine: System Messages</h1>
	<p>Current state of message logging:&nbsp;
	<?php
		switch($syslog)
		{
			case "y":
			echo("<b>ENABLED</b>");
			break;
			
			case "n":
			echo("<b>DISABLED</b>");
			break;
			
			default:
			echo("<b>INDETERMINATE</b>");
			break;
		}
	?></p>
	<p><a href="admin-syslog.php?clear=y">Clear log</a> WARNING: this is <b>IMMEDIATE</b> and <b><u>IRREVERSIBLE</u></b>!!!<br>
	<a href="admin-syslog.php?read=y">Mark log as read</a></p>
	<table id="stats" class="tablesorter-ice">
	<thead>
	<tr>
	<th>IP</th>
	<th>Time</th>
	<th>Page</th>
	<th class="sorter-false">Message</th>
	<th>U/R</th>
	</tr>
	</thead>
	<tbody>
	<?php
		foreach($logs as $log)
		{
			echo("<tr><td>" . $log->getIP() . "</td><td>" . date($dateformat,$log->getTime()) . "</td><td>" . $log->getPage() . "</td><td>" . $log->getText() . "</td><td>" . $log->getUnread() . "</td></tr>");
		}
	?>
	</tbody>
	</table>
	<p><a href="admin-index.php">Abscond</a>
  </body>
</html>