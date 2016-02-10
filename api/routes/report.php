<?php

define('IMAGE_DIR', '../uploaded_images/');

function makeHash($email){
	$now = date('Ymd');
	$hash = sha1($email . $now);

	return $hash;
}

$app->get('/reports', function() use($app){
	try {
		$session = new \lib\Session();
		$user_id = $session->get('id');
		$email = $session->get('email');
		$db = new \lib\db\ReportDB(
			$app->config('db_host'),
			$app->config('db_name'),
			$app->config('db_user'),
			$app->config('db_password')
		);
		$reports = $db->fetchAllByUserId($user_id);
		foreach($reports as $report){
			$arr = array();
			$images = unserialize($report->images);
			if($images){
				foreach($images as $image){
					$obj = new stdClass();
					$obj->src = IMAGE_DIR . $email . '/' . $image->name;
					$obj->memo = $image->memo;
					$arr[] = $obj;
				}
				$report->images = $arr;
			}
			/*for($i = 0; $i < count($images); $i++){
				$images[$i] = 'api/uploaded_images/' . $images[$i];
			}
			$report->images = $images;
			# サムネイルのパスも追加
			$path_info = pathinfo($images[0]);
			$dir = $path_info['dirname'];
			$filename = $path_info['filename'];
			$extension = $path_info['extension'];
			$thumbnail = $dir . '/'. $filename . '.thumb.' . $extension;
			$report->thumbnail = $thumbnail;*/
			if($report->thumbnail){
				$report->thumbnail = IMAGE_DIR . $email . '/' . $report->thumbnail;
			}
		}
		$ret = array('status' => true, 'reports' => $reports);
		echoResponse(200, $ret);
	} catch(PDOException $e) {
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$ret = array('status' => false, 'message' => $e->getMessage());
		echoResponse(400, $ret);
	}
});

$app->get('/reports/:id', function($id) use($app){
	try {
		$session = new \lib\Session();
		$email = $session->get('email');
		$db = new \lib\db\ReportDB(
			$app->config('db_host'),
			$app->config('db_name'),
			$app->config('db_user'),
			$app->config('db_password')
		);
		$r = $db->fetchByReportIdAndUserEmail($id, $email);
		$images = unserialize($r->images);
		$arr = array();
		foreach($images as $image){
			$obj = new stdClass();
			$obj->name = 'uploaded_images/' . $email . '/' . $image->name;
			$obj->memo = $image->memo;
			$arr[] = $obj;
		}
		$r->images = $arr;
		$r->thumbnail = 'uploaded_images/' . $email . '/'. $r->thumbnail;
		$ret = array('status' => true, 'reports' => $r);
		echoResponse(200, $ret);
	}catch(PDOException $e){
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$ret = array('status' => false, 'message' => $e->getMessage());
		echoResponse(400, $ret);
	}
});

$app->post('/report', function() use ($app) {
	$body = $app->request()->getBody();
	$r = json_decode($body);
	# 画像ファイルが配列で送信されていたら、「;」でつなぎ合わせた一つの文字列としてDBへ挿入する
	if(count($r->images) > 0){
		// image->srcは画像データそのものなので削除
		foreach($r->images as $image){
			unset($image->src);
		}
		$thumbnail = $r->images[0];
		$r->images = serialize($r->images);
		# 配列一つ目の画像は、縮小してサムネイルとして保存する
		$src_path = $thumbnail->name;
		# ハッシュを求める
		$pos = strrpos($src_path, '/');
		$hash = substr($src_path, 0, $pos);
		$pathinfo = pathinfo($src_path);
		$filename = $pathinfo['filename'];
		$extension = $pathinfo['extension'];
		$thumbnailname = $filename . '.thumb.' . $extension;
		$dst_path = IMAGE_DIR . $r->user->email . '/' . $hash . '/' . $thumbnailname;
		$src_path = IMAGE_DIR . $r->user->email . '/' . $src_path;
		\lib\util\Image::copy($src_path, $dst_path, array('width' => 100, 'height' => 100));
		$thumbnailpath = $hash . '/' . $thumbnailname;
	}else {
		$r->images = null;
		$thumbnailname = null;
		$thumbnailpath = null;
	}

	try {
		$db = new \lib\db\ReportDB(
			$app->config('db_host'),
			$app->config('db_name'),
			$app->config('db_user'),
			$app->config('db_password')
		);
		$db->insert($r->user->id, $r->title, $r->content, $r->images, $thumbnailpath);
		$ret = array(
			'status' => true,
			'report' => array(
				'id' => $db->lastInsertId(),
				'title' => $r->title,
				'content' => $r->content,
				'images' => $r->images,
				'thumbnail' => $thumbnailname
			)
		);
		echoResponse(200, $ret);
	}catch(PDOException $e) {
		$app->response()->status(500);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$ret = array('status' => false, 'message' => $e->getMessage());
		echoResponse(500, $ret);
	}catch(Exception $e){
		$app->response()->status(500);
		$app->response()->header('X-Status-Reason', $e->getMessage());
		$ret = array('status' => false, 'error' => $e->getMessage());
		echoResponse(500, $ret);
	}
});

