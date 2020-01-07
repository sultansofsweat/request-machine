<?php
	//This is the MRS Function Core, to be included with all pages.
?>
<?php
	//Basic database functions
	
	function open_db($file,$mode)
	{
		if(!file_exists($file))
		{
			trigger_error("The file passed to function open_db has been abducted by Russians.",E_USER_ERROR);
			return false;
		}
		elseif($mode != SQLITE3_OPEN_READONLY && $mode != SQLITE3_OPEN_READWRITE)
		{
			trigger_error("Parameter mode passed to function open_db is not a valid parameter value.",E_USER_ERROR);
			return false;
		}
		try
		{
			return new SQLite3($file,$mode);
		}
		catch(Exception $e)
		{
			trigger_error("The database file has defenestrated your modem.",E_USER_ERROR);
			return false;
		}
	}
	//Function for closing the database
	function close_db($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function close_db is not a valid database.",E_USER_ERROR);
			return false;
		}
		return $db->close();
	}
	//Function for running VACUUM command to clean up database
	function optimize_db($db)
	{
		$returns=array(false,-1,-1);
		$returns[1]=filesize($db);
		$dbase=open_db($db,SQLITE3_OPEN_READWRITE);
		if(!is_a($dbase,"SQLite3"))
		{
			trigger_error("Failed to open database $db in write mode.",E_USER_WARNING);
			$returns[2]=$returns[1];
			return $returns;
		}
		$debug=$dbase->exec("VACUUM");
		sleep(10);
		if($debug === true)
		{
			$returns[0]=true;
			$returns[2]=filesize($db);
		}
		else
		{
			$returns[2]=$returns[1];
		}
		close_db($dbase);
		return $returns;
	}
