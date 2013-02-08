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

	// Fetch the userdata
	$user = LayoutManager::getUser();
	
	// Check the user
	if($user->isLoggedIn() && $user->isAdmin()){
		// User is an admin
		
		// header
		echo "<center>
		<a href=\"?module=category\">Category</a> - <a href=\"?module=app\">Application</a> - Platform - <a href=\"?module=tag\">Tags</a>
		</center>";
		// Establish sql connection
		$sql = new SQL();
		
		// Handlers
		switch ($_GET['action']){
			case 'edit':
				if(eregi("^[0-9]{1,11}$", $_GET['id'])){
					// Edit a existing platform
					if($_POST['name'] != "" && $_POST['desc'] != ""){
						$query = sprintf("UPDATE `".$sql->prefix."Platform` SET `name`='%s', `description`='%s'  WHERE `platform_id`='%s'",
							mysql_real_escape_string(trim(strip_tags($_POST['name']))),
							mysql_real_escape_string(trim(strip_tags($_POST['desc']))),
							mysql_real_escape_string($_GET['id']));
						// Do the sql-update
						if($sql->doUpdate($query)){
							LayoutManager::alertSuccess("Platform successfully updated.");
						} else {
							LayoutManager::alertFailure("Unexpected error while handling your request.");
						}
					} else {
						$query = "SELECT * FROM ".$sql->prefix."Platform WHERE platform_id=".$_GET['id'];
						$cat = $sql->getArray($query);
						echo "<form action=\"?module=platform&action=edit&id=".$cat['platform_id']."\" method=\"post\">
						  <div class=\"form_settings\" style=\"border: 1px solid #999; padding: 5px; margin-bottom: 5px;\">
						  	<p><b>Change information</b></p>
						    <p><span>Name</span><input type=\"text\" name=\"name\" class=\"contact\" value=\"".$cat['name']."\"/></p>
						    <p><span>Description</span><input type=\"text\" name=\"desc\" class=\"contact\" value=\"".$cat['description']."\"/></p>
						    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
						  </div>
						</form>";
					}
				}
				break;
			case 'create':
				if($_POST['name'] != "" && $_POST['desc'] != ""){
					$query = sprintf("INSERT INTO ".$sql->prefix."Platform (`name`, `description`) VALUES ('%s', '%s')",
						mysql_real_escape_string(trim(strip_tags($_POST['name']))),
						mysql_real_escape_string(trim(strip_tags($_POST['desc']))));
					
					// Do the sql-update
					if($sql->doUpdate($query)){
						LayoutManager::alertSuccess("Platform successfully created.");
					} else {
						LayoutManager::alertFailure("Unexpected error while handling your request.");
					}
				} else {
					echo "<form action=\"?module=platform&action=create\" method=\"post\">
					  <div class=\"form_settings\" style=\"border: 1px solid #999; padding: 5px; margin-bottom: 5px;\">
					  	<p><b>New platform</b></p>
					    <p><span>Name</span><input type=\"text\" name=\"name\" class=\"contact\"/></p>
					    <p><span>Description</span><input type=\"text\" name=\"desc\" class=\"contact\"/></p>
					    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
					  </div>
					</form>";
				}
				break;
			case 'delete':
				// Delete the platform and all releases done on that platform
				// is this something we want?
				if(eregi("^[0-9]{1,11}$", $_GET['id'])){

				}
				break;
			default:
				// DO NOTHING
				break;
		}
		
		// List all the categorys
		$query = "SELECT * FROM ".$sql->prefix."Platform";
		$categorys = $sql->getFullArray($query);
		
		echo "<table width=100% cellspacing=0 cellpadding=0>";
		echo "<tr><td><b>Platform</b></td><td><b>Action</b></td></tr>";
		foreach ($categorys as $cat) {
			echo "<tr>
				<td>".$cat['name']."</td>
				<td>
					<a href=\"?module=platform&action=edit&id=".$cat['platform_id']."\">Edit</a>
					<a href=\"?module=platform&action=delete&id=".$cat['platform_id']."\">Delete</a>";
				echo "</td>
			</tr>";
		}
		echo "</table>";
		echo "<a href=\"?module=platform&action=create\">Create a new platform</a>";
	} else {
		// redirect back
		LayoutManager::redirect("../");
	}
?>