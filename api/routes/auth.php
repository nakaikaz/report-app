<?php

$app->get('/session', function() use($app){
	$session = new \lib\Session();
	$response = array();
	$response['id'] = $session->get('id');
	$response['name'] = $session->get('name');
	$response['email'] = $session->get('email');
	echoResponse(200, $response);
});

$app->post('/signup', function() use($app){
	$response = array();
	$r = json_decode($app->request->getBody());
	verifyRequiredParams(array('email', 'name', 'password'), $r->user);
	$db = new \lib\db\UserDB(
		$app->config('db_host'),
		$app->config('db_name'),
		$app->config('db_user'),
		$app->config('db_password')
	);
	$name = $r->user->name;
	$email = $r->user->email;
	$password = $r->user->password;
	$isUserExists = $db->fetchByEmail($email);
	if(!$isUserExists){
		$password = \lib\util\PasswordHash::hash($password);
		$result = $db->insert($email, $name, $password);
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
			$session = new \lib\Session();
			$session->set('id', $result);
			$session->set('name', $name);
			$session->set('email', $email);
			echoResponse(200, $response);
		}else{
			$response['status'] = false;
			$response['message'] = 'Failed to create user...';
			echoResponse(201, $response);
		}
	}else{
		$response['status'] = false;
		$response['message'] = 'The user with the provided email exists!';
		echoResponse(201, $response);
	}
});

$app->post('/login', function() use($app){
	$r = json_decode($app->request->getBody());
	verifyRequiredParams(array('email', 'password'), $r->user);
	$response = array();
	try {
		$db = new \lib\db\UserDB(
			$app->config('db_host'),
			$app->config('db_name'),
			$app->config('db_user'),
			$app->config('db_password')
		);
		$password = $r->user->password;
		$email = $r->user->email;
		$user = $db->fetchByEmail($email);
		if($user){
			if(\lib\util\PasswordHash::check($user->password, $password)){
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
				$session = new \lib\Session();
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
	$session = new \lib\Session();
	$session->clear();
	$response = array(
		'status' => 'info',
		'message' => 'Logged out successfully!'
	);
	echoResponse(200, $response);
});
