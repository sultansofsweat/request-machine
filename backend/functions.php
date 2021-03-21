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
			return array();
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
	//Function for getting settings descriptions
	function get_all_descriptions($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_descriptions is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Initialize set of defaults
		$settings=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT name,description FROM settings");
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
					if(isset($entry["Description"]) && isset($entry["Name"]))
					{
						$settings[$entry["Name"]]=$entry["Description"];
					}
					else
					{
						if(isset($entry["Name"]))
						{
							trigger_error("Setting \"" . $entry["Name"] . "\" has no corresponding description. Ignoring it, expect problems.",E_USER_WARNING);
						}
						elseif(isset($entry["Setting"]))
						{
							trigger_error("Retrieved setting has description but no corresponding name. Ignoring it, expect problems.",E_USER_WARNING);
						}
						else
						{
							trigger_error("Retrieved setting has no name or description. Ignoring it, expect problems.",E_USER_WARNING);
						}
					}
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $settings;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_all_descriptions.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_descriptions.",E_USER_ERROR);
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
			return array();
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
			return array();
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
			return array();
		}
		//Initialize set of defaults
		$settings=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT name,setting,static FROM obsoletesettings");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					$setting=array("name"=>"error","value"=>"error","default"=>"error");
					//Get data from result
					if(isset($entry["Setting"]))
					{
						$settings["value"]=$entry["Setting"];
					}
					if(isset($entry["Name"]))
					{
						$settings["name"]=$entry["Name"];
					}
					if(isset($entry["Static"]))
					{
						$settings["default"]=$entry["Static"];
					}
					$settings[]=$setting;
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
		$versioninfo=false;
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT buildcode,major,minor,revision,tag,release,installed FROM version ORDER BY buildcode DESC LIMIT 1");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Set up default
					$verinfo=array("build" => 1970010112001, "major" => 0, "minor" => 0, "revision" => 0, "tag" => "", "released" => "An error occurred. Dunk the nearest particle board table in the pool.","installed"=>0);
					//Get data from result
					if(isset($entry["BuildCode"]))
					{
						$verinfo["build"]=$entry["BuildCode"];
					}
					if(isset($entry["Major"]))
					{
						$verinfo["major"]=$entry["Major"];
					}
					if(isset($entry["Minor"]))
					{
						$verinfo["minor"]=$entry["Minor"];
					}
					if(isset($entry["Revision"]))
					{
						$verinfo["revision"]=$entry["Revision"];
					}
					if(isset($entry["Tag"]))
					{
						$verinfo["tag"]=$entry["Tag"];
					}
					if(isset($entry["Release"]))
					{
						$verinfo["released"]=$entry["Release"];
					}
					if(isset($entry["Installed"]))
					{
						$verinfo["installed"]=$entry["Installed"];
					}
					//Create version object
					$versioninfo=new Version($verinfo["build"],$verinfo["major"],$verinfo["minor"],$verinfo["revision"],$verinfo["tag"],$verinfo["released"],$verinfo["installed"]);
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
			return array();
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
					//Create version object
					$versionobject=new Version($versioninfo["build"],$versioninfo["major"],$versioninfo["minor"],$versioninfo["revision"],$versioninfo["tag"],$versioninfo["released"],$versioninfo["installed"]);
					//Add info to list
					$infos[]=$versionobject;
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
			return array();
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
		$db2=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
		if(get_setting($db2,"syslog") == "n")
		{
			return true;
		}
		close_db($db2);
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
			return array();
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
			return array();
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
			return array();
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
		$db2=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
		if(get_setting($db2,"errlog") == "n")
		{
			return true;
		}
		close_db($db2);
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
			return array();
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
			return array();
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
			return array();
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
		$db2=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
		if(get_setting($db2,"inlog") == "n")
		{
			return true;
		}
		close_db($db2);
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
			return array();
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
			return array();
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
			return array();
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
			return array();
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
	function insert_song($db,$list,$details)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function insert_song is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("INSERT INTO songs(list,details,added) VALUES (?,?,?)");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$list,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(3,time(),SQLITE3_INTEGER);
				if($debug !== false)
				{
					$debug=$statement->bindValue(2,$details,SQLITE3_TEXT);
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
						trigger_error("Failed to execute statement in function insert_song.",E_USER_ERROR);
						goto failure;
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function insert_song.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function insert_song.",E_USER_ERROR);
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
	//Function for updating song details
	function update_song($db,$id,$details)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function update_song is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE songs SET details = ? WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(2,$details,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
					trigger_error("Failed to execute statement in function update_song.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function update_song.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function update_song.",E_USER_ERROR);
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
	//Function for updating list associated with song
	function change_list($db,$id,$newlist)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function change_list is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE songs SET list = ? WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(2,$list,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
					trigger_error("Failed to execute statement in function change_list.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function change_list.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function change_list.",E_USER_ERROR);
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
	//Function for updating request counts for a song
	function update_request_count($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function update_request_count is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE songs SET count = count + 1, lastreq = ? WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(2,time(),SQLITE3_INTEGER);
			if($debug !== false)
			{
				$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
					trigger_error("Failed to execute statement in function update_request_count.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function update_request_count.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function update_request_count.",E_USER_ERROR);
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
	//Function for deleting a song
	function delete_song($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function delete_song is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM songs WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
				trigger_error("Failed to execute statement in function delete_song.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function delete_song.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function delete_song.",E_USER_ERROR);
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
	//Function for getting all songs
	function get_all_songs($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_songs is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Initialize set of defaults
		$songs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,list,details,added,count,lastreq FROM songs");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Set up data format
					$song=array("id"=>-1,"list"=>"ERROR","details"=>"Failed to obtain song. Microwave something expensive.","added"=>0,"count"=>666,"lastreq"=>0);
					//Get data from result
					if(isset($entry["ID"]))
					{
						$song["id"]=$entry["ID"];
					}
					if(isset($entry["List"]))
					{
						$song["list"]=$entry["List"];
					}
					if(isset($entry["Details"]))
					{
						$song["details"]=$entry["Details"];
					}
					if(isset($entry["Added"]))
					{
						$song["added"]=$entry["Added"];
					}
					if(isset($entry["Count"]))
					{
						$song["count"]=$entry["Count"];
					}
					if(isset($entry["LastReq"]))
					{
						$song["lastreq"]=$entry["LastReq"];
					}
					//Create song object
					$songobject=new Song($song["id"],$song["list"],$song["details"],$song["added"],$song["count"],$song["lastreq"]);
					//Add object to list
					$songs[]=$songobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $songs;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_all_songs.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_songs.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $songs;
	}
	//Function for counting all songs
	function count_all_songs($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function count_all_songs is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create default
		$count=0;
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT COUNT(*) as count FROM songs");
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
					if(isset($entry["count"]))
					{
						$count=max($count,$entry["count"]);
					}
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $count;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function count_all_songs.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function count_all_songs.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return 0;
	}
	//Function for counting all song lists
	function count_song_lists($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function count_song_lists is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create default
		$count=0;
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT COUNT(DISTINCT List) as count FROM songs");
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
					if(isset($entry["count"]))
					{
						$count=max($count,$entry["count"]);
					}
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $count;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function count_song_lists.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function count_song_lists.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return 0;
	}
	//Function for getting a single song
	function get_song($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_song is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Set up default
		$song=false;
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT details,added,count,lastreq FROM songs WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$song=array("id"=>$id,"list"=>$list,"details"=>"Failed to obtain song. Microwave something expensive.","added"=>0,"count"=>666,"lastreq"=>0);
						//Get data from result
						if(isset($entry["List"]))
						{
							$song["list"]=$entry["List"];
						}
						if(isset($entry["Details"]))
						{
							$song["details"]=$entry["Details"];
						}
						if(isset($entry["Added"]))
						{
							$song["added"]=$entry["Added"];
						}
						if(isset($entry["Count"]))
						{
							$song["count"]=$entry["Count"];
						}
						if(isset($entry["LastReq"]))
						{
							$song["lastreq"]=$entry["LastReq"];
						}
						//Create song object
						$songobject=new Song($song["id"],$song["list"],$song["details"],$song["added"],$song["count"],$song["lastreq"]);
						//Add object to list
						$song=$songobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $song;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_song.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_song.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_song.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $song;
	}
	//Function for getting all songs on a list
	function get_songs_by_list($db,$list)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_songs_by_list is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Create default
		$songs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,details,added,count,lastreq FROM songs WHERE list = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$list,SQLITE3_TEXT);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$song=array("id"=>-1,"list"=>$list,"details"=>"Failed to obtain song. Microwave something expensive.","added"=>0,"count"=>666,"lastreq"=>0);
						//Get data from result
						if(isset($entry["ID"]))
						{
							$song["id"]=$entry["ID"];
						}
						if(isset($entry["Details"]))
						{
							$song["details"]=$entry["Details"];
						}
						if(isset($entry["Added"]))
						{
							$song["added"]=$entry["Added"];
						}
						if(isset($entry["Count"]))
						{
							$song["count"]=$entry["Count"];
						}
						if(isset($entry["LastReq"]))
						{
							$song["lastreq"]=$entry["LastReq"];
						}
						//Create song object
						$songobject=new Song($song["id"],$song["list"],$song["details"],$song["added"],$song["count"],$song["lastreq"]);
						//Add object to list
						$songs[]=$songobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $songs;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_songs_by_list.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_songs_by_list.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_songs_by_list.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $songs;
	}
	//Function for getting songs by first letter of artist
	function get_songs_by_artist($db,$letter)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_songs_by_artist is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create default
		$songs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,list,details,added,count,lastreq FROM songs WHERE details LIKE ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,"artist=$letter",SQLITE3_TEXT);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$song=array("id"=>-1,"list"=>"ERROR","details"=>"Failed to obtain song. Microwave something expensive.","added"=>0,"count"=>666,"lastreq"=>0);
						//Get data from result
						if(isset($entry["ID"]))
						{
							$song["id"]=$entry["ID"];
						}
						if(isset($entry["List"]))
						{
							$song["list"]=$entry["List"];
						}
						if(isset($entry["Details"]))
						{
							$song["details"]=$entry["Details"];
						}
						if(isset($entry["Added"]))
						{
							$song["added"]=$entry["Added"];
						}
						if(isset($entry["Count"]))
						{
							$song["count"]=$entry["Count"];
						}
						if(isset($entry["LastReq"]))
						{
							$song["lastreq"]=$entry["LastReq"];
						}
						//Create song object
						$songobject=new Song($song["id"],$song["list"],$song["details"],$song["added"],$song["count"],$song["lastreq"]);
						//Add object to list
						$songs[]=$songobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $songs;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_songs_by_artist.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_songs_by_artist.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_songs_by_artist.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $songs;
	}
	//Function for getting songs by query
	function song_query($db,$field,$query,$exact)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function song_query is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Set up output
		$songs=array();
		//Get all songs
		$all=get_all_songs($db);
		//Loop through all songs
		foreach($all as $song)
		{
			if($exact === true && $song->getDetails($field) == $query)
			{
				//Add song to list
				$songs[]=$song;
			}
			elseif($exact === false && strpos(strtolower($song->getDetails($field)),strtolower($query)) !== false)
			{
				//Add song to list
				$songs[]=$song;
			}
		}
		//Return resulting list
		return $songs;
	}
	//Function for getting new songs
	function get_new_songs($db,$threshold)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_new_songs is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Create default
		$songs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,list,details,added,count,lastreq FROM songs WHERE added >= ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,(time()-$threshold),SQLITE3_INTEGER);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$song=array("id"=>-1,"list"=>"ERROR","details"=>"Failed to obtain song. Microwave something expensive.","added"=>0,"count"=>666,"lastreq"=>0);
						//Get data from result
						if(isset($entry["ID"]))
						{
							$song["id"]=$entry["ID"];
						}
						if(isset($entry["List"]))
						{
							$song["list"]=$entry["List"];
						}
						if(isset($entry["Details"]))
						{
							$song["details"]=$entry["Details"];
						}
						if(isset($entry["Added"]))
						{
							$song["added"]=$entry["Added"];
						}
						if(isset($entry["Count"]))
						{
							$song["count"]=$entry["Count"];
						}
						if(isset($entry["LastReq"]))
						{
							$song["lastreq"]=$entry["LastReq"];
						}
						//Create song object
						$songobject=new Song($song["id"],$song["list"],$song["details"],$song["added"],$song["count"],$song["lastreq"]);
						//Add object to list
						$songs[]=$songobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $songs;
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
		return $songs;
	}
	//Function for getting popular songs
	function get_popular_songs($db,$count,$includezero)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_popular_songs is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Create default
		$songs=array();
		if($includezero === true)
		{
			$lowerbound=0;
		}
		else
		{
			$lowerbound=1;
		}
		$lastcountseen=-1;
		$countchange=0;
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,list,details,added,count,lastreq FROM settings WHERE count >= ? ORDER BY count DESC");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$lowerbound,SQLITE3_INTEGER);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//If seen enough count changes, exit loop
						if($countchange >= $count)
						{
							break;
						}
						//Set up data format
						$song=array("id"=>-1,"list"=>"ERROR","details"=>"Failed to obtain song. Microwave something expensive.","added"=>0,"count"=>666,"lastreq"=>0);
						//Get data from result
						if(isset($entry["ID"]))
						{
							$song["id"]=$entry["ID"];
						}
						if(isset($entry["List"]))
						{
							$song["list"]=$entry["List"];
						}
						if(isset($entry["Details"]))
						{
							$song["details"]=$entry["Details"];
						}
						if(isset($entry["Added"]))
						{
							$song["added"]=$entry["Added"];
						}
						if(isset($entry["Count"]))
						{
							$song["count"]=$entry["Count"];
						}
						if(isset($entry["LastReq"]))
						{
							$song["lastreq"]=$entry["LastReq"];
						}
						//Create song object
						$songobject=new Song($song["id"],$song["list"],$song["details"],$song["added"],$song["count"],$song["lastreq"]);
						//Add object to list
						$songs[]=$songobject;
						//If count seen different from last, increment count change
						if($songobject->getCount() != $lastcountseen)
						{
							$lastcountseen=$songobject->getCount();
							$countchange++;
						}
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $songs;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_popular_songs.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_popular_songs.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_popular_songs.",E_USER_ERROR);
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
	
	//Function for getting all deleted songs
	function get_deleted_songs($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_deleted_songs is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Initialize set of defaults
		$songs=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,list,details,added,count,lastreq FROM oldsongs");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Set up data format
					$song=array("id"=>-1,"list"=>"ERROR","details"=>"Failed to obtain song. Microwave something expensive.","added"=>0,"count"=>666,"lastreq"=>0);
					//Get data from result
					if(isset($entry["ID"]))
					{
						$song["id"]=$entry["ID"];
					}
					if(isset($entry["List"]))
					{
						$song["list"]=$entry["List"];
					}
					if(isset($entry["Details"]))
					{
						$song["details"]=$entry["Details"];
					}
					if(isset($entry["Added"]))
					{
						$song["added"]=$entry["Added"];
					}
					if(isset($entry["Count"]))
					{
						$song["count"]=$entry["Count"];
					}
					if(isset($entry["LastReq"]))
					{
						$song["lastreq"]=$entry["LastReq"];
					}
					//Create song object
					$songobject=new Song($song["id"],$song["list"],$song["details"],$song["added"],$song["count"],$song["lastreq"]);
					//Add object to list
					$songs[]=$songobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $songs;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_deleted_songs.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_deleted_songs.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $songs;
	}
	//Function for getting a deleted song
	function get_deleted_song($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_deleted_song is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Set up default
		$song=false;
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT details,added,count,lastreq FROM oldsongs WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$song=array("id"=>$id,"list"=>$list,"details"=>"Failed to obtain song. Microwave something expensive.","added"=>0,"count"=>666,"lastreq"=>0);
						//Get data from result
						if(isset($entry["List"]))
						{
							$song["list"]=$entry["List"];
						}
						if(isset($entry["Details"]))
						{
							$song["details"]=$entry["Details"];
						}
						if(isset($entry["Added"]))
						{
							$song["added"]=$entry["Added"];
						}
						if(isset($entry["Count"]))
						{
							$song["count"]=$entry["Count"];
						}
						if(isset($entry["LastReq"]))
						{
							$song["lastreq"]=$entry["LastReq"];
						}
						//Create song object
						$songobject=new Song($song["id"],$song["list"],$song["details"],$song["added"],$song["count"],$song["lastreq"]);
						//Add object to list
						$song=$songobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $song;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_deleted_song.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_deleted_song.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_deleted_song.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $song;
	}
	//Function for restoring a deleted song
	function restore_deleted_song($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function restore_deleted_song is not a valid database.",E_USER_ERROR);
			return false;
		}
		$details=get_deleted_song($db,$id);
		$debug=permanently_delete_song($db,$id);
		if($debug === true)
		{
			$statement=$db->prepare("INSERT INTO songs(list,details,added,count,lastreq) VALUES (?,?,?,?,?)");
			if($statement !== false)
			{
				//Bind variables
				$debug=$statement->bindValue(1,$details->getList(),SQLITE3_TEXT);
				if($debug !== false)
				{
					$debug=$statement->bindValue(2,$details->getRawDetails(),SQLITE3_TEXT);
					if($debug !== false)
					{
						$debug=$statement->bindValue(3,$details->getAdded(),SQLITE3_INTEGER);
						if($debug !== false)
						{
							$debug=$statement->bindValue(4,$details->getCount(),SQLITE3_INTEGER);
							if($debug !== false)
							{
								$debug=$statement->bindValue(5,$details->getLastReq(),SQLITE3_INTEGER);
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
									trigger_error("Failed to execute statement in function restore_deleted_song.",E_USER_ERROR);
									goto failure;
								}
							}
						}
					}
				}
				//Failed to bind variables to statement
				trigger_error("Failed to bind values to statement in function restore_deleted_song.",E_USER_ERROR);
				goto failure;
			}
			//Failed to create statement
			trigger_error("Failed to create statement in function restore_deleted_song.",E_USER_ERROR);
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
		else
		{
			trigger_error("Failed to remove data from dumping database. Cannot proceed.",E_USER_ERROR);
			return false;
		}
	}
	//Function for restoring all deleted songs
	function restore_deleted_songs($db)
	{
		$debug=array();
		$songs=get_deleted_songs($db);
		foreach($songs as $song)
		{
			$debug[]=restore_deleted_song($db,$song->getID());
		}
		if(!in_array(false,$debug))
		{
			return true;
		}
		return false;
	}
	//Function for permanently deleting a song
	function permanently_delete_song($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function permanently_delete_song is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM oldsongs WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
				trigger_error("Failed to execute statement in function permanently_delete_song.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function permanently_delete_song.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function permanently_delete_song.",E_USER_ERROR);
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
	//Function for permanently deleting all songs
	function permanently_delete_songs($db)
	{
		$debug=array();
		$songs=get_deleted_songs($db);
		foreach($songs as $song)
		{
			$debug[]=permanently_delete_song($db,$song->getID());
		}
		if(!in_array(false,$debug))
		{
			return true;
		}
		return false;
	}
?>
<?php
	//Request functions
	
	//Function for inserting a request
	function insert_request($db,$name,$ip,$mode,$info,$comment)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function insert_request is not a valid database.",E_USER_ERROR);
			return false;
		}
		$insertmode=SQLITE3_TEXT;
		switch($mode)
		{
			case 0:
			$statement=$db->prepare("INSERT INTO requests(name,ip,mode,songid,time,comment) VALUES (?,?,?,?,?,?)");
			$insertmode=SQLITE3_INTEGER;
			break;
			
			case 1:
			$statement=$db->prepare("INSERT INTO requests(name,ip,mode,songtext,time,comment) VALUES (?,?,?,?,?,?)");
			break;
			
			case 2:
			$statement=$db->prepare("INSERT INTO requests(name,ip,mode,custom,time,comment) VALUES (?,?,?,?,?,?)");
			break;
			
			default:
			trigger_error("Failed to generate statement in function insert_request.",E_USER_ERROR);
			return false;
			break;
		}
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$name,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(2,$ip,SQLITE3_TEXT);
				if($debug !== false)
				{
					$debug=$statement->bindValue(3,$mode,SQLITE3_INTEGER);
					if($debug !== false)
					{
						$debug=$statement->bindValue(4,$info,$insertmode);
						if($debug !== false)
						{
							$debug=$statement->bindValue(5,time(),SQLITE3_INTEGER);
							if($debug !== false)
							{
								$debug=$statement->bindValue(6,$comment,SQLITE3_TEXT);
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
									trigger_error("Failed to execute statement in function insert_request.",E_USER_ERROR);
									goto failure;
								}
							}
						}
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function insert_request.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function insert_request.",E_USER_ERROR);
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
	//Function for updating request name
	function update_request_name($db,$id,$name)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function update_request_name is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE requests SET name = ? WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(2,$name,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
					trigger_error("Failed to execute statement in function update_request_name.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function update_request_name.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function update_request_name.",E_USER_ERROR);
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
	//Function for updating request comment
	function update_request_comment($db,$id,$comment)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function update_request_name is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE requests SET comment = ? WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(2,$comment,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
					trigger_error("Failed to execute statement in function update_request_comment.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function update_request_comment.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function update_request_comment.",E_USER_ERROR);
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
	//Function for updating request status
	function update_request_status($db,$id,$status,$response)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function update_request_status is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE requests SET comment = ? WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(2,$comment,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
					trigger_error("Failed to execute statement in function update_request_status.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function update_request_status.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function update_request_status.",E_USER_ERROR);
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
	//Function for converting request from ID mode to TEXT mode
	function convert_id_to_text($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function convert_id_to_text is not a valid database.",E_USER_ERROR);
			return false;
		}
		$req=get_request($id);
		if($req === false)
		{
		}
		$song=$req->getSong();
		if($req === false)
		{
		}
		$statement=$db->prepare("UPDATE requests SET mode = 1, songid = NULL, songtext = ? WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(2,$song->getRawDetails(),SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
					trigger_error("Failed to execute statement in function convert_id_to_text.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function convert_id_to_text.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function convert_id_to_text.",E_USER_ERROR);
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
	//Function for deleting a request
	function delete_request($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function delete_request is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM requests WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
				trigger_error("Failed to execute statement in function delete_request.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function delete_request.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function delete_request.",E_USER_ERROR);
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
	//Function for getting all requests
	function get_all_requests($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_requests is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Initialize set of defaults
		$requests=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,name,ip,mode,songid,songtext,custom,time,status,comment,response FROM requests");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Set up data format
					$request=array("id"=>-1,"name"=>"ERROR","ip"=>"0.0.0.0","mode"=>2,"details"=>"Failed to obtain request. Treat the MRS to the Floppy Disk Avalance.","time"=>0,"status"=>0,"comment"=>NULL,"response"=>NULL);
					//Get data from result
					if(isset($entry["ID"]))
					{
						$request["id"]=$entry["ID"];
					}
					if(isset($entry["Name"]))
					{
						$request["name"]=$entry["Name"];
					}
					if(isset($entry["IP"]))
					{
						$request["ip"]=$entry["IP"];
					}
					if(isset($entry["Mode"]))
					{
						$request["mode"]=$entry["Mode"];
					}
					if(isset($entry["Time"]))
					{
						$request["time"]=$entry["Time"];
					}
					if(isset($entry["Status"]))
					{
						$request["status"]=$entry["Status"];
					}
					if(isset($entry["Comment"]))
					{
						$request["comment"]=$entry["Comment"];
					}
					if(isset($entry["Response"]))
					{
						$request["response"]=$entry["Response"];
					}
					switch($request["mode"])
					{
						case 0:
						if(isset($entry["SongID"]))
						{
							$request["details"]=$entry["SongID"];
						}
						else
						{
							trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							$request["mode"]=2;
						}
						break;
						
						case 1:
						if(isset($entry["SongText"]))
						{
							$request["details"]=$entry["SongText"];
						}
						else
						{
							trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							$request["mode"]=2;
						}
						break;
						
						case 2:
						if(isset($entry["Custom"]))
						{
							$request["details"]=$entry["Custom"];
						}
						else
						{
							trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
						}
						break;
						
						default:
						trigger_error("Invalid request mode. Defaulting output and continuing, expect problems.",E_USER_WARNING);
						$request["mode"]=2;
						break;
					}
					//Create request object
					$requestobject=new Request($request["id"],$request["name"],$request["ip"],$request["mode"],$request["details"],$request["time"],$request["status"],$request["comment"],$request["response"]);
					//Add object to list
					$requests[]=$requestobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $requests;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_all_requests.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_requests.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $requests;
	}
	//Function for getting a request
	function get_request($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_request is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Set up default
		$request=false;
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT name,ip,mode,songid,songtext,custom,time,status,comment,response FROM requests WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$request=array("id"=>$id,"name"=>"ERROR","ip"=>"0.0.0.0","mode"=>2,"details"=>"Failed to obtain request. Treat the MRS to the Floppy Disk Avalance.","time"=>0,"status"=>0,"comment"=>NULL,"response"=>NULL);
						//Get data from result
						if(isset($entry["Name"]))
						{
							$request["name"]=$entry["Name"];
						}
						if(isset($entry["IP"]))
						{
							$request["ip"]=$entry["IP"];
						}
						if(isset($entry["Mode"]))
						{
							$request["mode"]=$entry["Mode"];
						}
						if(isset($entry["Time"]))
						{
							$request["time"]=$entry["Time"];
						}
						if(isset($entry["Status"]))
						{
							$request["status"]=$entry["Status"];
						}
						if(isset($entry["Comment"]))
						{
							$request["comment"]=$entry["Comment"];
						}
						if(isset($entry["Response"]))
						{
							$request["response"]=$entry["Response"];
						}
						switch($request["mode"])
						{
							case 0:
							if(isset($entry["SongID"]))
							{
								$request["details"]=$entry["SongID"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
								$request["mode"]=2;
							}
							break;
							
							case 1:
							if(isset($entry["SongText"]))
							{
								$request["details"]=$entry["SongText"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
								$request["mode"]=2;
							}
							break;
							
							case 2:
							if(isset($entry["Custom"]))
							{
								$request["details"]=$entry["Custom"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							}
							break;
							
							default:
							trigger_error("Invalid request mode. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							$request["mode"]=2;
							break;
						}
						//Create request object
						$requestobject=new Request($request["id"],$request["name"],$request["ip"],$request["mode"],$request["details"],$request["time"],$request["status"],$request["comment"],$request["response"]);
						//Add object to list
						$request=$requestobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $request;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_request.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_request.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_request.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $request;
	}
	//Function for getting all requests from a user
	function get_requests_by_user($db,$username)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_requests_by_user is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Set up default
		$requests=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,ip,mode,songid,songtext,custom,time,status,comment,response FROM requests WHERE name = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$username,SQLITE3_TEXT);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$request=array("id"=>-1,"name"=>$username,"ip"=>"0.0.0.0","mode"=>2,"details"=>"Failed to obtain request. Treat the MRS to the Floppy Disk Avalance.","time"=>0,"status"=>0,"comment"=>NULL,"response"=>NULL);
						//Get data from result
						if(isset($entry["ID"]))
						{
							$request["id"]=$entry["ID"];
						}
						if(isset($entry["IP"]))
						{
							$request["ip"]=$entry["IP"];
						}
						if(isset($entry["Mode"]))
						{
							$request["mode"]=$entry["Mode"];
						}
						if(isset($entry["Time"]))
						{
							$request["time"]=$entry["Time"];
						}
						if(isset($entry["Status"]))
						{
							$request["status"]=$entry["Status"];
						}
						if(isset($entry["Comment"]))
						{
							$request["comment"]=$entry["Comment"];
						}
						if(isset($entry["Response"]))
						{
							$request["response"]=$entry["Response"];
						}
						switch($request["mode"])
						{
							case 0:
							if(isset($entry["SongID"]))
							{
								$request["details"]=$entry["SongID"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
								$request["mode"]=2;
							}
							break;
							
							case 1:
							if(isset($entry["SongText"]))
							{
								$request["details"]=$entry["SongText"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
								$request["mode"]=2;
							}
							break;
							
							case 2:
							if(isset($entry["Custom"]))
							{
								$request["details"]=$entry["Custom"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							}
							break;
							
							default:
							trigger_error("Invalid request mode. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							$request["mode"]=2;
							break;
						}
						//Create request object
						$requestobject=new Request($request["id"],$request["name"],$request["ip"],$request["mode"],$request["details"],$request["time"],$request["status"],$request["comment"],$request["response"]);
						//Add object to list
						$requests[]=$requestobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $requests;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_requests_by_user.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_requests_by_user.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_requests_by_user.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $requests;
	}
	//Function for getting all requests from an IP
	function get_requests_by_ip($db,$ip)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_requests_by_ip is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Set up default
		$requests=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,name,mode,songid,songtext,custom,time,status,comment,response FROM requests WHERE ip = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$ip,SQLITE3_TEXT);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$request=array("id"=>-1,"name"=>"ERROR","ip"=>$ip,"mode"=>2,"details"=>"Failed to obtain request. Treat the MRS to the Floppy Disk Avalance.","time"=>0,"status"=>0,"comment"=>NULL,"response"=>NULL);
						//Get data from result
						if(isset($entry["ID"]))
						{
							$request["id"]=$entry["ID"];
						}
						if(isset($entry["Name"]))
						{
							$request["name"]=$entry["Name"];
						}
						if(isset($entry["Mode"]))
						{
							$request["mode"]=$entry["Mode"];
						}
						if(isset($entry["Time"]))
						{
							$request["time"]=$entry["Time"];
						}
						if(isset($entry["Status"]))
						{
							$request["status"]=$entry["Status"];
						}
						if(isset($entry["Comment"]))
						{
							$request["comment"]=$entry["Comment"];
						}
						if(isset($entry["Response"]))
						{
							$request["response"]=$entry["Response"];
						}
						switch($request["mode"])
						{
							case 0:
							if(isset($entry["SongID"]))
							{
								$request["details"]=$entry["SongID"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
								$request["mode"]=2;
							}
							break;
							
							case 1:
							if(isset($entry["SongText"]))
							{
								$request["details"]=$entry["SongText"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
								$request["mode"]=2;
							}
							break;
							
							case 2:
							if(isset($entry["Custom"]))
							{
								$request["details"]=$entry["Custom"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							}
							break;
							
							default:
							trigger_error("Invalid request mode. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							$request["mode"]=2;
							break;
						}
						//Create request object
						$requestobject=new Request($request["id"],$request["name"],$request["ip"],$request["mode"],$request["details"],$request["time"],$request["status"],$request["comment"],$request["response"]);
						//Add object to list
						$requests[]=$requestobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $requests;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_requests_by_ip.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_requests_by_ip.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_requests_by_ip.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $requests;
	}
	//Function for getting all requests of a specific status
	function get_requests_by_status($db,$status)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_requests_by_status is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Set up default
		$requests=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,name,ip,mode,songid,songtext,custom,time,comment,response FROM requests WHERE status = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$status,SQLITE3_INTEGER);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$request=array("id"=>-1,"name"=>"ERROR","ip"=>"0.0.0.0","mode"=>2,"details"=>"Failed to obtain request. Treat the MRS to the Floppy Disk Avalance.","time"=>0,"status"=>$status,"comment"=>NULL,"response"=>NULL);
						//Get data from result
						if(isset($entry["ID"]))
						{
							$request["id"]=$entry["ID"];
						}
						if(isset($entry["Name"]))
						{
							$request["name"]=$entry["Name"];
						}
						if(isset($entry["IP"]))
						{
							$request["ip"]=$entry["IP"];
						}
						if(isset($entry["Mode"]))
						{
							$request["mode"]=$entry["Mode"];
						}
						if(isset($entry["Time"]))
						{
							$request["time"]=$entry["Time"];
						}
						if(isset($entry["Comment"]))
						{
							$request["comment"]=$entry["Comment"];
						}
						if(isset($entry["Response"]))
						{
							$request["response"]=$entry["Response"];
						}
						switch($request["mode"])
						{
							case 0:
							if(isset($entry["SongID"]))
							{
								$request["details"]=$entry["SongID"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
								$request["mode"]=2;
							}
							break;
							
							case 1:
							if(isset($entry["SongText"]))
							{
								$request["details"]=$entry["SongText"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
								$request["mode"]=2;
							}
							break;
							
							case 2:
							if(isset($entry["Custom"]))
							{
								$request["details"]=$entry["Custom"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							}
							break;
							
							default:
							trigger_error("Invalid request mode. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							$request["mode"]=2;
							break;
						}
						//Create request object
						$requestobject=new Request($request["id"],$request["name"],$request["ip"],$request["mode"],$request["details"],$request["time"],$request["status"],$request["comment"],$request["response"]);
						//Add object to list
						$requests[]=$requestobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $requests;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_requests_by_status.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_requests_by_status.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_requests_by_status.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $requests;
	}
	
	//Function for getting all deleted requests
	function get_deleted_requests($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_deleted_requests is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Initialize set of defaults
		$requests=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,name,ip,mode,songid,songtext,custom,time,status,comment,response FROM oldrequests");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Set up data format
					$request=array("id"=>-1,"name"=>"ERROR","ip"=>"0.0.0.0","mode"=>2,"details"=>"Failed to obtain request. Treat the MRS to the Floppy Disk Avalance.","time"=>0,"status"=>0,"comment"=>NULL,"response"=>NULL);
					//Get data from result
					if(isset($entry["ID"]))
					{
						$request["id"]=$entry["ID"];
					}
					if(isset($entry["Name"]))
					{
						$request["name"]=$entry["Name"];
					}
					if(isset($entry["IP"]))
					{
						$request["ip"]=$entry["IP"];
					}
					if(isset($entry["Mode"]))
					{
						$request["mode"]=$entry["Mode"];
					}
					if(isset($entry["Time"]))
					{
						$request["time"]=$entry["Time"];
					}
					if(isset($entry["Status"]))
					{
						$request["status"]=$entry["Status"];
					}
					if(isset($entry["Comment"]))
					{
						$request["comment"]=$entry["Comment"];
					}
					if(isset($entry["Response"]))
					{
						$request["response"]=$entry["Response"];
					}
					switch($request["mode"])
					{
						case 0:
						if(isset($entry["SongID"]))
						{
							$request["details"]=$entry["SongID"];
						}
						else
						{
							trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							$request["mode"]=2;
						}
						break;
						
						case 1:
						if(isset($entry["SongText"]))
						{
							$request["details"]=$entry["SongText"];
						}
						else
						{
							trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							$request["mode"]=2;
						}
						break;
						
						case 2:
						if(isset($entry["Custom"]))
						{
							$request["details"]=$entry["Custom"];
						}
						else
						{
							trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
						}
						break;
						
						default:
						trigger_error("Invalid request mode. Defaulting output and continuing, expect problems.",E_USER_WARNING);
						$request["mode"]=2;
						break;
					}
					//Create request object
					$requestobject=new Request($request["id"],$request["name"],$request["ip"],$request["mode"],$request["details"],$request["time"],$request["status"],$request["comment"],$request["response"]);
					//Add object to list
					$requests[]=$requestobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $requests;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_deleted_requests.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_deleted_requests.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $requests;
	}
	//Function for getting a deleted request
	function get_deleted_request($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_deleted_request is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Set up default
		$request=false;
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT name,ip,mode,songid,songtext,custom,time,status,comment,response FROM oldrequests WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$request=array("id"=>$id,"name"=>"ERROR","ip"=>"0.0.0.0","mode"=>2,"details"=>"Failed to obtain request. Treat the MRS to the Floppy Disk Avalance.","time"=>0,"status"=>0,"comment"=>NULL,"response"=>NULL);
						//Get data from result
						if(isset($entry["Name"]))
						{
							$request["name"]=$entry["Name"];
						}
						if(isset($entry["IP"]))
						{
							$request["ip"]=$entry["IP"];
						}
						if(isset($entry["Mode"]))
						{
							$request["mode"]=$entry["Mode"];
						}
						if(isset($entry["Time"]))
						{
							$request["time"]=$entry["Time"];
						}
						if(isset($entry["Status"]))
						{
							$request["status"]=$entry["Status"];
						}
						if(isset($entry["Comment"]))
						{
							$request["comment"]=$entry["Comment"];
						}
						if(isset($entry["Response"]))
						{
							$request["response"]=$entry["Response"];
						}
						switch($request["mode"])
						{
							case 0:
							if(isset($entry["SongID"]))
							{
								$request["details"]=$entry["SongID"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
								$request["mode"]=2;
							}
							break;
							
							case 1:
							if(isset($entry["SongText"]))
							{
								$request["details"]=$entry["SongText"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
								$request["mode"]=2;
							}
							break;
							
							case 2:
							if(isset($entry["Custom"]))
							{
								$request["details"]=$entry["Custom"];
							}
							else
							{
								trigger_error("Appropriate song details were blank. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							}
							break;
							
							default:
							trigger_error("Invalid request mode. Defaulting output and continuing, expect problems.",E_USER_WARNING);
							$request["mode"]=2;
							break;
						}
						//Create request object
						$requestobject=new Request($request["id"],$request["name"],$request["ip"],$request["mode"],$request["details"],$request["time"],$request["status"],$request["comment"],$request["response"]);
						//Add object to list
						$request=$requestobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $request;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_deleted_request.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_deleted_request.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_deleted_request.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $request;
	}
	//Function for restoring a deleted request
	function restore_deleted_request($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function restore_deleted_request is not a valid database.",E_USER_ERROR);
			return false;
		}
		$request=get_deleted_request($db,$id);
		$songid=NULL;
		$songtext=NULL;
		$custom=NULL;
		switch($request->getMode())
		{
			case 0:
			$songid=$request->getSong();
			break;
			
			case 0:
			$songtext=$request->getSong();
			break;
			
			case 2:
			$custom=$request->getSong();
			break;
		}
		if($request !== false)
		{
			$statement=$db->prepare("INSERT INTO requests(name,ip,mode,songid,songtext,custom,time,status,comment,response) VALUES (?,?,?,?,?,?,?,?,?,?)");
			if($statement !== false)
			{
				//Bind variables
				$debug=$statement->bindValue(1,$request->getName(),SQLITE3_TEXT);
				if($debug !== false)
				{
					$debug=$statement->bindValue(2,$request->getIP(),SQLITE3_TEXT);
					if($debug !== false)
					{
						$debug=$statement->bindValue(3,$request->getMode(),SQLITE3_INTEGER);
						if($debug !== false)
						{
							$debug=$statement->bindValue(4,$songid,SQLITE3_INTEGER);
							if($debug !== false)
							{
								$debug=$statement->bindValue(5,$songtext,SQLITE3_TEXT);
								if($debug !== false)
								{
									$debug=$statement->bindValue(6,$custom,SQLITE3_TEXT);
									if($debug !== false)
									{
										$debug=$statement->bindValue(7,$request->getTime(),SQLITE3_INTEGER);
										if($debug !== false)
										{
											$debug=$statement->bindValue(8,$request->getStatus(),SQLITE3_INTEGER);
											if($debug !== false)
											{
												$debug=$statement->bindValue(9,$request->getComment(),SQLITE3_TEXT);
												if($debug !== false)
												{
													$debug=$statement->bindValue(10,$request->getResponse(),SQLITE3_TEXT);
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
														trigger_error("Failed to execute statement in function restore_deleted_request.",E_USER_ERROR);
														goto failure;
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
				//Failed to bind variables to statement
				trigger_error("Failed to bind values to statement in function restore_deleted_request.",E_USER_ERROR);
				goto failure;
			}
			//Failed to create statement
			trigger_error("Failed to create statement in function restore_deleted_request.",E_USER_ERROR);
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
		return false;
	}
	//Function for restoring all deleted requests
	function restore_deleted_requests($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_deleted_request is not a valid database.",E_USER_ERROR);
			return false;
		}
		$debug=array();
		$reqs=get_deleted_requests($db);
		foreach($reqs as $req)
		{
			$debug[]=restore_deleted_request($db,$req->getID());
		}
		if(!in_array(false,$debug))
		{
			return true;
		}
		return false;
	}
	//Function for permanently deleting a request
	function permanently_delete_request($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function permanently_delete_request is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM oldrequests WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
				trigger_error("Failed to execute statement in function permanently_delete_request.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function permanently_delete_request.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function permanently_delete_request.",E_USER_ERROR);
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
	//Function for permanently deleting all requests
	function permanently_delete_requests($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_deleted_request is not a valid database.",E_USER_ERROR);
			return false;
		}
		$debug=array();
		$reqs=get_deleted_requests($db);
		foreach($reqs as $req)
		{
			$debug[]=permanently_delete_request($db,$req->getID());
		}
		if(!in_array(false,$debug))
		{
			return true;
		}
		return false;
	}
?>
<?php
	//Report functions
	
	//Function for inserting a report
	function insert_report($db,$ip,$request,$reason)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function insert_report is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("INSERT INTO reports(ip,request,reason) VALUES (?,?,?)");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$ip,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(3,$reason,SQLITE3_TEXT);
				if($debug !== false)
				{
					$debug=$statement->bindValue(2,$request,SQLITE3_INTEGER);
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
						trigger_error("Failed to execute statement in function insert_report.",E_USER_ERROR);
						goto failure;
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function insert_report.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function insert_report.",E_USER_ERROR);
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
	//Function for marking a report as seen
	function mark_report_as_viewed($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function mark_report_as_viewed is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE reports SET unread = 1 WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
				trigger_error("Failed to execute statement in function mark_report_as_viewed.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function mark_report_as_viewed.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function mark_report_as_viewed.",E_USER_ERROR);
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
	//Function for deleting a report
	function delete_report($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function delete_report is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM reports WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
				trigger_error("Failed to execute statement in function delete_report.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function delete_report.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function delete_report.",E_USER_ERROR);
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
	//Function for getting all reports
	function get_reports($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_reports is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Initialize set of defaults
		$reports=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,ip,request,reason,unread FROM reports");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Set up data format
					$report=array("id"=>-1,"ip"=>"0.0.0.0","request"=>1,"reason"=>"Failed to obtain report. Someone probably tortured the MRS with the latest Prepubescent Carl \"hit\".","unread"=>0);
					//Get data from result
					if(isset($entry["ID"]))
					{
						$report["id"]=$entry["ID"];
					}
					if(isset($entry["IP"]))
					{
						$report["ip"]=$entry["IP"];
					}
					if(isset($entry["Request"]))
					{
						$report["request"]=$entry["Request"];
					}
					if(isset($entry["Reason"]))
					{
						$report["reason"]=$entry["Reason"];
					}
					if(isset($entry["Unread"]))
					{
						$report["unread"]=$entry["Unread"];
					}
					//Create report object
					$reportobject=new Report($report["id"],$report["ip"],$report["request"],$report["reason"],$report["unread"]);
					//Add object to list
					$reports[]=$reportobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $reports;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_reports.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_reports.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $reports;
	}
	//Function for getting a report
	function get_report($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_report is not a valid database.",E_USER_ERROR);
			return false;
		}
		//Set up default
		$report=false;
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip,request,reason,unread FROM reports WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$report=array("id"=>$id,"ip"=>"0.0.0.0","request"=>1,"reason"=>"Failed to obtain report. Someone probably tortured the MRS with the latest Prepubescent Carl \"hit\".","unread"=>0);
						//Get data from result
						if(isset($entry["IP"]))
						{
							$report["ip"]=$entry["IP"];
						}
						if(isset($entry["Request"]))
						{
							$report["request"]=$entry["Request"];
						}
						if(isset($entry["Reason"]))
						{
							$report["reason"]=$entry["Reason"];
						}
						if(isset($entry["Unread"]))
						{
							$report["unread"]=$entry["Unread"];
						}
						//Create report object
						$reportobject=new Report($report["id"],$report["ip"],$report["request"],$report["reason"],$report["unread"]);
						//Add object to list
						$report=$reportobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $request;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_report.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_report.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_report.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $request;
	}
	//Function for getting unread reports
	function get_unread_reports($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_unread_reports is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Initialize set of defaults
		$reports=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,ip,request,reason FROM reports WHERE unread = 0");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Set up data format
					$report=array("id"=>-1,"ip"=>"0.0.0.0","request"=>1,"reason"=>"Failed to obtain report. Someone probably tortured the MRS with the latest Prepubescent Carl \"hit\".","unread"=>0);
					//Get data from result
					if(isset($entry["ID"]))
					{
						$report["id"]=$entry["ID"];
					}
					if(isset($entry["IP"]))
					{
						$report["ip"]=$entry["IP"];
					}
					if(isset($entry["Request"]))
					{
						$report["request"]=$entry["Request"];
					}
					if(isset($entry["Reason"]))
					{
						$report["reason"]=$entry["Reason"];
					}
					//Create report object
					$reportobject=new Report($report["id"],$report["ip"],$report["request"],$report["reason"],$report["unread"]);
					//Add object to list
					$reports[]=$reportobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $reports;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_unread_reports.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_unread_reports.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $reports;
	}
?>
<?php
	//Banning functions
	
	//Function for inserting a username ban
	function insert_uname_ban($db,$uname,$until,$reason=NULL)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function insert_uname_ban is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("INSERT INTO usernames(username,date,until,reason) VALUES (?,?,?,?)");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$uname,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(4,$reason,SQLITE3_TEXT);
				if($debug !== false)
				{
					$debug=$statement->bindValue(2,$date,SQLITE3_INTEGER);
					if($debug !== false)
					{
						$debug=$statement->bindValue(3,$until,SQLITE3_INTEGER);
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
							trigger_error("Failed to execute statement in function insert_uname_ban.",E_USER_ERROR);
							goto failure;
						}
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function insert_uname_ban.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function insert_uname_ban.",E_USER_ERROR);
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
	//Function for updating a username ban
	function update_uname_ban($db,$id,$until,$reason)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function update_uname_ban is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE usernames SET until = ?, reason = ? WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(3,$id,SQLITE3_INTEGER);
			if($debug !== false)
			{
				$debug=$statement->bindValue(1,$until,SQLITE3_INTEGER);
				if($debug !== false)
				{
					$debug=$statement->bindValue(2,$reason,SQLITE3_TEXT);
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
						trigger_error("Failed to execute statement in function update_uname_ban.",E_USER_ERROR);
						goto failure;
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function update_uname_ban.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function update_uname_ban.",E_USER_ERROR);
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
	//Function for deleting a username ban
	function lift_uname_ban($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function lift_uname_ban is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM usernames WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
				trigger_error("Failed to execute statement in function lift_uname_ban.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function lift_uname_ban.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function lift_uname_ban.",E_USER_ERROR);
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
	//Function for getting all username bans
	function get_all_uname_bans($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_uname_bans is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Initialize set of defaults
		$bans=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,username,date,until,reason FROM usernames");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Set up data format
					$ban=array("id"=>-1,"username"=>"ERROR","date"=>0,"until"=>PHP_INT_MAX,"reason"=>"Failed to obtain ban information. Where are all the geese when you need them?!?");
					//Get data from result
					if(isset($entry["ID"]))
					{
						$report["id"]=$entry["ID"];
					}
					if(isset($entry["Username"]))
					{
						$report["username"]=$entry["Username"];
					}
					if(isset($entry["Date"]))
					{
						$report["date"]=$entry["Date"];
					}
					if(isset($entry["Reason"]))
					{
						$report["reason"]=$entry["Reason"];
					}
					if(isset($entry["Until"]))
					{
						$report["until"]=$entry["Until"];
					}
					//Create report object
					$banobject=new Ban($ban["id"],$ban["username"],$report["date"],$report["until"],$report["reason"]);
					//Add object to list
					$bans[]=$banobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $bans;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_all_uname_bans.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_uname_bans.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $bans;
	}
	//Function for getting all bans for a username
	function get_all_bans_for_uname($db,$uname)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_bans_for_uname is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Set up default
		$bans=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,date,reason,until FROM usernames WHERE username = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$uname,SQLITE3_TEXT);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$ban=array("id"=>-1,"username"=>$uname,"date"=>0,"until"=>PHP_INT_MAX,"reason"=>"Failed to obtain ban information. Where are all the geese when you need them?!?");
						//Get data from result
						if(isset($entry["ID"]))
						{
							$report["id"]=$entry["ID"];
						}
						if(isset($entry["Date"]))
						{
							$report["date"]=$entry["Date"];
						}
						if(isset($entry["Reason"]))
						{
							$report["reason"]=$entry["Reason"];
						}
						if(isset($entry["Until"]))
						{
							$report["until"]=$entry["Until"];
						}
						//Create report object
						$banobject=new Ban($ban["id"],$ban["username"],$report["date"],$report["until"],$report["reason"]);
						//Add object to list
						$bans[]=$banobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $bans;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_all_bans_for_uname.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_all_bans_for_uname.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_bans_for_uname.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $bans;
	}
	//Function for getting all active bans for a username
	function get_all_active_bans_for_uname($db,$uname)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_bans_for_uname is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Set up default
		$bans=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,date,reason,until FROM usernames WHERE username = ? AND (until > ? OR until = 0)");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$uname,SQLITE3_TEXT);
			if($debug !== false)
			{
				//Bind variables to statement
				$debug=$statement->bindValue(2,time(),SQLITE3_INTEGER);
				if($debug !== false)
				{
					//Execute statement
					$result=$statement->execute();
					if($result !== false)
					{
						//Loop through all entries
						while($entry=$result->fetchArray(SQLITE3_ASSOC))
						{
							//Set up data format
							$ban=array("id"=>-1,"username"=>$uname,"date"=>0,"until"=>PHP_INT_MAX,"reason"=>"Failed to obtain ban information. Where are all the geese when you need them?!?");
							//Get data from result
							if(isset($entry["ID"]))
							{
								$report["id"]=$entry["ID"];
							}
							if(isset($entry["Date"]))
							{
								$report["date"]=$entry["Date"];
							}
							if(isset($entry["Reason"]))
							{
								$report["reason"]=$entry["Reason"];
							}
							if(isset($entry["Until"]))
							{
								$report["until"]=$entry["Until"];
							}
							//Create report object
							$banobject=new Ban($ban["id"],$ban["username"],$report["date"],$report["until"],$report["reason"]);
							//Add object to list
							$bans[]=$banobject;
						}
						//Close statement
						$statement->close();
						unset($statement);
						return $bans;
					}
					//Failed to execute statement
					trigger_error("Failed to execute statement in function get_all_bans_for_uname.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_all_bans_for_uname.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_bans_for_uname.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $bans;
	}
	
	//Function for inserting an IP ban
	function insert_ip_ban($db,$ip,$until,$reason=NULL)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function insert_ip_ban is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("INSERT INTO ips(ip,date,until,reason) VALUES (?,?,?,?)");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$ip,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(4,$reason,SQLITE3_TEXT);
				if($debug !== false)
				{
					$debug=$statement->bindValue(2,$date,SQLITE3_INTEGER);
					if($debug !== false)
					{
						$debug=$statement->bindValue(3,$until,SQLITE3_INTEGER);
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
							trigger_error("Failed to execute statement in function insert_ip_ban.",E_USER_ERROR);
							goto failure;
						}
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function insert_ip_ban.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function insert_ip_ban.",E_USER_ERROR);
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
	//Function for updating an IP ban
	function update_ip_ban($db,$id,$until,$reason)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function update_ip_ban is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("UPDATE ips SET until = ?, reason = ? WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(3,$id,SQLITE3_INTEGER);
			if($debug !== false)
			{
				$debug=$statement->bindValue(1,$until,SQLITE3_INTEGER);
				if($debug !== false)
				{
					$debug=$statement->bindValue(2,$reason,SQLITE3_TEXT);
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
						trigger_error("Failed to execute statement in function update_ip_ban.",E_USER_ERROR);
						goto failure;
					}
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function update_ip_ban.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function update_ip_ban.",E_USER_ERROR);
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
	//Function for deleting an IP ban
	function lift_ip_ban($db,$id)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function lift_ip_ban is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM ips WHERE id = ?");
		if($statement !== false)
		{
			//Bind variables
			$debug=$statement->bindValue(1,$id,SQLITE3_INTEGER);
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
				trigger_error("Failed to execute statement in function lift_ip_ban.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function lift_ip_ban.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function lift_ip_ban.",E_USER_ERROR);
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
	//Function for getting all IP bans
	function get_all_ip_bans($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_ip_bans is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Initialize set of defaults
		$bans=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,ip,date,until,reason FROM ips");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Set up data format
					$ban=array("id"=>-1,"ip"=>"ERROR","date"=>0,"until"=>PHP_INT_MAX,"reason"=>"Failed to obtain ban information. Where are all the geese when you need them?!?");
					//Get data from result
					if(isset($entry["ID"]))
					{
						$report["id"]=$entry["ID"];
					}
					if(isset($entry["IP"]))
					{
						$report["ip"]=$entry["IP"];
					}
					if(isset($entry["Date"]))
					{
						$report["date"]=$entry["Date"];
					}
					if(isset($entry["Reason"]))
					{
						$report["reason"]=$entry["Reason"];
					}
					if(isset($entry["Until"]))
					{
						$report["until"]=$entry["Until"];
					}
					//Create report object
					$banobject=new Ban($ban["id"],$ban["ip"],$report["date"],$report["until"],$report["reason"]);
					//Add object to list
					$bans[]=$banobject;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $bans;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_all_ip_bans.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_ip_bans.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $bans;
	}
	//Function for getting all bans for an IP
	function get_all_bans_for_ip($db,$ip)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_bans_for_ip is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Set up default
		$bans=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,date,reason,until FROM ips WHERE ip = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$ip,SQLITE3_TEXT);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Set up data format
						$ban=array("id"=>-1,"ip"=>$ip,"date"=>0,"until"=>PHP_INT_MAX,"reason"=>"Failed to obtain ban information. Where are all the geese when you need them?!?");
						//Get data from result
						if(isset($entry["ID"]))
						{
							$report["id"]=$entry["ID"];
						}
						if(isset($entry["Date"]))
						{
							$report["date"]=$entry["Date"];
						}
						if(isset($entry["Reason"]))
						{
							$report["reason"]=$entry["Reason"];
						}
						if(isset($entry["Until"]))
						{
							$report["until"]=$entry["Until"];
						}
						//Create report object
						$banobject=new Ban($ban["id"],$ban["ip"],$report["date"],$report["until"],$report["reason"]);
						//Add object to list
						$bans[]=$banobject;
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $bans;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_all_bans_for_ip.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_all_bans_for_ip.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_bans_for_ip.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $bans;
	}
	//Function for getting all active bans for an IP
	function get_all_active_bans_for_ip($db,$ip)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_active_bans_for_ip is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Set up default
		$bans=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT id,date,reason,until FROM ips WHERE ip = ? AND (until > ? OR until = 0)");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$ip,SQLITE3_TEXT);
			if($debug !== false)
			{
				//Bind variables to statement
				$debug=$statement->bindValue(2,time(),SQLITE3_INTEGER);
				if($debug !== false)
				{
					//Execute statement
					$result=$statement->execute();
					if($result !== false)
					{
						//Loop through all entries
						while($entry=$result->fetchArray(SQLITE3_ASSOC))
						{
							//Set up data format
							$ban=array("id"=>-1,"ip"=>$ip,"date"=>0,"until"=>PHP_INT_MAX,"reason"=>"Failed to obtain ban information. Where are all the geese when you need them?!?");
							//Get data from result
							if(isset($entry["ID"]))
							{
								$report["id"]=$entry["ID"];
							}
							if(isset($entry["Date"]))
							{
								$report["date"]=$entry["Date"];
							}
							if(isset($entry["Reason"]))
							{
								$report["reason"]=$entry["Reason"];
							}
							if(isset($entry["Until"]))
							{
								$report["until"]=$entry["Until"];
							}
							//Create report object
							$banobject=new Ban($ban["id"],$ban["ip"],$report["date"],$report["until"],$report["reason"]);
							//Add object to list
							$bans[]=$banobject;
						}
						//Close statement
						$statement->close();
						unset($statement);
						return $bans;
					}
					//Failed to execute statement
					trigger_error("Failed to execute statement in function get_all_active_bans_for_ip.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_all_active_bans_for_ip.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_active_bans_for_ip.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $bans;
	}
	
	//Banhammer: automatic ban system
	function banhammer($reason,$immediate=false,$login=false)
	{
		if($immediate === true)
		{
			$db=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
			$autoban=get_setting($db,"autoban");
			close_db($db);
			if($autoban != "y")
			{
				return true;
			}
			$db=open_db("db/bans.sqlite",SQLITE3_OPEN_READWRITE);
			$bancount=get_all_bans_for_ip($db,$_SERVER['REMOTE_ADDR']);
			$days=max(1,$bancount*2);
			if($days > 30)
			{
				$bantime=0;
			}
			else
			{
				$bantime=time()+$days*24*60*60;
			}
			insert_ip_ban($db,$_SERVER['REMOTE_ADDR'],$bantime,"Automatic ban: $reason");
			if(!empty($_SESSION['username']))
			{
				$bancount=get_all_bans_for_user($db,$_SESSION['username']);
				$days=max(1,$bancount*2);
				if($days > 30)
				{
					$bantime=0;
				}
				else
				{
					$bantime=time()+$days*24*60*60;
				}
				insert_uname_ban($db,$_SESSION['username'],$bantime,"Automatic ban: $reason");
			}
			super_banhammer();
		}
		else
		{
			$db=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
			if($login === true)
			{
				$failcount=get_setting($db,"failedlogin");
			}
			else
			{
				$failcount=get_setting($db,"disallowcount");
			}
			$autoban=get_setting($db,"autoban");
			close_db($db);
			if($autoban != "y")
			{
				return true;
			}
			if(!empty($_SESSION['banhammer']))
			{
				$_SESSION['banhammer']++;
			}
			else
			{
				$_SESSION['banhammer']=1;
			}
			if($_SESSION['banhammer'] >= $failcount)
			{
				$db=open_db("db/bans.sqlite",SQLITE3_OPEN_READWRITE);
				$bancount=get_all_bans_for_ip($db,$_SERVER['REMOTE_ADDR']);
				$days=max(1,$bancount*2);
				if($days > 30)
				{
					$bantime=0;
				}
				else
				{
					$bantime=time()+$days*24*60*60;
				}
				insert_ip_ban($db,$_SERVER['REMOTE_ADDR'],$bantime,"Automatic ban: $reason");
				if(!empty($_SESSION['username']))
				{
					$bancount=get_all_bans_for_user($db,$_SESSION['username']);
					$days=max(1,$bancount*2);
					if($days > 30)
					{
						$bantime=0;
					}
					else
					{
						$bantime=time()+$days*24*60*60;
					}
					insert_uname_ban($db,$_SESSION['username'],$bantime,"Automatic ban: $reason");
				}
				super_banhammer();
			}
		}
	}
	//Super Banhammer: follows system user-IP mappings and bans them accordingly
	function super_banhammer()
	{
		$db=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
		$superban=get_setting($db,"superban");
		close_db($db);
		if($superban != "y")
		{
			return true;
		}
		$db=open_db("db/bans.sqlite",SQLITE3_OPEN_READWRITE);
		$ipbans=get_all_ip_bans($db);
		$userbans=get_all_uname_bans($db);
		if(count($ipbans) > 0)
		{
			foreach($ipbans as $ban)
			{
				if(is_a($ban,Ban))
				{
					if($ban->getUntil() == 0 || $ban->getUntil() > time())
					{
						$users=get_ip_maps($db,$ban->getItem());
						if(count($users) > 0)
						{
							foreach($users as $user)
							{
								$ubans=get_all_active_bans_for_uname($db,$user);
								if(count($ubans) > 0)
								{
									foreach($ubans as $uban)
									{
										if(is_a($uban,Ban))
										{
											if($uban->getUntil() != 0 && $uban->getUntil() < $ban->getUntil())
											{
												insert_uname_ban($db,$user,$ban->getUntil(),"Automatic ban: collateral from ban on IP address \"" . $ban->getItem() . "\"");
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		if(count($userbans) > 0)
		{
			foreach($userbans as $ban)
			{
				if(is_a($ban,Ban))
				{
					if($ban->getUntil() == 0 || $ban->getUntil() > time())
					{
						$ips=get_username_maps($db,$ban->getItem());
						if(count($ips) > 0)
						{
							foreach($ips as $ip)
							{
								$ibans=get_all_active_bans_for_ip($db,$ip);
								if(count($ibans) > 0)
								{
									foreach($ibans as $iban)
									{
										if(is_a($iban,Ban))
										{
											if($iban->getUntil() != 0 && $iban->getUntil() < $ban->getUntil())
											{
												insert_ip_ban($db,$ip,$ban->getUntil(),"Automatic ban: collateral from ban on username \"" . $ban->getItem() . "\"");
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		close_db($db);
	}
	
	//Function for getting all user-IP mappings
	function get_all_maps($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_all_maps is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Initialize set of defaults
		$mappings=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT username,ip,count FROM mappings");
		if($statement !== false)
		{
			//Execute statement
			$result=$statement->execute();
			if($result !== false)
			{
				//Loop through all entries
				while($entry=$result->fetchArray(SQLITE3_ASSOC))
				{
					//Set up data format
					$mapping=array("username"=>"ERROR","ip"=>"0.0.0.0","count"=>0);
					//Get data from result
					if(isset($entry["Username"]))
					{
						$mapping["username"]=$entry["Username"];
					}
					if(isset($entry["IP"]))
					{
						$mapping["ip"]=$entry["IP"];
					}
					if(isset($entry["Count"]))
					{
						$mapping["count"]=$entry["Count"];
					}
					//Add to list
					$mappings[]=$mapping;
				}
				//Close statement
				$statement->close();
				unset($statement);
				return $mappings;
			}
			//Failed to execute statement
			trigger_error("Failed to execute statement in function get_all_maps.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_all_maps.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $mappings;
	}
	//Function for checking if a mapping exists
	function does_map_exist($db,$username,$ip)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function does_map_exist is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Set up default
		$bans=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT count FROM mappings WHERE username = ? AND ip = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$username,SQLITE3_TEXT);
			if($debug !== false)
			{
				$debug=$statement->bindValue(2,$ip,SQLITE3_TEXT);
				if($debug !== false)
				{
					//Execute statement
					$result=$statement->execute();
					if($result !== false)
					{
						//Loop through all entries
						while($entry=$result->fetchArray(SQLITE3_ASSOC))
						{
							//Close statement
							$statement->close();
							unset($statement);
							return true;
						}
						//Close statement
						$statement->close();
						unset($statement);
						return false;
					}
					//Failed to execute statement
					trigger_error("Failed to execute statement in function does_map_exist.",E_USER_ERROR);
					goto failure;
				}
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function does_map_exist.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function does_map_exist.",E_USER_ERROR);
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
	//Function for inserting a new/updating an existing user-IP mapping
	function upsert_map($db,$username,$ip)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function upsert_map is not a valid database.",E_USER_ERROR);
			return false;
		}
		if(does_map_exist($db,$username,$ip) === true)
		{
			$statement=$db->prepare("INSERT INTO mappings(ip,username) VALUES (?,?)");
			if($statement !== false)
			{
				//Bind variables
				$debug=$statement->bindValue(1,$ip,SQLITE3_TEXT);
				if($debug !== false)
				{
					$debug=$statement->bindValue(2,$username,SQLITE3_TEXT);
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
						trigger_error("Failed to execute statement in function upsert_map.",E_USER_ERROR);
						goto failure;
					}
				}
				//Failed to bind variables to statement
				trigger_error("Failed to bind values to statement in function upsert_map.",E_USER_ERROR);
				goto failure;
			}
			//Failed to create statement
			trigger_error("Failed to create statement in function upsert_map.",E_USER_ERROR);
			goto failure;
		}
		else
		{
			$statement=$db->prepare("UPDATE mappings SET count = count + 1 WHERE ip = ? AND username = ?");
			if($statement !== false)
			{
				//Bind variables
				$debug=$statement->bindValue(1,$ip,SQLITE3_TEXT);
				if($debug !== false)
				{
					$debug=$statement->bindValue(2,$username,SQLITE3_TEXT);
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
						trigger_error("Failed to execute statement in function upsert_map.",E_USER_ERROR);
						goto failure;
					}
				}
				//Failed to bind variables to statement
				trigger_error("Failed to bind values to statement in function upsert_map.",E_USER_ERROR);
				goto failure;
			}
			//Failed to create statement
			trigger_error("Failed to create statement in function upsert_map.",E_USER_ERROR);
			goto failure;
		}		
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
	//Function for clearing all user-IP mappings
	function clear_maps($db)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function clear_maps is not a valid database.",E_USER_ERROR);
			return false;
		}
		$statement=$db->prepare("DELETE FROM mappings");
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
			trigger_error("Failed to execute statement in function clear_maps.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function clear_maps.",E_USER_ERROR);
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
	//Function for getting all IPs associated with a username
	function get_ip_maps($db,$ip)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_ip_maps is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Set up default
		$usernames=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT username FROM mappings WHERE ip = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$ip,SQLITE3_TEXT);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Add item to list if it exists
						if(!empty($entry['Username']))
						{
							$usernames[]=$entry['Username'];
						}
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $usernames;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_ip_maps.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_ip_maps.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_ip_maps.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $usernames;
	}
	//Function for getting all usernames associated with an IP
	function get_username_maps($db,$uname)
	{
		if(!is_a($db,"SQLite3"))
		{
			trigger_error("Handle passed to function get_username_maps is not a valid database.",E_USER_ERROR);
			return array();
		}
		//Set up default
		$ips=array();
		//Prepare statement for selecting
		$statement=$db->prepare("SELECT ip FROM mappings WHERE username = ?");
		if($statement !== false)
		{
			//Bind variables to statement
			$debug=$statement->bindValue(1,$uname,SQLITE3_TEXT);
			if($debug !== false)
			{
				//Execute statement
				$result=$statement->execute();
				if($result !== false)
				{
					//Loop through all entries
					while($entry=$result->fetchArray(SQLITE3_ASSOC))
					{
						//Add item to list if it exists
						if(!empty($entry['IP']))
						{
							$ips[]=$entry['IP'];
						}
					}
					//Close statement
					$statement->close();
					unset($statement);
					return $ips;
				}
				//Failed to execute statement
				trigger_error("Failed to execute statement in function get_username_maps.",E_USER_ERROR);
				goto failure;
			}
			//Failed to bind variables to statement
			trigger_error("Failed to bind values to statement in function get_username_maps.",E_USER_ERROR);
			goto failure;
		}
		//Failed to create statement
		trigger_error("Failed to create statement in function get_username_maps.",E_USER_ERROR);
		failure:
		//Close statement if necessary
		if(isset($statement) && is_a($statement,"SQLite3Stmt"))
		{
			$statement->close();
			unset($statement);
		}
		//Exit
		return $ips;
	}
?>
<?php
	//Updating functions
	
	//Function for getting list of upgrade packages
	function get_build_list($mirror)
	{
		$curl=@curl_init();
		if($curl !== false)
		{
			@curl_setopt($curl, CURLOPT_URL, "$mirror/getbuilds.php");
			@curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			@curl_setopt($curl, CURLOPT_HEADER, false);
			$data=@curl_exec($curl);
			if(!empty($data) && !curl_errno($curl) && curl_getinfo($curl,CURLINFO_HTTP_CODE) == 200)
			{
				$data=explode("\r\n",$data);
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Retrieved build codes from mirror.");
				@curl_close($curl);
				return new UpgradeReturn(0,count($data),$data);
			}
			else
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to get build codes from mirror: error code " . curl_errno($curl) . ", HTTP response code " . curl_getinfo($curl,CURLINFO_HTTP_CODE) . ".");
				$return=new UpgradeReturn(2,curl_errno($curl),curl_getinfo($curl,CURLINFO_HTTP_CODE));
				@curl_close($curl);
				return $return;
			}
		}
		else
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to get build codes from mirror: error code " . curl_errno($curl) . ", HTTP response code " . curl_getinfo($curl,CURLINFO_HTTP_CODE) . ".");
			$return=new UpgradeReturn(1);
			@curl_close($curl);
			return $return;
		}
	}
	//Function for getting upgrade package
	function get_upgrade_pack($mirror,$buildcode)
	{
		insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Beginning download of upgrade package \"$buildcode\".");
		if(!file_exists("package.zip"))
		{
			$dfh=@fopen("package.zip",'w+');
			if($dfh)
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Opened destination file \"package.zip\" for writing.");
				$curl=@curl_init();
				if($curl !== false)
				{
					@curl_setopt($curl, CURLOPT_URL, "$mirror/$buildcode.zip");
					@curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					@curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
					@curl_setopt($curl, CURLOPT_FILE,$dfh);
					@curl_setopt($curl, CURLOPT_HEADER, false);
					@curl_exec($curl);
					if(!curl_errno($curl) && curl_getinfo($curl,CURLINFO_HTTP_CODE) == 200)
					{
						insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Downloaded upgrade package from mirror");
					}
					else
					{
						@fclose($dfh);
						@unlink("package.zip");
						insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to download upgrade package from mirror: error code " . curl_errno($curl) . ", HTTP response code " . curl_getinfo($curl,CURLINFO_HTTP_CODE) . ".");
						$return=new UpgradeReturn(5,curl_errno($curl),curl_getinfo($curl,CURLINFO_HTTP_CODE));
						@curl_close($curl);
						return $return;
					}
					@curl_close($curl);
					@fclose($dfh);
				}
				else
				{
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to initialize cURL.");
					return new UpgradeReturn(4);
				}
			}
			else
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to open destination file \"package.zip\" for writing.");
				return new UpgradeReturn(3);
			}
		}
		insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Finished download of upgrade package \"$buildcode\".");
		insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Begin verifying checksum of upgrade package \"$buildcode\".");
		$curl=@curl_init();
		if($curl !== false)
		{
			@curl_setopt($curl, CURLOPT_URL, "$mirror/gethash.php?build=$buildcode");
			@curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			@curl_setopt($curl, CURLOPT_HEADER, false);
			$data=@curl_exec($curl);
			
			if(!empty($data) && !curl_errno($curl) && curl_getinfo($curl,CURLINFO_HTTP_CODE) == 200)
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Retrieved checksum from mirror: \"$data\".");
				$localhash=md5_file("package.zip");
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Retrieved checksum from local file: \"$localhash\".");
				if($localhash == $data)
				{
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Hashes match, download process complete.");
					@curl_close($curl);
					return new UpgradeReturn(0);
				}
				else
				{
					@unlink("package.zip");
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Hashes do not match, halting process.");
					$return=new UpgradeReturn(8,$data,$localhash);
					@curl_close($curl);
					return $return;
				}
			}
			else
			{
				@unlink("package.zip");
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to get checksum from mirror: error code " . curl_errno($curl) . ", HTTP response code " . curl_getinfo($curl,CURLINFO_HTTP_CODE) . ".");
				$return=new UpgradeReturn(7,curl_errno($curl),curl_getinfo($curl,CURLINFO_HTTP_CODE));
				@curl_close($curl);
				return $return;
			}
		}
		else
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to initialize cURL object.");
			@unlink("package.zip");
			return new UpgradeReturn(6);
		}
	}
	//Function for unpacking upgrade package
	function unpack_package()
	{
		if(!file_exists("package.zip"))
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Upgrade package doesn't exist, terminating process.");
			return new UpgradeReturn(9,"PACKAGE_NOT_EXISTING");
		}
		if(!file_exists("upgrade") || !is_dir("upgrade"))
		{
			$debug=@mkdir("upgrade");
			if($debug !== true)
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Cannot create temporary directory, terminating process.");
				return new UpgradeReturn(9,"CANNOT_MAKE_DIRECTORY");
			}
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Created temporary directory.");
		}
		$arch=new ZipArchive;
		if($arch->open("latest.zip"))
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Opened upgrade package.");
			$debug=$arch->extractTo("upgrade");
			if($debug === true)
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Extracted upgrade package to \"upgrade\".");
				$arch->close();
				return new UpgradeReturn(0);
			}
			else
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to extract upgrade package, terminating process.");
				$arch->close();
				@rmdir("files");
				return new UpgradeReturn(9,"CANNOT_UNZIP_FILES");
			}
		}
		else
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to open upgrade package, terminating process.");
			@rmdir("files");
			return new UpgradeReturn(9,"CANNOT_OPEN_ARCHIVE");
		}
	}
	//Function for backing up the MRS' existing files
	function back_up_mrs()
	{
		$debug=@mkdir("backup-frontend");
		if($debug !== true)
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to create backup directory for MRS frontend, terminating process.");
			return new UpgradeReturn(10);
		}
		insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Created backup directory for MRS frontend.");
		$debug=@mkdir("backup-backend");
		if($debug !== true)
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to create backup directory for MRS backend, terminating process.");
			return new UpgradeReturn(11);
		}
		insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Created backup directory for MRS backend.");
		$debug=@mkdir("backup-db");
		if($debug !== true)
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to create backup directory for MRS databases, terminating process.");
			return new UpgradeReturn(12);
		}
		insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Created backup directory for MRS databases.");
		$returns=array(0,0);
		$files=@glob("*.php");
		foreach($files as $file)
		{
			$debug=@copy($file,"backup-frontend");
			if($debug !== true)
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to backup file \"$file\".");
				$returns[1]++;
			}
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Backed up file \"$file\".");
			$returns[0]++;
		}
		$debug=@chdir("backend");
		if($debug === true)
		{
			$files=@glob("*.php");
			foreach($files as $file)
			{
				$debug=@copy($file,"../backup-backend");
				if($debug !== true)
				{
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to backup file \"$file\".");
					$returns[1]++;
				}
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Backed up file \"$file\".");
				$returns[0]++;
			}
		}
		else
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to change to backend directory, proceeding without backup.");
		}
		$debug=@chdir("../db");
		if($debug === true)
		{
			$files=@glob("*.sqlite");
			foreach($files as $file)
			{
				$debug=@copy($file,"../backup-db");
				if($debug !== true)
				{
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to backup file \"$file\".");
					$returns[1]++;
				}
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Backed up file \"$file\".");
				$returns[0]++;
			}
		}
		else
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to change to database directory, proceeding without backup.");
		}
		return new UpgradeReturn(0,$returns[0],$returns[1]);
	}
	//Function for running pre-processor
	function run_pre_processor()
	{
		if(strpos(getcwd(),"upgrade") === false)
		{
			$debug=@chdir("upgrade");
			if($debug !== true)
			{
				$debug=@chdir("../upgrade");
			}
		}
		else
		{
			$debug=true;
		}
		if($debug === true)
		{
			if(file_exists("preprocessor.php"))
			{
				require("preprocessor.php");
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Running pre-processor.");
				$debug=preprocessor_run();
				return new UpgradeReturn($debug);
			}
			else
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","No preprocessor script found, proceeding without running.");
				return new UpgradeReturn(0);
			}
		}
		else
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to change to temporary directory, terminating process.");
			return new UpgradeReturn(20);
		}
	}
	//Function for replacing system files
	function replace_files()
	{
		if(strpos(getcwd(),"upgrade") === false)
		{
			$debug=@chdir("upgrade");
			if($debug !== true)
			{
				$debug=@chdir("../upgrade");
			}
		}
		else
		{
			$debug=true;
		}
		if($debug === true)
		{
			if(file_exists("sysfiles.txt"))
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Processing list of system files.");
				$files=array();
				$raw=explode("\r\n",file_get_contents("sysfiles.txt"));
				foreach($raw as $file)
				{
					$file=explode("|",$file);
					if(!empty($file[0]) && !empty($file[1]) && file_exists($file[0]))
					{
						$files[$file[0]]=$file[1];
					}
					else
					{
						insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to process directive \"" . implode("|",$file) . "\", ignoring it, expect problems.");
					}
				}
				$returns=array(0,0);
				foreach($files as $temp=>$new)
				{
					$debug=@rename($temp,$new);
					if($debug === true)
					{
						insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Renamed file \"$temp\" to \"$new\".");
						$returns[0]++;
					}
					else
					{
						insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to rename file \"$temp\" to \"$new\", proceeding anyways, expect problems.");
						$returns[1]++;
					}
				}
				return new UpgradeReturn(0,$returns[0],$returns[1]);
			}
			else
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","No system file list found, proceeding without replacing anything.");
				return new UpgradeReturn(0);
			}
		}
		else
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to change to temporary directory, terminating process.");
			return new UpgradeReturn(30);
		}
	}
	//Function for upgrading databases
	function upgrade_dbs()
	{
		if(strpos(getcwd(),"upgrade") === false)
		{
			$debug=@chdir("upgrade");
			if($debug !== true)
			{
				$debug=@chdir("../upgrade");
			}
		}
		else
		{
			$debug=true;
		}
		if($debug === true)
		{
			if(file_exists("dbupgrade.php"))
			{
				require("dbupgrade.php");
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Running database upgrade script.");
				$debug=upgrade_databases();
				return new UpgradeReturn($debug);
			}
			else
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","No database upgrade script found, proceeding without running.");
				return new UpgradeReturn(0);
			}
		}
		else
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to change to temporary directory, terminating process.");
			return new UpgradeReturn(40);
		}
	}
	//Function for running post-processor
	function run_post_processor()
	{
		if(strpos(getcwd(),"upgrade") === false)
		{
			$debug=@chdir("upgrade");
			if($debug !== true)
			{
				$debug=@chdir("../upgrade");
			}
		}
		else
		{
			$debug=true;
		}
		if($debug === true)
		{
			if(file_exists("postprocessor.php"))
			{
				require("postprocessor.php");
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Running post-processor.");
				$debug=postprocessor_run();
				return new UpgradeReturn($debug);
			}
			else
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","No postprocessor script found, proceeding without running.");
				return new UpgradeReturn(0);
			}
		}
		else
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to change to temporary directory, terminating process.");
			return new UpgradeReturn(50);
		}
	}
	//Function to clean up remaining mess
	function clean_up_upgrade($keepback=true)
	{
		$returncode=0;
		$returns=array(0,0);
		if(strpos(getcwd(),"upgrade") === false)
		{
			$debug=@chdir("upgrade");
			if($debug !== true)
			{
				$debug=@chdir("../upgrade");
			}
		}
		else
		{
			$debug=true;
		}
		if($debug === true)
		{
			$removable=true;
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Beginning cleanup process.");
			$files=array_merge(glob("*.php"),glob("*.txt"));
			foreach($files as $file)
			{
				$debug=@unlink($file);
				if($debug === true)
				{
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Removed file \"$file\".");
					$returns[0]++;
				}
				else
				{
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove file \"$file\".");
					$returns[1]++;
					$removable=false;
				}
			}
			if($removable === true)
			{
				$debug=@chdir("..");
				if($debug === true)
				{
					$debug=@rmdir("files");
					if($debug === true)
					{
						insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Removed directory \"files\".");
					}
					else
					{
						insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"files\".");
						$returncode=68;
					}
				}
				else
				{
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"files\".");
					$returncode=68;
				}
			}
			else
			{
				insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"files\", as it isn't empty due to previous failures.");
				$returncode=61;
			}
			if($keepback === false)
			{
				$debug=@chdir("backup-frontend");
				if($debug === true)
				{
					$removable=true;
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Cleaning up directory \"backup-frontend\".");
					$files=glob("*.php");
					foreach($files as $file)
					{
						$debug=@unlink($file);
						if($debug === true)
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Removed file \"$file\".");
							$returns[0]++;
						}
						else
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove file \"$file\".");
							$returns[1]++;
							$removable=false;
						}
					}
					if($removable === true)
					{
						$debug=@chdir("..");
						if($debug === true)
						{
							$debug=@rmdir("backup-frontend");
							if($debug === true)
							{
								insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Removed directory \"backup-frontend\".");
							}
							else
							{
								insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-frontend\".");
								if($returncode != 0)
								{
									$returncode=66;
								}
								else
								{
									$returncode=62;
								}
							}
						}
						else
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-frontend\".");
							if($returncode != 0)
							{
								$returncode=66;
							}
							else
							{
								$returncode=62;
							}
						}
					}
					else
					{
						insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-frontend\", as it isn't empty due to previous failures.");
						if($returncode != 0)
						{
							$returncode=66;
						}
						else
						{
							$returncode=62;
						}
					}
				}
				else
				{
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to change to directory \"backup-frontend\", cancelling removal and proceeding with next step.");
					$returncode=69;
				}
				$debug=@chdir("backup-backend");
				if($debug === true)
				{
					$removable=true;
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Cleaning up directory \"backup-backend\".");
					$files=glob("*.php");
					foreach($files as $file)
					{
						$debug=@unlink($file);
						if($debug === true)
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Removed file \"$file\".");
							$returns[0]++;
						}
						else
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove file \"$file\".");
							$returns[1]++;
							$removable=false;
						}
					}
					if($removable === true)
					{
						$debug=@chdir("..");
						if($debug === true)
						{
							$debug=@rmdir("backup-backend");
							if($debug === true)
							{
								insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Removed directory \"backup-backend\".");
							}
							else
							{
								insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-backend\".");
								if($returncode != 0)
								{
									$returncode=66;
								}
								else
								{
									$returncode=63;
								}
							}
						}
						else
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-backend\".");
							if($returncode != 0)
							{
								$returncode=66;
							}
							else
							{
								$returncode=63;
							}
						}
					}
					else
					{
						insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-backend\", as it isn't empty due to previous failures.");
						if($returncode != 0)
						{
							$returncode=66;
						}
						else
						{
							$returncode=63;
						}
					}
				}
				else
				{
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to change to directory \"backup-backend\", cancelling removal and proceeding with next step.");
					$returncode=69;
				}
				$debug=@chdir("backup-upgrade");
				if($debug === true)
				{
					$removable=true;
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Cleaning up directory \"backup-upgrade\".");
					$files=glob("*.php");
					foreach($files as $file)
					{
						$debug=@unlink($file);
						if($debug === true)
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Removed file \"$file\".");
							$returns[0]++;
						}
						else
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove file \"$file\".");
							$returns[1]++;
							$removable=false;
						}
					}
					if($removable === true)
					{
						$debug=@chdir("..");
						if($debug === true)
						{
							$debug=@rmdir("backup-upgrade");
							if($debug === true)
							{
								insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Removed directory \"backup-upgrade\".");
							}
							else
							{
								insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-upgrade\".");
								if($returncode != 0)
								{
									$returncode=66;
								}
								else
								{
									$returncode=64;
								}
							}
						}
						else
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-upgrade\".");
							if($returncode != 0)
							{
								$returncode=66;
							}
							else
							{
								$returncode=64;
							}
						}
					}
					else
					{
						insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-upgrade\", as it isn't empty due to previous failures.");
						if($returncode != 0)
						{
							$returncode=66;
						}
						else
						{
							$returncode=64;
						}
					}
				}
				else
				{
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to change to directory \"backup-upgrade\", cancelling removal and proceeding with next step.");
					$returncode=69;
				}
				$debug=@chdir("backup-db");
				if($debug === true)
				{
					$removable=true;
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Cleaning up directory \"backup-db\".");
					$files=glob("*.sqlite");
					foreach($files as $file)
					{
						$debug=@unlink($file);
						if($debug === true)
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Removed file \"$file\".");
							$returns[0]++;
						}
						else
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove file \"$file\".");
							$returns[1]++;
							$removable=false;
						}
					}
					if($removable === true)
					{
						$debug=@chdir("..");
						if($debug === true)
						{
							$debug=@rmdir("backup-db");
							if($debug === true)
							{
								insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Removed directory \"backup-db\".");
							}
							else
							{
								insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-db\".");
								if($returncode != 0)
								{
									$returncode=66;
								}
								else
								{
									$returncode=65;
								}
							}
						}
						else
						{
							insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-db\".");
							if($returncode != 0)
							{
								$returncode=66;
							}
							else
							{
								$returncode=65;
							}
						}
					}
					else
					{
						insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to remove directory \"backup-db\", as it isn't empty due to previous failures.");
						if($returncode != 0)
						{
							$returncode=66;
						}
						else
						{
							$returncode=65;
						}
					}
				}
				else
				{
					insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to change to directory \"backup-frontend\", cancelling removal and proceeding with next step.");
					$returncode=69;
				}
			}
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Cleanup process ended.");
		}
		else
		{
			insert_system_log($_SERVER['REMOTE_ADDR'],date("g:i:s"),"functions.php","Failed to change to temporary directory, terminating process.");
			$returncode=60;
		}
		return new UpgradeReturn($returncode);
	}
