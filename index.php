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
		"header"=>false, // desabilitando o header padrão
		"footer"=>false // desabilitando o footer padrão
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

$app->get("/admin/users", function() {
	
	User::verifyLogin();
	$users = User::listAll();
	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users" => $users
	));
});

$app->get("/admin/users/create", function() {
	
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("users-create");
});

$app->post("/admin/users/:iduser/delete", function($iduser){

	User::verifyLogin();
});

$app->get("/admin/users/:iduser", function($idUser) {
	
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("users-update");
});

$app->post("/admin/users/create", function(){

	User::verifyLogin();

	$user = new User();

 	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [
 		"cost" => 12
 	]);

 	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
 	exit;
});

$app->post("/admin/users/:iduser", function($iduser){

	User::verifyLogin();
});

$app->run();//execução da tpl
