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
		if($_GET['action'] != "view"){
			$max_msg = 20;
			$out_offset = 0;
			$in_offset = 0;

			if($_GET['b'] == "in" && eregi("^[0-9]{1,11}$", $_GET['offset'])){
				$in_offset = $_GET['offset'];
			}
			if($_GET['b'] == "out" && eregi("^[0-9]{1,11}$", $_GET['offset'])){
				$out_offset = $_GET['offset'];
			}

			echo "
			<ul class=\"tabs\">
			    <li><a href=\"#tab1\">Inbox</a></li>
			    <li><a href=\"#tab2\">Outbox</a></li>
			    <li><a href=\"#tab3\">Compose</a></li>
			</ul>
			
			<div class=\"tab_container\">
			    <div id=\"tab1\" class=\"tab_content\">
			        <!--Content-->";
					if($_GET['action'] == "delete"){
						if(eregi("^[0-9]{1,11}$", $_GET['id'])){
							// Check if this user is the receiver of the message
							// if so delete it
							if($sql->getCount("SELECT msg_id FROM ".$sql->prefix."UserMsg WHERE msg_id=".$_GET['id']." AND to_u=".$user->getId()) == 1){
								$query = "UPDATE ".$sql->prefix."UserMsg SET unread=0, deleted=1 WHERE msg_id=".$_GET['id'];
								$result = $sql->doUpdate($query);
								if(!$result)
									echo Error::printError("Unexpected error.");
							}
						} else {
							// check if the checkboxes have been ticked off.
							if($_POST['delete']){
								$deleteSet = "";
								for ($i = 0; $i < count($_POST["checkbox"]); $i++){
									if($i == 0){
										$deleteSet = $_POST["checkbox"][$i];
									} else {
								   		$deleteSet = $deleteSet.",".$_POST["checkbox"][$i];
									}
								}

								if($sql->getCount("SELECT msg_id FROM ".$sql->prefix."UserMsg WHERE msg_id IN (".$deleteSet.") AND to_u=".$user->getId()) == count($_POST["checkbox"]) && count($_POST["checkbox"]) > 0){
									$query = "UPDATE ".$sql->prefix."UserMsg SET unread=0, deleted=1 WHERE msg_id IN (".$deleteSet.") AND to_u=".$user->getId();
									$result = $sql->doUpdate($query);
									if(!$result)
										echo Error::printError("Unexpected error.");
								}
							}
						}
					}
					echo "<script language=\"JavaScript\">
					function toggleCheckBox(source) {
						checkboxes = document.getElementsByName('checkbox');
						for each(var checkbox in checkboxes)
							checkbox.checked = source.checked;
					}
					</script>";
					echo "<form name=\"deleteMsg\" method=\"post\" action=\"?module=msg&action=delete\">
					<div style=\"float: right;\"><input class=\"change\" type=\"submit\" name=\"delete\" value=\"Delete\"/></div>";
					echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>
							<tr><td><b>From</b></td><td><b>Subject</b></td><td><b>Sent</b></td><td width=\"50px\"><input type=\"checkbox\" id=\"selectall\" value=\"Select all\"></td></tr>";
					$query = "SELECT * FROM ".$sql->prefix."UserMsg WHERE to_u=".$user->getId()." AND deleted=0";
					$cnt_in = $sql->getCount($query);

					$query = "SELECT * FROM ".$sql->prefix."UserMsg WHERE to_u=".$user->getId()." AND deleted=0 ORDER BY sent DESC LIMIT $in_offset,$max_msg";
					$deviceArray = $sql->getFullArray($query);
					
					echo $sql->getError();
					
					for($i=0; $i<$sql->getCount($query); $i++){
						$u = new User();
						$u->getInfo($deviceArray[$i]["from_u"]);
						echo "<tr>
						<td>".$u->toString()."</td><td>";
						if($deviceArray[$i]["unread"])
							echo "<img src=\"images/mail.unread.png\" alt=\"Unread\" width=\"10px\" heigth=\"10px\" border=\"0\"> ";
						echo "<a href=\"?module=msg&action=view&id=".$deviceArray[$i]["msg_id"]."\">".$deviceArray[$i]["header"]."</a></td>
						<td>".$deviceArray[$i]["sent"]."</td>
						<td><input name=\"checkbox[]\" type=\"checkbox\" id=\"deleteBox\" value=".$deviceArray[$i]["msg_id"]."></td></tr>";		
					}
					echo "</form>";
					echo "</table>";
					if($cnt_in > $max_msg){
						if(($in_offset-$max_msg)>= 0){
							$prev = $in_offset-$max_msg;
							echo "<a href=\"?module=msg&offset=$prev&b=in#tab1\">Previous</a> ";
						}
						if($cnt_in > ($in_offset+$max_msg)){
							$next = $in_offset+$max_msg;
							echo "<a href=\"?module=msg&offset=$next&b=in#tab1\">Next</a>";
						}
					}
			    echo "</div>
			    <div id=\"tab2\" class=\"tab_content\">
			       <!--Content-->";
			       	echo "<table cellspacing=0 cellpadding=0 border=0 width=100%>
							<tr><td><b>To</b></td><td><b>Subject</b></td><td><b>Sent</b></td></tr>";
					$query = "SELECT * FROM ".$sql->prefix."UserMsg WHERE from_u=".$user->getId();
					$cnt_out = $sql->getCount($query);

					$query = "SELECT * FROM ".$sql->prefix."UserMsg WHERE from_u=".$user->getId()." ORDER BY sent DESC LIMIT $out_offset,$max_msg";
					$deviceArray = $sql->getFullArray($query);
					for($i=0; $i<$sql->getCount($query); $i++){
						$u = new User();
						$u->getInfo($deviceArray[$i]["to_u"]);
						echo "<tr><td>".$u->toString()."</td>
						<td><a href=\"?module=msg&action=view&id=".$deviceArray[$i]["msg_id"]."\">".$deviceArray[$i]["header"]."</a></td>
						<td>".$deviceArray[$i]["sent"]."</td></tr>";		
					}
					echo "</table>";
					if($cnt_out > $max_msg){
						if(($out_offset-$max_msg)>= 0){
							$prev = $out_offset-$max_msg;
							echo "<a href=\"?module=msg&b=out&offset=$prev#tab2\">Previous</a> ";
						}
						if($cnt_out > ($out_offset+$max_msg)){
							$next = $out_offset+$max_msg;
							echo "<a href=\"?module=msg&b=out&offset=$next#tab2\">Next</a>";
						}
					}

			    echo "</div>
			    <div id=\"tab3\" class=\"tab_content\">
			       <!--Content-->";
			       if($_GET['action'] == "send"){
			       		$usera = stripslashes($_POST['msg_to']);
						// Check if the user excists
						$user_check_query = "SELECT user_id FROM ".$sql->prefix."User WHERE email='".$usera."' AND activate='NULL'";
						if($sql->getCount($user_check_query) == 1){
							$get_usr = $sql->getArray($user_check_query);
							$user_id = $get_usr['user_id'];
							
							$message = str_replace("<3", ":heart:", $_POST['your_message']);
							$message = str_replace(":>", ":smilie:", $message);
							$message = strip_tags($message);
							$message = stripslashes($message);
							$message = str_replace(":heart:", "<3", $message);
							$message = str_replace(":smilie:", ":>", $message);
							
							$subject = str_replace("<3", ":heart:", $_POST['subject']);
							$subject = str_replace(":>", ":smilie:", $subject);
							$subject = strip_tags($subject);
							$subject = stripslashes($subject);
							$subject = str_replace(":heart:", "<3", $subject);
							$subject = str_replace(":smilie:", ":>", $subject);
							// "Send" the message
							if(strlen($message) > 10 && strlen($subject) > 3){
								$query = "INSERT INTO ".$sql->prefix."UserMsg (to_u, from_u, header, msg) VALUES ('".$user_id."','".$user->getId()."','$subject','$message')";
	
								if(!$sql->doUpdate($query)){
									LayoutManager::alertFailure("Unexpected error. Please try again later.");
								} else {
									LayoutManager::alertSuccess("Message sent!");
								}
							} else {
								LayoutManager::alertFailure("Header needs to be at least 5 characters long.<br>Message needs to be at least 11 characters long.");
							}
						}	
			       }
			       $to_user = "";
			       if(isset($_POST['mail']))
			       	$to_user = $_POST['mail'];
			       echo "<form action=\"?module=msg&action=send#tab3\" method=\"post\" AUTOCOMPLETE=\"off\">
					  <div class=\"form_settings\" style=\"position: relative;\">
					    <p><span>To</span><input class=\"contact\" type=\"text\" id=\"user-text\" name=\"msg_to\" value=\"$to_user\" onkeyup=\"suggest(this.value);\" onblur=\"fill();\"/>
					    <div class=\"suggestionsBox\" id=\"suggestions\" style=\"display: none;\">
								<div class=\"suggestionList\" id=\"suggestionsList\"> &nbsp; </div>
							</div></p>
						<p><span>Subject</span><input class=\"contact\" type=\"text\" name=\"subject\" value=\"\"/></p>
					    <p><span>Message</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"your_message\"></textarea></p>
					    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"submit\" /></p>
					  </div>
					</form>
			    </div>
			</div>";
		} else {
			if(eregi("^[0-9]{1,11}$", $_GET['id'])){
				if($sql->getCount("SELECT * FROM ".$sql->prefix."UserMsg WHERE msg_id=".$_GET['id']." AND to_u=".$user->getId())>0){
					$query = "SELECT * FROM ".$sql->prefix."UserMsg WHERE msg_id=".$_GET['id']." AND to_u=".$user->getId();
					$ar = $sql->getArray($query);
					$query = "SELECT * FROM ".$sql->prefix."User WHERE user_id=".$ar['from_u'];
					$ar1 = $sql->getArray($query);
					$query = "UPDATE ".$sql->prefix."UserMsg SET unread=0 WHERE msg_id=".$_GET['id']." AND to_u=".$user->getId();
	
					if(!$sql->doUpdate($query)){
						echo Error::printError("Unexpected error. Please try again later.");
					}
					echo "<div class=\"form_settings\" style=\"position: relative;\">
					    <h2>".$ar['header']." - ".$ar1['name']."</h2>
					    <p>".nl2br (strip_tags ($ar['msg'], '<a><b><i><u><img>'))."</p>
					    <p>[<a href=\"?module=msg&action=delete&id=".$_GET['id']."\">Delete</a>]</p>
					  </div>";
					 echo "<form action=\"?module=msg&action=send#tab3\" method=\"post\" AUTOCOMPLETE=\"off\">
						  <div class=\"form_settings\" style=\"position: relative;\">
						    <input class=\"contact\" type=\"hidden\" id=\"user-text\" name=\"msg_to\" value=\"".$ar1['email']."\"/>
							<input class=\"contact\" type=\"hidden\" name=\"subject\" value=\"RE: ".$ar['header']."\"/>
						    <p><span>Message</span><textarea class=\"contact textarea\" rows=\"8\" cols=\"50\" name=\"your_message\"></textarea></p>
						    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Reply\" /></p>
						  </div>
						</form>
				    </div>";
				} else {
					$query = "SELECT * FROM ".$sql->prefix."UserMsg WHERE msg_id=".$_GET['id']." AND from_u=".$user->getId();
					$ar = $sql->getArray($query);
					$query = "SELECT * FROM ".$sql->prefix."User WHERE user_id=".$ar['from_u'];
					$ar1 = $sql->getArray($query);

					echo "<div class=\"form_settings\" style=\"position: relative;\">
					    <h2>".$ar['header']." - ".$ar1['name']."</h2>
					    <p>".nl2br (strip_tags ($ar['msg']))."</p>
					  </div>";
				}
			}
		}
	} else {
		// Posting loginform
		LayoutManager::printLoginForm();
	}
?>