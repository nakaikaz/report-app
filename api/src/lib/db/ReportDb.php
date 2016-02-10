<?php
namespace lib\db;

class ReportDB extends \lib\db\DbHandler {

	function __construct($host, $name, $user, $password){
		parent::__construct($host, $name, $user, $password);
	}

	public function fetchAll(){
		$sql = 'select * FROM reports';
		$stmt = $this->conn->query($sql);
		$result = $stmt->fetchAll(\PDO::FETCH_OBJ);
		return $result;
	}

	public function fetchByEmail($email){
		$sql = 'SELECT * FROM reports WHERE email=:email';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('email', $email);
		$result = $stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_OBJ);
		return $result;
	}

	public function fetchAllByUserId($id){
		$sql = 'SELECT r.*, u.email FROM reports r LEFT JOIN users u ON r.user_id=u.id WHERE u.id=:id';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('id', $id);
		$result = $stmt->execute();
		$result = $stmt->fetchAll(\PDO::FETCH_OBJ);
		return $result;
	}

	public function fetchByReportIdAndUserEmail($id, $email){
		$sql = 'SELECT r.*, u.email, u.name FROM reports r LEFT JOIN users u ON u.id=r.user_id WHERE r.id=:id AND u.email=:email';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('id', $id);
		$stmt->bindParam('email', $email);
		$result = $stmt->execute();
		$result = $stmt->fetch(\PDO::FETCH_OBJ);
		return $result;
	}

	public function insert($user_id, $title, $content, $images, $thumbnail) {
		$sql = 'INSERT INTO reports (`user_id`, `title`, `content`, `images`, `thumbnail`, `created_at`, `updated_at`) VALUES (:user_id, :title, :content, :images, :thumbnail, :created_at, :updated_at)';
		$stmt = $this->conn->prepare($sql);
		$stmt->bindParam('user_id', $user_id);
		$stmt->bindParam('title', $title);
		$stmt->bindParam('content', $content);
		$stmt->bindParam('images', $images);
		$stmt->bindParam('thumbnail', $thumbnail);
		$now = new \DateTime();
		$now = $now->format('Y-m-d H:i:s');
		$stmt->bindParam('created_at', $now);
		$stmt->bindParam('updated_at', $now);
		$result = $stmt->execute();
		return $result;
	}

	public function update($id, $params = array()){
		$sql = 'UPDATE reports SET user_id=:user_id, title=:title, content=:content, images=:images, updated_at=:updated_at WHERE id=:id';
		$r = $this->fetchByReportIdAndUserEmail($id, $params['email']);
		if($r){
			if(isset($params['user_id'])) $r->user_id = $params['user_id'];
			if(isset($params['title'])) $r->title = $params['title'];
			if(isset($params['content'])) $r->content = $params['content'];
			if(isset($params['images'])) $r->images = $params['images'];
			$stmt = $this->conn->prepare($sql);
			$stmt->bindParam('id', $id);
			$stmt->bindParam('user_id', $r->user_id);
			$stmt->bindParam('title', $r->title);
			$stmt->bindParam('content', $r->content);
			$stmt->bindParam('images', $r->images);
			$now = new \DateTime();
			$updated_at = $now->format('Y-m-d H:i:s');
			$stmt->bindParam('updated_at', $updated_at);
			$result = $stmt->execute();
		}else{
			$result = null;
		}
		return $result;
	}

	const SQL_SELECT = 'SELECT * FROM reports WHERE id=:id';
	const SQL_DELETE = 'DELETE FROM reports WHERE id=:id';

	public function delete($id, &$deleted){
		//$sql_select = 'SELECT * FROM reports WHERE id=:id';
		//$sql_delete = 'DELETE FROM reports WHERE id=:id';
		$stmt = $this->conn->prepare(self::SQL_SELECT);
		$stmt->bindParam('id', $id);
		$stmt->execute();
		$deleted = $stmt->fetchObject();
		if($deleted){
			$stmt = $this->conn->prepare(self::SQL_DELETE);
			$stmt->bindParam('id', $id);
			$result = $stmt->execute();
		}else{
			$result = false;
		}
		return $result;
	}

	public function lastInsertId(){
		return $this->conn->lastInsertId();
	}
}
