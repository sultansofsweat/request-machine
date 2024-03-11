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
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Visited removed songs page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Obtained setting \"security\"");
	$dateformat=get_setting($sysdb,"dateformat");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Obtained setting \"dateformat\"");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Closed read-only handle to system database");

	$format=map_format_string();
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Obtained system song format mapping");
	
	if(security_check($security) === false)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Not holding administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"login.php?redirect=admin-oldsongs\"</script>");
	}
	
	if(!empty($_GET['clear']) && $_GET['clear'] == "y")
	{
		$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Opened music database in write mode");
		if(permanently_delete_songs($musicdb) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Permanently dumped all deleted songs");
			trigger_error("Successfully dumped deleted songs.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Failed to dump all deleted songs");
			trigger_error("Failed to dump deleted songs. Check yourself before continuing.");
		}
		close_db($musicdb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Closed read-write handle to music database");
	}
	if(!empty($_GET['delete']) && !empty(($id=preg_replace("/[^0-9]/","",$_GET['delete']))))
	{
		$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Opened music database in write mode");
		if(permanently_delete_song($musicdb,$id) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Permanently dumped song \"$id\"");
			trigger_error("Successfully dumped song.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Failed to dump song \"$id\"");
			trigger_error("Failed to dump song. Check yourself before continuing.");
		}
		close_db($musicdb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Closed read-write handle to music database");
	}
	if(!empty($_GET['restoreall']) && $_GET['restoreall'] == "y")
	{
		$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Opened music database in write mode");
		if(restore_deleted_songs($musicdb) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Permanently restored all deleted songs");
			trigger_error("Successfully restored deleted songs.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Failed to restore all deleted songs");
			trigger_error("Failed to restore deleted songs. Check yourself before continuing.");
		}
		close_db($musicdb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Closed read-write handle to music database");
	}
	if(!empty($_GET['restore']) && !empty(($id=preg_replace("/[^0-9]/","",$_GET['restore']))))
	{
		$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Opened music database in write mode");
		if(restore_deleted_song($musicdb,$id) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Permanently restored song \"$id\"");
			trigger_error("Successfully restored song.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Failed to restore song \"$id\"");
			trigger_error("Failed to restore song. Check yourself before continuing.");
		}
		close_db($musicdb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Closed read-write handle to music database");
	}
	
	$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Opened music database in read mode");
	$songs=get_deleted_songs($musicdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Obtained all deleted songs");
	close_db($musicdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Closed read-only handle to music database");
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldsongs.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Request Machine: Dumped Songs</title>
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
		$('#songs').tablesorter({
			widgets        : ['zebra','columns'],
			usNumberFormat : false,
			sortReset      : true,
			sortRestart    : true,
			sortList       : [[4,0]]
		});
	});
	</script>
    
    <!--[if IE]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Request Machine: The Song Dumpster</h1>
	<p><a href="admin-oldsongs.php?clear=y">Empty the dumpster</a> WARNING: this is <b>IMMEDIATE</b> and <b><u>IRREVERSIBLE</u></b>!!!<br>
	<a href="admin-oldsongs.php?restoreall=y">Recycle the dumpster</a> this is also permanent but significantly less dangerous.</p>
	<table id="songs" class="tablesorter-ice">
	<thead>
	<tr>
	<th class="sorter-false"></th>
	<th class="sorter-false"></th>
	<th>List</th>
	<th>Details</th>
	<th>Added</th>
	<th>Count</th>
	<th>Last Requested</th>
	</tr>
	</thead>
	<tbody>
	<?php
		foreach($songs as $song)
		{
			echo("<tr><td><a href=\"admin-oldsongs.php?restore=" . $song->getID() . "\">Recycle</a></td><td><a href=\"admin-oldsongs.php?delete=" . $song->getID() . "\">Dump</a></td><td>" . $song->getList() . "</td><td>" . date($dateformat,$song->getAdded()) . "</td><td>");
			$songstring=array();
			foreach($format as $int=>$ext)
			{
				if(!empty($song->getDetails($int)))
				{
					$songstring[]="$ext: " . $song->getDetails($int);
				}
			}
			echo(implode(", ", $songstring) . "</td><td>" . $song->getCount() . "</td><td>" . date($dateformat,$song->getLastReq()) . "</td></tr>");
		}
	?>
	</tbody>
	</table>
	<p><a href="admin-index.php">Abscond</a>
  </body>
</html>