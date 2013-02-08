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
		if($_GET['action'] == ""){
			// Showing applications
			echo "
			<ul class=\"tabs\">
			    <li><a href=\"#tab1\">Browse</a></li>
			    <li><a href=\"#tab2\">Search</a></li>";
			if(isset($_POST['search']) && $_POST['search'] != ""){
				echo "<li><a href=\"#tab3\">Result</a></li>";
			}
			echo "</ul>
			
			<div class=\"tab_container\">
			    <div id=\"tab1\" class=\"tab_content\">
			        <!--Content-->";
				// Show cats
				$category = $sql->getFullArray("SELECT * FROM ".$sql->prefix."Category");
				
				if($_GET['cat'] != "")
					$cat = $_GET['cat'];
				else 
					$cat = $category[0]['category_id'];
					
				echo "<div style=\"float: left; width: 140px; padding-right:10px;\">";
				echo "<br><table cellspacing=0 cellpadding=0 border=0 width=150px>\n";
				echo "<tr>
					<td><b>Category</b></td>
					</tr>\n";
				for ($i = 0; $i < count($category); $i++) {
					echo "<tr>
					<td>";
					if($cat != $category[$i]['category_id'])
						echo "<a href=\"?module=app&cat=".$category[$i]['category_id']."\" title=\"".$category[$i]['desc']."\">".$category[$i]['name']."</a>";
					else
						echo $category[$i]['name'];
					echo "</td>\n
						</tr>";						
				}
				echo "</table>\n";
				echo "</div>";
				// paginator
				$query = "SELECT * FROM ".$sql->prefix."ApplicationView WHERE category=".$cat." ORDER BY released ASC";
				
				// Grab thread-post count
				$post_count = $sql->getCount($query);
				
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
				$result = $sql->getFullArray($query." LIMIT $offset,$rowsPerPage");
					
				// how many pages we have when using paging?
				$maxPage = ceil($post_count/$rowsPerPage);
				
				// print the link to access each page
				if($_GET['cat'] != "")
					$self = "?module=app&cat=".$_GET['cat'];
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
				// show applications connected with that category
				echo "<div style=\"float: right; width: 630px;\">";
				// print the navigation link
				echo "<div style=\"float: right;\">";
				echo $first . $prev . $nav . $next . $last;
				echo "</div><br>";
				echo "<table cellspacing=0 cellpadding=0 border=0 width=630px>\n";
				echo "<tr>
					<td><b>Name</b></td>
					<td><b>Last Update</b></td>
				</tr>";

				for($i=0; $i<count($result); $i++){
					$app = new App($result[$i]['name'], $result[$i]['public'], $result[$i]['desc'], $result[$i]['released'], $result[$i]['app_id'], $result[$i]['platform']);
					echo $app->printRow()."\n";
				}
				echo "</table>\n";
				echo "</div>";	
			   echo "</div>
			    <div id=\"tab2\" class=\"tab_content\">
			       <!--Content-->";
					echo "<form action=\"?module=app#tab3\" method=\"post\">
						  <div class=\"form_settings\"> 
						  <p><span>Search</span><input class=\"contact\" type=\"text\" name=\"search\" value=\"\" /></p>
						  <p>Search by name or tags, multiple tags should be separated with ','.</p>
						  <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Search\" /></p>";
					echo "</div>";
					echo "</form>";
			    echo "</div>";
			    if(isset($_POST['search']) && $_POST['search'] != ""){
				    	echo "<div id=\"tab3\" class=\"tab_content\">";
						$input = $_POST['search'];
						$tags = explode(",", $_POST['search']);
						$app = array();

						for ($i = 0; $i < count($tags); $i++) {
							$tag = ereg_replace("[^A-Za-z0-9 ]", "", $tags[$i]);
							// Check if the tag allready excists
							if(strlen($tag) > 1 && strlen($tag) < 45){
								$query = sprintf("SELECT app_id FROM ".$sql->prefix."Application WHERE name LIKE '%s' AND public=1",
									mysql_real_escape_string(strtolower(trim("%".$tag."%"))));
									
								$apps = $sql->getFullArray($query);
								for ($j = 0; $j < count($apps); $j++) {
									if($sql->getCount("SELECT * FROM ".$sql->prefix."ApplicationUpdates WHERE public=1 AND app_id=".$apps[$j]['app_id'])>0){
										// It's over 9000!!
										$app[$apps[$j]['app_id']] = $sql->getCount("SELECT tag_id FROM ".$sql->prefix."ApplicationTag") * 2;
									}
								}
								
								$query = sprintf("SELECT tag_id FROM ".$sql->prefix."Tag WHERE tag='%s'",
									mysql_real_escape_string(strtolower(trim($tag))));
								$array = $sql->getArray($query);
								if($array){
									$apps = $sql->getFullArray("SELECT app_id FROM ".$sql->prefix."ApplicationTag WHERE tag_id=".$array['tag_id']);
									for ($j = 0; $j < count($apps); $j++) {
										$app[$apps[$j]['app_id']] = (int)$app[$apps[$j]['app_id']]+(int)$sql->getCount("SELECT user_id FROM ".$sql->prefix."ApplicationTag WHERE tag_id=".$array['tag_id']." AND app_id=".$apps[$i]['app_id']);
									}
								}				
							}
						}
						echo "Searched for : '".$input."'";
						if(count($app)>0){
							// Sort the result
							arsort($app);	
							$a = new App();
							// show the result
							echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
							echo "<tr>
									<td><b>Name</b></td>
									<td><b>Last Update</b></td>
								</tr>";
							foreach ($app as $key => $value) {
								$apps = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationView WHERE app_id=".$key);
								for ($i = 0; $i < count($apps); $i++) {
									$a->getInfoFromSQL($key, $apps[$i]['platform_id']);
									echo $a->printRow();
								}
							}
							echo "</table>";
						}
						// end search
						echo "<br>The search returned ".count($app)." rows.";

					echo "</div>";
			    }  
			echo "</div>";
		} else if($_GET['action'] == "view"){
			// View one application if the ID AND platform is valid numbers
			if(eregi("^[0-9]{1,11}$", $_GET['id']) && eregi("^[0-9]{1,11}$", $_GET['platform'])){
				$query = sprintf("SELECT * FROM ".$sql->prefix."ApplicationView WHERE app_id='%s'",
					mysql_real_escape_string($_GET['id']));
				$check = $sql->getCount($query);
				if($check > 0){
					$app = new App();
					$app->getInfoFromSQL($_GET['id'], $_GET['platform']);
					
					// Silly workaround for forum
					$_SESSION['app_plat'] = $_GET['platform'];

					// ** HANDLE TAGS ** 
					if($_GET['tag'] == "true"){
						if($_POST['tag'] != ""){
							$tags = explode(",", $_POST['tag']);
						} else {
							$tags = explode(",", $_GET['tags']);
						}
						// Add the tags to the application
						$app->addTag($tags);
					}
					
					// ** HANDLE FOLLOW
					if($_GET['follow'] == "true"){
						$result = $user->followApp($_GET['id']);
						if($result != "true")
							echo Error::printError("Invalid application.");
					}
					
					// ** HANDLE FEEDBACK ** 
					$posted = false;
					$installed = false; 
					// Check if the user got the application installed
					if($sql->getCount("SELECT * FROM ".$sql->prefix."UserDeviceInstalledApps WHERE app_id=".$app->getId()." AND user_device_id IN (SELECT user_device_id FROM ".$sql->prefix."UserDevice WHERE user_id=".$user->getId().")") > 0)
						$installed = true;
					// Check if the user has posted feedback allready
					if($sql->getCount("SELECT * FROM ".$sql->prefix."ApplicationFeedback WHERE app_id=".$app->getId()." AND user_id=".$user->getId()) > 0)
						$posted = true;
					// handle insertion of feedback
					if(!$posted && $installed && isset($_POST['feedback']) && $_POST['feedback'] != ""){
						$feed = strip_tags($_POST['feedback']);
						if(strlen($feed) > 6 && strlen($feed) < 300){
							if($_POST['reco'] == "1")
								$reco = 1;
							else
								$reco = 0;
							$query = sprintf("INSERT INTO `".$sql->prefix."ApplicationFeedback` (`app_id`,`user_id`,`recommend`,`message`) VALUES ('%s','%s','%s','%s')",
								mysql_real_escape_string($app->getId()),
								mysql_real_escape_string($user->getId()),
								mysql_real_escape_string($reco),
								mysql_real_escape_string($feed));
							if($sql->doUpdate($query))
								$posted = true;
							else
								echo Error::printError("Unexpected error while handling your request.");
						} else {
							echo "<br>You need to enter a feedback message.";
						}
					}
					
			echo "<ul class=\"tabs\">
				    <li><a href=\"#tab1\">Information</a></li>
				    <li><a href=\"#tab6\">Feedback</a></li>
				    <li><a href=\"#tab3\">Groups</a></li>
				    <li><a href=\"#tab4\">Messageboard</a></li>
				    <li><a href=\"#tab5\">Tags</a></li>";
				if($installed){
				    echo "<li><a href=\"#tab2\">Bug report</a></li>";
				    echo "<li><a href=\"#tab7\">Report Issue</a></li>";
				}
			echo "</ul>
				
				<div class=\"tab_container\">
				    <div id=\"tab1\" class=\"tab_content\">";
					$app->printInfo();
					echo "<div class=\"container\"><B>Feedback</B><br>";
					$feedback = $app->getShortFeedback();
					$feed_user = new User();
					if(count($feedback) > 0){
						for ($i = 0; $i < count($feedback); $i++) {
							$feed_user->getInfo($feedback[$i]['user_id']);
							if($feedback[$i]['recommend'])
								$out = "up";
							else
								$out = "down";
							echo "<div class=\"container\" style=\"border: 1px solid #999; padding: 5px; margin-bottom: 5px;\">
							<div style=\"float: left;\">".$feed_user->toString()."</div><div style=\"float: right;\"><img src=\"images/thumb-".$out.".png\"></div><br>
							<div class=\"container\">".$feedback[$i]['message']."</div></div>";
						}
					} else {
						echo "<i>None</i>";
					}

					echo "</div><br>";
					echo "<div class=\"container\"><B>Tags</B><br>";
					print $app->printTagCloud();
					echo "</div><br>";
					// List the dependencies if the app got any
					$dependencies = $app->getDependencies();
					if(count($dependencies)>0 && $dependencies[0] != ""){
						echo "<div class=\"container\"><B>Dependencies</B><br>";
						echo "<ul>";
						foreach ($dependencies as $depen) {
							echo "<li>$depen</li>";
						}
						echo "</ul>";
						echo "</div>";
					}
					echo "</div>";
					if($installed){
						echo "<div id=\"tab2\" class=\"tab_content\">";
						if(isset($_POST['device']) && isset($_POST['bug_report']) && $_POST['bug_report'] != "" && eregi("^[0-9]{1,11}$", $_POST['device'])){
							$query = sprintf("INSERT INTO `".$sql->prefix."ApplicationBug` (`app_id`,`user_device_id`,`desc`) VALUES ('%s', '%s', '%s')",
								mysql_real_escape_string($app->getId()),
								mysql_real_escape_string($_POST['device']),
								mysql_real_escape_string(strip_tags($_POST['bug_report'])));
							if(!$sql->doUpdate($query))
								echo Error::printError("Unexpected error while handling your request.");
							else 
								echo "Bug report posted.<br>";
						}
							echo "<form action=\"?module=app&action=view&platform=".$_GET['platform']."&id=".$_GET['id']."#tab2\" method=\"post\">
								  <div class=\"form_settings\"> 
								    <p><span>Device</span><select class=\"contact\" name=\"device\">";
								    $device = $sql->getFullArray("SELECT user_device_id, name FROM ".$sql->prefix."UserDevice WHERE user_id=".$user->getId()." AND user_device_id IN 
								    (SELECT user_device_id FROM ".$sql->prefix."UserDeviceInstalledApps WHERE app_id=".$app->getId().")");
								    for ($i = 0; $i < count($device); $i++) {
								    	echo "<option value=\"".$device[$i]['user_device_id']."\">".$device[$i]['name']."</option>\n";
								    }
								    echo "</select></p>
								  <p><span>Bug</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"bug_report\"></textarea></p>
								  <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>";
							echo "</div>";
							echo "</form>";
						echo "</div>";
					}
					echo "<div id=\"tab3\" class=\"tab_content\">";
					echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
						$app_groups = $app->getGroups();
						$my_app = false;
						$user_apps = $user->getApplications(0,0,false); 
						for ($i = 0; $i < count($user_apps); $i++) {
							$check_app = $user_apps[$i];
							if($check_app->compare($app))
								$my_app = true;
						}
						if($my_app){
							for ($i = 0; $i < count($app_groups); $i++) {
								echo "<tr><td>".$app_groups[$i]->toString()."</td></tr>";
							}
						} else {
							$user_groups = $user->getGroups(0,0);
							for ($i = 0; $i < count($app_groups); $i++) {
								if($app_groups[$i]->isPublic())
									echo "<tr><td>".$app_groups[$i]->toString()."</td></tr>";
								else {
									for ($j = 0; $j < count($user_groups); $j++) {
										if($app_groups[$i]->compare($user_groups[$j]))
											echo "<tr><td>".$app_groups[$i]->toString()."</td></tr>";
									}
								}
							}
						}
						echo "</table>";
					echo "</div>
					<div id=\"tab4\" class=\"tab_content\">";
						include('modules/forum.inc.php');
					echo "</div>
					<div id=\"tab5\" class=\"tab_content\">";
						echo "<div class=\"container\">";
						print $app->printTagCloud();
						echo "</div>";
						// Poster form for entering tags for an application.
						echo "<form action=\"?module=app&action=view&platform=".$_GET['platform']."&id=".$_GET['id']."&tag=true#tab5\" method=\"post\">
							  <div class=\"form_settings\"> 
							  <p><span>Tag</span><input class=\"contact\" type=\"text\" name=\"tag\" value=\"\" /></p>
							  <p>Seperate tags with , for multiple tags.</p> 
							  <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Add tag\" /></p>";
						echo "</div>";
						echo "</form>";
					echo "</div>
					<div id=\"tab6\" class=\"tab_content\">";
						echo "<b>Feedback</b>";
						// show feedbacks order by timestamp
						$feed_user = new User();
						$feedback = $app->getAllFeedback();
						if(count($feedback) > 0){
							for ($i = 0; $i < count($feedback); $i++) {
								$feed_user->getInfo($feedback[$i]['user_id']);
								if($feedback[$i]['recommend'])
									$out = "up";
								else
									$out = "down";
								echo "<div class=\"container\" style=\"border: 1px solid #999; padding: 5px; margin-bottom: 5px;\">
								<div style=\"float: left;\">".$feed_user->toString()."</div><div style=\"float: right;\"><img src=\"images/thumb-".$out.".png\"></div><br>
								<div class=\"container\">".$feedback[$i]['message']."</div></div>";
							}
						} else {
							echo "<br><i>None</i>";
						}
						
						// if installed and not gived feedback yet give him the opertunity to post feedback
						if(!$posted && $installed){
							echo "<form action=\"?module=app&action=view&platform=".$_GET['platform']."&id=".$_GET['id']."#tab6\" method=\"post\">
								  <div class=\"form_settings\"> 
								  <p><span>Recommend</span><input type=\"radio\" name=\"reco\" value=\"1\" checked></p>
								  <p><span>Not Recommend</span><input type=\"radio\" name=\"reco\" value=\"0\"></p>
								  <p><span>Feedback</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"feedback\"></textarea></p>
								  <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>";
							echo "</div>";
							echo "</form>";
						} else {
							if($posted)
								echo "<br>You have already given feedback to this application.";
							else 
								echo "<br>You need to have this application installed to give feedback."; 
						}
					echo "</div>
					<div id=\"tab7\" class=\"tab_content\">
						<h2>Report an issue</h2>
						Here you can report any suspisions towards this application. If you suspect it for having virus or other problems related to security.";
						if(isset($_POST['report']) && $_POST['report'] != "" && strlen($_POST['report']) > 10){
							$query = sprintf("INSERT INTO ".$sql->prefix."ReportedApplication (user_id, app_id, note) VALUES ('%s', '%s', '%s')",
								mysql_real_escape_string($user->getId()),
								mysql_real_escape_string($app->getId()),
								mysql_real_escape_string(strip_tags($_POST['report'])));
							if($sql->doUpdate($query)){
								LayoutManager::alertSuccess("Your message has been successfully reported.");
							} else {
								LayoutManager::alertFailure("You have already reported an issue about this application");
							}
						}
						echo "<form action=\"?module=app&action=view&platform=".$_GET['platform']."&id=".$_GET['id']."#tab7\" method=\"post\">
							  <div class=\"form_settings\"> 
							  <p><span>Note</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"report\"></textarea></p>
							  <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>";
						echo "</div>";
						echo "</form>";					
					
					echo "</div>";
					echo "</div>";
					
				} else {
					echo "Invalid application.";
				}
			} else {
				echo "Invalid application.";
			}
		} else if($_GET['action'] == "manage") {
			// For managing applications that the user "owns"
			if(eregi("^[0-9]{1,11}$", $_GET['id'])){
		       	$query = sprintf("SELECT * FROM ".$sql->prefix."UserApplication WHERE user_id='%s' AND app_id='%s'",
						mysql_real_escape_string($user->getId()),
						mysql_real_escape_string($_GET['id']));
				if($sql->getCount($query) > 0){
					$app = new App();
					$app->getBasicInfo($_GET['id']);
					$app->printDashboard();
					switch ($_GET['f']){
						case 'bugs':
							switch ($_GET['a']){
								case 'view':
									if(eregi("^[0-9]{1,11}$", $_GET['bid'])){
										$array = $sql->getArray("SELECT * FROM ".$sql->prefix."ApplicationBug WHERE bug_id=".$_GET['bid']);
										$device = new Device($array['user_device_id']);
										$usera = new User();
										$usera->getInfo($device->getUser());
										echo "<div class=\"form_settings\" style=\"position: relative;\">
									    <h2>".strip_tags($device->toString(),'<img>')." - ".$usera->toString()." </h2>
									    <br>
									    <p>".nl2br (strip_tags ($array['desc'], '<a><b><i><u><img>'))."</p>";
									    // Post change status form
									    echo "<form action=\"?module=app&action=manage&id=".$_GET['id']."&f=bugs&a=status&bid=".$_GET['bid']."\" method=\"post\">
											  <div class=\"form_settings\">
											    <p><span>Status</span><select name=\"status\">";
											    echo "<option value=\"1\" "; if($array['status'] == "Discovered"){ echo "selected";} echo ">Discovered</option>\n";
											    echo "<option value=\"2\" "; if($array['status'] == "Working on"){ echo "selected";} echo ">Working on</option>\n";
											    echo "<option value=\"3\" "; if($array['status'] == "Fixed"){ echo "selected";} echo ">Fixed</option>\n";
											    echo "</select></p>
											    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Change\" /></p>
											  </div>
											</form>";
										// post add comment form
										echo "<form action=\"?module=app&action=manage&id=".$_GET['id']."&f=bugs&a=note&bid=".$_GET['bid']."\" method=\"post\">
											  <div class=\"form_settings\">
											    <p><span>Note</span><textarea name=\"note\">".$array['note']."</textarea></p>
											    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
											  </div>
											</form>";
									  echo "</div>";
									}
									break;
								case 'status':
									// Change status
									if(eregi("^[0-9]{1,11}$", $_GET['bid'])){
										switch ($_POST['status']) {
											case '1':
												$status = "Discovered";
												// Update database
												$query = "UPDATE ".$sql->prefix."ApplicationBug SET status='".$status."' WHERE bug_id=".$_GET['bid'];
												if($sql->doUpdate($query))
													LayoutManager::redirect("?module=app&action=manage&id=".$_GET['id']."&f=bugs&a=view&bid=".$_GET['bid']);
												else
													echo "Error while handling your request. Please try again later.";
												break;
											case '2':
												$status = "Working on";
												// Update database
												$query = "UPDATE ".$sql->prefix."ApplicationBug SET status='".$status."' WHERE bug_id=".$_GET['bid'];
												if($sql->doUpdate($query))
													LayoutManager::redirect("?module=app&action=manage&id=".$_GET['id']."&f=bugs&a=view&bid=".$_GET['bid']);
												else
													echo "Error while handling your request. Please try again later.";
												break;
											case '3':
												$status = "Fixed";
												// Update database
												$query = "UPDATE ".$sql->prefix."ApplicationBug SET status='".$status."' WHERE bug_id=".$_GET['bid'];
												if($sql->doUpdate($query))
													LayoutManager::redirect("?module=app&action=manage&id=".$_GET['id']."&f=bugs&a=view&bid=".$_GET['bid']);
												else
													echo "Error while handling your request. Please try again later.";
												break;
											
											default:
												// DO NOTHING
												break;
										}
									}
									break;
								case 'note':
									// Add note
									if(eregi("^[0-9]{1,11}$", $_GET['bid'])){
										$note = $_POST['note'];
										$note = strip_tags($note);
										
										$query = sprintf("UPDATE ".$sql->prefix."ApplicationBug SET note='%s' WHERE bug_id='%s'",
											mysql_real_escape_string($note),
											mysql_real_escape_string($_GET['bid']));
										if($sql->doUpdate($query))
												LayoutManager::redirect("?module=app&action=manage&id=".$_GET['id']."&f=bugs&a=view&bid=".$_GET['bid']);
										else
											echo "Error while handling your request. Please try again later.";
									}
									break;
								default:
									// List all the bugs reported
									$array = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationBug WHERE app_id=".$app->getId()." ORDER BY status DESC");
									echo "<br><div><div style=\"float:left; width: 150px;\"><b>Bug ID</b></div>
									<div style=\"float:left;\"><b>Status</b></div></div><br>";
									for ($i = 0; $i < count($array); $i++) {
										echo "<div><div style=\"float:left; width: 150px;\">
										<a href=\"?module=app&action=manage&id=".$app->getId()."&f=bugs&a=view&bid=".$array[$i]['bug_id']."\">".$array[$i]['bug_id']."</a>
										</div><div style=\"float:left;\">".$array[$i]['status']."</div></div><br>";	
									}
									break;
							}
							break;
						case 'screenshot':
							if($_GET['save'] == "true"){
								FileHandler::uploadScreenshots($_FILES['file'], $app->getId());
							}
							if($_GET['delete'] == "true" && eregi("^[0-9]{1,11}$", $_GET['ss'])){
								// Delete file
								$query = "SELECT * FROM ".$sql->prefix."ApplicationScreenshots WHERE screenshot_id=".$_GET['ss']." AND app_id=".$_GET['id'];
								if($sql->getCount($query) > 0){
									$ar = $sql->getArray($query);
									$url = "mobile/files/ss/".$ar['url'];
									$query = "DELETE FROM ".$sql->prefix."ApplicationScreenshots WHERE screenshot_id=".$_GET['ss']." AND app_id=".$_GET['id']; 
									if($sql->doUpdate($query)){
										FileHandler::delete($url);
									}
								}
							}
							// listing all the screenshots
							$ar = $app->getScreenshots();
							if(count($ar) > 0){
								echo "<div id=\"gallery\" style=\"width: 600px; padding: 10px;\">
								<ul>";
								for ($i = 0; $i < count($ar); $i++) {
									echo "<li>
								            <a href=\"mobile/files/ss/".$ar[$i]['url']."\" title=\"&lt;a href='?module=app&action=manage&id=".$_GET['id']."&f=screenshot&delete=true&ss=".$ar[$i]['screenshot_id']."'&gt;Delete&lt;/a&gt;\">
								                <img src=\"mobile/files/ss/".$ar[$i]['url']."\" width=\"72\" height=\"72\" alt=\"\" />
								            </a>
								        </li>";
								}
								echo "</ul></div>";
							} else {
								echo "No screenshots of this application.<br>";
							}

							echo "<br>Only the first 9 pictures will be uploaded";
							echo "<form action=\"?module=app&action=manage&id=".$_GET['id']."&f=screenshot&save=true\" method=\"post\" enctype=\"multipart/form-data\">
							  <div class=\"form_settings\">
							    <p><span>Files</span><input id=\"file\" class=\"contact\" type=\"file\" multiple=\"true\" name=\"file[]\" /></p>
							    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
							  </div>
							</form>";
							break;
						case 'privacy':
							$query = sprintf("UPDATE ".$sql->prefix."Application SET public=1 WHERE app_id='%s'", 
								mysql_real_escape_string($app->getId()));
							
							if($sql->doUpdate($query))
								LayoutManager::alertSuccess("Application visibility set to public.");
							break;
						case 'history':
							echo "\n<table cellspacing=0 cellpadding=0 border=0 width=100%>";
							$app_updates = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$app->getId()." ORDER BY released DESC");
							for ($i = 0; $i < count($app_updates); $i++) {
								echo "<tr><td>".$app_updates[$i]['revision_nr']."</td><td>".$app_updates[$i]['released']."</td>
								<td>";
								if($app_updates[$i]['public'])
									echo "Public";
								else
									echo "For testing only.";
								echo "</td></tr>";
							}
							echo "</table>";
							break;
						case 'dependencies':
							if(isset($_POST['depen'])){
								// Filter htmltags
								$input = strip_tags($_POST['depen']);
								// Update the dependencies in the database
								$query = sprintf("UPDATE ".$sql->prefix."Application SET dependencies='%s' WHERE app_id='%s'",
									mysql_real_escape_string($input),
									mysql_real_escape_string($app->getId()));
								// Give the user a indication on the outcome
								if($sql->doUpdate($query))
									LayoutManager::alertSuccess("Dependencies updated.");
								else
									LayoutManager::alertFailure("An error has occured while handling your request. Please try again.");
							}
							// Gather the excisting dependencies
							$app_depen = $sql->getArray("SELECT dependencies FROM ".$sql->prefix."Application WHERE app_id=".$app->getId());
							echo "<form action=\"?module=app&action=manage&id=".$_GET['id']."&f=dependencies\" method=\"post\">
								  <div class=\"form_settings\"> 
								  <p><span>Dependencies</span><input class=\"contact\" type=\"text\" name=\"depen\" value=\"".$app_depen[0]."\" /></p>
								  <p>Seperate dependencies with , for multiple dependencies.</p> 
								  <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>";
							echo "</div>";
							echo "</form>";
							break;
						case 'icon':
							if($_GET['save'] == "true"){
								$output = FileHandler::uploadIcon($_FILES['file'], $app->getId());
								if($output['success'] != "true"){
									echo $output['error'];
								}
							}
							if(file_exists("mobile/files/icon/".$app->getId().".png"))
								$img = "mobile/files/icon/".$app->getId().".png";
							else 
								$img = "mobile/files/icon/0.png";
								
							echo "<form action=\"?module=app&action=manage&id=".$_GET['id']."&f=icon&save=true\" method=\"post\" enctype=\"multipart/form-data\">
							  <div class=\"form_settings\">
							  <div style=\"float: right; text-align: center; width: 50px;\"><img src=\"$img\" border=\"0\"></div>
							    <p><span>Files</span><input id=\"file\" class=\"contact\" type=\"file\" name=\"file\" /></p>
							    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
							  </div>
							</form>";
							break;
						case 'announcement':
							echo "<br>
							<center>";
							if($_GET['s'] == ""){
								echo "View - <a href=\"?module=app&action=manage&id=".$_GET['id']."&f=announcement&s=post\">Post</a></center>";
							} else {
								echo "<a href=\"?module=app&action=manage&id=".$_GET['id']."&f=announcement\">View</a> - Post</center>";
							}
							if($_GET['s'] == ""){
								$query = "SELECT * FROM ".$sql->prefix."AppAnnouncement WHERE app_id=".$_GET['id']." ORDER BY time DESC";
								$news = $sql->getFullArray($query);
								echo "\n<table cellspacing=0 cellpadding=0 border=0 width=100%>";
								foreach ($news as $post) {
									echo "<tr><td><a href=\"?action=view&id=".$post['announcement_id']."\">".$post['header']."</a></td><td>".$post['time']."</td><td><a href=\"?module=app&action=manage&id=".$_GET['id']."&f=announcement&s=edit&post=".$post['announcement_id']."\">Edit</a></td>
								</tr>";
								}
								echo "</table>";
							} else if($_GET['s'] != "edit") {
								if($_POST['header'] != "" && $_POST['body'] != ""){
									// evaluate data
									$header = $_POST['header'];
									$body = $_POST['body'];
									if(strlen($header) > 3 && strlen($header) < 81){
										if(strlen($body) > 3 && strlen($body) < 4000){
											$query = sprintf("INSERT INTO ".$sql->prefix."AppAnnouncement (app_id, user_id, header, body) VALUES ('%s', '%s', '%s', '%s')",
												mysql_real_escape_string($_GET['id']),
												mysql_real_escape_string($user->getId()),
												mysql_real_escape_string($header),
												mysql_real_escape_string($body));
											if($sql->doUpdate($query))
												LayoutManager::alertSuccess("Announcement posted.");
											else
												LayoutManager::alertFailure("An error has occured while handling your request.");
										} else {
											LayoutManager::alertFailure("Body needs to be atleast 10 and less than 4000 characters long.");
										}
									} else {
										LayoutManager::alertFailure("Header needs to be atleast 4 and less than 80 characters long.");
									}
								}
								echo "<form action=\"?module=app&action=manage&id=".$_GET['id']."&f=announcement&s=post\" method=\"post\">
									  <div class=\"form_settings\">
									  <p><span>Header</span><input type=\"text\" name=\"header\"></p> 
									  <p><span>Body</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"body\"></textarea></p>
									  <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>";
								echo "</div>";
								echo "</form>";	
							} else {
								if(eregi("^[0-9]{1,11}$", $_GET['post'])){
									if($_POST['header'] != "" && $_POST['body'] != ""){
										// evaluate data
										$header = $_POST['header'];
										$body = $_POST['body'];
										if(strlen($header) > 3 && strlen($header) < 81){
											if(strlen($body) > 3 && strlen($body) < 4000){
												$query = sprintf("UPDATE ".$sql->prefix."AppAnnouncement SET header='%s', body='%s' WHERE announcement_id='%s'",
													mysql_real_escape_string($header),
													mysql_real_escape_string($body),
													mysql_real_escape_string($_GET['post']));
												if($sql->doUpdate($query))
													LayoutManager::alertSuccess("Announcement edited.");
												else
													LayoutManager::alertFailure("An error has occured while handling your request.");
											} else {
												LayoutManager::alertFailure("Body needs to be atleast 10 and less than 4000 characters long.");
											}
										} else {
											LayoutManager::alertFailure("Header needs to be atleast 4 and less than 80 characters long.");
										}
									}
									// Gather the post
									$query = "SELECT * FROM ".$sql->prefix."AppAnnouncement WHERE app_id=".$_GET['id']." AND announcement_id=".$_GET['post'];
									$array = $sql->getArray($query);
									echo "<form action=\"?module=app&action=manage&id=".$_GET['id']."&f=announcement&s=edit&post=".$_GET['post']."\" method=\"post\">
										  <div class=\"form_settings\">
										  <p><span>Header</span><input type=\"text\" name=\"header\" value=\"".$array['header']."\"></p> 
										  <p><span>Body</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"body\">".$array['body']."</textarea></p>
										  <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>";
									echo "</div>";
									echo "</form>";
								}
							}
							break;
						case 'update':
							if($_GET['save'] == "true") {
								$changelog = strip_tags($_POST['changelog']);
								$version = strip_tags($_POST['version']);
								$platform = $_POST['platform'];
								if(strlen($changelog) > 9 && strlen($version) > 0 && strlen($version) < 10){
									// Check if a platform has been selected
									$query = sprintf("SELECT * FROM ".$sql->prefix."Platform WHERE platform_id <> 0 AND platform_id='%s'",
											mysql_real_escape_string($platform));
									$check_plat = $sql->getCount($query);
											
									if(eregi("^[0-9]{1,11}$", $platform) && $check_plat>0){
										// Everything is good, let's upload the file
										$file = FileHandler::uploadAppFile($_FILES['file']);
										if($file['success']){
											$filename = $file['name'];
											if($_POST['public'])
												$public = 1;
											else
												$public = 0;
											$query = sprintf("INSERT INTO `".$sql->prefix."ApplicationUpdates` (`url`,`revision_nr`, `platform`, `changelog`, `public`, `app_id`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s')",
												mysql_real_escape_string($filename),
												mysql_real_escape_string($version),
												mysql_real_escape_string($platform),
												mysql_real_escape_string($changelog),
												mysql_real_escape_string($public),
												mysql_real_escape_string($app->getId()));

											$result = $sql->doUpdate($query);
											if(!$result){
												echo Error::printError("Mysql error. ".$sql->getError());
											} else 
												LayoutManager::alertSuccess("Success! Update released.");
										} else {
											LayoutManager::alertFailure("Error while uploading file. ".$file['error']);
										}
									} else {
										LayoutManager::alertFailure("Not a valid platform.");
									}
								} else 
									LayoutManager::alertFailure("Versionnumber needs to be between 1 and 9 characters long.
									Changelog needs to contain atleast 10 characters.");
							}
							echo "<br>Max Filesize: ".FileHandler::maxSize();
							echo "<form enctype=\"multipart/form-data\" action=\"?module=app&action=manage&id=".$_GET['id']."&f=update&save=true\" method=\"post\">
							  <div class=\"form_settings\">
							  	<p><span>Version</span><input id=\"version\" class=\"contact\" type=\"text\" name=\"version\" value=\"\" /></p>
							    <p><span>Changelog</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"changelog\"></textarea></p>
							    <p><span>Platform</span><select name=\"platform\">";
							    $platforms = $sql->getFullArray("SELECT platform_id, name FROM ".$sql->prefix."Platform WHERE platform_id <> 0");
							    for ($i = 0; $i < count($platforms); $i++) {
							    	echo "<option value=\"".$platforms[$i]['platform_id']."\">".$platforms[$i]['name']."</option>\n";
							    }
							    echo "</select></p>
							    <p><span>Public</span><input id=\"public\" class=\"contact\" type=\"checkbox\" name=\"public\" value=\"true\" /></p>
							    <p><span>File</span><input id=\"file\" class=\"contact\" type=\"file\" name=\"file\" /></p>
							    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
							  </div>
							</form>";
							break;
						case 'group':
							// This case is for managing groups connected to the applications
							switch ($_GET['p']){
								case 'delete':
									if(eregi("^[0-9]{1,11}$", $_GET['gid'])){
										$query = sprintf("SELECT role FROM ".$sql->prefix."UserInGroup WHERE group_id='%s' AND user_id='%s'",
											mysql_real_escape_string($_GET['gid']),
											mysql_real_escape_string($user->getId()));
										$ar = $sql->getArray($query);
										if($ar['role'] == "Owner"){
											$q1 = "DELETE FROM `".$sql->prefix."Group` WHERE group_id='".$_GET['gid']."'";
											$q2 = "DELETE FROM `".$sql->prefix."UserInGroup` WHERE group_id='".$_GET['gid']."'";
											$q3 = "DELETE FROM `".$sql->prefix."GroupApplication` WHERE group_id='".$_GET['gid']."'";
											$result = $sql->doUpdate($q1);
											$result1 = $sql->doUpdate($q2);
											$result2 = $sql->doUpdate($q3);
											if(!$result && !$result1 && !$result2){
												echo Error::printError("Unexpected error.");
											} else {
												echo "<a href=\"?module=app&action=manage&id=".$_GET['id']."&f=group\">Click here to continue.</a>";
												echo "<script type=\"text/javascript\">
												<!--
												window.location = \"?module=app&action=manage&id=".$_GET['id']."&f=group\"
												//-->
												</script>";
											}
										} else {
											echo Error::printError("You're not the owner of this group.");
										}
									}
									break;
								case 'create':
									if($_GET['s'] != "true"){
								       echo "<form id=\"regGroupForm\" action=\"?module=app&action=manage&id=".$_GET['id']."&f=group&p=create&s=true\" method=\"post\">
										  <div class=\"form_settings\">
										    <p><span>Name</span><input id=\"name\" class=\"contact\" type=\"text\" name=\"name\" value=\"\" /></p>
										    <p><span>Description</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"desc\"></textarea></p>
										    <p><span>Public</span><input id=\"public\" class=\"contact\" type=\"checkbox\" name=\"public\" value=\"true\" /></p>
										    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"submit\" /></p>
										  </div>
										</form>";
									} else {
										$name = stripslashes($_POST['name']);
										$desc = stripslashes($_POST['desc']);
										$public = stripslashes($_POST['public']);
										if($public == "true")
											$public = 1;
										else 
											$public = 0;
											
										if(strlen($name) > 4){
											$query = sprintf("INSERT INTO `".$sql->prefix."Group` (`name`, `desc`, `public`) VALUES ('%s', '%s', '%s')",
												mysql_real_escape_string($name),
												mysql_real_escape_string($desc),
												mysql_real_escape_string($public));
												
											$result = $sql->doUpdate($query);
											$group_id = mysql_insert_id();
												if($result){
													$query = sprintf("INSERT INTO `".$sql->prefix."GroupApplication` (app_id, group_id) VALUES ('%s', '%s')",
														mysql_real_escape_string($_GET['id']),
														mysql_real_escape_string($group_id));
													$result = $sql->doUpdate($query);
													if($result){
														$query = sprintf("INSERT INTO `".$sql->prefix."UserInGroup` (user_id, group_id, role) VALUES ('%s', '%s', 'Owner')",
														mysql_real_escape_string($user->getId()),
														mysql_real_escape_string($group_id));
														$result = $sql->doUpdate($query);
														if(!$result){
															$q1 = "DELETE FROM `".$sql->prefix."Group` WHERE group_id='".$group_id."'";
															$sql->doUpdate($q1);
															echo Error::printError("Unexpected error.");
														} else {
															LayoutManager::redirect("?module=app&action=manage&id=".$_GET['id']."&f=group&p=manage&gid=".$group_id);
														}
													} else {
														$q1 = "DELETE FROM `".$sql->prefix."Group` WHERE group_id='".$group_id."'";
														$sql->doUpdate($q1);
														echo Error::printError("Unexpected error.");
													}
												} else 
													LayoutManager::alertFailure("Unexpected error. Please try again later.");
										} else {
											LayoutManager::alertFailure("Group name must be at least 5 characters long.");
										}
										echo "<form id=\"regGroupForm\" action=\"?module=app&action=manage&id=".$_GET['id']."&f=group&p=create&s=true\" method=\"post\">
										  <div class=\"form_settings\">
										    <p><span>Name</span><input id=\"name\" class=\"contact\" type=\"text\" name=\"name\" value=\"\" /></p>
										    <p><span>Description</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"desc\"></textarea></p>
										    <p><span>Public</span><input id=\"public\" class=\"contact\" type=\"checkbox\" name=\"public\" value=\"true\" /></p>
										    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"submit\" /></p>
										  </div>
										</form>";
									}
									break;
								default:
									echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
									$app_groups = $app->getGroups();
									for ($i = 0; $i < count($app_groups); $i++) {
										echo "<tr><td><a href=\"?module=group&id=".$app_groups[$i]->getId()."\">".$app_groups[$i]->getName()."</a></td></tr>";
									}
									echo "</table>";
									echo "[<a href=\"?module=app&action=manage&id=".$_GET['id']."&f=group&p=create\">Create</a>]";
								break;		
							}
							break;
						default:
							$installed_total = $sql->getCount("SELECT user_device_id FROM ".$sql->prefix."UserDeviceInstalledApps WHERE app_id=".$_GET['id']."");
							$uninstalled_total = $sql->getCount("SELECT user_device_id FROM ".$sql->prefix."UserDeviceInstalledApps WHERE app_id=".$_GET['id']." AND removed IS NOT NULL");
							$query = "SELECT U.app_id, P.name, COUNT(U.app_id) AS number,P.platform_id FROM `".$sql->prefix."UserDeviceInstalledApps` U, ".$sql->prefix."Platform P, ".$sql->prefix."UserDevice D WHERE U.user_device_id = D.user_device_id AND U.app_id=".$_GET['id']." AND D.platform = P.platform_id GROUP BY D.platform";
							$result = $sql->getFullArray($query);
							if($installed_total > 0){
								$uninstalled_prosent = round(($uninstalled_total/$installed_total)*100, 2, PHP_ROUND_HALF_UP);
							} else {
								$uninstalled_prosent = 0;
							}
							echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
							echo "<tr><td>Total installs</td><td>".$installed_total."</td></tr>\n";
							echo "<tr><td>Total uninstall rate</td><td>".$uninstalled_prosent."%</td></tr>\n";
							for($i=0; $i<$sql->getCount($query); $i++){							
								$count = $sql->getCount("SELECT * FROM `".$sql->prefix."UserDeviceInstalledApps` U, ".$sql->prefix."Platform P, ".$sql->prefix."UserDevice D WHERE U.user_device_id = D.user_device_id AND U.app_id=".$result[$i]['app_id']." AND D.platform = P.platform_id AND P.platform_id=".$result[$i]['platform_id']." AND removed IS NOT NULL");
								echo "<tr><td>".$result[$i]['name']."</td><td>".$result[$i]['number']."(".round((($result[$i]['number'] / $installed_total) * 100), 2, PHP_ROUND_HALF_UP)."%) - ".round((($count / $result[$i]['number']) * 100), 2, PHP_ROUND_HALF_UP)."% uninstalls</td></tr>\n";	
							}
							echo "</table>";
							if($installed_total > 0){
								$query = "SELECT P.name, COUNT( U.app_id ) AS number, D.os_version FROM 
									`".$sql->prefix."UserDeviceInstalledApps` U, ".$sql->prefix."Platform P, ".$sql->prefix."UserDevice D
									WHERE U.user_device_id = D.user_device_id
									AND U.app_id = ".$_GET['id']."
									AND D.platform = P.platform_id
									GROUP BY D.os_version";
								$ar = $sql->getFullArray($query);
								
								$label = array();
								$stats = array();
								for ($i = 0; $i < count($ar); $i++) {
									$label[] = $ar[$i]['name']." ".$ar[$i]['os_version'];
									$stats[] = $ar[$i]['number'];
								}
								
								// build string
								$chart_url = "https://chart.googleapis.com/chart?cht=p&chs=800x300&chd=t:";
								for ($i = 0; $i < count($stats); $i++) {
									$chart_url .= $stats[$i];
									if($i < count($stats)-1){
										$chart_url .= ",";
									}
								}
								$chart_url .= "&chl=";
								for ($i = 0; $i < count($label); $i++) {
									$chart_url .= $label[$i];
									if($i < count($label)-1){
										$chart_url .= "|";
									}
								}
								$chart_url .= "&chdl=";
								for ($i = 0; $i < count($label); $i++) {
									$chart_url .= $label[$i];
									if($i < count($label)-1){
										$chart_url .= "|";
									}
								}
								
								echo "<div style=\"float: left;\"><img src=\"".$chart_url."\" width=800px height=300px></div>";
							} 
							break;
					}
				}
			}
		} else if($_GET['action'] == "create"){
			$desc = strip_tags($_POST['description']);
			$name = strip_tags($_POST['name']);
														
			$query = sprintf("SELECT app_id FROM `".$sql->prefix."Application` WHERE `name` LIKE '%s'",
					mysql_real_escape_string($name));
			$name_check = $sql->getCount($query);
			
			if(isset($_POST['description']) && isset($_POST['name']) && $name_check == 0){
				if(strlen($desc) > 9 && strlen($name) > 3 && strlen($name) < 46 && eregi("^[0-9]{1,11}$", $_POST['category'])){
					if($_POST['public'])
						$public = 1;
					else
						$public = 0;
					$query = sprintf("INSERT INTO `".$sql->prefix."Application` (`name`, `desc`, `public`) VALUES ('%s', '%s', '%s')",
								mysql_real_escape_string($name),
								mysql_real_escape_string($desc),
								mysql_real_escape_string($public));
					$result = $sql->doUpdate($query);
					$last_id = $sql->getLastId();
					if($result){
						$app_id = mysql_insert_id();
						$query = sprintf("INSERT INTO `".$sql->prefix."UserApplication` (`app_id`, `user_id`) VALUES ('%s', '%s')",
								mysql_real_escape_string($app_id),
								mysql_real_escape_string($user->getId()));
						$result = $sql->doUpdate($query);
						
						$query = sprintf("INSERT INTO `".$sql->prefix."ApplicationCategory` (`app_id`, `category_id`) VALUES ('%s', '%s')",
								mysql_real_escape_string($app_id),
								mysql_real_escape_string($_POST['category']));
						$result2 = $sql->doUpdate($query);
						if(!$result && !$result2)
							echo Error::printError("Mysql error.");
						else {
							echo "Success<br>";
							LayoutManager::redirect("?module=app&action=manage&id=".$last_id."&f=update");
						}
					} else {
						echo Error::printError("mysql error ". $sql->getError());
					}
				} else {
					LayoutManager::alertFailure("Description needs to be atleast 10 characters.
					Name needs to be between 4 and 45 characters.");
				}
			}
			if($name_check > 0 && $name != "")
				LayoutManager::alertFailure("Name already in use.");
			// Post create form when submit redirect to the manage page of that application
	       echo "<form action=\"#\" method=\"post\" AUTOCOMPLETE=\"off\">
			  <div class=\"form_settings\" style=\"position: relative;\">
			    <p><span>Name</span><input class=\"contact\" type=\"text\" id=\"user-text\" name=\"name\" value=\"".$name."\"/></p>
			    <p><span>Category</span><select class=\"contact\" name=\"category\">";
			    $category = $sql->getFullArray("SELECT category_id, name FROM ".$sql->prefix."Category");
			    for ($i = 0; $i < count($category); $i++) {
			    	echo "<option value=\"".$category[$i]['category_id']."\">".$category[$i]['name']."</option>\n";
			    }
			    echo "</select></p>
			    <p><span>Description</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"description\">".$desc."</textarea></p>
			    <p><span>Public</span><input id=\"public\" class=\"contact\" type=\"checkbox\" name=\"public\" value=\"true\" /></p>
			    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
			  </div>
			</form>";
		}
	} else {
		// Posting loginform
		LayoutManager::printLoginForm();
	}
?>