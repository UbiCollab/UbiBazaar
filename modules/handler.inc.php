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

if($_GET['action'] == "activate"){
	 $id = $_GET['id'];
	 $sql = new SQL();
	 $query = sprintf("SELECT * FROM ".$sql->prefix."User WHERE user_id='%s'",
					mysql_real_escape_string($id));
	 $rad = $sql->getArray($query);
	 $zzz = $rad['activate'];
	 
	 $get = stripcslashes($_GET['a']);
	 
	 if($get == $zzz){
		 $query = sprintf("UPDATE ".$sql->prefix."User SET activate='NULL' WHERE user_id='%s'",
					mysql_real_escape_string($id));
		 $sql = $sql->doUpdate($query);
		 if(!$sql){
	 		echo "Failed.";
			echo Error::printError("Unexpected error, please try again later.");
		 } else {
		 	echo "<b><center><font color=green>Success</font><br>
		 	You are now able to log in. <a href=\"./\">Back</a></center></b>";
		 }
	 } else {
 		echo "Failed.";
		echo Error::printError("Unexpected error, please try again later.");
	 }
 }
 
if($_GET['action'] == "login"){
	echo "<font color=\"#FF0000\">Trying to login</font><BR>";
	$user = new User();
	$user->login($_POST['user'], $_POST['password']);
}

if($_GET['action'] == "logout"){
	echo "<font color=\"#FF0000\">$logout_trying</font><BR>";
	if(isset($_SESSION['user'])){
		$user = $_SESSION['user'];
		$user->__destruct();
		session_start();
		session_destroy();
		print("<b><font color=\"#00FF00\">Success!</font></b><br>");
		LayoutManager::redirect("./");
	} else {
		print("<b><font color=\"#FF0000\">User not logged in.</font></b><br>");
		LayoutManager::redirect("./");
	}
}

if($_GET['action'] == "forgot"){
	include 'engine/values/site.values.php';
	if($_GET['save'] == "true"){
			if(stripslashes($_POST['mail']) == stripslashes($_POST['conf-mail'])){
				$email_address = stripslashes($_POST['mail']);
				if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email_address)){
					$sql = new SQL();
					
					// Check if the user actually excisttststst
					$query = sprintf("SELECT * FROM ".$sql->prefix."User WHERE email='%s'",
						mysql_real_escape_string($email_address));
					
					if($sql->getCount($query) > 0){
						$salt = "abchefghjkmnpqrstuvwxyz0123456789ABCDEFGHJKLMNOPQRSTUVWXYZ!#"; 
						srand((double)microtime()*1000000); 		
						$i = 0; 			
						while ($i <= 10) { 			
							$num = rand() % 61; 			
							$tmp = substr($salt, $num, 1); 			
							$pass = $pass . $tmp; 			
							$i++; 			
						} 
						
						$pass_crypt = md5($pass);
						
						$query = sprintf("UPDATE ".$sql->prefix."User SET password='$pass_crypt' WHERE email='%s'",
							mysql_real_escape_string($email_address));
						$sql->doUpdate($query);
						
// Let's mail the user!
$subject = $site_mail_subject." - Password reset";
$message = "
Hello

Here you got your new password: '$pass'
		
Click the following link to login: ".LayoutManager::curURL()."?module=me&action=settings

".$site_mail_greet."

Please do not respond to this email.";
						if(!mail($email_address, $subject, $message, "From: ".$site_mail_subject."<".$site_mail_address.">\nX-Mailer: PHP/" . phpversion())){
							LayoutManager::alertFailure("Unexpected error, please try again later.");
						}
					}
					LayoutManager::alertSuccess("A new password is now on it's way to your mailbox.");
				} else {
					LayoutManager::alertFailure("Please enter a valid email. This is not valid.");
				}
			} else {
				LayoutManager::alertFailure("Please enter the same email in both fields.");
			}
	}
	echo "<form action=\"?module=handler&action=forgot&save=true\" method=\"post\">
	  <div class=\"form_settings\" style=\"float:right; position:relative; left: -24%;\">
	  	<center><h2>".strip_tags($site_title)." - Reset password</h2></center>
	  	<br>
	  	<p><span>E-mail</span><input type=\"text\" name=\"mail\" tabindex=\"1\" class=\"contact\"/></p>
	    <p><span>Confirm e-mail</span><input type=\"text\" name=\"conf-mail\" tabindex=\"2\" class=\"contact\"/></p>
	    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" name=\"contact_submitted\" value=\"Submit\" /></p>
	    <br>
	  </div>
	</form>";
}


