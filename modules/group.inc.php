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
	$user = LayoutManager::getUser();
	if($user->isLoggedIn()){
		$sql = new SQL();
		// Need to have a valid group ID
		if(eregi("^[0-9]{1,11}$", $_GET['id'])){
			$group = new Group($_GET['id']);
			if($_GET['change'] == "true"){
				// If a user allready is in a group or the group is public he or she will be joining the group
				$countASD = $sql->getCount("SELECT group_id FROM ".$sql->prefix."UserInGroup WHERE user_id='".$user->getId()."' AND group_id='".$_GET['id']."'");
				$countOpen = $sql->getCount("SELECT group_id FROM `".$sql->prefix."Group` WHERE group_id='".$_GET['id']."' AND public=1");
				if($countASD == 0 && $countOpen == 1){
					if($group->addUser($user->getId()) != "true")
						echo Error::printError("Unexpected error.");
				} // If the user is allready invited to the group, he or she can accept or decline the invitation based on $_GET['a'] 
				else if($sql->getCount("SELECT group_id FROM ".$sql->prefix."UserInGroup WHERE user_id='".$user->getId()."' AND group_id='".$_GET['id']."' AND role='Invited'") == 1){
					if($_GET['a'] == "true"){
						if(!$group->changeRole($user->getId()))
							LayoutManager::alertFailure("Unexpected error.");
					} else {								
						if(!$group->removeUser($user->getId()))
							LayoutManager::alertFailure("Unexpected error.");
						$array = $sql->getArray("SELECT public FROM `".$sql->prefix."Group` WHERE group_id=".$_GET['id']);
						if(!$array['public']){
							// If the group is not public the user will be transfered away from group page.
							LayoutManager::redirect("?module=me&action=groups");
						}
					}

				} else {
					if(!$group->removeUser($user->getId()))
						echo Error::printError("Unexpected error.");
					$array = $sql->getArray("SELECT name,public FROM `".$sql->prefix."Group` WHERE group_id=".$_GET['id']);
					if(!$array['public']){
						// If the group is not public the user will be transfered away from group page.
						LayoutManager::redirect("?module=me&action=groups");
					}
				}
			}
			
			$array = $sql->getArray("SELECT role FROM `".$sql->prefix."UserInGroup` WHERE group_id=".$_GET['id']." AND user_id=".$user->getId());
			$user_role = $array['role'];
			
			// Show dashboard
			$array = $sql->getArray("SELECT name,public FROM `".$sql->prefix."Group` WHERE group_id=".$_GET['id']);
			if($array['public'] || $user_role != "" || $user->isAdmin()){
				$ar = $sql->getArray("SELECT app_id FROM `".$sql->prefix."GroupApplication` WHERE group_id=".$_GET['id']);
				echo "<div class=\"container\"><h2>".$array['name']."</h2></div>";
				echo "<div class=\"container\">
				[<a href=\"?module=group&id=".$_GET['id']."\">Info</a>]
				[<a href=\"?module=group&id=".$_GET['id']."&action=members\">Members</a>]
				[<a href=\"?module=forum&id=".$ar['app_id']."\">Messageboard</a>]
				</div>";
				switch ($_GET['action']){
					case 'members':
						// This case is for managing groups connected to the applications
						switch ($_GET['p']){
							case 'add':
								if($_GET['u'] != ""){
									if($user_role == "Owner" || $user_role == "Administrator"){
										$reply = $group->inviteUser($user, $_GET['u']);
										if($reply['error'] == "true")
											LayoutManager::alertFailure($reply['msg']);
										else LayoutManager::alertSuccess("User has been invited to the group, need to confirm if he wants to join or not.");
									}
								}
								
								// Grab thread-post count
								$post_count = $sql->getCount("SELECT user_id FROM ".$sql->prefix."User WHERE user_id NOT IN (SELECT user_id FROM ".$sql->prefix."UserInGroup WHERE group_id = '".$_GET['id']."') AND deleted=0 AND activate='NULL'");
								
								// how many rows to show per page
								$rowsPerPage = 20;
								
								// by default we show first page
								$pageNum = 1;
								
								// if $_GET['page'] defined, use it as page number
								if(isset($_GET['page'])){
								    $pageNum = $_GET['page'];
								}
								
								// counting the offset
								$offset = ($pageNum - 1) * $rowsPerPage;
								// Gather all the users
								$users = $sql->getFullArray("SELECT user_id FROM ".$sql->prefix."User WHERE user_id NOT IN (SELECT user_id FROM ".$sql->prefix."UserInGroup WHERE group_id = '".$_GET['id']."') AND deleted=0 AND activate='NULL' LIMIT $offset,$rowsPerPage");
								
								// how many pages we have when using paging?
								$maxPage = ceil($post_count/$rowsPerPage);
								
								// print the link to access each page
								$self = "?module=group&id=".$_GET['id'];
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
				  					$prev  = " <a href=\"$self&page=$page\" title=\"Previous\"><img src=\"images/pageinator/prev.gif\"></a> ";
										
									$first = " <a href=\"$self&page=1\" title=\"First Page\"><img src=\"images/pageinator/first.png\"></a> ";
								} else {
									$prev  = '&nbsp;'; // we're on page one, don't print previous link
									$first = '&nbsp;'; // nor the first page link
								}
								if ($pageNum < $maxPage){
									$page = $pageNum + 1;
									$next = " <a href=\"$self&page=$page\" title=\"Next\"><img src=\"images/pageinator/next.gif\"></a> ";
										
									$last = " <a href=\"$self&page=$maxPage\" title=\"Last Page\"><img src=\"images/pageinator/last.png\"></a> ";
								} else {
								   $next = '&nbsp;'; // we're on the last page, don't print next link
								   $last = '&nbsp;'; // nor the last page link
								}
								// print the navigation link
								echo "<br><div style=\"float: right;\">";
								echo $first . $prev . $nav . $next . $last;
								echo "</div>";
								echo "[<a href=\"?module=group&id=".$group->getId()."\">Back</a>]\n";
								echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>"; 
								echo "<tr><td width=90%><b>Name</b></td><td width=10%></td></tr>"; 
								$invitable_user = new User();
								for($i=0; $i<count($users); $i++){
									$invitable_user->getInfo($users[$i]['user_id']);
									$url = "?module=group&id=".$group->getId()."&action=members&p=add&u=".$invitable_user->getId();
									if($_GET['page'] != "")
										$url .= "&page=".$_GET['page']; 
	                                echo "<tr><td width=90%>".$invitable_user->toString()."</a></td> 
	                                <td width=10%><form action=\"?module=group&id=".$group->getId()."&action=members&p=add&u=".$invitable_user->getId()."\" method=\"post\"> 
	                                <input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Add\" /></form></td></tr>"; 
	                        	} 
	                        	echo "</table>";
								break;
						default:
							if($_GET['a'] == "true"){
								if($user_role == "Owner" || $user_role == "Administrator"){
									if(eregi("^[0-9]{1,11}$", $_GET['u'])){
										if($_GET['u'] != $user->getId()){
											if($sql->getCount("SELECT * FROM `".$sql->prefix."UserInGroup` WHERE `group_id`=".$_GET['id']." AND user_id=".$_GET['u']." AND role = 'Invited'") == 0){
												if($sql->getCount("SELECT * FROM `".$sql->prefix."UserInGroup` WHERE `group_id`=".$_GET['id']." AND user_id=".$_GET['u']." AND role <> 'Owner'") > 0){
													if($user_role != 'Administrator'){
														if($group->changeRole($_GET['u']) != "true")
															LayoutManager::alertFailure("Unexpected error");
														else LayoutManager::alertSuccess("Users role has successfully been changed.");
													} else {
														if($sql->getCount("SELECT * FROM `".$sql->prefix."UserInGroup` WHERE `group_id`=".$_GET['id']." AND user_id=".$_GET['u']." AND role <> 'Administrator'") > 0){
															if($group->changeRole($_GET['u']) != "true")
																LayoutManager::alertFailure("Unexpected error");
															else LayoutManager::alertSuccess("Users role has successfully been changed.");
														}
													}
												} else {
													LayoutManager::alertFailure("Invalid user.");
												}
											}
										}
									}
								}
							}
							if($_GET['rm'] == "true"){
								if($user_role == "Owner" || $user_role == "Administrator"){
									if(eregi("^[0-9]{1,11}$", $_GET['u'])){
										if($_GET['u'] != $user->getId()){
											// Might rewrite this
											if($sql->getCount("SELECT * FROM `".$sql->prefix."UserInGroup` WHERE `group_id`=".$_GET['id']." AND user_id=".$_GET['u']." AND role <> 'Owner'") > 0){
												if($user_role != 'Administrator'){
													if($group->removeUser($_GET['u']) != "true")
														LayoutManager::alertFailure("Unexpected error");
													else LayoutManager::alertSuccess("User has been removed from the group.");
												} else {
													if($sql->getCount("SELECT * FROM `".$sql->prefix."UserInGroup` WHERE `group_id`=".$_GET['id']." AND user_id=".$_GET['u']." AND role <> 'Administrator'") > 0){
														if($group->removeUser($_GET['u']) != "true")
															LayoutManager::alertFailure("Unexpected error");
														else LayoutManager::alertSuccess("User has been removed from the group.");
													}
												}
											} else {
												LayoutManager::alertFailure("Can't remove the group owner.");
											}
										}
									}
								}
							}
							echo "<b>Users in this group</b>";
							$array = $sql->getArray("SELECT role FROM `".$sql->prefix."UserInGroup` WHERE group_id=".$_GET['id']." AND user_id=".$user->getId());
							$user_role = $array['role'];
							if($user_role == "Owner" || $user_role == "Administrator"){
								echo "<div style=\"float: right;\">[<a href=\"?module=group&id=".$_GET['id']."&action=members&p=add\">Add users</a>]";		
							 	echo "</div>";
							}
							echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
							echo "<tr><td width=33%><b>Name</b></td><td width=33%><b>Joined</b></td><td width=30%><b>Role</b></td><td width=3%></td></tr>";
							$users_in_grp = $group->getMembers();
							for ($i = 0; $i < count($users_in_grp); $i++) {
								if($user_role == "Owner" || $user_role == "Administrator"){
									echo "<td width=33%>".$users_in_grp[$i]->toString()."</td>
									<td width=33%>".$users_in_grp[$i]->getJoined()."</td>";
									if($user_role == "Owner"){
										if($users_in_grp[$i]->getRole() == "Owner")
											echo "<td width=30%>".$users_in_grp[$i]->getRole()."</td><td width=3%></td></tr>";
										else if($users_in_grp[$i]->getRole() != "Invited") {
											echo "<td width=30%><form action=\"?module=group&id=".$_GET['id']."&action=members&a=true&u=".$users_in_grp[$i]->getId()."\" method=\"post\">
						  					<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"".$users_in_grp[$i]->getRole()."\" /></form></td>
						  					<td width=3%><form action=\"?module=group&id=".$_GET['id']."&action=members&rm=true&u=".$users_in_grp[$i]->getId()."\" method=\"post\">
						  					<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Remove\" /></form></td></tr>";
										} else {
											echo "<td width=30%>".$users_in_grp[$i]->getRole()."</td>
						  					<td width=3%><form action=\"?module=group&id=".$_GET['id']."&action=members&rm=true&u=".$users_in_grp[$i]->getId()."\" method=\"post\">
						  					<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Revoke invite\" /></form></td></tr>";
										}
									} else {
										if($users_in_grp[$i]->getRole() == "Owner" || $users_in_grp[$i]->getRole() == "Administrator")
											echo "<td width=30%>".$users_in_grp[$i]->getRole()."</td><td width=3%></td></tr>";
										else {
											if($users_in_grp[$i]->getRole() == "Invited"){
												echo "<td width=30%>".$users_in_grp[$i]->getRole()."</td>
							  					<td width=3%><form action=\"?module=group&id=".$_GET['id']."&action=members&rm=true&u=".$users_in_grp[$i]->getId()."\" method=\"post\">
							  					<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Revoke invite\" /></form></td></tr>";
											} else {
												echo "<td width=30%><form action=\"?module=group&id=".$_GET['id']."&action=members&a=true&u=".$users_in_grp[$i]->getId()."\" method=\"post\">
						  						<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"".$users_in_grp[$i]->getRole()."\" /></form></td>
						  						<td width=3%><form action=\"?module=group&id=".$_GET['id']."&action=members&rm=true&u=".$users_in_grp[$i]->getId()."\" method=\"post\">
						  						<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Remove\" /></form></td></tr>";
											}
										}
									}
								} else {
									echo "<tr><td width=33%>".$users_in_grp[$i]->toString()."</td>
									<td width=33%>".$users_in_grp[$i]->getJoined()."</td>
									<td width=30%>".$users_in_grp[$i]->getRole()."</td><td width=3%></td></tr>";
								}
							}
							echo "</table>";
							break;
						}
						break;
					default:
						if($_GET['save'] == "true"){
							if($user_role == "Owner"){
								if(!$group->changePrivacy())
									LayoutManager::alertFailure("Unexpected error");
								else LayoutManager::alertSuccess("The visibility of the group has successfully been changed.");
							}
						}
						$membercount = $sql->getCount("SELECT user_id FROM `".$sql->prefix."UserInGroup` WHERE role<>'Invited' AND group_id=".$_GET['id']);
						$invitecount = $sql->getCount("SELECT user_id FROM `".$sql->prefix."UserInGroup` WHERE role='Invited' AND group_id=".$_GET['id']);
						if($user_role == "Owner"){
							$query = "SELECT public FROM `".$sql->prefix."Group` WHERE group_id='".$_GET['id']."'";
							$ar = $sql->getArray($query);
							if($ar['public'])
								$text = "Hide";
							else 
								$text = "Make public";
							echo "<form action=\"?module=group&id=".$_GET['id']."&save=true\" method=\"post\">
								  <div class=\"form_settings\">";			 	
						 	echo "<p><input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"".$text."\"/></p>";
						 	echo "</div>";
							echo "</form>";
						} else if($user_role != "Invited") {
							$array = $sql->getArray("SELECT role FROM `".$sql->prefix."UserInGroup` WHERE group_id=".$_GET['id']." AND user_id=".$user->getId());
							$user_role = $array['role'];
							if($user_role == ""){
								$text = "Join";
							} else {
								$text = "Leave";
							}
							echo "<form action=\"?module=group&id=".$_GET['id']."&change=true\" method=\"post\">
							<div class=\"form_settings\">
							<p><input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"".$text."\" /></p>
							</div>
							</form>";
						} else {
							echo "<form action=\"?module=group&change=true&id=".$_GET['id']."&a=true\" method=\"post\">
							<div class=\"form_settings\">
							<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Accept\" />
							</form>";
							echo "<form action=\"?module=group&change=true&id=".$_GET['id']."&a=false\" method=\"post\">
							<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Decline\" />
							</div>
							</form>";
						}
						echo "<br><b>Description</b>";
						$array = $sql->getArray("SELECT * FROM `".$sql->prefix."Group` WHERE group_id=".$_GET['id']."");
						echo "<p>".$array['desc']."</p><br>";
						echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
						echo "<tr><td>Members</td><td>".$membercount."</td></tr>";
						echo "<tr><td>Pending invitations</td><td>".$invitecount."</td></tr>";
						echo "</table>";
						break;
				}
			} else {
				LayoutManager::alertFailure("You don't got the rights to watch this group.");
			}
		} else {
			LayoutManager::redirect("?module=me&action=groups");
		}
	} else {
		// Posting loginform
		LayoutManager::printLoginForm();
	}	
?>