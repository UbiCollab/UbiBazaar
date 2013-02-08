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
	//BUG: there are some notices displayed because $_POST["var"] is not checked
	error_reporting (E_ALL ^ E_NOTICE); 
	include_once 'engine/handler/user.handler.php';
	session_start();
	include_once 'engine/handler/layout.handler.php';
	include_once 'engine/handler/mysql.handler.php';
	include_once 'engine/handler/error.handler.php';
	include_once 'engine/handler/app.handler.php';
	include_once 'engine/handler/group.handler.php';
	include_once 'engine/handler/device.handler.php';
	include_once 'engine/handler/file.handler.php';
	include_once 'engine/values/site.values.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
  <script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-18514800-2']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

  </script>
  <title><?php echo strip_tags($site_title)." - ".$site_slogan; ?></title>
  <meta name="description" content="website description" />
  <meta name="keywords" content="website keywords, website keywords" />
  <meta http-equiv="content-type" content="text/html; charset=utf8" />
  <link rel="stylesheet" type="text/css" href="style/style.css" />
  <link rel="stylesheet" type="text/css" href="style/jquery.lightbox-0.5.css" />
  <link rel="shortcut icon" href="images/icon.ico" />
  
  <!-- JQuery & JavaScript imports -->
  <script src="js/jquery.js" type="text/javascript"></script>
  <script src="js/jquery.lightbox-0.5.min.js" type="text/javascript"></script>
  
  <script src="js/custom.js" type="text/javascript"></script>
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
    <?php LayoutManager::checkInstall(); ?>
    </div>
    <div id="header">
      <div style="float: right;"><span class="orange">[<a href="#" onclick="window.open('./help/', 'User Manual', 'width=675,height=500,scrollbars=yes');">Help</a>]</span></div>
      <div id="logo">
        <div id="logo_text">
          <h1><?php echo $site_title; ?></h1>
          <h2><?php echo $site_slogan; ?></h2>
        </div>
      </div>
      <div id="menubar">
        <ul id="menu">
          <!-- page menu -->
          <?php
			LayoutManager::printMenu();
		  ?>
		  <!-- END page menu -->
        </ul>
      </div>
    </div>
    <div id="site_content">
      <div id="content">
        <!-- page content here -->
		<?php
            LayoutManager::printModuleHandler($_GET['module']);
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