if($_GET['action'] == "registration"){

	$name = $_POST['name'];
	$name = stripslashes($name);
	$email1 = $_POST['email1'];
	$email1 = stripslashes($email1);
	$email2 = $_POST['email2'];
	$email2 = stripslashes($email2);
	$password1 = $_POST['password1'];
	$password1= md5($password1);
	$password2 = $_POST['password2'];
	$password2 = md5($password2);
	
	include 'engine/values/site.values.php';
	
	if($site_reg_open){
		$sql = new SQL();
		$query = sprintf("SELECT * FROM ".$sql->prefix."User WHERE email = '%s'",
			mysql_real_escape_string($email1));
		$count = $sql->getCount($query);
		if($count == 0){
			if($email1 == $email2){
				if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email1)){
					if($password1==$password2){
						if(strlen($_POST['password1'])>=$security_length){	
							$activate = md5(time());
							$query = sprintf("INSERT INTO ".$sql->prefix."User (email, name, password, activate) VALUES ('%s', '%s', '%s', '%s')",
							mysql_real_escape_string($email1),
							mysql_real_escape_string($name),
							mysql_real_escape_string($password1),
							mysql_real_escape_string($activate));
								
							$result = $sql->doUpdate($query);
							if($result){
								$blablabla = mysql_insert_id();
								if($site_reg_activ == "mail"){
									
// Let's mail the user!
$subject = $site_mail_subject." - Account activation";
$message = "
Hello ".$name.".

It is now soon available to log in for the user: $email1

But before you can login you need to activate the account.		
Click the following link to activate: ".LayoutManager::curURL()."?module=handler&action=activate&id=".$blablabla."&a=".$activate."

".$site_mail_greet."

Please do not respond to this email.";
			
									if(!mail($email1, $subject, $message, "From: ".$site_mail_subject."<".$site_mail_address.">\nX-Mailer: PHP/" . phpversion())){
										echo "Failed.";
										echo Error::printError("Unexpected error, please try again later.");
									} else {
										echo "Your account needs activating, please check your mail.<br>";
										echo "<a href=\"./\">Back</a>";
									}
								} else if($site_reg_activ == "admin") {
									echo "Your account needs to be activated by an admin. Please wait for a confirmation email.";
								} else {
									$query = sprintf("UPDATE ".$sql->prefix."User SET activate='NULL' WHERE user_id='%s'",
												mysql_real_escape_string($blablabla));
									 $sql->doUpdate($query);
									echo "You may now login!";
								}
							} else {
								echo "Failed.";
								$error = new Error();
								echo $error->printError("Unexpected error, please try again later.");
							}
						} else {
							echo "Failed.";
							echo Error::printError("Your password was less than the requered 6 characters.");
						}
					} else {
						echo "Failed.";
						echo Error::printError("Your passwords did not match.");
					}
				} else {
					echo "Failed.";
					echo Error::printError("Your email contains illegal characters.");
				} 
			}else {
				echo "Failed.";
				echo Error::printError("Your emails do not match.");
			} 
		}else {
			echo "Failed.";
			echo Error::printError("That email is allready in use.");
		}
	} else {
		echo "Failed.";
		echo Error::printError("Registration temporarily closed.");
	}	
}

?>