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
	if($_GET['profile'] == "") {
		if($_GET['action'] == "view"){
			$query = sprintf("SELECT * FROM ".$sql->prefix."User WHERE user_id='%s' AND activate='NULL'",
				mysql_real_escape_string($_GET['id']));
			$rad = $sql->getArray($query);
			
			if ($sql->getCount($query) == 0) {
				echo Error::printError("Invalid user-id.");
			} else {
				$usera = new User();
				$usera->newUser($rad['name'], $rad['email'], (int)$rad['user_id']);
				
				$epost = $rad['email'];
				
				$krollalfapos = strrpos($epost, '@');
				$lenpost = strlen($epost);
				$epost = substr_replace($epost, "@**********", $krollalfapos,(strlen($epost)-$krollalfapos-4));
				
				echo "<h2>".$usera->getName()."</h2>\n";
				
				echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">
				<tr valign=\"top\">
				<td width=\"20%\">
				<center>
				<img src=\"images/user/".$usera->getPicture()."\" border=\"0\">";
				if($rad['deleted'])
					echo "<font color=red><b>User banned</b></font>";
				if($usera->getId() != $user->getId()){
					if($_GET['follow'] == "true"){
						$user->followUser($usera->getId());
					}
					if($user->followingUser($usera->getId())){
						echo "<form action=\"?module=user&action=view&id=".$usera->getId()."&follow=true\" method=\"post\">
						<div class=\"form_settings\">";
						echo "<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Unfollow\" />";
						echo "</div>";
						echo "</form>";
					} else{
						echo "<form action=\"?module=user&action=view&id=".$usera->getId()."&follow=true\" method=\"post\">
						<div class=\"form_settings\">";
						echo "<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Follow\" />";
						echo "</div>";
						echo "</form>";	
					}
					echo "<form action=\"?module=msg#tab3\" method=\"post\">
					<div class=\"form_settings\">";
					echo "<p><input type=\"hidden\" name=\"mail\" value=\"".$usera->getUserName()."\">
					<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Send message\" /></p>";
					echo "</div>";
					echo "</form>";	
					echo "</center>";
				}
				echo "</td>
				<td width=\"75%\">
				<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">
				<tr valign=\"top\" style=\"border-left:1px solid #000; border-right:1px solid #000;\">
				<td width=\"50%\">
				<center><b>Developed</b></center>
				<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">
				<tr>
				<td>Application</td>
				<td>Last Update</td>
				</tr>";									
				$result = $usera->getApplications(6, 0, true);
				for($i=0; $i<count($result); $i++){
					echo $result[$i]->printRow()."\n";
				}
				echo "</table>
				</td>
				<td width=\"50%\">
				<center><b>Feed</b></center>
				<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" >
				<tr>
				<td>Message</td>
				<td>Date</td>
				</tr>";
				$history = array();
				$time = array();
				$result = $sql->getFullArray("SELECT * FROM ".$sql->prefix."AppAnnouncement WHERE user_id=".$usera->getId()." ORDER BY time DESC LIMIT 6");
				foreach ($result as $news) {
					if(strlen($news['header'])>25){
						$output['msg'] = "<a href=\"?action=view&id=".$news['announcement_id']."\" title=\"".$news['header']."\">".substr($news['header'], 0, 25)."..</a>";
					} else {
						$output['msg'] = "<a href=\"?action=view&id=".$news['announcement_id']."\">".$news['header']."</a>";
					} 
					$output['time'] = $news['time'];
					
					if (!in_array($output,$history)){
						$time[] = strtotime($post['time']);
						$history[] = $output;
					}
				}
				if (count($history)>7)
					$list_items = 7;
				else 
					$list_items = count($history);
				
				foreach ($history as $value) {
					echo "<tr>
					<td>".$value['msg']."</td>
					<td>".strftime ("%a %b %d, %Y %H:%M", strtotime($value['time']))."</td>
					</tr>";
				}
				echo "</table>
				</td>
				</tr>
				</table>
				</td>
				</tr>
				</table>";
				
				echo "<ul class=\"tabs\">
				<li><a href=\"#tab1\">Description</a></li>
				<li><a href=\"#tab2\">Followers</a></li>
				<li><a href=\"#tab3\">Following</a></li>
				</ul>
				
				<div class=\"tab_container\">
				<div id=\"tab1\" class=\"tab_content\">
				<!--Content-->";
				echo "<div class=\"form_settings\">"; 
				echo "<p>".nl2br ($rad['desc'])."</p>";
				echo "</div>";
				echo "</div>
				<div id=\"tab2\" class=\"tab_content\">
				<!--Content-->";
				$query = "SELECT * FROM ".$sql->prefix."UserFollowUser WHERE following=".$usera->getId();
				$result = $sql->getFullArray($query);
				for($i=0; $i<$sql->getCount($query); $i++){
					$ar = $sql->getArray("SELECT * FROM ".$sql->prefix."User WHERE user_id=".$result[$i]['follower']);
					echo "<a href=\"?module=user&action=view&id=".$ar['user_id']."\">".$ar['name']."</a><br>";
				}
				echo "</div>
				<div id=\"tab3\" class=\"tab_content\">
				<!--Content-->";
				$query = "SELECT * FROM ".$sql->prefix."UserFollowUser WHERE follower=".$usera->getId();
				$result = $sql->getFullArray($query);
				for($i=0; $i<$sql->getCount($query); $i++){
					$ar = $sql->getArray("SELECT * FROM ".$sql->prefix."User WHERE user_id=".$result[$i]['following']);
					echo "<a href=\"?module=user&action=view&id=".$ar['user_id']."\">".$ar['name']."</a><br>";
				}
				echo "</div>
				</div>";                 
			}
		}
		if($_GET['action'] == ""){
			
			/* variables */
			$max_users = 30;
			$offset = 0; 
			if($_GET['offset'] != ""){
				$offset = $_GET['offset']; 
			}
			
			$query = "SELECT * FROM ".$sql->prefix."User WHERE activate='NULL'";
			
			$cntUsers = $sql->getCount($query);
			
			if($cntUsers != 0){	
				$query = "SELECT email,name,user_id FROM ".$sql->prefix."User WHERE activate='NULL' AND deleted=0 ORDER BY email ASC LIMIT $offset,$max_users";
				
				$result = $sql->getFullArray($query);
				$usera = new User();
				echo "
				<ul class=\"tabs\">
				<li><a href=\"#tab1\">All Users</a></li>
				<li><a href=\"#tab2\">Search</a></li>";
				if (isset($_POST['search']) && $_POST['search'] != ""){
					echo "<li><a href=\"#tab3\">Result</a></li>";
				}	    			
				echo "</ul>
				
				<div class=\"tab_container\">
				<div id=\"tab1\" class=\"tab_content\">
				<!--Content-->";
				// Grab thread-post count
				$post_count = $sql->getCount("SELECT * FROM ".$sql->prefix."User WHERE deleted=0 AND activate='NULL'");
				
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
				$users = $sql->getFullArray("SELECT * FROM ".$sql->prefix."User WHERE deleted=0 AND activate='NULL' LIMIT $offset,$rowsPerPage");
				
				// how many pages we have when using paging?
				$maxPage = ceil($post_count/$rowsPerPage);
				
				// print the link to access each page
				$self = "?module=user";
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
				echo "</div><br>";
				// Show all the users with their picture attached.
				echo "<div class=\"container\" style=\"width: 850px;\">";
				for ($i = 0; $i < count($users); $i++) {
					echo "<div style=\"width: 183px; height: 225px; float: left; border: 1px solid #999; margin: 5px;\">";
					echo "<center>
					<h3><a href=\"?module=user&action=view&id=".$users[$i]['user_id']."\">".$users[$i]['name']."</a></h3>
					<img src=\"images/user/".$users[$i]['picture']."\" border=\"0\">
					<br>";
					echo "</center>
					</div>";
				}
				echo "</div>";
				echo "</div>
				<div id=\"tab2\" class=\"tab_content\">
				<!--Content-->";
				echo "Search by name or e-mail address";
				echo "<form action=\"?module=user#tab3\" method=\"post\">
				<div class=\"form_settings\"> 
				<p><span>Search</span><input class=\"contact\" type=\"text\" name=\"search\" value=\"\" /></p>
				<p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Search\" /></p>";
				echo "</div>";
				echo "</form>";
				echo "</div>";
				
				if(isset($_POST['search']) && $_POST['search'] != ""){
					echo "<div id=\"tab3\" class=\"tab_content\">";
					$input = $_POST['search'];
					$query = sprintf("SELECT user_id FROM ".$sql->prefix."User WHERE name LIKE '%s' OR email LIKE '%s'",
						mysql_real_escape_string("%".$input."%"),
						mysql_real_escape_string("%".$input."%"));
					$array = $sql->getFullArray($query);
					echo "Searched for : '".$input."'";
					
					$u = new User();
					for ( $i = 0; $i < count($array); $i++ ) {
						$u->getInfo($array[$i]['user_id']);
						echo "<div>".$u->toString()."</div>";
					}						   
					if (count($array)==1){
						echo "<br>The search returned ".count($array)." result.";
					} else{
						echo "<br>The search returned ".count($array)." results.";
					}
					echo "</div>";
				}  						
				echo "</div>";
			}	
		} else {
			// 404a function does not excist
		}
	}
} else {
	// Posting loginform
	LayoutManager::printLoginForm();
}
?>