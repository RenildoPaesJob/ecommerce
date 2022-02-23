<?php 

require_once("vendor/autoload.php");

$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Hcode\Page();// chamada da classe, contendo o __construct, e dentro do __construct tem o header da pagina;
	$page->setTpl("index");//colocando o template da pagina, e depois a sua execução chama o __destruct.

});

$app->run();

 ?>