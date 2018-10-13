<?php

use \Classes\PageAdmin;
use \Classes\Model\User;

$app->get('/admin', function() {
	User::verifyLogin();
	
	//Quando instancia a classe PageAdmin, já renderiza o arquivo header.html
	$page = new PageAdmin(); 
	$page->setTpl("index");
	
});

$app->get('/admin/login', function() {	
	//Rota para Login do Admin
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]); 
	$page->setTpl("login");
});

$app->post('/admin/login', function() {	
	User::login($_POST["login"], $_POST["password"]);
	header("location: /admin");
	exit;
});

$app->get('/admin/logout', function() {	
	User::logout();
	header("location: /admin/login");
	exit;
});


//Esqueceu a senha..
$app->get('/admin/forgot', function() {	
	//Rota para o Esqueceu a senha..
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]); 
	$page->setTpl("forgot");
});

$app->post('/admin/forgot', function(){	
	$user = User::getForgot($_POST["email"]);
	
	header("Location: /admin/forgot/sent");
	exit;	
});

$app->get('/admin/forgot/sent', function(){	
	
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]); 
	$page->setTpl("forgot-sent");	
});

$app->get('/admin/forgot/reset', function(){	
	
	$user = User::validForgotDecrypt($_GET["code"]);
	
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]); 
	$page->setTpl("forgot-reset", array(
		"name" => $user["desperson"],
		"code" => $_GET["code"] ));	
});

$app->post('/admin/forgot/reset', function(){
	
	$forgot = User::validForgotDecrypt($_POST["code"]);
	User::setForgotUsed($forgot["idrecovery"]);
	$user = new User();
	$user->get((int)$forgot["iduser"]);
	
	$password = password_hash($_POST["password"], PASSWORD_BCRYPT, 
													["cost" => 12]);
													
	$user->setPassword($password);
	
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]); 
	$page->setTpl("forgot-reset-success");	
	
});


?>