?>
<?php
	//Security functions
	
	//Function for checking admin flag
	function check_admin_flag()
	{
		if(!empty($_SESSION['mrsadmin']) && $_SESSION['mrsadmin'] == "y")
		{
			return true;
		}
		return false;
	}
	//Function for checking IP address
	function check_ip_address()
	{
		if(!empty($_SESSION['mrsip']) && $_SESSION['mrsip'] == $_SERVER['REMOTE_ADDR'])
		{
			return true;
		}
		return false;
	}
	//Function for checking useragent
	function check_user_agent()
	{
		if(!empty($_SESSION['mrsua']) && $_SESSION['mrsua'] == $_SERVER['HTTP_USER_AGENT'])
		{
			return true;
		}
		return false;
	}
	//Function for checking identifier
	function check_identifier()
	{
		$identifier=base64_encode($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . getcwd());
		if(!empty($_SESSION['mrsid']) && $_SESSION['mrsid'] == $identifier)
		{
			return true;
		}
		return false;
	}
	//Function to reduce array of TRUE and FALSE values
	function true_false_reduction($existing,$new)
	{
		$newval=0;
		if($new === true)
		{
			$newval=1;
		}
		$existing *= $newval;
		return $existing;
	}
	//Primary security checking function
	function security_check($level)
	{
		$entries=array();
		$entries[]=check_admin_flag();
		if($level == 2 || $level == 5 || $level == 7)
		{
			$entries[]=check_ip_address();
		}
		if($level == 2 || $level == 5 || $level == 7)
		{
			$entries[]=check_ip_address();
		}
		if($level == 3 || $level == 6 || $level == 7)
		{
			$entries[]=check_user_agent();
		}
		if($level == 4 || $level == 5 || $level == 6 || $level == 7)
		{
			$entries[]=check_identifier();
		}
		$result=array_reduce($entries,"true_false_reduction",1);
		if($result >= 1)
		{
			return true;
		}
		return false;
	}
