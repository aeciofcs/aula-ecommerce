<?php 

use \Classes\Page;

$app->get('/', function() {
	//Quando instancia a classe page, já renderiza o arquivo header.html
	$page = new Page(); 
	$page->setTpl("index");
});

?>