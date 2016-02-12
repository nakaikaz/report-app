<?php
//namespace lib\db;

//class ImageDB extends \lib\db\DbHandler {
class ImageDB extends DbHandler {

	function __construct($host, $name, $user, $password){
		parent::__construct($host, $name, $user, $password);
	}

	public function fetchAll(){
		$sql = 'select * FROM images';
		$stmt = $this->conn->query($sql);
		$result = $stmt->fetchAll(\PDO::FETCH_OBJ);
		return $result;
	}

	public function fetchByEmail($email){
		$sql = 'SELECT * FROM images WHERE email=:email';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('email', $email);
		$result = $stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_OBJ);
		return $result;
	}

	public function fetchAllByUserId($id){
		$sql = 'SELECT i.*, u.email FROM images i LEFT JOIN users u ON i.user_id=u.id WHERE u.id=:id';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('id', $id);
		$result = $stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_OBJ);
		return $result;
	}

	public function fetchByImagesIdAndEmail($id, $email){
		$sql = 'SELECT i.*, u.email, u.name FROM images i LEFT JOIN users u ON u.id=i.user_id WHERE i.id=:id AND u.email=:email';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('id', $id);
		$stmt->bindParam('email', $email);
		$result = $stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_OBJ);
		return $result;
	}

	public function insert($user_id, $email, $paths, $description) {
		$sql = 'INSERT INTO images (`user_id`, `email`, `paths`, `description`) VALUES (:user_id, :email, :paths, :description)';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('user_id', $user_id);
		$stmt->bindParam('email', $email);
		$stmt->bindParam('paths', $paths);
		$stmt->bindParam('description', $description);
		$result = $stmt->execute();
		return $result;
	}

	public function update($id, $params = array()){
		$sql = 'UPDATE images SET user_id=:user_id, email=:email, paths=:paths, description=:description, date=:date WHERE id=:id';
		$images = $this->fetchByImagesIdAndUserEmail($id, $params['email']);
		if($images){
			if(isset($params['user_id'])) $images['user_id'] = $params['user_id'];
			if(isset($params['email'])) $images['email'] = $params['email'];
			if(isset($params['paths'])) $images['paths'] = $params['paths'];
			if(isset($params['description'])) $images['description'] = $params['description'];
			if(isset($params['date'])) $images['date'] = $params['date'];
			$stmt = $this->conn->prepare($sql);
			$stmt->bindParam('id', $id);
			$stmt->bindParam('user_id', $images['user_id']);
			$stmt->bindParam('email', $images['email']);
			$stmt->bindParam('description', $images['description']);
			$stmt->bindParam('date', $images['date']);
			$result = $stmt->execute();
		}else{
			$result = null;
		}
		return $result;
	}

	public function lastInsertId(){
		return $this->conn->lastInsertId();
	}
}
