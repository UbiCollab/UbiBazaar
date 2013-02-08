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
	class Error {
		
		/**
		 * Will output an error message based on $error.
		 * 
		 * @param string $error
		 */
		public static function printError($error) {
			// Jquery fix for making it able to show ' in sentences
			$error = $error." Click here to dismiss this message.";
			$error = str_replace("'","\'",$error);
			return "\n<script type=\"text/javascript\"> 
				$(document).ready(function() {
					$('#error_text').html('$error');
		        	$('#overlay').fadeIn('fast',function(){
		        		$('#box').animate({'top':'160px'},500);
		        		setTimeout(\"$('#box').click();\", 6000);
			        });
			    });
			</script>";
		}
		
		function __destruct() {
	    	
		}
	}
?>