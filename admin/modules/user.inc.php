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
		// Establish sql connection
		$sql = new SQL();
		
		$deleted = "0";
		echo "<h2><center>";
		if($_GET['banned'] != ""){
			$deleted="1";
			echo "<a href=\"?module=user\">Users</a> - Banned Users";
		} else {
			echo "Users - <a href=\"?module=user&banned=true\">Banned Users</a>";
		}
		echo "</center></h2>";
		
		if(eregi("^[0-9]{1,11}$", $_GET['id'])){		
			switch($_GET['action']){
				case 'picture':
					// Fetch the pictureurl
					$ar = $sql->getArray("SELECT picture FROM `".$sql->prefix."User` WHERE user_id=".$_GET['id']);
					if($ar['picture'] != "pp_na.png"){
						$query = sprintf("UPDATE `".$sql->prefix."User` SET `picture`='%s' WHERE `user_id`='%s'",
							mysql_real_escape_string("pp_na.png"),
							mysql_real_escape_string($_GET['id']));
						if($sql->doUpdate($query)){
							FileHandler::delete("../images/user/".$ar['picture']);
						} else
							echo "Failed to update the mysql";
					}
					break;
				case 'admin':
					// Check if the user is the owner
					if($sql->getCount("SELECT user_id FROM ".$sql->prefix."AdminUser WHERE owner=1 AND user_id=".$_GET['id']) == 0 && $user->getId() != $_GET['id']){
						if($sql->getCount("SELECT user_id FROM ".$sql->prefix."AdminUser WHERE user_id=".$_GET['id']) == 1){
							// Remove the user from the Administrator table
							$query = "DELETE FROM `".$sql->prefix."AdminUser` WHERE user_id=".$_GET['id'];
							if(!$sql->doUpdate($query))
								echo "Mysql error while handling your request.";
							else
								LayoutManager::alertSuccess("User has been successfully demoted from administrator.");
						} else {
							$query = "INSERT INTO `".$sql->prefix."AdminUser` (user_id) VALUES ('".$_GET['id']."')";
							if(!$sql->doUpdate($query))
								echo "Mysql error while handling your request.";
							else
								LayoutManager::alertSuccess("User has been successfully promoted to administrator.");
						}
					}
					break;
				case 'ban':
					// Check if the user is the owner
					if($sql->getCount("SELECT user_id FROM ".$sql->prefix."AdminUser WHERE user_id=".$_GET['id']) == 0 && $user->getId() != $_GET['id']){
						$query = sprintf("UPDATE `".$sql->prefix."User` SET `deleted`='%s' WHERE `user_id`='%s'",
							mysql_real_escape_string("1"),
							mysql_real_escape_string($_GET['id']));
						if(!$sql->doUpdate($query))
							echo "Mysql error while handling your request.";
						else
							LayoutManager::alertSuccess("User has successfully been banned.");
					}
					break;
				case 'unban':
					// Check if the user is the owner
					$query = sprintf("UPDATE `".$sql->prefix."User` SET `deleted`='%s' WHERE `user_id`='%s'",
						mysql_real_escape_string("0"),
						mysql_real_escape_string($_GET['id']));
					if(!$sql->doUpdate($query))
						echo "Mysql error while handling your request.";
					else 
						LayoutManager::alertSuccess("User has successfully been unbanned.");
					break;
				case 'activate':
					if($sql->getCount("SELECT user_id FROM `".$sql->prefix."User` WHERE activate <> 'NULL' AND user_id=".$_GET['id']) == 1){
						$query = sprintf("UPDATE User SET activate='NULL' WHERE user_id='%s'",
							mysql_real_escape_string($_GET['id']));
						if(!$sql->doUpdate($query))
							echo "Mysql error while handling your request.";
						else {
							$ar = $sql->getArray("SELECT * FROM `".$sql->prefix."User` WHERE user_id=".$_GET['id']);
							include '../engine/values/site.values.php';
							// Let's mail the user!
$subject = $site_mail_subject." - Account activated";
$message = "
Hello ".$ar['name'].".

It is now possible to log in for the user: ".$ar['email']."

Click the following link to login: ".LayoutManager::curURL()."

".$site_mail_greet."

Please do not respond to this email.";
							
							if(!mail($ar['email'], $subject, $message, "From: ".$site_mail_subject."<".$site_mail_address.">\nX-Mailer: PHP/" . phpversion())){
								echo "Unable to mail the user due to an unexpected error.";
							} else {
								LayoutManager::alertSuccess("User has successfully been activated.");
							}
						}
					}
					break;
				default:
					break;
			}

		}
		// Grab thread-post count
		$post_count = $sql->getCount("SELECT * FROM ".$sql->prefix."User WHERE deleted=".$deleted);
		
		// how many rows to show per page
		$rowsPerPage = 16;
		
		// by default we show first page
		$pageNum = 1;
		
		// if $_GET['page'] defined, use it as page number
		if(isset($_GET['page'])){
		    $pageNum = $_GET['page'];
		}
		
		// counting the offset
		$offset = ($pageNum - 1) * $rowsPerPage;
		// Gather all the users
		$users = $sql->getFullArray("SELECT * FROM ".$sql->prefix."User WHERE deleted=".$deleted." LIMIT $offset,$rowsPerPage");
		
		// how many pages we have when using paging?
		$maxPage = ceil($post_count/$rowsPerPage);
		
		// print the link to access each page
		if($_GET['banned'] != ""){
			$self = "?module=user&banned=true";
		} else {
			$self = "?module=user";
		}
		$nav  = '';
		
		for($page = 1; $page <= $maxPage; $page++){
		   if ($page == $pageNum){
		      $nav .= " $page "; // no need to create a link to current page
		   } else {
		      $nav .= " <a href=\"$self&page=$page\">$page</a> ";
		   } 
		}
		if ($pageNum > 1){
		   $page  = $pageNum - 1;
		   $prev  = " <a href=\"$self&page=$page\" title=\"Previous\"><img src=\"../images/pageinator/prev.gif\"></a> ";
		
		   $first = " <a href=\"$self&page=1\" title=\"First Page\"><img src=\"../images/pageinator/first.png\"></a> ";
		} else {
		   $prev  = '&nbsp;'; // we're on page one, don't print previous link
		   $first = '&nbsp;'; // nor the first page link
		}
		
		if ($pageNum < $maxPage){
		   $page = $pageNum + 1;
		   $next = " <a href=\"$self&page=$page\" title=\"Next\"><img src=\"../images/pageinator/next.gif\"></a> ";
		
		   $last = " <a href=\"$self&page=$maxPage\" title=\"Last Page\"><img src=\"../images/pageinator/last.png\"></a> ";
		} else {
		   $next = '&nbsp;'; // we're on the last page, don't print next link
		   $last = '&nbsp;'; // nor the last page link
		}
		// print the navigation link
		echo "<div style=\"float: right;\">";
		echo $first . $prev . $nav . $next . $last;
		echo "</div><br>";
		// Show all the users with their picture attached.
		echo "<div class=\"container\" style=\"width: 880px;\">";
		for ($i = 0; $i < count($users); $i++) {
			echo "<div style=\"width: 190px; height: 225px; float: left; border: 1px solid #999; padding: 2px; margin: 5px;\">";
			if(!$users[$i]['deleted'] && $user->getId() != $users[$i]['user_id'] && $sql->getCount("SELECT user_id FROM ".$sql->prefix."AdminUser WHERE owner=1 AND user_id=".$users[$i]['user_id']) == 0){
				echo "<div style=\"float: right;\">"; 
				// upgrade/downgrade button
				if($sql->getCount("SELECT user_id FROM ".$sql->prefix."AdminUser WHERE user_id=".$users[$i]['user_id']) == 1)
					echo "<a href=\"?module=user&action=admin&id=".$users[$i]['user_id']."\" title=\"Revoke administrator status\"><img src=\"../images/downgrade.png\"></a>";
				else
					echo "<a href=\"?module=user&action=admin&id=".$users[$i]['user_id']."\" title=\"Grant administrator status\"><img src=\"../images/upgrade.png\"></a>";
				// Delete button
				echo "<a href=\"?module=user&action=ban&id=".$users[$i]['user_id']."\" title=\"Ban user\" onclick='return rmItem()'><img src=\"../images/delete-icon.png\"></a>";
				echo "</div>";
			} else if($users[$i]['deleted']){
				echo "<div style=\"float: right;\">"; 
				echo "<a href=\"?module=user&banned=true&action=unban&id=".$users[$i]['user_id']."\" title=\"Unban user\" onclick='return rmItem()'><img src=\"../images/check.png\"></a>";
				echo "</div>";
			}
			echo "<center>
			<h3><a href=\"../?module=user&action=view&id=".$users[$i]['user_id']."\">".$users[$i]['name']."</a></h3>
			<img src=\"../images/user/".$users[$i]['picture']."\" border=\"0\">
			<br>";
			if($users[$i]['activate'] != 'NULL'){
				echo "<font color=red>Not activated.</font> <a href=\"?module=user&action=activate&id=".$users[$i]['user_id']."\">Activate</a>";
			} else {
				if($users[$i]['picture'] != "pp_na.png")
					echo "<a href=\"?module=user&action=picture&id=".$users[$i]['user_id']."\">Reset picture</a>";
			}
			echo "</center>
			</div>";
		}
		echo "</div>";
	} else {
		LayoutManager::redirect("../");
	}
?>