?>
<?php
	//Helper functions, in no particularly useful order
	
	//Function for getting alternative session storage path, if set
	function alt_sess_store()
	{
		$db=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
		$store=get_setting($db,"session");
		$loc=get_setting($db,"sessloc");
		if($store == "y" && !empty($loc) && file_exists($loc) && is_dir($loc))
		{
			return $loc;
		}
		return false;
	}
	//Function for checking if logging is enabled
	function is_logging_enabled()
	{
		$db=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
		$syslog=get_setting($db,"syslog");
		if($syslog == "y")
		{
			return true;
		}
		return false;
	}
	//Function for extracting request IDs from list of request objects
	function extract_ids($reqs)
	{
		$ids=array();
		foreach($reqs as $req)
		{
			if(is_a($req,Request))
			{
				$ids[]=$req->getID();
			}
		}
		return $ids;
	}
	//Function for filtering based on duration and request time
	function filter_duration($req)
	{
		if(!is_a($req,Request))
		{
			trigger_error("Request passed not in valid format, ignoring, expect problems.",E_USER_WARNING);
			return true;
		}
		$db=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
		$duration=get_setting($db,"duration");
		close_db($db);
		if(($req->getTime()+$duration*60*60) <= time())
		{
			return false;
		}
		return true;
	}
	//Function for filtering based on day and request time
	function filter_daily($req)
	{
		if(!is_a($req,Request))
		{
			trigger_error("Request passed not in valid format, ignoring, expect problems.",E_USER_WARNING);
			return true;
		}
		if(($req->getTime()+24*60*60) <= time())
		{
			return false;
		}
		return true;
	}
	//Function for determining if system is in overload mode
	function system_in_overload()
	{
		$db=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
		$overload=get_setting($db,"overload");
		close_db($db);
		$db=open_db("db/music.sqlite",SQLITE3_OPEN_READONLY);
		$requests=array_merge(extract_ids(get_requests_by_status($db,0)),extract_ids(get_requests_by_status($db,1)));
		close_db($db);
		if($overload == 0 || count($requests) <= $overload)
		{
			return false;
		}
		return true;
	}
	//Function for determining if user is in queue and allowed to make more requests
	function user_in_queue()
	{
		$db=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
		$queue=get_setting($db,"multireq");
		close_db($db);
		$db=open_db("db/music.sqlite",SQLITE3_OPEN_READONLY);
		$ip=extract_ids(get_requests_by_ip($db,$_SERVER['REMOTE_ADDR']));
		$open=array_merge(extract_ids(get_requests_by_status($db,0)),extract_ids(get_requests_by_status($db,1)));
		$requests=array_intersect($ip,$open);
		close_db($db);
		if($queue == "y" || count($requests) <= 0)
		{
			return false;
		}
		return true;
	}
	//Function for determining if user has hit posting limits
	function user_hit_limit()
	{
		$db=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
		$peruser=get_setting($db,"peruser");
		$perip=get_setting($db,"perip");
		$daily=get_setting($db,"daily");
		close_db($db);
		$db=open_db("db/music.sqlite",SQLITE3_OPEN_READONLY);
		$times=extract_ids(array_filter(get_all_requests($db),"filter_duration"));
		$day=extract_ids(array_filter(get_all_requests($db),"filter_daily"));
		if(!empty($_SESSION['username']))
		{
			$user=extract_ids(get_requests_by_user($db,$_SESSION['username']));
		}
		else
		{
			$user=array();
		}
		$ip=extract_ids(get_requests_by_ip($db,$_SERVER['REMOTE_ADDR']));
		close_db($db);
		if($peruser > 0 && count(array_intersect($user,$times)) >= $peruser)
		{
			return true;
		}
		if($perip > 0 && count(array_intersect($ip,$times)) >= $perip)
		{
			return true;
		}
		if($daily > 0 && count(array_intersect($ip,$day)) >= $daily)
		{
			return true;
		}
		return false;
	}
	//Function for mapping out internal and external song list formats
	function map_format_string()
	{
		$mapping=array();
		$db=open_db("db/system.sqlite",SQLITE3_OPEN_READONLY);
		$intformat=explode("|",get_setting($db,"intformat"));
		$extformat=explode("|",get_setting($db,"extformat"));
		close_db($db);
		while(count($intformat) > 0)
		{
			if(count($extformat) <= 0)
			{
				break;
			}
			$string=array_shift($intformat);
			if(strpos($string,"*") === false)
			{
				$map=array_shift($extformat);
				$mapping[$string]=$map;
			}
		}
		return $mapping;
	}
?>