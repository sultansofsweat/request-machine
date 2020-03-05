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
	-Set timezone
	-Open read-only connection to bans database
	-Check for active bans against username or IP address
	-Close bans database
	-Open read-only connection to music database
	-Get counts of requests, songs and song lists
	-Close music database
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
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Visited homepage");
	
	$sysdb=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Opened system database in read mode");
	$name=get_setting($sysdb,"name");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"name\"");
	$dateformat=get_setting($sysdb,"dateformat");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"dateformat\"");
	$message=get_setting($sysdb,"message");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"message\"");
	$timezone=get_setting($sysdb,"timezone");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"timezone\"");
	$security=get_setting($sysdb,"security");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"security\"");
	$autorefresh=get_setting($sysdb,"autorefresh");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"autorefresh\"");
	switch(get_setting($sysdb,"showreqonclose"))
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
	switch(get_setting($sysdb,"displaystat"))
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
	switch(get_setting($sysdb,"displaycomment"))
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
	$hidereq=get_setting($sysdb,"hidereq");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"hidereq\"");
	$separator=get_setting($sysdb,"separator");
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Obtained setting \"separator\"");
	switch(get_setting($sysdb,"requests"))
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
	$dateformat=get_setting($sysdb,"dateformat");
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
	$unseenreqs=get_requests_by_status($musicdb,0);
	$queuedreqs=get_requests_by_status($musicdb,1);
	$playedreqs=array_merge(get_requests_by_status($musicdb,2),get_requests_by_status($musicdb,3));
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Got list of requests in system sorted by status");
	close_db($musicdb);
	insert_system_log($logdb,$_SERVER['REMOTE_ADDR'],time(),"index.php","Closed read-only handle to music database");
	
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
	<?php
		if($autorefresh > 0)
		{
			echo("<meta http-equiv=\"refresh\" content=\"$autorefresh\">\r\n");
		}
	?>
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
	<?php
		if(isset($_GET['in']) && $_GET['in'] == "yes")
		{
			trigger_error("Login successful.");
		}
		elseif(isset($_GET['in']) && $_GET['in'] == "banned")
		{
			trigger_error("Login failed: your rear end has been banned by this system.",E_USER_ERROR);
		}
		if(isset($_GET['out']) && $_GET['out'] == "yes")
		{
			trigger_error("Logout successful.");
		}
	?>
	<p>
	<?php
		if(!empty($_SESSION['username']))
		{
			echo("Hello, \"" . $_SESSION['username'] . "\"! ");
		}
		else
		{
			echo("Hello! ");
		}
		
		if(security_check($security) === true)
		{
			echo("<a href=\"logout.php\">Exit administrative mode</a> | <a href=\"admin-index.php\">MRS Central Control</a><br>\r\n");
		}
		else
		{
			echo("<a href=\"login.php\">Enter administrative mode</a><br>\r\n");
		}
		
		if(count($bans) > 0)
		{
			echo("You have been <b>banned</b> from this MRS. See details below and/or contact the BOFH. | ");
		}
		elseif(system_in_overload())
		{
			echo("The system is presently <b>overloaded</b> with requests. Please wait for the overload to clear before continuing. | ");
		}
		elseif(user_in_queue())
		{
			echo("You are currently <b>locked out</b> due to having a request in queue already. Please wait for your request to be played before continuing. | ");
		}
		elseif(user_hit_limit())
		{
			echo("You are currently <b>locked out</b> due to exceeding your request limit. Please wait a while before continuing. | ");
		}
		elseif($requests === true)
		{
			echo("<a href=\"search.php\">Make a request</a> | ");
		}
		else
		{
			echo("This MRS is <b>closed</b> and not accepting requests. | ");
		}
		
		echo("<a href=\"reqrules.php\">Request rules</a> | <a href=\"about.php\">About the MRS</a>");
	?>
	</p>
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
	<?php
		if(count($bans) > 0)
		{
			echo("<h3>YOU ARE BANNED FROM USING THIS SYSTEM!</h3>\r\n");
			echo("<p>You currently have a total of " . count($bans) . " ban(s) on your username and/or IP address. Below are notes of when the ban was issued, why it was issued, and when it expires (if it expires).<br>\r\n
			<b>It is recommended you contact the BOFH for next steps.</b> Attempting to circumvent bans is grounds for an <b>immediate and permanent</b> IP ban!</p>\r\n<hr>\r\n");
			foreach($bans as $ban)
			{
				echo("<p>Ban on: " . $ban->getItem() . "<br>\r\nIssued: " . date("l F jS, g:i A",$ban->getDate()) . "<br>\r\n<b>Active until: " . date("l F jS, g:i A",$ban->getUntil()) . "</b><br>\r\nReason for ban: " . $ban->getReason() . "</p>\r\n<hr>\r\n");
			}
			echo("<p>Attempting to use this system is futile. Nothing further is possible. Have a good day. Smash a GPX clock radio if it makes you feel better.</p>");
		}
		else
		{
			if($requests === true || $showreqonclose === true || security_check() === true)
			{
				if(count(array_merge($unseenreqs,$queuedreqs,$playedreqs)) > 0)
				{
					if(count($unseenreqs) > 0)
					{
						foreach($unseenreqs as $req)
						{
							if(!is_a($req,Request))
							{
								trigger_error("Stumbled upon request that is not in the proper format. Ignoring it, expect problems.",E_USER_WARNING);
								continue;
							}
							echo("<div style=\"opacity:1\"><img src=\"newflag/active.gif\" alt=\"New\" border=\"0px\"><img src=\"newflag/active.gif\" alt=\"New\" border=\"0px\"><img src=\"newflag/active.gif\" alt=\"New\" border=\"0px\">\r\n");
							echo($req->getSong() . "<br>\r\n");
							echo("Requested by " . $req->getName());
							if(security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP())
							{
								echo(" with IP address " . $req->getIP());
							}
							echo(" on " . date($dateformat,$req->getTime()) . "<br>\r\n");
							if(security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP() || $displaystat === true)
							{
								echo("This request has not yet been seen.<br>\r\n");
							}
							if($req->getComment() != "None" && ($displaycomment === true || security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP()))
							{
								echo("Comment: " . $req->getComment() . "<br>\r\n");
							}
							if($req->getResponse() != "None" && ($displaycomment === true || security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP()))
							{
								echo("The BOFH responds: " . $req->getResponse() . "<br>\r\n");
							}
							if(security_check() === true)
							{
								echo("<a href=\"delete.php?id=" . $req->getID() . "\">Delete this request</a> | <a href=\"chname.php?id=" . $req->getID() . "\">Change poster name</a> | <a href=\"queue.php?id=" . $req->getID() . "\">Put this request in play queue</a> | <a href=\"decline.php?id=" . $req->getID() . "\">Decline this request</a> | <a href=\"banuser.php?postid=" . $req->getID() . "\">Ban this username</a> | <a href=\"banip.php?postid=" . $req->getID() . "\">Ban this IP address</a>");
							}
							elseif($req->getIP() == $_SERVER['REMOTE_ADDR'])
							{
								echo("<a href=\"delete.php?id=" . $req->getID() . "\">Delete this request</a> | <a href=\"chname.php?id=" . $req->getID() . "\">Change poster name</a> | <a href=\"addcomment.php?id=" . $req->getID() . "\">Add/edit comment for this request</a>");
							}
							else
							{
								echo("<a href=\"report.php?id=" . $req->getID() . "\">Report this request</a>");
							}
							echo("\r\n</div>\r\n<hr>\r\n");
						}
						if($separator == 1)
						{
							echo("<hr>\r\n<hr>\r\n<hr>\r\n");
						}
					}
					if(count($queuedreqs) > 0)
					{
						foreach($queuedreqs as $req)
						{
							if(!is_a($req,Request))
							{
								trigger_error("Stumbled upon request that is not in the proper format. Ignoring it, expect problems.",E_USER_WARNING);
								continue;
							}
							echo("<div style=\"opacity:1\">\r\n");
							echo($req->getSong() . "<br>\r\n");
							echo("Requested by " . $req->getName());
							if(security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP())
							{
								echo(" with IP address " . $req->getIP());
							}
							echo(" on " . date($dateformat,$req->getTime()) . "<br>\r\n");
							if(security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP() || $displaystat === true)
							{
								echo("This request has been inserted in the play queue.<br>\r\n");
							}
							if($req->getComment() != "None" && ($displaycomment === true || security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP()))
							{
								echo("Comment: " . $req->getComment() . "<br>\r\n");
							}
							if($req->getResponse() != "None" && ($displaycomment === true || security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP()))
							{
								echo("The BOFH responds: " . $req->getResponse() . "<br>\r\n");
							}
							if(security_check() === true)
							{
								echo("<a href=\"delete.php?id=" . $req->getID() . "\">Delete this request</a> | <a href=\"chname.php?id=" . $req->getID() . "\">Change poster name</a> | <a href=\"play.php?id=" . $req->getID() . "\">Mark this request as played</a> | <a href=\"decline.php?id=" . $req->getID() . "\">Decline this request instead</a> | <a href=\"banuser.php?postid=" . $req->getID() . "\">Ban this username</a> | <a href=\"banip.php?postid=" . $req->getID() . "\">Ban this IP address</a>");
							}
							elseif($req->getIP() == $_SERVER['REMOTE_ADDR'])
							{
								echo("<a href=\"delete.php?id=" . $req->getID() . "\">Delete this request</a> | <a href=\"chname.php?id=" . $req->getID() . "\">Change poster name</a> | <a href=\"addcomment.php?id=" . $req->getID() . "\">Add/edit comment for this request</a>");
							}
							else
							{
								echo("<a href=\"report.php?id=" . $req->getID() . "\">Report this request</a>");
							}
							echo("\r\n</div>\r\n<hr>\r\n");
						}
						if($separator == 1)
						{
							echo("<hr>\r\n<hr>\r\n<hr>\r\n");
						}
					}
					if(count($playedreqs) > 0)
					{
						foreach($playedreqs as $req)
						{
							if(!is_a($req,Request))
							{
								trigger_error("Stumbled upon request that is not in the proper format. Ignoring it, expect problems.",E_USER_WARNING);
								continue;
							}
							if($separator == 0 && time() < ($req->getTime() + (24*60*60)))
							{
								$opacity=0.7;
							}
							elseif($separator == 0)
							{
								$opacity=0.2;
							}
							else
							{
								$opacity=1;
							}
							echo("<div style=\"opacity:$opacity\">\r\n");
							echo($req->getSong() . "<br>\r\n");
							echo("Requested by " . $req->getName());
							if(security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP())
							{
								echo(" with IP address " . $req->getIP());
							}
							echo(" on " . date($dateformat,$req->getTime()) . "<br>\r\n");
							if(security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP() || $displaystat === true)
							{
								echo("This request has not yet been seen.<br>\r\n");
							}
							if($req->getComment() != "None" && ($displaycomment === true || security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP()))
							{
								echo("Comment: " . $req->getComment() . "<br>\r\n");
							}
							if($req->getResponse() != "None" && ($displaycomment === true || security_check() === true || $_SERVER['REMOTE_ADDR'] == $req->getIP()))
							{
								echo("The BOFH responds: " . $req->getResponse() . "<br>\r\n");
							}
							if(security_check() === true)
							{
								echo("<a href=\"delete.php?id=" . $req->getID() . "\">Delete this request</a> | <a href=\"chname.php?id=" . $req->getID() . "\">Change poster name</a> | <a href=\"banuser.php?postid=" . $req->getID() . "\">Ban this username</a> | <a href=\"banip.php?postid=" . $req->getID() . "\">Ban this IP address</a>");
							}
							elseif($req->getIP() == $_SERVER['REMOTE_ADDR'])
							{
								echo("<a href=\"chname.php?id=" . $req->getID() . "\">Change poster name</a> | <a href=\"addcomment.php?id=" . $req->getID() . "\">Add/edit comment for this request</a>");
							}
							echo("\r\n</div>\r\n<hr>\r\n");
						}
					}
					echo("<p>End of requests in this system. Don't see yours? Make a new one!</p>");
				}
			}
		}
	?>
  </body>
</html>