<?php
//namespace lib\util;

class passwordHash {
	private static $algo = '$2a';
	private static $cost = '$10';

	public static function unique_salt(){
		return substr(sha1(mt_rand()), 0, 22);
	}

	public static function hash($password){
		$salt = self::$algo . self::$cost . '$' . self::unique_salt();
		return crypt($password, $salt);
	}

	public static function check($hash, $password){
		$full_salt = substr($hash, 0, 29);
		$new_hash = crypt($password, $full_salt);
		return ($hash == $new_hash);
	}
}