?>
<?php
	//System functions
	
	//Function for inserting new setting
	function insert_setting($db,$name,$value)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function insert_setting is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("INSERT INTO settings(name,setting,static) VALUES (?,?,?)");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$name,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(2,$value,SQLITE3_TEXT);
				if($debug !== false)
				{
					$debug=$statement->bindValue(3,$value,SQLITE3_TEXT);
					if($debug !== false)
					{
						//Execute statement
						$result=$statement->execute();
						if($result !== false)
						{
							//Close statement
							$statement->close();
							unset($statement);
							return true;
						}
						//Failed to execute statement
						trigger_error("Failed to execute statement in function insert_setting.",E_USER_ERROR);
						goto failure;
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function insert_setting.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function insert_setting.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for updating a setting
	function update_setting($db,$name,$value)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function update_setting is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE settings SET setting = ? WHERE name = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(2,$name,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(1,$value,SQLITE3_TEXT);
				if($debug !== false)
				{
					//Execute statement
					$result=$statement->execute();
					if($result !== false)
					{
						//Close statement
						$statement->close();
						unset($statement);
						return true;
					}
					//Failed to execute statement
					trigger_error("Failed to execute statement in function update_setting.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function update_setting.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function update_setting.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for deleting a setting
	function delete_setting($db,$name)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function delete_setting is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM settings WHERE name = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$name,SQLITE3_TEXT);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Close statement
					$statement->close();
					unset($statement);
					return true;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function delete_setting.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function delete_setting.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function delete_setting.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for getting all settings
	function get_all_settings($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_settings is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Initialize set of defaults
		$settings=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT name,setting FROM settings");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Get data from result
					if(isset($entry["Setting"]) && isset($entry["Name"]))
					{
						$settings[$entry["Name"]]=$entry["Setting"];
					}
					else
					{
						if(isset($entry["Name"]))
						{
							trigger_error("Setting \"" . $entry["Name"] . "\" has no corresponding data. Ignoring it, expect problems.",E_USER_WARNING);
						}
						elseif(isset($entry["Setting"]))
						{
							trigger_error("Retrieved setting has data but no corresponding name. Ignoring it, expect problems.",E_USER_WARNING);
						}
						else
						{
							trigger_error("Retrieved setting has no name or data. Ignoring it, expect problems.",E_USER_WARNING);
						}
					}
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $settings;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_all_settings.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_settings.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $settings;
	}
	//Function for getting a setting
	function get_setting($db,$name)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_setting is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create default
		$setting=false;
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT setting FROM settings WHERE name = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$name,SQLITE3_TEXT);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Get data from result
						if(isset($entry["Setting"]))
						{
							$setting=$entry["Setting"];
						}
						else
						{
							trigger_error("Retrieved setting has no data. Ignoring it, expect problems.",E_USER_WARNING);
						}
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $setting;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_setting.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_setting.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_setting.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	
	//Function for getting all previous settings
	function get_all_previous($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_previous is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Initialize set of defaults
		$settings=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT name,previous FROM settings");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Get data from result
					if(isset($entry["Previous"]) && isset($entry["Name"]))
					{
						$settings[$entry["Name"]]=$entry["Previous"];
					}
					else
					{
						if(isset($entry["Previous"]))
						{
							trigger_error("Retrieved setting has data but no corresponding name. Ignoring it, expect problems.",E_USER_WARNING);
						}
					}
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $settings;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_all_previous.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_previous.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $settings;
	}
	//Function for reverting back to previous settings
	function revert_to_previous($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function revert_to_previous is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE settings SET setting = previous WHERE previous IS NOT NULL");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Close statement
				$statement->close();
				unset($statement);
				return true;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function revert_to_previous.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function revert_to_previous.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	
	//Function for updating a static default
	function update_default($db,$name,$value)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function update_default is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE settings SET static = ? WHERE name = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(2,$name,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(1,$value,SQLITE3_TEXT);
				if($debug !== false)
				{
					//Execute statement
					$result=$statement->execute();
					if($result !== false)
					{
						//Close statement
						$statement->close();
						unset($statement);
						return true;
					}
					//Failed to execute statement
					trigger_error("Failed to execute statement in function update_default.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function update_default.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function update_default.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for getting all static defaults
	function get_all_defaults($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_defaults is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Initialize set of defaults
		$settings=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT name,static FROM settings");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Get data from result
					if(isset($entry["Static"]) && isset($entry["Name"]))
					{
						$settings[$entry["Name"]]=$entry["Static"];
					}
					else
					{
						if(isset($entry["Name"]))
						{
							trigger_error("Setting \"" . $entry["Name"] . "\" has no corresponding data. Ignoring it, expect problems.",E_USER_WARNING);
						}
						elseif(isset($entry["Static"]))
						{
							trigger_error("Retrieved setting has data but no corresponding name. Ignoring it, expect problems.",E_USER_WARNING);
						}
						else
						{
							trigger_error("Retrieved setting has no name or data. Ignoring it, expect problems.",E_USER_WARNING);
						}
					}
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $settings;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_all_defaults.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_defaults.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $settings;
	}
	//Function for reverting to static default
	function revert_to_defaults($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function revert_to_defaults is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE settings SET setting = static WHERE name <> 'passwd'");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Close statement
				$statement->close();
				unset($statement);
				return true;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function revert_to_defaults.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function revert_to_defaults.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	
	//Function for getting all obsolete settings
	function get_all_obsolete($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_obsolete is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Initialize set of defaults
		$settings=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT name,setting FROM obsoletesettings");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Get data from result
					if(isset($entry["Setting"]) && isset($entry["Name"]))
					{
						$settings[$entry["Name"]]=$entry["Setting"];
					}
					else
					{
						if(isset($entry["Name"]))
						{
							trigger_error("Setting \"" . $entry["Name"] . "\" has no corresponding data. Ignoring it, expect problems.",E_USER_WARNING);
						}
						elseif(isset($entry["Setting"]))
						{
							trigger_error("Retrieved setting has data but no corresponding name. Ignoring it, expect problems.",E_USER_WARNING);
						}
						else
						{
							trigger_error("Retrieved setting has no name or data. Ignoring it, expect problems.",E_USER_WARNING);
						}
					}
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $settings;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_all_obsolete.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_obsolete.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $settings;
	}
	//Function for clearing obsolete settings
	function clear_obsolete($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function clear_obsolete is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM obsoletesettings");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Close statement
				$statement->close();
				unset($statement);
				return true;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function clear_obsolete.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function clear_obsolete.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	
	//Function for inserting version information
	function insert_version_info($db,$bc,$maj,$min,$rev,$tag,$rd)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function insert_version_info is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("INSERT INTO version(buildcode,major,minor,revision,tag,release) VALUES (?,?,?,?,?,?)");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$bc,SQLITE3_INTEGER);
			if($debug !== false)
			{
				$debug=$statement->bindValue(2,$maj,SQLITE3_INTEGER);
				if($debug !== false)
				{
					$debug=$statement->bindValue(3,$min,SQLITE3_INTEGER);
					if($debug !== false)
					{
						$debug=$statement->bindValue(4,$rev,SQLITE3_INTEGER);
						if($debug !== false)
						{
							$debug=$statement->bindValue(5,$tag,SQLITE3_TEXT);
							if($debug !== false)
							{
								$debug=$statement->bindValue(6,$rd,SQLITE3_TEXT);
								if($debug !== false)
								{
									//Execute statement
									$result=$statement->execute();
									if($result !== false)
									{
										//Close statement
										$statement->close();
										unset($statement);
										return true;
									}
									//Failed to execute statement
									trigger_error("Failed to execute statement in function insert_version_info.",E_USER_ERROR);
									goto failure;
								}
							}
						}
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function insert_version_info.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function insert_version_info.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for getting current version
	function get_current_version($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_current_version is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create default
		$versioninfo=array("build" => 1970010112001, "major" => 0, "minor" => 0, "revision" => 0, "tag" => "", "released" => "An error occurred. Dunk the nearest particle board table in the pool.");
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT buildcode,major,minor,revision,tag,release FROM version ORDER BY buildcode DESC LIMIT 1");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Get data from result
					if(isset($entry["BuildCode"]))
					{
						$versioninfo["build"]=$entry["BuildCode"];
					}
					if(isset($entry["Major"]))
					{
						$versioninfo["major"]=$entry["Major"];
					}
					if(isset($entry["Minor"]))
					{
						$versioninfo["minor"]=$entry["Minor"];
					}
					if(isset($entry["Revision"]))
					{
						$versioninfo["revision"]=$entry["Revision"];
					}
					if(isset($entry["Tag"]))
					{
						$versioninfo["tag"]=$entry["Tag"];
					}
					if(isset($entry["Release"]))
					{
						$versioninfo["released"]=$entry["Release"];
					}
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $versioninfo;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_current_version.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_current_version.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $versioninfo;
	}
	//Function for getting version history
	function get_version_history($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_version_history is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$infos=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT buildcode,major,minor,revision,tag,release,installed FROM version");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Setup default
					$versioninfo=array("build" => 1970010112001, "major" => 0, "minor" => 0, "revision" => 0, "tag" => "error", "released" => "An error occurred. Dunk the nearest particle board table in the pool.", "installed" => "1970-01-01 12:00:00");
					//Get data from result
					if(isset($entry["BuildCode"]))
					{
						$versioninfo["build"]=$entry["BuildCode"];
					}
					if(isset($entry["Major"]))
					{
						$versioninfo["major"]=$entry["Major"];
					}
					if(isset($entry["Minor"]))
					{
						$versioninfo["minor"]=$entry["Minor"];
					}
					if(isset($entry["Revision"]))
					{
						$versioninfo["revision"]=$entry["Revision"];
					}
					if(isset($entry["Tag"]))
					{
						$versioninfo["tag"]=$entry["Tag"];
					}
					if(isset($entry["Release"]))
					{
						$versioninfo["released"]=$entry["Release"];
					}
					if(isset($entry["Installed"]))
					{
						$versioninfo["installed"]=$entry["Installed"];
					}
					//Add info to list
					$infos[]=$versioninfo;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $infos;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_version_history.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_version_history.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $infos;
	}
	//Function for getting all build codes
	function get_build_codes($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_build_codes is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$infos=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT buildcode FROM version");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Get data from result
					if(isset($entry["BuildCode"]))
					{
						$infos[]=$entry["BuildCode"];
					}
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $infos;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_build_codes.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_build_codes.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $infos;
	}
	
	//Function to set initial admin password
	function set_initial_password($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function set_initial_password is not a valid database.",E_USER_ERROR);
			return false;
		}
		return update_setting($db,"passwd",password_hash("admin",PASSWORD_DEFAULT));
	}
?>
<?php
	//Logging functions
	
	//Function for inserting system log
	function insert_system_log($db,$ip,$time,$page,$text)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function insert_system_log is not a valid database.",E_USER_ERROR);
			return false;
		}
		if(get_setting($db,"syslog") == "n")
		{
			return true;
		}
		$statement=$db->prepare("INSERT INTO system(ip,time,page,text) VALUES (?,?,?,?)");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$ip,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(2,$time,SQLITE3_INTEGER);
				if($debug !== false)
				{
					$debug=$statement->bindValue(3,$page,SQLITE3_TEXT);
					if($debug !== false)
					{
						$debug=$statement->bindValue(4,$text,SQLITE3_TEXT);
						if($debug !== false)
						{
							//Execute statement
							$result=$statement->execute();
							if($result !== false)
							{
								//Close statement
								$statement->close();
								unset($statement);
								return true;
							}
							//Failed to execute statement
							trigger_error("Failed to execute statement in function insert_system_log.",E_USER_ERROR);
							goto failure;
						}
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function insert_system_log.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function insert_system_log.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for marking system log as read
	function mark_system_log_as_read($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function mark_system_log_as_read is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE system SET unread = 1");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Close statement
				$statement->close();
				unset($statement);
				return true;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function mark_system_log_as_read.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function mark_system_log_as_read.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for clearing system log
	function clear_system_log($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function clear_system_log is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM system");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Close statement
				$statement->close();
				unset($statement);
				return true;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function clear_system_log.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function clear_system_log.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for getting system log
	function get_system_logs($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_system_logs is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$logs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip,time,page,text,unread FROM system");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Setup default
					$logentry=array("ip" => "127.0.0.1", "time" => 0, "page" => "ERROR", "text" => "Failed to obtain information. Drop kick a can of Pepsi Lime.", "unread" => 0);
					//Get data from result
					if(isset($entry["IP"]))
					{
						$logentry["ip"]=$entry["IP"];
					}
					if(isset($entry["Time"]))
					{
						$logentry["time"]=$entry["Time"];
					}
					if(isset($entry["Page"]))
					{
						$logentry["page"]=$entry["Page"];
					}
					if(isset($entry["Text"]))
					{
						$logentry["text"]=$entry["Text"];
					}
					if(isset($entry["Unread"]))
					{
						$logentry["unread"]=$entry["Unread"];
					}
					//Make log entry object
					$entryobject=new SystemLogEntry($logentry["ip"],$logentry["time"],$logentry["page"],$logentry["text"],$logentry["unread"]);
					//Add object to list
					$logs[]=$entryobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $logs;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_system_logs.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_system_logs.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $logs;
	}
	//Function for getting unread system logs
	function get_unread_system_logs($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_unread_system_logs is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$logs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip,time,page,text FROM system WHERE unread = 1");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Setup default
					$logentry=array("ip" => "127.0.0.1", "time" => 0, "page" => "ERROR", "text" => "Failed to obtain information. Drop kick a can of Pepsi Lime.", "unread" => 1);
					//Get data from result
					if(isset($entry["IP"]))
					{
						$logentry["ip"]=$entry["IP"];
					}
					if(isset($entry["Time"]))
					{
						$logentry["time"]=$entry["Time"];
					}
					if(isset($entry["Page"]))
					{
						$logentry["page"]=$entry["Page"];
					}
					if(isset($entry["Text"]))
					{
						$logentry["text"]=$entry["Text"];
					}
					//Make log entry object
					$entryobject=new SystemLogEntry($logentry["ip"],$logentry["time"],$logentry["page"],$logentry["text"],$logentry["unread"]);
					//Add object to list
					$logs[]=$entryobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $logs;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_unread_system_logs.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_unread_system_logs.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $logs;
	}
	//Function for getting system logs for a specific date
	function get_system_logs_by_date($db,$start,$end)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_system_logs_by_date is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$logs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip,time,page,text,unread FROM system WHERE time >= ? AND time <= ?");
		if($statement !== false)
		{
			//Bind values to statement
			$debug=$statement->bindValue(1,$start,SQLITE3_INTEGER);
			if($debug === true)
			{
				$debug=$statement->bindValue(2,$end,SQLITE3_INTEGER);
				if($debug === true)
				{
					//Execute statement
					$result=$statement->execute();
					if($result !== false)
					{
						//Loop through all entries
						while($entry=$result->fetchArray(SQLITE3_ASSOC))
						{
							//Setup default
							$logentry=array("ip" => "127.0.0.1", "time" => 0, "page" => "ERROR", "text" => "Failed to obtain information. Drop kick a can of Pepsi Lime.", "unread" => 1);
							//Get data from result
							if(isset($entry["IP"]))
							{
								$logentry["ip"]=$entry["IP"];
							}
							if(isset($entry["Time"]))
							{
								$logentry["time"]=$entry["Time"];
							}
							if(isset($entry["Page"]))
							{
								$logentry["page"]=$entry["Page"];
							}
							if(isset($entry["Text"]))
							{
								$logentry["text"]=$entry["Text"];
							}
							//Make log entry object
							$entryobject=new SystemLogEntry($logentry["ip"],$logentry["time"],$logentry["page"],$logentry["text"],$logentry["unread"]);
							//Add object to list
							$logs[]=$entryobject;
						}
						//Close statement
						$statement->close();
						unset($statement);
						return $logs;
					}
					//Failed to execute statement
					trigger_error("Failed to execute statement in function get_system_logs_by_date.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind values to statement
			trigger_error("Failed to bind values to statement in function get_system_logs_by_date.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_system_logs_by_date.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $logs;
	}
	
	//Function for inserting error log
	function insert_error_log($db,$ip,$time,$page,$error,$text)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function insert_error_log is not a valid database.",E_USER_ERROR);
			return false;
		}
		if(get_setting($db,"errlog") == "n")
		{
			return true;
		}
		$statement=$db->prepare("INSERT INTO error(ip,time,page,text,error) VALUES (?,?,?,?,?)");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$ip,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(2,$time,SQLITE3_INTEGER);
				if($debug !== false)
				{
					$debug=$statement->bindValue(3,$page,SQLITE3_TEXT);
					if($debug !== false)
					{
						$debug=$statement->bindValue(4,$text,SQLITE3_TEXT);
						if($debug !== false)
						{
							$debug=$statement->bindValue(5,$error,SQLITE3_INTEGER);
							if($debug !== false)
							{
								//Execute statement
								$result=$statement->execute();
								if($result !== false)
								{
									//Close statement
									$statement->close();
									unset($statement);
									return true;
								}
								//Failed to execute statement
								trigger_error("Failed to execute statement in function insert_error_log.",E_USER_ERROR);
								goto failure;
							}
						}
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function insert_error_log.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function insert_error_log.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for marking error log as read
	function mark_error_log_as_read($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function mark_error_log_as_read is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE error SET unread = 1");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Close statement
				$statement->close();
				unset($statement);
				return true;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function mark_error_log_as_read.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function mark_error_log_as_read.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for clearing error log
	function clear_error_log($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function clear_error_log is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM error");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Close statement
				$statement->close();
				unset($statement);
				return true;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function clear_error_log.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function clear_error_log.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for getting error log
	function get_error_logs($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_error_logs is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$logs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip,time,page,text,unread,error FROM error");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Setup default
					$logentry=array("ip" => "127.0.0.1", "time" => 0, "page" => "ERROR", "error" => E_RECOVERABLE_ERROR, "text" => "Failed to obtain information. Drop kick a can of Pepsi Lime.", "unread" => 0);
					//Get data from result
					if(isset($entry["IP"]))
					{
						$logentry["ip"]=$entry["IP"];
					}
					if(isset($entry["Time"]))
					{
						$logentry["time"]=$entry["Time"];
					}
					if(isset($entry["Page"]))
					{
						$logentry["page"]=$entry["Page"];
					}
					if(isset($entry["Text"]))
					{
						$logentry["text"]=$entry["Text"];
					}
					if(isset($entry["Unread"]))
					{
						$logentry["unread"]=$entry["Unread"];
					}
					if(isset($entry["Error"]))
					{
						$logentry["error"]=$entry["Error"];
					}
					//Make log entry object
					$entryobject=new ErrorLogEntry($logentry["ip"],$logentry["time"],$logentry["page"],$logentry["error"],$logentry["text"],$logentry["unread"]);
					//Add object to list
					$logs[]=$entryobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $logs;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_error_logs.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_error_logs.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $logs;
	}
	//Function for getting unread error logs
	function get_unread_error_logs($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_unread_error_logs is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$logs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip,time,page,text,error FROM error WHERE unread = 1");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Setup default
					$logentry=array("ip" => "127.0.0.1", "time" => 0, "page" => "ERROR", "error" => E_RECOVERABLE_ERROR, "text" => "Failed to obtain information. Drop kick a can of Pepsi Lime.", "unread" => 1);
					//Get data from result
					if(isset($entry["IP"]))
					{
						$logentry["ip"]=$entry["IP"];
					}
					if(isset($entry["Time"]))
					{
						$logentry["time"]=$entry["Time"];
					}
					if(isset($entry["Page"]))
					{
						$logentry["page"]=$entry["Page"];
					}
					if(isset($entry["Text"]))
					{
						$logentry["text"]=$entry["Text"];
					}
					if(isset($entry["Error"]))
					{
						$logentry["error"]=$entry["Error"];
					}
					//Make log entry object
					$entryobject=new ErrorLogEntry($logentry["ip"],$logentry["time"],$logentry["page"],$logentry["error"],$logentry["text"],$logentry["unread"]);
					//Add object to list
					$logs[]=$entryobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $logs;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_unread_error_logs.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_unread_error_logs.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $logs;
	}
	//Function for getting error logs for a specific date
	function get_error_logs_by_date($db,$start,$end)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_error_logs_by_date is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$logs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip,time,page,text,unread FROM error WHERE time >= ? AND time <= ?");
		if($statement !== false)
		{
			//Bind values to statement
			$debug=$statement->bindValue(1,$start,SQLITE3_INTEGER);
			if($debug === true)
			{
				$debug=$statement->bindValue(2,$end,SQLITE3_INTEGER);
				if($debug === true)
				{
					//Execute statement
					$result=$statement->execute();
					if($result !== false)
					{
						//Loop through all entries
						while($entry=$result->fetchArray(SQLITE3_ASSOC))
						{
							//Setup default
							$logentry=array("ip" => "127.0.0.1", "time" => 0, "page" => "ERROR", "error" => E_RECOVERABLE_ERROR, "text" => "Failed to obtain information. Drop kick a can of Pepsi Lime.", "unread" => 0);
							//Get data from result
							if(isset($entry["IP"]))
							{
								$logentry["ip"]=$entry["IP"];
							}
							if(isset($entry["Time"]))
							{
								$logentry["time"]=$entry["Time"];
							}
							if(isset($entry["Page"]))
							{
								$logentry["page"]=$entry["Page"];
							}
							if(isset($entry["Text"]))
							{
								$logentry["text"]=$entry["Text"];
							}
							if(isset($entry["Unread"]))
							{
								$logentry["unread"]=$entry["Unread"];
							}
							if(isset($entry["Error"]))
							{
								$logentry["error"]=$entry["Error"];
							}
							//Make log entry object
							$entryobject=new ErrorLogEntry($logentry["ip"],$logentry["time"],$logentry["page"],$logentry["error"],$logentry["text"],$logentry["unread"]);
							//Add object to list
							$logs[]=$entryobject;
						}
						//Close statement
						$statement->close();
						unset($statement);
						return $logs;
					}
					//Failed to execute statement
					trigger_error("Failed to execute statement in function get_error_logs_by_date.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind values to statement
			trigger_error("Failed to bind values to statement in function get_error_logs_by_date.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_error_logs_by_date.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $logs;
	}
	
	//Function for inserting login log
	function insert_login_log($db,$ip,$browser,$time,$success)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function insert_login_log is not a valid database.",E_USER_ERROR);
			return false;
		}
		if(get_setting($db,"inlog") == "n")
		{
			return true;
		}
		$statement=$db->prepare("INSERT INTO login(ip,time,browser,status) VALUES (?,?,?,?)");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$ip,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(2,$time,SQLITE3_INTEGER);
				if($debug !== false)
				{
					$debug=$statement->bindValue(3,$browser,SQLITE3_TEXT);
					if($debug !== false)
					{
						$debug=$statement->bindValue(4,$success,SQLITE3_INTEGER);
						if($debug !== false)
						{
							//Execute statement
							$result=$statement->execute();
							if($result !== false)
							{
								//Close statement
								$statement->close();
								unset($statement);
								return true;
							}
							//Failed to execute statement
							trigger_error("Failed to execute statement in function insert_login_log.",E_USER_ERROR);
							goto failure;
						}
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function insert_login_log.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function insert_login_log.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for marking login log as read
	function mark_login_log_as_read($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function mark_login_log_as_read is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE login SET unread = 1");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Close statement
				$statement->close();
				unset($statement);
				return true;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function mark_login_log_as_read.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function mark_login_log_as_read.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for clearing login log
	function clear_login_log($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function clear_login_log is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM login");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Close statement
				$statement->close();
				unset($statement);
				return true;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function clear_login_log.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function clear_login_log.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return false;
	}
	//Function for getting login log
	function get_login_logs($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_login_logs is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$logs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip,time,browser,status,unread FROM login");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Setup default
					$logentry=array("ip" => "127.0.0.1", "time" => 0, "browser" => "ERROR", "status" => 0, "unread" => 0);
					//Get data from result
					if(isset($entry["IP"]))
					{
						$logentry["ip"]=$entry["IP"];
					}
					if(isset($entry["Time"]))
					{
						$logentry["time"]=$entry["Time"];
					}
					if(isset($entry["Browser"]))
					{
						$logentry["browser"]=$entry["Browser"];
					}
					if(isset($entry["Status"]))
					{
						$logentry["status"]=$entry["Status"];
					}
					if(isset($entry["Unread"]))
					{
						$logentry["unread"]=$entry["Unread"];
					}
					//Make log entry object
					$entryobject=new LoginLogEntry($logentry["ip"],$logentry["browser"],$logentry["time"],$logentry["status"],$logentry["unread"]);
					//Add object to list
					$logs[]=$entryobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $logs;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_login_logs.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_login_logs.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $logs;
	}
	//Function for getting unread login logs
	function get_unread_login_logs($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_unread_login_logs is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$logs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip,time,browser,status FROM system WHERE unread = 1");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Setup default
					$logentry=array("ip" => "127.0.0.1", "time" => 0, "browser" => "ERROR", "status" => 0, "unread" => 1);
					//Get data from result
					if(isset($entry["IP"]))
					{
						$logentry["ip"]=$entry["IP"];
					}
					if(isset($entry["Time"]))
					{
						$logentry["time"]=$entry["Time"];
					}
					if(isset($entry["Browser"]))
					{
						$logentry["browser"]=$entry["Browser"];
					}
					if(isset($entry["Status"]))
					{
						$logentry["status"]=$entry["Status"];
					}
					//Make log entry object
					$entryobject=new LoginLogEntry($logentry["ip"],$logentry["browser"],$logentry["time"],$logentry["status"],$logentry["unread"]);
					//Add object to list
					$logs[]=$entryobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $logs;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_unread_login_logs.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_unread_login_logs.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $logs;
	}
	//Function for getting failed login logs
	function get_failed_login_logs($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_failed_login_logs is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$logs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip,time,browser,unread FROM login WHERE status = 0");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Setup default
					$logentry=array("ip" => "127.0.0.1", "time" => 0, "browser" => "ERROR", "status" => 0, "unread" => 0);
					//Get data from result
					if(isset($entry["IP"]))
					{
						$logentry["ip"]=$entry["IP"];
					}
					if(isset($entry["Time"]))
					{
						$logentry["time"]=$entry["Time"];
					}
					if(isset($entry["Browser"]))
					{
						$logentry["browser"]=$entry["Browser"];
					}
					if(isset($entry["Unread"]))
					{
						$logentry["unread"]=$entry["Unread"];
					}
					//Make log entry object
					$entryobject=new LoginLogEntry($logentry["ip"],$logentry["browser"],$logentry["time"],$logentry["status"],$logentry["unread"]);
					//Add object to list
					$logs[]=$entryobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $logs;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_failed_login_logs.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_failed_login_logs.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $logs;
	}
	//Function for getting system logs for a specific date
	function get_login_logs_by_date($db,$start,$end)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_login_logs_by_date is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create empty storage array
		$logs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip,time,browser,status,unread FROM login WHERE time >= ? AND time <= ?");
		if($statement !== false)
		{
			//Bind values to statement
			$debug=$statement->bindValue(1,$start,SQLITE3_INTEGER);
			if($debug === true)
			{
				$debug=$statement->bindValue(2,$end,SQLITE3_INTEGER);
				if($debug === true)
				{
					//Execute statement
					$result=$statement->execute();
					if($result !== false)
					{
						//Loop through all entries
						while($entry=$result->fetchArray(SQLITE3_ASSOC))
						{
							//Setup default
							$logentry=array("ip" => "127.0.0.1", "time" => 0, "browser" => "ERROR", "status" => 0, "unread" => 0);
							//Get data from result
							if(isset($entry["IP"]))
							{
								$logentry["ip"]=$entry["IP"];
							}
							if(isset($entry["Time"]))
							{
								$logentry["time"]=$entry["Time"];
							}
							if(isset($entry["Browser"]))
							{
								$logentry["browser"]=$entry["Browser"];
							}
							if(isset($entry["Status"]))
							{
								$logentry["status"]=$entry["Status"];
							}
							if(isset($entry["Unread"]))
							{
								$logentry["unread"]=$entry["Unread"];
							}
							//Make log entry object
							$entryobject=new LoginLogEntry($logentry["ip"],$logentry["browser"],$logentry["time"],$logentry["status"],$logentry["unread"]);
							//Add object to list
							$logs[]=$entryobject;
						}
						//Close statement
						$statement->close();
						unset($statement);
						return $logs;
					}
					//Failed to execute statement
					trigger_error("Failed to execute statement in function get_login_logs_by_date.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind values to statement
			trigger_error("Failed to bind values to statement in function get_login_logs_by_date.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_login_logs_by_date.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $logs;
	}
?>
<?php
	//Music functions
	
	//Function for inserting song
	//Function for updating song details
	//Function for updating list associated with song
	//Function for updating request counts for a song
	//Function for deleting a song
	//Function for getting all songs
	//Function for counting all songs
	//Function for counting all song lists
	//Function for getting all songs on a list
	//Function for getting songs by first letter of artist
	//Function for getting songs by query
	//Function for getting new songs
	//Function for getting popular songs
	
	//Function for getting all deleted songs
	//Function for getting a deleted song
	//Function for restoring a deleted song
	//Function for permanently deleting a song
?>
<?php
	//Request functions
	
	//Function for inserting a request
	//Function for updating request name
	//Function for updating request comment
	//Function for updating request status
	//Function for converting request from ID mode to TEXT mode
	//Function for converting request to CUSTOM mode
	//Function for deleting a request
	//Function for getting all requests
	//Function for getting a request
	//Function for getting all requests from a user
	//Function for getting all requests from an IP
	//Function for getting all requests of a specific status
	
	//Function for getting all deleted requests
	//Function for getting a deleted request
	//Function for restoring a deleted request
	//Function for permanently deleting a request
?>
<?php
	//Report functions
	
	//Function for inserting a report
	//Function for marking a report as seen
	//Function for deleting a report
	//Function for getting all reports
	//Function for getting a report
	//Function for getting unread reports
?>
<?php
	//Banning functions
	
	//Function for inserting a username ban
	//Function for updating a username ban
	//Function for deleting a username ban
	//Function for getting all username bans
	//Function for getting all bans for a username
	//Function for getting active ban for a username
	
	//Function for inserting an IP ban
	//Function for updating an IP ban
	//Function for deleting an IP ban
	//Function for getting all IP bans
	//Function for getting all bans for an IP
	//Function for getting active ban for an IP
	
	//Function for getting a user-IP mapping
	//Function for inserting a new/updating an existing user-IP mapping
	//Function for getting all IPs associated with a username
	//Function for getting all usernames associated with an IP
?>
<?php
	//Updating functions
	
	//Function for getting list of upgrade packages
	//Function for getting upgrade package
	//Function for unpacking upgrade package
	//Function for running pre-processor
	//Function for replacing system files
	//Function for upgrading databases
	//Function for running post-processor
	//Function to clean up remaining mess
?>
<?php
	//Security functions
	
	//Function for checking admin flag
	//Function for checking IP address
	//Function for checking useragent
	//Function for checking identifier
	//Primary security checking function
?>