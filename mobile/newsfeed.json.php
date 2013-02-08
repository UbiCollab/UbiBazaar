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
	include_once '../engine/handler/error.handler.php';
	include_once '../engine/handler/mysql.handler.php';
	include_once '../engine/handler/user.handler.php';
	include_once '../engine/handler/app.handler.php';

	// Security messure	
	$email = stripslashes($_REQUEST['username']);
	$passord = stripslashes($_REQUEST['password']);

	if($email != "" && $passord != ""){
		if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){ 					
			$user = new User();
			$user->loginMobile($email, $passord);
			if($user->isLoggedIn()){
				$sql = new SQL();
				
				// Creating arrays
				$history = array();
				$time = array();
				switch ($_REQUEST['action']){
					case 'app':
						// Gathering applications and 
						$array = $sql->getFullArray("SELECT app_id FROM ".$sql->prefix."UserApplication WHERE user_id IN 
										(SELECT following FROM ".$sql->prefix."UserFollowUser WHERE follower=".$user->getId().") 
										AND app_id IN (SELECT app_id FROM ".$sql->prefix."ApplicationUpdates WHERE public=1) 
										AND app_id NOT IN (SELECT app_id FROM ".$sql->prefix."UserDeviceInstalledApps WHERE user_device_id IN 
										(SELECT user_device_id FROM ".$sql->prefix."UserDevice WHERE user_id =".$user->getId()."))");
						for($i=0; $i<count($array); $i++){
							$ar = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$array[$i]['app_id']." AND public=1");
							// Fetch all the platform it's been released on.
							for($j=0; $j<count($ar); $j++){
								$ar1 = $sql->getArray("SELECT * FROM ".$sql->prefix."Application WHERE app_id=".$array[$i]['app_id']." AND public=1");
								$ar2 = $sql->getArray("SELECT name FROM ".$sql->prefix."Platform WHERE platform_id=".$ar[$j]['platform']);
								$output['msg'] = "Released a new update of '".$ar1['name']."'.";
								$output['time'] = $ar[$j]['released'];
								$output['app_id'] = $array[$i]['app_id'];
								$output['platform'] = $ar2['name'];
								$output['platform_id'] = $ar[$j]['platform'];
								
								if (!in_array($output,$history)) 
								{ 
									$time[] = strtotime($ar[$j]['released']);
									$history[] = $output;
								}	
							}
						}
						
						$array = $sql->getFullArray("SELECT app_id FROM ".$sql->prefix."UserFollowApplication WHERE follower=".$user->getId()." AND app_id IN 
						(SELECT app_id FROM ".$sql->prefix."ApplicationUpdates WHERE public=1) AND app_id NOT IN 
						(SELECT app_id FROM ".$sql->prefix."UserDeviceInstalledApps WHERE user_device_id IN 
						(SELECT user_device_id FROM ".$sql->prefix."UserDevice WHERE user_id =".$user->getId().")) 
						AND app_id NOT IN (SELECT app_id FROM ".$sql->prefix."UserApplication WHERE user_id IN 
						(SELECT following FROM ".$sql->prefix."UserFollowUser WHERE follower=".$user->getId()."))");
						for($i=0; $i<count($array); $i++){
							$ar = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$array[$i]['app_id']." AND public=1");
							// Fetch all the platform it's been released on.
							for($j=0; $j<count($ar); $j++){
								$ar1 = $sql->getArray("SELECT name FROM ".$sql->prefix."Application WHERE app_id=".$array[$i]['app_id']." AND public=1");
								$ar2 = $sql->getArray("SELECT name FROM ".$sql->prefix."Platform WHERE platform_id=".$ar[$j]['platform']);
								$output['msg'] = "Released a new update of '".$ar1['name']."'.";
								$output['time'] = $ar[$j]['released'];
								$output['app_id'] = $array[$i]['app_id'];
								$output['platform'] = $ar2['name'];
								$output['platform_id'] = $ar[$j]['platform'];
								
								if (!in_array($output,$history)) 
								{ 
									$time[] = strtotime($ar[$j]['released']);
									$history[] = $output;
								}	
							}
						}
						$array = $sql->getFullArray("SELECT DISTINCT app_id FROM ".$sql->prefix."UserDeviceInstalledApps WHERE user_device_id IN (SELECT user_device_id FROM ".$sql->prefix."UserDevice WHERE user_id=".$user->getId().")");
						for($i=0; $i<count($array); $i++){
							$ar = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$array[$i]['app_id']." AND public=1");
							// Fetch all the platform it's been released on.
							for($j=0; $j<count($ar); $j++){
								$ar1 = $sql->getArray("SELECT name FROM ".$sql->prefix."Application WHERE app_id=".$array[$i]['app_id']." AND public=1");
								$ar2 = $sql->getArray("SELECT name FROM ".$sql->prefix."Platform WHERE platform_id=".$ar[$j]['platform']);
								$output['msg'] = "Released a new update of '".$ar1['name']."'.";
								$output['time'] = $ar[$j]['released'];
								$output['app_id'] = $array[$i]['app_id'];
								$output['platform'] = $ar2['name'];
								$output['platform_id'] = $ar[$j]['platform'];
								
								if (!in_array($output,$history)) 
								{ 
									$time[] = strtotime($ar[$j]['released']);
									$history[] = $output;
								}
							}
						}
						break;
					case 'social':
						// Gather info about unread messages
						$query = "SELECT * FROM ".$sql->prefix."UserMsg WHERE to_u=".$user->getId()." AND unread=1 ORDER BY sent DESC LIMIT 30";
						$array = $sql->getFullArray($query);
						
						$from_user = new User();
						foreach ($array as $value) {
							// Get username of the user that sendt the message
							$from_user->getInfo($value['from_u']);
							
							$output['msg'] = "Received a message from ".$from_user->getName().".";
							$output['time'] = $value['sent'];

							if (!in_array($output,$history)) { 
								$time[] = strtotime($value['sent']);
								$history[] = $output;
							}
						}
						
						// Gather info about feedback of the users applications
						$query = "SELECT * FROM ".$sql->prefix."ApplicationFeedback WHERE app_id IN 
								(SELECT app_id FROM ".$sql->prefix."UserApplication WHERE user_id=".$user->getId().") ORDER BY time DESC LIMIT 30";
						$array = $sql->getFullArray($query);
						
						$app = new App();
						foreach ($array as $value) {
							// Get username of the user that sendt the message
							$app->getBasicInfo($value['app_id']);
							
							$output['msg'] = "Your app ".$app->getName()." received feedback.";
							$output['time'] = $value['time'];
							if (!in_array($output,$history)) { 
								$time[] = strtotime($value['time']);
								$history[] = $output;
							}
						}
						break;
				}
				// Sort the information gathered
				array_multisort($time, SORT_DESC, $history);
				
				// Limiting messages
				if(count($history)>30)
					$size = 30;
				else
					$size = count($history);
				
				for ($i = 0; $i < $size; $i++) {
					$tmp[] = $history[$i];
				}
				// printing JSON-objects
				print(json_encode($tmp));
			}
		}
	}	
?>