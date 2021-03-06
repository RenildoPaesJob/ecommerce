<?php
session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Category;

$app = new Slim();

$app->config("debug", true);

$app->get("/", function() {
    
	$page = new Page();// chamada da classe, contendo o __construct, e dentro do __construct tem o header da pagina;
	$page->setTpl("index");//colocando o template da pagina, e depois a sua execução chama o __destruct.
});

$app->get("/admin", function(){

	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("index");
});

$app->get("/admin/login", function() {

	$page = new PageAdmin([
		"header" => false, // desabilitando o header padrão
		"footer" => false // desabilitando o footer padrão
	]);

	$page->setTpl("login");
});

$app->post("/admin/login", function(){//método para pegar os valores atráves do formulário de login e senha;
	
	User::login($_POST["login"], $_POST["password"]);//usando o método estático da classe
	header("location: /admin");
	exit;
});

$app->get("/admin/logout", function(){
	User::logout();
	header("location: /admin/login");
	exit;
});

/**
 * ///////////////////////////////////////////////
 * //////////////// CRUD DE USERS ////////////////
 * ///////////////////////////////////////////////
*/

//método para deletar um user
$app->get("/admin/users/:iduser/delete", function ($iduser) {

	User::verifyLogin();

	$user = new User();
	$user->get((int)$iduser);
	$user->delete();
	header("location: /admin/users");
	exit;
});

//método para MOSTRA TODOS os users
$app->get("/admin/users", function () {
	User::verifyLogin();

	$users = User::listAll();
	$page = new PageAdmin(); 
	$page->setTpl("users", array(
		"users" => $users
	));
});

//método para CRIAR um user
$app->get("/admin/users/create", function () {
	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl("users-create");
});

//método para CRIAR um user
$app->post("/admin/users/create", function () {
	User::verifyLogin();

	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
	$user->setData($_POST);
	$user->save();
	header("Location: /admin/users");
	exit;
});

//METODO QUE DELETA USER PELO O POST
$app->post("/admin/users/:iduser/delete", function($iduser){

	User::verifyLogin();	

	$user = new User();
	$user->get((int)$iduser);
	$user->delete();
	header("Location: /admin/users");
	exit;
});

//METODO QUE ALTERA UM USER
$app->get("/admin/users/:iduser", function($iduser) {
	
	User::verifyLogin();

	$user = new User();
	$user->get((int)$iduser);
	$page = new PageAdmin();
	$page->setTpl("users-update", array(
		'user' => $user->getValues()
	));
});

//método para salvar o UPDATE
$app->post("/admin/users/:iduser", function ($iduser) {
	User::verifyLogin();

	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
	$user->get((int)$iduser);
	$user->setData($_POST);
	$user->update();
	header("location: /admin/users");
	exit;
});
/**
 * ///////////////////////////////////////////////
 * //////////// FIM CRUD DE USERS ////////////////
 * ///////////////////////////////////////////////
*/

$app->get("/admin/forgot", function(){
	$page = new PageAdmin([
		"header" => false, // desabilitando o header padrão
		"footer" => false // desabilitando o footer padrão
	]);

	$page->setTpl("forgot");
});

$app->post("/admin/forgot", function(){
	$user = User::getForgot($_POST['email']);

	header("Location: /admin/forgot/sent");
	exit;
});

$app->get("/admin/forgot/sent", function(){
	$page = new PageAdmin([
		"header" => false, // desabilitando o header padrão
		"footer" => false // desabilitando o footer padrão
	]);

	$page->setTpl("forgot-sent");
});

$app->get('/admin/forgot/reset', function(){

	$user = User::validForgotDecrypt($_GET['code']);

	$page = new PageAdmin([
		"header" => false, // desabilitando o header padrão
		"footer" => false // desabilitando o footer padrão
	]);

	$page->setTpl("forgot-reset", array(
		"name" => $user['desperson'],
		"code" => $_GET['code']
	));
});

$app->post('/admin/forgot/reset', function(){

	$forgot = User::validForgotDecrypt($_POST['code']);

	User::setForgotUsed($forgot['idrecovery']);

	$user = new User();

	$user->get((int)$forgot['iduser']);

	
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT, [
		"cost" => 12
	]);
	
	$user->setPassword($password);

	$page = new PageAdmin([
		"header" => false, // desabilitando o header padrão
		"footer" => false // desabilitando o footer padrão
	]);

	$page->setTpl("forgot-reset-success");
});


/**
 * ///////////////////////////////////////////////
 * //////////// CRUD DE CATEGORIA ////////////////
 * ///////////////////////////////////////////////
*/
$app->get('/admin/categories', function(){
	User::verifyLogin();

	$categories = Category::listAll();
	$page = new PageAdmin();

	$page->setTpl('categories', [
		'categories' => $categories
	]);
});

$app->get('/admin/categories/create', function(){
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl('categories-create');
});

$app->post('/admin/categories/create', function(){
	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;
});

$app->get('/admin/categories/:idcategory/delete', function($idcategory){
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header('Location: /admin/categories');
	exit;
});

$app->get('/admin/categories/:idcategory', function($idcategory){
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl('categories-update', [
		'category' => $category->getValues()
	]);
});

$app->post('/admin/categories/:idcategory', function($idcategory){
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;
});

$app->get("/categories/:idcategory", function($idcategory) {
    
	$category = new Category();
	$category->get((int)$idcategory);

	$page = new Page();
	$page->setTpl("category", [
		'category' => $category->getValues(),
		'pruducts' => []
	]);
});
/**
 * ///////////////////////////////////////////////
 * //////////// FIM CRUD DE CATEGORIA ////////////
 * ///////////////////////////////////////////////
*/
$app->run();//execução da tpl
