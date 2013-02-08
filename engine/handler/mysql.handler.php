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
	class SQL {
		
		private $connection;
		public $prefix;
		
		/**
		 * 
		 * Default constructor.
		 * This will establish a connection with the mySQL server and will die when the
		 * script calling on this class 
		 */
		function __construct() {
			if(strstr($_SERVER['PHP_SELF'], "/admin/misc"))
				include '../../engine/values/mysql.values.php';
			else if(strstr($_SERVER['PHP_SELF'], "/mobile/") || strstr($_SERVER['PHP_SELF'], "/xml/") || strstr($_SERVER['PHP_SELF'], "/admin/") || strstr($_SERVER['PHP_SELF'], "/modules/"))
				include '../engine/values/mysql.values.php';
			else
				include 'engine/values/mysql.values.php';
			$this->prefix = $db_prefix;				
			$connection = mysql_connect($db_host, $db_user, $db_password) or die(LayoutManager::redirect("error.html")); 		
			mysql_select_db($db_db);
			mysql_set_charset("utf8", $connection);
			$this->doUpdate("SET `time_zone` = 'Europe/Paris'");
		}
		
		/**
		 * Returns the array of rows in the database whitch corresponds with the SQL statement
		 * @param string $param SQL statement
		 */
		public function getArray($query) {
			$db_query = mysql_query($query);
			// return array with data
			return mysql_fetch_array($db_query);
		}
		
		/**
		 * 
		 * Returns the array of rows in the database whitch corresponds with the SQL statement
		 * @param string $result SQL statement
		 */
		public function getFullArray($query){
			$db_query = mysql_query($query);
		    $table_result=array();
		    $r=0;
		    while($row = mysql_fetch_assoc($db_query)){
		        $arr_row=array();
		        $c=0;
		        while ($c < mysql_num_fields($db_query)) {        
		            $col = mysql_fetch_field($db_query, $c);    
		            $arr_row[$col -> name] = $row[$col -> name];
		            $c++;
		        }    
		        $table_result[$r] = $arr_row;
		        $r++;
		    }    
		    return $table_result;
		}
		
		/**
		 * Returns the numbers of rows in the database whitch corresponds with the SQL statement
		 * @param string $result SQL statement
		 */
		public function getCount($query) {
			$check_result = mysql_query($query);
			return @mysql_num_rows($check_result);
		}
		
		/**
		 * @return Returns the number of rows affected.
		 */
		public function doUpdate($query) {
			$result = mysql_query($query);
			return $result;
		}
		
		/**
		 * 
		 * @return Returns true if connected, false if not
		 */
		public function isConnected() {
			return mysql_ping();
		}
		
		/**
		 * 
		 * @return The last id auto incremented
		 */
		public function getLastId() {
			return mysql_insert_id();
		}
		
		/**
		 * 
		 * @return string The error message
		 */
		public function getError() {
			return mysql_error();
		}
		
		/**
		 * 
		 * Runs a script with sql-commands.
		 * Used when installing the UbiHome server
		 * 
		 * @param string $scriptlocation Location to the .sql file
		 */
		public function run_sql_script($scriptlocation) {
			if ($script = file_get_contents($scriptlocation)) {
		
				$errors = array();
		
				$script = preg_replace('/\-\-.*\n/', '', $script);
				$sql_statements =  preg_split('/;[\n\r]+/', $script);
				foreach($sql_statements as $statement) {
					$statement = trim($statement);
					$statement = str_replace("prefix_", $this->prefix, $statement);
					if (!empty($statement)) {
						try {
							$result = $this->doUpdate($statement);
						} catch (Exception $e) {
							$errors[] = $e->getMessage();
						}
					}
					echo $statement."<br>";
				}
				if (!empty($errors)) {
					$errortxt = "";
					foreach($errors as $error)
						$errortxt .= " {$error};";
					echo "<b>Error</b><br>".$errortxt;
				}
			} else {
				echo "<b>Error</b><br>SQL-Script file not found";
			}
		}
	}

?>