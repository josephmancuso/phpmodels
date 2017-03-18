<?php

abstract class Model{
	
	// CODE FOR CREATING DATABSES AND TABLES
	public $create = null;
	
	// CODE FOR MODEL FUNCTIONALITY
	public $database = null;
	public $arr = array();
	public $update = array();
	public $pdo = array(
		'username' => 'username',
		'password' => 'password'
	);
	
	
	function __construct($default_database){
		if (!$default_database){
			echo("DID NOT SUPPLY DATABASE INSIDE INSTATIATED CLASS");
		}
		$this->database = $default_database;
		
		// check if database exists
		$handler = new PDO('mysql:host=localhost;dbname='.$this->database,$this->pdo['username'],$this->pdo['password']);
		$table = static::class;
		$query = $handler->query("SHOW TABLES LIKE '$table' ");
		
		// query the database
		// get the columns and echo them
		
		if ($this->create == true && $query->rowCount()){
			// the table exists and create is true. try and add the columns
			$query = $handler->query("SELECT * FROM $table WHERE 1");
			$get_column = $query->GetColumnMeta(1);
			
			// match the get column ['name'] to each element in the array.
			$variables = get_class_vars(static::class);
			
			
			foreach($variables as $variable){
				
				$get_column = $query->GetColumnMeta($i);
				
				if ($variable){
					//if ($get_column['name'] == array_keys($variables)[$i+1] ){
					if (in_array($get_column['name'], array_keys($variables), true)){
						
						
					}else{
						// add the columns to the table
						$column_name = array_keys($variables)[$i+1];
						$data_type = array_values($variables)[$i+1];
						$old_column = $get_column['name'];
						
						if ($data_type == "varchar"){
							$data_type = "VARCHAR (255)";
						}else if ($data_type == "int"){
							$data_type = "INT (11)";
						}
						
						// modify table
						if ($handler->query("ALTER TABLE $table CHANGE $old_column $column_name $data_type")){
							
						}else{
							
							if ($data_type == "varchar"){
								$data_type = "VARCHAR (255)";
							}else if ($data_type == "int"){
								$data_type = "INT (11)";
							}
							
							
							$handler->query("ALTER TABLE $table ADD $column_name $data_type");
							
						}
						// add column
						
						
					}
					$i++;
				}
			}
		}
		
		
		if (!$query->rowCount() && $this->create == true){
			// check if database exists
			$handler = new PDO('mysql:host=localhost;dbname='.$this->database,$this->pdo['username'],$this->pdo['password']);
			$table = static::class;
			$query = $handler->query("SHOW TABLES LIKE '$table' ");
			
			// Construct a query
			$testing = get_class_vars(static::class);
			$query_s = "";
			$query_s .= "CREATE TABLE $table ( ";
			foreach($testing as $key => $value){
				if ($value === "id"){
					$query_s .= $key ." INT (11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, ";
				}
				else if ($value === "int"){
					$query_s .= $key ." INT (11), ";
				}else if ($value === "varchar"){
					$query_s .= $key ." VARCHAR (255) NOT NULL,";
				}
			}
			$query_s = rtrim($query_s, ",");
			$query_s .= ")";
			
			// table does not exist, create the table
			$handler->query($query_s);
				
			
			
		}
		return static::class;
	}
	
	function setDatabase($database){
		$this->$database = $database;
		return true;
	}
	
	function __call($method, $value){
		//remove set from the variable
		$choice = explode("_", $method);
		
		if ($choice[0] == "update"){
			
			// This is an update call
			
			$method = explode("update", $method);
			$method = strtolower(substr_replace($method[1], ":", 0, 0));
			$method = str_replace("_", "", $method);
			$this->update[$method] = $value[0];
		}else{
			
			// This is an set call to be inserted into a database.
			
			$method = explode("set", $method);
			$method = strtolower(substr_replace($method[1], ":", 0, 0));
			$method = str_replace("_", "", $method);
			
			
			
			$this->arr[$method] = $value[0];
		}
		
	}
	
