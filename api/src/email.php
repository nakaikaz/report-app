<?php

function sendPreSignupEmail($email, $urlForRegister){
	$app = \Slim\Slim::getInstance();
	$smtp = new PHPMailer();
	$smtp->isSMTP();
	$smtp->Host = $app->config('smtp_host');
	$smtp->SMTPAuth = true;
	$smtp->Port = $app->config('smtp_port');
	$smtp->Username = $app->config('smtp_user');
	$smtp->Password = $app->config('smtp_pass');
	$smtp->SMTPSecure = 'ssl';
	$smtp->From = $app->config('sender_email');
	$smtp->FromName = $app->config('sender_name');
	$smtp->AddAddress($email);
	$smtp->CharSet = 'UTF-8';
	$smtp->Subject = '[' . $app->config('app_name') . '] ユーザー登録手続きのご案内';
	$smtp->Body = <<<EOT
{$app->config('app_name')}にお申し込みいただき、誠にありがとうございます。

下記のアカウント登録ページで、ユーザー情報の登録を行ってください。
{$urlForRegister}

--
{$app->config('app_name')}

{$app->config('app_name')} 公式サイト・ブログ
{$app->config('app_official_url')}
{$app->config('app_blog_url')}

{$app->config('app_name')} facebookページ
{$app->config('app_facebook_page')}

運営元　◯◯会社
{$app->config('company_url')}
EOT;
	if(!$smtp->send()){
		$response = array(
			'status' => false,
			'message' => 'The email could not be sent.' . $smtp->ErrorInfo
		);
		throw new AppException(500, $response);
	}
	return true;
}

