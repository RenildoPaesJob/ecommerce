<?php

namespace Hcode\Model;

class Model
{

    private $values = []; //vai conter todos os valores dos campos que tem dentro do objeto.
    /*
    * verifica se foi o método getters ou setters.
    * O argumento $name é o nome do método sendo chamado.
    * O argumento $arguments é um array enumerado contendo os parâmetros passados para o método $name.
    */
    public function __call($name, $arguments)
    {
        $method = substr($name, 0, 3); //0 = inicio da string(método), 3 = quantidade de caracteres(do metodo passado).
        $fieldName = substr($name, 3, strlen($name));

        switch ($method) {
            case 'get':
                return $this->values[$fieldName];
                break;

            case 'set':
                $this->values[$fieldName] = $arguments[0];
                break;
        }
    }

    public function setData($data = array()) //$data => a ficha(todos os dados gravados) do usuário que ele encontrou no banco.
    {
        foreach ($data as $key => $value) {

            $this->{"set" . $key}($value);
        }
    }

    public function getValues()
    {
        return $this->values; //retorna os valores que estão dentro de $values.
    }
}
