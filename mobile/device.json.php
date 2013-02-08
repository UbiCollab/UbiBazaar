<?php
	include_once '../engine/handler/error.handler.php';
	include_once '../engine/handler/mysql.handler.php';
	include_once '../engine/handler/user.handler.php';

	// Security messure	
	$email = stripslashes($_REQUEST['username']);
	$passord = stripslashes($_REQUEST['password']);

	if($email != "" && $passord != ""){
		if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){ 					
			$user = new User();
			$user->loginMobile($email, $passord);
			if($user->isLoggedIn()){
				$action = $_REQUEST["action"];
				switch($action){
					case 'check':
						$serial = $_REQUEST['serial'];
					
						if($serial != ""){
							$sql = new SQL();
		
							$query = sprintf("SELECT * FROM ".$sql->prefix."UserDevice WHERE serialnumber = '%s' AND user_id = '%s'",
									mysql_real_escape_string($serial),
									mysql_real_escape_string($user->getId()));
						
							$deviceCnt = $sql->getCount($query);
						
							if($deviceCnt < 1){
								$output["success"] = "false";
							} else {
								$output["success"] = "true";
							}
							print(json_encode($output));
						}
						break;
					case 'register':
						$serial = $_REQUEST['serial'];
						$os = $_REQUEST['os'];
						$name = $_REQUEST['name'];
						$plat = $_REQUEST['platform'];
						if($os != "" && $plat != "" && $name != ""){
							if(eregi("^[0-9]{15}$", $serial)){
								$sql = new SQL();
								$query = sprintf("SELECT platform_id FROM ".$sql->prefix."Platform WHERE name LIKE '%s'",
									mysql_real_escape_string($plat));
								
								$array = $sql->getArray($query);
								$platform = $array["platform_id"];
								$query = sprintf("INSERT INTO ".$sql->prefix."UserDevice (serialnumber, os_version, name, user_id) VALUES ('%s', '%s', '%s', '%s')",
									mysql_real_escape_string($serial),
									mysql_real_escape_string($os),
									mysql_real_escape_string($name),
									mysql_real_escape_string($user->getId()));
									
								$changed = $sql->doUpdate($query);
								$output["success"] = $changed;
							} else {
								$output["success"] = "false";	
							}
						} else {
							$output["success"] = "false";
						}
						
						print(json_encode($output));
						break;
					case 'update':
						$serial = $_REQUEST['serial'];
						$os = $_REQUEST['os'];
						$plat = $_REQUEST['platform'];
						
						if($os != "" && $plat != "" && $serial != ""){
							if(eregi("^[0-9]{15}$", $serial)){
								$sql = new SQL();
								$query = sprintf("SELECT platform_id FROM ".$sql->prefix."Platform WHERE name LIKE '%s'",
									mysql_real_escape_string($plat));
								
								$array = $sql->getArray($query);
								$platform = $array["platform_id"];
								
								$query = sprintf("UPDATE ".$sql->prefix."UserDevice SET platform='%s', os_version='%s' WHERE serialnumber='%s' AND user_id='%s'",
									mysql_real_escape_string($platform),
									mysql_real_escape_string($os),
									mysql_real_escape_string($serial),
									mysql_real_escape_string($user->getId()));
								$changed = $sql->doUpdate($query);
								
								$output["success"] = $changed;
							}
						}
						print(json_encode($output));
					case 'install':
						$serial = $_REQUEST['serial'];
						$app_id = $_REQUEST['app_id'];
					
						if($serial != "" && $app_id != ""){
							$sql = new SQL();
		
							$query = sprintf("SELECT user_device_id FROM ".$sql->prefix."UserDevice WHERE serialnumber = '%s' AND user_id = '%s'",
								mysql_real_escape_string($serial),
								mysql_real_escape_string($user->getId()));
							$array = $sql->getArray($query);
							
							$user_device = 	$array['user_device_id'];
							
							$query = sprintf("INSERT INTO ".$sql->prefix."UserDeviceInstalledApps (user_device_id, app_id) VALUES ('%s', '%s')",
								mysql_real_escape_string($user_device),
								mysql_real_escape_string($app_id));
							$changed = $sql->doUpdate($query);
							
							$output["success"] = $changed;
						}
						print(json_encode($output));
						break;
				}
			}
		}
	}	