<?php 

use \Classes\Page;
use \Classes\Model\Product;

$app->get('/', function() {
	//Quando instancia a classe page, já renderiza o arquivo header.html
	$products = Product::listAll();
	
	$page = new Page(); 
	$page->setTpl("index", [
		'products' => Product::checkList($products) ]);
});

?>