<?php 

use \Classes\PageAdmin;
use \Classes\Model\User;
use \Classes\Model\Category;
use \Classes\Model\Product;

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

$app->get("/admin/categories/:idcategory/products", function($idcategory){
	//
	User::verifyLogin();
	$category = new Category();
	$category->get((int)$idcategory);

	$page = new PageAdmin();	
	$page->setTpl("categories-products", array(
			"category"           => $category->getValues(),
			"productsRelated"    => $category->getProducts(),
			"productsNotRelated" => $category->getProducts(false) ));	
});

$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){
	//Adicionando relacionamento entre produto e categoria
	User::verifyLogin();
	$category = new Category();
	$product  = new Product();
	$category->get((int)$idcategory);
	$product->get((int)$idproduct);
	
	$category->addProduct($product);
	
	header("Location: /admin/categories/" . $idcategory . "/products");
	exit;
	
});

$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){
	       
	//Excluindo relacionamento entre produto e categoria
	User::verifyLogin();
	$category = new Category();
	$product  = new Product();
	$category->get((int)$idcategory);
	$product->get((int)$idproduct);
	
	$category->removeProduct($product);
	
	header("Location: /admin/categories/" . $idcategory . "/products");
	exit;
	
});
       


?>