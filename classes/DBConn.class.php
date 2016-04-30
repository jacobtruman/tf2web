<?php

class DBConn extends MySqli {
	public function __construct($dbname = "trucraft") {
		parent::__construct('localhost', '#####', '#####', $dbname);
	}
	
	public function query($query) {
		$result = parent::query($query);
		
		if(strlen($this->error)) {
			throw new CustomException($this->error);
		}
		
		return $result;
	}
}

?>
