<?php

class DBConn extends MySqli {
	public function __construct($dbname = "trucraft") {
		$host = "172.17.0.6";
		$user = "web_user";
		$pass = "w3bu53r";
		$port = 3306;
		parent::__construct($host, $user, $pass, $dbname, $port);
	}
	
	public function query($query) {
		$result = parent::query($query);
		
		if(strlen($this->error)) {
			throw new Exception($this->error);
		}
		
		return $result;
	}
}
