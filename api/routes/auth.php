<?php
require_once dirname(__FILE__) . '/../src/Session.php';
require_once dirname(__FILE__) . '/../src/db/UserDB.php';

$app->get('/session', function() use($app){
	$session = new Session();
	$response = array();
	$response['id'] = $session->get('id');
	$response['name'] = $session->get('name');
	$response['email'] = $session->get('email');
	echoResponse(200, $response);
});

$app->post('/presignup', function() use($app){
    $response = array();
    $r = json_decode($app->request->getBody());
	$email = $r->user->email;
	try {
		verifyRequiredParams(array('email'), $r->user);
		$db = new UserDB($app->config('db_host'), $app->config('db_name'), $app->config('db_user'), $app->config('db_password'));
		$userExists = $db->fetchByEmail($email);
		if($userExists){
			$response = array(
				'status' => false,
				'message' => 'The user with the provided email exists!'
			);
			throw new AppException(500, $response);
		}
		// 本登録するための一時URLを作成
		$token = sha1($email . session_id() . microtime());
		// トークンを名前にしたテキストファイルをトークンディレクトリに作成
		$tokenFile = dirname(__FILE__) . '/../../token/' . $token;
		if(!touch($tokenFile)){
			$response = array(
				'status' => false,
				'message' => 'failed to create a token file.'
			);
			throw new AppException(500, $response);
		}
		// とりあえず１時間以内でテスト
		$limit = time() + 3600;
		// $limit = time() + (7 * 24 * 60 * 60);
		if(!file_put_contents($tokenFile, $limit, LOCK_EX)){
			$response = array(
				'status' => false,
				'message' => 'failed to create a token file.'
			);
			throw new AppException(500, $response);
		}
		$urlForRegister = 'http://'.$_SERVER['HTTP_HOST'].'/'.$app->config('app_name').'/#/account/signup?t='.$token;
		require dirname(__FILE__) . '/../src/email.php';
		sendPreSignupEmail($email, $urlForRegister);
		$response = array(
			'status' => true,
			'message' => 'You can proceed next step.'
		);
		echoResponse(200, $response);
	}catch(AppException $e){
		echoResponse($e->getStatusCode(), $e->getResponse());
	}
});

$app->get('/signup', function() use($app){
	$response = array();
    // パラメータからトークンを取得
	$token = $app->request->get('t');
	// トークンディレクトリから同名ファイルを開く
    $tokenFile = dirname(__FILE__) . '/../../token/' . $token;
    try {
        if(!is_file($tokenFile)){
            $response = array(
                'status' => false,
                'canSignup' => false,
                'message' => 'The token file is not found'
            );
            throw new AppException(500, $response);
        }
        $data = file_get_contents($tokenFile);
        // ファイル作成日＋期限と現在時刻の比較。期限内なら登録フォームを表示する。
        if(time() > $data){
            $response = array(
                'status' => true,
                'canSignup' => false,
                'message' => 'expired.'
            );
            throw new AppException(200, $response);
        }
        $response = array(
            'status' => true,
            'canSignup' => true,
            'token' => $token,
            'message' => 'You can register now!'
        );
        // ユーザー登録が完了した時点でトークンファイルを削除する
        //unlink($tokenFile);
        echoResponse(200, $response);
    }catch(AppException $e){
        echoResponse($e->getStatusCode(), $e->getResponse());
    }
});

$app->post('/signup', function() use($app){
    $response = array();
	$r = json_decode($app->request->getBody());
	verifyRequiredParams(array('email', 'name', 'password'), $r->user);
    $dbHost = $app->config('db_host');
    $dbName = $app->config('db_name');
    $dbUser = $app->config('db_user');
    $dbPass = $app->config('db_password');
    $db = new UserDB($dbHost, $dbName, $dbUser, $dbPass);
	$name = $r->user->name;
	$email = $r->user->email;
    $password = $r->user->password;
    $token = $r->token;
	$isUserExists = $db->fetchByEmail($email);
	if(!$isUserExists){
		//$password = PasswordHash::hash($password);
		$hashed_password = password_hash($password, PASSWORD_DEFAULT, array('cost' => 10));
		$result = $db->insert($email, $name, $hashed_password);
		if($result){
			$response = array(
				'status' => true,
				'message' => 'User account created successfully!',
				'user' => array(
					'id' => $result,
					'name' => $name,
					'email' => $email
				)
			);
			$session = new Session();
			$session->set('id', $result);
			$session->set('name', $name);
            $session->set('email', $email);
            // ユーザー登録が完了した時点でトークンファイルを削除する
            unlink(dirname(__FILE__) . '/../../token/'. $token);
			echoResponse(200, $response);
        }else{
            $response = array(
                'status' => false,
                'message' => 'Failed to create user...'
            );
			echoResponse(201, $response);
		}
    }else{
        $response = array(
            'status' => false,
            'message' => 'The user with the provided email exists!'
        );
		echoResponse(201, $response);
	}
});

$app->post('/login', function() use($app){
	$r = json_decode($app->request->getBody());
	verifyRequiredParams(array('email', 'password'), $r->user);
	$response = array();
	try {
        $dbHost = $app->config('db_host');
        $dbName = $app->config('db_name');
        $dbUser = $app->config('db_user');
        $dbPass = $app->config('db_password');
        $db = new UserDB($dbHost, $dbName, $dbUser, $dbPass);
		$password = $r->user->password;
		$email = $r->user->email;
		$user = $db->fetchByEmail($email);
		if($user){
			//if(PasswordHash::check($user->password, $password)){
			if(password_verify($password, $user->password)){
				$response = array(
					'status' => true,
					'message' => 'Logged in successfully!',
					'user' => array(
						'id' => $user->id,
						'name' => $user->name,
						'email' => $user->email,
						'createdAt' => $user->created
					)
				);
				$session = new Session();
				$session->set('id', $user->id);
				$session->set('email', $user->email);
				$session->set('name', $user->name);
			}else{
				$response = array(
					'status' => false,
					'message' => 'Login failed. Incorrect credentials.'
				);
			}
		}else{
			$response = array(
				'status' => false,
				'message' => 'No such user is registered.'
			);
		}
	}catch(PDOException $e){
		$response = array(
			'status' => false,
			'message' => $e->getMessage()
		);
	}catch(Exception $e){
		$response = array(
			'status' => false,
			'message' => $e->getMessage()
		);
	}
	echoResponse(200, $response);
});

$app->get('/logout', function() use($app){
	$session = new Session();
	$session->clear();
	$response = array(
		'status' => 'info',
		'message' => 'Logged out successfully!'
	);
	echoResponse(200, $response);
});
