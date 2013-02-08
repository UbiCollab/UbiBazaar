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
				$offset = $_REQUEST['offset'];
				$limit = $_REQUEST['limit'];
				$category = $_REQUEST['category'];

				if($limit != "" && $offset != "" && $category == ""){
					$sql = new SQL();					
					$q = "SELECT A.`app_id` , A.`name` , A.`desc` , A.released, A.platform, A.category, A.platform_id, U.update_id, U.changelog, U.revision_nr, U.url
						FROM ".$sql->prefix."ApplicationView A,".$sql->prefix."ApplicationUpdates U 
						WHERE A.app_id = U.app_id
						AND A.update_id = U.update_id 
						AND U.platform = A.platform_id
						GROUP BY A.app_id, A.platform LIMIT ".$offset.",".$limit;
					
					$appArray = $sql->getFullArray($q);
				
					for($i=0; $i<$sql->getCount($q); $i++){
						// Check if the user is in groups connected with apps that got non public releases.
						if($sql->getCount("SELECT user_id FROM `".$sql->prefix."UserInGroup` WHERE group_id IN (SELECT group_id FROM ".$sql->prefix."GroupApplication WHERE app_id = ".$appArray[$i]['app_id'].") AND user_id=".$user->getId()) > 0 || 
							$sql->getCount("SELECT user_id FROM `".$sql->prefix."UserApplication` WHERE user_id=".$user->getId()." AND app_id=".$appArray[$i]['app_id']) == 1){
							// Check if the last release is public
							$check_release = $sql->getArray("
								SELECT public FROM ".$sql->prefix."ApplicationUpdates
								 WHERE app_id=".$appArray[$i]['app_id']." 
								 AND platform=".$appArray[$i]['platform_id']." ORDER BY released DESC LIMIT 1");
							if($check_release['public'] == 1){
								$output[] = $appArray[$i];
							} else {
								$temp = array();
								$temp['app_id'] = $appArray[$i]['app_id'];
								$temp['name'] = $appArray[$i]['name'];
								$temp['desc'] = $appArray[$i]['desc'];
								$temp['released'] = $appArray[$i]['released'];
								$temp['platform'] = $appArray[$i]['platform'];
								$temp['category'] = $appArray[$i]['category'];
								$temp['platform_id'] = $appArray[$i]['platform_id'];
								$q = "SELECT update_id, changelog, revision_nr, url
								FROM ".$sql->prefix."ApplicationUpdates 
								WHERE platform = '".$appArray[$i]['platform_id']."'
								AND app_id = '".$appArray[$i]['app_id']."' ORDER BY released DESC LIMIT 1";
								$ar = $sql->getArray($q);
								$temp['update_id'] = $ar['update_id'];
								$temp['changelog'] = $ar['changelog'];
								$temp['revision_nr'] = $ar['revision_nr'];
								$temp['url'] = $ar['url'];
								$output[] = $temp;
							}
						} else {
							$output[] = $appArray[$i];
						}
					}
					// Now gather applications that haven't published an public update yet
					$query = "SELECT A.`app_id` , A.`name` , A.`desc` , MAX( U.`released` ) AS released, P.`name` AS platform, P.`platform_id`, C.`category_id` AS category, MAX( U.`update_id` ) AS update_id, U.changelog, U.revision_nr, U.url
						FROM ".$sql->prefix."Application A, ".$sql->prefix."ApplicationUpdates U, ".$sql->prefix."Platform P, ".$sql->prefix."Category C, ".$sql->prefix."ApplicationCategory CU
						WHERE U.`app_id` = A.`app_id` 
						AND CU.`app_id` = A.`app_id` 
						AND C.`category_id` = CU.`category_id` 
						AND P.`platform_id` = U.`platform` 
						AND A.`public` = 1
						AND U.`public` = 0
						AND A.app_id NOT IN (SELECT app_id FROM ".$sql->prefix."ApplicationView)
						GROUP BY U.`app_id` ,  `platform` LIMIT ".$offset.",".$limit;
					
					$appArray = $sql->getFullArray($query);
					for($i=0; $i<$sql->getCount($q); $i++){
						// Check if the user is in groups connected with apps that got non public releases.
						if($sql->getCount("SELECT user_id FROM `".$sql->prefix."UserInGroup` WHERE group_id IN (SELECT group_id FROM ".$sql->prefix."GroupApplication WHERE app_id = ".$appArray[$i]['app_id'].") AND user_id=".$user->getId()) > 0 || 
							$sql->getCount("SELECT user_id FROM `".$sql->prefix."UserApplication` WHERE user_id=".$user->getId()." AND app_id=".$appArray[$i]['app_id']) == 1){
							$output[] = $appArray[$i];			
						}
					}	
					
					// Should print the whole application array				
					print(json_encode($output));
				} else if($limit != "" && $offset != "" && $category != ""){
					$sql = new SQL();					
					$q = "SELECT A.`app_id` , A.`name` , A.`desc` , A.released, A.platform, A.platform_id, A.category, U.update_id, U.changelog, U.revision_nr, U.url
						FROM ".$sql->prefix."ApplicationView A,".$sql->prefix."ApplicationUpdates U 
						WHERE A.app_id = U.app_id
						AND A.update_id = U.update_id
						AND A.category = '".$category."' 
						AND U.platform = A.platform_id
						GROUP BY A.app_id, A.platform
						ORDER BY A.released DESC 
						LIMIT ".$offset.",".$limit;
					
					$appArray = $sql->getFullArray($q);
				
					for($i=0; $i<$sql->getCount($q); $i++){
						// Check if the user is in groups connected with apps that got non public releases.
						if($sql->getCount("SELECT user_id FROM `".$sql->prefix."UserInGroup` 
							WHERE group_id IN (SELECT group_id FROM ".$sql->prefix."GroupApplication WHERE app_id = ".$appArray[$i]['app_id'].") 
							AND user_id=".$user->getId()."") > 0 || 
							$sql->getCount("SELECT user_id FROM `".$sql->prefix."UserApplication` WHERE user_id=".$user->getId()." AND app_id=".$appArray[$i]['app_id']."") > 0){
							// Check if the last release is public
							$check_release = $sql->getArray("
								SELECT public FROM ".$sql->prefix."ApplicationUpdates
								 WHERE app_id=".$appArray[$i]['app_id']." 
								 AND platform=".$appArray[$i]['platform_id']." ORDER BY released DESC LIMIT 1");
							if($check_release['public'] == 1){
								$output[] = $appArray[$i];
							} else {
								$temp = array();
								$temp['app_id'] = $appArray[$i]['app_id'];
								$temp['name'] = $appArray[$i]['name'];
								$temp['desc'] = $appArray[$i]['desc'];
								$temp['released'] = $appArray[$i]['released'];
								$temp['platform'] = $appArray[$i]['platform'];
								$temp['category'] = $appArray[$i]['category'];
								$temp['platform_id'] = $appArray[$i]['platform_id'];
								$q = "SELECT update_id, changelog, revision_nr, url
								FROM ".$sql->prefix."ApplicationUpdates 
								WHERE platform = '".$appArray[$i]['platform_id']."'
								AND app_id = '".$appArray[$i]['app_id']."' ORDER BY released DESC LIMIT 1";
								$ar = $sql->getArray($q);
								$temp['update_id'] = $ar['update_id'];
								$temp['changelog'] = $ar['changelog'];
								$temp['revision_nr'] = $ar['revision_nr'];
								$temp['url'] = $ar['url'];
								$output[] = $temp;
							}
						} else {
							$output[] = $appArray[$i];
						}
					}
					// Now gather applications that haven't published an public update yet
					$query = "SELECT A.`app_id` , A.`name` , A.`desc` , MAX( U.`released` ) AS released, P.`name` AS platform, P.`platform_id`, C.`category_id` AS category, MAX( U.`update_id` ) AS update_id, U.changelog, U.revision_nr, U.url
						FROM ".$sql->prefix."Application A, ".$sql->prefix."ApplicationUpdates U, ".$sql->prefix."Platform P, ".$sql->prefix."Category C, ".$sql->prefix."ApplicationCategory CU
						WHERE U.`app_id` = A.`app_id` 
						AND CU.`app_id` = A.`app_id` 
						AND C.`category_id` = CU.`category_id` 
						AND P.`platform_id` = U.`platform` 
						AND A.`public` = 1
						AND U.`public` = 0
						AND C.`category_id` = ".$category."
						AND A.app_id NOT IN (SELECT app_id FROM ".$sql->prefix."ApplicationView)
						GROUP BY U.`app_id` ,  `platform` LIMIT ".$offset.",".$limit;
					
					$appArray = $sql->getFullArray($query);
					for($i=0; $i<$sql->getCount($q); $i++){
						// Check if the user is in groups connected with apps that got non public releases.
						if($sql->getCount("SELECT user_id FROM `".$sql->prefix."UserInGroup` WHERE group_id IN (SELECT group_id FROM ".$sql->prefix."GroupApplication WHERE app_id = ".$appArray[$i]['app_id'].") AND user_id=".$user->getId()) > 0 || 
							$sql->getCount("SELECT user_id FROM `".$sql->prefix."UserApplication` WHERE user_id=".$user->getId()." AND app_id=".$appArray[$i]['app_id']) == 1){
							$output[] = $appArray[$i];			
						}
					}						
					// Should print the whole application array				
					print(json_encode($output));
				} else {
					$app_id = $_REQUEST['id'];
					$plat = $_REQUEST['platform'];
					$sql = new SQL();					
					$q = "SELECT A.`app_id` , A.`name` , A.`desc` , A.released, A.platform, A.category, U.update_id, U.changelog, U.revision_nr, U.url
						FROM ".$sql->prefix."ApplicationView A,".$sql->prefix."ApplicationUpdates U 
						WHERE A.app_id = U.app_id
						AND A.update_id = U.update_id 
						AND U.platform = A.platform_id
						AND A.app_id = ".$app_id."
						AND A.platform_id = ".$plat."
						GROUP BY A.app_id, A.platform LIMIT 1";
					$output[] = $sql->getArray($q);
					print(json_encode($output));
				}
			}
		}
	}	
?>