<?php 
session_start();
require_once("vendor/autoload.php");

use Hcode\Model\User;
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();// chamada da classe, contendo o __construct, e dentro do __construct tem o header da pagina;
	$page->setTpl('index');//colocando o template da pagina, e depois a sua execução chama o __destruct.

});

$app->get('/admin', function(){

	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('index');

});

$app->get('/admin/login', function() {

	$page = new PageAdmin([
		"header"=>false, // desabilitando o header padrão
		"footer"=>false // desabilitando o footer padrão
	]);

	$page->setTpl("login");

});

$app->post('/admin/login', function(){//método para pegar os valores atráves do formulário de login e senha;
	
	User::login($_POST['login'], $_POST['password']);//usando o método estático da classe
	header("location: /admin");
	exit;
});

$app->get('/admin/logout', function(){
	User::logout();
	header("location: /admin/login");
	exit;
});

$app->run();//execução da tpl