$app->post('/report/images', function() use($app){
	try {
		if(!isset($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'])){
			throw new Exception('an image file doesn\'t exist.');
		}
		$desc = $app->request()->post('desc');
		$user = $app->request()->post('user');
		if(!isset($user)){
			throw new Exception('user isn\'t set.');
		}

		$image_name = $_FILES['image']['name'];
		$image_size = $_FILES['image']['size'];
		$image_tmp = $_FILES['image']['tmp_name'];

		$output_dir = IMAGE_DIR . $user . '/';
		// IMAGE_DIR/$userディレクトリが無ければ作成
		if(!is_dir($output_dir)){
			mkdir($output_dir, 0755, true);
			chgrp($output_dir, '_www');
		}
		// 出力ファイルのパスを作成
		$hash = makeHash($user);
		$output_dir = $output_dir . $hash . '/';
		// ./uploaded_images/$user/$hash/ディレクトリが無ければ作成
		if(!is_dir($output_dir)){
			mkdir($output_dir, 0755, true);
			chgrp($output_dir, '_www');
		}
		$image_info = pathinfo($image_name);
		$image_extension = strtolower($image_info['extension']);
		$image_name_only = strtolower($image_info['filename']);
		$dst_path = $output_dir . $image_name_only . '.' . $image_extension;

		// 画像サイズ、タイプを取得
		$image_info = getimagesize($image_tmp);
		$src_width = $image_info[0];
		$src_height = $image_info[1];
		$image_type = $image_info['mime'];
		$dst_width = $src_width * 1;
		$dst_height = $src_height * 1;

		// 画像リソースを取得
		switch($image_type) {
		case 'image/png':
			$src_res = imagecreatefrompng($image_tmp);
			break;
		case 'image/gif':
			$src_res = imagecreatefromgif($image_tmp);
			break;
		case 'image/jpeg':
		case 'image/pjpeg':
			$src_res = imagecreatefromjpeg($image_tmp);
			break;
		default:
			$src_res = false;
		}

		// TrueColorイメージを新規作成
		$dst_res = imagecreatetruecolor($dst_width, $dst_height);
		// 新規作成したイメージキャンパスに元イメージをコピー
		imagecopyresampled($dst_res, $src_res, 0, 0, 0, 0, $dst_width, $dst_height, $src_width, $src_height);

		// 画像の保存
		switch($image_type){
		case 'image/png':
			$ret = imagepng($dst_res, $dst_path);
			break;
		case 'image/gif':
		$ret = imagegif($dst_res, $dst_path);
			break;
		case 'image/jpeg':
		case 'image/gif':
			$ret = imagejpeg($dst_res, $dst_path);
			break;
		default:
			$ret = false;
		}

		imagedestroy($dst_res);

		if(!$ret) {
			throw new Exception('failed create an image');
		}

		$session = new \lib\Session();
		$email = $session->get('email');
		$full_path = IMAGE_DIR . $email . '/' . $hash . '/' . $image_name_only . '.' . $image_extension;
		$name = $hash . '/' . $image_name_only . '.' . $image_extension;
		$image = array('name' => $name, 'fullpath' => $full_path, 'memo' => '');
		$ret = array('status' => true, 'image' => $image);
		echoResponse(200, $ret);
	} catch(Exception $e){
		$ret = array('status' => false, 'message' => $e->getMessage());
		echoResponse(500, $ret);
	}
});

$app->delete('/report/image/:hash/:name', function($hash, $imageName) use($app) {
	try{
		if(empty($hash) || empty($imageName)){
			throw new Exception('the hash or the name of image isn\'t set.');
		}

		$session = new \lib\Session();
		$email = $session->get('email');

		// 削除対象ファイルのパスを作成
		$imagePath = IMAGE_DIR . $email . '/' . $hash . '/' . $imageName;
		if(!is_file($imagePath)){
			throw new Exception('the image dosen\'t exist.');
		}
		if(!unlink($imagePath)){
			throw new Exception('the image couldn\'t removed.');
		}
		$ret = array('status' => true, 'message' => 'successfully removed.');
		echoResponse(200, $ret);
	}catch(Exception $e){
		$ret = array('status' => false, 'message' => $e->getMessage());
		echoResponse(500, $ret);
	}
});

$app->delete('/report/:id/images/:dir/:email/:hash/:name', function($id, $dir, $email, $hash, $name) use($app){
	try{
		//$session = new \lib\Session();
		//$email = $session->get('email');

		//$db = new \lib\db\ReportDB();
		//$r = $db->fetchByReportIdAndUserEmail($id, $email);
		//if(isset($r)){
			//$images = unserialize($r->images);
			// 削除対象ファイルのパスを作成
			$image_path = '../' . $dir  . '/' . $email . '/' . $hash . '/' . $name;
			//$imagePath = IMAGE_DIR . $email . '/' . $images[(int)$index]->name;
			if(!is_file($image_path)){
				throw new Exception('the image dosen\'t exist.');
			}
			if(!unlink($image_path)){
				throw new Exception('the image couldn\'t removed.');
			}
		//}
		echoResponse(200, array('status' => true));
	}catch(Exception $e){
		$ret = array('status' => false, 'message' => $e->getMessage());
		echoResponse(500, $ret);
	}
});

$app->delete('/reports/:id', function($id) use($app){
	try {
		$report = null;
		// レポートを削除
		$db = new \lib\db\ReportDB(
			$app->config('db_host'),
			$app->config('db_name'),
			$app->config('db_user'),
			$app->config('db_password')
		);
		$db->delete($id, $report);
		// 保存されている画像も削除
		$session = new \lib\Session();
		$email = $session->get('email');
		$images = unserialize($report->images);
		foreach($images as $image){
			$image_path = IMAGE_DIR . $email . '/' . $image->name;
			$done = unlink($image_path);
		}
		$ret = array('status' => true);
		echoResponse(200, $ret);
	}catch(PDOException $e) {
		$app->response->headers->set('X-Status-Reason', $e->getMessage());
		$ret = array('status' => false, 'message' => $e->getMessage());
		echoResponse(500, $ret);
	}catch(Exception $e){
		$app->response->headers->set('X-Status-Reason', $e->getMessage());
		$ret = array('status' => false, 'error' => $e->getMessage());
		echoResponse(500, $ret);
	}
});

$app->put('/reports/:id', function($id) use($app) {
	$body = $app->request->getBody();
	$r = json_decode($body);
	$session = new \lib\Session();
	$email = $session->get('email');
	$arr = array();
	if(isset($r->images)){
		foreach($r->images as $image){
			$obj = new stdClass();
			$pieces = explode('/', $image->name);
			if($pieces[0] == '..'){
				$obj->name =$pieces[3] . '/' . $pieces[4];
				$obj->memo = $image->memo;
			}else{
				$obj->name = $pieces[2] . '/' . $pieces[3];
				$obj->memo = $image->memo;
			}
			$arr[] = $obj;
			}
		$r->images = serialize($arr);
	}
	try {
		$db = new \lib\db\ReportDB(
			$app->config('db_host'),
			$app->config('db_name'),
			$app->config('db_user'),
			$app->config('db_password')
		);
		// TODO $r->images もアップデートする
		$db->update($id, array(
			'user_id' => $r->user->id,
			'title' => $r->title,
			'content' => $r->content,
			'images' => $r->images,
			'email' => $r->user->email
			)
		);
		$ret = array(
			'status' => true,
			'report' => array(
				'id' => $db->lastInsertId(),
				'user_id' => $r->user->id,
				'title' => $r->title,
				'content' => $r->content,
				'images' => $r->images
			)
		);
		echoResponse(200, $ret);
	}catch(PDOException $e) {
		$app->response->headers->set('X-Status-Reason', $e->getMessage());
		$ret = array('status' => false, 'message' => $e->getMessage());
		echoResponse(500, $ret);
	}catch(Exception $e){
		$app->response->headers->set('X-Status-Reason', $e->getMessage());
		$ret = array('status' => false, 'error' => $e->getMessage());
		echoResponse(500, $ret);
	}
});
