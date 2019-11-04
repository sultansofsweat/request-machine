<?php
	require("backend/functions.php");
	if(isset($_GET['step']) && ($step=preg_replace("/[^0-9]/","",$_GET['step'])) != "")
	{
		switch($step)
		{
			case 1:
			$db=open_db("db/system.sqlite",SQLITE3_OPEN_READWRITE);
			insert_version_info($db,20190630171001,3,0,2,"","Not released");
			close_db($db);
			break;
		}
	}
	else
	{
		copy("db/system-base.sqlite","db/system.sqlite");
		$step=0;
	}
	$db=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	$current=get_current_version($db);
	$history=get_version_history($db);
	$codes=get_build_codes($db);
	close_db($db);
	unset($db);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="generator" content="CoffeeCup HTML Editor (www.coffeecup.com)">
    <meta name="dcterms.created" content="Sun, 14 Jan 2018 04:33:28 GMT">
    <meta name="description" content="Microwave ovens.">
    <meta name="keywords" content="music, request, system, microwave, oven, russians, gpx, spoooooorts">
	<link rel="shortcut icon" href="backend/favicon.ico">
    <title>Music Request System: V3 Engine Demo</title>
	<style type="text/css">
    <!--
    body {
      color:#000000;
	  background-color:#FFFFFF;
      background-image:url('backend/background.gif');
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
	<h1>VERSION DATABASE TESTER</h1>
	<h3>Current step:
	<?php
		switch($step)
		{
			case 0:
			echo("Initial database values");
			break;
			
			case 1:
			echo("insert_version_info");
			break;
		}
	?>
	</h3>
	<p>Current version:</p>
	<?php
		echo("<p>" . $current['major'] . "." . $current['minor'] . $current['tag'] . ", revision " . $current['revision'] . "<br>\r\n
		Build code " . $current['build'] . ", released " . $current['released'] . "</p>\r\n");
	?>
	<hr>
	<p>Version history:</p>
	<?php
		if(count($history) > 0)
		{
			foreach($history as $version)
			{
				echo("<p>" . $version['major'] . "." . $version['minor'] . $version['tag'] . ", revision " . $version['revision'] . "<br>\r\n
				Build code " . $version['build'] . ", released " . $version['released'] . "</p>\r\n");
			}
		}
		else
		{
			echo("<p>There are no entries to display.</p>\r\n");
		}
	?>
	<hr>
	<p>Build codes:</p>
	<?php
		if(count($codes) > 0)
		{
			echo("<p>");
			foreach($codes as $code)
			{
				echo("$code<br>\r\n");
			}
			echo("</p>\r\n");
		}
		else
		{
			echo("<p>There are no entries to display.</p>\r\n");
		}
	?>
	<hr>
	<p><?php
		$step++;
		switch($step)
		{
			case 1:
			echo("<input type=\"button\" value=\"Test insert_version_info\" onclick=\"window.location.href='test-version.php?step=1'\"><br>\r\n");
			break;
		}
	?><br>
	<input type="button" value="Reset to defaults" onclick="window.location.href='test-version.php'"></p>
  </body>
</html>