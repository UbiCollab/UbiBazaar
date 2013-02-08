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

	class LayoutManager {

		/**
		 * 
		 * Function that will print the menu. Will change based if the user is logged in or not.
		 */
		public static function printMenu() {
          echo "<li"; if($_GET['module'] == "" 
		  				|| $_GET['module'] == "index" 
						|| $_GET['module'] == "handler"){ echo " class=\"selected\"";} echo "><a href=\"./\">Home</a></li>";
		  if(isset($_SESSION['user'])){
			  $user = new User();
			  $user = $_SESSION['user'];
			  if($user->isLoggedIn()){
			  	$output = "";
			  	if($user->getUnreadCount() > 0)
			  		$output = "(<img src=\"images/mail.unread.png\" alt=\"Message inbox\" width=\"10px\" heigth=\"10px\" border=\"0\">".$user->getUnreadCount().")";
				  echo "<li"; if($_GET['module'] == "me" || $_GET['module'] == "device" || ($_GET['module'] == "app" && $_GET['action'] == "manage") || $_GET['module'] == "group"){ echo " class=\"selected\"";} echo "><a href=\"?module=me\">My Stuff</a></li>
				  <li"; if($_GET['module'] == "user" 
						&& $_GET['profile'] != "true"){ echo " class=\"selected\"";} echo "><a href=\"?module=user\">Users</a></li>
				  <li"; if(($_GET['module'] == "app" && $_GET['action'] != "manage" ) || $_GET['module'] == "forum"){ echo " class=\"selected\"";} echo "><a href=\"?module=app\">Browse Apps</a></li>
				  <li"; if($_GET['module'] == "msg"){ echo " class=\"selected\"";} echo "><a href=\"?module=msg\">Messages $output</a></li>";
				echo "<li><a href=\"?module=handler&action=logout\" style=\"color: #FF7A0F;\">Logout</a></li>";
				if($user->isAdmin())
					echo "<li><a href=\"admin/\" style=\"color: #FF7A0F;\">Admin</a></li>";
			  }
			} else {
				  echo "<li"; if($_GET['module'] == "started"){ echo " class=\"selected\"";} echo "><a href=\"?module=started\">Getting started</a></li>
				  <li"; if($_GET['module'] == "about"){ echo " class=\"selected\"";} echo "><a href=\"?module=about\">About Us</a></li>

				  <li"; if($_GET['module'] == "registration"){ echo " class=\"selected\"";} echo "><a href=\"?module=registration\">Register</a></li>
				  <li"; if($_GET['module'] == "login"){ echo " class=\"selected\"";} echo "><a href=\"?module=login\" style=\"color: #FF7A0F;\">Login</a></li>";
			}
		}
		
		/**
		 * 
		 * Function that will include the required files based on the status of the user and what module.
		 * @param string $module
		 */
		public static function printModuleHandler($module){
			$user = LayoutManager::getUser();
			if($user->isLoggedIn()){
				/* Instillinger */
				$Defaultpath = "modules/index.inc.php";
		
				if($module == ""){
					include("$Defaultpath");
				}
				else{
					if(file_exists("modules/$module.inc.php")){
						include("modules/$module.inc.php");
					} else {
						echo"
						<h1>404 - File not found</h1>
			
						<p>
						If the problem persists, please contact the administrators.
						</p>
						
						<p>
						<a href=\"javascript:history.back(1)\">Back</a>
						</p>
						";
					}
				}
			} else if($module == "login") {
				// Handle the users requests about login and registration
				// Check if the user is needed to be refered to a specific url after login.
				LayoutManager::printLoginForm();
			} else if($module == "registration"){ 
				LayoutManager::printRegForm();
			} else {
				/* Instillinger */
				$Defaultpath = "modules/index.inc.php";
		
				if($module == ""){
					include("$Defaultpath");
				}
				else{
					if(file_exists("modules/$module.inc.php")){
						include("modules/$module.inc.php");
					} else {
						echo"
						<h1>404 - File not found</h1>
			
						<p>
						If the problem persists, please contact the administrators.
						</p>
						
						<p>
						<a href=\"javascript:history.back(1)\">Back</a>
						</p>
						";
					}
				}
			}
		}
		
		/**
		 * 
		 * Shows the user the login form
		 */
		public static function printLoginForm() {
			if($_GET['module'] != "login"){
				$cur_page = $_SERVER["REQUEST_URI"];
				$cur_page = str_replace("/index.php", "", $cur_page);
				$_SESSION['ref'] = $cur_page; 
			}
			// The user is not logged in shown the login form
			include 'engine/values/site.values.php';
			echo "<form action=\"?module=handler&action=login\" method=\"post\">
			  <div class=\"form_settings\" style=\"float:right; position:relative; left: -24%;\">
			  	<center><h2>".strip_tags($site_title)." - Login</h2></center>
			  	<br>
			  	<p><span>Email</span><input type=\"text\" name=\"user\" tabindex=\"1\" class=\"contact\"/></p>
			    <p><span>Password</span><input type=\"password\" name=\"password\" tabindex=\"2\" class=\"contact\"/></p>
			    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
			    <br><center>";
				if($site_reg_open){
	        		echo "[<a href=\"?module=registration\">Register</a>]";
				}
			  echo "[<a href=\"?module=handler&action=forgot\">Forgotten password?</a>]</center></div>
			</form>";
		}
		
		/**
		 * 
		 * Shows the user the registration form
		 */
		private static function printRegForm() {
			include 'engine/values/site.values.php';
			if($site_reg_open){
			echo "<form action=\"?module=handler&action=registration\" method=\"post\">
				  <div class=\"form_settings\">
				  	<center><h2>".strip_tags($site_title)." - Registration</h2></center>
				  	<br>
				    <p><span>Email</span><input type=\"text\" name=\"email1\" tabindex=\"1\" class=\"contact\"/></p>
				    <p><span>Confirm email</span><input type=\"text\" name=\"email2\" tabindex=\"2\" class=\"contact\"/></p>
				    <p><span>Name</span><input type=\"text\" name=\"name\" tabindex=\"3\" class=\"contact\"/></p>
				    <p><span>Password</span><input type=\"password\" name=\"password1\" tabindex=\"4\" class=\"contact\"/></p>
				    <p><span>Confirm password</span><input type=\"password\" name=\"password2\" tabindex=\"5\" class=\"contact\"/></p>
				    <p><span>User agreement</span><textarea class=\"contact textarea\" name=\"useragreement\" readonly=\"readonly\">".file_get_contents('engine/values/user.agreement.txt')."</textarea></p>
				    <p>By pressing submit you agree to the user agreement above.</p>
				    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" tabindex=\"6\" name=\"contact_submitted\" value=\"Submit\" /></p>
				  </div>
				</form>";
			} else {
				echo "Registration is currently closed. For more information contact the administrators.";
			}
		}
		
		/**
		 * 
		 * Returns the url of the server the system is installed on.
		 */
		public static function curURL() {
			$pageURL = 'http';
			if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
				$pageURL .= "://";
			if ($_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER['PHP_SELF'];
			} else {
				$pageURL .= $_SERVER["SERVER_NAME"]."/";
			}
			return $pageURL;
		}
		
		/**
		 * 
		 * Grabs the userdata about the loggedin user
		 */
		public static function getUser() {
			if(isset($_SESSION['user']))
				$user = $_SESSION['user'];
			else
				$user = new User();
			return $user;
		}
		
		/**
		 * 
		 * Gives an positive feedback to the user with the $msg
		 * @param string $msg
		 */
		public static function alertSuccess($msg) {
			echo "<div class=\"alert success\">
				<p>
					<strong>".$msg."</strong>
				</p>
			</div>";
		}
		
		/**
		 * 
		 * Gives an negative feedback to the user with the $msg.
		 * @param string $msg
		 */
		public static function alertFailure($msg) {
			echo "<div class=\"alert failure\">
				<p>
					<strong>".$msg."</strong>
				</p>
			</div>";
		}
		
		/**
		 * 
		 * checks if the install is complete
		 */
		public static function checkInstall() {
			// TODO: Something awesoem
			if(!file_exists("engine/values/mysql.values.php")){
				LayoutManager::redirect("INSTALL.php");
			} else if(file_exists("INSTALL.php")){
				FileHandler::delete("INSTALL.php");
				echo "<center><img src=\"images/warning.png\" height=14px border=0/> Please delete 'INSTALL.php'. It's unsafe to have this in the root directory.</center>";
			} 
		}
		
		/**
		 * 
		 * Redirects the user to the specified $url
		 * @param string $url
		 */
		public static function redirect($url) {
			echo "<a href=\"./".$url."\">Click here to continue.</a>";
			echo "<script type=\"text/javascript\">
			<!--
			window.location = \"./".$url."\"
			//-->
			</script>";
		}
	}