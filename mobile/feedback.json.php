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

	// Security messure	
	$email = stripslashes($_REQUEST['username']);
	$passord = stripslashes($_REQUEST['password']);

	if($email != "" && $passord != ""){
		if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){ 					
			$user = new User();
			$user->loginMobile($email, $passord);
			if($user->isLoggedIn()){
				$id = $_REQUEST['id'];
				$action = $_REQUEST['action'];
				
				// Output array
				$output = array();
				$sql = new SQL();
				switch($action){
					case 'score':
						$query = "SELECT * FROM ".$sql->prefix."ApplicationFeedback WHERE recommend=0 AND app_id=".$id;
						$neg = $sql->getCount($query);
						
						$query = "SELECT * FROM ".$sql->prefix."ApplicationFeedback WHERE recommend=1 AND app_id=".$id;
						$pos = $sql->getCount($query);
						
						$output['positiv'] = $pos;
						$output['negativ'] = $neg;
						break;
					case 'feedback':
						$query = "SELECT u.name, f.message, f.recommend, f.time  FROM ".$sql->prefix."ApplicationFeedback f,".$sql->prefix."User u WHERE f.app_id=".$id." AND u.user_id=f.user_id ORDER BY time DESC";
						$array = $sql->getFullArray($query);
						foreach ($array as $value) {
							$output[] = $value;
						}
						break;
					default:
						break;
				}
				print(json_encode($output));
			}
		}
	}	
?>