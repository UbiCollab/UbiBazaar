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
	class Device {
		
		private $name;
		private $device_id;
		private $serial;
		private $platform;
		private $os_version;
		private $last_update;
		private $user;
		
		function __construct($device_id) {
			$this->device_id = $device_id;
			$sql = new SQL();
			$array = $sql->getArray("SELECT * FROM `".$sql->prefix."UserDevice` WHERE user_device_id=".$this->device_id);
			$this->serial = $array['serialnumber'];
			$this->platform = $array['platform'];
			$this->os_version = $array['os_version'];
			$this->name = $array['name'];
			$this->last_update = $array['last_update'];
			$this->user = $array['user_id'];
		}
		
		/**
		 * Will output an row with options to manage the specified device based on the object data
		 */
		public function printManageRow() {
			echo "<tr>
			<td><a href=\"?module=me&action=device&a=view&id=".$this->device_id."\">".$this->name."</a></td>
			<td><img src=\"images/platform/".$this->platform.".png\" border=0 width=12 height=12> ".$this->os_version."</td>
			<td>".strftime ("%a %b %d, %Y %H:%M", strtotime($this->last_update))."</td>
			<td>[<a href=\"?module=me&action=device&a=delete&id=".$this->device_id."\" onclick=\"return rmItem()\">Delete device</a>]</td>
			</tr>";
		}
		
		/**
		 * Default toString to link to the device
		 */
		public function toString() {
			return "<a href=\"?module=me&action=device&a=view&id=$this->device_id\">".$this->name."</a>  <img src=\"images/platform/".$this->platform.".png\" border=0 width=12 height=12>".$this->os_version;
		}
		
		/**
		 * !!Not implemented yet!!
		 * 
		 * @param unknown_type $app_id
		 */
		public function installApp($app_id) {
			// TODO: THIS;
		}
		
		/**
		 * !! Not implemented yet!!
		 * 
		 * @param unknown_type $app_id
		 */
		public function uninstallApp($app_id) {
			// TODO: THIS;
		}
		
		public function getName() {
			return $this->name;
		}
		
		public function getId() {
			return $this->id;
		}
		
		public function getUser() {
			return $this->user;
		}
		
		public function getPlatform() {
			return $this->platform;
		}
		
		public function getOs(){
			return $this->os_version;
		}
		
	}

?>