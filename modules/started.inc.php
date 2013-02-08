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
?>
<h2>Get started!</h2>
<p> First you need to download the Android application, use the QR code below, then you need to register an account on the application.
 You can now start using the application, congratulation!</p>
<?
 	function google_qr($url,$size ='130',$EC_level='M',$margin='0') {
		$url = urlencode($url); 
		echo '<img src="http://chart.apis.google.com/chart?chs='.$size.'x'.$size.'&cht=qr&chld='.$EC_level.'|'.$margin.'&chl='.$url.'" alt="QR code" width="'.$size.'" height="'.$size.'"/>';
	}
	echo google_qr(LayoutManager::curURL()."mobile/files/apk/UbiHome-12052011-v2.0.apk");
	echo "<br>";
	echo "<a href=\"mobile/files/apk/UbiHome-12052011-v2.0.apk\">Click to download</a>"
?>