<?php
	/* ORDER OF OPERATIONS
	-Require object core
	-Require function core
	-Open session
	-Open read-write connection to logging database
	-Open read-only connection to system database
	-Get required settings
	-Close system database
	-Set timezone
	-Open read-only connection to bans database
	-Check for active bans against username or IP address
	-Close bans database
	-Open read-only connection to music database
	-Get counts of requests, songs and song lists
	-Close music database
	-Close logging database
	*/
	
	require_once("backend/objects.php");
	require_once("backend/functions.php");
	
	if(alt_sess_store() !== false)
	{
		session_save_path(alt_sess_store());
	}
	session_start();
	
	$logdb=open_db("db/logs.sqlite",SQLITE3_OPEN_READWRITE);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Visited homepage");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Opened system database in read mode");
	$name=get_setting($db,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"name\"");
	$message=get_setting($db,"message");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"message\"");
	$timezone=get_setting($db,"timezone");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"timezone\"");
	$security=get_setting($db,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"security\"");
	$autorefresh=get_setting($db,"autorefresh");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"autorefresh\"");
	switch(get_setting($db,"showreqonclose"))
	{
		case "y":
		$showreqonclose=true;
		break;
		
		case "n":
		default:
		$showreqonclose=false;
		break;
	}
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"showreqonclose\"");
	switch(get_setting($db,"displaystat"))
	{
		case "y":
		$displaystat=true;
		break;
		
		case "n":
		default:
		$displaystat=false;
		break;
	}
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"displaystat\"");
	switch(get_setting($db,"displaycomment"))
	{
		case "y":
		$displaycomment=true;
		break;
		
		case "n":
		default:
		$displaycomment=false;
		break;
	}
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"displaycomment\"");
	$hidereq=get_setting($db,"hidereq");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"hidereq\"");
	$separator=get_setting($db,"separator");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"separator\"");
	switch(get_setting($db,"requests"))
	{
		case "y":
		$requests=true;
		break;
		
		case "n":
		default:
		$requests=false;
		break;
	}
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"requests\"");
	$dateformat=get_setting($db,"dateformat");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"dateformat\"");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Closed read-only handle to system database");
	
	date_default_timezone_set($timezone);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Changed timezone to \"$timezone\"");
	
	$bans=array();
	$bandb=open_db("db/bans.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Opened bans database in read mode");
	if(!empty($_SESSION['username']))
	{
		$bans=array_merge(get_all_active_bans_for_ip($bandb,$_SERVER['REMOTE_ADDR']),get_all_active_bans_for_uname($bandb,$_SESSION['username']));
		insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained active username and IP bans");
	}
	else
	{
		$bans=get_all_active_bans_for_ip($bandb,$_SERVER['REMOTE_ADDR']);
	}
	close_db($bandb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Closed read-only handle to bans database");
	
	$reqcount=0;
	$songcount=0;
	$listcount=0;
	$listext="lists";
	$musicdb=open_db("db/music.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Opened music database in read mode");
	$reqcount=count(get_all_requests($musicdb));
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained count of requests in system");
	$songcount=count_all_songs($musicdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained count of songs in system");
	$listcount=count_song_lists($musicdb);
	if($listcount == 1)
	{
		$listext="list";
	}
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained count of lists in system");
	close_db($musicdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Closed read-only handle to bans database");
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Music Request System</title>
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
    
    <!--[if IE]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
	<!-- Processing details go here -->
	<!-- Header goes here -->
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Music Request System</h1>
	<h3>There have been <?php echo $reqcount; ?> all-time requests on this system. Right now, we have <?php echo $songcount; ?> songs in our library across <?php echo $listcount . " " . $listext; ?>.</h3>
	<?php
		if(!empty($message))
		{
			echo("<h3>$message</h3>\r\n");
		}
		if(is_logging_enabled())
		{
			echo("<h3>/!\ WARNING: System logging capabilities are enabled!</h3>\r\n");
		}
	?>
	<hr>
	<!-- Requests or ban details go here -->
	<hr>
	<h4>This engine demo is presently using static data. It is not a representation of a real MRS.</h4>
  </body>
</html>