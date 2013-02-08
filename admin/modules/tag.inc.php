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
		// Header
		// header
		echo "<center>
		<a href=\"?module=category\">Category</a> - <a href=\"?module=app\">Application</a> - <a href=\"?module=platform\">Platform</a> - Tags
		</center>";
		// Establish sql connection
		$sql = new SQL();
		if(eregi("^[0-9]{1,11}$", $_GET['id'])){
			switch($_GET['action']){
				case 'delete':
					$query = "DELETE FROM ".$sql->prefix."ApplicationTag WHERE tag_id=".$_GET['id'];
					$sql->doUpdate($query);
					$query = "DELETE FROM ".$sql->prefix."Tag WHERE tag_id=".$_GET['id'];
					if($sql->doUpdate($query))
						LayoutManager::alertSuccess("Tag has been deleted.");
					break;
				default:
					// DO NOTHING
					break;
			}
		}

		$tags = $sql->getFullArray("SELECT * FROM ".$sql->prefix."Tag ORDER BY added DESC");
	
		echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>\n";
		echo "<tr><td><b>Tag</b></td><td><b>Action</b></td></tr>";
		for ($i = 0; $i < count($tags); $i++) {
			echo "<tr><td class=\"edit_text\" id=\"".$tags[$i]['tag_id']."\">".$tags[$i]['tag']."</td><td><a href=\"?module=tag&action=delete&id=".$tags[$i]['tag_id']."\">Delete</a></td></tr>\n";
		}
		echo "</table>\n";		
	} else {
		LayoutManager::redirect("../");
	}
?>