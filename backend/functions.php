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
?>
<?php
	//Music functions
?>
<?php
	//Request functions
?>
<?php
	//Report functions
?>
<?php
	//Banning functions
?>
<?php
	//Updating functions
?>