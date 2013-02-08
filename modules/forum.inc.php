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
		if(eregi("^[0-9]{1,11}$", $_GET['id'])){
			// establish sql connection
			$sql = new SQL();
			
			$user->setRole("");
			// Get the users role on this app. Owner / not
			$count = $sql->getCount("SELECT * FROM ".$sql->prefix."UserApplication WHERE app_id=".$_GET['id']." AND user_id=".$user->getId());
			if($count > 0)
				$user->setRole("app-owner");
			
			// if !Owner Then check on induvidual group forums.
			if(eregi("^[0-9]{1,11}$", $_GET['t']) && $user->getRole() == ""){
				$ar = $sql->getArray("SELECT `group` FROM ".$sql->prefix."Forum WHERE forum_id=".$_GET['f']);
				if($ar['group'] == -1){
					// This is open for all groups connected to the application
					if($sql->getCount("SELECT * FROM ".$sql->prefix."UserInGroup WHERE group_id IN (SELECT group_id FROM ".$sql->prefix."GroupApplication 
					WHERE app_id=".$_GET['id'].") AND role<>'Invited' AND role<>'Member' AND user_id=".$user->getId())>0)
						$user->setRole("app-group-administrator");							
				} else if($ar['group'] != null) {
					// This is if the user is in the specific group
					if($sql->getCount("SELECT * FROM ".$sql->prefix."UserInGroup WHERE group_id=".$ar['group']." AND role<>'Invited' AND role<>'Member' AND user_id=".$user->getId())>0)
						$user->setRole("app-group-member");
				}
			}
			if($user->isAdmin())
				$user->setRole("app-owner");
			
			function print_breadcrumbs(){
				$sql = new SQL();
				$app = new App();
				$app->getBasicInfo($_GET['id']);
				echo "<a href=\"?module=app&action=view&platform=".$_SESSION['app_plat']."&id=".$_GET['id']."\"><img src=\"images/forum/breadcrumbs-home.png\" border=0></a>";
				echo "  <img src=\"images/forum/breadcrumbs-arrow.png\">  ";
				echo "<a href=\"?module=forum&id=".$_GET['id']."\">".$app->getName()."</a>";
				if($_GET['a'] != ""){
					if(eregi("^[0-9]{1,11}$", $_GET['f'])){
						echo "  <img src=\"images/forum/breadcrumbs-arrow.png\">  ";
						$array = $sql->getArray("SELECT * FROM ".$sql->prefix."Forum WHERE forum_id=".$_GET['f']);
						echo "<a href=\"?module=forum&id=".$_GET['id']."&a=forum&f=".$_GET['f']."\">".$array['name']."</a>";
					}
				}
				if($_GET['t'] != ""){
					if(eregi("^[0-9]{1,11}$", $_GET['t'])){
						echo "  <img src=\"images/forum/breadcrumbs-arrow.png\">  ";
						$query = "SELECT * FROM ".$sql->prefix."ForumPost WHERE post_id IN (SELECT post_id FROM ".$sql->prefix."ForumThreadPost 
						WHERE thread_id = ".$_GET['t'].") AND title IS NOT null";
						$array = $sql->getArray($query);
						echo "<a href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$_GET['t']."\">".$array['title']."</a>";
					}
				}
				if($_GET['a'] == "reply"){
					echo "  <img src=\"images/forum/breadcrumbs-arrow.png\">  ";
					echo "Reply to Thread";
				} else if($_GET['a'] == "create"){
					echo "  <img src=\"images/forum/breadcrumbs-arrow.png\">  ";
					echo "Post New Thread";
				}
			}
		
			// print the breadcrumb
			print_breadcrumbs();
			switch($_GET['a']){
				case 'report':
					if(eregi("^[0-9]{1,11}$", $_GET['post'])){
						if(isset($_POST['confirm']) && $_POST['confirm'] == "true"){
							// Report a post.
							$query = "INSERT INTO `".$sql->prefix."ReportedPost` (`post_id`, `user_id`) VALUES ('".$_GET['post']."', '".$user->getId()."')";
							if($sql->doUpdate($query))
								LayoutManager::alertSuccess("Administrator has been notified about the post.");
							else
								LayoutManager::alertFailure("The post have already been reported by someone.");
						} else {
							echo "<form action=\"?module=forum&id=".$_GET['id']."&a=report&post=".$_GET['post']."\" method=\"post\">
							  <div class=\"form_settings\">
							  	<center><h2>Report a forum post</h2></center>
							  	<p>By pressing submit you will report this post to the moderators and your name will be shown as the reporter.
							  	This shall only be used for foul language, bad behavior and remarks towards other users.
							  	<br>Please not that misuse of the reporting function might lead to a warning and worst case a permanent ban of your account.</p>
							    <input type=\"hidden\" name=\"confirm\" value=\"true\" class=\"contact\"/>
							    <p style=\"padding-top: 15px\"><center><input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" />&#9;&#9;
							    <input class=\"change\" type=\"button\" name=\"contact_submitted\" onclick=\"javascript:history.back()\" value=\"Cancel\" /></center></p>
							    <br>
							  </div>
							</form>";
						}
					}
					break;
				case 'thread':
					if(eregi("^[0-9]{1,11}$", $_GET['f']) && eregi("^[0-9]{1,11}$", $_GET['t'])){
						// Grab thread-post count
						$post_count = $sql->getCount("SELECT * FROM ".$sql->prefix."ForumPost WHERE post_id IN (SELECT post_id FROM ".$sql->prefix."ForumThreadPost WHERE thread_id = ".$_GET['t'].")");
						
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
						
						// list the threads attached to this application
						$query = "SELECT * FROM ".$sql->prefix."ForumPost WHERE post_id IN (SELECT post_id FROM ".$sql->prefix."ForumThreadPost WHERE thread_id = ".$_GET['t'].") 
						ORDER BY timestamp ASC LIMIT $offset,$rowsPerPage";
						$array = $sql->getFullArray($query);
						$query2 = "SELECT * FROM ".$sql->prefix."ForumThread WHERE thread_id = ".$_GET['t'];
						$ar = $sql->getArray($query2);
						echo "<table cellspacing=0 cellpadding=0 border=0 width=100% style=\"border-collapse:separate; border-spacing:0px 8px;\">";
						for ($i = 0; $i < count($array); $i++) {
							// Print the forums linked to this app.
							$usera = new User();
							$usera->getInfo($array[$i]['user_id']);
							
							$msg = nl2br (strip_tags ($array[$i]['message'], '<a>'));
							// Handle quote and other BB-code crap
							
							// Swap all occurrences of "[quote:ID]
							if(substr_count($msg,"[quote:") > 0 && substr_count($msg,"[/quote]")>0 && substr_count($msg,"[quote:") == substr_count($msg,"[/quote]") && 
							substr_count($msg,"[quote:") < 5 && substr_count($msg,"[/quote]") < 5){
								$msg = str_ireplace("[/quote]", "</blockquote>", $msg);
								for ($j = 0; $j < substr_count($msg,"[quote:"); $j++) {
									$msg = str_ireplace("[quote:", "<blockquote style=\"background: #FFF; padding: 10px; margin:15px;\">
									<b>Original post</b> <a href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$_GET['t']."#", $msg);
									$msg = str_ireplace("]", "\"><img src=\"images/forum/viewpost.gif\" border=0></a><br>", $msg);
								}
								// END Handle quote and other BB-code crap
							}
							
							echo "<tr>";
							echo "<td>
								<div style=\"float: left; padding-left: 5px;\"><img src=\"images/forum/post_old.gif\"> ".strftime ("%a %b %d, %Y %H:%M",strtotime($array[$i]['timestamp']))."</div>
								<div style=\"float: right;\"><a href=\"?module=forum&id=".$_GET['id']."&a=report&post=".$array[$i]['post_id']."\" title=\"Report!\"><img src=\"images/warning-icon.png\" border=0></a></div>
									<div style=\"float: right; padding-right: 5px;\"\"><a name=\"".($offset+($i+1))."\" 
									href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$_GET['t']."&page=".$pageNum."#".($offset+($i+1))."\">#".($offset+($i+1))."</a></div>
									<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">
										<tr valign=\"top\">
									    	<td width=\"150px\">
									        <center>
									        ".$usera->toString()."<br>
									    	<img src=\"images/user/".$usera->getPicture()."\" border=\"0\" width=\"120\" style=\"padding-top: 5px;\">";
											if($usera->isAdmin()){ echo "<font color=red>Moderator</font>";}
									        echo "</center>
									        </td>
									        <td width=\"100%\">
									         <div style=\"position: relative; width: 630px;\">
									         ".$msg."
									         </div>  
									        </td>
										</tr>
									</table>
									<div style=\"float: right; padding-right: 5px;\"\">";
									if(!$ar['locked']){
										if($usera->getId() == $user->getId() || ($user->getRole() == "app-owner" || $user->getRole() == "app-group-administrator")){
											echo "<a href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=reply&t=".$_GET['t']."&e=".$array[$i]['post_id']."\" 
											onmouseover=\"document.edit".$array[$i]['post_id'].".src='images/forum/hepic.png'\" 
											onmouseout=\"document.edit".$array[$i]['post_id'].".src='images/forum/epic.png'\">
											<img src=\"images/forum/epic.png\" name=\"edit".$array[$i]['post_id']."\" alt=\"Quote\" border=0></a> ";
										}
										echo "<a href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=reply&t=".$_GET['t']."&q=".$array[$i]['post_id']."\" 
										onmouseover=\"document.quote".$array[$i]['post_id'].".src='images/forum/hqpic.png'\" 
										onmouseout=\"document.quote".$array[$i]['post_id'].".src='images/forum/qpic.png'\">
										<img src=\"images/forum/qpic.png\" name=\"quote".$array[$i]['post_id']."\" alt=\"Quote\" border=0></a>";
										echo "</div>";
									}
							echo "</td>";
							echo "</tr>";
						}
						echo "</table>";
						// how many pages we have when using paging?
						$maxPage = ceil($post_count/$rowsPerPage);
						
						// print the link to access each page
						$self = "?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$_GET['t'];
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
						echo "<div style=\"float: right;\">";
						echo $first . $prev . $nav . $next . $last;
						echo "</div>";
						if(!$ar['locked']){
							echo "<a href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=reply&t=".$_GET['t']."\" onmouseover=\"document.reply.src='images/forum/hpostreply.png'\"
	onmouseout=\"document.reply.src='images/forum/postreply.png'\" title=\"Post reply.\">
							<img src=\"images/forum/postreply.png\" name=\"reply\" border=0>
							</a>";
						} else {
							echo "<img src=\"images/forum/threadclosed.gif\" name=\"link\" alt=\"Thread closed.\" border=0>";
						}
					}
					break;
				case 'forum':
					if(eregi("^[0-9]{1,11}$", $_GET['f'])){
						// Need to check if the user is in the group specificed to this forum
						// First get the group attached
						$ar = $sql->getArray("SELECT `group` FROM ".$sql->prefix."Forum WHERE forum_id=".$_GET['f']);

						$valid = false;
						if($ar['group'] != null){
							if($ar['group'] == "-1"){
								// This is open for all groups connected to the application
								if($sql->getCount("SELECT * FROM ".$sql->prefix."UserInGroup WHERE group_id IN (SELECT group_id FROM ".$sql->prefix."GroupApplication 
								WHERE app_id=".$_GET['id'].") AND role<>'Invited' AND user_id=".$user->getId())>0)
									$valid = true;							
							} else {
								// This is if the user is in the specific group
								if($sql->getCount("SELECT * FROM ".$sql->prefix."UserInGroup WHERE group_id=".$ar['group']." AND role<>'Invited' AND user_id=".$user->getId())>0)
									$valid = true;
							}	
						} else {
							$valid = true;
						}
						if($user->getRole() == "app-owner" || $user->getRole() == "app-group-administrator")
							$valid = true;
						if($valid){
							// list the threads attached to this application
							$query = "SELECT TP.thread_id, T.locked, T.sticky, MAX(P.timestamp) AS timestamp 
							FROM ".$sql->prefix."ForumPost P, ".$sql->prefix."ForumThread T, ".$sql->prefix."ForumThreadPost TP 
							WHERE P.post_id = TP.post_id AND T.thread_id = TP.thread_id AND T.forum_id=".$_GET['f']." GROUP BY TP.thread_id ORDER BY sticky DESC, MAX(P.timestamp) DESC";

							
							// Grab thread-post count
							$post_count = $sql->getCount($query);
							
							// how many rows to show per page
							$rowsPerPage = 30;
							
							// by default we show first page
							$pageNum = 1;
							
							// if $_GET['page'] defined, use it as page number
							if(isset($_GET['page'])){
							    $pageNum = $_GET['page'];
							}
							
							// counting the offset
							$offset = ($pageNum - 1) * $rowsPerPage;
							
							// listing the threads attached
							$array = $sql->getFullArray($query." LIMIT $offset,$rowsPerPage");
							echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
							for ($i = 0; $i < count($array); $i++) {
								// Print the forums linked to this app.
								$ar = $sql->getArray("SELECT title, user_id FROM ".$sql->prefix."ForumPost WHERE post_id IN (SELECT post_id FROM ".$sql->prefix."ForumThreadPost 
								WHERE thread_id=".$array[$i]['thread_id'].") AND title IS NOT null");
								$usera = new User();
								$usera->getInfo($ar['user_id']);
								
								$query = "SELECT P.post_id, TP.thread_id, P.user_id, MAX( P.timestamp ) AS TIMESTAMP
								FROM ".$sql->prefix."ForumPost P, ".$sql->prefix."ForumThread T, ".$sql->prefix."ForumThreadPost TP
								WHERE P.post_id = TP.post_id AND T.thread_id = TP.thread_id AND 
								T.thread_id=".$array[$i]['thread_id']." GROUP BY P.post_id ORDER BY MAX( P.timestamp ) DESC";
								$last_post = $sql->getArray($query." LIMIT 1");
								
								$thread_post_count = $sql->getCount($query);
								$max_Page = ceil($thread_post_count/10);
								
								$img = "<img src=\"images/forum/icon1.gif\">";
								if($array[$i]['locked'])
									$img = "<img src=\"images/forum/thread-closed.png\">";
								if($array[$i]['sticky'])
									$img = "<img src=\"images/forum/thread-sticky.gif\">";
								echo "<tr>";
								echo "<td width=30px><center>".$img."</center></td>
								<td width=20px><center>";
								if($array[$i]['locked'] && $array[$i]['sticky']) {
									echo "<img src=\"images/forum/pin.png\">";
								} else if($array[$i]['sticky']) {
									echo "<a href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$last_post['thread_id']."#".$last_post['post_id']."\">
									<img src=\"images/forum/pin_new.png\"></a>";
								} else {
									echo "<a href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$last_post['thread_id']."#".$last_post['post_id']."\">
									<img src=\"images/forum/new_post.png\"></a>";
								}
								echo "</center></td>
								<td> <h2><a href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$array[$i]['thread_id']."\">".$ar['title']."</a></h2>
								Originally posted by ".$usera->toString()."</td>";
								$userb = new User();
								$userb->getInfo($last_post['user_id']);
								echo "<td width=200px>by ".$userb->toString()." 
								<a href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$last_post['thread_id']."&page=".$max_Page."#".$thread_post_count."\">
								<img src=\"images/forum/viewpost.gif\" border=0></a><br>".strftime ("%a %b %d, %Y %H:%M",strtotime($last_post['TIMESTAMP']))."</td>";
								if($user->getRole() == "app-owner" || $user->getRole() == "app-group-administrator"){
									// Show the manage buttons
									$ablah = $sql->getArray("SELECT sticky, locked FROM `".$sql->prefix."ForumThread` WHERE thread_id=".$array[$i]['thread_id']);
									if($ablah['sticky'] == 0)
										$stick = "Stick";
									else
										$stick = "Unstick";
									if($ablah['locked'] == 0)
										$lock = "unlock";
									else
										$lock = "lock";
									echo "<td width=110px>
									[<a href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=manage&m=s&t=".$array[$i]['thread_id']."\" title=\"Sticky\">$stick</a>]
									<a href=\"?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=manage&m=l&t=".$array[$i]['thread_id']."\" title=\"".$lock."ed thread!\"><img src=\"images/forum/lock-".$lock.".png\" border=0></a>
									</td>";
								}
								echo "<tr>";
							}
							echo "</table>";
						// how many pages we have when using paging?
						$maxPage = ceil($post_count/$rowsPerPage);
						
						// print the link to access each page
						$self = "?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=forum";
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
						echo "<div style=\"float: right;\">";
						echo $first . $prev . $nav . $next . $last;
						echo "</div>";
							echo "<a href=\"?module=forum&id=".$_GET['id']."&a=create&f=".$_GET['f']."\" title=\"Create a new thread.\"><img src=\"images/forum/newthread.png\" border=0></a>";
						} else {
							// redirect back to app-forum frontpage
							$url = "?module=forum&id=".$_GET['id'];
							LayoutManager::redirect($url);
						}
					}
					break;
				case 'create':
					// Need to check if the user is in the group specificed to this forum
					// First get the group attached
					$ar = $sql->getArray("SELECT `group` FROM ".$sql->prefix."Forum WHERE forum_id=".$_GET['f']);

					$valid = false;
					if($ar['group'] != null){
						if($ar['group'] == -1){
							// This is open for all groups connected to the application
							if($sql->getCount("SELECT * FROM ".$sql->prefix."UserInGroup WHERE group_id IN (SELECT group_id FROM ".$sql->prefix."GroupApplication WHERE app_id=".$_GET['id'].") 
							AND role<>'Invited' AND user_id=".$user->getId())>0)
								$valid = true;
						} else {
							// This is if the user is in the specific group
							if($sql->getCount("SELECT * FROM ".$sql->prefix."UserInGroup WHERE group_id=".$ar['group']." AND role<>'Invited' AND user_id=".$user->getId())>0)
								$valid = true;
						}	
					} else {
						$valid = true;
					}
					if($user->getRole() == "app-owner" || $user->getRole() == "app-group-administrator")
						$valid = true;
					if($valid){
						$msg = strip_tags($_POST['message']);
						// Check the message.
						$invalid = false;

						$title = strip_tags($_POST['title']);
						if($msg != "" && $title != "" && strlen($title) < 46 && !$invalid){
							// Check if the POST is posted from the correct url.
							// This is not safe cause HTTP_REFERER can be modified
							if($_SERVER['HTTP_REFERER'] == "http://".$_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']){
								// Check the input
															// Check the input
								// Insert into the forumpost and forumthreadpost
								$result = $sql->doUpdate("INSERT INTO `".$sql->prefix."ForumThread` (`forum_id`) VALUES ( '".$_GET['f']."')");
								if($result){
									$thread = mysql_insert_id();

									$query = sprintf("INSERT INTO `".$sql->prefix."ForumPost` (`user_id`, `title`, `message`) VALUES ( '%s', '%s', '%s')",
										mysql_real_escape_string($user->getId()),
										mysql_real_escape_string($title),
										mysql_real_escape_string($msg));
									$result = $sql->doUpdate($query);
									if($result){
										$id = mysql_insert_id();
										$result = $sql->doUpdate("INSERT INTO `".$sql->prefix."ForumThreadPost` (`post_id`, `thread_id`) VALUES ( '".$id."', '".$thread."')");
										if($result){
											// redirect to the new post
											echo "<br>";
											$url = "?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$thread;
											LayoutManager::redirect($url);
										} else {
											$sql->doUpdate("DELETE FROM `".$sql->prefix."ForumThread` WHERE thread_id=".$thread);
											LayoutManager::alertFailure("Unexpected error. ");
										}
									} else {
										LayoutManager::alertFailure("Unexpected error. ");
									}
								}
							}

						}
						if($invalid)
							LayoutManager::alertFailure("You have a word witch contains more than 100 characters. Please fix it.");
						echo "<form action=\"?module=forum&id=".$_GET['id']."&a=create&f=".$_GET['f']."\" method=\"post\">
						  <div class=\"form_settings\">
						  	<p><span>Title</span><input class=\"contact\" type=\"text\" name=\"title\" value=\"".$title."\" /></p>
						    <p><span>Message</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"message\">".$msg."</textarea></p>
						    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
						  </div>
						</form>";
					} else {
						echo "<br><br>";
						// redirect back to app-forum frontpage
						$url = "?module=forum&id=".$_GET['id'];
						LayoutManager::redirect($url);
					}
					break;
				case 'reply':
					// Need to check if the user is in the group specificed to this forum
					// First get the group attached
					$ar = $sql->getArray("SELECT `group` FROM ".$sql->prefix."Forum WHERE forum_id=".$_GET['f']);

					$valid = false;
					if($ar['group'] != null){
						if($ar['group'] == -1){
							// This is open for all groups connected to the application
							if($sql->getCount("SELECT * FROM ".$sql->prefix."UserInGroup WHERE group_id IN (SELECT group_id FROM ".$sql->prefix."GroupApplication WHERE app_id=".$_GET['id'].") 
							AND role<>'Invited' AND user_id=".$user->getId())>0)
								$valid = true;
							
						} else {
							// This is if the user is in the specific group
							if($sql->getCount("SELECT * FROM ".$sql->prefix."UserInGroup WHERE group_id=".$ar['group']." AND role<>'Invited' AND user_id=".$user->getId())>0)
								$valid = true;
							
						}	
					} else {
						$valid = true;
					}
					// if app-owner you get to edit post
					if($user->getRole() == "app-owner" || $user->getRole() == "app-group-administrator")
						$valid = true;

					if($valid){
						$msg = strip_tags($_POST['message'], '<a>');
						
						$invalid = false;
						$msg_array = str_word_count($msg, 1);
						for ($i = 0; $i < count($msg_array); $i++) {
							if(strlen($msg_array[$i])>85)
								$invalid = true;
						}
						
						if($msg != "" && !$invalid){
							// Check if the POST is posted from the correct url.
							// This is not safe cause HTTP_REFERER can be modified
							if($_SERVER['HTTP_REFERER'] == "http://".$_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']){
								// Check the input
								if(substr_count($msg,"[quote:") < 5 && substr_count($msg,"[/quote]") < 5){
									// Insert into the forumpost and forumthreadpost
									if(eregi("^[0-9]{1,11}$", $_GET['t']) && $_GET['e'] == "" ){
										$query = sprintf("INSERT INTO `".$sql->prefix."ForumPost` (`user_id`, `message`) VALUES ( '%s', '%s')",
											mysql_real_escape_string($user->getId()),
											mysql_real_escape_string($msg));
										$result = $sql->doUpdate($query);
										if($result){
											$id = mysql_insert_id();
											$result = $sql->doUpdate("INSERT INTO `".$sql->prefix."ForumThreadPost` (`post_id`, `thread_id`) VALUES ( '".$id."', '".$_GET['t']."')");
											if($result){
												// redirect to the new post
												echo "<br><br>";
												$url = "?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$_GET['t']."#".$id;
												LayoutManager::redirect($url);
											} else {
												LayoutManager::alertFailure("Unexpected error. ");
											}
										} else {
											LayoutManager::alertFailure("Unexpected error. ");
										}
									} else if(eregi("^[0-9]{1,11}$", $_GET['e'])){
										$ar = $sql->getArray("SELECT * FROM ".$sql->prefix."ForumPost WHERE post_id =".$_GET['e']);							
										if($user->getRole() == "app-owner" || $user->getRole() == "app-group-administrator" || $ar['user_id'] == $user->getId()){
											$msg .= "
											
											Edited by ".$user->toString()." at ".date('D M j, Y G:i').".";
																								
											$query = sprintf("UPDATE `".$sql->prefix."ForumPost` SET `message` = '%s' WHERE post_id='%s'",
												mysql_real_escape_string($msg),
												mysql_real_escape_string($_GET['e']));
											$result = $sql->doUpdate($query);
											if(!$result){
												LayoutManager::alertFailure("Unexpected error. Try again later");
											} else {
												echo "<br><br>";
												$url = "?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$_GET['t']."#".$_GET['e'];
												LayoutManager::redirect($url);
											}
										} else {
											echo "<br><br>";
											$url = "?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$_GET['t']."#".$_GET['e'];
											LayoutManager::redirect($url);
										}
									}
								} else {
									LayoutManager::alertFailure("It's not allowd to quote more than 4 times in one post.");
								}
							}
						}
						if($invalid)
							echo Error::printError("You have a word witch contains more than 85 characters. Please fix it.");
						if(eregi("^[0-9]{1,11}$", $_GET['q'])){
							// get the message to be quoted and then add it to the message in the front
							$ar = $sql->getArray("SELECT * FROM ".$sql->prefix."ForumPost WHERE post_id =".$_GET['q']);							
							// Build quote string
							$msg = "[quote:".$ar['post_id']."]".$ar['message']."[/quote]";
						} else if(eregi("^[0-9]{1,11}$", $_GET['e'])){
							$ar = $sql->getArray("SELECT * FROM ".$sql->prefix."ForumPost WHERE post_id =".$_GET['e']);							
							if($ar['user_id'] != $user->getId() && !($user->getRole() == "app-owner" || $user->getRole() == "app-group-administrator")){
								// if not owner of the post or moderator, then redirect back to the post
								$url = "?module=forum&id=".$_GET['id']."&f=".$_GET['f']."&a=thread&t=".$_GET['t']."#".$_GET['e'];
								LayoutManager::redirect($url);
							}
							// Adding the message to the quote
							$msg = $ar['message'];
						}
						echo "<form action=\""."http://".$_SERVER["SERVER_NAME"].$_SERVER['REQUEST_URI']."\" method=\"post\">
						  <div class=\"form_settings\">
						    <p><span>Message</span><textarea class=\"contact textarea\" rows=\"15\" cols=\"150\" name=\"message\">".$msg."</textarea></p>
						    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
						  </div>
						</form>";
					} else {
						echo "<br><br>";
						// redirect back to app-forum frontpage
						$url = "?module=forum&id=".$_GET['id'];
						LayoutManager::redirect($url);
					}
					break;
				case 'manage':
					if($user->getRole() == "app-owner" || $user->getRole() == "app-group-administrator"){
						switch($_GET['m']){
							case 's':
								$array = $sql->getArray("SELECT sticky FROM `".$sql->prefix."ForumThread` WHERE thread_id=".$_GET['t']);
								if($array['sticky'] == 0)
									$stick = 1;
								else
									$stick = 0;
								$result = $sql->doUpdate("UPDATE `".$sql->prefix."ForumThread` SET `sticky` = '".$stick."' WHERE `thread_id` =".$_GET['t']);
								if(!$result)
									LayoutManager::alertFailure("Failed to change sticky");
								break;
							case 'l':
								$array = $sql->getArray("SELECT locked FROM `".$sql->prefix."ForumThread` WHERE thread_id=".$_GET['t']);
								if($array['locked'] == 0)
									$stick = 1;
								else
									$stick = 0;
								$result = $sql->doUpdate("UPDATE `".$sql->prefix."ForumThread` SET `locked` = '".$stick."' WHERE `thread_id` =".$_GET['t']);
								if(!$result)
									LayoutManager::alertFailure("Failed to change locked");
								break;
							default:
								// Do nothing
								break;
						}
						echo "<br><br>";
						$url = "?module=forum&id=".$_GET['id']."&a=forum&f=".$_GET['f'];
						LayoutManager::redirect($url);
					} else {
						echo "<br><br>";
						$url = "?module=forum&id=".$_GET['id']."&a=forum&f=".$_GET['f'];
						LayoutManager::redirect($url);
					}
					break;
				case 'subforum':
					if($user->getRole() == "app-owner"){
						if($_GET['save'] == "true"){
							$title = $_POST['title'];
							$msg = $_POST['message'];
							if(strlen($msg) > 4 && strlen($msg) < 100 && strlen($title) > 3 && strlen($title) < 40){
								if($_POST['group'] == 0){
									$query = sprintf("INSERT INTO `".$sql->prefix."Forum` (`name`, `desc`, `group`) VALUES ( '%s', '%s', NULL)",
											mysql_real_escape_string($title),
											mysql_real_escape_string($msg));
								} else {
									$query = sprintf("INSERT INTO `".$sql->prefix."Forum` (`name`, `desc`, `group`) VALUES ( '%s', '%s', '%s')",
											mysql_real_escape_string($title),
											mysql_real_escape_string($msg),
											mysql_real_escape_string($_POST['group']));
								}								
								if($sql->doUpdate($query)){
									$id = $sql->getLastId();
									$query = sprintf("INSERT INTO `".$sql->prefix."AppForum` (`forum_id`, `app_id`) VALUES ( '%s', '%s')",
											mysql_real_escape_string($id),
											mysql_real_escape_string($_GET['id']));
									if($sql->doUpdate($query)){
										LayoutManager::redirect("?module=forum&id=".$_GET['id']."&a=forum&f=".$id);
									}	
								}
								
							} else {
								LayoutManager::alertFailure("Title(between 4 - 41 characters). Description(between 5-100 chars)");
							}
						}
						echo "<form action=\"?module=forum&id=".$_GET['id']."&a=subforum&save=true\" method=\"post\">
						  <div class=\"form_settings\">
						  	<p><span>Title</span><input class=\"contact\" type=\"text\" name=\"title\" value=\"".$title."\" /></p>
						    <p><span>Description</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"message\">".$msg."</textarea></p>
						    	<p><span>Restriction</span><select name=\"group\">";
							    $platforms = $sql->getFullArray("SELECT G.group_id,G.name FROM `".$sql->prefix."Group` G, ".$sql->prefix."GroupApplication A WHERE app_id='".$_GET['id']."' AND G.group_id = A.group_id");
							    echo "<option value=\"0\">Public</option>\n";
							    echo "<option value=\"-1\">All groups</option>\n";
							    for ($i = 0; $i < count($platforms); $i++) {
							    	echo "<option value=\"".$platforms[$i]['group_id']."\">".$platforms[$i]['name']."</option>\n";
							    }
							    echo "</select></p>
						   <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
						  </div>
						</form>";
					}
					break;
				default:
					// list the forums attached to this application
					$query = "SELECT * FROM ".$sql->prefix."Forum WHERE forum_id IN (SELECT forum_id FROM ".$sql->prefix."AppForum WHERE app_id=".$_GET['id'].") AND (`group` IS null)";
					$array = $sql->getFullArray($query);

					echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
					for ($i = 0; $i < count($array); $i++) {
						// Gather stats
						$threads = $sql->getCount("SELECT * FROM ".$sql->prefix."ForumThread WHERE forum_id = ".$array[$i]['forum_id']);
						$posts = $sql->getCount("SELECT * FROM ".$sql->prefix."ForumThreadPost WHERE thread_id IN (SELECT thread_id FROM ".$sql->prefix."ForumThread WHERE forum_id = ".$array[$i]['forum_id'].")");
						// Gather last post			
						$query = "SELECT P.post_id, TP.thread_id, P.user_id, MAX( P.timestamp ) AS TIMESTAMP
							FROM ".$sql->prefix."ForumPost P, ".$sql->prefix."ForumThread T, ".$sql->prefix."ForumThreadPost TP
							WHERE P.post_id = TP.post_id AND T.thread_id = TP.thread_id AND 
							T.forum_id=".$array[$i]['forum_id']." GROUP BY P.post_id ORDER BY MAX( P.timestamp ) DESC";
						$last_post = $sql->getArray($query." LIMIT 1");
						
						$thread_post_count = $sql->getCount("SELECT * FROM ".$sql->prefix."ForumThreadPost WHERE thread_id=".$last_post['thread_id']);
						$max_Page = ceil($thread_post_count/10);
						// Print the forums linked to this app.
						echo "<tr>";
						echo "<td width=32px><img src=\"images/forum/forum_new.png\"></td>";
						echo "<td><h2><a href=\"?module=forum&id=".$_GET['id']."&a=forum&f=".$array[$i]['forum_id']."\">".$array[$i]['name']."</a></h2>\n
						".nl2br (strip_tags ($array[$i]['desc']))."</td>";
						echo "<td width=100px><b>Threads</b>: $threads<br>
						<b>Posts</b>: $posts</td>";
						if($posts > 0){
							$usera = new User();
							$usera->getInfo($last_post['user_id']);
							echo "<td width=200px>by ".$usera->toString()." 
							<a href=\"?module=forum&id=".$_GET['id']."&f=".$array[$i]['forum_id']."&a=thread&t=".$last_post['thread_id']."&page=".$max_Page."#".$thread_post_count."\">
							<img src=\"images/forum/viewpost.gif\" border=0></a><br>
							".strftime ("%a %b %d, %Y %H:%M", strtotime($last_post['TIMESTAMP']))."</td>";
						} else {
							echo "<td width=200px>No posts yet, be the first!</td>";
						}
						echo "<tr>";
					}
					
					// Open for ALL groups
					$query = "SELECT * FROM ".$sql->prefix."Forum WHERE forum_id IN (SELECT forum_id FROM ".$sql->prefix."AppForum WHERE app_id=".$_GET['id'].") AND `group`='-1'";
					$query2 = "SELECT app_id FROM ".$sql->prefix."GroupApplication WHERE app_id=".$_GET['id']." AND group_id IN (SELECT group_id FROM ".$sql->prefix."UserInGroup WHERE user_id=".$user->getId()." AND role <> 'Invited')";
					$array = $sql->getFullArray($query);
					if($sql->getCount($query)>0 && $sql->getCount($query2)>0){
						for ($i = 0; $i < count($array); $i++) {
							// Gather stats
							$threads = $sql->getCount("SELECT * FROM ".$sql->prefix."ForumThread WHERE forum_id = ".$array[$i]['forum_id']);
							$posts = $sql->getCount("SELECT * FROM ".$sql->prefix."ForumThreadPost WHERE thread_id IN (SELECT thread_id FROM ".$sql->prefix."ForumThread WHERE forum_id = ".$array[$i]['forum_id'].")");
							// Gather last post
							$query = "SELECT P.post_id, TP.thread_id, P.user_id, MAX( P.timestamp ) AS TIMESTAMP
								FROM ".$sql->prefix."ForumPost P, ".$sql->prefix."ForumThread T, ".$sql->prefix."ForumThreadPost TP
								WHERE P.post_id = TP.post_id AND T.thread_id = TP.thread_id AND 
								T.forum_id=".$array[$i]['forum_id']." GROUP BY P.post_id ORDER BY MAX( P.timestamp ) DESC";
							$last_post = $sql->getArray($query." LIMIT 1");
							
							// Checking what page the post is on
							$thread_post_count = $sql->getCount("SELECT * FROM ".$sql->prefix."ForumThreadPost WHERE thread_id=".$last_post['thread_id']);;
							$max_Page = ceil($thread_post_count/10);
							// Print the forums linked to this app.
							echo "<tr>";
							echo "<td width=32px><img src=\"images/forum/forum_new.png\"></td>";
							echo "<td><h2><a href=\"?module=forum&id=".$_GET['id']."&a=forum&f=".$array[$i]['forum_id']."\">".$array[$i]['name']."</a></h2>\n
							".nl2br (strip_tags ($array[$i]['desc']))."</td>";
							echo "<td width=100px><b>Threads</b>: $threads<br>
							<b>Posts</b>: $posts</td>";
							if($posts > 0){
								$usera = new User();
								$usera->getInfo($last_post['user_id']);
								echo "<td width=200px>by ".$usera->toString()." <a href=\"?module=forum&id=".$_GET['id']."&f=".$array[$i]['forum_id']."&a=thread&t=".$last_post['thread_id']."&page=".$max_Page."#".$thread_post_count."\">
								<img src=\"images/forum/viewpost.gif\" border=0></a><br>
								".strftime ("%a %b %d, %Y %H:%M", strtotime($last_post['TIMESTAMP']))."</td>";
							} else {
								echo "<td width=200px>No posts yet, be the first!</td>";
							}
							echo "<tr>";
						}
					}					
					
					// Group specific forums
					$query = "SELECT * FROM ".$sql->prefix."Forum WHERE forum_id IN (SELECT forum_id FROM ".$sql->prefix."AppForum WHERE app_id=".$_GET['id'].") AND `group` IN 
					(SELECT group_id FROM ".$sql->prefix."UserInGroup WHERE user_id=".$user->getId()." AND role <> 'Invited')";
					$array = $sql->getFullArray($query);
					if($sql->getCount($query)>0){
						for ($i = 0; $i < count($array); $i++) {
							// Gather stats
							$threads = $sql->getCount("SELECT * FROM ".$sql->prefix."ForumThread WHERE forum_id = ".$array[$i]['forum_id']);
							$posts = $sql->getCount("SELECT * FROM ".$sql->prefix."ForumThreadPost WHERE thread_id IN (SELECT thread_id FROM ".$sql->prefix."ForumThread WHERE forum_id = ".$array[$i]['forum_id'].")");
							// Gather last post
							$query = "SELECT P.post_id, TP.thread_id, P.user_id, MAX( P.timestamp ) AS TIMESTAMP
								FROM ".$sql->prefix."ForumPost P, ".$sql->prefix."ForumThread T, ".$sql->prefix."ForumThreadPost TP
								WHERE P.post_id = TP.post_id AND T.thread_id = TP.thread_id AND 
								T.forum_id=".$array[$i]['forum_id']." GROUP BY P.post_id ORDER BY MAX( P.timestamp ) DESC";
							$last_post = $sql->getArray($query." LIMIT 1");
							
							// Checking what page the post is on
							$thread_post_count = $sql->getCount("SELECT * FROM ".$sql->prefix."ForumThreadPost WHERE thread_id=".$last_post['thread_id']);;
							$max_Page = ceil($thread_post_count/10);
							// Print the forums linked to this app.
							echo "<tr>";
							echo "<td width=32px><img src=\"images/forum/forum_new.png\"></td>";
							echo "<td><h2><a href=\"?module=forum&id=".$_GET['id']."&a=forum&f=".$array[$i]['forum_id']."\">".$array[$i]['name']."</a></h2>\n
							".nl2br (strip_tags ($array[$i]['desc']))."</td>";
							echo "<td width=100px><b>Threads</b>: $threads<br>
							<b>Posts</b>: $posts</td>";
							if($posts > 0){
								$usera = new User();
								$usera->getInfo($last_post['user_id']);
								echo "<td width=200px>by ".$usera->toString()." <a href=\"?module=forum&id=".$_GET['id']."&f=".$array[$i]['forum_id']."&a=thread&t=".$last_post['thread_id']."&page=".$max_Page."#".$thread_post_count."\">
								<img src=\"images/forum/viewpost.gif\" border=0></a><br>
								".strftime ("%a %b %d, %Y %H:%M", strtotime($last_post['TIMESTAMP']))."</td>";
							} else {
								echo "<td width=200px>No posts yet, be the first!</td>";
							}
							echo "<tr>";
						}
					}
					
					echo "</table>";
					if($user->getRole() == "app-owner"){
						echo "[<a href=\"?module=forum&id=".$_GET['id']."&a=subforum\">Create Subforum</a>]";
					}
					break;
			}
		}		
	} else {
		LayoutManager::printLoginForm();
	}
?>