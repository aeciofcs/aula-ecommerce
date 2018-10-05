<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Classes\Page;
use \Classes\PageAdmin;
use \Classes\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
	//Quando instancia a classe page, já renderiza o arquivo header.html
	$page = new Page(); 
	$page->setTpl("index");
});

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

$app->run();

 ?>