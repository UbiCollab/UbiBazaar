<?php 
/*
 *     Licensed to UbiCollab.org under one or more contributor
 *     license agreements.  See the NOTICE file distributed 
 *     with this work for additional information regarding
 *     copyright ownership. UbiCollab.org licenses this file
 *     to you under the Apache License, Version 2.0 (the "License");
 *     you may not use this file except in compliance
 *     with the License. You may obtain a copy of the License at
 * 
 *     		http://www.apache.org/licenses/LICENSE-2.0
 *     
 *     Unless required by applicable law or agreed to in writing,
 *     software distributed under the License is distributed on an
 *     "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 *     KIND, either express or implied.  See the License for the
 *     specific language governing permissions and limitations
 *     under the License.
 */
	$user = LayoutManager::getUser();
	if($user->isLoggedIn()){
		$sql = new SQL();
		// User is logged in, now show him/her there dashboard and stats about them self
		echo "<div class=\"container\"><h2>".$user->getName()."</h2></div>";
		// <a href=\"?module=user&action=view&id=".$user->getId()."\"></a>
		// 		    <li"; if($_GET['action'] == "followers"){ echo " class=\"active\"";} echo "><a href=\"?module=me&action=followers\">Followers</a></li>
		echo "<ul class=\"tabs_no_js\">
			<li"; if($_GET['action'] == ""){ echo " class=\"active\"";} echo "><a href=\"?module=me\">Apps</a></li>	
		    <li"; if($_GET['action'] == "groups"){ echo " class=\"active\"";} echo "><a href=\"?module=me&action=groups\">Groups</a></li>
			<li"; if($_GET['action'] == "connect"){ echo " class=\"active\"";} echo "><a href=\"?module=me&action=connect\">Connections</a></li>
		    <li"; if($_GET['action'] == "device"){ echo " class=\"active\"";} echo "><a href=\"?module=me&action=device\">Device</a></li>
		    <li"; if($_GET['action'] == "settings"){ echo " class=\"active\"";} echo "><a href=\"?module=me&action=settings\">Settings</a></li>
		    <li"; if($_GET['action'] == "stats"){ echo " class=\"active\"";} echo "><a href=\"?module=me&action=stats\">Statistics</a></li>
		    <li"; if($_GET['action'] == "history"){ echo " class=\"active\"";} echo "><a href=\"?module=me&action=history\">History</a></li>
		</ul>";
		echo "<div class=\"tab_container\">
			<div class=\"tab_content\">";
		switch($_GET['action']){
			case 'settings':
				$usera = new User();
				$usera->getInfo($user->getId());
				switch($_GET['p']){
					case 'password':
						// Change password
						if($user->changePassword($_POST['oldPass'], $_POST['password1'], $_POST['password2']))
							LayoutManager::alertSuccess("You have successfully changed your password.");
						break;
					case 'info':
						// Save info
						$query = sprintf("UPDATE `".$sql->prefix."User` SET `desc`='%s' WHERE `user_id`='%s'",
							mysql_real_escape_string($_POST['description']),
							mysql_real_escape_string($user->getId()));
						if($sql->doUpdate($query))
							LayoutManager::alertSuccess("Information successfully saved.");
						else
							LayoutManager::alertFailure("An error has occured while handling your request.");
						break;
					case 'picture':
						if(isset($_FILES['file']) && $_FILES['file'] != "" && $_GET['delete'] == ""){
							$result = FileHandler::uploadProfilePicture($_FILES['file'], $user->getId());
							if(!$result['error']){
								LayoutManager::alertSuccess($result['msg']);
							} else {
								LayoutManager::alertFailure($result['msg']);
							}
						} else if($_GET['delete'] == "true" && $usera->getPicture() != "pp_na.png"){
							// remove the record of the users picture and substituce it with the std one.
							$query = sprintf("UPDATE `".$sql->prefix."User` SET `picture`='%s' WHERE `user_id`='%s'",
								mysql_real_escape_string("pp_na.png"),
								mysql_real_escape_string($user->getId()));
							if(!$sql->doUpdate($query)){
								LayoutManager::alertFailure("Unable to store the request.");
							} else {
								// deleting the old picture.
								FileHandler::delete("images/user/".$usera->getPicture());
							}
						}
						break;
					default:
						// Do nothing
						break;
				}
				// Print settings forms etc
				$usera = new User();
				$usera->getInfo($user->getId());
				list($width,$height) = getimagesize("images/user/".$usera->getPicture());
				if($height<92)
					$height = 92;
				echo "<form action=\"?module=me&action=settings&p=picture\" method=\"post\" enctype=\"multipart/form-data\">
				  <div class=\"form_settings\" style=\"height: ".($height+25)."px ;border: 1px solid #999; padding: 5px;\">
				  <div style=\"float: right; text-align: center; width: ".$width."px;\"><img src=\"images/user/".$usera->getPicture()."\" border=\"0\">";
				if($usera->getPicture() != "pp_na.png"){
				 	echo "<a href=\"?module=me&action=settings&p=picture&delete=true\">Delete</a>";
				}
				  echo "</div>
				  	<p><b>Change profilepicture</b></p>
				    <p><span>File</span><input type=\"file\" name=\"file\" class=\"contact\"/></p>
				    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
				  </div>
				</form>";
				  $desc = $sql->getArray("SELECT `desc` FROM ".$sql->prefix."User WHERE user_id=".$user->getId());
				echo "<form action=\"?module=me&action=settings&p=info\" method=\"post\">
				  <div class=\"form_settings\" style=\"border: 1px solid #999; padding: 5px;\">
				  	<p><b>Change information</b></p>
				    <p><span>Description</span><textarea class=\"contact textarea\" name=\"description\">".$desc['desc']."</textarea></p>
				    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
				  </div>
				</form>";
				echo "<form action=\"?module=me&action=settings&p=password\" method=\"post\">
				  <div class=\"form_settings\" style=\"border: 1px solid #999; padding: 5px;\">
				  	<p><b>Change password</b></p>
				    <p><span>Old Passord</span><input type=\"password\" name=\"oldPass\" class=\"contact\"/></p>
				    <p><span>New Password</span><input type=\"password\" name=\"password1\" class=\"contact\"/></p>
				    <p><span>Confirm new password</span><input type=\"password\" name=\"password2\" class=\"contact\"/></p>
				    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
				  </div>
				</form>";

				break;
			case 'connect':
				switch ($_GET['a']){
					case 'app':
						$user->followApp($_GET['id']);
						break;
					case 'user':
						$user->followUser($_GET['id']);
						break;
				}
				echo "<div class=\"form_settings\" style=\"width: 350px; float: left; border: 1px solid #999; padding: 5px; margin-bottom: 15px;\">";
				echo "<h2>Following</h2>";
				$query = "SELECT * FROM ".$sql->prefix."UserFollowUser WHERE follower=".$user->getId();
		       	$result = $sql->getFullArray($query);
		       	if(count($result)>0){
	        		for($i=0; $i< count($result); $i++){
	        			$ar = $sql->getArray("SELECT * FROM ".$sql->prefix."User WHERE user_id=".$result[$i]['following']);
	        			echo "<div class=container style=\"min-height: 21px;\">
	        			<a href=\"?module=user&action=view&id=".$ar['user_id']."\">".$ar['name']."</a>
	        			<div style=\"float: right; min-height: 21px; min-width: 110px;\">
	        			<form action=\"?module=me&action=connect&a=user&id=".$ar['user_id']."\" method=\"post\">
	        			<input type=\"hidden\" name=\"confirm\" value=\"true\">
	        			<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Unfollow\" />
	        			</form>
	        			</div>
	        			</div>";
	        		}
		       	} else {
		       		echo "<i>None</i>";
		       	}
		       	echo "<br><h2>Applications</h2>";
				$query = "SELECT * FROM ".$sql->prefix."UserFollowApplication WHERE follower=".$user->getId();
		       	$result = $sql->getFullArray($query);
		       	if(count($result)>0){
	        		for($i=0; $i< count($result); $i++){
	        			$ar = $sql->getArray("SELECT * FROM ".$sql->prefix."ApplicationView WHERE app_id=".$result[$i]['app_id']);
	        			echo "<div class=container style=\"min-height: 21px;\">
	        			<a href=\"?module=app&action=view&platform=".$ar['platform_id']."&id=".$result[$i]['app_id']."\">".$ar['name']."</a>
	         			<div style=\"float: right; min-height: 21px; min-width: 110px;\">
	        			<form action=\"?module=me&action=connect&a=app&id=".$ar['app_id']."\" method=\"post\">
	        			<input type=\"hidden\" name=\"confirm\" value=\"true\">
	        			<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Unfollow\" />
	        			</form>
	        			</div>
	        			</div>";
	        		}
		       	} else {
		       		echo "<i>None</i>";
		       	}
				echo "</div>";
				echo "<div class=\"form_settings\" style=\"width: 350px; float: right; border: 1px solid #999; padding: 5px; margin-bottom: 15px;\">";
				echo "<h2>Followers</h2>";
				$query = "SELECT * FROM ".$sql->prefix."UserFollowUser WHERE following=".$user->getId()." AND follower IN (SELECT user_id FROM ".$sql->prefix."User WHERE deleted=0)";
		       	$result = $sql->getFullArray($query);
		       	if(count($result)>0){
	        		for($i=0; $i< count($result); $i++){
	        			$ar = $sql->getArray("SELECT * FROM ".$sql->prefix."User WHERE user_id=".$result[$i]['follower']);
	        			echo "<a href=\"?module=user&action=view&id=".$ar['user_id']."\">".$ar['name']."</a><br>";
	        		}
		       	} else {
		       		echo "<i>None</i>";
		       	}
				echo "</div>";
				break;
			case 'device':
				$imei = $_POST['serial'];
				$name = stripslashes($_POST['name']);
				if($imei != "" || $name != ""){
					if(eregi("^[0-9]{15}$", $imei)){
						$count = $sql->getCount("SELECT * FROM ".$sql->prefix."UserDevice WHERE serialnumber=".$imei." AND user_id=".$user->getId());
						if($count < 1){
							if(strlen($name) > 4){
								$query = sprintf("INSERT INTO ".$sql->prefix."UserDevice (serialnumber, name, user_id) VALUES ('%s', '%s', '%s')",
										mysql_real_escape_string($imei),
										mysql_real_escape_string($name),
										mysql_real_escape_string($user->getId()));
											
								$result = $sql->doUpdate($query);
								if($result != "true"){
									LayoutManager::alertFailure("Unexpected error, please try again later.");
								} else {
									LayoutManager::alertSuccess("Your device has been successfully added.");
								}
							} else {
								LayoutManager::alertFailure("Your device name must be longer than 4 characters.");
							}
						} else {
							LayoutManager::alertFailure("Your device is allready registered.");
						}
					} else {
						LayoutManager::alertFailure("Not a valid IMEI number.");
					}
				}
				switch ($_GET['a']){
					case 'reg':
						echo "<form id=\"regDeviceForm\" action=\"?module=me&action=device&a=reg\" method=\"post\">
						  <div class=\"form_settings\">
						    <p><span>Name</span><input id=\"name\" class=\"contact\" type=\"text\" name=\"name\" value=\"\" /></p>
						    <p><span>IMEI number</span><input id=\"serial\" class=\"contact\" type=\"text\" name=\"serial\" value=\"\" /></p>
						    <p>Call *#06# to get your IMEI number.</p>
						    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"submit\" /></p>
						  </div>
						</form>";
						echo "[<a href=\"?module=me&action=device\">Back</a>]";
						break;
					case 'view':
						$id = $_GET['id'];
						$device = new Device($id);
						if($device->getUser() == $user->getId()){
							echo "<div class=\"container\">
								<h2>".$device->getName()."  <img src=\"images/platform/".$device->getPlatform().".png\" border=0 width=16 height=16>".$device->getOs()."</h2>";
							echo "</div>";
							
							$query = sprintf("SELECT * FROM ".$sql->prefix."UserDeviceInstalledApps WHERE user_device_id='%s' ORDER BY installed DESC",
									mysql_real_escape_string($id));
							if($sql->getCount($query) != 0){
								echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
								echo "<tr><td width=45%><b>Name</b></td><td width=45%><b>Installed</b></td><td width=10%></td></tr>\n";
								$ar = $sql->getFullArray($query);
								for($i=0; $i<$sql->getCount($query); $i++){
									$app = new App();
									if(!is_null($ar[$i]['removed'])){
										$app->getBasicInfo($ar[$i]['app_id']);
									} else {
										$app->getInfoFromSQL($ar[$i]['app_id'], $device->getPlatform());
									}
									echo "<tr><td width=45%>".$app->toString()."</td>
									<td width=45%>".strftime ("%a %b %d, %Y %H:%M", strtotime($ar[$i]['installed']))."</td>
									<td width=10%>";
									if(!is_null($ar[$i]['removed']))
										echo "Uninstalled";
									else 
										echo "[<a href=\"#\" onclick=\"return rmItem()\">Uninstall</a>]";
									echo "</td></tr>\n";
								}
								echo "</table>\n";
							} else {
								echo "<div class=\"container\">Nothing installed on this device.</div>";
							}
							echo "[<a href=\"?module=me&action=device\">Back</a>]";
						}
						break;
					case 'delete':
						$id = $_GET['id'];
						if(eregi("^[0-9]{1,11}$", $id)){
							$query = sprintf("SELECT * FROM ".$sql->prefix."UserDevice WHERE user_device_id='%s' AND user_id='%s'",
									mysql_real_escape_string($id),
									mysql_real_escape_string($user->getId()));
							if ($sql->getCount($query) == 1){
								$query = sprintf("DELETE FROM ".$sql->prefix."UserDevice WHERE user_device_id='%s'",
								mysql_real_escape_string($id));
								$result = $sql->doUpdate($query);
								$query = sprintf("DELETE FROM ".$sql->prefix."UserDeviceInstalledApps WHERE user_device_id='%s'",
								mysql_real_escape_string($id));
								$result2 = $sql->doUpdate($query);
								if($result && $result2){
									LayoutManager::alertSuccess("Your device have been successfully deleted.");
								} else {
									LayoutManager::alertFailure("Unexpected error.");
								}
							} else {
								LayoutManager::alertFailure("You do not have rights to delete this device.");
							}
						} else {
							LayoutManager::redirect("./?module=me&action=device");
						}
						break;
					case 'edit':
						$id = $_GET['id'];
						$device = new Device($id);
						if($device->getUser() == $user->getId()){
							
						}
						break;
					default:
						break;
				}
				if($_GET['a'] != "view" && $_GET['a'] != "reg"){
					echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
					echo "<tr><td><b>Name</b></td><td><b>Platform</b></td><td><b>Last Update</b></td><td><b>Action</b></td></tr>";
					$deviceArray = $user->getDevices(0, 0);
					for($i=0; $i<count($deviceArray); $i++){
						$deviceArray[$i]->printManageRow();		
					}
					echo "</table>";
					echo "<a href=\"?module=me&action=device&a=reg\">New device?</a>";
				}
				break;
			case 'groups':
				switch($_GET['a']){
					case 'delete':
						if(eregi("^[0-9]{1,11}$", $_GET['id'])){
							if($sql->getCount("SELECT * FROM `".$sql->prefix."UserInGroup` WHERE group_id = '".$_GET['id']."' AND user_id=".$user->getId()." AND role='Owner'")>0){
								$result1 = $sql->doUpdate("DELETE FROM `".$sql->prefix."UserInGroup` WHERE group_id = '".$_GET['id']."'");
								$result3 = $sql->doUpdate("DELETE FROM `".$sql->prefix."GroupApplication` WHERE group_id = '".$_GET['id']."'");
								$result2 = $sql->doUpdate("DELETE FROM `".$sql->prefix."Group` WHERE group_id = '".$_GET['id']."'");
								$result4 = $sql->doUpdate("UPDATE ".$sql->prefix."Forum SET group='-1' WHERE group='".$_GET['group']."'");
								if($result1 && $result2 && $result3 && $result4)
									LayoutManager::alertSuccess("The group has been successfully deleted.");
								else
									LayoutManager::alertFailure("An error has occured while handling your request.");
							} else {
								LayoutManager::alertFailure("Your not the owner of this group.");
							}
						}
						break;
				}
				echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
				$user_array = $user->getGroups(0, 0);
				echo "<tr><td><b>Application</b></td><td><b>Group</b></td><td><b>Role</b></td><td width=220px><b>Action</b></td></tr>";
				for ($j = 0; $j < count($user_array); $j++) {
					echo "<tr><td>".$user_array[$j]->getApp()->getName()."</td>
					<td>".$user_array[$j]->toString()."</td>
					<td>".$user_array[$j]->getRole()."</td>
					<td>";
					if($user_array[$j]->getRole() == "Owner"){
						echo "[<a href=\"?module=me&action=groups&id=".$user_array[$j]->getId()."&a=delete\" onclick=\"return rmItem()\">Delete group</a>]";
					} else if($user_array[$j]->getRole() == "Invited"){
						echo "<div style=\"float: left;\">
						<form action=\"./?module=group&change=true&id=".$user_array[$j]->getId()."&a=true\" method=\"post\">
						<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Accept\" />
						</form>
						</div>";
						echo "
						<div style=\"float: right;\"><form action=\"./?module=group&change=true&id=".$user_array[$j]->getId()."&a=false\" method=\"post\">
						<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Decline\" />
						</form>
						</div>";
					}
					echo "</td></tr>";
				}
				echo "</table>";
				break;
			case 'history':
				// Gather history
				$array = $sql->getArray("SELECT * FROM ".$sql->prefix."User WHERE user_id=".$user->getId());
				$output['msg'] = "Joined the site.";
				$output['time'] = $array['joined'];
				$time[] = strtotime($array['joined']);
				$history[] = $output;
				// Gather group info
				$array = $sql->getFullArray("SELECT * FROM ".$sql->prefix."UserInGroup WHERE user_id=".$user->getId());
				for($i=0; $i<count($array); $i++){
					$ar = $sql->getArray("SELECT name FROM `".$sql->prefix."Group` WHERE group_id=".$array[$i]['group_id']);
					if($array[$i]['role'] == "Owner"){
						$output['msg'] = "Created the group '<a href=\"?module=group&id=".$array[$i]['group_id']."\">".$ar['name']."</a>'.";
					} else {
						$output['msg'] = "Joined the group '<a href=\"?module=group&id=".$array[$i]['group_id']."\">".$ar['name']."</a>'.";
					}
					$output['time'] = $array[$i]['joined'];
					$time[] = strtotime($array[$i]['joined']);
					$history[] = $output;
				}
				// Gathering follow info (USER)
				$array = $sql->getFullArray("SELECT * FROM ".$sql->prefix."UserFollowUser WHERE follower=".$user->getId());
				for($i=0; $i<count($array); $i++){
					$ar = $sql->getArray("SELECT name FROM ".$sql->prefix."User WHERE user_id=".$array[$i]['following']);
					$output['msg'] = "Started following the user '<a href=\"?module=user&action=view&id=".$array[$i]['following']."\">".$ar['name']."</a>'.";
					$output['time'] = $array[$i]['since'];
					$time[] = strtotime($array[$i]['since']);
					$history[] = $output;
				}
				// Gathering follow info (APPLICATION)
				$array = $sql->getFullArray("SELECT * FROM ".$sql->prefix."UserFollowApplication WHERE follower=".$user->getId());
				for($i=0; $i<count($array); $i++){
					$ar = $sql->getArray("SELECT name FROM ".$sql->prefix."Application WHERE app_id=".$array[$i]['app_id']);
					$output['msg'] = "Started following the application '<a href=\"?module=app&action=view&id=".$array[$i]['app_id']."\">".$ar['name']."</a>'.";
					$output['time'] = $array[$i]['since'];
					$time[] = strtotime($array[$i]['since']);
					$history[] = $output;
				}
				// Gather application info
				$array = $sql->getFullArray("SELECT app_id FROM ".$sql->prefix."UserApplication WHERE user_id=".$user->getId()." AND app_id IN (SELECT app_id FROM ".$sql->prefix."ApplicationUpdates)");
				for($i=0; $i<count($array); $i++){
					$ar = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$array[$i]['app_id']);
					for($j=0; $j<count($ar); $j++){
						$ar1 = $sql->getArray("SELECT * FROM ".$sql->prefix."Application WHERE app_id=".$array[$i]['app_id']);
						if($ar[$j]['public']){
							$msg = "Released a new update";
						} else {
							$msg = "Uploaded an update for testing";
						}
						$output['msg'] = $msg." of '<a href=\"?module=app&action=view&platform=".$ar[$j]['platform']."&id=".$array[$i]['app_id']."\">".$ar1['name']."</a>'.
						 <img src=\"images/platform/".$ar[$j]['platform'].".png\" border=0 width=12 height=12>";
						$output['time'] = $ar[$j]['released'];
						$time[] = strtotime($ar[$j]['released']);
						$history[] = $output;	
					}
				}
				// Gather device info
				$array = $sql->getFullArray("SELECT * FROM ".$sql->prefix."UserDevice WHERE user_id=".$user->getId());
				for($i=0; $i<count($array); $i++){
					$output['msg'] = "Last update of '<a href=\"?module=device&action=view&id=".$array[$i]['user_device_id']."\">".$array[$i]['name']."</a>'. <img src=\"images/platform/".$array[$i]['platform'].".png\" border=0 width=12 height=12>";
					$output['time'] = $array[$i]['last_update'];
					$time[] = strtotime($array[$i]['last_update']);
					$history[] = $output;
					// Gather installed apps on device info
					$ar = $sql->getFullArray("SELECT * FROM ".$sql->prefix."UserDeviceInstalledApps WHERE user_device_id=".$array[$i]['user_device_id']);
					for($j=0; $j<count($ar); $j++){
						$ar1 = $sql->getArray("SELECT * FROM ".$sql->prefix."Application WHERE app_id=".$ar[$j]['app_id']);
						$output['msg'] = "Installed application '<a href=\"?module=app&action=view&platform=".$array[$i]['platform']."&id=".$ar1['app_id']."\">".$ar1['name']."</a>' on '<a href=\"?module=device&action=view&id=".$array[$i]['user_device_id']."\">".$array[$i]['name']."</a>'.
						 <img src=\"images/platform/".$array[$i]['platform'].".png\" border=0 width=12 height=12>";
						$output['time'] = $ar[$j]['installed'];
						$time[] = strtotime($ar[$j]['installed']);
						$history[] = $output;
					}
					// Gather uminstalled apps on device info
					$ar = $sql->getFullArray("SELECT * FROM ".$sql->prefix."UserDeviceInstalledApps WHERE user_device_id=".$array[$i]['user_device_id']." AND removed IS NOT NULL");
					for($j=0; $j<count($ar); $j++){
						$ar1 = $sql->getArray("SELECT * FROM ".$sql->prefix."Application WHERE app_id=".$ar[$j]['app_id']);
						$output['msg'] = "Uninstalled application '<a href=\"?module=app&action=view&platform=".$array[$i]['platform']."&id=".$ar1['app_id']."\">".$ar1['name']."</a>'
						 from '<a href=\"?module=device&action=view&id=".$array[$i]['user_device_id']."\">".$array[$i]['name']."</a>'. 
						 <img src=\"images/platform/".$array[$i]['platform'].".png\" border=0 width=12 height=12>";
						$output['time'] = $ar[$j]['removed'];
						$time[] = strtotime($ar[$j]['removed']);
						$history[] = $output;
					}
				}
				// Sort the information gathered
				array_multisort($time, SORT_DESC, $history);

				// Show the user his / her history on the site
				for($i=0;$i<count($history); $i++){
					echo "<div class=\"container\" style=\"width: 100%;\"><div style=\"float: left;\">".$history[$i]['msg']."</div><div style=\"float: right;\">".strftime ("%a %b %d, %Y %H:%M", strtotime($history[$i]['time']))."</div></div><br>";
				}
				break;
			case 'migrate':
				// Give the user his / her info on a XML page to migrate out
				break;
			case 'stats':
				// Gather stats
				$sendt_msgs = $sql->getCount("SELECT msg_id FROM ".$sql->prefix."UserMsg WHERE from_u=".$user->getId());
				$received_msgs = $sql->getCount("SELECT msg_id FROM ".$sql->prefix."UserMsg WHERE to_u=".$user->getId());
				$in_groups = $sql->getCount("SELECT group_id FROM ".$sql->prefix."UserInGroup WHERE user_id=".$user->getId());
				$devices = $sql->getCount("SELECT user_id FROM ".$sql->prefix."UserDevice WHERE user_id=".$user->getId());
				$applications = $sql->getCount("SELECT user_id FROM ".$sql->prefix."UserApplication WHERE app_id IN (SELECT app_id FROM ".$sql->prefix."Application WHERE public=1) AND user_id=".$user->getId());
				$not_released = $sql->getCount("SELECT user_id FROM ".$sql->prefix."UserApplication WHERE app_id IN (SELECT app_id FROM ".$sql->prefix."Application WHERE public=0) AND user_id=".$user->getId());
				$installed_on_dev = $sql->getCount("SELECT user_device_id FROM ".$sql->prefix."UserDeviceInstalledApps WHERE user_device_id IN (SELECT user_device_id FROM ".$sql->prefix."UserDevice WHERE user_id=".$user->getId().")");
				$average_installed = 0;
				if($devices > 0){
					$average_installed = round($installed_on_dev/$devices, 2, PHP_ROUND_HALF_UP);
				}
				
				// Print stats
				echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
				echo "<tr><td><b>Messages</b></td><td></td></tr>";
				echo "<tr><td>Sent messages</td><td>".$sendt_msgs."</td></tr>";
				echo "<tr><td>Received messages</td><td>".$received_msgs."</td></tr>";
				echo "<tr><td><b>Application</b></td><td></td></tr>";
				echo "<tr><td>Released applications</td><td>".$applications."</td></tr>";
				echo "<tr><td>Not yet released applications</td><td>".$not_released."</td></tr>";
				echo "<tr><td>In groups</td><td>".$in_groups."</td></tr>";
				echo "<tr><td><b>Device</b></td><td></td></tr>";
				echo "<tr><td>Devices</td><td>".$devices."</td></tr>";
				echo "<tr><td>Installed applications</td><td>".$installed_on_dev."</td></tr>";
				echo "<tr><td>Average applications per device</td><td>".$average_installed."</td></tr>";
				echo "</table>";
				break;
			default:
				echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
				$user_array = $user->getApplications(0, 0, false);
					for ($j = 0; $j < count($user_array); $j++) {
						echo $user_array[$j]->printManageRow();
					}
				echo "</table>";
				echo "[<a href=\"?module=app&action=create\">Create new project</a>]";
				break;
		}
		echo "</div>
			</div>";
	} else {
		// Posting loginform
		LayoutManager::printLoginForm();
	}
?>