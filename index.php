<?php 
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Classes\Page;
use \Classes\PageAdmin;
use \Classes\Model\User;
use \Classes\Model\Category;
use \Classes\Model\Product;

$app = new Slim();

$app->config('debug', true);

require_once("functions.php");
require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");

$app->run();

 ?>