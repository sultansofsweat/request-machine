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
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Visited settings dump page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Obtained setting \"security\"");
	$current=get_all_settings($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Obtained list of all current settings");
	$previous=get_all_previous($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Obtained list of all previous settings");
	$defaults=get_all_defaults($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Obtained list of all settings defaults");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Closed read-only handle to system database");
	
	$settings=array();
	foreach($current as $setting=>$value)
	{
		if($setting != "passwd" && $setting != "apipass" && $setting != "subpass")
		{
			$settings[$setting]=new Setting($setting,$value);
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
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Not holding administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"login.php?redirect=admin-resdef\"</script>");
	}
	
	if(!empty($_POST['setting']) && count($_POST['setting']) > 0)
	{
		$result=array(true,0);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Beginning reset of system settings to default values");
		$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Opened system database in write mode");
		if(in_array("SELECT_ALL",$_POST['setting']))
		{
			foreach($settings as $setting)
			{
				if($setting->getDefault() != $setting->getCurrent())
				{
					$debug=update_setting($sysdb,$setting->getName(),$setting->getDefault());
					if($debug === true)
					{
						insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Changed value of setting \"" . $setting->getName() . "\" from \"" . $setting->getCurrent() . "\" to \"" . $setting->getDefault() . "\"");
					}
					else
					{
						insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Failed to change value of setting \"" . $setting->getName() . "\" from \"" . $setting->getCurrent() . "\" to \"" . $setting->getDefault() . "\"");
						$result[1]++;
					}
				}
				else
				{
					insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Default value for setting \"" . $setting->getName() . "\" same as current, ignoring");
				}
			}
		}
		else
		{
			foreach($_POST['setting'] as $setting)
			{
				if(!empty($settings[$setting]))
				{
					if($settings[$setting]->getDefault() != $settings[$setting]->getCurrent())
					{
						$debug=update_setting($sysdb,$settings[$setting]->getName(),$settings[$setting]->getDefault());
						if($debug === true)
						{
							insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Changed value of setting \"" . $settings[$setting]->getName() . "\" from \"" . $settings[$setting]->getCurrent() . "\" to \"" . $settings[$setting]->getDefault() . "\"");
						}
						else
						{
							insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Failed to change value of setting \"" . $settings[$setting]->getName() . "\" from \"" . $settings[$setting]->getCurrent() . "\" to \"" . $settings[$setting]->getDefault() . "\"");
							$result[1]++;
						}
					}
					else
					{
						insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Default value for setting \"" . $settings[$setting]->getName() . "\" same as current, ignoring");
					}
				}
			}
		}
		close_db($sysdb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Closed read-write handle to system database");
	}
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-resdef.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Request Machine: Reset To Defaults</title>
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
				trigger_error("Completed settings reset with errors. Count of errors is \"" . $result[1] . "\". Throwing a GPX item at the server might solve the problem.",E_USER_WARNING);
			}
			else
			{
				trigger_error("Completed settings reset without errors.");
			}
		}
	?>
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Request Machine: Reset Settings to Default Values</h1>
	<p><b>IMPORTANT:</b> this action is at least semi-permanent! Make sure this is what you actually want to do, otherwise you may risk being buried in Furhead socks.</p>
	<form method="post" action="admin-resdef.php">
	<input type="submit" value="Reset settings"><input type="reset" value="De-select All"><br>
	<table id="settings" class="tablesorter-ice">
	<thead>
	<tr>
	<th class="sorter-false"><i>Select</i></th>
	<th>Name</th>
	<th>Current Value</th>
	<th>New Value</th>
	</tr>
	</thead>
	<tbody>
	<tr><td><input type="checkbox" name="setting[]" value="SELECT_ALL"></td><td colspan="3">Reset all settings to default values</td>
	<?php
		foreach($settings as $setting)
		{
			echo("<tr><td><input type=\"checkbox\" name=\"setting[]\" value=\"" . $setting->getName() . "\"></td><td>" . $setting->getName() . "</td><td>" . $setting->getCurrent() . "</td><td>" . $setting->getDefault() . "</td></tr>");
		}
	?>
	</tbody>
	</table>
	</form>
	<p><a href="admin-index.php">Abscond</a>
  </body>
</html>