	#### CREATE
	
	public function insert(){
		
		foreach(array_keys($this->arr) as $key){
			$newkey .= " ".$key;
		}
		$cols = str_replace(":", "", trim($newkey));
		$cols = str_replace(" ", ",", $cols);
		
		$newkey = str_replace(" ", ",", trim($newkey));		
		
		$handler = new PDO('mysql:host=localhost;dbname='.$this->database,$this->pdo['username'],$this->pdo['password']);
		$table = static::class;
		$view_query = "INSERT INTO `$table` ($cols) VALUES($newkey)";
		return $query = $handler->prepare("INSERT INTO `$table` ($cols) VALUES($newkey)");
		
	}	
	
	#### READ
	
	function all(){
		$handler = new PDO('mysql:host=localhost;dbname='.$this->database,$this->pdo['username'],$this->pdo['password']);
		$table = static::class;
		$query = $handler->query("SELECT * FROM `$table` WHERE 1");
		
		return $query->fetchAll();
		
	}
	
	function get($where_clause = 1){
		$handler = new PDO('mysql:host=localhost;dbname='.$this->database,$this->pdo['username'],$this->pdo['password']);
		
		$table = static::class;
		$query = $handler->query('SELECT * FROM `'.$table.'` WHERE '.$where_clause.' LIMIT 1');
		
		return $query->fetchAll();
		
	}
	
	function filter($where_clause = 1){
		$handler = new PDO('mysql:host=localhost;dbname='.$this->database,$this->pdo['username'],$this->pdo['password']);
		
		$table = static::class;
		$query = $handler->query('SELECT * FROM `'.$table.'` WHERE '.$where_clause.'');
		
		return $query->fetchAll();
		
	}
	
	#### UPDATE
	
	function update($where_clause = 1){
		foreach($this->update as $key => $value){
			$values .= "`".$key."` = '".$value."',";
		}
		
		$val = str_replace(":", "", $values);
		$val = rtrim($val, ",");
	
		$handler = new PDO('mysql:host=localhost;dbname='.$this->database,$this->pdo['username'],$this->pdo['password']);
		$table = static::class;
		return $query = $handler->prepare("UPDATE `$table` SET $val WHERE $where_clause LIMIT 1");
		
	}
	
	#### DELETE
	
	function delete($where_clause = 1){
		$handler = new PDO('mysql:host=localhost;dbname='.$this->database,$this->pdo['username'],$this->pdo['password']);
		$table = static::class;
		return $query = $handler->prepare("DELETE FROM `$table` WHERE $where_clause LIMIT 1");
		
	}
	
	function deleteAll($where_clause = 1){
		$handler = new PDO('mysql:host=localhost;dbname='.$this->database,$this->pdo['username'],$this->pdo['password']);
		$table = static::class;
		return $query = $handler->prepare("DELETE FROM `$table` WHERE $where_clause ");
		
	}
	
	function confirm(){
		try{
			$this->delete()->execute();
			return true;
		}catch(Exception $e){
			return false;
		}
	}
	
	function confirmAll(){
		try{
			$this->deleteAll()->execute();
			return true;
		}catch(Exception $e){
			return false;
		}
	}
	
	
	
	
	function save(){
		try{
			if (!empty($this->arr)){
				$this->insert()->execute($this->arr);
			}
			
			if (!empty($this->update)){
				$this->update()->execute($this->update);
			}
			$this->arr = array();
			$this->update = array();
			return true;
		}catch(Exception $e){
			return false;
		}
	}
	
	function works(){
		echo "works";		
	}
	
	function viewSave(){
		print_r($this->arr);
	}
	
	function viewUpdate(){
		print_r($this->update);
	}
	
	function viewQuery(){
		echo $this->include()->view_query;
	}
}


?>