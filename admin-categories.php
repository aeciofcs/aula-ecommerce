<?php 

use \Classes\PageAdmin;
use \Classes\Model\User;
use \Classes\Model\Category;
use \Classes\Page;


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
	//Gravando alterações de edição nas Categorias ->>
	User::verifyLogin();
	$category = new Category();
	$category->get((int)$idcategory);
	$category->setData($_POST);	
	$category->save();
	header('Location: /admin/categories');
	exit;
});

$app->get("/categories/:idcategory", function($idcategory){
	
	//Informando categorias no rodapé do site.
	User::verifyLogin();
	$category = new Category();
	$category->get((int)$idcategory);
	
	$page = new Page();	
	$page->setTpl("category", array(
			"category" => $category->getValues(),
			"products" => [] ));
	
});



?>