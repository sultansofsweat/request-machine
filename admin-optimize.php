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
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Visited database optimize page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Obtained setting \"security\"");
	$sizes=array();
	$dbs=glob("db/*.sqlite");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Obtained list of databases");
	foreach($dbs as $db)
	{
		$sizes[basename($db)]=array(filesize($db),filesize($db));
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Obtained file size of database \"" . basename($db) . "\"");
	}
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Closed read-only handle to system database");
	
	if(security_check($security) === false)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Not holding administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"login.php?redirect=admin-resdef\"</script>");
	}
	
	if(!empty($_GET['s']) && $_GET['s'] == "y")
	{
		$result=array(true,0);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Beginning optimization of system databases");
		$dbs=glob("db/*.sqlite");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Obtained list of databases");
		foreach($dbs as $db)
		{
			$debug=optimize_db($db);
			if(isset($debug[0]) && isset($debug[2]))
			{
				$sizes[basename($db)][1]=$debug[2];
				insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Successfully optimized database \"" . basename($db) . "\"");
			}
			else
			{
				$result[1]++;
				insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Failed to optimize database \"" . basename($db) . "\"");
			}
		}
	}
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-optimize.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Request Machine: Optimize Database</title>
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
		$('#settings').tablesorter({
			widgets        : ['zebra','columns'],
			usNumberFormat : false,
			sortReset      : true,
			sortRestart    : true,
			sortList       : [[0,0]]
		});
	});
	</script>
    
    <!--[if IE]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
    <?php
		if(!empty($result) && isset($result[1]))
		{
			if($result[1] > 0)
			{
				trigger_error("Completed database optimization with errors. Count of errors is \"" . $result[1] . "\". Throwing a GPX item at the server might solve the problem.",E_USER_WARNING);
			}
			else
			{
				trigger_error("Completed database optimization without errors.");
			}
		}
	?>
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Request Machine: Optimize Database</h1>
	<p>This will optimize and reduce the file sizes of the system databases. It is <b>permanent</b> but likely won't summon any Furdon gas even if it goes wrong.</p>
	<p>File sizes before optimization:<br>
	<?php
		foreach($sizes as $db=>$size)
		{
			echo("$db: " . ($size[0]/1024) . "KB<br>\r\n");
		}
	?></p>
	<p>File sizes after optimization:<br>
	<?php
		foreach($sizes as $db=>$size)
		{
			echo("$db: " . ($size[1]/1024) . "KB (difference of " . ($size[0]-$size[1]) . " bytes)<br>\r\n");
		}
	?></p>
	<p><a href="admin-optimize.php?s=y">Optimize Me</a> | <a href="admin-index.php">Abscond</a>
  </body>
</html>