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
	$email = $_REQUEST['username'];
	$passord = $_REQUEST['password'];
	
	

	if($email != "" && $passord != ""){
		if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){ 					
			$user = new User();
			$user->loginMobile($email, $passord);
			if($user->isLoggedIn()){
				$input = $_REQUEST['search'];
				$tags = explode(",", $input);
				$app = array();
				$output = array();
							
				$sql = new SQL();
				
				for ($i = 0; $i < count($tags); $i++) {
					$tag = ereg_replace("[^A-Za-z0-9 ]", "", $tags[$i]);
					// Check if the tag allready excists
					if(strlen($tag) > 1 && strlen($tag) < 45){
						$query = sprintf("SELECT app_id FROM ".$sql->prefix."Application WHERE name LIKE '%s' AND public=1",
							mysql_real_escape_string(strtolower(trim("%".$tag."%"))));
							
						$apps = $sql->getFullArray($query);
						for ($j = 0; $j < count($apps); $j++) {
							if($sql->getCount("SELECT * FROM ".$sql->prefix."ApplicationUpdates WHERE public=1 AND app_id=".$apps[$j]['app_id'])>0){
								// It's over 9000!!
								$app[$apps[$j]['app_id']] = $sql->getCount("SELECT tag_id FROM ".$sql->prefix."ApplicationTag") * 2;
							}
						}
						
						$query = sprintf("SELECT tag_id FROM ".$sql->prefix."Tag WHERE tag='%s'",
							mysql_real_escape_string(strtolower(trim($tag))));
						$array = $sql->getArray($query);
						if($array){
							$apps = $sql->getFullArray("SELECT app_id FROM ".$sql->prefix."ApplicationTag WHERE tag_id=".$array['tag_id']);
							for ($j = 0; $j < count($apps); $j++) {
								$app[$apps[$j]['app_id']] = (int)$app[$apps[$j]['app_id']]+(int)$sql->getCount("SELECT user_id FROM ".$sql->prefix."ApplicationTag WHERE tag_id=".$array['tag_id']." AND app_id=".$apps[$i]['app_id']);
							}
						}				
					}
				}

				if(count($app)>0){
					// Sort the result
					arsort($app);	
					
					// show the result
					foreach ($app as $key => $value) {
						$apps = $sql->getFullArray("SELECT platform_id FROM ".$sql->prefix."ApplicationView WHERE app_id=".$key);
						for ($i = 0; $i < count($apps); $i++) {
							$q = "SELECT A.`app_id` , A.`name` , A.`desc` , A.released, A.platform, A.category, U.update_id, U.changelog, U.revision_nr, U.url
								FROM ".$sql->prefix."ApplicationView A,".$sql->prefix."ApplicationUpdates U 
								WHERE A.app_id = U.app_id
								AND A.update_id = U.update_id 
								AND U.platform = A.platform_id
								AND A.app_id = ".$key."
								AND A.platform_id = ".$apps[$i]['platform_id']."
								GROUP BY A.app_id, A.platform LIMIT 1";
							$output[] = $sql->getArray($q);
						}
					}
				}
			}
		}
		print(json_encode($output));
	}
?>