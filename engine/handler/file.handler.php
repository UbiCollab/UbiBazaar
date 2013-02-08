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
	class FileHandler {
		
		/**
		 * Will upload screenshots $_FILES and link them to $app_id
		 * 
		 * @param post-data $_FILES
		 * @param int $app_id
		 */
		public static function uploadScreenshots($_FILES, $app_id){
			$sql = new SQL();
			$size = sizeof($_FILES['name']);
			if($size > 9){
				echo "To many pictures, uploading just the first 9 pictures.<br>";
				$size = 9;
			}
			include 'engine/values/site.values.php';
			$filetype_array = array("image/png", "image/gif", "image/jpeg", "image/pjpeg");
			for($i = 0; $i < $size; $i++){
				if (in_array($_FILES['type'][$i], $filetype_array) && ($_FILES["size"][$i] < $max_file_size)){
					if ($_FILES["error"][$i] > 0){
						echo "errorkode: " . $_FILES["error"][$i] . "<br />";
					} else {
						$tmpName = $_FILES['tmp_name'][$i]; 
						list($width,$height) = getimagesize($tmpName);
						$ext = FileHandler::findexts($_FILES['name'][$i]);
						$ext = strtolower($ext);
						if(($width > $picture_max_width) || ($height > $picture_max_height)){
							echo "Image too large, trying to resize picture.<br>";
							if($ext=="jpg" || $ext=="jpeg") {
								$tmpName = $_FILES['tmp_name'][$i];
								$src = imagecreatefromjpeg($tmpName);
							} else if($ext=="png") {
								$tmpName = $_FILES['tmp_name'][$i];
								$src = imagecreatefrompng($tmpName);
							} else {
								$src = imagecreatefromgif($tmpName);
							}
							if($height > $width){
								$newheight=$picture_max_height;
								$newwidth=($width/$height)*$newheight;
							} else {
								$newwidth=$picture_max_width;
								$newheight=($height/$width)*$newwidth;
							}		

							$tmp=imagecreatetruecolor($newwidth,$newheight);
											
							imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight, $width,$height);
							
							$name = date("U");
							$name = md5($name);
							//This applies the function to our file 
							while(file_exists("mobile/files/ss/" .$name.".".$ext)){
								$name = date("U");
								$name = md5($name);
							}
								
							$filename = "mobile/files/ss/" .$name.".".$ext;
							
							imagejpeg($tmp,$filename,75);
							
							imagedestroy($src);
							imagedestroy($tmp);	

							$filnavn = (string)($name.".".$ext);
							
							// tid for å lagre det i databasen.
							$query = "INSERT INTO ".$sql->prefix."ApplicationScreenshots (app_id, url) VALUES ('$app_id', '$filnavn')";
							$result = $sql->doUpdate($query);

							if(!$result){
								echo "ERROR";
								echo "mysql error: <br><b>" . mysql_error() . "</b><br>";
							} else {
								echo "Picture ".($i+1)." stored!<br>";
							}				
						} else {
							
							$name = date("U");
							$name = md5($name);
							
							//This applies the function to our file 
							while(file_exists("mobile/files/ss/" .$name.".".$ext)){
								$name = date("U");
								$name = md5($name);
							}
							if (file_exists("mobile/files/ss/" .$name.".".$ext)){
								echo (string)$name . ".$ext already exists. ";
							} else {
								move_uploaded_file($_FILES["tmp_name"][$i], "mobile/files/ss/" .$name.".".$ext);
								echo "Stored in: " . "mobile/files/ss/" .$name.".".$ext."<br>";

								$filnavn = (string)($name.".".$ext);
								
								// tid for å lagre det i databasen.
								$query = "INSERT INTO ".$sql->prefix."ApplicationScreenshots (app_id, url) VALUES ('$app_id', '$filnavn')";
								$result = $sql->doUpdate($query);
	
								if(!$result){
									echo "ERROR";
									echo "mysql error: <br><b>" . mysql_error() . "</b><br>";
								} else {
									echo "Picture ".($i+1)." stored!<br>";
								}
							}
						}
					}
				} else {
					echo "<br>Invalid file. <br>";
					echo $_FILES['type'][$i]." not supported.<br>";
				}
			}
		}
		
		/**
		 * Will upload an icon $file for he specified $app_id
		 * 
		 * @param unknown_type $file
		 * @param unknown_type $app_id
		 */
		public static function uploadIcon($file, $app_id) {
			// setting up output array to return
			$output = array();
			
			// Checking the filetype
			$filetype_array = array("image/png", "image/gif", "image/jpeg", "image/pjpeg");
			if (in_array($file['type'], $filetype_array)){
				if ($file["error"] > 0){
					echo "errorkode: " . $file["error"] . "<br />";
				} else {
					// Setting the filepath
					$filename = "mobile/files/icon/" .$app_id.".png";
					// Gathering information.
					$tmpName = $file['tmp_name']; 
					list($width,$height) = getimagesize($tmpName);
					
					// Get file extension
					$ext = FileHandler::findexts($file['name']);
					
					if($ext=="jpg" || $ext=="jpeg") {
						$src = imagecreatefromjpeg($tmpName);
					} else if($ext=="png") {
						$src = imagecreatefrompng($tmpName);
					} else {
						$src = imagecreatefromgif($tmpName);
					}
					
					// Creating a temporary picture with the correct size.
					$tmp = imagecreatetruecolor(48, 48);
					
					imagecopyresampled($tmp, $src, 0, 0, 0, 0, 48, 48, $width, $height);
					
					// Check if theres an icon already uploaded.
					if(file_exists($filename)){
						// Delete the icon
						FileHandler::delete($filename);
					}
					
					$output['success'] = imagepng($tmp, $filename, 7);
					$output['error'] = "Something unexpected happend. Try again later.";
						
					imagedestroy($src);
					imagedestroy($tmp);
				}
			} else {
				$output['success'] = false;
				$output['error'] = "Invalid filetype. type:".$ext;
			}
			return $output;
		}
		
		/**
		 * Function to upload a profilepicture $file for the specified $user_id
		 * 
		 * @param post-data $file
		 * @param int $user_id
		 */
		public static function uploadProfilePicture($file, $user_id) {
			$sql = new SQL();
			$output = array();
			$output['error'] = false;
			include 'engine/values/site.values.php';
			$filetype_array = array("image/png", "image/gif", "image/jpeg", "image/pjpeg");
			if (in_array($file['type'], $filetype_array) && ($file["size"] < $max_file_size)){
				if ($file["error"] > 0){
					$output['error'] = true;
					$output['msg'] .= "errorkode: " . $file["error"] . "<br />";
				} else {
					$tmpName = $file['tmp_name']; 
					list($width,$height) = getimagesize($tmpName);
					$ext = FileHandler::findexts($file['name']);
					$ext = strtolower($ext);
					if(($width > 175) || ($height > 175)){
						$output['msg'] .= "Image to large, trying to resize picture. ";
						if($ext=="jpg" || $ext=="jpeg") {
							$src = imagecreatefromjpeg($tmpName);
						} else if($ext=="png") {
							$src = imagecreatefrompng($tmpName);
						} else {
							$src = imagecreatefromgif($tmpName);
						}
						if($height > $width){
							$newheight=175;
							$newwidth=($width/$height)*$newheight;
						} else {
							$newwidth=175;
							$newheight=($height/$width)*$newwidth;
						}		

						$tmp=imagecreatetruecolor($newwidth,$newheight);
										
						imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight, $width,$height);
						
						$name = md5($user_id);
						//This applies the function to our file 
						if(file_exists("images/user/" .$name.".".$ext)){
							FileHandler::delete("images/user/" .$name.".".$ext);
						}
							
						$filename = "images/user/" .$name.".".$ext;
						
						imagejpeg($tmp,$filename,75);
						
						imagedestroy($src);
						imagedestroy($tmp);	

						$filnavn = (string)($name.".".$ext);
						
						// tid for å lagre det i databasen.
						$query = sprintf("UPDATE `".$sql->prefix."User` SET `picture`='%s' WHERE `user_id`='%s'",
							mysql_real_escape_string($filnavn),
							mysql_real_escape_string($user_id));
						
						$result = $sql->doUpdate($query);

						if(!$result){
							$output['error'] = true;
							$output['msg'] .= "mysql error:  <b>" . $sql->getError() . "</b> ";
						} else {
							$output['msg'] .= "Success! Your new profile picture has been stored!";
						}
					} else {
						
						$name = md5($user_id);
						//This applies the function to our file 
						if(file_exists("images/user/" .$name.".".$ext)){
							FileHandler::delete("images/user/" .$name.".".$ext);
						}
							
						$filename = "images/user/" .$name.".".$ext;
						
						move_uploaded_file($file["tmp_name"], $filename);

						$filnavn = (string)($name.".".$ext);
						
						// tid for å lagre det i databasen.
						$query = sprintf("UPDATE `".$sql->prefix."User` SET `picture`='%s' WHERE `user_id`='%s'",
							mysql_real_escape_string($filnavn),
							mysql_real_escape_string($user_id));

						$result = $sql->doUpdate($query);
						
						if(!$result){
							$output['error'] = true;
							$output['msg'] .= "mysql error:<b>" . $sql->getError() . "</b> ";
						} else {
							$output['msg'].= "Your new profile picture has been stored!";
						}
					}
				}
			} else {
				$output['error'] = true;
				$output['msg'] .= "<br>Invalid file. ".$file['type']." not supported. ";
			}
			return $output;
		}
		
		/**
		 * Will upload a $file to a specific application
		 * 
		 * @param post-data $file
		 * @return string url to the specified location of the $file
		 */
		public static function uploadAppFile($file){
			include 'engine/values/site.values.php';
			$ext = FileHandler::findexts($file['name']);
			$ext = strtolower($ext);
			if(in_array($ext, $allowed_file_type) && $file["size"] < $max_file_size){
				$name = md5($file["tmp_name"])."-".md5(date("U"));
				while(file_exists("mobile/files/".$ext."/".$name.".".$ext)){
					$name = md5($file["tmp_name"])."-".md5(date("U"));
				}
				move_uploaded_file($file["tmp_name"], "mobile/files/".$ext."/".$name.".".$ext);
				$output['success'] = true;
				$output['name'] = "files/".$ext."/".$name.".".$ext;
				return $output;
			}
			$output['success'] = false;
			$output['error'] = "Invalid filetype. type:".$ext;
			return $output;
		}
		
		/**
		 * Will delete a file based on location $file
		 * 
		 * @param string $file
		 * @return boolean based on result
		 */
		public static function delete($file) {
			return unlink($file);
		}
		
		/**
		 * Will find a files fileextension (.apk, .ipa and so on)
		 * 
		 * @param file $filename
		 * @return string file extension
		 */
		private function findexts($filename) { 
			$filename = strtolower($filename) ; 
			$exts = split("[/\\.]", $filename) ; 
			$n = count($exts)-1; 
			$exts = $exts[$n]; 
			return $exts; 
		}

		/**
		 * Function to find out what the highest filesize is possible to upload to the website.
		 * @return int max filesize
		 */
		public static function maxSize() {
		  static $max_size = -1;
		
		  if ($max_size < 0) {
		    $upload_max = ini_get('upload_max_filesize');
		    $post_max = ini_get('post_max_size');
		    $max_size = ($upload_max < $post_max) ? $upload_max : $post_max;
		  }
		  return $max_size;
		}
	}
?>