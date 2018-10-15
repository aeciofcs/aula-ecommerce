<?php 

use \Classes\Model\User;

function formatPrice(float $vlprice){
	return number_format($vlprice,2,",",".");		
}

function checkLogin($inadmin = true){
	return User::checkLogin($inadmin);
}

function getUserName(){
	$user = User::getFromSession();
	//ar_dump($user);
	//exit;
	return $user->getdesperson();
}


?>