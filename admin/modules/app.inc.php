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
		echo "<center>
		<a href=\"?module=category\">Category</a> - Application - <a href=\"?module=platform\">Platform</a> - <a href=\"?module=tag\">Tags</a>
		</center>";
		
		// Establish sql connection
		$sql = new SQL();
		
		// Handlers
		switch ($_GET['action']){
			case 'delete':
				if(eregi("^[0-9]{1,11}$", $_GET['id'])){
					// Sett all as removed so that they will be uninstalled by the application
					$query = sprintf("UPDATE `".$sql->prefix."UserDeviceInstalledApps` SET `removed`=NOW() WHERE `app_id`='%s'",
						mysql_real_escape_string($_GET['id']));
					$sql->doUpdate($query);
					
					// Delete all files related to this application
					$query = "SELECT url FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$_GET['id'];
					$files = $sql->getFullArray($query);
					foreach ($files as $fil) {
						FileHandler::delete("../mobile/".$fil['url']);
					}
					$sql->doUpdate("DELETE FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$_GET['id']);
					
					// Delete all the screenshots added to this application
					$query = "SELECT url FROM ".$sql->prefix."ApplicationScreenshots WHERE app_id=".$_GET['id'];
					$ar = $sql->getFullArray($query);
					for ($i = 0; $i < count($ar); $i++) {
						FileHandler::delete("../mobile/files/ss/".$ar[$i]['url']);
					}
					$sql->doUpdate("DELETE FROM ".$sql->prefix."ApplicationScreenshots WHERE app_id=".$_GET['id']);
					
					// Remove the application from all its followers
					$sql->doUpdate("DELETE FROM ".$sql->prefix."UserFollowApplication WHERE app_id=".$_GET['id']);
					
					// Create an announcement that state what's happend. (Standard form)
					$header = "Application removed";
$body = "This application has been removed by the administrators after several reports of issues breaching the user agreement.

We have therefor decided to remove this application for good and it's not possible to reverse this decision.
The application will be removed from all devices who have this installed next time you sync up with the server and we appologice for the trouble.

With best regards
".$user->getName();
					$query = sprintf("INSERT INTO `".$sql->prefix."AppAnnouncement` (app_id, user_id, header, body) VALUES ('%s', '%s', '%s', '%s')",
						mysql_real_escape_string($_GET['id']),
						mysql_real_escape_string($user->getId()),
						mysql_real_escape_string($header),
						mysql_real_escape_string($body));
					$sql->doUpdate($query);					

					// Delete all groups connected to the application and remove all users from them
					$sql->doUpdate("DELETE FROM `".$sql->prefix."UserInGroup` WHERE group_id IN (SELECT group_id FROM ".$sql->prefix."GroupApplication WHERE app_id='".$_GET['id']."')");
					$result3 = $sql->doUpdate("DELETE FROM `".$sql->prefix."GroupApplication` WHERE group_id = '".$_GET['id']."'");
					$sql->doUpdate("DELETE FROM `".$sql->prefix."Group` WHERE group_id IN (SELECT group_id FROM ".$sql->prefix."GroupApplication WHERE app_id='".$_GET['id']."')");					
					
					// Remove all connections to users
					$sql->doUpdate("DELETE FROM ".$sql->prefix."UserApplication WHERE app_id=".$_GET['id']);
					
					// Hide the application by setting it not "public"
					$sql->doUpdate("UPDATE `".$sql->prefix."Application` SET `public`=0 WHERE `app_id`='".$_GET['id']."'");
					
					LayoutManager::alertSuccess("Application have been removed and the users with this installed will have it uninstalled.");
				}
				break;
			case 'view':
				if(eregi("^[0-9]{1,11}$", $_GET['id'])){
					echo "<div class=\"container\" style=\"width: 825px; border: 1px solid #999; padding: 3px;\">";
					$app = new App();
					$app->getBasicInfo($_GET['id']);
					echo "<h2>".$app->getName()."</h2>";
					switch ($_GET['f']){
						case 'report':
							switch ($_GET['a']){
								case 'dismiss':
									if(eregi("^[0-9]{1,11}$", $_GET['user'])){
										if($sql->doUpdate("DELETE FROM `".$sql->prefix."ReportedApplication` WHERE app_id = '".$_GET['id']."' AND user_id='".$_GET['user']."'"))
											LayoutManager::alertSuccess("Report dismissed.");
										else 
											LayoutManager::alertFailure("An error has occured while handling your request.");
									}
									break;
							}
							// Gather all the reports about this application
							$query = "SELECT * FROM ".$sql->prefix."ReportedApplication WHERE app_id=".$app->getId();
							$reports = $sql->getFullArray($query);
							// Create user-object
							$user_reporter = new User();
							
							// List all the reports
							foreach ($reports as $value) {
								// Gather info about the user
								$user_reporter->getInfo($value['user_id']);
								
								// print the report
								echo $user_reporter->toString()." - Reported the following on ".strftime ("%a %b %d, %Y %H:%M", strtotime($value['timestamp'])).".<br><br>";
								echo $value['note']."<br><br>";
								echo " [<a href=\"?module=app&action=view&id=".$app->getId()."&f=report&a=dismiss&user=".$value['user_id']."&page=".$_GET['page']."\">Dismiss</a>] [<a href=\"?module=app&action=delete&id=".$app->getId()."&page=".$_GET['page']."\" onclick=\"return rmItem()\">Delete application</a>]";
							}
							break;
						case 'group':
							switch ($_GET['a']){
								case 'delete':
									if(eregi("^[0-9]{1,11}$", $_GET['group'])){
										$result1 = $sql->doUpdate("DELETE FROM `".$sql->prefix."UserInGroup` WHERE group_id = '".$_GET['group']."'");
										$result3 = $sql->doUpdate("DELETE FROM `".$sql->prefix."GroupApplication` WHERE group_id = '".$_GET['group']."'");
										$result2 = $sql->doUpdate("DELETE FROM `".$sql->prefix."Group` WHERE group_id = '".$_GET['group']."'");
										$result4 = $sql->doUpdate("UPDATE ".$sql->prefix."Forum SET group='-1' WHERE group='".$_GET['group']."'");
										if($result1 && $result2)
											LayoutManager::alertSuccess("The group has been successfully deleted.");
										else
											LayoutManager::alertFailure("An error has occured while handling your request.");
									}
									break;
							}
							// gather all the groups related to the application
							$query = "SELECT * FROM `".$sql->prefix."Group` WHERE group_id IN (SELECT group_id FROM ".$sql->prefix."GroupApplication WHERE app_id='".$app->getId()."')";
							$groups = $sql->getFullArray($query);
							// showing them in a table
							echo "<table cellspacing=0 cellpadding=0 width=100%>";
							foreach ($groups as $group) {
								echo "<tr>
									<td><a href=\"../?module=group&id=".$group['group_id']."\">".$group['name']."</a></td><td>[<a href=\"?module=app&action=view&f=group&id=".$app->getId()."&page={$_GET['page']}&a=delete&group=".$group['group_id']."\">Delete</a>]</td>
									</tr>";
							}
							echo "</table>";
							break;
					}
					echo "</div>";
				}
				break;
			default:
				// DO NOTHING!
				break;
		}
		
		if($_GET['reported'] == "true"){
			$query_1 = "SELECT * FROM ".$sql->prefix."Application WHERE app_id IN (SELECT app_id FROM ".$sql->prefix."ReportedApplication)";
		} else {
			$query_1 = "SELECT * FROM ".$sql->prefix."Application";
		}
		
		// Grab thread-post count
		$post_count = $sql->getCount($query_1);
		
		// how many rows to show per page
		$rowsPerPage = 10;
		
		// by default we show first page
		$pageNum = 1;
		
		// if $_GET['page'] defined, use it as page number
		if(isset($_GET['page'])){
		    $pageNum = $_GET['page'];
		}
		
		// counting the offset
		$offset = ($pageNum - 1) * $rowsPerPage;
		// Gather all the users
		$users = $sql->getFullArray($query_1." LIMIT $offset,$rowsPerPage");
			
		// how many pages we have when using paging?
		$maxPage = ceil($post_count/$rowsPerPage);
		
		// print the link to access each page
		if($_GET['reported'] == "true")
			$self = "?module=app&reported=true";
		else 
			$self = "?module=app";
			
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
		echo "<br><div style=\"float: right;\">";
		echo $first . $prev . $nav . $next . $last;
		echo "</div>";
		
		// Show a link to filter only reported apps
		if($_GET['reported'] == "true")
			echo "<a href=\"?module=app\">Normal</a> - Reported";
		else
			echo "Normal - <a href=\"?module=app&reported=true\">Reported</a>";
		
		// Container for the apps
		echo "<div class=\"container\" style=\"width: 880px;\">";
		// induvidual container for the app
		for ($i = 0; $i < count($users); $i++) {
			$app = new App();
			$app->getBasicInfo($users[$i]['app_id']);
			echo "<div style=\"width: 400px; height: 120px; float: left; border: 1px solid #999; padding: 2px; margin: 5px;\">";
			// Administration button in the to right corner
			echo "<div style=\"float: right;\">";
			// If reported show a warning triangle
			$counted = $sql->getCount("SELECT app_id FROM ".$sql->prefix."ReportedApplication WHERE app_id=".$app->getId());
			if($counted > 0)
				echo "<a href=\"#\" title=\"Reported $counted times.\"><img src=\"../images/warning-icon.png\"></a>";
			// Delete application function.
			if(count($app->getOwner()) != 0){
				echo "<a href=\"?module=app&action=delete&id=".$users[$i]['app_id']."\" title=\"Delete application\" onclick='return rmItem()'><img src=\"../images/delete-icon.png\"></a>";
			} 
			echo "</div>";
			// Information about the app
			echo "<h2>".$app->getName()."</h2>";
			if(count($app->getOwner()) > 1){
				$owner = "";
				foreach ($app->getOwner() as $o_u) {
					$owner .= $o_u->getName().", ";
				}
				$owner = substr($owner, 0, strlen($owner)-2);
				echo "<a href=\"#\" title=\"$owner\">Multiple</a><br>";
			} else if(count($app->getOwner()) == 1) {
				$owner = $app->getOwner();
				echo $owner[0]->toString()."<br>";
			} else {
				echo "Application have been deleted.<br>";
			}
			if($sql->getCount("SELECT app_id FROM ".$sql->prefix."Application WHERE app_id=".$app->getId()." AND public=1") > 0){
				echo "<font color=green>Public</font><br>";
			} else {
				echo "<font color=red>Not public</font><br>";
			}
			echo "Releases: ".$sql->getCount("SELECT app_id FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$app->getId()." AND public=1")."
			 / ".$sql->getCount("SELECT app_id FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$app->getId());
			echo " (public / not public)<br>Reported: <a href=\"?module=app&action=view&f=report&id=".$app->getId()."&page=$pageNum\">".$counted."</a><br>";
			$groups = $sql->getCount("SELECT group_id FROM ".$sql->prefix."GroupApplication WHERE app_id=".$app->getId()."");
			echo "Groups: <a href=\"?module=app&action=view&f=group&id=".$app->getId()."&page=$pageNum\" title=\"View groups\">$groups</a>";
			echo "</div>";
		}
		echo "</div>";
	} else {
		// Redirect back
		LayoutManager::redirect("../");
	}
?>