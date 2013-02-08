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
	include_once '../engine/handler/user.handler.php';
	session_start();
	include_once '../engine/handler/layout.handler.php';
	include_once '../engine/handler/mysql.handler.php';
	include_once '../engine/handler/error.handler.php';
	include_once '../engine/handler/app.handler.php';
	include_once '../engine/handler/group.handler.php';
	include_once '../engine/handler/device.handler.php';
	include_once '../engine/handler/file.handler.php';
	include_once '../engine/values/site.values.php';
	
	// TODO: check if the user is logged in and an administrator.
	// if(not) redirect back to regularpage.
	$user = LayoutManager::getUser();
	if(!$user->isAdmin())
		LayoutManager::redirect("../");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <title><?php echo strip_tags($site_title)." - Administration"; ?></title>
  <meta name="description" content="website description" />
  <meta name="keywords" content="website keywords, website keywords" />
  <meta http-equiv="content-type" content="text/html; charset=utf8" />
  <link rel="stylesheet" type="text/css" href="../style/style.css" />
  <link rel="stylesheet" type="text/css" href="../style/jquery.lightbox-0.5.css" />
  <link rel="shortcut icon" href="../images/icon.ico" />
  <!-- JQuery & JavaScript imports -->
  <script src="../js/jquery.js" type="text/javascript"></script>
  <script src="../js/jquery.jeditable.js" type="text/javascript"></script>
  <script src="../js/jquery.lightbox-0.5.min.js" type="text/javascript"></script>
  <script src="../js/custom.js" type="text/javascript"></script>
  <script type="text/javascript" charset="utf-8">
			$(function() {
			  $(".edit_text").editable("misc/tag.edit.php", {
			      indicator : "<img src='../images/indicator.gif'>",
			      tooltip   : "Click to edit...",
				  cssclass: 'edit_text',
				  width     : 400,		      
			      height: 'none'
			  });
			});
			</script>
  <!-- END JQuery & JavaScript imports -->
</head>
<body>
<!-- error message-box -->
<div class="overlay" id="overlay" style="display:none;"></div>
<div class="box" id="box">
 <h1>Error!</h1>
 <p id="error_text">ERROR TEXT</p>
<!-- END error message-box -->
</div>
  <div id="main">
    <div id="links">
    </div>
    <div id="header">
      <div id="logo">
        <div id="logo_text">
          <h1><?php echo $site_title; ?></h1>
          <h2><?php echo "Administration"; ?></h2>
        </div>
      </div>
      <div id="menubar">
        <ul id="menu">
          <!-- page menu -->
			<?php 
	        	echo "<li><a href=\"../\" style=\"color: #FF7A0F;\">Home</a></li>";
            	echo "<li"; if($_GET['module'] == "" 
	  				|| $_GET['module'] == "index"){ echo " class=\"selected\"";} echo "><a href=\"./\">Admin</a></li>";
	  			echo "<li"; if($_GET['module'] == "user"){ echo " class=\"selected\"";} echo "><a href=\"?module=user\">User</a></li>";
	  			echo "<li"; if($_GET['module'] == "forum"){ echo " class=\"selected\"";} echo "><a href=\"?module=forum\">Forum</a></li>";
	  			echo "<li"; if($_GET['module'] == "app" || $_GET['module'] == "category" || $_GET['module'] == "platform" || $_GET['module'] == "tag" ){ echo " class=\"selected\"";} echo "><a href=\"?module=app\">Application</a></li>";
	  			echo "<li"; if($_GET['module'] == "settings"){ echo " class=\"selected\"";} echo "><a href=\"?module=settings\">Settings</a></li>";
			?>
		  <!-- END page menu -->
        </ul>
      </div>
    </div>
    <div id="site_content">
      <div id="content">
        <!-- page content here -->
		<?php 
			$module = $_GET['module'];
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
