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
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Visited removed requests page");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Obtained setting \"name\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Obtained setting \"security\"");
	$dateformat=get_setting($sysdb,"dateformat");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Obtained setting \"dateformat\"");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Closed read-only handle to system database");

	$format=map_format_string();
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Obtained system song format mapping");
	
	if(security_check($security) === false)
	{
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Not holding administrative privileges, exiting");
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Closing read-write handle to logging database, last log message from this page");
		close_db($logdb);
		die("<script type=\"text/javascript\">window.location = \"login.php?redirect=admin-oldreqs\"</script>");
	}
	
	if(!empty($_GET['clear']) && $_GET['clear'] == "y")
	{
		$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Opened music database in write mode");
		if(permanently_delete_requests($musicdb) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Permanently dumped all deleted requests");
			trigger_error("Successfully dumped deleted requests.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Failed to dump all deleted requests");
			trigger_error("Failed to dump deleted requests. Check yourself before continuing.");
		}
		close_db($musicdb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Closed read-write handle to music database");
	}
	if(!empty($_GET['delete']) && !empty(($id=preg_replace("/[^0-9]/","",$_GET['delete']))))
	{
		$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Opened music database in write mode");
		if(permanently_delete_request($musicdb,$id) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Permanently dumped request \"$id\"");
			trigger_error("Successfully dumped request.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Failed to dump request \"$id\"");
			trigger_error("Failed to dump request. Check yourself before continuing.");
		}
		close_db($musicdb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Closed read-write handle to music database");
	}
	if(!empty($_GET['restoreall']) && $_GET['restoreall'] == "y")
	{
		$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Opened music database in write mode");
		if(restore_deleted_requests($musicdb) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Permanently restored all deleted requests");
			trigger_error("Successfully restored deleted requests.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Failed to restore all deleted requests");
			trigger_error("Failed to restore deleted requests. Check yourself before continuing.");
		}
		close_db($musicdb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Closed read-write handle to music database");
	}
	if(!empty($_GET['restore']) && !empty(($id=preg_replace("/[^0-9]/","",$_GET['restore']))))
	{
		$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READWRITE);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Opened music database in write mode");
		if(restore_deleted_request($musicdb,$id) === true)
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Permanently restored request \"$id\"");
			trigger_error("Successfully restored request.");
		}
		else
		{
			insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Failed to restore request \"$id\"");
			trigger_error("Failed to restore request. Check yourself before continuing.");
		}
		close_db($musicdb);
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Closed read-write handle to music database");
	}
	
	$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Opened music database in read mode");
	$requests=get_deleted_requests($musicdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Obtained all deleted requests");
	close_db($musicdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Closed read-only handle to music database");
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"admin-oldreqs.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Request Machine: Dumped Requests</title>
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
			sortList       : [[5,0]]
		});
	});
	</script>
    
    <!--[if IE]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Request Machine: The Request Dumpster</h1>
	<p><a href="admin-oldreqs.php?clear=y">Empty the dumpster</a> WARNING: this is <b>IMMEDIATE</b> and <b><u>IRREVERSIBLE</u></b>!!!<br>
	<a href="admin-oldreqs.php?restoreall=y">Recycle the dumpster</a> this is also permanent but significantly less dangerous.</p>
	<table id="songs" class="tablesorter-ice">
	<thead>
	<tr>
	<th class="sorter-false"></th>
	<th class="sorter-false"></th>
	<th>Name</th>
	<th>IP Address</th>
	<th class="sorter-false">Data</th>
	<th>Posted</th>
	<th class="sorter-false">Comment</th>
	<th>Status</th>
	<th class="sorter-false">Response</th>
	</tr>
	</thead>
	<tbody>
	<?php
		$db=open_db("db/music.sqlite",SQLITE3_OPEN_READONLY);
		foreach($requests as $request)
		{
			echo("<tr><td><a href=\"admin-oldreqs.php?restore=" . $request->getID() . "\">Recycle</a></td><td><a href=\"admin-oldreqs.php?delete=" . $request->getID() . "\">Dump</a></td><td>" . $request->getName() . "</td><td>" . $request->getIP() . "</td><td>");
			$songstring=array();
			switch($request->getMode())
			{
				case 0:
				$song=get_song($db,$request->getSong());
				foreach($format as $int=>$ext)
				{
					if(!empty($song->getDetails($int)))
					{
						$songstring[]="$ext: " . $song->getDetails($int);
					}
				}
				break;

				case 1:
				$song=new Song(NULL,NULL,$request->getSong(),NULL,NULL,NULL);
				foreach($format as $int=>$ext)
				{
					if(!empty($song->getDetails($int)))
					{
						$songstring[]="$ext: " . $song->getDetails($int);
					}
				}
				break;

				case 2:
				default:
				$songstring[]=$request->getSong();
				break;
			}
			echo(implode(", ", $songstring) . "</td><td>" . date($dateformat,$request->getTime()) . "</td><td>" . $request->getComment() . "</td><td>");
			switch($request->getStatus())
			{
				case 0:
				echo("Unseen");
				break;

				case 1:
				echo("Queued");
				break;

				case 2:
				echo("Declined");
				break;

				case 3:
				echo("Played");
				break;

				default:
				echo("Indeterminate");
				break;
			}
			echo("</td><td>" . $request->getResponse() . "</td></tr>");
		}
		close_db($db);
	?>
	</tbody>
	</table>
	<p><a href="admin-index.php">Abscond</a>
  </body>
</html>