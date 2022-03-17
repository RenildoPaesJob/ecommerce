<?php

namespace Hcode;

class PageAdmin extends Page{

    public function __construct($opts = array(), $tpl_dir = "/views/admin/")
    {
        //USANDO AS HERANÇAS.
        parent::__construct($opts, $tpl_dir);//chamando o construtor da classe pai, passandos os parâmentros,
    }
}