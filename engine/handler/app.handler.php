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
	class App {
		
		private $name;
		private $public;
		private $desc;
		private $released;
		private $id;
		private $url;
		private $platform;
		private $platform_id;
		private $latestUpdate;
		private $latestChangelog;
		private $category;
		private $owner;
		private $dependencies = array();
		
		
		/**
		 * Default constructor. All variables is default = ""
		 * 
		 * @param string $name
		 * @param string $public
		 * @param string $desc
		 * @param timestamp $released
		 * @param int $id
		 * @param int $platform
		 */
		function __construct($name="", $public="", $desc="", $released="", $id="", $platform="") {
			$this->name = $name;
			$this->id = $id;
			$this->desc = $desc;
			$this->public = $public;
			$this->released = $released;
			$this->platform = $platform;
			$sql = new SQL();
					
			$query = "SELECT * 
				FROM ".$sql->prefix."ApplicationView
				WHERE `app_id`='".$id."'
				AND platform='".$this->platform."'";

			$db_array = $sql->getArray($query);
			$this->platform_id = $db_array['platform_id'];
		}
		
		public function getInfoFromSQL($id, $platform) {
			$sql = new SQL();
			$query = "SELECT A.`app_id` , A.`name` , A.`desc` , A.released, A.platform, A.category, A.platform_id, U.update_id, U.changelog, U.revision_nr, U.url
						FROM ".$sql->prefix."ApplicationView A,".$sql->prefix."ApplicationUpdates U 
						WHERE A.app_id = U.app_id
						AND A.update_id = U.update_id 
						AND U.platform = A.platform_id
						AND A.app_id = '".$id."'  
			      		AND A.platform_id = '".$platform."'
			      		GROUP BY A.app_id, A.platform LIMIT 1";


			$db_array = $sql->getArray($query);
		
			// Fetching the data from the array
			$this->id = $id;
			$this->platform_id = $db_array['platform_id'];
			$this->category = $db_array['category'];
			$this->name = $db_array['name'];
			$this->desc = nl2br (strip_tags ($db_array['desc'], '<a><b><i><u><img>'));
			$this->released = $db_array['released'];
			$this->platform = $db_array['platform'];
			$this->latestUpdate = $db_array['update_id'];
			$this->url = $db_array['url'];
			
			$db_array = $sql->getArray("SELECT * FROM ".$sql->prefix."ApplicationUpdates WHERE update_id='".$this->latestUpdate."' AND platform='".$platform."' AND public=1");
			$this->latestChangelog = nl2br (strip_tags ($db_array['changelog'], ''));

			$query = "SELECT * FROM ".$sql->prefix."UserApplication WHERE app_id=".$this->id;
			
			$db_array = $sql->getFullArray($query);
			for ($i = 0; $i < count($db_array); $i++) {
				$u = new User();
				$u->getInfo($db_array[$i]['user_id']);
				$this->owner[] = $u;
			}
		}
		
		public function getBasicInfo($id) {
			$sql = new SQL();
			$query = "SELECT * FROM ".$sql->prefix."Application WHERE app_id=".$id;
			$result = $sql->getArray($query);

			// Fetching the data from the array
			$this->id = $id;
			$this->name = $result['name'];
			$this->desc = nl2br (strip_tags ($result['desc'], '<a><b><i><u><img>'));
			$this->public = $result['public'];
			
			$query = "SELECT * FROM ".$sql->prefix."UserApplication WHERE app_id=".$this->id;
			
			$db_array = $sql->getFullArray($query);
			for ($i = 0; $i < count($db_array); $i++) {
				$u = new User();
				$u->getInfo($db_array[$i]['user_id']);
				$this->owner[] = $u;
			}
		}
		
		public function toString() {
			if($this->platform_id != "")
				return "<a href=\"?module=app&action=view&platform=$this->platform_id&id=$this->id\">".$this->name."</a>";
			else
				return $this->name;
		}
		
		public function printRow() {
			$img = "mobile/files/icon/".$this->getId().".png";
			if(!file_exists($img))
				$img = "mobile/files/icon/0.png";
			return "<tr>
				<td><img src=\"".$img."\" width=20 height=20> <a href=\"?module=app&action=view&platform=$this->platform_id&id=$this->id\">$this->name</a> <img src=\"images/platform/".$this->platform_id.".png\" border=0 width=12 height=12></td>
				<td>".strftime ("%a %b %d, %Y %H:%M",strtotime($this->released))."</td>
			</tr>";			 
		}
		
		public function printInfo() {
			echo "<div class=\"container\">";
			echo "<div style=\"float: right;\">";
			$user = LayoutManager::getUser();
			echo "<form action=\"?module=app&action=view&platform=".$this->platform_id."&id=".$this->id."&follow=true\" method=\"post\">";  			
			if($user->followingApp($this->id)){
				echo "<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Unfollow\" />";
			} else {
				echo "<input class=\"change\" type=\"submit\" name=\"contact_submitted\" value=\"Follow\" />";
			}
			echo "</form>";	
			echo "</div>";
			echo"<h2>".$this->name." <img src=\"images/platform/".$this->platform_id.".png\" border=0 width=16 height=16>";
			echo "</h2>";
			$sql = new SQL();
			$total = $sql->getCount("SELECT * FROM ".$sql->prefix."ApplicationFeedback WHERE app_id=".$this->id."");
			if($total > 0){
				$rec = $sql->getCount("SELECT * FROM ".$sql->prefix."ApplicationFeedback WHERE app_id=".$this->id." AND recommend=1");
				$not_rec = $total-$rec;
				$up_prs = ($rec/$total)*100;
				echo "<div style=\"float: right; width: 150px;\">";

				echo "<div style=\"float: left;\">".$rec." <img src=\"images/thumb-up.png\"></div><div style=\"float: right;\"><img src=\"images/thumb-down.png\"> ".$not_rec."</div><br>
				<div class=\"spark-bar\">
					<div class=\"spark-bar-up\" style=\"width: ".$up_prs."%;\"></div>
					<div class=\"spark-bar-down\" style=\"width: ".(100-$up_prs)."%;\"></div>
				</div>
				</div>";
			} else {
				echo "<div style=\"float: right; width: 150px;\">";
				echo "<div style=\"float: left;\">0 <img src=\"images/thumb-up.png\"></div><div style=\"float: right;\"><img src=\"images/thumb-down.png\"> 0</div><br>
				<div class=\"spark-bar\">
					<div class=\"spark-bar-up\" style=\"width: 50%;\"></div>
					<div class=\"spark-bar-down\" style=\"width: 50%;\"></div>
				</div>
				</div>";
			}
			echo "</div>";
			echo "<div class=\"container\">";
			echo "By ";
			for ($i = 0; $i < count($this->owner); $i++) {
				echo $this->owner[$i]->toString();
				if($i <= count($this->owner)-3)
					echo ", ";
				else if($i == count($this->owner)-2)
					echo " and ";
			}
			echo "</div><br>";

			$ar = $this->getScreenshots();
			if(count($ar) > 0){
				echo "<div class=\"container\" style=\"float: right; width: 174px;\">";
				echo "<div id=\"gallery\">
				<ul>";
				for ($i = 0; $i < count($ar); $i++) {
					echo "<li>
				            <a href=\"mobile/files/ss/".$ar[$i]['url']."\">
				                <img src=\"mobile/files/ss/".$ar[$i]['url']."\" width=\"72\" height=\"72\" alt=\"\" />
				            </a>
				        </li>";
				}
				echo "</ul></div>";
				echo "</div>";
			}
			echo "<div class=\"container\" style=\"width: 600px; overflow: auto;\">".$this->desc."<br><br></div>";
			echo "<div class=\"container\" style=\"width: 600px; overflow: auto;\"><B>What's new in this version</B><br>".$this->latestChangelog."</div>";
			if($this->platform_id != 0)
				echo "<div class=\"container\">".$this->google_qr(LayoutManager::curURL()."?module=app&action=view&platform=".$this->platform_id."&id=".$this->id)."</div>";
			else
				echo "<br><a href=\"".$this->url."\">Click to download</a><br>";
		}
		
		public function printManageRow() {
			$img = "mobile/files/icon/".$this->getId().".png";
			if(!file_exists($img))
				$img = "mobile/files/icon/0.png";
			return "<tr><td width=100%><img src=\"".$img."\" width=20 height=20> <a href=\"?module=app&action=manage&id=".$this->id."\">$this->name</a></td></tr>";
		}
		
		public function printDashboard() {
			echo "<div class=\"container\">
				<h2>".$this->name;
			echo "</h2>";
			echo "</div>";	
			echo "<div class=\"container\">
			[<a href=\"?module=app&action=manage&id=".$this->id."\">Info</a>]
			[<a href=\"?module=app&action=manage&id=".$this->id."&f=group\">Groups</a>]
			[<a href=\"?module=app&action=manage&id=".$this->id."&f=bugs\">Bugs</a>]
			[<a href=\"?module=app&action=manage&id=".$this->id."&f=screenshot\">Screenshot</a>]
			[<a href=\"?module=forum&id=".$this->id."\">Messageboard</a>]
			[<a href=\"?module=app&action=manage&id=".$this->id."&f=icon\">Icon</a>]
			[<a href=\"?module=app&action=manage&id=".$this->id."&f=dependencies\">Dependencies</a>]
			[<a href=\"?module=app&action=manage&id=".$this->id."&f=history\">Releases</a>]
			[<a href=\"?module=app&action=manage&id=".$this->id."&f=announcement\">Announcement</a>]
			[<a href=\"?module=app&action=manage&id=".$this->id."&f=update\">Update</a>]";
			// Show a release to public button.
			$sql = new SQL();
			if($sql->getCount("SELECT app_id FROM ".$sql->prefix."Application WHERE app_id=".$this->id." AND public=0") > 0 && $_GET['f'] != "privacy")
				echo "<div style=\"float: right;\">
				<font color=red>
				<b>[</b><a href=\"?module=app&action=manage&id=".$this->id."&f=privacy\">Release project</a><b>]</b>
				</font>
				</div>";
			echo "</div>"; 
		}
		
		/**
		 * Not in use atm.
		 * Enter description here ...
		 */
		public function getMobileObject() {
			$object["app_id"] = $this->id;
			$object["update_id"] = $this->latestUpdate;
			$object["name"] = $this->name;
			$object["desc"] = $this->desc;
			$object["released"] = $this->released;
			$object["url"] = $this->url;
			$object["platform"] = $this->platform;
			$object["changelog"] = $this->latestChangelog;
			return $object;
		}
		
		private function google_qr($url,$size ='130',$EC_level='M',$margin='0') {
			$url = urlencode($url); 
			echo '<img src="http://chart.apis.google.com/chart?chs='.$size.'x'.$size.'&cht=qr&chld='.$EC_level.'|'.$margin.'&chl='.$url.'" alt="QR code" width="'.$size.'" height="'.$size.'"/>';
		}
		
		public function getGroups() {
			$sql = new SQL();
			$query = "SELECT G.group_id FROM `".$sql->prefix."Group` G, ".$sql->prefix."GroupApplication A WHERE app_id='".$this->id."' AND G.group_id = A.group_id";
			$result = $sql->getFullArray($query);
			for ($i = 0; $i < count($result); $i++) {
				$group = new Group($result[$i]['group_id']);
				$output[] = $group;
			}
			return $output;
		}
				
		public function getId(){
			return $this->id;
		}
		
		public function getName(){
			return $this->name;
		}
		
		public function getOwner() {
			return $this->owner;
		}
		
		private function getTagArray() {
			$sql = new SQL();
			$query = "SELECT tag_id FROM `".$sql->prefix."ApplicationTag` WHERE app_id=".$this->id;
			$array = $sql->getFullArray($query);
			$output = array();
			for ($i = 0; $i < count($array); $i++) {
				$ar = $sql->getArray("SELECT * FROM `".$sql->prefix."Tag` WHERE tag_id=".$array[$i]['tag_id']);
				$output[$ar['tag']] = $sql->getCount("SELECT * FROM `".$sql->prefix."ApplicationTag` WHERE app_id=".$this->id." AND tag_id=".$ar['tag_id']);
			}
			return $output;
		}
		
		public function printTagCloud() {
			$tags = $this->getTagArray();
			// Default font sizes
			$min_font_size = 12;
			$max_font_size = 35;
			
			$cloud_html = '<i>None</i>';
			if(count($tags)>0){
				$minimum_count = min(array_values($tags));
				$maximum_count = max(array_values($tags));
				$spread = $maximum_count - $minimum_count;
				
				if($spread == 0) {
				    $spread = 1;
				}
				
				$cloud_tags = array(); // create an array to hold tag code
				foreach ($tags as $tag => $count) {
					$size = $min_font_size + ($count - $minimum_count) 
						* ($max_font_size - $min_font_size) / $spread;
					$cloud_tags[] = '<a style="font-size: '. floor($size) . 'px' 
						. '" class="tag_cloud" href="?module=app&action=view&platform='.$this->platform_id.'&id='.$this->id.'&tag=true&tags='.$tag.'#tab5' 
						. '" title="\'' . $tag  . '\' returned a count of ' . $count . '">' 
						. htmlspecialchars(stripslashes($tag)) . '</a>';
				}
				$cloud_html = join("\n", $cloud_tags) . "\n";
			}
			return $cloud_html;
		}
		
		public function compare(App $app) {
			if($this->id == $app->getId())
				return true;
			return false;
		}
		
		public function getDependencies() {
			$sql = new SQL();
			$query = "SELECT dependencies FROM ".$sql->prefix."Application WHERE app_id=".$this->id;
			$result = $sql->getArray($query);
			$this->dependencies = explode(",", $result['dependencies']);
			return $this->dependencies;
		}
		
		public function getScreenshots(){
			$sql = new SQL();
			$query = "SELECT * FROM ".$sql->prefix."ApplicationScreenshots WHERE app_id=".$_GET['id'];
			$ar = $sql->getFullArray($query);
			return $ar;
		}
		
		public function getAllFeedback() {
			$sql = new SQL();
			return $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationFeedback WHERE app_id=".$this->id." ORDER BY time DESC");
		}
		
		public function getShortFeedback(){
			$sql = new SQL();
			return $sql->getFullArray("SELECT * FROM ".$sql->prefix."ApplicationFeedback WHERE app_id=".$this->id." ORDER BY recommend DESC LIMIT 3");
		}
		
		public function addTag($tags){
			$user = LayoutManager::getUser();
			$sql = new SQL();
			for ($i = 0; $i < count($tags); $i++) {
				// Check if the tag allready excists
				$tag = ereg_replace("[^A-Za-z0-9 ]", "", $tags[$i]);
				if(strlen($tag) > 1 && strlen($tag) < 45){
					$query = "SELECT tag_id FROM ".$sql->prefix."Tag WHERE tag='".strtolower(trim($tag))."'";
					if($sql->getCount($query) > 0){
						$ar = $sql->getArray($query);
						$query = sprintf("INSERT INTO `".$sql->prefix."ApplicationTag` (`tag_id`,`app_id`, `user_id`) VALUES ('%s', '%s', '%s')",
								mysql_real_escape_string($ar['tag_id']),
								mysql_real_escape_string($this->id),
								mysql_real_escape_string($user->getId()));
						if(!$sql->doUpdate($query)){
							if($sql->getCount("SELECT tag_id FROM ".$sql->prefix."ApplicationTag WHERE tag_id='".$ar['tag_id']."' AND user_id='".$user->getId()."'")>0)
								LayoutManager::alertFailure("You have allready added this tag to this application.");
							else 
								echo Error::printError("Encountered an error while prosessing your request.");
						}
					} else {
						// insert the tag and link it to the application
						$query = sprintf("INSERT INTO `".$sql->prefix."Tag` (`tag`) VALUES ('%s')",
							mysql_real_escape_string(strtolower(trim($tag))));
						if($sql->doUpdate($query)){
							$query = sprintf("INSERT INTO `".$sql->prefix."ApplicationTag` (`tag_id`,`app_id`, `user_id`) VALUES ('%s', '%s', '%s')",
								mysql_real_escape_string($sql->getLastId()),
								mysql_real_escape_string($this->id),
								mysql_real_escape_string($user->getId()));
							if(!$sql->doUpdate($query)){
								echo Error::printError("Encountered an error while prosessing your request.");
							}
						} else {
							echo Error::printError("Encountered an error while prosessing your request.");
						}
					}
				}
			}
		}
	}
	