<?php
// httpd-vhosts.confの環境変数を取得
defined('APP_ENV') || define('APP_ENV', (getenv('APP_ENV') ? getenv('APP_ENV') : 'production'));

require 'vendor/autoload.php';

// デフォルトタイムゾーンを確認
$timezone = date_default_timezone_get();
if("Asia/Tokyo" != $timezone){
	date_default_timezone_set("Asia/Tokyo");
}

// 開発用はデバッグモード
if('development' === APP_ENV){
	define('APP_MODE', 'development');
	define('DEBUG_MODE', true);
}else{
	define('APP_MODE', 'production');
	define('DEBUG_MODE', false);
}

$app = new \Slim\Slim(array(
	'debug' => DEBUG_MODE,
	'mode' => APP_MODE
));
$cors = array(
	"origin" => "*",
	"exposeHeaders" => array("Content-Type", "X-Requested-With", "X-authentication", "X-client"),
	"allowMethods" => array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS')
);
$app->add(new \CorsSlim\CorsSlim($cors));

// 設定ファイルの読み込み
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
