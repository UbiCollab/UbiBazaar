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
	class Group {
		
		private $group_id;
		private $name;
		private $description;
		private $app; // Not implemeted yet
		private $public;
		private $role;
		
		function __construct($group_id){
			if(eregi("^[0-9]{1,11}$", $group_id)){
				$this->group_id = $group_id;
				$this->fetchData();
			}
		}
		
		/**
		 * 
		 * Gathering information from the SQL-database
		 */
		private function fetchData() {
			$sql = new SQL();
			$array = $sql->getArray("SELECT * FROM `".$sql->prefix."Group` WHERE group_id='".$this->group_id."'");
			
			$this->name = $array['name'];
			$this->description = nl2br (strip_tags ($array['desc']));
			$this->public = $array['public'];
			
			$array = $sql->getArray("SELECT * FROM `".$sql->prefix."GroupApplication` WHERE group_id='".$this->group_id."'");
			$this->app = new App();
			$this->app->getBasicInfo($array['app_id']);
		}
		
		/**
		 * 
		 * Remove a user from the Group.
		 * 
		 * @param int $user_id The user_id that's getting removed from the group
		 * @return string $output true or false depending on the success
		 */
		public function removeUser($user_id) {
			if(eregi("^[0-9]{1,11}$", $user_id)){
				$sql = new SQL();
				$array = $sql->getArray("SELECT role FROM `".$sql->prefix."UserInGroup` WHERE group_id=".$this->group_id." AND user_id=".$user_id);
				$user_role = $array['role'];
				if($user_role != "Owner"){
					$query = sprintf("DELETE FROM `".$sql->prefix."UserInGroup` WHERE `group_id`='%s' AND user_id='%s'",
						mysql_real_escape_string($this->group_id),
						mysql_real_escape_string($user_id));
					$output = $sql->doUpdate($query);
				} else 
					$output = false;	
			} else 
				$output = false;
			return $output;
		}
		
		/**
		 * Function to add(invite) a user to a group.
		 * 
		 * @param int $user_id The user_id for the user that's going to be add to the group
		 * @return string $output true or false depending on the success
		 */
		public function addUser($user_id) {
			if(eregi("^[0-9]{1,11}$", $user_id)){
				$sql = new SQL();
				$query = sprintf("INSERT INTO `".$sql->prefix."UserInGroup` (user_id, group_id, role) VALUES ('%s', '%s', 'Member')",
					mysql_real_escape_string($user_id),
					mysql_real_escape_string($this->group_id));
				$output = $sql->doUpdate($query);
			} else 
				$output = false;
			return $output;
		}
		
		/**
		 * Functio to change a users role in the specified group
		 * 
		 * @param int $user_id The user_id for the user that's role is subject to change.
		 * @return string $output true or false depending on the success
		 */
		public function changeRole($user_id) {
			if(eregi("^[0-9]{1,11}$", $user_id)){
				$sql = new SQL();
				$array = $sql->getArray("SELECT role FROM `".$sql->prefix."UserInGroup` WHERE group_id=".$this->group_id." AND user_id=".$user_id);
				$user_role = $array['role'];
				if($user_role != "Owner"){
					if($user_role != "Member")
						$text = "UPDATE `".$sql->prefix."UserInGroup` SET `role`= 'Member' WHERE `group_id`=".$this->group_id." AND user_id=".$user_id;
					else 
						$text = "UPDATE `".$sql->prefix."UserInGroup` SET `role`= 'Administrator' WHERE `group_id`=".$this->group_id." AND user_id=".$user_id;
					$output = $sql->doUpdate($text);
				} else 
					$output = false;	
			} else
				$output = false;
			return $output;
		}
		
		/**
		 * 
		 * Returns a full User SQL-array with all the users in the group
		 * @return string array 
		 */
		public function getMembers() {
			$sql = new SQL();
			$query = "SELECT * FROM ".$sql->prefix."UserInGroup WHERE group_id = '".$this->group_id."' ORDER BY role ASC";
			$result = $sql->getFullArray($query);
			for ($i = 0; $i < count($result); $i++) {
				$user = new User();
				$user->getInfo($result[$i]['user_id']);
				$user->setRole($result[$i]['role']);
				$user->setJoined($result[$i]['joined']);
				$output[] = $user;
			}
			return $output;
		}
		
		/**
		 * 
		 * Returns a full User SQL-array with all the users that's not already in the group.
		 * @return string array
		 */
		public function getInvitableUsers() {
			$sql = new SQL();
			$query = "SELECT user_id FROM ".$sql->prefix."User WHERE user_id NOT IN (SELECT user_id FROM ".$sql->prefix."UserInGroup WHERE group_id = '".$this->group_id."') AND deleted=0";
			$result = $sql->getFullArray($query);
			for ($i = 0; $i < count($result); $i++) {
				$user = new User();
				$user->getInfo($result[$i]['user_id']);
				$output[] = $user;
			}
			return $output;
		}
				
		public function inviteUser($user, $user_id) {
			if(eregi("^[0-9]{1,11}$", $user_id)){
				$sql = new SQL();
				if($sql->getCount("SELECT user_id FROM ".$sql->prefix."UserInGroup WHERE group_id = '".$this->group_id."' AND user_id='".$user_id."'") == 0){
					$query = sprintf("SELECT * FROM `".$sql->prefix."Group` WHERE group_id='%s'",
						mysql_real_escape_string($this->group_id));
					$ar = $sql->getArray($query);
					$query = sprintf("INSERT INTO `".$sql->prefix."UserInGroup` (user_id, group_id, role) VALUES ('%s', '%s', 'Invited')",
						mysql_real_escape_string($user_id),
						mysql_real_escape_string($this->group_id));
						$result = $sql->doUpdate($query);
$msg = "Hi.\n
You have been invited to the group '".$ar['name']."'.

<a href=\"?module=group&change=true&id=".$ar['group_id']."&a=true\">Click here</a> to accept the invitation or <a href=\"?module=group&change=true&id=".$ar['group_id']."&a=false\">Click here</a> to decline.

With best regards
".$user->getName();														
					$query = sprintf("INSERT INTO `".$sql->prefix."UserMsg` (to_u, from_u, header, msg) VALUES ('%s', '%s', 'Group invitation','%s')",
						mysql_real_escape_string($user_id),
						mysql_real_escape_string($user->getId()),
						mysql_real_escape_string($msg));
						$result = $sql->doUpdate($query);
					if(!$result){
						$output['error'] = "true";
						$output['msg'] = "Unexpected error.";
					} else {
						$output['error'] = "false";
						$output['msg'] = "Unexpected error.";
					}
				} else {
					$output['error'] = "true";
					$output['msg'] = "User already invited.";
				}
			} else {
				$output['error'] = "true";
				$output['msg'] = "Invalid user id.";
			}
			return $output;
		}
		
		/**
		 * Function to change the visibility of the group.
		 * 
		 * @return string $output depending on the success
		 */
		public function changePrivacy() {
			$sql = new SQL();
			$query = "SELECT public FROM `".$sql->prefix."Group` WHERE group_id='".$this->group_id."'";
			$ar = $sql->getArray($query);
			if($ar['public'])
				$text = "UPDATE `".$sql->prefix."Group` SET `public`= '0' WHERE `group_id`=".$this->group_id;
			else 
				$text = "UPDATE `".$sql->prefix."Group` SET `public`= '1' WHERE `group_id`=".$this->group_id;
			$output = $sql->doUpdate($text);
			return $output;
		}
			
		public function toString() {
			return "<a href=\"?module=group&id=".$this->group_id."\">".$this->name."</a>";
		}
		
		/*
		 *  GETTERS / SETTERS
		 */
		public function getId() {
			return $this->group_id;
		}
		
		public function setRole($param) {
			$this->role = $param;
		}
		
		public function getName() {
			return $this->name;
		}
		
		public function getDescription() {
			return $this->description;
		}
		
		public function getRole() {
			return $this->role;
		}
		
		public function isPublic() {
			return $this->public;
		}
		
		public function getApp() {
			return $this->app;
		}
		
		/**
		 * 
		 * Compare the ID of two groups
		 * @param Group $group Group object
		 */
		public function compare(Group $group){
			if($this->group_id == $group->getId())
				return true;
			return false;
		}
	}
?>