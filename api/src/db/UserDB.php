<?php
//namespace lib\db;
require_once 'DbHandler.php';

//class UserDB extends \lib\db\DbHandler {
class UserDB extends DbHandler {

	private static $algo = '$2a';
	private static $cost = '$10';

	function __construct($host, $name, $user, $password){
		parent::__construct($host, $name, $user, $password);
	}

	public function insert($email, $name, $password){
		$sql = 'INSERT INTO users (`email`, `name`, `password`) VALUES (:email, :name, :password)';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('email', $email);
		$stmt->bindParam('name', $name);
		$stmt->bindParam('password', $password);
		$r = $stmt->execute();

		if($r){
			$id = $this->conn->lastInsertId();
			return $id;
		}
		return null;
	}

	public function fetchByEmail($email){
		$sql = 'SELECT * FROM users WHERE email=:email';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('email', $email);
		$stmt->execute();
		$user = $stmt->fetchObject();
		return $user;
	}

	public function isUniqueUserEmail($email){
		$sql = 'SELECT COUNT(id) as count FROM users WHERE email=:email';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('email', $email);
		$stmt->execute();
		$obj = $stmt->fetchObject();
		if($obj->count === '0'){
			return true;
		}
		return false;
	}

	public static function unique_salt(){
		return substr(sha1(mt_rand()), 0, 22);
	}

	public static function hash($password){
		$salt = self::$algo . self::$cost . '$' . self::unique_salt();
		return crypt($password, $salt);
	}

	public static function check_password($hash, $password){
		$full_salt = substr($hash, 0, 29);
		$new_hash = crypt($password, $full_salt);
		return ($hash == $new_hash);
	}
}
