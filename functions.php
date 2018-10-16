<?php 

use \Classes\Model\User;
use \Classes\Model\Cart;

function formatPrice(float $vlprice){	
	return number_format($vlprice,2,",",".");
}

function checkLogin($inadmin = true){
	return User::checkLogin($inadmin);
}

function getUserName(){
	$user = User::getFromSession();	
	return $user->getdesperson();
}

?>