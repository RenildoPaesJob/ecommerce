<?php
session_start();
require_once("vendor/autoload.php");

use Hcode\Model\User;
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;

$app = new Slim();

$app->config("debug", true);

$app->get("/", function () {

	$page = new Page(); // chamada da classe, contendo o __construct, e dentro do __construct tem o header da pagina;
	$page->setTpl("index"); //colocando o template da pagina, e depois a sua execução chama o __destruct.

});

$app->get("/admin", function () {

	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("index");
});

$app->get("/admin/login", function () {

	$page = new PageAdmin([
		"header" => false, // desabilitando o header padrão
		"footer" => false // desabilitando o footer padrão
	]);

	$page->setTpl("login");
});

$app->post("/admin/login", function () { //método para pegar os valores atráves do formulário de login e senha;

	User::login($_POST["login"], $_POST["password"]); //usando o método estático da classe
	header("Loction: /admin");
	exit;
});

$app->get("/admin/logout", function () {
	User::logout();
	header("Location: /admin/login");
	exit;
});
/**
 * ///////////////////////////////////////////////
 * //////////////// CRUD DE USERS ////////////////
 * ///////////////////////////////////////////////
 */
//método para MOSTRA TODOS os users
$app->get("/admin/users", function () {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("users");
});

//método para CRIAR um user
$app->get("/admin/user/create", function () {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("users-create");
});

//método para ATUALIZAR um user
$app->get("/admin/user/:iduser", function ($iduser) {
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("users-update");
});

//método para salvar os registros
$app->post("/admin/user/create", function () {
	User::verifyLogin();
});

//método para salvar o UPDATE
$app->post("/admin/user/:iduser", function ($iduser) {
	User::verifyLogin();
});

//método para deletar um user
$app->delete("/admin/user/:iduser", function ($iduser) {
	User::verifyLogin();
});

$app->run();//execução da tpl
