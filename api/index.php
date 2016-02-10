<?php
define('DEV_HOST', 'agri-report.local');

require 'vendor/autoload.php';

// デフォルトタイムゾーンを確認
$timezone = date_default_timezone_get();
if("Asia/Tokyo" != $timezone){
	date_default_timezone_set("Asia/Tokyo");
}

// 開発用はデバッグモード
$hostname = $_SERVER['SERVER_NAME'];
if(DEV_HOST === $hostname){
	define('APP_ENVIRONMENT', 'development');
	define('DEBUG_MODE', true);
}else{
	define('APP_ENVIRONMENT', 'production');
	define('DEBUG_MODE', false);
}

$app = new \Slim\Slim(array(
	'debug' => DEBUG_MODE,
	'mode' => APP_ENVIRONMENT
));
$cors = array(
	"origin" => "*",
	"exposeHeaders" => array("Content-Type", "X-Requested-With", "X-authentication", "X-client"),
	"allowMethods" => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS')
);
$app->add(new \CorsSlim\CorsSlim($cors));

require 'config.php';

function echoResponse($statusCode, $response){
	$app = \Slim\Slim::getInstance();
	$app->response->setStatus($statusCode);
	$app->response->headers->set('Content-Type', 'application/json');
	echo json_encode($response);
}

function verifyRequiredParams($fields, $params){
	$error = false;
	$error_fields = '';
	foreach($fields as $field){
		if(!isset($params->$field) || strlen(trim($params->$field)) < 1){
			$error = true;
			$error_fields .= $field . ',';
		}
	}

	if($error){
		$response = array();
		$app = \Slim\Slim::getInstance();
		$response['status'] = 'error';
		$response['message'] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
		echoResponse(200, json_encode($response));
		$app->stop();
	}
}

require_once 'routes/auth.php';
require_once 'routes/report.php';

$app->run();
?>
