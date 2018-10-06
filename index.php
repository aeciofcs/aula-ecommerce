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

//Rotas para Users
$app->get('/admin/users', function() {	
	//Rota para página de listagem dos usuários.
	User::verifyLogin();
	$users = User::listAll();
	
	$page = new PageAdmin(); 
	$page->setTpl("users", array(
		"users" => $users
	));
});

$app->get('/admin/users/create', function() {	
	//Rota para acessar a página de cadastro de usuário
	User::verifyLogin();
	$page = new PageAdmin(); 
	$page->setTpl("users-create");
});

$app->get('/admin/users/:iduser/delete', function($iduser) {	
	User::verifyLogin();
	$user = new User();
	$user->get((int)$iduser);
	$user->delete();
	
	//Redireciona para a lista de usuários
	header("Location: /admin/users");
	exit;
});

$app->get('/admin/users/:iduser', function($iduser) {
	//Rota para acessar a pagina de alteração do usuário
	User::verifyLogin();
	
	$user = new User();
	$user->get((int)$iduser);
	
	$page = new PageAdmin(); 
	$page->setTpl("users-update", array( 
		"user" => $user->getValues()
	));
});

$app->post('/admin/users/create', function() {	
	//Rota para gravar os dados do Usuário vindo do POST da página.
	User::verifyLogin();
	
	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))? 1 : 0;
	$_POST["despassword"] = password_hash($_POST["despassword"], PASSWORD_BCRYPT, ["cost" => 12]);
	$user->setData($_POST);	
	$user->save($user);
	
	//Redireciona para a lista de usuários
	header("Location: /admin/users");
	exit;
});

$app->post('/admin/users/:iduser', function($iduser) {	
	User::verifyLogin();
	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))? 1 : 0;
	//$_POST["despassword"] = password_hash($password, PASSWORD_DEFAULT);
	$user->get((int)$iduser); //carrega e coloca nos values;
	$user->setData($_POST); //alteração do que foi alterado via POST;
	$user->update();
    
	//Redireciona para a lista de usuários
	header("Location: /admin/users");
	exit;
});



$app->run();

 ?>