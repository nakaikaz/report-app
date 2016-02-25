<?php
require_once dirname(__FILE__) . '/../src/Session.php';
require_once dirname(__FILE__) . '/../src/db/UserDB.php';
//require_once dirname(__FILE__) . '/../src/util/PasswordHash.php';

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
    verifyRequiredParams(array('email'), $r->user);
    $dbHost = $app->config('db_host');
    $dbName = $app->config('db_name');
    $dbUser = $app->config('db_user');
    $dbPass = $app->config('db_password');
    $db = new UserDB($dbHost, $dbName, $dbUser, $dbPass);
    $isUserExists = $db->fetchByEmail($email);
    if(!$isUserExists){
        // TODO:登録用URLを記載したメールを送信する
        $response = array(
            'status' => true,
            'message' => 'You can proceed next step.',
        );
        echoResponse(200, $response);
	}else{
        $response = array(
            'status' => false,
            'message' => 'The user with the provided email exists!'
        );
		echoResponse(201, $response);
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
