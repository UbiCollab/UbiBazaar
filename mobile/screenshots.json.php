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
				
				// Output array
				$output = array();
				$sql = new SQL();
				$query = "SELECT url FROM ".$sql->prefix."ApplicationScreenshots WHERE app_id=".$id;
				$ss = $sql->getFullArray($query);
				foreach ($ss as $shot) {
					$asd['url'] = "files/ss/".$shot['url'];
					$output[] = $asd;
				}
				print(json_encode($output));
			}
		}
	}	
?>