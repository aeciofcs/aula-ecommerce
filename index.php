<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Classes\Page;
use \Classes\PageAdmin;
use \Classes\Model\User;
use \Classes\Model\Category;

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

// Rotas para Categorias
$app->get("/admin/categories", function(){
	//Tela inicial com a listagem das Categorias
	User::verifyLogin();
	$categories = Category::listAll();
	
	$page = new PageAdmin();
	$page->setTpl("categories", array(
		"categories" => $categories
	));
	
});

$app->get("/admin/categories/create", function(){
	//Tela de cadsatro de uma categoria
	User::verifyLogin();	
	$page = new PageAdmin();
	$page->setTpl("categories-create");
	
});

$app->post("/admin/categories/create", function(){
	//Gravação de uma nova categoria
	User::verifyLogin();
	$category = new Category();
	$category->setData($_POST);
	$category->save();
	header('Location: /admin/categories');
	exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){
	//Excluir categoria
	User::verifyLogin();
	$category = new Category();	
	$category->get( (int)$idcategory );
	$category->delete();
	header('Location: /admin/categories');
	exit;	
});

$app->get("/admin/categories/:idcategory", function($idcategory){
	//Tela para editar as Categorias
	User::verifyLogin();
	$category = new Category();
	$category->get((int)$idcategory);
	
	$page = new PageAdmin();
	$page->setTpl("categories-update", array(
		"category" => $category->getValues() ));
});

$app->post("/admin/categories/:idcategory", function($idcategory){
	//Gravando alterações de edição nas Categorias
	User::verifyLogin();
	$category = new Category();
	$category->get((int)$idcategory);
	$category->setData($_POST);	
	$category->save();
	header('Location: /admin/categories');
	exit;
});


$app->run();

 ?>