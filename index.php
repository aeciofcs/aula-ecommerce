<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Classes\Page;
use \Classes\PageAdmin;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
	//Quando instancia a classe page, já renderiza o arquivo header.html
	$page = new Page(); 
	$page->setTpl("index");
});

$app->get('/admin', function() {
	//Quando instancia a classe page, já renderiza o arquivo header.html
	$page = new PageAdmin(); 
	$page->setTpl("index");
});



$app->run();

 ?>