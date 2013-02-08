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
	include_once 'engine/handler/layout.handler.php';
	include_once 'engine/handler/mysql.handler.php';
	include_once 'engine/handler/error.handler.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <title>UbiHome - Installation</title>
  <meta name="description" content="website description" />
  <meta name="keywords" content="website keywords, website keywords" />
  <meta http-equiv="content-type" content="text/html; charset=utf8" />
  <link rel="stylesheet" type="text/css" href="style/style.css" />
  <link rel="shortcut icon" href="images/icon.ico" />
</head>

<body>
  <div id="main">
    <div id="links">
    </div>
    <div id="header">
      <div id="logo">
        <div id="logo_text">
          <h1>Ubi<span class="orange">Home</span></h1>
          <h2>Bringing the apps home to you!</h2>
        </div>
      </div>
      <div id="menubar">
        <ul id="menu">
          <!-- page menu -->
		  <!-- END page menu -->
        </ul>
      </div>
    </div>
    <div id="site_content">
      <div id="content">
		<?php 
		// IF some criteria create config files
		switch($_GET['step']){
			case '1':
				$valid = true;
				// TODO: do the checks
				$db_host = $_POST['host'];
				$db_user = $_POST['username'];
				$db_password = $_POST['password'];
				$db_db = $_POST['database'];
				$db_prefix = $_POST['prefix'];
				
				if($db_host == ""){
					$valid = false;
					LayoutManager::alertFailure("Host can't be empty.");
				}
				if($db_user == ""){
					$valid = false;
					LayoutManager::alertFailure("User can't be empty.");
				}
				if($db_db == ""){
					$valid = false;
					LayoutManager::alertFailure("Database can't be empty.");
				}
				if($db_password == ""){
					$valid = false;
					LayoutManager::alertFailure("Password can't be empty.");
				}

				if($valid){				
					$file2 = "engine/values/mysql.values.php";
					$fz = fopen($file2, 'w');
					echo "Trying to create SQL config-file.<br>";
					if($fz != false){
						fwrite($fz,"<?php");
						fwrite($fz, "\n\t$");
						fwrite($fz, "db_host = \"". $db_host ."\";\n");
						fwrite($fz, "\t$");
						fwrite($fz, "db_user = \"". $db_user ."\";\n");
						fwrite($fz, "\t$");
						fwrite($fz, "db_password = \"". $db_password ."\";\n");
						fwrite($fz, "\t$");
						fwrite($fz, "db_db = \"". $db_db ."\";\n");
						fwrite($fz, "\t$");
						fwrite($fz, "db_prefix = \"". $db_prefix ."\";\n");
						fwrite($fz,"?>");
						fclose($fz);
						LayoutManager::alertSuccess("SQL config-file created.");
					}
					echo "Trying to connect to SQL-server.<br>";
					// Insert the sql script
					$sql = new SQL();
					if($sql->isConnected()){
						echo "Success!<br>";
						echo "Trying to create !<br>";
						// TODO: Create all the tables with the prefix
						// TODO: validate if the script was completed correctly
						$sql->run_sql_script("engine/values/mysql.sql");
						// if true redirect to step 2
						LayoutManager::redirect("INSTALL.php?step=2");
					} else {
						echo "Unable to connect to the SQL-server. Is the credentials you entered correct?<br>";
						echo "Trying to delete SQL config-file.<br>";
						if(!unlink($file2))
							echo "Failed to delete '".$file2."'<br>";
					}
				}
				break;
			case '2':
				if(file_exists("engine/values/mysql.sql")){
					if(!unlink("engine/values/mysql.sql"))
						echo "Failed to delete 'engine/values/mysql.sql'. Please do it manually.";
				}
				$valid = true;
				$site_mail_address = $_POST['email'];
				$site_title = $_POST['title'];
				$site_slogan = $_POST['slogan'];
				if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $site_mail_address)){
					$valid = false;
					echo "Not a valid email adress.<br>";
				}
				if($site_title == ""){
					$valid = false;
					echo "Title can't be empty.<br>";
				}
				if($site_slogan == ""){
					$valid = false;
					echo "Slogan can't be empty.<br>";
				}
				if($valid){
					$file1 = "engine/values/site.values.php";
					$fz = fopen($file1, 'w');
					
					if($fz != false){
						fwrite($fz,"<?php");
						fwrite($fz,"\n\t// Site variables\n");
						fwrite($fz, "\t$");
						fwrite($fz, "site_title = \"". $site_title ."\";\n");
						fwrite($fz, "\t$");
						fwrite($fz, "site_slogan = \"". $site_slogan ."\";\n");
						fwrite($fz, "\t$");
						fwrite($fz, "site_reg_open = true;\n");
						fwrite($fz,"\n\t// Email variables\n");
						fwrite($fz, "\t$");
						fwrite($fz, "site_mail_address = \"". $site_mail_address ."\";\n");
						fwrite($fz, "\t$");
						fwrite($fz, "site_mail_subject = \"". $site_title ."\";\n");
						fwrite($fz, "\t$");
						fwrite($fz, "site_mail_greet = \"Thanks, the management.\";\n");
						fwrite($fz, "\t$");
						fwrite($fz, "site_reg_activ = \"mail\";\n");
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
						echo "Site config-file created.<br>";
					}
					
					// TODO: validate the file.
					// if correct redirect to step 3
					LayoutManager::redirect("INSTALL.php?step=3");
				} else {
					echo "<form action=\"?step=2\" method=\"post\">
					  <div class=\"form_settings\" style=\"float:right; position:relative; left: -29%;\">
					  	<center><h2>Site configuration</h2></center>
					  	<br>
					    <p><span>Title</span><input type=\"text\" name=\"title\" tabindex=\"1\" class=\"contact\"/></p>
					    <p><span>Slogan</span><input type=\"text\" name=\"slogan\" tabindex=\"2\" class=\"contact\"/></p>
					    <p><span>Email</span><input type=\"text\" name=\"email\" tabindex=\"3\" class=\"contact\"/></p>
					    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" tabindex=\"4\" name=\"contact_submitted\" value=\"Next\" /></p>
					  </div>
					</form>";
				}
				break;
			case '3':
				echo "<form action=\"?step=4\" method=\"post\">
				  <div class=\"form_settings\" style=\"float:right; position:relative; left: -29%;\">
				  	<center><h2>Administrator Registration</h2></center>
				  	<br>
				    <p><span>Email</span><input type=\"text\" name=\"email1\" tabindex=\"1\" class=\"contact\"/></p>
				    <p><span>Confirm email</span><input type=\"text\" name=\"email2\" tabindex=\"2\" class=\"contact\"/></p>
				    <p><span>Name</span><input type=\"text\" name=\"name\" tabindex=\"3\" class=\"contact\"/></p>
				    <p><span>Password</span><input type=\"password\" name=\"password1\" tabindex=\"4\" class=\"contact\"/></p>
				    <p><span>Confirm password</span><input type=\"password\" name=\"password2\" tabindex=\"5\" class=\"contact\"/></p>
				    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" tabindex=\"6\" name=\"contact_submitted\" value=\"Finish\" /></p>
				  </div>
				</form>";
				break;
			case '4':
				include 'engine/values/site.values.php';
				// Create the user and grant admin rights
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
				$sql = new SQL();
				if($email1 == $email2){
					if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email1)){
						if($password1==$password2){
							if(strlen($_POST['password1'])>=$security_length){	
								$query = sprintf("INSERT INTO ".$sql->prefix."User (email, name, password, activate) VALUES ('%s', '%s', '%s', '%s')",
									mysql_real_escape_string($email1),
									mysql_real_escape_string($name),
									mysql_real_escape_string($password1),
									mysql_real_escape_string("NULL"));
									
								$result = $sql->doUpdate($query);
								$last_id = $sql->getLastId();
								$query = sprintf("INSERT INTO ".$sql->prefix."AdminUser (user_id, owner) VALUES ('%s', '%s')",
									mysql_real_escape_string($last_id),
									mysql_real_escape_string("1"));
								
								if($result){
									$sql->doUpdate($query);
									LayoutManager::redirect("./");
								} else {
									LayoutManager::alertFailure("Unexpected mysql error. ".$sql->getError());
								}
							} else {
								LayoutManager::alertFailure("Password needs to be atleast 6 characters long.");
							}
						} else {
							LayoutManager::alertFailure("Passwords not alike.");
						}
					} else {
						LayoutManager::alertFailure("Not a valid email adress.");
					}
				} else {
					LayoutManager::alertFailure("The email adresses was not alike.");
				}
				echo "<a href=\"INSTALL.php?step=3\">Back</a>";
				break;
			default:
			echo "<form action=\"?step=1\" method=\"post\">
				  <div class=\"form_settings\" style=\"float:right; position:relative; left: -29%;\">
				  	<center><h2>Database setup</h2></center>
				  	<br>
				    <p><span>Host</span><input type=\"text\" name=\"host\" tabindex=\"1\" class=\"contact\" value=\"localhost\"/></p>
				    <p><span>Username</span><input type=\"text\" name=\"username\" tabindex=\"2\" class=\"contact\"/></p>
				    <p><span>Password</span><input type=\"password\" name=\"password\" tabindex=\"3\" class=\"contact\"/></p>
				    <p><span>Database</span><input type=\"text\" name=\"database\" tabindex=\"4\" class=\"contact\"/></p>
				    <p><span>Table prefix</span><input type=\"text\" name=\"prefix\" tabindex=\"5\" class=\"contact\" value=\"ubihome_\"/></p>
				    <p style=\"padding-top: 15px\"><span>&nbsp;</span><input class=\"submit\" type=\"submit\" tabindex=\"6\" name=\"contact_submitted\" value=\"Next\" /></p>
				  </div>
				</form>";
				break; 
		}
		?>
        <!-- END page content -->
      </div>
    <div id="site_content_bottom"></div>
    </div>
    <!-- page footer here -->
    <div id="footer">Copyright &copy; Ubicollab. All Rights Reserved. | <a href="http://validator.w3.org/check?uri=referer">XHTML</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a></div>
    <!-- END page content here -->
  </div>
</body>
</html>