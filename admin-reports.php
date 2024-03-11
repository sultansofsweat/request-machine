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
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Visited reports page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Obtained setting \"security\"");
	$dateformat=get_setting($sysdb,"dateformat");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Obtained setting \"dateformat\"");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Closed read-only handle to system database");
	
	if(security_check($security) === false)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Not holding administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"login.php?redirect=admin-reports\"</script>");
	}
	
	if(!empty($_GET['read']) && !empty(($id=preg_replace("/[^0-9]/","",$_GET['read']))))
	{
		$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Opened music database in write mode");
		if(mark_report_as_viewed($musicdb,$id) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Marked report \"$id\" as read");
			trigger_error("Successfully marked report as read.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Failed to mark report \"$id\" as read");
			trigger_error("Failed to mark report as read. Check yourself before continuing.");
		}
		close_db($musicdb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Closed read-write handle to music database");
	}
	
	if(!empty($_GET['delete']) && !empty(($id=preg_replace("/[^0-9]/","",$_GET['delete']))))
	{
		$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Opened music database in write mode");
		if(delete_report($musicdb,$id) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Deleted report \"$id\"");
			trigger_error("Successfully deleted report.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Failed to delete report \"$id\"");
			trigger_error("Failed to delete report. Check yourself before continuing.");
		}
		close_db($musicdb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Closed read-write handle to music database");
	}
	
	$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Opened music database in read mode");
	$unread=get_unread_reports($musicdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Obtained all unread reports");
	$read=array_diff(get_reports($musicdb),$unread);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Obtained all other reports");
	close_db($musicdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Closed read-only handle to music database");
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-reports.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Request Machine: Troublemakers</title>
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
		$('#reports').tablesorter({
			widgets        : ['zebra','columns'],
			usNumberFormat : false,
			sortReset      : true,
			sortRestart    : true,
			sortList       : [[6,0]]
		});
	});
	</script>
    
    <!--[if IE]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Request Machine: Problematic Posts</h1>
	<form method="get" action="admin-reports.php">
	<input type="checkbox" name="showread" value="n" <?php if(!empty($_GET['showread'])) { echo("checked=\"checked\""); } ?>> Only show unread reports <input type="submit">
	</form>
	<table id="reports" class="tablesorter-ice">
	<thead>
	<tr>
	<th class="sorter-false"></th>
	<th class="sorter-false"></th>
	<th class="sorter-false"></th>
	<th>IP</th>
	<th>Request ID</th>
	<th>Reason</th>
	<th>State</th>
	</tr>
	</thead>
	<tbody>
	<?php
		foreach($unread as $report)
		{
			echo("<tr><td><a href=\"admin-repdet.php?id=" . $report->getID() . "\">Get more details</a></td><td><a href=\"admin-reports.php?read=" . $report->getID() . "\">Mark as read</a></td><td><a href=\"admin-reports.php?delete=" . $report->getID() . "\">Dump this report</a></td><td>" . $report->getIP() . "</td><td>" . $report->getRequest() . "</td><td>" . $report->getReason() . "</td><td>" . $report->getUnread() . "</td></tr>");
		}
		if(empty($_GET['showread']) || $_GET['showread'] != "n")
		{
			foreach($read as $report)
			{
				echo("<tr><td><a href=\"admin-repdet.php?id=" . $report->getID() . "\">Get more details</a></td><td><a href=\"admin-reports.php?read=" . $report->getID() . "\">Mark as read</a></td><td><a href=\"admin-reports.php?delete=" . $report->getID() . "\">Dump this report</a></td><td>" . $report->getIP() . "</td><td>" . $report->getRequest() . "</td><td>" . $report->getReason() . "</td><td>" . $report->getUnread() . "</td></tr>");
			}
		}
	?>
	</tbody>
	</table>
	<p><a href="admin-index.php">Abscond</a>
  </body>
</html>