<?php

namespace Hcode;

use Rain\Tpl;//chamando a classe Tpl pelo seu nomespace

class Page{

    private $tpl;
    private $options = [];
    private $defaults = [
        "header" => true,
        "footer" => true,
        "data" => []
    ];

    //construct para o cabeçalho das paginas
    public function __construct($opts = array())
    {
        $this->options = array_merge($this->defaults, $opts);//faz um merge dos arrays, um sobrescrevendo o outro (o ultimo por cima)

        $config = array(
            "base_url"  => null,
            "tpl_dir"   => $_SERVER["DOCUMENT_ROOT"] . "/views/", //caminho do template
            "cache_dir" => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/", //caminho do cache
            "debug"     => true
        );

        Tpl::configure($config);

        $this->tpl = new Tpl(); // instância da library dentro da $tpl;

        if($this->options['data']) $this->setData($this->options['data']);//metodo para buscar todos os dados do corpo da pagina

        if($this->options['header'] === true) $this->tpl->draw('header', false);//carregando em todas as paginas (que chamar essa classe) o cabeçalho da pagina.
    }

    private function setData($data = array()){//function que busca array de dados a serem usados na pagina.
        foreach ($data as $key => $value) {//pegando cada dado, e sua chave e setando na $value.
            $this->tpl->assign($key, $value);//pegando sua chave e seu valor, e colocando no template da pagina
        }
    }

    public function setTpl($name, $data = array(), $returnHTML = false){//function para montar o template na tela do user
        
        $this->setData($data);//array de dados a serem usados na pagina
        
        return $this->tpl->draw($name, $returnHTML);// $name => nome da pagina que contém o corpo da pagina os dados setados no metodo acima.
    }

    //destruct para o rodapé da pagina
    public function __destruct()
    {
        if ($this->options['footer'] === true) $this->tpl->draw("footer", false);
    }
}