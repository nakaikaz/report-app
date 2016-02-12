<?php
//namespace lib\db;

class DbHandler {

	protected $conn;

	function __construct($host, $name, $user, $password){
		$this->conn = new \PDO('mysql:host=' . $host . ';dbname=' . $name, $user, $password);
		$this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	function __destruct(){
		unset($this->conn);
	}
}
