<?php
	require("backend/functions.php");
	if(isset($_GET['step']) && ($step=preg_replace("/[^0-9]/","",$_GET['step'])) != "")
	{
		switch($step)
		{
			case 1:
			$db=open_db("db/system.sqlite",SQLITE3_OPEN_READWRITE);
			insert_setting($db,"testsetting","testvalue1");
			close_db($db);
			break;
			
			case 2:
			$db=open_db("db/system.sqlite",SQLITE3_OPEN_READWRITE);
			update_setting($db,"testsetting","testvalue2");
			close_db($db);
			break;
			
			case 3:
			$db=open_db("db/system.sqlite",SQLITE3_OPEN_READWRITE);
			delete_setting($db,"testsetting");
			close_db($db);
			break;
			
			case 4:
			$db=open_db("db/system.sqlite",SQLITE3_OPEN_READWRITE);
			update_setting($db,"name","Test name");
			echo("<p>PRE-TEST OUTPUT FOR SETTING \"name\": " . get_setting($db,"name") . "</p>\r\n");
			revert_to_previous($db);
			close_db($db);
			break;
			
			case 5:
			$db=open_db("db/system.sqlite",SQLITE3_OPEN_READWRITE);
			update_default($db,"name","Test name");
			close_db($db);
			break;
			
			case 6:
			$db=open_db("db/system.sqlite",SQLITE3_OPEN_READWRITE);
			revert_to_defaults($db);
			close_db($db);
			break;
			
			case 7:
			$db=open_db("db/system.sqlite",SQLITE3_OPEN_READWRITE);
			clear_obsolete($db);
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
	$settings=get_all_settings($db);
	$previous=get_all_previous($db);
	$defaults=get_all_defaults($db);
	$obsolete=get_all_obsolete($db);
	$mirror=get_setting($db,"mirror");
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
	<h1>SETTINGS DATABASE TESTER</h1>
	<h3>Current step:
	<?php
		switch($step)
		{
			case 0:
			echo("Initial database values");
			break;
			
			case 1:
			echo("insert_setting");
			break;
			
			case 2:
			echo("update_setting");
			break;
			
			case 3:
			echo("delete_setting");
			break;
			
			case 4:
			echo("revert_to_previous");
			break;
			
			case 5:
			echo("update_default");
			break;
			
			case 6:
			echo("revert_to_defaults");
			break;
			
			case 7:
			echo("clear_obsolete");
			break;
		}
	?>
	</h3>
	<p>Current settings:</p>
	<?php
		if(count($settings) > 0)
		{
			echo("<p>");
			foreach($settings as $name=>$value)
			{
				echo("<b>$name</b>: $value<br>\r\n");
			}
			echo("</p>\r\n");
		}
		else
		{
			echo("<p>There are no entries to display.</p>\r\n");
		}
	?>
	<p>TEST GET FOR SINGLE SETTING ("mirror"): <?php echo $mirror; ?></p>
	<hr>
	<p>Previous settings:</p>
	<?php
		if(count($previous) > 0)
		{
			echo("<p>");
			foreach($previous as $name=>$value)
			{
				echo("<b>$name</b>: $value<br>\r\n");
			}
			echo("</p>\r\n");
		}
		else
		{
			echo("<p>There are no entries to display.</p>\r\n");
		}
	?>
	<hr>
	<p>Default settings:</p>
	<?php
		if(count($defaults) > 0)
		{
			echo("<p>");
			foreach($defaults as $name=>$value)
			{
				echo("<b>$name</b>: $value<br>\r\n");
			}
			echo("</p>\r\n");
		}
		else
		{
			echo("<p>There are no entries to display.</p>\r\n");
		}
	?>
	<hr>
	<p>Obsolete settings:</p>
	<?php
		if(count($obsolete) > 0)
		{
			echo("<p>");
			foreach($obsolete as $name=>$value)
			{
				echo("<b>$name</b>: $value<br>\r\n");
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
			echo("<input type=\"button\" value=\"Test insert_setting\" onclick=\"window.location.href='test-settings.php?step=1'\"><br>\r\n");
			break;
			
			case 2:
			echo("<input type=\"button\" value=\"Test update_setting\" onclick=\"window.location.href='test-settings.php?step=2'\"><br>\r\n");
			break;
			
			case 3:
			echo("<input type=\"button\" value=\"Test delete_setting\" onclick=\"window.location.href='test-settings.php?step=3'\"><br>\r\n");
			break;
			
			case 4:
			echo("<input type=\"button\" value=\"Test revert_to_previous\" onclick=\"window.location.href='test-settings.php?step=4'\"><br>\r\n");
			break;
			
			case 5:
			echo("<input type=\"button\" value=\"Test update_default\" onclick=\"window.location.href='test-settings.php?step=5'\"><br>\r\n");
			break;
			
			case 6:
			echo("<input type=\"button\" value=\"Test revert_to_defaults\" onclick=\"window.location.href='test-settings.php?step=6'\"><br>\r\n");
			break;
			
			case 7:
			echo("<input type=\"button\" value=\"Test clear_obsolete\" onclick=\"window.location.href='test-settings.php?step=7'\"><br>\r\n");
			break;
		}
	?><br>
	<input type="button" value="Reset to defaults" onclick="window.location.href='test-settings.php'"></p>
  </body>
</html>