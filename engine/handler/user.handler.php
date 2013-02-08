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
	class User {
		
		private $name;
		private $passWord;
		private $email;
		private $loggedIn;
		private $userId;
		private $unreadMsg;
		private $role; // Only used when displayed from the groups perspective
		private $joined; // Only used when displayed from the groups perspective
		private $profile_pic;
		
		function __construct() {
			$this->email = "";
			$this->passWord = "";
			$this->userId = 0;
			$this->loggedIn = false;
		}
		
		function __destruct(){
			// KILL
		}
		
		/**
		 * Called when the user logges in at the website.
		 * 
		 * @param string $username
		 * @param string $password
		 */
		public function login($username, $password) {
			$this->email = $username;
			$this->passWord = md5($password);
			$this->loggedIn = false;
			// Checking if the credentials is correct and logging the user in.
			// Output is posted.
			$this->checkCredentials(false);
			if($this->isLoggedIn())
				$this->updateMsgCount();
		}
		
		/**
		 * 
		 * Gather information about a user based on his/her ID.
		 * @param int $id a user_id
		 */
		public function getInfo($id) {
			$sql = new SQL();
			$this->userId = $id;
			$array = $sql->getArray("SELECT * FROM ".$sql->prefix."User WHERE user_id=".$this->userId);
			$this->email = $array['email'];
			$this->name = $array['name'];
			$this->profile_pic = $array['picture'];
		}
		
		/**
		 * Called when logging in via a mobile application
		 * 
		 * @param string $username
		 * @param string $password
		 */
		public function loginMobile($username, $password) {
			$this->email = $username;
			$this->passWord = $password;
			
			// Checking if the credentials is correct and logging the user in.
			// Output is not posted.
			$this->checkCredentials(true);
			return $this->loggedIn;
		}

		/**
		 * Used to create a new user-object.
		 * Basicly like a constructor
		 * 
		 * @param string $name
		 * @param string $email
		 * @param id $id
		 */
		public function newUser($name, $email,$id) {
			$this->email = $email;
			$this->name = $name;
			$this->userId = (int)$id;
			$sql = new SQL();
			$array = $sql->getArray("SELECT * FROM ".$sql->prefix."User WHERE user_id=".$this->userId);
			$this->profile_pic = $array['picture'];
		}
		
		/**
		 * 
		 * Checks the users credentials
		 * The $check paramter is if error messages is to be shown or not
		 * 
		 * @param boolean $check
		 */
		private function checkCredentials($check) {
			if($this->email != "" && $this->passWord != ""){
				$sql = new SQL();
				$query = sprintf("SELECT * FROM ".$sql->prefix."User WHERE email='%s' AND activate='NULL' AND deleted=0",
						mysql_real_escape_string($this->email));
						
				// Counter the user
				$user_db_count = $sql->getCount($query);
				
				// Fetching array with data
				$db_array = $sql->getArray($query);
				
				// Fetching the data from the array
				$db_usrpass = $db_array['password'];
				$this->dbWord = $db_usrpass;
				$this->userId = $db_array['user_id'];
				$this->name = $db_array['name'];
		
				// Doing the checks.
				if(($user_db_count > 0) && ($this->passWord == $db_usrpass)){	
					$this->loggedIn = true;
					if(!$check){
						// The user is logged in and the session is active until the browser windows is closed.
						$_SESSION['user'] = $this;
						
						$url = "./";
						
						if(isset($_SESSION['ref'])){
							// Swapping characters around
							$url = $_SESSION['ref'];
							unset($_SESSION['ref']);
						}
															
						// Redirect the user to the frontpage
						print("<b><font color=\"#00FF00\">Success!</font></b><br>");
						LayoutManager::redirect($url);
					}
				} else {
					if(!$check){
						$query = sprintf("SELECT * FROM ".$sql->prefix."User WHERE email='%s' AND activate='NULL'",
							mysql_real_escape_string($this->email));
						$count = $sql->getCount($query);
						if($user_db_count == 0 && $count == 1 ){
							LayoutManager::alertFailure("The user has been banned. For more information please contact the administrators.");
						} else {
							LayoutManager::alertFailure("Wrong username or password. (Error code: #2)");
						}
						print("<font color=\"#FF0000\">Failed..</font><br>\n");
						print("<a href=\"javascript:history.back(1)\">Back</a>\n");
					}	
					$this->loggedIn = false;
				}
			}
		}
		
		/**
		 * 
		 * Checks the users credentials and returns true / false if the credentials is correct or not.
		 * @return boolean true/falsed based on if the user is logged in or not.
		 */
		public function isLoggedIn() {
			$this->checkCredentials(true);
			return $this->loggedIn;
		}
		
		/**
		 * For outputting a link to the users profile.
		 */
		public function toString() {
			return "<a href=\"?module=user&action=view&id=$this->userId\">".$this->name."</a>";
		}
		
		/**
		 * Function to force an update of the unread messages to the user.
		 * !! For internal use !!
		 */
		private function updateMsgCount() {
			$sql = new SQL();
			$db_query = "SELECT * FROM ".$sql->prefix."UserMsg WHERE to_u=".$this->userId." AND unread=true";
			$this->unreadMsg = $sql->getCount($db_query);
		}
		
		/**
		 * Function to get the updated unread messages count
		 * 
		 */
		public function getUnreadCount() {
			$this->updateMsgCount();
			return $this->unreadMsg;
		}
		
		/**
		 * Function to change the users password.
		 * 
		 * @param unknown_type $old
		 * @param unknown_type $new
		 * @param unknown_type $confNew
		 */
		public function changePassword($old, $new, $confNew) {
			include 'engine/values/site.values.php';
			$old = md5($old);
			if($old == $this->passWord){
				$new = md5($new);
				$confNew = md5($confNew);
				if($new == $confNew){
					if(strlen($new)>=$security_length){
						if($this->isLoggedIn()){
							$sql = new SQL();
							$result = $sql->doUpdate("UPDATE ".$sql->prefix."User SET password='".$new."' WHERE user_id='$this->userId'");
							
							$this->passWord = $new;
							
							unset($_SESSION['user']);
							$_SESSION['user'] = $this;
							
							if($result)
								return true;
							else {
								LayoutManager::alertFailure("Unexpected error, please try again.");
							}
						} else {
							LayoutManager::alertFailure("You are not logged in.");
						}
					} else {
						LayoutManager::alertFailure("Password was not long enough, needs to be atleast ".$security_length.".");
					}
				} else {
					LayoutManager::alertFailure("The two password you entered is not correct.");
				}
			} else {
				LayoutManager::alertFailure("Old password is not correct.");
			}
			return false;
		}
		
		/**
		 * Returns the users name.
		 */
		public function getName() {
			return $this->name;
		}

		/**
		 * Only used from groups perspective
		 */
		public function getRole() {
			return $this->role;
		}
		
		/**
		 * Returns the url for the users profile picture
		 */
		public function getPicture() {
			return $this->profile_pic;
		}

		/**
		 * Only used from groups perspective
		 */
		public function setRole($param) {
			$this->role = $param;
		}
		
		/**
		 * Only used from groups perspective
		 */
		public function getJoined() {
			return $this->joined;
		}

		/**
		 * Only used from groups perspective
		 */
		public function setJoined($param) {
			$this->joined = $param;
		}
			
		/**
		 * Function to retrive the users email.
		 */
		public function getUserName() {
			return $this->email; 
		}
		
		/**
		 * 
		 * Returns the users id.
		 */
		public function getId() {
			return $this->userId;
		}
		
		/**
		 * Function that add / removes a user that the user will follow/unfollow
		 * 
		 * @param int $userId
		 */
		public function followUser($userId){
			$sql = new SQL();
			if(eregi("^[0-9]{1,11}$", $userId)){
				if($userId != $this->userId){
					if($sql->getCount("SELECT * FROM ".$sql->prefix."UserFollowUser WHERE follower = '".$this->userId."' AND following = '".$userId."'")> 0){
						return $sql->doUpdate("DELETE FROM ".$sql->prefix."UserFollowUser WHERE follower = '".$this->userId."' AND following = '".$userId."'");
					} else {
						return $sql->doUpdate("INSERT INTO ".$sql->prefix."UserFollowUser (following, follower) VALUES ('$userId', '".$this->userId."')");
					}
				} else 
				return false;
			} else 
			return false;
		}
		
		/**
		 * Function that add / removes an app the user will follow/unfollow
		 * 
		 * @param unknown_type $id
		 */
		public function followApp($id){
			if(eregi("^[0-9]{1,11}$", $id)){
				$sql = new SQL();
				if($sql->getCount("SELECT * FROM ".$sql->prefix."UserFollowApplication WHERE follower = '".$this->userId."' AND app_id = '".$id."'")> 0){
					return $sql->doUpdate("DELETE FROM ".$sql->prefix."UserFollowApplication WHERE follower = '".$this->userId."' AND app_id = '".$id."'");
				} else {
					return $sql->doUpdate("INSERT INTO ".$sql->prefix."UserFollowApplication (app_id, follower) VALUES ('$id', '".$this->userId."')");
				}
			} else 
			return false;
		}
		
		/**
		 * Function that will return the users applications
		 * 
		 * @param int $max
		 * @param int $offset
		 * @param boolean $public
		 */
		public function getApplications($max, $offset, $public) {
			if($max == 0)
				$max = 100;
			$sql = new SQL();
			if($public){
				$query = sprintf("SELECT * FROM ".$sql->prefix."UserApplication WHERE user_id='%s' AND app_id IN (SELECT app_id FROM ".$sql->prefix."Application WHERE public=1) LIMIT ".$offset.",".$max,
				mysql_real_escape_string($this->userId));
				$result = $sql->getFullArray($query);
				$output = array();
				for($i=0; $i<count($result); $i++){
					$res = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationView WHERE app_id=".$result[$i]['app_id']);
					for ($j = 0; $j < count($res); $j++) {
						$app = new App();
						$app->getInfoFromSQL($res[$j]['app_id'], $res[$j]['platform_id']);
						$output[] = $app;
					}	
				}
				return $output;
			} else {
				$query = sprintf("SELECT * FROM ".$sql->prefix."UserApplication WHERE user_id='%s' LIMIT ".$offset.",".$max,
				mysql_real_escape_string($this->userId));
				$result = $sql->getFullArray($query);
				$output = array();
				for($i=0; $i<count($result); $i++){
					$app = new App("", "", "", "", "", "");
					$app->getBasicInfo($result[$i]['app_id']);
					$output[] = $app;
				}
				return $output;
			}
		}
		
		/**
		 * Get the groups the user is connected too
		 * 
		 * @param int $max
		 * @param int $offset
		 */
		public function getGroups($max, $offset) {
			if($max == 0)
				$max = 100;
			$sql = new SQL();
			$query = "SELECT * FROM `".$sql->prefix."UserInGroup` WHERE user_id=".$this->userId." ORDER BY role LIMIT ".$offset.",".$max;
			$array = $sql->getFullArray($query);
			$output = array();
			for ($i = 0; $i < count($array); $i++) {
				$group = new Group($array[$i]['group_id']);
				$group->setRole($array[$i]['role']);
				$output[] = $group;
			}
			return $output;
		}
	
		/**
		 * Get the devices of the user
		 * 
		 * @param int $max
		 * @param int $offset
		 */
		public function getDevices($max, $offset) {
			if($max == 0)
				$max = 100;
			$sql = new SQL();
			$query = "SELECT user_device_id FROM `".$sql->prefix."UserDevice` WHERE user_id=".$this->userId." LIMIT ".$offset.",".$max;
			$array = $sql->getFullArray($query);
			$output = array();
			for ($i = 0; $i < count($array); $i++) {
				$device = new Device($array[$i]['user_device_id']);
				$output[] = $device;
			}
			return $output;
		}
		
		/**
		 * Is a user followin an app based on $app_id
		 * 
		 * @param int $app_id
		 */
		public function followingApp($app_id) {
			if(eregi("^[0-9]{1,11}$", $app_id)){
				$sql = new SQL();
				if($sql->getCount("SELECT * FROM ".$sql->prefix."UserFollowApplication WHERE follower=".$this->userId." AND app_id=".$app_id)>0)
					return true;
			}
			return false;
		}
		
		/**
		 * Is a user following an user based on $user_id
		 * 
		 * @param int $user_id
		 */
		public function followingUser($user_id) {
			if(eregi("^[0-9]{1,11}$", $user_id)){
				$sql = new SQL();
				if($sql->getCount("SELECT * FROM ".$sql->prefix."UserFollowUser WHERE follower=".$this->userId." AND following=".$user_id)>0)
					return true;
			}
			return false;
		}
		
		/**
		 * Function that returns true/false depending on if the user
		 * is an administrator or not.
		 * 
		 */
		public function isAdmin() {
			$sql = new SQL();
			if($sql->getCount("SELECT user_id FROM ".$sql->prefix."AdminUser WHERE user_id=".$this->userId) == 1)
				return true;
			return false;
		}
	}
?>