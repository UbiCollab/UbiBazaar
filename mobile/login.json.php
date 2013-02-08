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
	$email = $_REQUEST['username'];
	$passord = $_REQUEST['password'];

	$output = array();
	
	if($email != "" && $passord != ""){
		if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){ 					
			$user = new User();
//			;
			if($user->loginMobile($email, $passord)){
				$output["loggedin"] = "true";
				$output["error"] = "";
				$output["id"] = $user->getId();
				$output["name"] = $user->getName();
			} else {
				$output["loggedin"] = "false";
				$sql = new SQL();
				$query = sprintf("SELECT * FROM ".$sql->prefix."User WHERE email='%s' AND activate='NULL' AND deleted=1",
					mysql_real_escape_string($email));
				$count = $sql->getCount($query);
				if($count == 1){
					$output["error"] = "The user has been banned. For more information please contact the administrators.";
				} else {
					$output["error"] = "Incorrect username or password.";
				}
				$output["name"] = "";
			}
		} else {
			$output["loggedin"] = "false";
			$output["error"] = "Not a valid email address.";
			$output["name"] = "";
		}
		print(json_encode($output));
	} else {
		include '../engine/handler/layout.handler.php';
		$output['check_sum'] = md5(stripslashes(LayoutManager::curURL()));
		print(json_encode($output));
	}
?>