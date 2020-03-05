<?php
	/* ORDER OF OPERATIONS
	-Require object core
	-Require function core
	-Include error handler
	-Open session
	-Open read-write connection to logging database
	-Open read-only connection to system database
	-Get required settings
	-Close system database
	-Close logging database
	*/
	
	include_once("backend/errorhandler.php");
	require_once("backend/objects.php");
	require_once("backend/functions.php");
	
	if(alt_sess_store() !== false)
	{
		session_save_path(alt_sess_store());
	}
	session_start();
	
	$logdb=open_db("db/logs.sqlite",SQLITE3_OPEN_READWRITE);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"reqrules.php","Visited homepage");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"reqrules.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"reqrules.php","Obtained setting \"name\"");
	close_db($sysdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"reqrules.php","Closed read-only handle to system database");
	
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"reqrules.php","Closing read-write handle to logging database, last log message from this page");
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
    <title><?php echo $name; ?> Music Request System: Request Rules</title>
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
	<h1 style="text-align:center; text-decoration:underline;"><?php echo $name; ?> Music Request System: Request Rules</h1>
	<hr>
	<p>The general rules for requests can be found <a href="http://greyghost.mooo.com/live-stream-rules.html">here</a>, however this request system has a few of it's own. This page will also serve to re-iterate a few rules as well.</p>
	<ul>
	<li>Please don't attempt to make requests if the system is off.</li>
	<li>If you find the system is off, get in touch with me and I'll figure it out from there. Do NOT, however, repeatedly contact me; once is enough usually.</li>
	<li>Only make your request ONCE.</li>
	<li>Please don't duplicate requests. I keep them up for a reason!</li>
	<li>Duplicate requests from the same or differing users will be declined. Repeat offenders will be banned.</li>
	<li>If anonymous and/or open requesting mode is on, please don't abuse it and ruin it for everyone.</li>
	<li>Users caught abusing this system will be subject to bans of varying severity in accordance with the circumstances.</li>
	<li>Username bans can potentially be lifted. Profane usernames, or usernames with which particularly troublesome behavior has been noted, will not be lifted. Users using these names may be allowed back.</li>
	<li>Users caught evading username bans will be subject to immediate IP bans.</li>
	<li>IP bans are permanent, with zero grounds for reinstatement (with the exception of fringe cases where operator error took place).</li>
	<li>Attempting to hijack this system will result in an immediate IP ban and notification of the relevant authorities. Spam will earn you the same thing. Don't try it.</li>
	<li>Requests may or may not be accepted or declined during a show. Please do note this.</li>
	<li>Begging, pleading, and crying to get your request played will have the opposite effect, even if it is in this system.</li>
	<li>Please do not suggest means of obtaining music such as YouTube. I have no way to do this, and even if I did, I still would not do it. I like to maintain a level of professionalism to my show.</li>
	<li>This system has backend support for logging. Logging will track everything you do. If this is undesirable it is in your best interest to not use this system.</li>
	<li>This system is not for use as a chit-chat system, or for any other purpose that is not requesting music. Repeat offenders will be banned.</li>
	<li>You may not administer this system. Period. Only I may have administrator duties.</li>
	<li>Bugs should be reported as soon as they are found. Resolutions are even better. Do the right thing!</li>
	<li>Do not complain about rules. They are in place so everyone is on the same page as to what is acceptable and what is not.</li>
	<li>This system is always in testing, and may disappear at any time, most importantly if it becomes clear that it is just going to be abused.</li>
	</ul>
	<p><a href="index.php">Return to homepage</a></p>
  </body>
</html>