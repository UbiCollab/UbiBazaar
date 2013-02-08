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
		// User is admin
		// Declare function to write the settings file
		function makeFile($variables, $values) {
			include '../engine/values/site.values.php';
			
			$file1 = "../engine/values/site.values.php";
			$fz = fopen($file1, 'w');

			$site_title = str_replace('"', '\"', $site_title);
			$site_mail_subject = str_replace('"', '\"', $site_mail_subject);
			$site_mail_greet = str_replace('"', '\"', $site_mail_greet);
			
			// Handle variable changes
			// True / false variables makes life a bitch if you want the as strings qq
			if($site_reg_open && !in_array("site_reg_open", $variables))
				$site_reg_open = "true";
			else if(!$site_reg_open && !in_array("site_reg_open", $variables))
				$site_reg_open = "false";
			else
				$site_reg_open = $values[0];
			
			if(in_array("site_title", $variables)){
				$site_title = $values[0];
				$site_slogan = $values[1];
			}
				
			if(in_array("site_reg_activ", $variables)){
				$site_reg_activ = $values[0];
			}
			if(in_array("site_mail_subject", $variables) && in_array("site_mail_greet", $variables)){
				$site_mail_subject = $values[0];
				$site_mail_greet = $values[1];
			}
			
			if($fz != false){
				fwrite($fz,"<?php");
				fwrite($fz,"\n\t// Site variables\n");
				fwrite($fz, "\t$");
				fwrite($fz, "site_title = \"". $site_title ."\";\n");
				fwrite($fz, "\t$");
				fwrite($fz, "site_slogan = \"". $site_slogan ."\";\n");
				fwrite($fz, "\t$");
				fwrite($fz, "site_reg_open = ".$site_reg_open.";\n");
				fwrite($fz, "\t$");
				fwrite($fz, "site_reg_activ = \"".$site_reg_activ."\";\n");
				fwrite($fz,"\n\t// Email variables\n");
				fwrite($fz, "\t$");
				fwrite($fz, "site_mail_address = \"". $site_mail_address ."\";\n");
				fwrite($fz, "\t$");
				fwrite($fz, "site_mail_subject = \"". $site_mail_subject ."\";\n");
				fwrite($fz, "\t$");
				fwrite($fz, "site_mail_greet = \"". $site_mail_greet ."\";\n");
				fwrite($fz,"\n\t// Security variables\n");
				fwrite($fz, "\t$");
				fwrite($fz, "security_length = \"6\";\n");
				fwrite($fz, "\t$");
				fwrite($fz, "max_file_size = \"40000000\";\n");
				fwrite($fz, "\t$");
				fwrite($fz, "allowed_file_type = array('apk', 'exe', 'ipa');\n");
				fwrite($fz,"\n\t// Picture variables\n");
				fwrite($fz, "\t$");
				fwrite($fz, "picture_max_height = 600;\n");
				fwrite($fz, "\t$");
				fwrite($fz, "picture_max_width = 800;\n");
				fwrite($fz,"?>");
				fclose($fz);
			}
			// Reload this page so that you can see the changes
			LayoutManager::redirect("?module=settings");
		}
		
		include '../engine/values/site.values.php';
		
		// Handlers
		switch ($_GET['action']){
			case 'email':
				$variables = array();
				$values = array();
				$sub = $_POST['subject'];
				$greet = $_POST['greet'];
				$greet = strip_tags($greet);
				$sub = strip_tags($sub);
				$sub = str_replace('"', '\"', $sub);
				$greet = str_replace('"', '\"', $greet);
				if($greet != "" && $sub != ""){
					array_push($variables, "site_mail_subject", "site_mail_greet");
					array_push($values, $sub, $greet);
					makeFile($variables, $values);
				}
				break;
			case 'activation':
				$variables = array();
				$values = array();
				$reg = $_POST['activ'];
				$reg = strip_tags($reg);
				$reg = strtolower($reg);
				$check = array("mail","admin","none");
				if(in_array($reg, $check)){
					array_push($variables, "site_reg_activ");
					array_push($values, $reg);
					makeFile($variables, $values);
				}
				break;
			case 'registration':
				$variables = array();
				$values = array();
				$reg = $_POST['reg'];
				$reg = strip_tags($reg);
				$reg = strtolower($reg);
				if($reg == "true" || $reg == "false"){
					array_push($variables, "site_reg_open");
					array_push($values, $reg);
					makeFile($variables, $values);
				}
				break;
			case 'title':
				$variables = array();
				$values = array();
				$slogan = strip_tags($_POST['slogan']);
				$title = $_POST['title'];
				$title = str_replace('"', '', $title);; // make it possible to have " in title
				array_push($variables, "site_title");
				array_push($values, $title);
				array_push($values, $slogan);
				makeFile($variables, $values);
				break;
			default:
				// Invalid command DO NOTHING
				break;
		}
		echo "<center><h2>Configuration</h2></center>";
		echo "<form action=\"?module=settings&action=title\" method=\"post\">
		  <div class=\"form_settings\" style=\"border: 1px solid #999; padding: 5px;\">
		  	<p><b>Title</b></p>
			<p><span>Title</span><input type=\"text\" name=\"title\" class=\"contact\" value=\"".$site_title."\"/></p>
			<p><span>Slogan</span><input type=\"text\" name=\"slogan\" class=\"contact\" value=\"".$site_slogan."\"/></p>
		    <p style=\"padding-top: 15px;\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Change\" /></p>
		  </div>
		</form>";
		echo "<form action=\"?module=settings&action=registration\" method=\"post\">
		  <div class=\"form_settings\" style=\"border: 1px solid #999; padding: 5px;\">
		  	<p><b>Registration</b></p>
		    <p>";
			if($site_reg_open){
				echo "<span><font color=\"#228b22\">Registration is currently open</font></span>";
				echo "<input type=\"hidden\" name=\"reg\" value=\"false\">";
			} else {
				echo "<span><font color=\"#FF0000\">Registration is currently closed</font></span>";
				echo "<input type=\"hidden\" name=\"reg\" value=\"true\">";
			}
			echo "</p>
		    <p style=\"padding-top: 15px; position: relative; left: -24.5%;\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Change\" /></p>
		  </div>
		</form>";
		echo "<form action=\"?module=settings&action=activation\" method=\"post\">
		  <div class=\"form_settings\" style=\"border: 1px solid #999; padding: 5px;\">
		  	<p><b>Activation</b></p>
		  	<p>This regulates how the user accounts are activated after registration.<br><br>
		  	<b>E-mail</b>: The users activate the accounts themselves by clicking a link sent to their e-mail.<br><br>
		  	<b>Administrator</b>: The administrators activate the accounts manually for the users. Gives the administrators control over which users activated on the UbiHome system.<br><br>
		  	<b>None</b>: This is open registration and the users does not need to activate their accounts. <b>NOT recommended!</b></p><br>
		    <p><span>Activation</span><select name=\"activ\">";
			echo "<option value=\"mail\"";if($site_reg_activ == "mail"){ echo " selected"; } echo ">E-mail</option>";		
			echo "<option value=\"admin\"";if($site_reg_activ == "admin") { echo " selected"; } echo ">Administrator</option>";
			echo "<option value=\"none\"";if($site_reg_activ == "none") { echo " selected"; } echo ">None</option>";
			echo "</select></p>
		    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Change\" /></p>
		  </div>
		</form>";
		echo "<form action=\"?module=settings&action=email\" method=\"post\">
		  <div class=\"form_settings\" style=\"border: 1px solid #999; padding: 5px;\">
		  	<p><b>Email</b></p>
		  	<p>This is to change the subject & greeting on all e-mails sendt by the UbiHome system.</p>
		    <p><span>Subject</span><input type=\"text\" name=\"subject\" class=\"contact\" value=\"".$site_mail_subject."\"/></p>
		    <p><span>Greeting</span><textarea id=\"greet\" name=\"greet\" class=\"contact\"/>".$site_mail_greet."</textarea></p>
		    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
		  </div>
		</form>";

		// List forms etc
	} else {
		// redirect back
		LayoutManager::redirect("../");
	}
?>