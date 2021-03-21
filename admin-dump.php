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
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Visited settings dump page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Obtained setting \"security\"");
	$current=get_all_settings($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Obtained list of all current settings");
	$descriptions=get_all_descriptions($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Obtained list of all setting descriptions");
	$previous=get_all_previous($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Obtained list of all previous settings");
	$defaults=get_all_defaults($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Obtained list of all settings defaults");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Closed read-only handle to system database");
	
	$settings=array();
	foreach($current as $setting=>$value)
	{
		if($setting != "passwd" && $setting != "apipass" && $setting != "subpass")
		{
			$description="";
			if(isset($descriptions[$setting]))
			{
				$description=$descriptions[$setting];
			}
			$settings[$setting]=new Setting($setting,$description,$value);
		}
	}
	foreach($previous as $setting=>$value)
	{
		if($setting != "passwd" && $setting != "apipass" && $setting != "subpass")
		{
			$settings[$setting]->setPrevious($value);
		}
	}
	foreach($defaults as $setting=>$value)
	{
		if($setting != "passwd" && $setting != "apipass" && $setting != "subpass")
		{
			$settings[$setting]->setDefault($value);
		}
	}
	
	if(security_check($security) === false)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Not holding administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"login.php?redirect=admin-dump\"</script>");
	}
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-dump.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Music Request System: Settings Dump</title>
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
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Music Request System: List Of All Settings</h1>
	<table id="settings" class="tablesorter-ice">
	<thead>
	<tr>
	<th>Name</th>
	<th>Description</th>
	<th>Current</th>
	<th>Previous</th>
	<th>Default</th>
	</tr>
	</thead>
	<tbody>
	<?php
		foreach($settings as $setting)
		{
			echo("<tr><td>" . $setting->getName() . "</td><td>" . $setting->getDescription() . "</td><td>" . $setting->getCurrent() . "</td><td>" . $setting->getPrevious() . "</td><td>" . $setting->getDefault() . "</td></tr>");
		}
	?>
	</tbody>
	</table>
	<p><a href="admin-index.php">Abscond</a>
  </body>
</html>