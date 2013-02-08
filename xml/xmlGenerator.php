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
 
header("Content-type: text/xml");
 
include '../engine/handler/mysql.handler.php';
include '../engine/handler/error.handler.php';
include '../engine/handler/layout.handler.php';
 

if($_GET['id'] != "" && eregi("^[0-9]{1,11}$", $_GET['id'])){
	// Get an sql connection
	$sql = new SQL();
	
	// FUNKER
	$query = "SELECT au.`revision_nr`, au.`url`, au.`released`, au.`changelog`, p.name AS platform
	 		  FROM Platform p, ApplicationUpdates au
	 		  WHERE p.platform_id = au.platform AND au.app_id = '".$_GET['id']."'";
	$db_array = $sql->getFullArray($query);
	 
	 //FUNKER
	$query1 = "SELECT DISTINCT t.`tag`
			FROM Tag t, ApplicationTag at, Application a
			WHERE t.tag_id = at.tag_id AND at.app_id = a.app_id AND a.app_id = '".$_GET['id']."'";
	$db_array1 = $sql->getFullArray($query1);
	
	//FUNKER
	$query2 = "SELECT `name`,`desc`, dependencies
		     FROM Application
			 WHERE app_id ='".$_GET['id']."'";
	$db_array2 = $sql->getArray($query2);
	
	//FUNKER
	$query3 = "SELECT * FROM User WHERE user_id IN (SELECT user_id FROM UserApplication WHERE app_id = '".$_GET['id']."')";
	$db_array3 = $sql->getArray($query3);
	
	$dependencies = explode(",", $db_array2['dependencies']);
	
	// START XML<
	$xml_output = "<?xml version=\"1.0\"?>\n"; 
	$xml_output = "<!DOCTYPE application SYSTEM '".LayoutManager::curURL()."xml/application.dtd'>\n";	
	$xml_output .= "<application>\n";
	$xml_output .= "\t<name>" . $db_array2['name'] . "</name>\n";
	
	// description
	$xml_output .= "\t<description>\n";
	$xml_output .= "\t\t" . $db_array2['desc'] . "\n";
	$xml_output .= "\t</description>\n";
	
	// misc information
	$xml_output .= "\t<developer>" . $db_array3['name']  . "</developer>\n";
	
	// Releases
	foreach ($db_array as $release) {
		$xml_output .= "\t<release>\n";
		$xml_output .= "\t\t<release_date>".$release['released']."</release_date>\n";
		$xml_output .= "\t\t<platform>".$release['platform']."</platform>\n";
		// print changelogs
		$xml_output .= "\t\t<changelog>\n";
		$xml_output .= "\t\t".$release['changelog']."\n";
		$xml_output .= "\t\t</changelog>\n";
		$xml_output .= "\t\t<version>".$release['revision_nr']."</version>\n";
		$xml_output .= "\t\t<file_url>".LayoutManager::curURL()."mobile/".$release['url']."</file_url>\n";
		$xml_output .= "\t</release>\n";
	}
	

	// print tags
	if(count($db_array1)>0){
		$xml_output .= "\t<tags>\n";
		for($i = 0 ; $i < count($db_array1)  ; $i++){
			$xml_output .= "\t\t<tag>" .$db_array1[$i]['tag']. "</tag>\n";
		}
		$xml_output .= "\t</tags>\n";
	}
	
	// print dependencies
	if(count($dependencies)>0 && $dependencies[0] != ""){
		$xml_output .= "\t<dependencies>\n";
		for($i = 0 ; $i < count($dependencies)  ; $i++){
			$xml_output .= "\t\t<dependency>" .htmlspecialchars($dependencies[$i]). "</dependency>\n";
		}
		$xml_output .= "\t</dependencies>\n";
	}
	// END XML
	$xml_output .= "</application>";
	
	echo $xml_output;
	 
	 
	//SKRIVER UT SHAIT TIL FIL
	$file = "appData.xml";
	$handle = fopen($file, 'w') or die("can't open file");
	fwrite($handle, $xml_output);
	fclose($handle);
}
?>
