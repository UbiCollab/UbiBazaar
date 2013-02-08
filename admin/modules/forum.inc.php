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
		// Get SQL connection
		$sql = new SQL();
		
		// ** HANDLERS ** //
		switch ($_GET['action']){
			case 'dismiss':
				// Delete the report-record
				if(eregi("^[0-9]{1,11}$", $_GET['id'])){
					$query = "DELETE FROM ".$sql->prefix."ReportedPost WHERE post_id=".$_GET['id'];
					if($sql->doUpdate($query))
						LayoutManager::alertSuccess("Report dismissed.");
					else
						LayoutManager::alertFailure("An error has occured while handling your request.");
				}
				break;
			case 'warning':
				// Send the user a message with an official warning
				if(eregi("^[0-9]{1,11}$", $_GET['id'])){
					// Gather information about the reported post
					$post = $sql->getArray("SELECT * FROM ".$sql->prefix."ForumPost WHERE post_id=".$_GET['id']);
					
					// Format the message
					$subject = "Behavior warning!";
$msg = "Hi.

This is an official warning letting you know that your behavior in the forum is not tolerated
and the administrators are considering giving you are permanent ban if this continues.

The administrators.";
					
					$query = "INSERT INTO ".$sql->prefix."UserMsg (to_u, from_u, header, msg) VALUES ('".$post['user_id']."','".$user->getId()."','$subject','$msg')";
					if($sql->doUpdate($query)){
						$query = "DELETE FROM ".$sql->prefix."ReportedPost WHERE post_id=".$_GET['id'];
						$sql->doUpdate($query);
						LayoutManager::alertSuccess("Warning successfully sent.");	
					}
				}
				break;
			case 'ban':
				// Call on the same function that we use to ban users from admin/user.inc.php
				if(eregi("^[0-9]{1,11}$", $_GET['id'])){
					$post = $sql->getArray("SELECT * FROM ".$sql->prefix."ForumPost WHERE post_id=".$_GET['id']);
					if($sql->getCount("SELECT user_id FROM ".$sql->prefix."AdminUser WHERE user_id=".$post['user_id']) == 0 && $user->getId() != $post['user_id']){
						$query = sprintf("UPDATE `".$sql->prefix."User` SET `deleted`='%s' WHERE `user_id`='%s'",
							mysql_real_escape_string("1"),
							mysql_real_escape_string($post['user_id']));
						if(!$sql->doUpdate($query))
							echo "Mysql error while handling your request.";
						else
							LayoutManager::alertSuccess("User has successfully been banned.");
					}
				}
				break;
		}
		// Grab thread-post count
		$post_count = $sql->getCount("SELECT * FROM ".$sql->prefix."ReportedPost");
		
		// how many rows to show per page
		$rowsPerPage = 12;
		
		// by default we show first page
		$pageNum = 1;
		
		// if $_GET['page'] defined, use it as page number
		if(isset($_GET['page'])){
		    $pageNum = $_GET['page'];
		}
		
		// counting the offset
		$offset = ($pageNum - 1) * $rowsPerPage;
		// Gather all reported posts
		$reports = $sql->getFullArray("SELECT * FROM ".$sql->prefix."ReportedPost ORDER BY timestamp DESC LIMIT $offset,$rowsPerPage");
		
		// how many pages we have when using paging?
		$maxPage = ceil($post_count/$rowsPerPage);
		
		// print the link to access each page
		$self = "?module=forum";
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
		// print the navigation link
		echo "<div style=\"float: right;\">";
		echo $first . $prev . $nav . $next . $last;
		echo "</div><br>";
		foreach ($reports as $rep) {
			$post = $sql->getArray("SELECT * FROM ".$sql->prefix."ForumPost WHERE post_id=".$rep['post_id']);
			$msg = $post['message'];
			if(substr_count($msg,"[quote:") > 0 && substr_count($msg,"[/quote]")>0){
				$msg = str_ireplace("[/quote]", "</blockquote>", $msg);
				for ($j = 0; $j < substr_count($msg,"[quote:"); $j++) {
					$msg = str_ireplace("[quote:", "<blockquote style=\"background: #FFF; padding: 10px; margin:15px;\">", $msg);
					$msg = substr_replace($msg, "", strpos($msg, "]")-1, 2);
					//$msg = str_ireplace("]", "", $msg);
				}
			}
			// Display the posts
			echo "<div class=\"form_settings\" style=\"border: 1px solid #999; padding-left: 5px; padding-bottom: 5px;\">";
			$user_r = new User();
			$user_r->getInfo($rep['user_id']);
			echo "<div style=\"float: right; border-bottom: 1px solid #999; border-left: 1px solid #999; padding: 2px;\">Reported by ".$user_r->toString().".</div>";
			$user_r->getInfo($post['user_id']);
			echo "Original post by ".$user_r->toString().".<br>".$msg;
			echo "</div>";
			echo "<div style=\"float: right; border-bottom: 1px solid #999; border-left: 1px solid #999; border-right: 1px solid #999; margin-bottom: 5px; padding: 2px;\">
			[<a href=\"?module=forum&action=warning&id=".$rep['post_id']."\">Give warning</a>] 
			[<a href=\"?module=forum&action=ban&id=".$rep['post_id']."\">Ban</a>] 
			[<a href=\"?module=forum&action=dismiss&id=".$rep['post_id']."\">Dismiss</a>]</div><br>";
		}
		if(count($reports) == 0)
			echo "Hooray, no reports here!";
	} else {
		// redirect back
		LayoutManager::redirect("../");
	}
?>