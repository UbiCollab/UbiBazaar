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
		if($_GET['action'] == "view"){
			if(eregi("^[0-9]{1,11}$", $_GET['id'])){
				$query = sprintf("SELECT * FROM ".$sql->prefix."AppAnnouncement WHERE announcement_id='%s'",
					mysql_real_escape_string($_GET['id']));
				$news = $sql->getArray($query);
				// Show the announcement
				echo "<div class=container style=\"border: 1px solid #999; padding: 5px; margin-top: 20px; overflow: auto;\">";
				echo "<div style=\"float: right;\">";
				$poster = new User();
				$app = new App();
				$app->getBasicInfo($news['app_id']);
				$poster->getInfo($news['user_id']);
				echo "<img src=\"images/forum/viewpost.gif\"> Posted ".strftime ("%a %b %d, %Y %H:%M", strtotime($news['time']))." by ".$poster->toString().".";
				echo "</div>";
				echo "<h2>".$news['header']." - ".$app->getName()."</h2><br>";
				echo "<p>".nl2br ($news['body'])."</p>";
				// Show comments and form to post comment
			} else {
				// Not a valid id so the user is redirected back.
				LayoutManager::redirect("./");
			}
		} else {
		echo "	
		<ul class=\"tabs\">
		    <li><a href=\"#tab1\">Newsfeed</a></li>
		    <li><a href=\"#tab2\">Recommended apps</a></li>
		</ul>
		
		<div class=\"tab_container\">
		    <div id=\"tab1\" class=\"tab_content\">
		        <!--Content-->";

				$history = array();
				$app = new App();
				// Gathering applications and 
				$array = $sql->getFullArray("SELECT app_id FROM ".$sql->prefix."UserApplication WHERE user_id IN 
								(SELECT following FROM ".$sql->prefix."UserFollowUser WHERE follower=".$user->getId().") 
								AND app_id IN (SELECT app_id FROM ".$sql->prefix."ApplicationUpdates WHERE public=1) 
								AND app_id NOT IN (SELECT app_id FROM ".$sql->prefix."UserDeviceInstalledApps WHERE user_device_id IN 
								(SELECT user_device_id FROM ".$sql->prefix."UserDevice WHERE user_id =".$user->getId()."))");
				for($i=0; $i<count($array); $i++){
					$ar = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$array[$i]['app_id']." AND public=1");
					// Fetch all the platform it's been released on.
					for($j=0; $j<count($ar); $j++){
						$ar1 = $sql->getArray("SELECT * FROM ".$sql->prefix."Application WHERE app_id=".$array[$i]['app_id']." AND public=1");
						if(!file_exists("mobile/files/icon/".$array[$i]['app_id'].".png"))
							$output['img'] = "mobile/files/icon/0.png";
						else
							$output['img'] = "mobile/files/icon/".$array[$i]['app_id'].".png";
						$output['msg'] = "A new update of '<a href=\"?module=app&action=view&platform=".$ar[$j]['platform']."&id=".$array[$i]['app_id']."\">".$ar1['name']."</a>' has just been released for <img src=\"images/platform/".$ar[$j]['platform'].".png\" border=0 width=12 height=12>.";
						$output['time'] = $ar[$j]['released'];
						
						if (!in_array($output,$history)) 
						{ 
							$time[] = strtotime($ar[$j]['released']);
							$history[] = $output;
						}		
					}
					// Gathering news
					$news = $sql->getFullArray("SELECT * FROM ".$sql->prefix."AppAnnouncement WHERE app_id=".$array[$i]['app_id']);
					
					foreach ($news as $post) {
						// Gather app name
						$app->getBasicInfo($post['app_id']);
						if(!file_exists("mobile/files/icon/".$array[$i]['app_id'].".png"))
							$output['img'] = "mobile/files/icon/0.png";
						else
							$output['img'] = "mobile/files/icon/".$array[$i]['app_id'].".png";
						$output['msg'] = "Announcement from ".$app->getName().": <a href=\"?action=view&id=".$post['announcement_id']."\">".$post['header']."</a>";
						$output['time'] = $post['time'];
						
						if (!in_array($output,$history)){
							$time[] = strtotime($post['time']);
							$history[] = $output;
						}
					}
				}
				
				$array = $sql->getFullArray("SELECT app_id FROM ".$sql->prefix."UserFollowApplication WHERE follower=".$user->getId()." AND app_id IN 
				(SELECT app_id FROM ".$sql->prefix."ApplicationUpdates WHERE public=1) AND app_id NOT IN 
				(SELECT app_id FROM ".$sql->prefix."UserDeviceInstalledApps WHERE user_device_id IN 
				(SELECT user_device_id FROM ".$sql->prefix."UserDevice WHERE user_id =".$user->getId().")) 
				AND app_id NOT IN (SELECT app_id FROM ".$sql->prefix."UserApplication WHERE user_id IN 
				(SELECT following FROM ".$sql->prefix."UserFollowUser WHERE follower=".$user->getId()."))");
				for($i=0; $i<count($array); $i++){
					$ar = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$array[$i]['app_id']." AND public=1");
					// Fetch all the platform it's been released on.
					for($j=0; $j<count($ar); $j++){
						$ar1 = $sql->getArray("SELECT name FROM ".$sql->prefix."Application WHERE app_id=".$array[$i]['app_id']." AND public=1");
						if(!file_exists("mobile/files/icon/".$array[$i]['app_id'].".png"))
							$output['img'] = "mobile/files/icon/0.png";
						else
							$output['img'] = "mobile/files/icon/".$array[$i]['app_id'].".png";
						$output['msg'] = "A new update of '<a href=\"?module=app&action=view&platform=".$ar[$j]['platform']."&id=".$array[$i]['app_id']."\">".$ar1['name']."</a>' has just been released for <img src=\"images/platform/".$ar[$j]['platform'].".png\" border=0 width=12 height=12>.";
						$output['time'] = $ar[$j]['released'];
						
						if (!in_array($output,$history)) 
						{ 
							$time[] = strtotime($ar[$j]['released']);
							$history[] = $output;
						}	
					}
					
					$news = $sql->getFullArray("SELECT * FROM ".$sql->prefix."AppAnnouncement WHERE app_id=".$array[$i]['app_id']);
					foreach ($news as $post) {
						$app->getBasicInfo($post['app_id']);
						if(!file_exists("mobile/files/icon/".$array[$i]['app_id'].".png"))
							$output['img'] = "mobile/files/icon/0.png";
						else
							$output['img'] = "mobile/files/icon/".$array[$i]['app_id'].".png";
						$output['msg'] = "Announcement from ".$app->getName()."<br><a href=\"?action=view&id=".$post['announcement_id']."\">".$post['header']."</a>";
						$output['time'] = $post['time'];
						
						if (!in_array($output,$history)){
							
							$time[] = strtotime($post['time']);
							$history[] = $output;
						}
					}
				}
				$array = $sql->getFullArray("SELECT DISTINCT app_id FROM ".$sql->prefix."UserDeviceInstalledApps WHERE user_device_id IN (SELECT user_device_id FROM ".$sql->prefix."UserDevice WHERE user_id=".$user->getId().")");
				for($i=0; $i<count($array); $i++){
					$ar = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationUpdates WHERE app_id=".$array[$i]['app_id']." AND public=1");
					// Fetch all the platform it's been released on.
					for($j=0; $j<count($ar); $j++){
						$ar1 = $sql->getArray("SELECT name FROM ".$sql->prefix."Application WHERE app_id=".$array[$i]['app_id']." AND public=1");
						if(!file_exists("mobile/files/icon/".$array[$i]['app_id'].".png"))
							$output['img'] = "mobile/files/icon/0.png";
						else
							$output['img'] = "mobile/files/icon/".$array[$i]['app_id'].".png";
							
						$output['msg'] = "A new update of '<a href=\"?module=app&action=view&platform=".$ar[$j]['platform']."&id=".$array[$i]['app_id']."\">".$ar1['name']."</a>' has just been released for <img src=\"images/platform/".$ar[$j]['platform'].".png\" border=0 width=12 height=12>.";
						$output['time'] = $ar[$j]['released'];
						
						if (!in_array($output,$history))  
						{ 
							$time[] = strtotime($ar[$j]['released']);
							$history[] = $output;
						}	
					}
					
					$news = $sql->getFullArray("SELECT * FROM ".$sql->prefix."AppAnnouncement WHERE app_id=".$array[$i]['app_id']);
					foreach ($news as $post) {
						$app->getBasicInfo($post['app_id']);
						if(!file_exists("mobile/files/icon/".$array[$i]['app_id'].".png"))
							$output['img'] = "mobile/files/icon/0.png";
						else
							$output['img'] = "mobile/files/icon/".$array[$i]['app_id'].".png";
						$output['msg'] = "Announcement from ".$app->getName()."<br><a href=\"?action=view&id=".$post['announcement_id']."\">".$post['header']."</a>";
						$output['time'] = $post['time'];
						
						if (!in_array($output,$history)){
							
							$time[] = strtotime($post['time']);
							$history[] = $output;
						}
					}
				}
				
				$array = $sql->getFullArray("SELECT * FROM ".$sql->prefix."UserInGroup WHERE user_id IN (SELECT following FROM ".$sql->prefix."UserFollowUser WHERE follower=".$user->getId().") AND role='Owner' AND group_id IN (SELECT group_id FROM `".$sql->prefix."Group` WHERE public=1)");
				for($i=0; $i<count($array); $i++){
					$ar = $sql->getArray("SELECT name FROM `".$sql->prefix."Group` WHERE group_id=".$array[$i]['group_id']);
					$ar1 = $sql->getArray("SELECT name, picture FROM ".$sql->prefix."User WHERE user_id=".$array[$i]['user_id']);
					$output['img'] = "images/user/".$ar1['picture'];
					$output['msg'] = "<a href=\"?module=user&action=view&id=".$array[$i]['user_id']."\">".$ar1['name']."</a> created the group '<a href=\"?module=group&id=".$array[$i]['group_id']."\">".$ar['name']."</a>'.";
					$output['time'] = $array[$i]['joined'];
					$time[] = strtotime($array[$i]['joined']);
					$history[] = $output;
				}
				
				if(count($time)>0){
					// Sort the information gathered
					array_multisort($time, SORT_DESC, $history);
	
					// How many to list
					if(count($history) > 20){
						$list_items = 20;
					} else {
						$list_items = count($history);
					}
					
					if($_GET['max'] > $list_items && eregi("^[0-9]{1,11}$", $_GET['max'])){	
						$list_items = $_GET['max'];
						if($list_items > count($history))
							$list_items = count($history);
					}
					// Show the user his / her history on the site
					for($i=0;$i<$list_items; $i++){
						echo "<div class=\"container\" style=\"width: 100%; min-height: 40px; border-bottom: 2px solid #CCC; padding-bottom: 2px; padding-top: 2px;\">
						<div style=\"float: left; margin-right: 5px;\"><img src=\"".$history[$i]['img']."\" width=40 height=40></div>
						<div style=\"float: right;\">".strftime ("%a %b %d, %Y %H:%M", strtotime($history[$i]['time']))."</div>".$history[$i]['msg']."</div>";
					}
					if(count($history) > $list_items){
						echo "<div class=\"container\" style=\"width: 100%; height: 36px; padding-top: 10px; background: #F5F5EC;\">
						<center><a href=\"?max=".($list_items+20)."\">More?</a></center>
						</div>";
					}
				} else {
					echo "<div class=\"container\">Nothing to report. Are you following any people or applications?</div>";
				}
		    echo "</div>
		    <div id=\"tab2\" class=\"tab_content\">
		       <!--Content-->";
		    	$apps = array();

		    	$array2 = $sql->getFullArray("SELECT app_id, COUNT( user_id ) AS number FROM `".$sql->prefix."ApplicationFeedback` WHERE recommend=1 GROUP BY app_id ORDER BY number DESC LIMIT 10");
			    for ($i = 0; $i < count($array2); $i++) {
		    		$apps[$array2[$i]['app_id']] = $array2[$i]['number'];
		    	}
		    	$a = new App();
   				echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>";
				echo "<tr>
						<td><b>Name</b></td>
						<td><b>Last Update</b></td>
					</tr>";
				foreach ($apps as $key => $value) {
					$apps = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationView WHERE app_id=".$key);
					for ($i = 0; $i < count($apps); $i++) {
						$a->getInfoFromSQL($key, $apps[$i]['platform_id']);
						echo $a->printRow();
					}
				}
				echo "</table>";
		    echo "</div>
		</div>";
		}	
	} else {
		echo "<div class=\"container\"><h2>Welcome to UbiHome</h2>";
		
		$sql = new SQL();
		
		$query = "SELECT * FROM ".$sql->prefix."Application WHERE app_id IN (SELECT app_id FROM ".$sql->prefix."ApplicationScreenshots) AND public=1";
		$app_showcase_array = $sql->getFullArray($query);
		// Slideshow to showcase applications with screenshots. TODO
		if(count($app_showcase_array)>0){
//			echo "<ul id=\"slider\">";
//			foreach ($app_showcase_array as $app) {
//				$query = "SELECT url FROM ".$sql->prefix."ApplicationScreenshots WHERE app_id=".$app['app_id']." LIMIT 1";
//				$screenshot = $sql->getArray($query);
//				echo "<li>\n";
//				echo "<img src=\"mobile/files/ss/".$screenshot['url']."\" alt=\"\" />\n";
//				echo "<div class=\"caption-bottom\">
//   					".$app['desc']."
// 					</div>\n";
//				echo "</li>\n";
//			}
//			echo "</ul>";
		} else {
			echo "No applications to showcase here.";
		}
		echo "</div>";
	}
?>