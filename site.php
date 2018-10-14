<?php 

use \Classes\Page;
use \Classes\Model\Product;
use \Classes\Model\Category;

$app->get('/', function() {
	//Quando instancia a classe page, já renderiza o arquivo header.html
	$products = Product::listAll();
	
	$page = new Page(); 
	$page->setTpl("index", [
		'products' => Product::checkList($products) ]);
});

$app->get("/categories/:idcategory", function($idcategory){
	
	//Informando categorias no rodap? do site.	
	$category = new Category();
	$category->get((int)$idcategory);
	
	$page = new Page();	
	$page->setTpl("category", array(
			"category" => $category->getValues(),
			"products" => Product::checkList($category->getProducts()) ));
	
});

?>