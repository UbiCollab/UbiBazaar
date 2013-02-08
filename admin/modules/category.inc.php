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
		Category - <a href=\"?module=app\">Application</a> - <a href=\"?module=platform\">Platform</a> - <a href=\"?module=tag\">Tags</a>
		</center>";
		// Establish sql connection
		$sql = new SQL();
		
		// Handlers
		switch ($_GET['action']){
			case 'edit':
				if(eregi("^[0-9]{1,11}$", $_GET['id'])){
					// Edit a existing category
					if($_POST['name'] != "" && $_POST['desc'] != ""){
						$query = sprintf("UPDATE `".$sql->prefix."Category` SET `name`='%s', `desc`='%s'  WHERE `category_id`='%s'",
							mysql_real_escape_string(trim(strip_tags($_POST['name']))),
							mysql_real_escape_string(trim(strip_tags($_POST['desc']))),
							mysql_real_escape_string($_GET['id']));
						// Do the sql-update
						if($sql->doUpdate($query)){
							LayoutManager::alertSuccess("Category successfully updated.");
						} else {
							LayoutManager::alertFailure("Unexpected error while handling your request.");
						}
					} else {
						$query = "SELECT * FROM ".$sql->prefix."Category WHERE category_id=".$_GET['id'];
						$cat = $sql->getArray($query);
						echo "<form action=\"?module=category&action=edit&id=".$cat['category_id']."\" method=\"post\">
						  <div class=\"form_settings\" style=\"border: 1px solid #999; padding: 5px; margin-bottom: 5px;\">
						  	<p><b>Change information</b></p>
						    <p><span>Name</span><input type=\"text\" name=\"name\" class=\"contact\" value=\"".$cat['name']."\"/></p>
						    <p><span>Description</span><input type=\"text\" name=\"desc\" class=\"contact\" value=\"".$cat['desc']."\"/></p>
						    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
						  </div>
						</form>";
					}
				}
				break;
			case 'create':
				if($_POST['name'] != "" && $_POST['desc'] != ""){
					$query = sprintf("INSERT INTO ".$sql->prefix."Category (`name`, `desc`) VALUES ('%s', '%s')",
						mysql_real_escape_string(trim(strip_tags($_POST['name']))),
						mysql_real_escape_string(trim(strip_tags($_POST['desc']))));
					
					// Do the sql-update
					if($sql->doUpdate($query)){
						LayoutManager::alertSuccess("Category successfully created.");
					} else {
						LayoutManager::alertFailure("Unexpected error while handling your request.");
					}
				} else {
					echo "<form action=\"?module=category&action=create\" method=\"post\">
					  <div class=\"form_settings\" style=\"border: 1px solid #999; padding: 5px; margin-bottom: 5px;\">
					  	<p><b>New category</b></p>
					    <p><span>Name</span><input type=\"text\" name=\"name\" class=\"contact\"/></p>
					    <p><span>Description</span><input type=\"text\" name=\"desc\" class=\"contact\"/></p>
					    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
					  </div>
					</form>";
				}
				break;
			case 'delete':
				// Delete the category only if it's empty
				if(eregi("^[0-9]{1,11}$", $_GET['id'])){
					if($sql->getCount("SELECT * FROM ".$sql->prefix."ApplicationCategory WHERE category_id=".$cat['category_id']) == 0){
						$query = "DELETE FROM ".$sql->prefix."Category WHERE category_id=".$_GET['id'];
						if($sql->doUpdate($query)){
							LayoutManager::alertSuccess("Category has been deleted.");
						} else {
							LayoutManager::alertFailure("Unexpected error while handling your request.");
						}
					}
				}
				break;
			case 'move':
				if(eregi("^[0-9]{1,11}$", $_GET['id'])){
					if($sql->getCount("SELECT * FROM ".$sql->prefix."ApplicationCategory WHERE category_id=".$_GET['id']) > 0){
						// Move the apps from one category to another.
						if($_POST['category'] != ""){
							if(eregi("^[0-9]{1,11}$", $_POST['category'])){
								if($sql->getCount("SELECT category_id FROM `".$sql->prefix."Category` WHERE category_id=".$_POST['category'])){
									$query = sprintf("UPDATE `".$sql->prefix."ApplicationCategory` SET `category_id`='%s' WHERE `category_id`='%s'",
										mysql_real_escape_string($_POST['category']),
										mysql_real_escape_string($_GET['id']));
									// Execute the sql-query
									if($sql->doUpdate($query)){
										LayoutManager::alertSuccess("All applications have been moved.");
									} else {
										LayoutManager::alertFailure("Unexpected error while handling your request.");
									}
								}
							}
						} else {
							$query = "SELECT * FROM ".$sql->prefix."Category WHERE category_id=".$_GET['id'];
							$cat = $sql->getArray($query);
							echo "<form action=\"?module=category&action=move&id=".$_GET['id']."\" method=\"post\">
						  		<div class=\"form_settings\" style=\"border: 1px solid #999; padding: 5px; margin-bottom: 5px;\">";
							echo "Move applications currently in '<a href=\"../?module=app&cat=".$cat['category_id']."\" title=\"".$cat['desc']."\">".$cat['name']."</a>'. ";
							echo "To <select name=\"category\">";
						    $platforms = $sql->getFullArray("SELECT category_id, name FROM ".$sql->prefix."Category WHERE category_id <> ".$_GET['id']);
						    for ($i = 0; $i < count($platforms); $i++) {
						    	echo "<option value=\"".$platforms[$i]['category_id']."\">".$platforms[$i]['name']."</option>\n";
						    }
						    echo "</select>
						    <input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" />
						    </div>
						    </form>";
						}
					}
				}
				break;
			default:
				// DO NOTHING
				break;
		}
		
		// List all the categorys
		$query = "SELECT * FROM ".$sql->prefix."Category";
		$categorys = $sql->getFullArray($query);
		
		echo "<table width=100% cellspacing=0 cellpadding=0>";
		echo "<tr><td><b>Category</b></td><td><b>Action</b></td></tr>";
		foreach ($categorys as $cat) {
			echo "<tr>
				<td><a href=\"../?module=app&cat=".$cat['category_id']."\" title=\"".$cat['desc']."\">".$cat['name']."</a></td>
				<td>
					<a href=\"?module=category&action=edit&id=".$cat['category_id']."\">Edit</a>";
					if($sql->getCount("SELECT * FROM ".$sql->prefix."ApplicationCategory WHERE category_id=".$cat['category_id']) > 0){
						echo " <a href=\"?module=category&action=move&id=".$cat['category_id']."\">Move</a>";
					} else {
						echo " <a href=\"?module=category&action=delete&id=".$cat['category_id']."\"  onclick='return rmItem()'>Delete</a>";
					}
				echo "</td>
			</tr>";
		}
		echo "</table>";
		echo "<a href=\"?module=category&action=create\">Create a new category</a>";
	} else {
		// redirect back
		LayoutManager::redirect("../");
	}
?>