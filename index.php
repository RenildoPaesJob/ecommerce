<?php
session_start();
require_once("vendor/autoload.php");

use Hcode\Model\User;
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;

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

$app->post("/admin/users/:iduser/delete", function($iduser){

	User::verifyLogin();	

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;
});

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

$app->post("/admin/users/:iduser", function($iduser){

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header('location: /admin/users');
	exit;
});

$app->run();//execução da tpl
